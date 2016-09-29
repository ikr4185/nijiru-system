<?php
namespace Models;
use Models\Commons\AbstractModel;

class LowVoteLogModel extends AbstractModel
{
	
	/**
	 * DBにある全低評価記事の情報を取得する(ソフトデリートされた記事は含まない)
	 * @return mixed
	 */
	public function getAllLowVotes()
	{
		
		$low_votes_log = $this->execSql( 'SELECT *
FROM low_votes_log
WHERE del_flg = 0',
			array(),
			true
		);
		
		return $low_votes_log;
		
	}
	
	/**
	 * 低評価記事の情報をidから取得する
	 * @param $low_votes_number
	 * @return mixed
	 */
	public function searchLowVotesById($low_votes_number)
	{
		
		$low_votes_log = $this->execSql( 'SELECT *
FROM low_votes_log
WHERE low_votes_number = ?',
			array($low_votes_number)
		);
		
		return $low_votes_log;		
	}

	/**
	 * ソフトデリート
	 * @param $del_flg  int
	 * @param $url  string
	 * @return bool
	 */
	public function setSoftDeleteLowVotes( $del_flg, $url )
	{
		$sql = 'UPDATE low_votes_log SET del_flg = ? WHERE url = ?';
		$stmt = $this->pdo->prepare($sql);
		$flag = $stmt->execute(array( $del_flg, $url ));

		if (!$flag){
			return false;
		}
		return true;
	}
	
}