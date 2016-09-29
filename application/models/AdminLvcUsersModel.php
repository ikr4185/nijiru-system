<?php
namespace Models;
use Models\Commons\AbstractModel;

class AdminLvcUsersModel extends AbstractModel
{

	/**
	 * 全レコードの取得
	 * @return mixed
	 */
	public function getAll() {

		$result = $this->execSql( 'SELECT *
FROM admin_lvc_users
WHERE 1',
			array(),
			true
		);
		return $result;
	}

	/**
	 * 有効なレコードの取得
	 * @return mixed
	 */
	public function getAvailable() {

		$result = $this->execSql( 'SELECT *
FROM admin_lvc_users
WHERE is_available = 1',
			array(),
			true
		);
		return $result;
	}

	/**
	 * 情報をidから取得する
	 * @param $id
	 * @return mixed
	 */
	public function searchRecordById($id) {

		$result = $this->execSql( 'SELECT *
FROM admin_lvc_users
WHERE id = ?',
			array($id)
		);
		return $result;
	}
	
	/**
	 * 情報をmailから取得する
	 * @param $mail
	 * @return mixed
	 */
	public function searchRecordByMail($mail) {
		
		$result = $this->execSql( 'SELECT *
FROM admin_lvc_users
WHERE mail= ?',
			array($mail)
		);
		return $result;
	}


	/**
	 * 新規登録
	 * @param $name
	 * @param $mail
	 * @return bool
	 */
	public function register($name,$mail){

		$sql = 'insert into admin_lvc_users(name,mail,is_available) values (?, ?, 1)';
		$stmt = $this->pdo->prepare($sql);

		return $stmt->execute(array($name,$mail));
	}

	/**
	 * ソフトデリート
	 * @param $id
	 * @param int $is_available default:0(デリートする)
	 * @return array|string
	 */
	public function softDelete( $id, $is_available=0 ) {
		return $this->execUpdate( 'UPDATE admin_lvc_users SET is_available = ? WHERE id = ?', array($is_available, $id) );
	}

}