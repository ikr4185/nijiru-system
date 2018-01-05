<?php
namespace Cli;
use Cli\Commons\CliAbstract;
use Logics\CliLowVoteCheckerLogic;
use Cli\Commons\Console;

// Docs

/*

LVC標準配列: array( "記事名" => array( post, del_date, vote, url, comment, is_expanding, is_notified ) )
array(5) {
	["SCP-801-JP"]=>
		array(7) {
		["post"]=>
			string(19) "2016-04-17 23:40:00"
		["del_date"]=>
			string(19) "2016-04-23 23:24:47"
		["vote"]=>
			string(2) "-4"
		["url"]=>
			string(33) "http://ja.scp-wiki.net/scp-801-jp"
		["comment"]=>
			string(1) "3"
		["is_expanding"]=>
			bool(true)
			["is_notified"]=>
			bool(true)
		}
	}
	...
}

*/

/**
 * Class CliLowVoteChecker
 * @package Cli
 */
class CliLowVoteChecker extends CliAbstract{
	
	/**
	 * @var CliLowVoteCheckerLogic
	 */
	protected $logic = null;

	protected $is_debug = false;
	
	protected function getLogic() {
		$this->logic = new CliLowVoteCheckerLogic();
		$this->is_debug = false;
	}
	
	public function indexAction() {
		Console::log("Start.");
		
		// 評価の低い記事一覧をスクレイピング・パースする
		Console::log("scraping");
		$lowVotes = $this->logic->parseLowVotes($this->logic->scraping());
        
		// 削除基準以下の記事に、削除対象フラグを付与
		Console::log("checkVote");
		$lowVotes = $this->logic->checkVote( $lowVotes );
		
		// LVC標準配列に変換
		Console::log("convertLvcArray");
		$lvcArray = $this->logic->convertLvcArray( $lowVotes, $this->is_debug );
		
		// 削除対象フラグで記事配列を振り分け
		Console::log("isExpanding");
		$redAndYellow  = $this->isExpanding( $lvcArray );

		if ($this->is_debug) {
			// （テスト用）評価を無理やり回復したことにする
//			$redAndYellow["yellow"]["SCP-395-JP"] = $redAndYellow["red"]["SCP-395-JP"];
//			unset($redAndYellow["red"]["SCP-395-JP"]);
		}

		// イエローカード記事配列の中から、DBに記録があるもの = 過去記録されたが、評価が回復した記事を抽出
		Console::log("extractRecoveredPosts");
		$recovered_lvcArray = array();
		if ( !empty($redAndYellow["yellow"]) ) {
			$recovered_lvcArray = $this->logic->extractRecoveredPosts( $redAndYellow["yellow"] );
		}

		// DB保存
		Console::log("Save Data");
		$saveInfoArray = $this->logic->saveData( $redAndYellow["red"] );
		
//		 DBのみに存在するレコード = 削除された記事 ( or Voteが0以上になった記事 )を検出して、ソフトデリート TODO こうじゃない(DBにはレッドカード記事しか無い)
//		 DBのみに存在するレコード = 評価が回復した記事を検出して、ソフトデリート // TODO これもあやしい？要調査
		Console::log("deleteLowVotes");
		$deletion_existed = $this->logic->deleteLowVotes( $redAndYellow["red"] );
		
		// 各メール通知の条件をまとめる ( Linuxパーミッションの要領で計算 )
		Console::log("calculateLvcStatus");
		$lvcStatus = $this->logic->calculateLvcStatus( $saveInfoArray, $recovered_lvcArray, $redAndYellow["red"], $deletion_existed );
		
		$sendMail = false;
		// 新着(1)があれば | 基準抜け(2)がアレば | 猶予期間を過ぎていたら(4) → メール通知
		if ( $lvcStatus > 0 ) {
			$sendMail = true;
		}
		// 7:00 だったら一回メールする TODO 夜中に溜まった通知をどげんかせんといかん
		if ( intval(date("H")) == 7 && intval(date("i")) == 0 ) {
			$sendMail = true;
			$lvcStatus = 99;
		}
		// SendMail
		if ($sendMail) {
			Console::log("semdMail");
			$this->logic->sendMail( $saveInfoArray, $recovered_lvcArray, $lvcArray, $lvcStatus, $redAndYellow["yellow"], $this->is_debug );
		}
		
		Console::log("Done.");
	}

	/**
	 * 削除対象フラグで記事配列を振り分け
	 * @param $lvcArray
	 * @return array
	 */
	private function isExpanding( $lvcArray ) {

		$redCardsLvcArray = array();
		$yellowCardsLvcArray = array();

		foreach ( $lvcArray as $key=>$val ) {

			if ($val["is_expanding"]) {
				// レッドカード記事の配列 (削除対象)
				$redCardsLvcArray[$key] = $val;
			} else {
				// イエローカード記事の配列 (非削除対象)
				$yellowCardsLvcArray[$key] = $val;
			}

		}

		return array(
			"red" => $redCardsLvcArray,
			"yellow" => $yellowCardsLvcArray,
		);

	}
	
}

