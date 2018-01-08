<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;
use Logics\Commons\Mail;
use Cli\Commons\Console;
use Models\LowVoteLogModel;

/**
 * Class CliLowVoteCheckerLogic
 * @package Logics
 */
class CliLowVoteCheckerLogic extends AbstractLogic
{
    const LOWEST_RATED_PAGES_URL = "http://ja.scp-wiki.net/lowest-rated-pages";
    
    /**
     * @var LowVoteLogModel
     */
    protected $model;
    
    protected function getModel()
    {
        $this->model = LowVoteLogModel::getInstance();
    }
    
    /**
     * 評価の低い記事一覧スクレイピング
     * @return mixed|string
     */
    public function scraping()
    {
        $html = Scraping::run(self::LOWEST_RATED_PAGES_URL);
        
        // curlのrangeオプションが死んでるっぽいので暫定対応
//        $html = substr($html, 23326, 4761);
        
        return $html;
    }
    
    /**
     * 生のソースから各データをパースする
     * @param $html
     * @return array
     */
    public function parseLowVotes($html)
    {
        // 記事始まり～記事終わりまでを取得
        $contents = mb_strstr($html, '<div id="main-content">', false);
        $contents = mb_strstr($contents, '<div class="list-pages-box">', false);
        $contents = mb_strstr($contents, '<div class="page-tags">', true);
        
        // <a href="/scp-395-jp">Scp 395 Jp　望遠眼鏡</a> <span style="color: #777">(評価: -35, コメント: 3)</span>
        preg_match_all('@(<a href="/)([\s\S]*?)(">)([\s\S]*?)(</a> <span style="color: #777">\(評価: )([\s\S]*?)(, コメント: )([\s\S]*?)(\)</span>)@', $contents, $allArticle);
        
        // わかり易い名前にしような
        $LowVotes = array();
        foreach ($allArticle[2] as $key => $val) {
            
            $LowVotes[$key]['url'] = $allArticle[2][$key];
            $LowVotes[$key]['title'] = $allArticle[4][$key];
            $LowVotes[$key]['vote'] = $allArticle[6][$key];
            $LowVotes[$key]['comment'] = $allArticle[8][$key];
            
        }
        return $LowVotes;
    }
    
    /**
     * 削除基準以下かのフラグを付与
     * @param $lowVotes    array    scrapeWrapperで切り出した配列 (低評価記事一覧掲載記事の配列)
     * @return array    array    削除基準以下に到達しているかのフラグを付与した配列
     */
    public function checkVote($lowVotes)
    {
        $result = array();
        
        // 低評価記事一覧掲載記事の配列でループ
        foreach ($lowVotes as $key => $val) {
            $result[$key] = $val;
            $result[$key]["is_expanding"] = false;
            
            // Vote数が削除基準なら、is_expandingをtrue
            if (intval($val['vote']) <= -3) {
                $result[$key]["is_expanding"] = true;
            }
        }
        return $result;
    }
    
    /**
     * LVC標準配列へ変換
     * @param $lowVotes
     * @param bool $is_debug
     * @return array
     */
    public function convertLvcArray($lowVotes, $is_debug = false)
    {
        $lvcArray = array();
        
        // 低評価記事一覧掲載記事の配列でループ
        foreach ($lowVotes as $key => $post) {
            
            // 各記事毎に、LVC標準形式に変換する
            Console::log("convert {$post['url']}", "convertLvcArray");
            $lvcPost = $this->convertLvcPost($post);
            
            // LVC標準配列に追加する
            $lvcArray = array_merge($lvcArray, $lvcPost);
        }
        
        // debug ////////////////////////////////////////
        if ($is_debug) {
            // デバッグ用記事配列を追加
//            $lvcArray['SCP-test-JP'] = array(
//                'post' => '2016-03-25 00:00:00',
//                'del_date' => '2016-07-19 23:28:00',
//                'protect' => false,
//                'is_notified' => false,
//                'vote' => '-100',
//                'url' => 'njr-sys.net',
//                'comment' => '100',
//                'is_expanding' => true,
////				'is_expanding' => false,
//            );
        }
        
        return $lvcArray;
    }
    
