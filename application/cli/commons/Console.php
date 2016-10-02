<?php
namespace Cli\Commons;

/**
 * Class Console
 * コンソール ツールクラス
 * @package Cli\Commons
 */
class Console {
	
	/**
	 * コンソールログ出力
	 * @param $msg
	 * @param string $category
	 */
	public static function log( $msg, $category="" ) {
		
		$preMsg = "[".self::getTime()."]";
		if (!empty($category)) {
			$preMsg .= "[{$category}]";
		}
		
		echo $preMsg."\t".$msg."\n";
	}
	
	/**
	 * 実行時刻をミリ秒まで取得
	 * @return string
	 */
	private static function getTime() {
		$arrTime = explode('.',microtime(true));
		$microTime = str_pad($arrTime[1], 4, 0, STR_PAD_RIGHT);
		return date('Y-m-d H:i:s', $arrTime[0]) . '.' .$microTime;
	}
	
}