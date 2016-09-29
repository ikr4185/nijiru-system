<?php
namespace Models;
use Models\Commons\AbstractModel;

class SiteMembersModel extends AbstractModel
{

	/**
	 * 情報の保存
	 * @param $name
	 * @param $wikidot_id
	 * @param $since
	 * @return bool
	 */
	public function insert( $name, $wikidot_id, $since ) {
		$sql = 'insert into site_members(name, wikidot_id, since) values ( ?, ?, ? )';
		$stmt = $this->pdo->prepare($sql);

		return $stmt->execute( array( $name, $wikidot_id, $since ) );
	}
	
//	/**
//	 * 情報の更新
//	 * @param $name
//	 * @param $wikidot_id
//	 * @param $since
//	 * @return bool
//	 */
//	public function update( $name, $wikidot_id, $since ) {
//		return  $this->execUpdate(
//			'UPDATE site_members
//SET name = ?, 
//WHERE users_number = ?
//AND item_number = ?',
//			array( $name, $wikidot_id, $since )
//		);
//	}
	
	
	/**
	 * 同名のメンバー情報がないかチェック
	 * @param $name
	 * @return array|string
	 */
	public function check($name) {
		$result = $this->execSql( 'SELECT * FROM site_members WHERE name = ? AND del_flg = 0', array($name), true );
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * 情報の取得
	 * @param $id
	 * @return array|string
	 */
	public function get($id) {
		return $this->execSql( 'SELECT * FROM site_members WHERE id = ? AND del_flg = 0', array($id), true );
	}

	/**
	 * 情報の全取得
	 * @return array|string
	 */
	public function getAll() {
		return $this->execSql( 'SELECT * FROM site_members WHERE 1 AND del_flg = 0', array(), true );
	}

	/**
	 * ソフトデリート操作
	 * @param $del_flg  int
	 * @param $id int
	 * @return bool
	 */
	public function setSoftDelete( $del_flg, $id )
	{
		$sql = 'UPDATE site_members SET del_flg = ? WHERE id = ?';
		$stmt = $this->pdo->prepare($sql);
		$flag = $stmt->execute(array( $del_flg, $id ));

		if (!$flag){
			return false;
		}
		return true;
	}
	/**
	 * 最新メンバーを取得
	 * @return array|string
	 */
	public function getNewestMember() {
		return $this->execSql( 'SELECT * FROM site_members WHERE del_flg = 0 ORDER BY since DESC LIMIT 1;', array(), true );
	}
	
	/**
	 * 特定期間内の新人職員の取得
	 * @param $date
	 * @return array|string
	 */
	public function getNewbiesInDateRange($date) {
		$date = $date."%";
		return $this->execSql( 'SELECT * FROM site_members WHERE del_flg = 0 AND since like (?);', array($date), true );
	}
}