    /**
     * 低評価記事一覧 → 各記事毎にLVC標準形式に整形
     * @param $post
     * @return mixed
     */
    protected function convertLvcPost($post)
    {
        $lvcPost["{$post['title']}"] = $this->countExtensionTime("http://ja.scp-wiki.net/" . $post['url']);
        
        $lvcPost["{$post['title']}"]['vote'] = $post['vote'];
        $lvcPost["{$post['title']}"]['url'] = "http://ja.scp-wiki.net/" . $post['url'];
        $lvcPost["{$post['title']}"]['comment'] = $post['comment'];
        $lvcPost["{$post['title']}"]['is_expanding'] = $post['is_expanding'];
        
        return $lvcPost;
    }
    
    /**
     * 削除猶予時刻はいつか(削除勧告のチェック含む)
     * @param $url
     * @return array ( 投稿時刻, 猶予期限, 通知済みフラグ )
     */
    protected function countExtensionTime($url)
    {
        // ディスカッションをスクレイピング
        $html = $this->getDiscussionHtml($url);
        
        // 投稿時刻の取得
        $postTimestamp = $this->checkPostTime($html);
        $postDate = date("Y-m-d H:i:s", $postTimestamp);
        
        // 現時刻からの、猶予期限の算出
        $gracePeriod = $this->calculateGracePeriod();
        
        // 削除通知済みなら猶予期限を置換、通知済みフラグをtrueにする
        $isNotified = false;
        preg_match_all('@(<iframe src="http://scp-jp-sandbox2.wdfiles.com/local--files/holy-nova/timer.html\?timestamp=)(.*?)(&amp;type=0")@', $html, $delDate);
        if (!empty($delDate[2])) {
            $gracePeriod = date("Y-m-d H:i:s", substr($delDate[2][0], 0, -3));
            $isNotified = true;
        }
        unset($html);
        
        // 結果を配列にしてリターン
        $result = array(
            "post" => $postDate,
            "del_date" => $gracePeriod,
            "is_notified" => $isNotified,
        );
        return $result;
    }
    
    /**
     * ページごとのディスカッションのソースを取得する
     * @param $url
     * @return mixed|string
     */
    protected function getDiscussionHtml($url)
    {
        // フォーラムURLの取得
        $html = Scraping::run($url);
        preg_match_all('@(<a href="/forum/)([\s\S]*?)(" class="btn btn-default" id="discuss-button">)@', $html, $discussUrlArray);
        
        // 念のためスリープ
        sleep(1);
        
        // ソースの取得
        $html = Scraping::run("http://ja.scp-wiki.net/forum/" . $discussUrlArray[2][0]);
        
        // TODO 複数ページになった際、ページャーをカウントして、全ページをスクレイピングする処理
        
        return $html;
    }
    
    /**
     * 記事の投稿時刻をチェックする
     * @param $html
     * @return bool|string
     */
    protected function checkPostTime($html)
    {
        // 投稿日時のパース
        // <span class="odate time_1456835280 format_%25e%20%25b%20%25Y%2C%20%25H%3A%25M%7Cagohover">01 Mar 2016 12:28</span><br/>
        preg_match_all('@(<span class="odate time_)(.*)( format_%25e%20%25b%20%25Y%2C%20%25H%3A%25M%7Cagohover">)(.*)(</span>)@', $html, $postDate);
        
        // Wikidotの日付をTimestampに変換
        return Scraping::convertWikidotDateToTimestamp($postDate[4][0]);
    }
    
    /**
     * 現時刻からの、猶予期限の算出
     * @return bool|string
     */
    protected function calculateGracePeriod()
    {
        // 削除基準到達から72時間 の同意待ち
        $gracePeriod = date("Y-m-d H:i:s", time() + 72 * 60 * 60);
        return $gracePeriod;
    }
    
