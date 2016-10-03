<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Models\ScpJpModel;
use Models\SiteMembersModel;
use Models\SiteActivityModel;
use Models\UsersModel;

/**
 * Class SiteMembersLogic
 * @package Logics
 */
class SiteMembersLogic extends AbstractLogic {
	
	/**
	 * @var SiteMembersModel
	 */
	protected $SiteMembers = null;
	
	/**
	 * @var SiteActivityModel
	 */
	protected $SiteActivity = null;
	
	/**
	 * @var ScpJpModel
	 */
	protected $ScpJp = null;
	
	/**
	 * @var UsersModel
	 */
	protected  $Users = null;
	
	protected function getModel() {
		$this->SiteMembers = SiteMembersModel::getInstance();
		$this->SiteActivity = SiteActivityModel::getInstance();
		$this->ScpJp = ScpJpModel::getInstance();
		$this->Users = UsersModel::getInstance();
	}

	/**
	 * ソートするやつ
	 * @see http://qiita.com/tadasuke/items/e7be0d214e02105ab6d8
	 * @param $array
	 * @param $sortKey
	 * @param int $sortType
	 */
	public function sortArrayByKey( &$array, $sortKey, $sortType = SORT_ASC ) {

		$tmpArray = array();
		foreach ( $array as $key => $row ) {
			$tmpArray[$key] = $row[$sortKey];
		}
		array_multisort( $tmpArray, $sortType, $array );
		unset( $tmpArray );
	}

	/**
	 * 全サイトメンバーを取得
	 * @return array|string
	 */
	public function getUserIndex() {
		$siteMembers = $this->SiteMembers->getAll();
		return $siteMembers;
	}
	
	/**
	 * メンバーの直近の活動を取得
	 * @param $name
	 * @return mixed
	 */
	public function getUserRecentActivity( $name ) {
		$result = $this->SiteActivity->getRecentByName( $name );
		return $result[0];
	}
	
	/**
	 * 最近活動のあったメンバーを取得
	 * @param $day
	 * @return mixed
	 */
	public function getActiveUser( $day ) {
		$result = $this->SiteActivity->getRecentDate( $day );
		$result = \Cores\Helper\SortHelper::sort( $result, "recent_date", true );
		return $result;
	}

	/**
	 * 月間のアクティブメンバー数を取得
	 * @param $date
	 * @return mixed
	 */
	public function getActiveUserInDateRange( $date ) {
		$result = $this->SiteActivity->getRecentInDateRange( $date );

		// debug ////////////////////////////////////////
//		vD($result);
		
		if (!$result) return array();
		
		$return=array();
		foreach ( $result as $record ){
			$return[] = $record["name"];
		}
		
		$result = \Cores\Helper\SortHelper::sort( $result, "recent_date", true );
		return $result;
	}

	/**
	 * メンバーの執筆数を計上
	 * @param $name
	 * @return mixed
	 */
	public function getUserArticlesCount( $name ) {
		$result = $this->ScpJp->getUserArticlesCount( $name );
		return $result[0]["count(*)"];
	}
	
	/**
	 * メンバーの最高評価記事情報を取得
	 * @param $name
	 * @return mixed
	 */
	public function getMaxVote( $name ) {
		$result = $this->ScpJp->getMaxVote( $name );
		return $result[0];
		
	}

	/**
	 * メンバーの平均評価を計算
	 * @param $name
	 * @param $articleCount
	 * @return float
	 */
	public function getAverageVote( $name, $articleCount ) {

		// 0除算阻止
		if ($articleCount == 0) {
			return 0;
		}

		// 全記事情報の取得
		$allArticles = $this->ScpJp->getUserArticles( $name );

		// voteの合計
		$totalVote = 0;
		foreach ($allArticles as $article){
			$totalVote = $totalVote + $article["vote"];
		}

		// 平均値を返す
		return floor($totalVote / $articleCount);

	}

	/**
	 * 最新の記事を取得
	 * @return array|string
	 */
	public function getNewestArticle() {
		return  $this->ScpJp->getNewestArticle();
	}
	
	/**
	 * 特定期間内の全Voteの配列を取得
	 * @param $date
	 * @return array|string
	 */
	public function getVotesInDateRange($date) {
		$result = $this->ScpJp->getVotesInDateRange($date);

		if (!$result) {
			return array(0);
		}

		$return=array();
		foreach ( $result as $record ){
			$return[] = $record["vote"];
		}

		return $return;
	}
	
	/**
	 * 平均値算出ラッパー
	 * @see http://otukutun.hatenablog.com/entry/2011/12/09/153204
	 * @param array $values
	 * @return float
	 */
	public function average($values) {
		return (float) (array_sum($values) / count($values));
	}

	/**
	 * 中央値算出ラッパー
	 * @see http://otukutun.hatenablog.com/entry/2011/12/09/153204
	 * @param array $values
	 * @return float|mixed
	 */
	public function median($values){
		sort($values);
		$count = count($values);
		if ($count % 2 == 0){
			return (($values[($count/2)-1]+$values[(($count/2))])/2);
		}else{
			return ($values[floor($count/2)]);
		}
	}
	
	
	/**
	 * 最新の記事を取得
	 * @return array|string
	 */
	public function getNewestMember() {
		return  $this->SiteMembers->getNewestMember();
	}

	/**
	 * 特定期間内の新人職員のアカウント名配列を取得
	 * @param $date
	 * @return array|string
	 */
	public function getNewbiesInDateRange($date) {
		$result = $this->SiteMembers->getNewbiesInDateRange($date);

		if (!$result) {
			return array(0);
		}

		$return=array();
		foreach ( $result as $record ){
			$return[] = $record["name"];
		}

		return $return;
	}
}