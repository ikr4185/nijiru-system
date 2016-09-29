<?php

/**
 * LVC Model 下請けクラス
 * Class CliLvcModelSubcontract
 */

require_once '/home/njr-sys/public_html/class/FilePath/FilePath.php';
require_once(CMMN_MDL);
require_once(DB_CLS);
require_once(MAIL_CLS);

// Configファイル読み込み
require_once "/home/njr-sys/public_html/application/_cores/config/Config.php";

class CliLvcModelSubcontract extends CommonModel {

	/**
	 * @var object Database
	 */
	protected $database = '';

	public function __construct() {

		// 親のコンストラクタを継承
		parent::__construct();

		// データベース接続
		$this->database = new Database(
			\Cores\Config\Config::load("db.dsn"),
			\Cores\Config\Config::load("db.user"),
			\Cores\Config\Config::load("db.pass")
		);
	}


	/**
	 * 生のソースから各データをパースする
	 * @param $html
	 * @return array
	 * @see CliLvcModel::scrapeWrapper()
	 */
	protected function parseLowVotes( $html )
	{

		// 記事抽出
		$array = preg_split("/(id=\"page-content\")/",$html);

		$html = $array[1];
		$array = preg_split("/(class=\"page-tags\")/",$html);
		$html = $array[0];

		// <a href="/scp-395-jp">Scp 395 Jp　望遠眼鏡</a> <span style="color: #777">(評価: -35, コメント: 3)</span>
		preg_match_all("/(<a href=\"\/)([\s\S]*?)(\">)([\s\S]*?)(<\/a> <span style=\"color: #777\">\(評価: )([\s\S]*?)(, コメント: )([\s\S]*?)(\)<\/span>)/", $html, $allArticle);

		// わかり易い名前にしような
		$LowVotes = array();
		foreach($allArticle[2] as $key=>$val){

			$LowVotes[$key]['url'] = $allArticle[2][$key];
			$LowVotes[$key]['title'] = $allArticle[4][$key];
			$LowVotes[$key]['vote'] = $allArticle[6][$key];
			$LowVotes[$key]['comment'] = $allArticle[8][$key];

		}
		return $LowVotes;
	}

	/**
	 * 低評価記事一覧 → 各記事毎にLVC標準形式に整形
	 * @param $post
	 * @return mixed
	 * @see CliLvcModel::convertLvcArray()
	 */
	protected function convertLvcPost( $post ) {

		$lvcPost["{$post['title']}"] = $this->countExtensionTime("http://ja.scp-wiki.net/".$post['url']);
		$lvcPost["{$post['title']}"]['vote'] = $post['vote'];
		$lvcPost["{$post['title']}"]['url'] = "http://ja.scp-wiki.net/".$post['url'];
		$lvcPost["{$post['title']}"]['comment'] = $post['comment'];
		$lvcPost["{$post['title']}"]['is_expanding'] = $post['is_expanding'];

		return $lvcPost;
	}

	/**
	 * DB → 各記事毎にLVC標準形式に整形
	 * @param $dbRecord
	 * @return mixed
	 * @see CliLvcModel::convertLvcArray()
	 * @see convertDbRecord()
	 */
	protected function convertLvcDbRecord( $dbRecord ) {

		$lvcPost["{$dbRecord['name']}"]["post"] = $dbRecord['post_date'];
		$lvcPost["{$dbRecord['name']}"]["del_date"] = $dbRecord['del_date'];
		$lvcPost["{$dbRecord['name']}"]["is_notified"] = (bool) $dbRecord['is_notified'];
		$lvcPost["{$dbRecord['name']}"]['vote'] = null;
		$lvcPost["{$dbRecord['name']}"]['url'] = $dbRecord['url'];
		$lvcPost["{$dbRecord['name']}"]['comment'] = null;
		$lvcPost["{$dbRecord['name']}"]['is_expanding'] = true; // DBに入ってる時点でレッドカード記事

		return $lvcPost;
	}

	/**
	 * 削除猶予時刻はいつか
	 * @param $url
	 * @return array ( 投稿時刻, 猶予期限, 通知済みフラグ )
	 * @see convertLvcArray()
	 */
	private function countExtensionTime($url)
	{
		// ディスカッションをスクレイピング
		$html = $this->getDiscussionHtml( $url );

		// 投稿時刻の取得
		$postTimestamp = $this->checkPostTime( $html );
		$postDate = date( "Y-m-d H:i:s", $postTimestamp );

		// 現時刻からの、猶予期限の算出
		$gracePeriod = $this->calculateGracePeriod( false, $postTimestamp );

		// 削除通知済みなら猶予期限を置換、通知済みフラグをtrueにする
		$isNotified = false;
		preg_match_all('@(<iframe src="http://scp-jp-sandbox2.wdfiles.com/local--files/holy-nova/timer.html\?timestamp=)(.*?)(&amp;type=0")@', $html, $delDate);
		if (!empty($delDate[2])) {
			$gracePeriod = date("Y-m-d H:i:s", substr($delDate[2][0], 0, -3) );
			$isNotified = true;
		}
		unset($html);

		// 結果を配列にしてリターン
		$result = array(
			"post" => $postDate,
			"del_date" => $gracePeriod,
			"is_notified" => $isNotified
		);
		return $result;
	}

