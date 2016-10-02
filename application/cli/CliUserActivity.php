<?php
namespace Cli;
use Logics\CliUserActivityLogic;
use Cli\Commons\Console;
use Logics\ForumLogic;

/**
 * サイトメンバー情報の取得
 */
class CliUserActivity {

	/**
	 * @var ForumLogic
	 */
	protected $ForumLogic;

	/**
	 * @var CliUserActivityLogic
	 */
	protected $logic;
	
	public function __construct(  ) {
		$this->getLogic();
	}
	
	protected function getLogic() {
		$this->logic = new CliUserActivityLogic();
		$this->ForumLogic = new ForumLogic();
	}
	
	public function indexAction(){

		Console::log("Start.");

		// 取得するページの制限
		$recentChangePageLimit = 1;

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
//		sleep(3);

		// 「最近のフォーラム投稿」RSSから、更新情報を抽出
		Console::log("get RSS", "ResentPosts");
		$recentPostInfo = $this->ForumLogic->getRss('http://ja.scp-wiki.net/feed/forum/cp-790921.xml');


		// 各更新情報を基に、ユーザーの最新の活動時刻を抽出
		Console::log("user extract...");
		$userActivity = array();
		foreach ($recentChangeInfo as $info) {
			// ユーザーが既に配列に存在するか、更新時刻降順(ソートの必要なし)でチェック
			if ( !array_key_exists($info["name"], $userActivity) ) {
				// まだ居なければ、単純に追加
				$userActivity[$info["name"]] = strtotime($info["mod-date"]);
			}
		}
		foreach ($recentPostInfo as $info) {
			// ユーザーが既に配列に存在するか、更新時刻降順(ソートの必要なし)でチェック
			if ( !array_key_exists( (string)$info["user"], $userActivity ) ) {
				// まだ居なければ、単純に追加
				$userActivity[ (string)$info["user"] ] = strtotime((string)$info["date"]);
			}
		}
		// 配列のソート
		arsort($userActivity);
		
		// 情報をデータベースに保存
		Console::log("Save Data");
		$this->logic->saveData($userActivity);

		var_dump($userActivity);

		Console::log("Done.");
	}
	
}

