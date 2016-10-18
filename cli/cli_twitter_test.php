<?php
// OAuthスクリプトの読み込み
require_once('twitteroauth/twitteroauth.php');

// Consumer key
$consumer_key = "XXXXXXXXXXXXXXXXXXXXX";
// Consumer secret
$consumer_secret = "YYYYYYYYYYYYYYYYYYYYYYYYYYYY";
// Access token
$access_token = "ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ";
// Access token secret
$access_token_secret = "VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV";

// ファイルの行をランダムに抽出
$filelist = file('list.txt');
if( shuffle($filelist) ){
	$message = $filelist[0];
}

// つぶやく
$connection = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);
$req = $connection->OAuthRequest("https://api.twitter.com/1.1/statuses/update.json","POST",array("status"=> $message ));