    /**
     * 0~-2の記事から、評価が回復した記事を抽出
     * @param $yellowCardsLvcArray
     * @return array
     */
    public function extractRecoveredPosts($yellowCardsLvcArray)
    {
        $recoveredLvcArray = array();
        foreach ($yellowCardsLvcArray as $post) {
            
            // DBにある記事 = 評価が回復した記事を抽出
            $dbResult = $this->model->searchLowVotesNonDel($post["url"], $post["post"]);
            
            // 回復記事があれば
            if (!empty($dbResult)) {
                // LVC標準形式に変換する
                $recoveredLvcArray = $this->convertLvcDbRecord($dbResult);
            }
        }
        return $recoveredLvcArray;
    }
    
    /**
     * DB → 各記事毎にLVC標準形式に整形
     * @param $dbRecord
     * @return mixed
     */
    protected function convertLvcDbRecord($dbRecord)
    {
        $lvcPost["{$dbRecord['name']}"]["post"] = $dbRecord['post_date'];
        $lvcPost["{$dbRecord['name']}"]["del_date"] = $dbRecord['del_date'];
        $lvcPost["{$dbRecord['name']}"]["is_notified"] = (bool)$dbRecord['is_notified'];
        $lvcPost["{$dbRecord['name']}"]['vote'] = null;
        $lvcPost["{$dbRecord['name']}"]['url'] = $dbRecord['url'];
        $lvcPost["{$dbRecord['name']}"]['comment'] = null;
        $lvcPost["{$dbRecord['name']}"]['is_expanding'] = true; // DBに入ってる時点でレッドカード記事
        
        return $lvcPost;
    }
    
    /**
     * レッドカード記事について処理し、DB保存、処理結果をLVC標準配列として返す
     * @param $redCardsLvcArray
     * @return array|null
     */
    public function saveData($redCardsLvcArray)
    {
        // 処理結果として使用する、LVC標準配列
        $saveInfoArray = array();
        
        try {
            
            // レッドカード記事の配列でループ
            foreach ($redCardsLvcArray as $key => $info) {
                
                // 既存DBレコードから、過去の削除対象記事含めて検索
                $dbRecord = $this->model->searchLowVotes($info['url'], $info['post']);
                
                if (!$dbRecord) {
                    // DBにレコードがなかった時の処理
                    Console::log("no record {$info['url']}", "saveData");
                    
                    // 新規レコードをインサート
                    $this->insertPost($key, $info);
                    
                    // 処理結果を配列に追加
                    $saveInfoArray[$key] = $info;
                    
                } else {
                    // DBにレコードがあった時の処理
                    
                    if (1 == $dbRecord["del_flg"]) {
                        // ソフトデリート済みがまた来た時
                        // 「ばかだな　またきたのか」
                        Console::log("ReEntry {$info['url']}", "saveData");
                        
                        // ソフトデリート解除
                        $this->model->setSoftDeleteLowVotes(0, $dbRecord["url"], $dbRecord["post_date"]);
                        
                        // 猶予期限+基準超え日時+記事タイトル($key)を更新する
                        $this->updateLowVotes($dbRecord["url"], $dbRecord["post_date"], $key);
                        
                        // 処理結果を配列に追加
                        $saveInfoArray[$key] = $info;
                    }
                }
                
                // 猶予勧告状況を保存
                if ($info['is_notified']) {
                    
                    // 猶予済みフラグを更新する
                    $this->model->updateNotified($info['url'], $info['post'], "1");
                    
                    // 猶予期限を更新する
                    $this->model->updateMulti($info['url'], $info['post'], "del_date", $info["del_date"]); // とりあえず期限の更新
                    
                }
            }
        } catch (\Exception $e) {
            // 例外処理
            // とりあえずログに記録
            $this->saveLogs($e->getMessage());
            Console::log($e->getMessage() . $e->getLine(), "saveData");
        }
        
        // 処理結果を返す
        if (!empty($saveInfoArray)) {
            return $saveInfoArray;
        }
        return null;
    }
    
