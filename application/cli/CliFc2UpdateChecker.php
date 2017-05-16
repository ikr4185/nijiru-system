<?php
namespace Cli;

use Cli\Commons\CliAbstract;
use Cli\Commons\Console;

// 一時利用なので肥大化しても良しとする
use Logics\Commons\Scraping;
use Logics\Commons\Mail;

/**
 * Class CliLowVoteChecker
 * @package Cli
 */
class CliFc2UpdateChecker extends CliAbstract
{

    protected $logic = null;

    const LOG_DIR = "/home/njr-sys/public_html/logs/fc2wiki";

    protected function getLogic()
    {
//        $this->logic = new CliLowVoteCheckerLogic();
    }

    public function indexAction()
    {
        Console::log("Start.");

        // 最新の更新をスクレイピングする
        Console::log("scraping");

        $rawHtml = Scraping::run("http://scpjapan.wiki.fc2.com/wiki/%E6%9C%80%E8%BF%91%E6%9B%B4%E6%96%B0%E3%83%BB%E4%BD%9C%E6%88%90%E3%81%97%E3%81%9F%E3%83%9A%E3%83%BC%E3%82%B8%28100%E4%BB%B6%29");

        preg_match_all('/<div class="user_body">(.*?)<!--\/user_body-->/s', $rawHtml, $matches);
        $userBody = $matches[1][0];

        $pattern = '/<li class="ulist1" title="更新日時:(.*?)"><a href="(.*?)">(.*?)<\/a><\/li>/';
        preg_match_all($pattern, $userBody, $matches);

        $titles = $matches[3];
        $links = $matches[2];
        $dates = $matches[1];

        // ログ（古い順ソート）に保存されていない更新があれば記録する
        Console::log("log checking");

        // ファイル読み込み
        $fileName = self::LOG_DIR . "/updates.log";
        if (touch($fileName)) {
            $logs = file($fileName);
            if (!$logs) {
                Console::log("can not open {$fileName}.");
                $logs = array();
            }
        } else {
            Console::log("can not touch {$fileName}.");
            die();
        }

        // 全行トリミング
        $logs = array_map(function ($val) {
            return trim($val);
        }, $logs);

        // ログチェック
        $newUpdate = array();
        $newUpdateCount = 0;
        foreach ($titles as $key => $title) {

            // 保存する行を生成
            $title = trim($title);
            $link = "http://scpjapan.wiki.fc2.com" . trim($links[$key]);
            $date = trim($dates[$key]);
            $record = "{$date} {$title} {$link}";

            // ログの中に同一行があるか確認
            if (in_array($record, $logs)) {
                // あれば処理停止
                break;
            }

            // 無ければ更新リストに追加
            $newUpdate[] = $record;
            $newUpdateCount++;
        }

        // 更新リストをマージ
        $newUpdate = array_reverse($newUpdate);
        $logs = array_merge($logs, $newUpdate);

        // 上書き保存
        $logStr = implode("\n", $logs);
        file_put_contents($fileName, $logStr);

        // 数が多い場合は通知する
        Console::log("update {$newUpdateCount}.");

        if ($newUpdateCount >= 3) {

            $message = "翻訳wikiの更新が 3時間で {$newUpdateCount} 件を超過しました。\n";
            $message .= implode("\n\n",$newUpdate);

            $mail = new Mail("/home/njr-sys/public_html/application/views/mail_templates/fc2.tpl");
            $mail->send('ikr.4185@gmail.com', array(
                "user" => "ikr_4185",
                "now" => date("Y-m-d H:i:s"),
                "message" => $message,
            ));
        }

        Console::log("Done.");
    }

}

