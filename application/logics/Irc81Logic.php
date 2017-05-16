<?php
namespace Logics;
use Models\IrcLog81Model;

/**
 * Class IrcLogic
 * @package Logics
 */
class Irc81Logic extends IrcLogic {
	
	/**
	 * @var IrcLog81Model
	 */
	protected $Irc = null;
	
	protected function getModel() {
		$this->Irc = IrcLog81Model::getInstance();
	}

	/**
	 * #site8181 ログ一覧
	 * @return mixed
	 */
	public function getIrcLog81Array() {
		
		$logArray = $this->Irc->getLogs();
		
		// 最大発言数
		$max = $this->getMax( $logArray );
		
		// バーの一単位あたりの発言数
		$unit = floor( $max / 100 );

		// ゼロ除算阻止
		if ($unit == 0) {
			$unit = 1;
		}
		
		foreach ($logArray as &$log) {
			$log = array_values($log);

			$log[2] = floor( intval(trim($log[1])) / $unit );
		}
		unset($log);
		
		return array_reverse($logArray);
	}

	/**
	 * #site8181 ログ
	 * @param $date
	 * @return array|string
	 */
	public function getLog81( $date )	{
		$records = $this->Irc->getLog($date);

		foreach($records as &$record){

			// 日付の短縮
			$record["datetime"] = substr($record["datetime"], -(strcspn($record["datetime"],' ') - 2 ));

			// 色設定
			$record["specialColor"] = "";
			$color = $this->getColor( $record["nick"] );
			if ( strpos($record["nick"], "KASHIMA-EXE") !== false ) {
				$color = "KASHIMA-EXE";
				$record["specialColor"] = $color;
			}
			if ( "Holy_nova" == $record["nick"] || "kasyu-maki" == $record["nick"] ) $color = "irc-color-op";
			$record["color"] = $color;

            if (strpos($record["message"], "&#x")!==false) {
                $record["message"]  = $this->utf8mb4_decode_numericentity($record["message"]) . " [system: 特殊文字が含まれています]";
            }

		}
		unset($record);

		return $records;
	}

    /**
     * 4バイト文字エスケープを元に戻す
     * @see http://qiita.com/masakielastic/items/ec483b00ff6337a02878
     * @param $str
     * @return mixed
     */
    protected function utf8mb4_decode_numericentity($str)
    {
        $re = '/&#(x[0-9a-fA-F]{5,6}|\d{5,7});/';
        return preg_replace_callback($re, function ($m) {
            return html_entity_decode($m[0]);
        }, $str);
    }
    
}