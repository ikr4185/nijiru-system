<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Models\ScpReaderAccessLogModel;
use Models\ScpJpModel;
use Models\FavoriteScpModel;
use Models\UsersModel;
use Logics\Commons\Scraping;
use Logics\Commons\WikidotApi;

/**
 * Class ScpreaderLogic
 * @package Logics
 */
class ScpreaderLogic extends AbstractLogic {

	/**
	 * @var WikidotApi
	 */
	protected $WikidotApi = null;

	/**
	 * @var ScpReaderAccessLogModel
	 */
	protected $ScpReaderAccessLog = null;

	/**
	 * @var ScpJpModel
	 */
	protected $ScpJp = null;

	/**
	 * @var FavoriteScpModel
	 */
	protected  $FavoriteScp = null;

	/**
	 * @var UsersModel
	 */
	protected  $Users = null;

	protected function getModel() {
		$this->ScpReaderAccessLog = ScpReaderAccessLogModel::getInstance();
		$this->ScpJp = ScpJpModel::getInstance();
		$this->FavoriteScp = FavoriteScpModel::getInstance();
		$this->Users = UsersModel::getInstance();

		$this->WikidotApi = new WikidotApi();
	}

	/**
	 * 一覧テンプレート生成
	 * @return string
	 */
	public function getViewTemplate() {

		$articleDataArray = $this->getArticleDataArray();
		$arrayCount = count($articleDataArray[0]);

		// テンプレート生成
//		$html = "<ul>\n";
		$ArticleArray = array();
		$i = 0;
		// カテゴリーアーカイブx2の10件は無視する
		while($i < $arrayCount){

			// 安全装置
			if ( $i >= $arrayCount ) {
				break;
			}

			// まずカウントを進める
			// 下記continue要因が配列の最後に来るので、continue前にやらないといつまで経ってもカウントが進まなくなる
			$i++;

			// カテゴリーアーカイブを飛ばす
			if (
				"joke-scps-jp" == $articleDataArray[2][$i-1] ||
				"archived-scps-jp" == $articleDataArray[2][$i-1] ||
				"scp-jp-ex" == $articleDataArray[2][$i-1] ||
				"log-of-anomalous-items-jp" == $articleDataArray[2][$i-1] ||
				"log-of-extranormal-events-jp" == $articleDataArray[2][$i-1]

			) {
				continue;
			}

			// カウントが常に1進んでいるので、-1した数で処理を実行する
			$ArticleArray[$i-1] = array(
				"item_number"    =>  preg_replace( '/[^0-9]/', '', $this->deleteTags($articleDataArray[2][$i-1]) ),
				"name"         =>  strip_tags($articleDataArray[4][$i-1]),
				"nickname"      =>  $articleDataArray[6][$i-1]
			);


		}
		return $ArticleArray;

	}

	/**
	 * 記事テンプレート生成
	 * @param $itemNumber
	 * @param string $articleData
	 * @return array
	 */
	public function getScpArray( $itemNumber, $articleData="" ) {

		// debug //////////
		echo "get_title,";

		// タイトル取得
		if ( $itemNumber < 1000) {
			$articleTitleStr = $this->getArticleTitle( $itemNumber, 1 );
		}else{
			$articleTitleStr = $this->getArticleTitle( $itemNumber, 2 );
		}

		// debug //////////
		echo "get_article,";

		// 記事取得
		if (empty($articleData)) {
			$articleData = $this->getScpArticleData( $itemNumber );
		}
		$articleArray = $this->convertArticle( $articleData, $itemNumber );

		// debug //////////
		echo "api,";

		// WikidotApi
		$pageMeta = $this->getPage( $itemNumber );

		$scpArray = array(
			"scp_num" => $itemNumber,
			"title" => $articleTitleStr,
			"item_number" => $pageMeta["title"],
			"class" => $articleArray["class"],
			"protocol" => $articleArray["protocol"],
			"description" => $articleArray["description"],
			"vote" => $pageMeta["rating"],
			"created_by" => $pageMeta["created_by"],
			"created_at" => $pageMeta["created_at"],
			"tags" => $pageMeta["tags"],
		);

		// debug //////////
		echo "scraping_done,";

		return $scpArray;
	}

