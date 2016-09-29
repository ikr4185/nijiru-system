<?php
namespace Models;
use Models\Commons\AbstractModel;

class IrcLog81Model extends AbstractModel
{
	
	/**
	 * ログを取得
	 * @param $date
	 * @return array|string
	 */
	public function getLog($date) {

		$before = $date." 00:00:00";
		$after = $date." 23:59:59";

		return $this->execSql( 'SELECT * FROM irc_log_81 WHERE datetime BETWEEN ? AND ?', array($before,$after), true );
	}
	
	/**
	 * 全ログリストを取得
	 * @return array|string
	 * TODO 秋口にはスロークエリ対策が必要になるはず
	 */
	public function getLogs() {
		return $this->execSql( 'SELECT date as "0", COUNT( * ) as "1" FROM irc_log_81 WHERE 1 GROUP BY date ORDER BY date', array(), true );
	}
	
}