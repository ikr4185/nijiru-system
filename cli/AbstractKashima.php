<?php

require_once "/home/njr-sys/public_html/application/_cores/config/Config.php";

abstract class AbstractKashima
{
    /**
     * curl
     * @param $url
     * @param null $byte
     * @return mixed
     */
    protected function curl($url, $byte = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Nijiru System - SCP_Foundation");
        curl_setopt($ch, CURLOPT_REFERER, "http://ja.scp-wiki.net/");
        if (!empty($byte)) {// 取得Byte数制限
            curl_setopt($ch, CURLOPT_RANGE, $byte);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    /**
     * 保存処理の実体
     * 実装は継承先クラスで定義
     * @param $nick
     * @param $message
     * @return mixed
     */
    abstract protected function saveLog($nick, $message);
    
    /**
     * ログ保存
     * @param Kashima_Core $irc
     * @param $data
     */
    function getlog($irc, $data)
    {
        $this->saveLog($data->nick, $data->message);
    }
    
    /**
     * メッセージ送信
     * @param Kashima_Core $irc
     * @param $data
     * @param $msg
     * @return bool
     */
    protected function sendMsg($irc, $data, $msg)
    {
        $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, $msg);
        $this->saveLog(IRC_NAME, $msg);
        return true;
    }
    
    /**
     * メッセージ文の生成
     * @param $nick
     * @param $category
     * @param $title
     * @param $url
     * @return string
     */
    protected function createMsg($nick, $category, $title, $url)
    {
        $title = trim(strip_tags($title));
        return "{$nick}: [{$category}] {$title} {$url}";
    }
    
    /**
     * エラーメッセージ送信
     * @param Kashima_Core $irc
     * @param $data
     * @param $errorNum
     * @return bool
     */
    protected function sendError($irc, $data, $errorNum)
    {
        $text = '不明なエラー';
        $sub = '';
        
        if ($errorNum === 0) {
            $text = 'コマンドの形式が間違っています';
            $sub = ": {$data->message}";
        }
        
        if ($errorNum === 1) {
            $text = 'データ取得に失敗しました';
            $sub = ": {$data->message}";
        }
        
        if ($errorNum === 2) {
            $text = '記事が見つかりませんでした';
            $sub = ": {$data->message}";
        }
        
        if ($errorNum === 3) {
            $text = '不正なコマンド';
            $sub = ": {$data->message}";
        }
        
        if ($errorNum === 666) {
            $text = '致命的なエラー';
            $sub = ": {$data->message}";
        }
        
        $msg = $this->createMsg($data->nick, "ERROR", $text, $sub);
        return $this->sendMsg($irc, $data, $msg);
    }
    
    /**
     * 針山博士の名前を間違える
     * @return mixed
     */
    protected function getHarryName()
    {
        $names = array(
            "貼山",
            "晴山",
            "春山",
            "梁山",
            "花山",
            "原山",
            "イエローストーン博士",
            "ハリー",
            "ニド",
            "板東英二",
            "堂本光一",
            "なんとか山",
            "音MAD素材博士",
            "名前を呼んではいけないあの人",
            "スレンディ",
            "字が下手",
            "毛虫ばらまきマン",
            "毛虫",
            "毛",
            "葉加瀬博士",
            "ハンス=ウルリッヒ=ルーデル博士",
        );
        $rand = mt_rand(0, count($names) - 1);
        return $names[$rand];
    }
    
    /**
     * 入室
     * @param Kashima_Core $irc
     * @param $data
     */
    function welcome($irc, $data)
    {
        if (IRC_NAME == $data->nick) {
            $msg = IRC_NAME . ' 起動しました';
        } elseif ("hal-aki" == $data->nick || "hal_aki" == $data->nick) {
            $msg = $this->getHarryName() . 'さんが入室しました';
        } else {
            $msg = $data->nick . 'さんが入室しました';
        }
        
        $this->sendMsg($irc, $data, $msg);
    }
    
    /**
     * 退室
     * @param Kashima_Core $irc
     * @param $data
     */
    function bye($irc, $data)
    {
        if ("hal-aki" == $data->nick || "hal_aki" == $data->nick) {
            $msg = $this->getHarryName() . 'さんが退室しました';
        } else {
            $msg = $data->nick . 'さんが退室しました';
        }
        
        $this->sendMsg($irc, $data, $msg);
    }
    
    /**
     * こんにちは
     * @param Kashima_Core $irc
     * @param $data
     */
    function hello($irc, $data)
    {
        $this->sendMsg($irc, $data, "こんにちは");
    }
    
    /**
     * ping-pong
     * @param Kashima_Core $irc
     * @param $data
     */
    function pong($irc, $data)
    {
        $this->sendMsg($irc, $data, 'ぽん');
    }
    
    /**
     * コマンドのバリデーション・値取得
     * @param $message
     * @param $patterns
     * @return bool
     */
    protected function validateCommand($message, $patterns)
    {
        if (is_string($patterns)) {
            $patterns = array($patterns);
        }
        foreach ($patterns as $pattern) {
            preg_match($pattern, $message, $match);
            
            if (!empty($match)) {
                return $match;
            }
        }
        
        return false;
    }
    
    /**
     * SCP一覧のURLを返す
     * @param $num
     * @param bool $branchPrefix
     * @return string
     */
    protected function getScpSeriesUrl($num, $branchPrefix = false)
    {
        if ($branchPrefix == "jp") {
            if ($num >= 1000) {
                $url = "http://ja.scp-wiki.net/scp-series-jp-2";
            } else {
                $url = "http://ja.scp-wiki.net/scp-series-jp";
            }
            return $url;
        }
        
        if ($num >= 3000) {
            $url = "http://ja.scp-wiki.net/scp-series-4";
        } elseif ($num >= 2000 && $num < 3000) {
            $url = "http://ja.scp-wiki.net/scp-series-3";
        } elseif ($num >= 1000 && $num < 2000) {
            $url = "http://ja.scp-wiki.net/scp-series-2";
        } else {
            $url = "http://ja.scp-wiki.net/scp-series";
        }
        
        return $url;
    }
    
    /**
     * scp index
     * @param $nick
     * @param $title
     * @param $num
     * @param string $branchPrefix
     * @return string
     */
    protected function createMsgByScp($nick, $title, $num, $branchPrefix = "")
    {
        $category = "SCP";
        $itemName = "SCP-{$num}";
        $title = trim(strip_tags($title));
        $url = "http://ja.scp-wiki.net/scp-{$num}";
        
        if (!empty($branchPrefix)) {
            $category .= "-" . strtoupper($branchPrefix);
            $itemName .= "-" . strtoupper($branchPrefix);
            $url .= "-" . strtolower($branchPrefix);
        }
        
        return $this->createMsg($nick, $category, "{$itemName} \"{$title}\"", $url);
    }
    
    /**
     * wikidotのSCP記事一覧から該当記事を探し、メッセージを送信する
     * @param $irc
     * @param $data
     * @param $num
     * @param string $branchPrefix
     * @return bool
     */
    protected function sendMsgByScp($irc, $data, $num, $branchPrefix = "")
    {
        // SCP記事一覧スクレイピング
        $url = $this->getScpSeriesUrl($num, $branchPrefix);
        $html = $this->curl($url);
        if (!$html) {
            $this->sendError($irc, $data, 1);
            return false;
        }
        
        // 該当行のマッチ
        $pattern = "@(<li><a href=\"/scp-{$num}\">(.*?)SCP-{$num}</a> - )(.*?)(</li>)@iu";
        if (!empty($branchPrefix)) {
            $pattern = "@(<li><a href=\"/scp-{$num}-{$branchPrefix}\">(.*?)SCP-{$num}-" . strtoupper($branchPrefix) . "</a> - )(.*?)(</li>)@iu";
        }
        preg_match($pattern, $html, $matches);
        
        unset($html); // メモリ節約
        
        if (!isset($matches[1])) {
            $this->sendError($irc, $data, 2);
            return false;
        }
        
        $msg = $this->createMsgByScp($data->nick, $matches[3], $num, $branchPrefix);
        return $this->sendMsg($irc, $data, $msg);
    }
    
    /**
     * SCP記事出力
     * @param Kashima_Core $irc
     * @param $data
     */
    function scp($irc, $data)
    {
        $match = $this->validateCommand($data->message, array(
            '/^(\.scp-)(\d*)$/i',
            '/^(\.scp )(\d*)$/i',
        ));
        if (empty($match)) {
            $this->sendError($irc, $data, 0);
            return;
        }
        
        $num = $match[2];
        unset($match); // メモリ節約
        
        // SCP記事一覧スクレイピング
        $this->sendMsgByScp($irc, $data, $num);
    }
    
    /**
     * SCP-JP記事出力
     * @param Kashima_Core $irc
     * @param $data
     */
    function scpjp($irc, $data)
    {
        $match = $this->validateCommand($data->message, array(
            '/^(\.scpjp-)(\d*)$/i',
            '/^(\.scpjp )(\d*)$/i',
        ));
        if (empty($match)) {
            $this->sendError($irc, $data, 0);
            return;
        }
        
        $num = $match[2];
        unset($match); // メモリ節約
        
        // SCP記事一覧スクレイピング
        $this->sendMsgByScp($irc, $data, $num, "jp");
    }
    
    /**
     * タグ検索一覧から該当タイトルを探し、メッセージを送信する
     * @param Kashima_Core $irc
     * @param $data
     * @param $title
     * @param $tag
     * @return bool
     */
    protected function sendMsgByTag($irc, $data, $title, $tag)
    {
        $category = strtoupper($tag);
        $url = "http://ja.scp-wiki.net/system:page-tags/tag/{$tag}";
        $html = $this->curl($url);
        if (!$html) {
            $this->sendError($irc, $data, 1);
            return false;
        }
        
        $pattern = '@<div class="title">\s*?<a href="/(.*?)">(' . $title . ')</a>@u';
        preg_match($pattern, $html, $matches);
        unset($html); // メモリ節約
        
        if (!isset($matches[1])) {
            $this->sendError($irc, $data, 2);
            return false;
        }
        
        $pageUrl = trim($matches[1]);
        $msg = $this->createMsg($data->nick, $category, $title, "http://ja.scp-wiki.net/{$pageUrl}");
        return $this->sendMsg($irc, $data, $msg);
    }
    
    /**
     * tale検索
     * @param Kashima_Core $irc
     * @param $data
     */
    function tale($irc, $data)
    {
        $match = $this->validateCommand($data->message, array(
            '/^(\.tale-)(.*)$/iu',
            '/^(\.tale )(.*)$/iu',
        ));
        if (empty($match)) {
            $this->sendError($irc, $data, 0);
            return;
        }
        
        $this->sendMsgByTag($irc, $data, $match[2], "tale");
    }
    
    /**
     * tale-jp 検索
     * @param Kashima_Core $irc
     * @param $data
     */
    function talejp($irc, $data)
    {
        $match = $this->validateCommand($data->message, array(
            '/^(\.talejp-)(.*)$/iu',
            '/^(\.talejp )(.*)$/iu',
        ));
        if (empty($match)) {
            $this->sendError($irc, $data, 0);
            return;
        }
        
        $this->sendMsgByTag($irc, $data, $match[2], "tale-jp");
    }
    
    /**
     * Wikidot内URL出力
     * @param Kashima_Core $irc
     * @param $data
     */
    function wiki($irc, $data)
    {
        $match = $this->validateCommand($data->message, '/^(\.wiki )(.*)$/i');
        if (empty($match)) {
            $this->sendError($irc, $data, 0);
            return;
        }
        
        $page = $match[2];
        unset($match); // メモリ節約
        
        // スクレイピング
        $url = "http://ja.scp-wiki.net/{$page}";
        $html = $this->curl($url, "0-1000");
        if (!$html) {
            $this->sendError($irc, $data, 1);
            return;
        }
        
        preg_match('/(<title>)(.*?)(<\/title>)/iu', $html, $matches);
        unset($html); // メモリ節約
        if (!isset($matches[2])) {
            $this->sendError($irc, $data, 2);
            return;
        }
        
        // タイトルの取得
        $title = str_replace(' - SCP財団', '', $matches[2]);
        unset($matches); // メモリ節約
        
        $msg = $this->createMsg($data->nick, "WIKI", $title, "{$url}");
        $this->sendMsg($irc, $data, $msg);
    }

    /**
     * 予約ページリンク
     * @param $irc
     * @param $data
     */
    function draft($irc, $data)
    {
        $match = $this->validateCommand($data->message, array(
            '/^(\.draft)$/iu',
            '/^(\.draft )(.*?)$/iu',
        ));
        if (empty($match)) {
            $this->sendError($irc, $data, 0);
            return;
        }
        
        $link = "http://njr-sys.net/irc/draftReserve/";
        $dateStr = date("Y-m-d");
        $dateStatus = "Today";
        
        if (isset($match[2])) {
            if (!strtotime($match[2])) {
                $this->sendError($irc, $data, 0);
                return;
            }
            $dateStr = date("Y-m-d", strtotime($match[2]));
            $dateStatus = "Custom";
        }
        
        $link .= $dateStr;
        unset($match); // メモリ節約
        
        $msg = $this->createMsg($data->nick, "DRAFT", $dateStatus, $link);
        $this->sendMsg($irc, $data, $msg);
    }

    /**
     * 予約状況確認
     * @param $irc
     * @param $data
     */
    function draftstatus($irc, $data){
        $match = $this->validateCommand($data->message, array(
            '/^(\.draft-status)$/iu',
            '/^(\.draft-status )(.*?)$/iu',
        ));
        if (empty($match)) {
            $this->sendError($irc, $data, 0);
            return;
        }

        $dateStr = date("Y-m-d");
        $dateStatus = "Today";

        if (isset($match[2])) {
            if (!strtotime($match[2])) {
                $this->sendError($irc, $data, 0);
                return;
            }
            $dateStr = date("Y-m-d", strtotime($match[2]));
            $dateStatus = "";
        }
        unset($match); // メモリ節約

        $drafts = $this->getDraftReserve($dateStr);
        if (empty($drafts)) {
            $this->sendError($irc, $data, 1);
            return;
        }
        $draftsStr = "";
        foreach ($drafts as $draft) {
            $time = date("H:i:s", strtotime(str_replace($dateStr, "", $draft[0])));

//            $draftsStr .= "{$time} {$draft[1]} " . trim($draft[3]) . " / ";
            $draftsStr .= "{$time} {$draft[1]} > ";
        }

        $msg = $this->createMsg($data->nick, "DRAFT", $dateStatus, $draftsStr);
        $this->sendMsg($irc, $data, $msg);
    }

    /**
     * 下書き批評予約の読み込み
     * @param $date
     * @return array
     */
    private function getDraftReserve($date)
    {
        $dirName = "/home/njr-sys/public_html/logs/irc/draft_reserve/";
        $fileName = $dirName . $date . ".log";

        $logs = array();

        // ファイル読み込み
        $fp = @fopen($fileName, 'r');

        // ファイルが存在しない場合
        if (!$fp) {
            return $logs;
        }

        if (flock($fp, LOCK_SH)) {

            while (!feof($fp)) {

                // 一行ずつ読み込んで$logsに格納
                $buffer = fgets($fp);

                if (empty($buffer)) {
                    break;
                }

                $buffer = str_replace("@@,@@", ",", $buffer);
                $buffer = htmlspecialchars($buffer);

                $logs[] = explode(",", $buffer);
            }
            flock($fp, LOCK_UN);

        } else {
            return false;
        }

        fclose($fp);

        return $logs;
    }
    
    /**
     * サンドボックス出力
     * @param Kashima_Core $irc
     * @param $data
     */
    function sandbox($irc, $data)
    {
        $match = $this->validateCommand($data->message, array(
            '/^(\.sb )(.*)$/i',
            '/^(\.sandbox )(.*)$/i',
        ));
        if (empty($match)) {
            $this->sendError($irc, $data, 0);
            return;
        }
        
        $user = $match[2];
        $user = str_replace("_", "-", $user);
        unset($match); // メモリ節約
        
        // スクレイピング
        $url = "http://scp-jp-sandbox2.wikidot.com/{$user}/";
        $html = $this->curl($url, "27000-36000");
        if (!$html) {
            $this->sendError($irc, $data, 1);
            return;
        }
        
        preg_match('@(<div id="page-title">)([\s|\S]*?)(</div>)@iu', $html, $matches);
        if (!isset($matches[2])) {
            $this->sendError($irc, $data, 2);
            return;
        }
        unset($html); // メモリ節約
        
        // タイトルの取得
        $title = $matches[2];
        $title = preg_replace('/^[ 　]+/u', '', $title);
        $title = preg_replace('/[ 　]+$/u', '', $title);
        $title = trim($title);
        unset($matches); // メモリ節約
        
        $msg = $this->createMsg($data->nick, "SANDBOX / {$user}", $title, "{$url}");
        $this->sendMsg($irc, $data, $msg);
    }

    /**
     * サンドボックス3出力
     * @param Kashima_Core $irc
     * @param $data
     */
    function sandbox3($irc, $data)
    {
        $match = $this->validateCommand($data->message, array(
            '/^(\.sb3 )(.*)$/i',
            '/^(\.sandbox3 )(.*)$/i',
        ));
        if (empty($match)) {
            $this->sendError($irc, $data, 0);
            return;
        }

        $page = $match[2];
        $page = str_replace("_", "-", $page);
        unset($match); // メモリ節約

        // スクレイピング
        $url = "http://scp-jp-sandbox3.wikidot.com/{$page}/";
        $html = $this->curl($url, "0-10000");
        if (!$html) {
            $this->sendError($irc, $data, 1);
            return;
        }

        preg_match('@(<title>)([\s|\S]*?)( - SCP-JPサンドボックスⅢ</title>)@iu', $html, $matches);
        if (!isset($matches[2])) {
            $this->sendError($irc, $data, 2);
            return;
        }
        unset($html); // メモリ節約

        // タイトルの取得
        $title = $matches[2];
        $title = preg_replace('/^[ 　]+/u', '', $title);
        $title = preg_replace('/[ 　]+$/u', '', $title);
        $title = trim($title);
        unset($matches); // メモリ節約

        $msg = $this->createMsg($data->nick, "SANDBOX3 / {$page}", $title, "{$url}");
        $this->sendMsg($irc, $data, $msg);
    }
    
    /**
     * 強制終了
     * @param Kashima_Core $irc
     * @param $data
     */
    function quit($irc, $data)
    {
        $pass = file_get_contents(\Cores\Config\Config::load("dir.cli") . "/logs/KASHIMA_quit.log");
        
        preg_match('/^(\.quit )(.*)$/i', $data->message, $match);
        
        if ($pass == $match[2]) {
            
            file_put_contents(\Cores\Config\Config::load("dir.cli") . "/logs/KASHIMA_quit.log", $this->random(8));
            $msg = '強制終了します';
            $irc->quit($msg);
            
        } else {
            $this->sendError($irc, $data, 3);
        }
    }
    
    /**
     * @param int $length
     * @return string
     */
    protected function random($length = 8)
    {
        return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
    }
    
    /**
     * かしまちゃんかわいい
     * @param $irc
     * @param $data
     */
    function dere($irc, $data)
    {
        $a = array("あ", "う", "い", "び", "お", "ひゃ", "ほ", "わ");
        $b = array("う", "い", "お", "ひゃ", "ほ", "わ");
        $c = array("ゃ", "ゅ", "ょ", "ぁ", "ぃ", "ぅ", "ぇ", "ぉ", "い", "び", "お", "ひゃ", "ほ", "わ", "");
        
        $msg = $a[array_rand($a)];
        $msg .= $b[array_rand($b)];
        $msg .= $c[array_rand($c)];
        
        $this->sendMsg($irc, $data, $msg);
    }
}