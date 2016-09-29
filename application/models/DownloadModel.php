<?php
namespace Models;
use Models\Commons\AbstractModel;

class DownloadModel extends AbstractModel
{
	
	/**
	 * 全レコードの取得
	 * @return mixed
	 */
	public function getAll() {
		
		$result = $this->execSql( 'SELECT *
FROM download
WHERE 1',
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
FROM download
WHERE download_id = ?',
			array($id)
		);
		return $result;
	}


}