	/**
	 * ページごとのディスカッションのソースを取得する
	 * @param $url
	 * @return mixed|string
	 * @see countExtensionTime()
	 */
	private function getDiscussionHtml( $url ){

		// フォーラムURLの取得
		$html = $this->scraping($url);
		preg_match_all("/(<a href=\"\/forum\/)([\s\S]*?)(\" class=\"btn btn-default\" id=\"discuss-button\">)/", $html, $discussUrlArray);

		// 念のためスリープ
		sleep(1);

		// ソースの取得
		$html = $this->scraping( "http://ja.scp-wiki.net/forum/".$discussUrlArray[2][0] );

		// TODO 複数ページになった際、ページャーをカウントして、全ページをスクレイピングする処理

		return $html;
	}

	/**
	 * 記事の投稿時刻をチェックする
	 * @param $html
	 * @return bool|string
	 * @see countExtensionTime()
	 */
	private function checkPostTime( $html ) {
		// 投稿日時のパース
		// <span class="odate time_1456835280 format_%25e%20%25b%20%25Y%2C%20%25H%3A%25M%7Cagohover">01 Mar 2016 12:28</span><br/>
		preg_match_all('@(<span class="odate time_)(.*)( format_%25e%20%25b%20%25Y%2C%20%25H%3A%25M%7Cagohover">)(.*)(</span>)@', $html, $postDate);

		// Wikidotの日付をTimestampに変換
		return $this->convertDatetoTimestamp($postDate[4][0]);
	}

	/**
	 * Wikidotの日付をTimestampに変換
	 * @param $dateStr
	 * @return int
	 * @see checkPostTime()
	 */
	private function convertDatetoTimestamp( $dateStr )
	{
		// Wikidotの日付をパース
		$dateArray = explode( " ", $dateStr );
		$timeArray = explode( ":", $dateArray[3] );
		$dateStr = $dateArray[0]."-".$dateArray[1]." ".$dateArray[2];

		// タイムスタンプに変換
		$timestamp = strtotime( $dateStr ) + $timeArray[0] * 60 * 60 + $timeArray[1] * 60;

		// Wikidotのソースに含まれる時刻はグリニッジ時間なので、9時間足す
		$timestamp = $timestamp + 9 * 60 * 60;

		return $timestamp;
	}

	/**
	 * 現時刻からの、猶予期限の算出
	 * @return bool|string
	 * @see countExtensionTime()
	 * @see CliLvcModel::updateLowVotes()
	 */
	protected function calculateGracePeriod() {

		// 削除基準到達から72時間 の同意待ち
		$gracePeriod = date( "Y-m-d H:i:s", time() + 72 * 60 * 60 );
		return $gracePeriod;
	}

	/**
	 * 新規削除対象記事をDBに追加する
	 * @param $key
	 * @param $info
	 * @return bool
	 * @see CliLvcModel::saveData()
	 */
	protected function insertPost( $key, $info ) {

		// DBにレコード追加
		$result = $this->database->insertLowVotes( $key, $info['url'], $info["post"], $info["del_date"] );

		// エラー処理
		if (!$result) {
			echo $key . "insert fail\n"; // TODO 今んとこターミナル表示のみ
			return false;
		}
		return true;
	}

	/**
	 * 猶予期限+基準超え日時を更新する
	 * @param $url
	 * @param $post_time	string	投稿日時(Y-m-d H:i:s)
	 * @return bool
	 * @see CliLvcModel::saveData()
	 */
	protected function updateLowVotes( $url, $post_time ) {

		// 猶予期間の算出
		$gracePeriod = $this->calculateGracePeriod( false, strtotime($post_time) );

		// UPDATE実行
		$result = $this->database->updateLowVotes( $url, $gracePeriod );
		return $result;
	}

	/**
	 * 低評価記事のどれか一つでも、猶予期限を過ぎかつstatusが1でない場合、その記事のstatusを1にして true を返す
	 * @param $lvcArray
	 * @return bool
	 * @see CliLvcModel::calculateLvcStatus()
	 */
	protected function checkDelDate( $lvcArray )
	{

		$result = false;
		foreach ($lvcArray as $key=>$info) {

			// 該当記事のDB情報を取得
			$db_info = $this->database->searchLowVotes($info["url"]);

			// 猶予期限のチェック
			$del_date_diff = $this->compareTimestamp( time(), strtotime($db_info["del_date"]) );
			if ( $del_date_diff <= 0 ) {

				// status が 1 以外の場合、猶予期限切れの通知
				if ( $db_info["status"] != 1 ) {
					$result = true;
				}

				// status を 1 にする
				$this->database->updateMulti( $info['url'], "status", 1 );

			}else{
				// 猶予期限を過ぎていなければ

				// status が 1 の場合、 0 にする ( = 猶予期限切れ後に、期限が延長された場合など )
				if ( $db_info["status"] != 1 ) {
					$this->database->updateMulti( $info['url'], "status", 0 );
				}
			}
		}
		return $result;
	}

