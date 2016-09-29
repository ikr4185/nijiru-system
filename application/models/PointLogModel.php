<?php
namespace Models;
use Models\Commons\AbstractModel;

class PointLogModel extends AbstractModel
{
	
	/**
	 * ニジポ喜捨ログの保存
	 * @param $users_number
	 * @param $given_users_number
	 * @param $moved_point
	 * @return bool
	 */
	public function savePointLog( $users_number, $given_users_number, $moved_point ) {
		$sql = 'insert into point_log(users_number, given_users_number, moved_point) values (?, ?, ?)';
		$stmt = $this->pdo->prepare($sql);
				
		return $stmt->execute( array($users_number, $given_users_number, $moved_point) );
	}
	
}