<?php
namespace Cli;

use Cli\Commons\Console;
use Cores\Config\Config;
use Logics\Commons\ChatWorkApi;

/**
 * サイトメンバー情報の取得
 */
class CliRssReader
{
    protected $logDir = "";
    
    /**
     * @var ChatWorkApi
     */
    protected $chatWorkApi = null;
    
    public function __construct()
    {
        $this->getLogic();
        $this->logDir = Config::load("dir.logs") . "/cli";
    }
    
    protected function getLogic()
    {
        $this->chatWorkApi = new ChatWorkApi();
    }
    
    /**
     * RSS取得＋チャットワーク送信
     */
    public function indexAction()
    {
        if (date("H") > 2 && date("H") < 7) {
            exit;
        }

        Console::log("Start.");

        $rssUrl = 'http://ja.scp-wiki.net/feed/forum/posts.xml';
//        $rssUrl = 'http://ja.scp-wiki.net/feed/forum/ct-790926.xml';
        $cpName = basename($rssUrl, ".xml");
        $logPath = $this->logDir . "/rss-reader_{$cpName}.log";
        $newPosts = array();

        // getRss
        $rss = simplexml_load_file($rssUrl);

        // parseRss
        $posts = array();
        foreach ($rss->channel->item as $item) {
            $posts[] = array(
                "pubDate" => (String)$item->pubDate,
                "category" => (String)$rss->channel->title,
                "title" => (String)$item->title,
                "link" => (String)$item->link,
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

            // 空行詰め
            $posts = array_filter($posts);
            $posts = array_values($posts);

            // unserializeLastLog
            $rawLogs = array_reverse($rawLogs);
            $lastLog = unserialize($rawLogs[0]);

            // checkNewThreadExist
            foreach ($posts as $post) {
                
                // ログ最新より新しいポストのみ抽出する
                if (strtotime($lastLog["pubDate"]) < strtotime($post["pubDate"])) {
                    $newPosts[] = $post;
                    file_put_contents($logPath, serialize($post) . "\n", FILE_APPEND);
                }
            }

        };

        if (!empty($newPosts)) {
            
            $msg = "";
            foreach ($newPosts as $newPost){
                $msg .= implode("\t", $newPost)."\n";
            }

            var_dump($msg);

            $roomId = Config::load("chatwork.room_id_myroom");
            $responseBody = $this->chatWorkApi->rooms($roomId, "messages", array("body" => $msg));

            var_dump($responseBody);

        }
        
        Console::log("Done.");
    }
    
}
