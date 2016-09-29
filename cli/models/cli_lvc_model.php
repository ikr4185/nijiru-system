<?php

// 継承する下請けクラスの読み込み
require_once('/home/njr-sys/public_html/cli/models/cli_lvc_model_subcontract.php');

/**
 * Class CliLvcModel
 */
class CliLvcModel extends CliLvcModelSubcontract {


	// debug mode !
	private $lvc_debug = 0;
//	private $lvc_debug = 1;


	/**
	 * 評価の低い記事一覧をスクレイピング + パースして返す
	 * @return mixed|string
	 */
	public function scrapeWrapper() {

		// 評価の低い記事一覧をスクレイピング
		$html = $this->scraping( 'http://ja.scp-wiki.net/lowest-rated-pages', "24646-26050");

		// curlのrangeオプションが死んでるっぽいので暫定対応
		$html = substr( $html, 22826, 4000 );

		// パース処理
		$lowVotes = $this->parseLowVotes( $html );

		return $lowVotes;
	}


	/**
	 * 削除基準以下かのフラグを付与
	 * @param $lowVotes	array	scrapeWrapperで切り出した配列 (低評価記事一覧掲載記事の配列)
	 * @return array	array	削除基準以下に到達しているかのフラグを付与した配列
	 */
	public function checkVote($lowVotes) {

		$topLowVotes = array();

		// 低評価記事一覧掲載記事の配列でループ
		foreach($lowVotes as $key=>$val){
			$topLowVotes[] = $val;
			$topLowVotes[$key]["is_expanding"] = false;

			// Vote数が削除基準なら、is_expandingをtrue
			if ( intval($val['vote']) <= -3 ) {
				$topLowVotes[$key]["is_expanding"] = true;
			}
		}
		return $topLowVotes;
	}


	/**
	 * LVC標準配列を取得
	 * @param $lowVotes
	 * @return array
	 * LVC標準形式: array( 記事名 => array( post, del_date, vote, url, comment, is_expanding, is_notified ), 記事名[], 記事名[] )
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
	public function convertLvcArray( $lowVotes ) {

		$lvcArray = array();

		// 低評価記事一覧掲載記事の配列でループ
		foreach( $lowVotes as $key=>$post ){

			// 各記事毎に、LVC標準形式に変換する
			echo "convert {$post['url']}\n"; // debug //////////
			$lvcPost = $this->convertLvcPost( $post );

			// LVC標準配列に追加する
			$lvcArray = array_merge( $lvcArray, $lvcPost );
		}

		// debug //////////
		// デバッグ用記事配列を追加
//		$lvcArray['SCP-test-JP'] =
//			array (
//				'post' => '2016-03-25 00:00:00',
//				'del_date' => '2016-07-19 23:28:00',
//				'protect' => false,
//				'is_notified' => false,
//				'vote' => '-100',
//				'url' => 'njr-sys.net',
//				'comment' => '100',
//				'is_expanding' => true,
////				'is_expanding' => false,
//			);

		return $lvcArray;
	}


	/**
	 * 0~-2の記事から、評価が回復した記事を抽出
	 * @param $yellowCardsLvcArray
	 * @return array
	 */
	public function extractRecoveredPosts( $yellowCardsLvcArray ) {

		$recoveredLvcArray = array();
		foreach ( $yellowCardsLvcArray as $post ) {

			// DBにある記事 = 評価が回復した記事を抽出
			$dbResult = $this->database->searchLowVotesNonDel($post["url"]);

			// 回復記事があれば
			if ( !empty($dbResult) ) {
				// LVC標準形式に変換する
				$recoveredLvcArray = $this->convertLvcDbRecord( $dbResult );
			}
		}
		return $recoveredLvcArray;
	}


