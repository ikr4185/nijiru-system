<?php
namespace Models;
use Models\Commons\AbstractModel;

class ScpReaderAccessLogModel extends AbstractModel
{
	
	/**
	 * ログの保存
	 * @param $user_id
	 * @param $url
	 * @return bool
	 */
	public function saveReaderLog( $user_id, $url ) {
		$sql = 'insert into scp_reader_access_log(user_id, url) values (?, ?)';
		$stmt = $this->pdo->prepare($sql);

		return $stmt->execute( array( $user_id, $url ) );
	}
	
	/**
	 * ログの取得
	 * @param $user_id
	 * @return array|string
	 */
	public function getReaderLog($user_id) {
		return $this->execSql( 'SELECT * FROM scp_reader_access_log WHERE user_id = ? GROUP BY url ORDER BY created_date DESC', array($user_id), true );
	}

}