    /**
     * 実行ログを保存
     * @param $msg
     */
    public function saveLogs($msg)
    {
        $result = file_put_contents("/home/njr-sys/public_html/application/cli/logs/low_vote_log.log", $msg . "\n", FILE_APPEND);
        if ($result) {
            Console::log("logged", "saveLogs");
        }
    }
    
    /**
     * 新規削除対象記事をDBに追加する
     * @param $key
     * @param $info
     * @return bool
     * @see CliLvcModel::saveData()
     */
    protected function insertPost($key, $info)
    {
        // DBにレコード追加
        $result = $this->model->insertLowVotes($key, $info['url'], $info["post"], $info["del_date"]);
        
        // エラー処理
        if (!$result) {
            echo $key . "insert fail\n"; // TODO 今んとこターミナル表示のみ
            return false;
        }
        return true;
    }
    
    /**
     * 猶予期限+基準超え日時を更新する
     * @param string $url 記事URL
     * @param string $postDate 投稿日
     * @param $name
     * @return bool
     * @see CliLvcModel::saveData()
     */
    protected function updateLowVotes($url, $postDate, $name)
    {
        // 猶予期間の算出
        $gracePeriod = $this->calculateGracePeriod();
        
        // UPDATE実行
        $result = $this->model->updateLowVotes($url, $postDate, $gracePeriod, $name);
        return $result;
    }
    
    /**
     * DBにあるが、低評価記事データになかった記事(評価回復記事)をソフトデリート
     * @param array $lvcArray 削除基準以下になった記事のLVC標準形式配列
     * @return bool
     */
    public function deleteLowVotes($lvcArray)
    {
        // 低評価記事データ（LVC標準形式配列）から、URL をキーとする post_date の配列を生成
        $redPosts = array();
        foreach ($lvcArray as $info) {
            if ($info["url"]) {
                $redPosts[$info["url"]] = $info["post"];
            }
        }

        // DBの全記事レコードを取得(ソフトデリートされた記事は含まない)
        $delInfoArray = $this->model->getAllLowVotes();

        // 記事レコード配列から、URL をキーとする post_date の配列を生成
        $recordedPosts = array();
        if (is_array($delInfoArray)) {
            foreach ($delInfoArray as $info) {
                $recordedPosts[$info["url"]] = $info["post_date"];
            }
        }

        // もしDB上に、ソフトデリートされていない同一URLのレコードがあり、投稿日時も同一なら、同一記事と判定し、ソフトデリート対象にする
        $delTargets = array();
        foreach ($redPosts as $redPostsUrl => $redPostsDate) {
            if (isset($recordedPosts[$redPostsUrl]) && $recordedPosts[$redPostsUrl] == $redPostsDate) {
                $delTargets[] = $recordedPosts[$redPostsUrl];
            }
        }
        
        // ソフトデリート対象レコードがなかったら false 返して終了
        if (empty($delTargets)) {
            return false;
        }
        
        // ソフトデリート実行
        foreach ($delTargets as $delTarget) {
            $result = $this->model->setSoftDeleteLowVotes(1, $delTarget["url"], $delTarget["post_date"]);
            // エラーで中止
            if (!$result) {
                return false;
            }
        }
        return true;
        
    }
    
