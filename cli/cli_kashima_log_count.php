<?php
/**
 * カシマちゃんのその日取ったログの総数を取得、irc-logs_sizes.datに保存
 * 毎晩0:01にCronで実行
 */

switch($argc)
{
	case 1:
		// 引数が与えられていないとき
		$date =  date('Y-m-d', strtotime('-1 day')); // 昨日の日付;
		break;
	default:
		// 正常に引数が与えられたとき
		$date = $argv[1];
		break;
}


// コマンドでやってしまおうな
//if ( intval( strtotime($date) ) >= intval( strtotime("2016-03-28") )  ) {
//	$count = exec( "wc -l /home/njr-sys/public_html/cli/logs/irc/irc-logs_{$date}.dat | awk '{print $1}'" );
//}else{
//	$count = exec( "wc -l /home/njr-sys/public_html/cli/logs/irc_old/irc-logs_{$date}.log | awk '{print $1}'" );
//}
$count = exec( "wc -l /home/njr-sys/public_html/cli/logs/irc/irc-logs_{$date}.dat | awk '{print $1}'" );

$file_name = "/home/njr-sys/public_html/cli/logs/irc/irc-logs_sizes.dat";

file_put_contents($file_name,$date."\t".$count."\n",FILE_APPEND);