	/**
	 * WikidotAPI
	 * @param $itemNumber
	 * @return array
	 */
	public function getPage( $itemNumber ) {
		$rawData = $this->WikidotApi->pagesGetMeta( "scp-jp", array("scp-{$itemNumber}-jp") );

		// retry once
		if (empty($rawData)) {
			// debug //////////
			echo "api_retry,";
			sleep(3);
			$rawData = $this->WikidotApi->pagesGetMeta( "scp-jp", array("scp-{$itemNumber}-jp") );
		}

		// データ取得失敗時(存在しない記事への参照等)
		if ( empty($rawData) ) {
			return array (
				"title"=>"[データ削除済]",
				"rating"=>"[データ削除済]",
				"created_by"=>"[データ削除済]",
				"created_at"=>"[データ削除済]",
//				"tags"=>"[データ削除済]",
			);
		}

		// 日付の修正(XMLRPC形式->Mysqlへ変換)
		if( !empty($rawData["scp-{$itemNumber}-jp"]["created_at"]) ) {
//			$rawData["scp-{$itemNumber}-jp"]["created_at"] = date( "Y-m-d H:i:s", Scraping::convertWikidotDateToTimestamp($rawData["scp-{$itemNumber}-jp"]["created_at"]) );
			$rawData["scp-{$itemNumber}-jp"]["created_at"] = date( "Y-m-d H:i:s", strtotime($rawData["scp-{$itemNumber}-jp"]["created_at"]));
		}

		// データが欠けている場合の補完(アカウント退会済み等)
		if( empty($rawData["scp-{$itemNumber}-jp"]["title"]) )         $rawData["scp-{$itemNumber}-jp"]["title"] = "[データ削除済]";
		if( empty($rawData["scp-{$itemNumber}-jp"]["rating"]) )        $rawData["scp-{$itemNumber}-jp"]["rating"] = "[データ削除済]"; // ※ここを数値にすると、Controller側のDB操作判定でバグる
		if( empty($rawData["scp-{$itemNumber}-jp"]["created_by"]) )    $rawData["scp-{$itemNumber}-jp"]["created_by"] = "[データ削除済]";
		if( empty($rawData["scp-{$itemNumber}-jp"]["created_at"]) )    $rawData["scp-{$itemNumber}-jp"]["created_at"] = "[データ削除済]";
//		if( empty($rawData["scp-{$itemNumber}-jp"]["tags"]) )          $rawData["scp-{$itemNumber}-jp"]["tags"] = "[データ削除済]";

		return $rawData["scp-{$itemNumber}-jp"];
	}

	/**
	 * SCP記事データベースの更新・保存
	 * @param $scp_num
	 * @param $title
	 * @param $item_number
	 * @param $class
	 * @param $protocol
	 * @param $description
	 * @param $vote
	 * @param $created_by
	 * @param $tags
	 * @param $created_at
	 */
	public function saveScpArray( $scp_num, $title, $item_number, $class, $protocol, $description, $vote, $created_by, $tags, $created_at ) {

		// 該当ナンバーの記事レコードがあるか検索する
		$is_exist = $this->ScpJp->selectScpJp( $scp_num );

		if ($is_exist) {
			// あれば更新
			$this->ScpJp->updateScpJp( $scp_num, $title, $item_number, $class, $protocol, $description, $vote, $created_by, $tags, $created_at, $is_exist[0]["id"] );
		}else{
			// なければ挿入
			$this->ScpJp->insertScpJp( $scp_num, $title, $item_number, $class, $protocol, $description, $vote, $created_by, $tags, $created_at );
		}
		// TODO エラー処理
	}

	/**
	 * SCP-JP記事のソフトデリート操作
	 * @param $del_flg
	 * @param $scp_num
	 * @return bool
	 */
	public function setSoftDelete( $del_flg, $scp_num ) {

		// 該当ナンバーの記事レコードがあるか検索する
		$is_exist = $this->ScpJp->selectScpJp( $scp_num );

		// あればソフトデリート
		if ($is_exist) {
			return $this->ScpJp->setSoftDelete( $del_flg, $is_exist[0]["id"] );
		}
		return false;

	}

