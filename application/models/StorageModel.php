<?php
namespace Models;
use Models\Commons\AbstractModel;

class StorageModel extends AbstractModel
{

	/**
	 * ファイル情報の保存
	 * @param $file_name
	 * @param $file_path
	 * @param $user_id
	 * @param $credit
	 * @param $size
	 * @return bool
	 */
	public function saveFileInfo( $file_name, $file_path, $user_id, $credit, $size ) {
		$sql = 'insert into storage(file_name, file_path, user_id, credit, size) values ( ?, ?, ?, ?, ? )';
		$stmt = $this->pdo->prepare($sql);

		return $stmt->execute( array( $file_name, $file_path, $user_id, $credit, $size ) );
	}
	
	/**
	 * 同名のファイルがないかチェック
	 * @param $file_name
	 * @return array|string
	 */
	public function checkFileInfo($file_name) {
		$result = $this->execSql( 'SELECT * FROM storage WHERE file_name = ? AND del_flg = 0', array($file_name), true );
		if (!$result) {
			return false;
		}		
		return true;
	}
	
	/**
	 * ファイル情報の取得
	 * @param $id
	 * @return array|string
	 */
	public function getFileInfo($id) {
		return $this->execSql( 'SELECT * FROM storage WHERE id = ? AND del_flg = 0', array($id), true );
	}
	
	/**
	 * ファイル情報の全取得
	 * @return array|string
	 */
	public function getFileInfoAll() {
		return $this->execSql( 'SELECT * FROM storage WHERE 1 AND del_flg = 0', array(), true );
	}
	
	/**
	 * ソフトデリート操作
	 * @param $del_flg  int
	 * @param $id int
	 * @return bool
	 */
	public function setSoftDelete( $del_flg, $id )
	{
		$sql = 'UPDATE storage SET del_flg = ? WHERE id = ?';
		$stmt = $this->pdo->prepare($sql);
		$flag = $stmt->execute(array( $del_flg, $id ));
		
		if (!$flag){
			return false;
		}
		return true;
	}
	

}