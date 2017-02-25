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
     * @param bool $eol
     */
	public static function log( $msg, $category="", $eol=true ) {
		
		$preMsg = "[".self::getTime()."]";
		if (!empty($category)) {
			$preMsg .= "[{$category}]";
		}
        
        if ($eol) {
            $msg .= "\n";
        }

		echo $preMsg." ".$msg;
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

	/**
	 * エラーログの出力
	 * @param $msg
	 */
	public static function errorLog( $msg ) {
		$preMsg = "[".self::getTime()."]";
		file_put_contents("/home/njr-sys/public_html/error.log",$preMsg.$msg,FILE_APPEND);
	}
}