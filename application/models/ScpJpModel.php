<?php
namespace Models;
use Models\Commons\AbstractModel;

class ScpJpModel extends AbstractModel
{

	/**
	 * SCPナンバーから検索する
	 * @param $scp_num
	 * @return array|string
	 */
	public function selectScpJp( $scp_num ) {
		return $this->execSql( 'SELECT * FROM scp_jp WHERE scp_num = ?;', array($scp_num), true );
	}

	/**
	 * インサート
	 * @param $scp_num
	 * @param $title
	 * @param $item_number
	 * @param $class
	 * @param $protocol
	 * @param $description
	 * @param $vote
	 * @param $created_by
	 * @param $tags
	 * @param $created_at
	 * @return bool
	 */
	public function insertScpJp( $scp_num, $title, $item_number, $class, $protocol, $description, $vote, $created_by, $tags, $created_at ) {
		$sql = 'insert into scp_jp( scp_num, title, item_number, class, protocol, description, vote, created_by, tags, created_at ) values ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )';
		$stmt = $this->pdo->prepare($sql);

		return $stmt->execute(array( $scp_num, $title, $item_number, $class, $protocol, $description, $vote, $created_by, $tags, $created_at ));
	}

	/**
	 * アップデート
	 * @param $scp_num
	 * @param $title
	 * @param $item_number
	 * @param $class
	 * @param $protocol
	 * @param $description
	 * @param $vote
	 * @param $created_by
	 * @param $tags
	 * @param $created_at
	 * @param $id
	 * @return array|string
	 */
	public function updateScpJp( $scp_num, $title, $item_number, $class, $protocol, $description, $vote, $created_by, $tags, $created_at, $id ) {
		return $this->execUpdate(
			'UPDATE scp_jp SET scp_num = ?, title = ?, item_number = ?, class = ?, protocol = ?, description = ?, vote = ?, created_by = ?, tags = ?, created_at = ? WHERE id = ?',
			array( $scp_num, $title, $item_number, $class, $protocol, $description, $vote, $created_by, $tags, $created_at, $id )
		);
	}

	/**
	 * ソフトデリート操作
	 * @param $del_flg  int
	 * @param $id int
	 * @return bool
	 */
	public function setSoftDelete( $del_flg, $id )
	{
		$sql = 'UPDATE scp_jp SET del_flg = ? WHERE id = ?';
		$stmt = $this->pdo->prepare($sql);
		$flag = $stmt->execute(array( $del_flg, $id ));

		if (!$flag){
			return false;
		}
		return true;
	}
	
	// ==========================================================================================
	// Search
	
	/**
	 * あいまい検索: タイトル
	 * @param $search
	 * @return array|string
	 */
	public function selectScpJpWhereTitle ( $search ) {
		
		$search = "%{$search}%";
		return $this->execSql( 'SELECT * FROM scp_jp WHERE title LIKE ? AND del_flg = 0 ORDER BY scp_num LIMIT 100;', array($search), true );
	}
	/**
	 * あいまい検索: プロトコル
	 * @param $search
	 * @return array|string
	 */
	public function selectScpJpWhereProtocol ( $search ) {
		
		$search = "%{$search}%";
		return $this->execSql( 'SELECT * FROM scp_jp WHERE protocol LIKE ? AND del_flg = 0 ORDER BY scp_num LIMIT 100;', array($search), true );
	}
	/**
	 * あいまい検索: 説明
	 * @param $search
	 * @return array|string
	 */
	public function selectScpJpWhereDescription ( $search ) {

		$search = "%{$search}%";
		return $this->execSql( 'SELECT * FROM scp_jp WHERE description LIKE ? AND del_flg = 0 ORDER BY scp_num LIMIT 100;', array($search), true );
	}
	/**
	 * あいまい検索: 著作者
	 * @param $search
	 * @return array|string
	 */
	public function selectScpJpWhereCreatedBy ( $search ) {

		$search = "%{$search}%";
		return $this->execSql( 'SELECT * FROM scp_jp WHERE created_by LIKE ? AND del_flg = 0 ORDER BY scp_num LIMIT 100;', array($search), true );
	}
	/**
	 * あいまい検索: タグ
	 * @param $search
	 * @return array|string
	 */
	public function selectScpJpWhereTags ( $search ) {

		$search = "%{$search}%";
		return $this->execSql( 'SELECT * FROM scp_jp WHERE tags LIKE ? AND del_flg = 0 ORDER BY scp_num LIMIT 100;', array($search), true );
	}
	
	// ==========================================================================================
	// SiteMembers
	
	/**
	 * メンバーの全記事データを取得
	 * @param $created_by
	 * @return array|string
	 */
	public function getUserArticles( $created_by ) {
		return $this->execSql( 'SELECT * FROM scp_jp WHERE created_by = ? AND del_flg = 0 ORDER BY scp_num;', array($created_by), true );
	}

	/**
	 * メンバーの記事数の計上
	 * @param $created_by
	 * @return array|string
	 */
	public function getUserArticlesCount( $created_by ) {
		return $this->execSql( 'SELECT count(*) FROM scp_jp WHERE created_by = ? AND del_flg = 0 ORDER BY scp_num;', array($created_by), true );
	}
	
	/**
	 * メンバーの最高評価記事を取得
	 * @param $created_by
	 * @return array|string
	 */
	public function getMaxVote( $created_by ) {
		return $this->execSql( 'SELECT * FROM scp_jp WHERE created_by = ? AND del_flg = 0 ORDER BY vote DESC LIMIT 1;', array($created_by), true );
	}
	
	/**
	 * 最新の記事を取得
	 * @return array|string
	 */
	public function getNewestArticle() {
		return $this->execSql( 'SELECT * FROM scp_jp WHERE del_flg = 0 ORDER BY created_at DESC LIMIT 1;', array(), true );
	}

//	/**
//	 * 特定期間内のVote平均を求める
//	 * @param $date
//	 * @return array|string
//	 */
//	public function getAverageVoteFromDateRange($date) {
//		$date = $date."%";
//		return $this->execSql( 'SELECT avg(vote) FROM scp_jp WHERE del_flg = 0 AND created_at like (?);', array($date), true );
//	}
	
	/**
	 * 特定期間内の全Voteの取得
	 * @param $date
	 * @return array|string
	 */
	public function getVotesInDateRange($date) {
		$date = $date."%";
		return $this->execSql( 'SELECT vote FROM scp_jp WHERE del_flg = 0 AND created_at like (?);', array($date), true );
	}
	
	// ==========================================================================================
	// cli mtf checker 
	
	/**
	 * 検索: プロトコル
	 * @param $search
	 * @return array|string
	 */
	public function selectScpJpWhereProtocolNoLimit ( $search ) {
		
		$search = "%{$search}%";
		return $this->execSql( 'SELECT * FROM scp_jp WHERE protocol LIKE ? AND del_flg = 0 ORDER BY scp_num;', array($search), true );
	}
	/**
	 * 検索: 説明
	 * @param $search
	 * @return array|string
	 */
	public function selectScpJpWhereDescriptionNoLimit ( $search ) {
		
		$search = "%{$search}%";
		return $this->execSql( 'SELECT * FROM scp_jp WHERE description LIKE ? AND del_flg = 0 ORDER BY scp_num;', array($search), true );
	}
	
}