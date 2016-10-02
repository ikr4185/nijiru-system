<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;
use Cli\Commons\Console;

/**
 * Class CliLowVoteCheckerLogic
 * @package Logics
 */
class CliLowVoteCheckerLogic extends AbstractLogic {

//	/**
//	 * @var SiteMembersModel
//	 */
//	protected $SiteMembers = null;

	/**
	 * @var WikidotApi
	 */
	protected $WikidotApi = null;

	protected function getModel() {
//		$this->SiteMembers = SiteMembersModel::getInstance();
	}

	/**
	 * 評価の低い記事一覧スクレイピング
	 * @return mixed|string
	 */
	public function scraping() {
		$html = Scraping::run( 'http://ja.scp-wiki.net/lowest-rated-pages');

		// curlのrangeオプションが死んでるっぽいので暫定対応
		$html = substr( $html, 22826, 4000 );
		return $html;
	}

	/**
	 * 生のソースから各データをパースする
	 * @param $html
	 * @return array
	 */
	public function parseLowVotes( $html ) {

		// 記事始まり～記事終わりまでを取得
		$contents = mb_strstr($html, '<div id="page-content">', false);
		$contents = mb_strstr($contents, '<div class="page-tags">', true);

		// <a href="/scp-395-jp">Scp 395 Jp　望遠眼鏡</a> <span style="color: #777">(評価: -35, コメント: 3)</span>
		preg_match_all('@(<a href="/)([\s\S]*?)(">)([\s\S]*?)(</a> <span style="color: #777">\(評価: )([\s\S]*?)(, コメント: )([\s\S]*?)(\)</span>)@', $contents, $allArticle);

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
	 * 削除基準以下かのフラグを付与
	 * @param $lowVotes	array	scrapeWrapperで切り出した配列 (低評価記事一覧掲載記事の配列)
	 * @return array	array	削除基準以下に到達しているかのフラグを付与した配列
	 */
	public function checkVote($lowVotes) {

		$result = array();

		// 低評価記事一覧掲載記事の配列でループ
		foreach($lowVotes as $key=>$val){
			$result[$key] = $val;
			$result[$key]["is_expanding"] = false;

			// Vote数が削除基準なら、is_expandingをtrue
			if ( intval($val['vote']) <= -3 ) {
				$result[$key]["is_expanding"] = true;
			}
		}
		return $result;
	}

	/**
	 * LVC標準配列へ変換
	 * @param $lowVotes
	 * @param bool $is_debug
	 * @return array
	 */
	public function convertLvcArray( $lowVotes, $is_debug=false ) {

		$lvcArray = array();

		// 低評価記事一覧掲載記事の配列でループ
		foreach( $lowVotes as $key=>$post ){

			// 各記事毎に、LVC標準形式に変換する
			Console::log("convert {$post['url']}", "convertLvcArray");
			$lvcPost = $this->convertLvcPost( $post );

			// LVC標準配列に追加する
			$lvcArray = array_merge( $lvcArray, $lvcPost );
		}

		// debug ////////////////////////////////////////
		if ($is_debug) {
			// デバッグ用記事配列を追加
			$lvcArray['SCP-test-JP'] =
				array (
					'post' => '2016-03-25 00:00:00',
					'del_date' => '2016-07-19 23:28:00',
					'protect' => false,
					'is_notified' => false,
					'vote' => '-100',
					'url' => 'njr-sys.net',
					'comment' => '100',
					'is_expanding' => true,
//				'is_expanding' => false,
			);
		}

		return $lvcArray;
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
		$gracePeriod = $this->calculateGracePeriod();

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
		$html = Scraping::run($url);
		preg_match_all('@(<a href="/forum/)([\s\S]*?)(" class="btn btn-default" id="discuss-button">)@', $html, $discussUrlArray);

		// 念のためスリープ
		sleep(1);

		// ソースの取得
		$html = Scraping::run( "http://ja.scp-wiki.net/forum/".$discussUrlArray[2][0] );

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
		return Scraping::convertWikidotDateToTimestamp($postDate[4][0]);
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

}