	/**
	 * SCP-JPタイトル取得
	 * @param $itemNumber
	 * @param $series
	 * @return mixed
	 */
	private function getArticleTitle( $itemNumber, $series=1 ) {

		$html = "";
		if ( $series == 1 ) {
			$html = Scraping::run("http://ja.scp-wiki.net/scp-series-jp");
		}elseif( $series = 2 ){
			$html = Scraping::run("http://ja.scp-wiki.net/scp-series-jp-2");
		}

		preg_match( "@(<li><a href=\"/scp-{$itemNumber}-jp\">SCP-{$itemNumber}-JP</a> - )(.*?)(</li>)@i", $html, $matches);

		// 記事タイトルを返す
		if (isset($matches[2])) {
			return $matches[2];
		}
		return "[データ削除済]"; // 無かったらネタで返す

	}


	/**
	 * 記事一覧スクレイピング
	 * @return mixed
	 */
	private function getArticleDataArray()
	{
		//htmlソースの取得
		$html = Scraping::run('http://ja.scp-wiki.net/scp-series-jp'); // シリーズ1
		$html .= Scraping::run('http://ja.scp-wiki.net/scp-series-jp-2'); // シリーズ2

		// マッチング処理
		preg_match_all('@(<li><a href="/)(.*?)(">)(.*?)(</a> - )(.*?)(</li>)@', $html, $articles);

		// ソースの破棄
		unset($html);
		return $articles;
	}

	/**
	 * SCP記事スクレイピング
	 * @param $itemNumber
	 * @return string
	 */
	private function getScpArticleData($itemNumber)
	{
		$url = 'http://ja.scp-wiki.net/scp-'.$itemNumber.'-jp';
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		$result = curl_exec( $ch );

		if (curl_errno($ch)) {
			$result =  "Error: " . curl_error($ch);
		} else {
		}

		curl_close($ch);

		// 記事始まり～記事終わりまでを取得
		$result = mb_strstr($result, '<div id="page-content">', false);
		$result =  mb_strstr($result, '<div id="page-info-break"></div>', true);

		return $result;
	}

