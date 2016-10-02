<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\SiteMembersLogic;
use Inputs\BasicInput;


/**
 * Class SitemembersController
 * @package Controllers
 */
class SitemembersController extends AbstractController {
	
	/**
	 * @var SiteMembersLogic
	 */
	protected $logic;
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->logic = new SiteMembersLogic();
	}
	
	protected function getInput() {
		$this->input = new BasicInput();
	}
		
	public function indexAction() {

		// 全サイトメンバーを取得
		$siteMembers = $this->logic->getUserIndex();

		// メンバー総数
		$count = count($siteMembers);
		
		// アクティブメンバーの取得
		$activeMembers = $this->logic->getActiveUser(30);

		// 各メンバーの情報
		foreach($siteMembers as &$member){
			
			// 執筆数
			$member["articleCount"] = $this->logic->getUserArticlesCount($member["name"]);
			
			// 最高評価
			$maxVote = $this->logic->getMaxVote($member["name"]);
			$member["maxVoteArticle"] = $maxVote["item_number"];
			$member["maxVote"] = $maxVote["vote"];
			unset($maxVote);
			
			// 平均評価
			$member["averageVote"] = $this->logic->getAverageVote( $member["name"], $member["articleCount"] );

			// 最後の活動時刻
			$recentActivity = $this->logic->getUserRecentActivity($member["name"]);
			$member["recentDate"] =  $recentActivity["recent_date"];
			$member["recentActivity"] =  $recentActivity["type"];
		}
		unset($member);

		// ソートの指定
		$sortBy = "";
		if ( $this->input->getRequest("max",true) == "desc" ) {
			$this->logic->sortArrayByKey($siteMembers,"maxVote",SORT_DESC);
			$sortBy = "max";
		}elseif( $this->input->getRequest("ave",true) == "desc" ){
			$this->logic->sortArrayByKey($siteMembers,"averageVote",SORT_DESC);
			$sortBy = "ave";
		}elseif( $this->input->getRequest("count",true) == "desc" ){
			$this->logic->sortArrayByKey($siteMembers,"articleCount",SORT_DESC);
			$sortBy = "count";
		}elseif( $this->input->getRequest("date",true) == "desc" ){
			$this->logic->sortArrayByKey($siteMembers,"since",SORT_DESC);
			$sortBy = "date";
		}elseif( $this->input->getRequest("recent",true) == "desc" ){
			$this->logic->sortArrayByKey($siteMembers,"recentDate",SORT_DESC);
			$sortBy = "recent";
		}
		
		$result = array(
			"siteMembers"  => $siteMembers,
			"activeMembers"  => $activeMembers,
			"count"  => $count,
			"sortBy"  => $sortBy,
			"msg"   => $this->logic->getMsg(),
		);
		$jsPathArray = array(
			"http://njr-sys.net/application/views/assets/js/nijiru_accordion.js",
		);
		$this->getView( "index", "サイトメンバー一覧", $result, $jsPathArray );
		
	}

	/**
	 * サイトメンバーに関する統計
	 */
	public function memberHistoryAction() {

		// 最新記事を取得、月日へ変換
		$newestArticle = $this->logic->getNewestMember();
		$newestDate = date("Y-m", strtotime($newestArticle[0]["since"]));

		// 最古参メンバー「Dr Devan」の登録時刻を、月日へ変換
		$oldestDate = date("Y-m", strtotime("2013-07-08 20:09:00"));

		// 結果として出力するデータ
		$memberHistory = array();

		// 累計メンバー数計上
		$allMemberCount = 0;
		
		$i=0;
		while(1){

			// 安全装置
			if ($i > 120) {
				exit;
			}

			// 調査月を設定
			if ($i == 0) {
				$date = date("Y-m", strtotime($oldestDate));
			}else{
				$date = date("Y-m", strtotime($oldestDate."-01 +{$i} month"));
			}

			// 月間の新人職員のアカウント名配列
			$allMemberName = $this->logic->getNewbiesInDateRange($date);

			// 人数
			$count = count($allMemberName);
			
			// 累計メンバー数
			$allMemberCount = $allMemberCount + $count;

			// 結果を格納
			$memberHistory[$i] = array(
				"date"=>$date,
				"count"=>$count,
				"allMemberCount"=>$allMemberCount,
				"newbies"=> implode("/",$allMemberName)
			);

			// カウントを進める
			$i++;

			// 日付が現在に至ったら終了
			if ($date == $newestDate) {
				break;
			}
		}

		$result = array(
			"memberHistory"  => $memberHistory,
			"msg"   => $this->logic->getMsg(),
		);
		$jsPathArray = array(
			"https://www.google.com/jsapi",
			"http://njr-sys.net/application/views/assets/js/member_history_chart.js",
			"http://njr-sys.net/application/views/assets/js/jquery.balloon.js",
			"http://njr-sys.net/application/views/assets/js/member_balloon.js",
		);
		$this->getView( "memberhistory", "Site Member History Statistics", $result, $jsPathArray );
	}
	
	/**
	 * 過去Voteの履歴と統計的分析
	 */
	public function voteHistoryAction() {

		// 最新記事を取得、月日へ変換
		$newestArticle = $this->logic->getNewestArticle();
		$newestDate = date("Y-m", strtotime($newestArticle[0]["created_at"]));

		// 最古の記事「稲穂」の投稿時刻を、月日へ変換
		$oldestDate = date("Y-m", strtotime("2013-10-15 00:36:04"));

		// 結果として出力するデータ
		$voteHistory = array();
		$totalCount = 0;
		$totalVote = 0;

		$i=0;
		while(1){

			// 安全装置
			if ($i > 120) {
				exit;
			}

			// 調査月を設定
			if ($i == 0) {
				$date = date("Y-m", strtotime($oldestDate));
			}else{
				$date = date("Y-m", strtotime($oldestDate."-01 +{$i} month"));
			}

			// 月間の全Voteの配列
			$allVote = $this->logic->getVotesInDateRange($date);
			
			// 記事数
			$count = count($allVote);

			// 月間平均Voteを求める
			$avg = floor($this->logic->average($allVote));

			// 月間の中央値を求める
			$med = floor($this->logic->median($allVote));

			// 結果を格納
			$voteHistory[$i] = array(
				"date"=>$date,
				"count"=>$count,
				"avg"=>$avg,
				"med"=>$med
			);

			// 総数
			$totalCount = $totalCount + $count;
			$totalVote = $totalVote + array_sum($allVote);

			// カウントを進める
			$i++;

			// 日付が現在に至ったら終了
			if ($date == $newestDate) {
				break;
			}
		}

		// 全体での平均評価
		$totalAverageVote = floor($totalVote / $totalCount);

		$result = array(
			"voteHistory"  => $voteHistory,
			"totalCount"  => $totalCount,
			"totalAverageVote"  => $totalAverageVote,
			"msg"   => $this->logic->getMsg(),
		);
		$jsPathArray = array(
			"https://www.google.com/jsapi",
			"http://njr-sys.net/application/views/assets/js/vote_history_chart.js",
		);
		$this->getView( "votehistory", "Vote History Statistics", $result, $jsPathArray );
	}
	
}