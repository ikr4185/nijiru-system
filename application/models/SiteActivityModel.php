<?php
namespace Models;
use Models\Commons\AbstractModel;

class SiteActivityModel extends AbstractModel
{

	/**
	 * 情報の保存
	 * @param $name
	 * @param $type
	 * @param $recent_date
	 * @return bool
	 */
	public function insert( $name, $type, $recent_date ) {
		$sql = 'insert into site_activity(name, type, recent_date) values ( ?, ?, ? )';
		$stmt = $this->pdo->prepare($sql);

		return $stmt->execute( array( $name, $type, $recent_date ) );
	}

	/**
	 * 情報の更新
	 * @param $name
	 * @param $recent_date
	 * @return array|string
	 */
	public function update( $name, $recent_date ) {
		return  $this->execUpdate(
			'UPDATE site_activity
SET recent_date = ?, 
WHERE name = ?',
			array( $recent_date, $name )
		);
	}
	
	/**
	 * DB上、最新のレコードを取得
	 * @return array|string
	 */
	public function getRecent() {
		return $this->execSql( 'SELECT * FROM site_activity WHERE 1 ORDER BY recent_date DESC LIMIT 1', array(), true );
	}
	
	/**
	 * サイトメンバーの、直近の活動時刻を取得
	 * @param $name
	 * @return array|string
	 */
	public function getRecentByName( $name ) {
		return $this->execSql( 'SELECT * FROM site_activity WHERE name = ? ORDER BY recent_date DESC LIMIT 1', array($name), true );
	}
	
	
	/**
	 * 最近活動のあったサイトメンバー
	 * @param $day
	 * @return array|string
	 */
	public function getRecentDate( $day ) {
		if (!is_numeric($day)) {
			return false;
		}
		return $this->execSql( 'SELECT * 
FROM (SELECT * 
FROM site_activity
WHERE recent_date >= DATE_ADD( NOW( ) , INTERVAL -'.$day.'
DAY ) 
ORDER BY recent_date DESC
) AS recent
GROUP BY name', array(), true );
	}


	/**
	 * 月間のアクティブメンバーを取得
	 * @param $date
	 * @return array|string
	 */
	public function getRecentInDateRange( $date ) {
		$date = $date."%";
		return $this->execSql( 'SELECT * 
FROM (SELECT * 
FROM site_activity
WHERE recent_date like (?)
ORDER BY recent_date DESC
) AS recent
GROUP BY name', array($date), true );
	}


	/**
	 * 情報の取得
	 * @param $name
	 * @return array|string
	 */
	public function get( $name ) {
		return $this->execSql( 'SELECT * FROM site_activity WHERE name = ?', array($name), true );
	}

	/**
	 * 情報の全取得
	 * @return array|string
	 */
	public function getAll() {
		return $this->execSql( 'SELECT * FROM site_members WHERE 1 AND del_flg = 0', array(), true );
	}

//	/**
//	 * ソフトデリート操作
//	 * @param $del_flg  int
//	 * @param $id int
//	 * @return bool
//	 */
//	public function setSoftDelete( $del_flg, $id )
//	{
//		$sql = 'UPDATE site_members SET del_flg = ? WHERE id = ?';
//		$stmt = $this->pdo->prepare($sql);
//		$flag = $stmt->execute(array( $del_flg, $id ));
//
//		if (!$flag){
//			return false;
//		}
//		return true;
//	}
}