    /**
     * 各メール通知の条件をまとめる ( Linuxパーミッションの要領で計算 )
     * @param $saveInfoArray
     * @param $recovered_lvcArray
     * @param $lvcArray
     * @param $deletion_existed
     * @return int
     */
    public function calculateLvcStatus($saveInfoArray, $recovered_lvcArray, $lvcArray, $deletion_existed)
    {
        
        $newInfo_flg = !empty($saveInfoArray) ? 1 : 0;
        $delPost_flg = !empty($recovered_lvcArray) ? 2 : 0;
        $expire_flg = $this->checkDelDate($lvcArray) ? 4 : 0;
//		$deletion_flg = $deletion_existed ? 8 : 0 ;
        $deletion_flg = 0; // TODO 開発中
        
        // 単純に足し算
        $lvcStatus = $newInfo_flg + $delPost_flg + $expire_flg + $deletion_flg;
        
        // debug
        var_dump($newInfo_flg, $delPost_flg, $expire_flg, $deletion_flg);
        
        return $lvcStatus;
    }
    
    /**
     * 低評価記事のどれか一つでも、猶予期限を過ぎかつstatusが1でない場合、その記事のstatusを1にして true を返す
     * @param $lvcArray
     * @return bool
     * @see CliLvcModel::calculateLvcStatus()
     */
    protected function checkDelDate($lvcArray)
    {
        
        $result = false;
        foreach ($lvcArray as $key => $info) {
            
            // 該当記事のDB情報を取得
            $db_info = $this->model->searchLowVotes($info["url"], $info["post"]);
            
            // 猶予期限のチェック
            $del_date_diff = $this->compareTimestamp(time(), strtotime($db_info["del_date"]));
            if ($del_date_diff <= 0) {
                
                // status が 1 以外の場合、猶予期限切れの通知
                if ($db_info["status"] != 1) {
                    $result = true;
                }
                
                // status を 1 にする
                $this->model->updateMulti($info['url'], $info['post'], "status", 1);
                
            } else {
                // 猶予期限を過ぎていなければ
                
                // status が 1 の場合、 0 にする ( = 猶予期限切れ後に、期限が延長された場合など )
                if ($db_info["status"] != 1) {
                    $this->model->updateMulti($info['url'], $info['post'], "status", 0);
                }
            }
        }
        return $result;
    }
    