	/**
	 * 記事データ整形
	 * @param $html
	 * @param $itemNumber
	 * @return array
	 */
	private function convertArticle($html, $itemNumber){

		$rawArticleArray = array();
		$articleArray = array();

		$error ="";

		// メモリリーク防止
		$volm = strlen(bin2hex($html)) / 2;
		$volm = $volm - 35228;
		if ( $volm > 20000 ) {
			$error =  " (system: ".$volm." byte over)";
		}
		if ( $volm >= 24500) {
			$articleArray["vote"] = "System Error: Article is too long";
			$articleArray["item_number"] = "SCP-{$itemNumber}-JP" . $error;
			$articleArray["class"] = "System Error: Article is too long";
			$articleArray["protocol"] = "System Error: Article is too long";
			$articleArray["description"] = "System Error: Article is too long";
			return $articleArray;
		}

		// 各要素の抜き出し
		preg_match_all('@(<span class="number prw54353">)((.|\n)*?)(</span>)@', $html, $rawArticleArray["vote"]);
		preg_match_all('@(<p><strong>アイテム番号.*?</strong>)((.|\n)*?)(</p>)@u', $html, $rawArticleArray["item_number"]);
		preg_match_all('@(<p><strong>オブジェクトクラス.*?</strong>)((.|\n)*?)(</p>)@u', $html, $rawArticleArray["class"]);
		preg_match_all('@(<p><strong>特別収容プロトコル.*?</strong>)((.|\n)*?)(</p>(.|\n)*?<strong>(説明|内容).*?</strong>)@u', $html, $rawArticleArray["protocol"]);

		// 上記抜き出しの漏れ対応(<p>タグが入っていないケースの対応)
		if ( !isset($rawArticleArray["item_number"][2][0]) )     preg_match_all('@(<strong>アイテム番号.*?</strong>)((.)*?)(\n)@', $html, $rawArticleArray["item_number"]);
		if ( !isset($rawArticleArray["class"][2][0]) )          preg_match_all('@(<strong>オブジェクトクラス.*?</strong>)((.)*?)(\n)@u', $html, $rawArticleArray["class"]);
		if ( !isset($rawArticleArray["protocol"][2][0]) )       preg_match_all('@(<strong>特別収容プロトコル.*?</strong>)((.|\n)*?)(<strong>(説明|内容).*?</strong>)@u', $html, $rawArticleArray["protocol"]);

		// 特別収容プロトコルまでが正常に取得できたら、説明文の取得処理を実行
		if ( isset($rawArticleArray["protocol"][2][0]) ){

			// 生htmlから、特別収容プロトコル以降～タグを抽出
			$rawDescription = mb_strstr($html, $rawArticleArray["protocol"][2][0], false);
			$rawDescription = str_replace( $rawArticleArray["protocol"][2][0], "", $rawDescription );
			$rawDescription = mb_strstr( $rawDescription, "<div class=\"page-tags\">", true );
			
			// 「説明」が長すぎる場合のメモリーリーク対策
			if ( ( strlen(bin2hex($rawDescription))/2 ) > 36000) {

				// 特別収容プロトコルを除いた残りが長すぎる場合
				$rawArticleArray["description"] = array();
				$rawArticleArray["description"][3][0] = $rawDescription;

			}else{

				// 正常な長さの場合
				preg_match_all('@(<p><strong>(説明|内容).*?</strong>)((.|\n)*?)(<div class="page-tags">)@u', $html, $rawArticleArray["description"]);

				// 取得漏れ対策(<p>タグが入っていないケースの対応)
				if ( !isset($rawArticleArray["description"][3][0]) )    preg_match_all('@(<strong>(説明|内容).*?</strong>)((.|\n)*?)(<div class="page-tags">)@u', $html, $rawArticleArray["description"]);

			}
			unset($rawDescription);
		}

		// 生htmlを削除
		unset($html);

		// それでもダメなら[データ削除済]
		if ( !isset($rawArticleArray["vote"][2][0]) )            $rawArticleArray["vote"][2][0]="[データ削除済]";
		if ( !isset($rawArticleArray["item_number"][2][0]) )      $rawArticleArray["item_number"][2][0]="[データ削除済]";
		if ( !isset($rawArticleArray["class"][2][0]) )           $rawArticleArray["class"][2][0]="[データ削除済]";
		if ( !isset($rawArticleArray["protocol"][2][0]) )    $rawArticleArray["protocol"][2][0]="[データ削除済]";
		if ( !isset($rawArticleArray["description"][3][0]) )     $rawArticleArray["description"][3][0]="[データ削除済]";

		// 不要なhtmlタグ除去
		$articleArray["vote"] = $this->deleteTags( $rawArticleArray["vote"][2][0] );
		$articleArray["item_number"] = $this->deleteTags( $rawArticleArray["item_number"][2][0] ) . $error;
		$articleArray["class"] = $this->deleteTags( $rawArticleArray["class"][2][0] );
		$articleArray["protocol"] = $this->deleteTags( $rawArticleArray["protocol"][2][0] );
		$articleArray["description"] = $this->deleteTags( $rawArticleArray["description"][3][0] );

		// <br>タグの整理
		$articleArray["protocol"] = $this->deleteBr($articleArray["protocol"]);
		$articleArray["description"] = $this->deleteBr($articleArray["description"]);

		return $articleArray;

	}