	/**
	 * レッドカード記事について処理し、DB保存、処理結果をLVC標準配列として返す
	 * @param $redCardsLvcArray
	 * @return array|null
	 */
	public function saveData( $redCardsLvcArray ) {

		// 処理結果として使用する、LVC標準配列
		$saveInfoArray = array();

		try {

			// レッドカード記事の配列でループ
			foreach ( $redCardsLvcArray as $key=>$info ) {

				// 既存DBレコードから、過去の削除対象記事含めて検索
				$dbRecord = $this->database->searchLowVotes($info['url']);

				if ( !$dbRecord ) {
					// DBにレコードがなかった時の処理

					// 新規レコードをインサート
					$this->insertPost( $key, $info );

					// 処理結果を配列に追加
					$saveInfoArray[$key] = $info;

				}else{
					// DBにレコードがあった時の処理

					if ( 1 == $dbRecord["del_flg"] ) {
						// ソフトデリート済みがまた来た時
						// 「ばかだな　またきたのか」

						// ソフトデリート解除
						$this->database->setSoftDeleteLowVotes( 0, $dbRecord["url"] );

						// 猶予期限+基準超え日時を更新する
						$this->updateLowVotes( $dbRecord["url"], $dbRecord["post_date"] );

						// 処理結果を配列に追加
						$saveInfoArray[$key] = $info;
					}
				}

				// 猶予勧告状況を保存
				if ($info['is_notified']) {

					// 猶予済みフラグを更新する
					$this->database->updateNotified(  $info['url'], "1" );

					// 猶予期限を更新する
					$this->database->updateMulti( $info['url'], "del_date", $info["del_date"] ); // とりあえず期限の更新

				}
			}
		} catch(Exception $e) {
			// 例外処理
			// とりあえずログに記録
			file_put_contents("/home/njr-sys/public_html/cli/logs/low_vote_log.log",$e->getMessage()."\n",FILE_APPEND);
			// debug
			echo $e->getMessage() . $e->getLine();
		}

		// 処理結果を返す
		if ( !empty($saveInfoArray) ) {
			return $saveInfoArray;
		}
		return null;

	}


	/**
	 * DBにあるが、低評価記事データになかった記事(評価回復記事)をソフトデリート
	 * @param $lvcArray
	 * @return bool
	 */
	public function deleteLowVotes( $lvcArray ) {

		// LVC標準形式配列から、URLの配列を抽出
		$postUrls = array();
		foreach ($lvcArray as $info) {
			if ($info["url"]) {
				$postUrls[] = $info["url"];
			}
		}

		// DBの全記事レコードを取得(ソフトデリートされた記事は含まない)
		$delInfoArray = $this->database->getAllLowVotes();

		// 記事レコード配列から、URLの配列を抽出
		$SavedUrls = array();
		foreach ($delInfoArray as $info) {
			$SavedUrls[] = $info["url"];
		}

		// DB配列に、新規削除対象記事があったら削除して、その残り = ソフトデリート対象
		// ( DBにあるが、低評価記事データになかった記事 )
		$delUrls = array_diff( $SavedUrls, $postUrls );

		// ソフトデリート対象レコードがなかったら false 返して終了
		if ( empty($delUrls) ) {
			return false;
		}

		// ソフトデリート実行
		foreach ($delUrls as $url) {
			$result = $this->database->setSoftDeleteLowVotes( 1, $url );
			// エラーで中止
			if (!$result) {
				return false;
			}
		}
		return true;

	}

	/**
	 * 各メール通知の条件をまとめる ( Linuxパーミッションの要領で計算 )
	 * @param $saveInfoArray
	 * @param $recovered_lvcArray
	 * @param $lvcArray
	 * @param $deletion_existed
	 * @return int
	 */
	public function calculateLvcStatus( $saveInfoArray, $recovered_lvcArray, $lvcArray, $deletion_existed ) {

		$newInfo_flg = !empty($saveInfoArray) ? 1 : 0 ;
		$delPost_flg = !empty($recovered_lvcArray) ? 2 : 0 ;
		$expire_flg = $this->checkDelDate( $lvcArray ) ? 4 : 0 ;
//		$deletion_flg = $deletion_existed ? 8 : 0 ;
		$deletion_flg = 0; // TODO 開発中

		// 単純に足し算
		$lvcStatus = $newInfo_flg + $delPost_flg + $expire_flg + $deletion_flg;

		// debug
		var_dump($newInfo_flg,$delPost_flg,$expire_flg,$deletion_flg);

		return $lvcStatus;
	}


