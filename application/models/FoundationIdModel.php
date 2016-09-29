<?php
namespace Models;
use Models\Commons\AbstractModel;

class FoundationIdModel extends AbstractModel
{
	
	
	/**
	 * ユーザーのIDgenデータが存在するかチェックする
	 * @param $id
	 * @return array|string
	 */
	public function checkIdgenData($id)
	{
		return $this->execSql( 'SELECT a.foundation_number
FROM foundation_id as a
JOIN users as b
ON b.number = a.users_number
WHERE b.id = ?', array($id) );
	}
	
	/**
	 * 新規追加
	 * @param $number
	 * @param $name
	 * @param $staff
	 * @param $idnum
	 * @param $scl
	 * @param $duty
	 * @param $locate
	 * @param $date
	 * @return bool|string
	 */
	public function insertRecord( $number, $name, $staff, $idnum, $scl, $duty, $locate, $date ) {
		
		// インサート
		$sql = 'insert into foundation_id( users_number, name, staff, idnum, scl, duty, locate, modified_date ) values (?, ?, ?, ?, ?, ?, ?, ?)';
		$stmt = $this->pdo->prepare($sql);
		
		return $stmt->execute(array( $number, $name, $staff, $idnum, $scl, $duty, $locate, $date ));
	}
	
	/**
	 * 既存レコード更新
	 * @param $name
	 * @param $staff
	 * @param $idnum
	 * @param $scl
	 * @param $duty
	 * @param $locate
	 * @param $date
	 * @param $id
	 * @return bool|string
	 */
	public function updateRecord( $name, $staff, $idnum, $scl, $duty, $locate, $date, $id ) {
		
		return $this->execUpdate('UPDATE foundation_id as a
LEFT JOIN users as b
ON b.number = a.users_number
SET name=?, staff=?, idnum=?, scl=?, duty=?, locate=?, modified_date=?
WHERE id = ?',
			array($name, $staff, $idnum, $scl, $duty, $locate, $date, $id)
		);
	}


	/**
	 * ユーザーのIdgen情報を取得する
	 * @param $id
	 * @return mixed
	 */
	public function getIdgenData($id)
	{

		return $this->execSql( 'SELECT a.name, a.staff, a.idnum, a.scl, a.duty, a.locate
FROM foundation_id as a
JOIN users as b
ON b.number = a.users_number
WHERE b.id = ?', array($id) );
	}

}