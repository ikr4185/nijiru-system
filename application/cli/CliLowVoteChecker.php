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
	protected $logic;
	
	protected function getLogic() {
		$this->logic = new CliLowVoteCheckerLogic();
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
		$lvcArray = $this->logic->convertLvcArray( $lowVotes );
		
//		// 削除対象フラグで記事配列を振り分け
//		$redCardsLvcArray = array();
//		$yellowCardsLvcArray = array();
//		foreach ( $lvcArray as $key=>$val ) {
//			$this->isExpanding( $key, $val, $redCardsLvcArray, $yellowCardsLvcArray );
//		}
		
		var_dump($lvcArray);
		
		Console::log("Done.");
	}
	
	
}

