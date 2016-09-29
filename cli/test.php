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

// 常駐させるテスト
$count=0;
while (true) {

	sleep(1);
	echo $count;

	$count++;

}
exit;