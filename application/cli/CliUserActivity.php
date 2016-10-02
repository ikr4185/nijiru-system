<?php
namespace Cli;
use Logics\CliUserActivityLogic;
use Cli\Commons\Console;

/**
 * サイトメンバー情報の取得
 */
class CliUserActivity {

	/**
	 * @var CliUserActivityLogic
	 */
	protected $logic;
	
	public function __construct(  ) {
		$this->getLogic();
	}
	
	protected function getLogic() {
		$this->logic = new CliUserActivityLogic();
	}
	
	public function indexAction(){

		Console::log("Start.");

		// 取得するページの制限
		$recentChangePageLimit = 5;

		// 「最近の更新」から、更新情報を抽出
		$recentChangeInfo = array();
		for ( $page=1; $page<=$recentChangePageLimit; $page++ ) {

			Console::log("Scraping", "RecentChanges {$page}");
			$html = $this->logic->getRecentChangesHtml($page);
			$recentChanges = $this->logic->matchHtml($html);
			unset($html);

			// 各更新の情報を抽出・保存
			Console::log("matchRecentChanges", "RecentChanges {$page}");
			foreach ($recentChanges[3] as $recentChange){
				$recentChangeInfo[] = $this->logic->matchRecentChanges( $recentChange );
			}
			unset($recentChange);

			sleep(1);
		}

		Console::log("sleeping...");
		sleep(3);

		// 「最近のフォーラム投稿」RSSから、更新情報を抽出
		Console::log("get RSS", "ResentPosts");
		$recentPostInfo = $this->logic->getRss('http://ja.scp-wiki.net/feed/forum/posts.xml');
		
		// 「IRCログ」から、参加者情報を取得
		Console::log("get IRC", "ResentIrc");
		$recentIrcInfo = $this->logic->getIrcUser();


		// 各更新情報を基に、ユーザーの最新の活動時刻を抽出、配列に格納
		Console::log("user extract...");
		$userActivity = array();
		foreach ($recentChangeInfo as $info) {
			$userActivity[] = array(
				"name" => $info["name"],
				"timestamp" => (int)strtotime($info["mod-date"]),
				"recent_date" => $info["mod-date"],
				"type" => $info["type"],
			);
		}
		foreach ($recentPostInfo as $info) {
			$userActivity[] = array(
				"name" => (string)$info["user"],
				"timestamp" => (int)$info["date"],
				"recent_date" => date("Y-m-d H:i:s", (int)$info["date"] - 9*60*60),
				"type" => "forum_post",
			);
		}
		foreach ($recentIrcInfo as $info) {
			$userActivity[] = array(
				"name" => $info["name"],
				"timestamp" => (int)$info["timestamp"],
				"recent_date" => $info["recent_date"],
				"type" => $info["type"],
			);
		}
		// 配列を古い順にソート
		$userActivity = \Cores\Helper\SortHelper::sort( $userActivity, "timestamp" );
		
		// 情報をデータベースに保存
		Console::log("Save Data");
		$this->logic->saveData($userActivity);
//		var_dump($userActivity);

		Console::log("Done.");
	}
	
}

