<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;
use Models\SiteMembersModel;
use Models\SiteActivityModel;
use Cli\Commons\Console;

class CliUserActivityLogic extends AbstractLogic {
	
	/**
	 * @var SiteMembersModel
	 */
	protected $SiteMembers = null;

	/**
	 * @var SiteActivityModel
	 */
	protected $SiteActivity = null;

	// DB上の、最新のレコード
	protected $recentRecord = null;

	public function __construct() {
		parent::__construct();

		// DB上、最新のレコードを取得
		$this->recentRecord = $this->SiteActivity->getRecent();
	}
	
	protected function getModel() {
		$this->SiteMembers = SiteMembersModel::getInstance();
		$this->SiteActivity = SiteActivityModel::getInstance();
	}

	/**
	 * 最近の更新 スクレイピング
	 * @param $page
	 * @return mixed
	 */
	public function getRecentChangesHtml($page) {
		$url = "http://ja.scp-wiki.net/system:recent-changes/?page={$page}";
		$contents = Scraping::run($url);

		// 必要箇所の抜き出し
		$contents = mb_strstr($contents, '<div class="changes-list" id="site-changes-list">', false);
		return mb_strstr($contents, '<div id="page-info-break"></div>', true);
	}
	
	/**
	 * 最近の更新 マッチング処理
	 * @param $html
	 * @return mixed
	 */
	public function matchHtml( $html ) {
		preg_match_all('@((<div class="changes-list-item">)((.|\s)*?)(</div>))@', $html, $users);
		return $users;
	}
	
	/**
	 * 最近の更新 情報の抽出
	 * @param $recentChange
	 * @return array
	 */
	public function matchRecentChanges( $recentChange ) {
		
		// returnするやつの初期化
		$result = array();
		
		// ユーザー名
		preg_match('@onclick="(.*?)return false;" >(.*?)</a>@', $recentChange, $tmp);
		$result["name"] = str_replace("http://www.wikidot.com/user:info/","",$tmp[2]);

		// 更新種別
		// TODO 複数要素に対応する
		preg_match('@<span class="spantip" title="(.*?)">(.*?)</span>@', $recentChange, $tmp);
		if ($tmp[2]=="S"){
			$result["type"]  = "change_page_text";
		}elseif($tmp[2]=="A"){
			$result["type"]  = "change_page_tags";
		}elseif($tmp[2]=="M"){
			$result["type"]  = "change_page_meta";
		}elseif($tmp[2]=="T"){
			$result["type"]  = "change_page_title";
		}elseif($tmp[2]=="N"){
			$result["type"]  = "create_page";
		}elseif($tmp[2]=="F"){
			$result["type"]  = "change_page_file";
		}elseif($tmp[2]=="R"){
			$result["type"]  = "rename_page";
		}else{
			$result["type"]  = "change_page";
		}


		// 更新日
		preg_match('@<span class="odate time_(.*?)">((.|\s)*?)</span>@', $recentChange, $tmp);
		$result["mod-date"] = date("Y-m-d H:i:s", Scraping::convertWikidotDateToTimestamp($tmp[2]));
		
		unset($tmp);
		return $result;
	}

	/**
	 * RSSの取得
	 * @param $url
	 * @return array
	 */
	public function getRss($url)
	{
		
		$rss = simplexml_load_file( $url, 'SimpleXMLElement', LIBXML_NOCDATA );
		
		$forumItemArray = array();
		$i = 0;
		
		foreach ($rss->channel->item as $item) {
			$forumItemArray[$i]['title']	=	$item->title;
			$forumItemArray[$i]['date']		=	strtotime($item->pubDate);
			$forumItemArray[$i]['link']		=	$item->link;
			$forumItemArray[$i]['user']		=	$item->children('wikidot', true)->authorName;

			$i++;
		}
		
		// debug //////////////////////////////////////
//		var_dump($forumItemArray);
		// debug //////////////////////////////////////
		
		return $forumItemArray;
		
	}

	public function getIrcUser() {

		$result = array();

		// 今日の分のログを取得
		$today = date("Y-m-d");
		$todayLogName = "irc-logs_".$today.".dat";
		$logArray = file("/home/njr-sys/public_html/cli/logs/irc/".$todayLogName);
		
		// まだログが無い場合はnull
		if (empty($logArray)) {
			return null;
		}

		// 前回取得したIRCログの時刻が、前日のものだったら、前日のIRCログも参照する
		$record = $this->recentRecord;
		if ( strpos($record[0]["recent_date"], $today) === false ) {

			Console::log("load yesterday log","Save Data");

			$yesterday = date('Y-m-d', strtotime('-1 day'));
			$yesterdayLogName = "irc-logs_".$yesterday.".dat";
			$yesterdaylogArray = file("/home/njr-sys/public_html/cli/logs/irc/".$yesterdayLogName);

			$logArray = array_merge($logArray,$yesterdaylogArray);
		}

		// パース
		foreach ($logArray as $line) {

			// 名前
			preg_match('/((\d{2}):(\d{2}):(\d{2})) - \((.*?)\) - /', $line, $matches);

			// カシマちゃん以外を取得
			if ( $matches[5] != "KASHIMA-EXE") {

				$result[] = array(
					"name" => $matches[5],
					"timestamp" => strtotime($today." ".$matches[1]),
					"recent_date" => $today." ".$matches[1],
					"type" => "irc_scp-jp",
				);
			}
		}

		return $result;
	}

	/**
	 * データベース保存
	 * @param $userActivity
	 * @return bool
	 */
	public function saveData( $userActivity ) {

		// DB上の最新のレコード
		$record = $this->recentRecord;

		// debug ////////////////////////////////////////
		file_put_contents("/home/njr-sys/public_html/cli/logs/test.dat", serialize($userActivity));

		$saveCount=0;
		foreach ( $userActivity as $item ) {

			// DBにレコードがある
			if ( isset($record[0]["recent_date"]) ) {

				// もしDB上の最新レコードまで到達したら、処理終了
				if ( strtotime($record[0]["recent_date"]) > $item["timestamp"] ) {
					Console::log("save end ".$item["recent_date"],"Save Data");
					break;
				}

			}

			// レコードを追加
//			$result = $this->SiteActivity->insert( $item["name"], $item["type"], $item["recent_date"] );
			$result = true; // debug ////////////////////////////////////////
			if (!$result) {
				return false;
			}
			$saveCount++;
		}

		Console::log("saved {$saveCount} records","Save Data");
		return true;
	}
}