<?php
namespace Cli;

use Cli\Commons\Console;
use Cores\Config\Config;

/**
 * サイトメンバー情報の取得
 */
class CliRssReader
{
    protected $logDir = "";
    
    public function __construct()
    {
        $this->getLogic();
        $this->logDir = Config::load("dir.logs") . "/cli";
    }
    
    protected function getLogic()
    {
    }

    /**
     * @param string $rssUrl 特定categoryのポストRSS
     */
    public function indexAction()
    {
        if (date("H") > 2 && date("H") < 7) {
            exit;
        }

        Console::log("Start.");

        $rssUrl = 'http://ja.scp-wiki.net/feed/forum/posts.xml';
        $cpName = basename($rssUrl, ".xml");
        $logPath = $this->logDir . "/rss-reader_{$cpName}.log";
        $newPosts = array();

        // getRss
        $rss = simplexml_load_file($rssUrl);

        // parseRss
        $posts = array();
        foreach ($rss->channel->item as $item) {
            $posts[] = array(
                "category" => (String)$rss->channel->title,
                "title" => (String)$item->title,
                "link" => (String)$item->link,
                "pubDate" => (String)$item->pubDate,
            );
        }
        $posts = array_reverse($posts);

        // diffRssLog
        if (($rawLogs = file($logPath)) === false) {

            // insertNewData
            foreach ($posts as $post) {
                file_put_contents($logPath, serialize($post) . "\n", FILE_APPEND);
            }

            $newPosts = $posts;

        } else {

            // unserializeLastLog
            $lastLog = unserialize($rawLogs[0]);

            // checkNewThreadExist
            foreach ($posts as $post) {

                // ログ最新より新しいポストのみ抽出する
                if (strtotime($lastLog["pubDate"] < strtotime($post["pubDate"]))) {
                    $newPosts[] = $post;
                }
            }

        };

        var_dump($newPosts);

        if (!empty($newPosts)) {

            //https://api.chatwork.com/v2
            // TODO つくったライブラリつかう
            $this->sendChatWork(implode("\n", $newPosts), Config::load("chatwork.room_id_myroom"));

        }
        
        Console::log("Done.");
    }

    private function sendChatWork($msg, $roomId)
    {
        $option = array('body' => "[njr-sys]: " . $msg);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.chatwork.com/v2/rooms/' . $roomId . '/messages');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-ChatWorkToken: ' . Config::load("chatwork.api")));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($option, '', '&'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        echo $response;
    }
    
}

