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

		}
		unset($record);

		return $records;
	}
	
}