	/**
	 * <br>タグの整理
	 * @param $article
	 * @return mixed
	 */
	private function deleteBr($article)
	{

		// <table ~ </table>の間の<br>を削除する
		$article = preg_replace( '@(<table(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<tr(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<th(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<td(.|\s)*?>)(<br>)@', "$1", $article );

		$article = preg_replace( '@(</table>)(<br>)*@', "$1".'', $article );
		$article = preg_replace( '@(</tr>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(</th>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(</td>)(<br>)@', "$1", $article );

		// <ul><ol>内の<br>を削除する
		$article = preg_replace( '@(<ul(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<ol(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<li(.|\s)*?>)(<br>)*@', "$1", $article );

		$article = preg_replace( '@(</ul>)(<br>)*@', "$1".'', $article );
		$article = preg_replace( '@(</ol>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(</li>)(<br>)*@', "$1", $article );

		// <a class="collapsible-block-link">前後の<br>を削除する
		$article = preg_replace( '@(<br>|\n)*(<a class="collapsible-block-link"(.|\s)*?>)(.*?)(</a>)(<br>|\n)*@', "<br>\n<br>\n<br>\n$2$4$5<br>\n<br>\n<br>\n", $article );

		// <blockquote>周辺の<br>を整理
		$article = preg_replace( '@(<br>|\n)*(<blockquote>)(<br>|\n)*@', "<br>\n<br>\n$2", $article );
		$article = preg_replace( '@(<br>|\n)*(</blockquote>)(<br>|\n)*@', "$2<br>\n", $article );

		// <br>タグの短縮
		$article = preg_replace( '/(<br>\s*){3,}/', "<br>\n<br>\n<br>\n", $article );

		return $article;
	}


	/**
	 * htmlタグ除去
	 * @param $html
	 * @return string
	 */
	private function deleteTags($html){

		// <div> エスケープ

		//      タブ
		$html = preg_replace( '/(<div\sid="wiki-tab-\d-\d"\sstyle="display:none">)([\s\S]*?)(<\/div>\n)/', "@@wiki-tab@@$2@@/div@@", $html );
		$html = preg_replace( '/(<div\sclass="yui-content">)([\s\S]*?)(<\/div>\n)/', "@@yui-content@@$2@@/div@@", $html );

		//      折りたたみ
		$html = preg_replace( '/(<div\sclass="collapsible-block-folded">)([\s\S]*?<\/a>)(<\/div>\n)/', "@@collapsible-block-folded@@$2@@/div@@", $html );
		$html = preg_replace( '/(<div\sclass="collapsible-block-unfolded-link">)([\s\S]*?<\/a>)(<\/div>\n)/', "@@collapsible-block-unfolded-link@@$2@@/div@@", $html );

		$html = preg_replace( '/(<div\sclass="collapsible-block-unfolded"\sstyle="display:none">\n)/', "@@collapsible-block-unfolded@@", $html );
		$html = preg_replace( '/(<div\sclass="collapsible-block-content">\n)/', "@@collapsible-block-content@@", $html );
		$html = preg_replace( '/(<div\sclass="collapsible-block">\n)/', "@@collapsible-block@@", $html );

		//      共通
		$html = preg_replace( '/(<\/div>\n<\/div>\n<\/div>\n)/', "@@/div/div/div@@", $html );

		// 不要タグ削除
		$html = strip_tags( $html,'<strong><span><em><a><ul><li><blockquote><table><tr><th><td>' );

		// <div> 差し戻し
		$html = str_replace( "@@/div/div/div@@", "</div></div></div>", $html );

		$html = str_replace( "@@collapsible-block@@", "<div class=\"collapsible-block\">", $html );
		$html = str_replace( "@@collapsible-block-content@@", "<div class=\"collapsible-block-content\">", $html );
		$html = str_replace( "@@collapsible-block-unfolded@@", "<div class=\"collapsible-block-unfolded\" style=\"display:none\">", $html );

		$html = str_replace( "@@collapsible-block-unfolded-link@@", "<div class=\"collapsible-block-unfolded-link\">", $html );
		$html = str_replace( "@@collapsible-block-folded@@", "<div class=\"collapsible-block-folded\">", $html );

		$html = str_replace( "@@/div@@", "</div>", $html );

		$html = str_replace( array("\r\n", "\n", "\r"), "<br><br>\n", $html );

		$divStart = mb_substr_count( $html, "<div" );
		$divEnd = mb_substr_count( $html, "</div>" );

		// <div> の数揃え ※暫定
		//TODO そのうちなおす
		if ( $divStart > $divEnd) {

			$count = $divStart - $divEnd;
			$i = 0;
			while ( $i < $count) {
				$html .= "</div><!-- force close -->\n";
				$i++;
			}

		}
		if ( $divStart < $divEnd) {

			$count = $divEnd - $divStart;
			$i = 0;
			while ( $i <= $count) {
				$html = preg_replace('/(.*)(<\/div>)/', '$1', $html, 1 );
				$i++;
			}

		}


		return $html;
	}