    /**
     * タイムスタンプを比較
     * @param $timestampBfr
     * @param $timestampAft
     * @return string
     * @see checkDelDate()
     * @see CliLvcModel::sendMail()
     */
    protected function compareTimestamp($timestampBfr, $timestampAft)
    {
        // 単純に引き算
        $relative_time = $timestampAft - $timestampBfr;
        
        // ゼロ除算回避
        if ($relative_time == 0) {
            return "";
        }
        return $relative_time;
    }
    
    
    /**
     * メール送信
     * @param $saveInfoArray    array    saveData処理結果のLVC標準配列
     * @param $recovered_lvcArray    array    評価が回復した記事の一覧
     * @param $lvcArray    array    低評価記事の配列
     * @param $lvcStatus    int    LVCステータス
     * @param $yellowCardsLvcArray    array   0～-2の評価の記事
     * @param $is_debug
     */
    public function sendMail($saveInfoArray, $recovered_lvcArray, $lvcArray, $lvcStatus, $yellowCardsLvcArray, $is_debug = false)
    {
        
        // 現在時刻
        $now = date("Y-m-d H:i:s");
        
        // 夜中にめーる送ってくんなアホ
        if (!$is_debug) {
            if (intval(date("H")) < 7 && intval(date("H")) >= 2) {
                return;
            }
        }
        
        // 新着があれば | 基準抜けがアレば | 猶予期間を過ぎていたら
        $message1_title = $this->setMessageTitle($lvcStatus, $recovered_lvcArray, $lvcArray);
        
        // message1 ==========================================================================================
        //書き出し
        $message1 = <<< EOD
----------------------------
▼[通知] {$message1_title}
----------------------------

EOD;
        
        if (isset($saveInfoArray)) {
            // もし新規基準超え記事がアレば----------------------------------------
            
            // 詳細情報
            foreach ($saveInfoArray as $key => $info) {
                
                $is_notified = null;
                if ($info['is_notified']) {
                    $is_notified = "勧告状況: [勧告済み]";
                }
                
                $message1 .= <<< EOD

{$key}
{$info['url']}
vote: {$info['vote']}
comment: {$info['comment']}
投稿: {$info['post']}
基準超え: {$now}
猶予期限: {$info["del_date"]}
{$is_notified}
----------------------------

EOD;
            }
        }
        
        // message2 ==========================================================================================
        // 現在の状況
        $dbRecords = $this->convertDbRecord($lvcArray);
        $message2 = "";
        foreach ($dbRecords as $key => $info) {
            
            $msg = null;
            if ($info['is_notified']) {
                $msg = "[削除勧告済み]";
            }
            if ($this->compareTimestamp(time(), strtotime($info["del_date"])) <= 0) {
                $msg = "[猶予期限を過ぎています]";
            }
            
            $message2 .= <<< EOD

{$key}
{$info['url']}
vote: {$info['vote']}
comment: {$info['comment']}
投稿: {$info['post']}
基準超え: {$info['fall_date']}
猶予期限: {$info["del_date"]}
{$msg}
----------------------------

EOD;
        }
        
        // message3 ==========================================================================================
        // イエローカード記事
//		$message3 = "";
        foreach ($yellowCardsLvcArray as $key => $info) {

//			var_dump($key,$info);

//			$message3 .= <<< EOD
//
//{$key}
//{$info['url']}
//vote: {$info['vote']}
//comment: {$info['comment']}
//投稿: {$info['post']}
//基準超え: {$info['fall_date']}
//猶予期限: {$info["del_date"]}
//
//----------------------------
//
//EOD;
        }
        
        
        // 1 行が 70 文字を超える場合のため、wordwrap() を用いる
//		$message = wordwrap($message, 70, "\r\n");
        
        // メールクラスオブジェクト
        $mail = new Mail("/home/njr-sys/public_html/application/views/mail_templates/low_vote_checker.tpl");
        
        
        // 送信する
        $logMsg = "";
//        if (!$is_debug) {
//
//            $lvcUsers = $this->model->getAvailableLvcUsers();
//            foreach ($lvcUsers as $item) {
//
//                $mail->send($item["mail"], array(
//                    "user" => $item["name"],
//                    "now" => $now,
//                    "message1" => $message1,
//                    "message2" => $message2,
//                ));
//
//            }
//
//        } else { // debug mode

        // debug ////////////////////////////////////////
        Console::log("debug mode!", "semdMail");
        $lvcUsers = $this->model->getAvailableLvcUsers();
        foreach ($lvcUsers as $item) {

            if ($item["name"] == "ikr_4185") { // 育良だけに送信
                $mail->send($item["mail"], array(
                    "user" => $item["name"],
                    "now" => $now,
                    "message1" => $message1,
                    "message2" => $message2,
                ));
            }
        }

        $logMsg = "is_debug";

//        }
        
        // メール送信ログ
        $this->saveLogs(date("Y-m-d H:i:s") . "\t" . $message1_title . "\t" . $lvcStatus . count($saveInfoArray) . "\t" . count($dbRecords) . "\t" . $logMsg);
        
    }
    