	/**
	 * メール送信
	 * @param $saveInfoArray	array	saveData処理結果のLVC標準配列
	 * @param $recovered_lvcArray	array	評価が回復した記事の一覧
	 * @param $lvcArray	array	低評価記事の配列
	 * @param $lvcStatus	int	LVCステータス
	 * @param $yellowCardsLvcArray	array   0～-2の評価の記事
	 */
	public function sendMail( $saveInfoArray, $recovered_lvcArray, $lvcArray, $lvcStatus, $yellowCardsLvcArray ) {

		// 現在時刻
		$now = date("Y-m-d H:i:s");

		// 夜中にめーる送ってくんなアホ
		if ( $this->lvc_debug == 0) {
			if ( intval( date( "H" ) ) < 7 && intval( date( "H" ) ) >= 2 ) {
				return;
			}
		}

		// 新着があれば | 基準抜けがアレば | 猶予期間を過ぎていたら
		$message1_title = $this->setMessageTitle( $lvcStatus, $recovered_lvcArray, $lvcArray );

		// message1 ==========================================================================================
		//書き出し
		$message1 = <<< EOD
----------------------------
▼[通知] {$message1_title}
----------------------------

EOD;

		if ( isset($saveInfoArray) ) {
			// もし新規基準超え記事がアレば----------------------------------------

			// 詳細情報
			foreach ( $saveInfoArray as $key=>$info ) {

				$is_notified = null;
				if ($info['is_notified']) {
					$is_notified = "勧告状況: [勧告済み]";
				}

				$message1 .= <<< EOD

{$key}
{$info['url']}
vote: {$info['vote']}
comment: {$info['comment']}
投稿: {$info['post']}
基準超え: {$now}
猶予期限: {$info["del_date"]}
{$is_notified}
----------------------------

EOD;
			}
		}

		// message2 ==========================================================================================
		// 現在の状況
		$dbRecords = $this->convertDbRecord( $lvcArray );
		$message2 = "";
		foreach ( $dbRecords as $key=>$info ) {

			$msg = null;
			if ($info['is_notified']) {
				$msg = "[削除勧告済み]";
			}
			if ( $this->compareTimestamp( time(), strtotime($info["del_date"]) ) <= 0 ) {
				$msg = "[猶予期限を過ぎています]";
			}

			// debug //////////
//			var_dump($info);

			$message2 .= <<< EOD

{$key}
{$info['url']}
vote: {$info['vote']}
comment: {$info['comment']}
投稿: {$info['post']}
基準超え: {$info['fall_date']}
猶予期限: {$info["del_date"]}
{$msg}
----------------------------

EOD;
		}

		// message3 ==========================================================================================
		// イエローカード記事
//		$message3 = "";
		foreach ( $yellowCardsLvcArray as $key=>$info ) {

			var_dump($key,$info);

//			$message3 .= <<< EOD
//
//{$key}
//{$info['url']}
//vote: {$info['vote']}
//comment: {$info['comment']}
//投稿: {$info['post']}
//基準超え: {$info['fall_date']}
//猶予期限: {$info["del_date"]}
//
//----------------------------
//
//EOD;
		}




		// 1 行が 70 文字を超える場合のため、wordwrap() を用いる
//		$message = wordwrap($message, 70, "\r\n");

		// メールクラスオブジェクト
		$mail = new MailModel("/home/njr-sys/public_html/template/mail/low_vote_checker.tpl");


		// 送信する
		if ( $this->lvc_debug == 0) {

			$lvcUsers = $this->getAvailableLvcUsers();
			foreach ( $lvcUsers as $item ) {

				$mail->send($item["mail"], array(
					"user" => $item["name"],
					"now" => $now,
					"message1" => $message1,
					"message2" => $message2
				));

			}

		}else{ // debug mode

			$lvcUsers = $this->getAvailableLvcUsers();
			foreach ( $lvcUsers as $item ) {

				if ($item["name"]=="ikr_4185") { // 育良だけに送信
					$mail->send($item["mail"], array(
						"user" => $item["name"],
						"now" => $now,
						"message1" => $message1,
						"message2" => $message2
					));
				}
			}
			echo "debug mode!";

		}

		// メール送信ログ
		file_put_contents("/home/njr-sys/public_html/cli/logs/low_vote_log.log",date("Y-m-d H:i:s")."\t".$message1_title."\t".$lvcStatus.count($saveInfoArray)."\t".count($dbRecords)."\n",FILE_APPEND);

	}

}