	/**
	 * お気に入り済みかのチェック
	 * @param $user_id
	 * @param $itemNumber
	 * @return bool
	 */
	public function checkFavoriteScp($user_id, $itemNumber){

		// お気に入りデータ読み込み
		$is_favorite = $this->FavoriteScp->checkEnableFavoriteScp( $user_id, $itemNumber );

		// 判定
		if ( "0" == $is_favorite ) {
			return false;
		}
		return true;
	}

	/**
	 * お気に入り登録/解除
	 * @param $user_id
	 * @param $is_enable
	 * @param $itemNumber
	 */
	public function setFavoriteScp( $user_id, $is_enable, $itemNumber )
	{

		$is_enable = $is_enable-1;

		// お気に入り登録が、有効/無効問わず、されているかチェック
		$is_exist = $this->FavoriteScp->checkFavoriteScp( $user_id, $itemNumber );

		// IDからユニークナンバーを取得、insert実行
		$usersNum = $this->Users->getNumberById($user_id);

		// 登録済みならis_enableの変更
		if ( $is_exist > 0 ) {

			$flag = $this->FavoriteScp->updateFavoriteScp( $is_enable, $usersNum, $itemNumber );

		} else {

			// 新規登録時のみ、行追加
			if ( $is_enable > 0 ) {

				$flag = $this->FavoriteScp->insertFavoriteScp( $usersNum, $itemNumber );

			}else {
				return;
			}
		}

		// 書き込みチェック
		if (!$flag){
			$this->setMsg("書き込みに失敗しました");
		} else {
			if (empty($is_enable)) {
				$this->setMsg("お気に入り解除しました");
			}else{
				$this->setMsg("お気に入りしました");
			}
		}
	}

	/**
	 * お気に入り読み込み
	 * @param $users_id
	 * @return array
	 */
	public function loadFavoriteScp($users_id) {

		// IDからユニークナンバーを取得
		$usersNum = $this->Users->getNumberById($users_id);

		// お気に入りデータ読み込み
		$dataArray = $this->FavoriteScp->selectFavoriteScp( $usersNum );

		if (!$dataArray) {
			$this->setMsg("お気に入り記録がありません");
		}

		return $dataArray;

	}

	public function saveReaderLog( $users_id, $url ) {

		// IDからユニークナンバーを取得
		$usersNum = $this->Users->getNumberById($users_id);

		return $this->ScpReaderAccessLog->saveReaderLog($usersNum, $url);
	}

	public function getReaderLog( $users_id ) {

		// IDからユニークナンバーを取得
		$users_number = $this->Users->getNumberById($users_id);

		return $this->ScpReaderAccessLog->getReaderLog($users_number);
	}

	/**
	 * あいまい検索
	 * @param $search
	 * @return array
	 */
	public function searchScpJp( $search ) {
		$result = array();

		$result["title"] = $this->ScpJp->selectScpJpWhereTitle ( $search );
		$result["protocol"] = $this->ScpJp->selectScpJpWhereProtocol ( $search );
		$result["description"] = $this->ScpJp->selectScpJpWhereDescription ( $search );
		$result["created_by"] = $this->ScpJp->selectScpJpWhereCreatedBy ( $search );
		$result["tags"] = $this->ScpJp->selectScpJpWhereTags ( $search );

		return $result;
	}
	
	
	
	/**
	 * cliMtfChecker : 検索
	 * @param $search
	 * @return array
	 */
	public function searchScpJpNoLimit( $search ) {
		$result = array();
		
		$result["protocol"] = $this->ScpJp->selectScpJpWhereProtocolNoLimit( $search );
		$result["description"] = $this->ScpJp->selectScpJpWhereDescriptionNoLimit ( $search );
		
		return $result;
	}
}