	/**
	 * タイムスタンプを比較
	 * @param $timestampBfr
	 * @param $timestampAft
	 * @return string
	 * @see checkDelDate()
	 * @see CliLvcModel::sendMail()
	 */
	protected function compareTimestamp( $timestampBfr, $timestampAft )
	{
		// 単純に引き算
		$relative_time = $timestampAft - $timestampBfr;

		// ゼロ除算回避
		if ($relative_time == 0) {
			return "";
		}
		return $relative_time;
	}


	/**
	 * DBの記事レコードを取得、LVC標準形式配列+基準超え日時にして返す
	 * @param $lvcArray
	 * @return array
	 */
	protected function convertDbRecord( $lvcArray )
	{
		// DBの記事レコード配列を取得
		$dbRecords = $this->database->getAllLowVotes();

//		var_dump($lvcArray);

		// LVC標準形式に変換
		$records = array();
		foreach ( $dbRecords as $record ) {

			$lvcPost = $this->convertLvcDbRecord( $record );

			// LVC標準配列に追加する
			$records = array_merge( $records, $lvcPost );

			// レコードの無いデータは、該当する低評価記事配列から引っ張ってくる
			$records[$record["name"]]["vote"] = $lvcArray[$record["name"]]["vote"];
			$records[$record["name"]]["comment"] = $lvcArray[$record["name"]]["comment"];

			// fall_date を追加する
			$records[$record["name"]]["fall_date"] = $record["fall_date"];

		}

//		var_dump($records);

		return $records;
	}
	
	protected function setMessageTitle( $lvcStatus, $recovered_lvcArray, $lvcArray ) {
		
		// 評価が回復した記事名
		$recoveredPostsNames = array();
		if ( $lvcStatus == ( 2 || 6 ) ) {
			foreach ($recovered_lvcArray as $key=>$val) {
				$recoveredPostsNames[] = $key;
			}
			$recoveredPostsNames = implode(",", $recoveredPostsNames);
		}
		
		// 猶予期限が切れた記事名
		$expiredPostsNames = array();
		foreach ($lvcArray as $key=>$val) {
			
			// レッドカード記事のみに絞る
			if ( $val['is_expanding'] ) {
				
				// 単純に現在時刻と比較して、0以下だったら名前を格納
				$relative_time = $this->compareTimestamp( time(), strtotime($val['del_date']) );
				if ( $relative_time <=0 ) {
					$expiredPostsNames[] = $key;
				}
			}
		}
		if ( !empty($expiredPostsNames) ) {
			$expiredPostsNames = implode(",", $expiredPostsNames);
		}
		
		switch ($lvcStatus){
			case '1':
				// 新着有り
				$message1_title = "削除基準を下回りました";
				break;
			case '2':
				// 基準抜け有り
				$message1_title = "記事の評価が回復しました({$recoveredPostsNames})";
				break;
			case '4':
				// 猶予期限切れ有り
				$message1_title = "猶予期限を超えた記事があります。確認してください。({$expiredPostsNames})";
				break;
			case '3':
				// 新着＋基準抜け
				$message1_title = "削除基準を下回りました。また、記事の評価が回復しました({$recoveredPostsNames})";
				break;
			case '5':
				// 新着+猶予期限切れ
				$message1_title = "削除基準を下回りました。また、猶予期限超え記事があります({$expiredPostsNames})";
				break;
			case '6':
				// 基準抜け+猶予期限切れ
				$message1_title = "評価回復記事({$recoveredPostsNames})、猶予期限超え記事があります({$expiredPostsNames})";
				break;
			case '7':
				// 全部
				$message1_title = "削除基準を下回りました。また、評価回復記事({$recoveredPostsNames})、猶予期限超え記事があります({$expiredPostsNames})";
				break;
//			case '8':   // 記事消滅
//				// 記事消滅
//				$message1_title = "低評価記事一覧より記事が削除されました";
//				break;
//			case '9':   // 記事消滅 + 新規(1)
//			case '10':  // 記事消滅 + 基準抜け(2)
//			case '12':  // 記事消滅 + 猶予期限切れ(4)
//			case '11':  // 記事消滅 + 新規 + 基準抜け(3)
//			case '13':  // 記事消滅 + 新規 + 猶予期限切れ(5)
//			case '14':  // 記事消滅 + 基準抜け + 猶予期限切れ(6)
//			case '15':  // 記事消滅 + 新規 + 基準抜け + 猶予期限切れ(7)
//				// 記事消滅 + 他
//				$message1_title = "低評価記事一覧より記事が削除されました";
//				break;
			case '99':
				// 新着有り
				$message1_title = "定時通知です";
				break;
			default:
				$message1_title = "";
		}
		
		return $message1_title;
		
	}
	
	/**
	 * 有効なレコードの取得
	 * @return mixed
	 */
	protected function getAvailableLvcUsers() {
		
		$result = $this->database->execSql( 'SELECT *
FROM admin_lvc_users
WHERE is_available = 1',
			array(),
			true
		);
		return $result;
	}

}