    /**
     * @param $lvcStatus
     * @param $recovered_lvcArray
     * @param $lvcArray
     * @return string
     */
    protected function setMessageTitle($lvcStatus, $recovered_lvcArray, $lvcArray)
    {
        // 評価が回復した記事名
        $recoveredPostsNames = array();
        if ($lvcStatus == (2 || 6)) {
            foreach ($recovered_lvcArray as $key => $val) {
                $recoveredPostsNames[] = $key;
            }
            $recoveredPostsNames = implode(",", $recoveredPostsNames);
        }
        
        // 猶予期限が切れた記事名
        $expiredPostsNames = array();
        foreach ($lvcArray as $key => $val) {
            
            // レッドカード記事のみに絞る
            if ($val['is_expanding']) {
                
                // 単純に現在時刻と比較して、0以下だったら名前を格納
                $relative_time = $this->compareTimestamp(time(), strtotime($val['del_date']));
                if ($relative_time <= 0) {
                    $expiredPostsNames[] = $key;
                }
            }
        }
        if (!empty($expiredPostsNames)) {
            $expiredPostsNames = implode(",", $expiredPostsNames);
        }
        
        switch ($lvcStatus) {
            case '1':
                // 新着有り
                $message1_title = "削除基準を下回りました";
                break;
            case '2':
                // 基準抜け有り
                $message1_title = "記事の評価が回復しました({$recoveredPostsNames})";
                break;
            case '4':
                // 猶予期限切れ有り
                $message1_title = "猶予期限を超えた記事があります。確認してください。({$expiredPostsNames})";
                break;
            case '3':
                // 新着＋基準抜け
                $message1_title = "削除基準を下回りました。また、記事の評価が回復しました({$recoveredPostsNames})";
                break;
            case '5':
                // 新着+猶予期限切れ
                $message1_title = "削除基準を下回りました。また、猶予期限超え記事があります({$expiredPostsNames})";
                break;
            case '6':
                // 基準抜け+猶予期限切れ
                $message1_title = "評価回復記事({$recoveredPostsNames})、猶予期限超え記事があります({$expiredPostsNames})";
                break;
            case '7':
                // 全部
                $message1_title = "削除基準を下回りました。また、評価回復記事({$recoveredPostsNames})、猶予期限超え記事があります({$expiredPostsNames})";
                break;
//			case '8':   // 記事消滅
//				// 記事消滅
//				$message1_title = "低評価記事一覧より記事が削除されました";
//				break;
//			case '9':   // 記事消滅 + 新規(1)
//			case '10':  // 記事消滅 + 基準抜け(2)
//			case '12':  // 記事消滅 + 猶予期限切れ(4)
//			case '11':  // 記事消滅 + 新規 + 基準抜け(3)
//			case '13':  // 記事消滅 + 新規 + 猶予期限切れ(5)
//			case '14':  // 記事消滅 + 基準抜け + 猶予期限切れ(6)
//			case '15':  // 記事消滅 + 新規 + 基準抜け + 猶予期限切れ(7)
//				// 記事消滅 + 他
//				$message1_title = "低評価記事一覧より記事が削除されました";
//				break;
            case '99':
                // 新着有り
                $message1_title = "定時通知です";
                break;
            default:
                $message1_title = "";
        }
        
        return $message1_title;
        
    }
    
    /**
     * DBの記事レコードを取得、LVC標準形式配列+基準超え日時にして返す
     * @param $lvcArray
     * @return array
     */
    protected function convertDbRecord($lvcArray)
    {
        // DBの記事レコード配列を取得
        $dbRecords = $this->model->getAllLowVotes();
        
        // LVC標準形式に変換
        $records = array();
        foreach ($dbRecords as $record) {
            
            $lvcPost = $this->convertLvcDbRecord($record);
            
            // LVC標準配列に追加する
            $records = array_merge($records, $lvcPost);
            
            // 該当する低評価記事配列がない場合
            if (isset($lvcArray[$record["name"]])) {
                // TODO 暫定対応
                // エラーっぽいのでロギングする
                $msg = "{$record["name"]} not found in lvcArray.";
                $result = file_put_contents("/home/njr-sys/public_html/application/cli/logs/low_vote_log_err.log", $msg . "\n", FILE_APPEND);
                if ($result) {
                    Console::log($msg, "convertDbRecord");
                }
                
                exit;
            }
            
            // レコードの無いデータは、該当する低評価記事配列から引っ張ってくる
            $records[$record["name"]]["vote"] = $lvcArray[$record["name"]]["vote"];
            $records[$record["name"]]["comment"] = $lvcArray[$record["name"]]["comment"];
            
            // fall_date を追加する
            $records[$record["name"]]["fall_date"] = $record["fall_date"];
            
        }

//		var_dump($records);
        
        return $records;
    }
    
}