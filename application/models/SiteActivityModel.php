<?php
namespace Models;
use Models\Commons\AbstractModel;

class SiteActivityModel extends AbstractModel
{

	/**
	 * 情報の保存
	 * @param $name
	 * @param $recent_date
	 * @return bool
	 */
	public function insert( $name, $recent_date ) {
		$sql = 'insert into site_activity(name, recent_date) values ( ?, ? )';
		$stmt = $this->pdo->prepare($sql);

		return $stmt->execute( array( $name, $recent_date ) );
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