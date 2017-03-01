<?php
/**
 * php /home/njr-sys/public_html/cli/test.php
 */

//require_once "/home/njr-sys/public_html/class/common/MailModel.php";
//
//// メールクラスオブジェクト
//$mail = new MailModel("/home/njr-sys/public_html/template/mail/test.tpl");
//
//$now = date("Y-m-d H:i:s");
//
//
//// 送信する
//$mail->send('ikr.4185@gmail.com', array(
//	"user" => "育良 啓一郎",
//	"now" => $now,
//	"message1" => "test",
//));

//// 常駐させるテスト
//$count=0;
//while (true) {
//
//	sleep(1);
//	echo $count;
//
//	$count++;
//
//}
//exit;

function curl($url, $byte = null)
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

function talejp($irc, $data)
{
    preg_match('/^(\.TALEJP-)(.*)$/iu', $data->message, $match);
    // 再試行
    if (empty($match)) {
        preg_match('/^(\.TALEJP )(.*)$/iu', $data->message, $match);
    }
    if (empty($match)) {
//        $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 認識に失敗しました');
        echo '[ERROR] 認識に失敗しました';
        return;
    }

    $title = $match[2];
    $url = "http://ja.scp-wiki.net/system:page-tags/tag/tale-jp";
//    $html = $this->curl($url);
    $html = curl($url);
    if (!$html) {
//        $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 接続に失敗しました');
        echo '[ERROR] 接続に失敗しました';
    }

    $pattern = '@<div class="title">\s*?<a href="/(.*?)">('.$title.')</a>@u';
    preg_match($pattern, $html, $matches);

    unset($html); // メモリ節約

    $pageUrl = trim($matches[1]);
    $title = htmlspecialchars_decode(trim($matches[2]));

    $msg = "ikr_4185: [TALE-JP] \"{$title}\" http://ja.scp-wiki.net/{$pageUrl}";

    // 発言
//    $this->botMsg( $irc, $data, $msg );
    echo $msg;
}

$data = new stdClass();
$data->message = ".talejp あいのさめのゆめ";

talejp(null,$data);
exit;
