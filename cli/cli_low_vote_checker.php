<?php
/**
 * 評価の低い記事をチェックする
 * php /home/njr-sys/public_html/cli/cli_low_vote_checker.php
 */

require_once '/home/njr-sys/public_html/class/FilePath/FilePath.php';

/**
 * Class CliLowVoteChecker
 */
class CliLowVoteChecker
{

	// Modelインスタンス
	private $model = '';

	public function __construct(){

		// Modelインスタンスの生成
		require_once '/home/njr-sys/public_html/cli/models/cli_lvc_model.php';
		$this->model = new CliLvcModel();
	}

	/**
	 * スクリプト実行
	 */
	public function run()
	{
		//debug
		echo date("Y-m-d H:i:s")."\n";

		// 評価の低い記事一覧をスクレイピング・パースする
		echo "scraping\n";// debug //////////
		$lowVotes = $this->model->scrapeWrapper();

		// 削除基準以下の記事に、削除対象フラグを付与
		echo "checkVote\n";// debug //////////
		$lowVotes = $this->model->checkVote( $lowVotes );

		// LVC標準配列に変換
		$lvcArray = $this->model->convertLvcArray( $lowVotes );
//		var_dump($lvcArray);

		// 削除対象フラグで記事配列を振り分け
		$redCardsLvcArray = array();
		$yellowCardsLvcArray = array();
		foreach ( $lvcArray as $key=>$val ) {
			$this->isExpanding( $key, $val, $redCardsLvcArray, $yellowCardsLvcArray );
		}
//		echo "redCardsLvcArray: ";		var_dump($redCardsLvcArray);
//		echo "yellowCardsLvcArray: ";	var_dump($yellowCardsLvcArray);

		// debug //////////
		// 評価を無理やり回復したことにする
//		$yellowCardsLvcArray["SCP-395-JP"] = $redCardsLvcArray["SCP-395-JP"];
//		unset($redCardsLvcArray["SCP-395-JP"]);

		// イエローカード記事配列の中から、DBに記録があるもの = 過去記録されたが、評価が回復した記事を抽出
		$recovered_lvcArray = array();
		if ( !empty($yellowCardsLvcArray) ) {
			$recovered_lvcArray = $this->model->extractRecoveredPosts( $yellowCardsLvcArray );
		}

		// DB保存
		echo "Save Data\n";// debug //////////
		$saveInfoArray = $this->model->saveData( $redCardsLvcArray );

//		 DBのみに存在するレコード = 削除された記事 ( or Voteが0以上になった記事 )を検出して、ソフトデリート TODO こうじゃない(DBにはレッドカード記事しか無い)
//		 DBのみに存在するレコード = 評価が回復した記事を検出して、ソフトデリート // TODO これもあやしい？要調査
		$deletion_existed = $this->model->deleteLowVotes( $redCardsLvcArray );

		// 各メール通知の条件をまとめる ( Linuxパーミッションの要領で計算 )
		$lvcStatus = $this->model->calculateLvcStatus( $saveInfoArray, $recovered_lvcArray, $redCardsLvcArray, $deletion_existed );
		
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
			echo "send mail\n";// debug //////////
			$this->model->sendMail( $saveInfoArray, $recovered_lvcArray, $lvcArray, $lvcStatus, $yellowCardsLvcArray );
		}

		echo "done.\n";// debug //////////

		// debug
		// check log
		file_put_contents("/home/njr-sys/public_html/cli/logs/low_vote_check_log.log",date("Y-m-d H:i:s")."\t".$lvcStatus."\n",FILE_APPEND);
	}

	/**
	 * 削除対象フラグで記事配列を振り分け
	 * @param $key
	 * @param $val
	 * @param $redCardsLvcArray
	 * @param $yellowCardsLvcArray
	 */
	private function isExpanding( $key, $val, &$redCardsLvcArray, &$yellowCardsLvcArray ) {

		if ($val["is_expanding"]) {
			// レッドカード記事の配列 (削除対象)
			$redCardsLvcArray[$key] = $val;
		} else {
			// イエローカード記事の配列 (非削除対象)
			$yellowCardsLvcArray[$key] = $val;
		}
	}

}

$instance = new CliLowVoteChecker;
$instance->run();