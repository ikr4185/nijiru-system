<?php
namespace Logics\Commons;
// use Logics\Commons\Api;

/**
 * Class WikidotApi
 * @package Logics\Commons
 * @see http://www.wikidot.com/doc:api
 * @see http://developer.wikidot.com/doc:api
 */
class WikidotApi extends Api {

	// ==========================================================================================
	// Wikidot API Wrappers
	// ----------------------------------------
	// 'system.listMethods', 'system.methodHelp', 'system.methodSignature', 'system.multicall',
	// 'categories.select',
	// 'tags.select',
	// 'pages.select', 'pages.get_meta', 'pages.get_one', 'pages.save_one',
	// 'files.select', 'files.get_meta', 'files.get_one', 'files.save_one',
	// 'users.get_me',
	// 'posts.select', 'posts.get'
	// ==========================================================================================

	/**
	 * サイト内に存在するカテゴリ名の一覧を取得
	 * $api->categoriesSelect( "sugoi-chirimenjako-pain" );
	 *
	 * @param $site string
	 * @return array|bool
	 */
	public function categoriesSelect( $site ) {
		$arg = array(
			"site" => $site,
		);
		return $this->xml_rpc( 'categories.select', $arg );
	}

	/**
	 * 添付されたファイル名の一覧を取得
	 * $api->filesSelect( "sugoi-chirimenjako-pain", "test-2016-09-03-pages-save-one-001" );
	 *
	 * @param $site string
	 * @param $page string
	 * @return array|bool
	 */
	public function filesSelect( $site, $page ) {
		$arg = array(
			"site" => $site,
			"page" => $page,
		);
		return $this->xml_rpc( 'files.select', $arg );
	}

	/**
	 * ファイルの情報を取得
	 * $api->filesGetMeta( "sugoi-chirimenjako-pain", "test-2016-09-03-pages-save-one-001", array("nijiru-icon.png") );
	 *
	 * @param $site string
	 * @param $page string
	 * @param $files array
	 * @return array|bool
	 */
	public function filesGetMeta( $site, $page, $files ) {
		$arg = array(
			"site" => $site,
			"page" => $page,
			"files" => $files,
		);
		return $this->xml_rpc( 'files.get_meta', $arg );
	}

	/**
	 * ファイルダウンロード
	 * $fileDataArray = $api->filesGetOne( "sugoi-chirimenjako-pain", "test-2016-09-03-pages-save-one-001", "nijiru-icon.png" );
	 * $file = base64_decode($fileDataArray["content"]);
	 *
	 * @param $site string
	 * @param $page string
	 * @param $file string
	 * @return array|bool
	 */
	public function filesGetOne( $site, $page, $file ) {
		$arg = array(
			"site" => $site,
			"page" => $page,
			"file" => $file,
		);
		return $this->xml_rpc( 'files.get_one', $arg );
	}

	/**
	 * ファイルアップロード
	 * $path = "/home/njr-sys/public_html/application/views/assets/img/common/nijiru-icon.png";
	 * return $api->filesSaveOne( "sugoi-chirimenjako-pain", "test-2016-09-03-pages-save-one-001", "nijiru-icon.png", file_get_contents($path) );
	 *
	 * With this method you can attach files not bigger than 50MB. Other file size limits also apply:
	 *  * site storage — can't upload file bigger than current unused file storage for site
	 *	* maximum file size depending on free/Pro Wikidot plan
	 * @param $site string
	 * @param $page string
	 * @param $file string name of file to attach
	 * @param $content string base64-encoded file content / file_get_contents等で取得したファイルデータ
	 * @param string $revision_comment
	 * @param string $comment
	 * @param string $save_mode
	 * @param bool $notify_watchers
	 * @return array|bool
	 */
	public function filesSaveOne( $site, $page, $file, $content, $revision_comment="", $comment="", $save_mode="create_or_update",  $notify_watchers=false ) {

		$arg = array(
			"site" => $site,
			"page" => $page,
			"file" => $file,
			"content" => base64_encode($content),
		);

		// optional
		if (!empty($revision_comment))  $arg["revision_comment"] = $revision_comment;
		if (!empty($comment))  $arg["comment"] = $comment;
		if (!empty($save_mode))         $arg["save_mode"] = $save_mode;
		if ($notify_watchers) $arg["notify_watchers"] = true;

		return $this->xml_rpc( 'files.save_one', $arg );
	}

	/**
	 * 条件検索: タグ
	 * $api->tagsSelect( "scp-jp" );
	 *
	 * @param $site string
	 * @param $categories array
	 * @param $pages array
	 * @return array|bool
	 */
	public function tagsSelect( $site, $categories=array(), $pages=array() ) {

		$arg = array(
			"site" => $site,
		);

		// optional
		if (!empty($categories)) $arg["categories"] = $categories;
		if (!empty($pages)) $arg["pages"] = $pages;

		return $this->xml_rpc( 'tags.select', $arg );
	}

	/**
	 * 条件検索: 記事urlを取得
	 * $api->pagesSelect("scp-jp",null,null,null,null,null,null,"ikr_4185");
	 *
	 * @param $site string
	 * @param string $pagetype default "*"
	 * @param array $categories
	 * @param array $tags_any
	 * @param array $tags_all
	 * @param array $tags_none
	 * @param string $parent
	 * @param string $created_by
	 * @param int $rating
	 * @param string $order
	 * @return array|bool
	 */
	public function pagesSelect( $site, $pagetype="", $categories=array(), $tags_any=array(), $tags_all=array(), $tags_none=array(), $parent="", $created_by="", $rating=null, $order="created_by") {

		$arg = array(
			"site" => $site,
		);

		// optional
		if (!empty($pagetype)) $arg["pagetype"] = $pagetype;
		if (!empty($categories)) $arg["categories"] = $categories;
		if (!empty($tags_any)) $arg["tags_any"] = $tags_any;
		if (!empty($tags_all)) $arg["tags_all"] = $tags_all;
		if (!empty($tags_none)) $arg["tags_none"] = $tags_none;
		if (!empty($parent)) $arg["parent"] = $parent;
		if (!empty($created_by)) $arg["created_by"] = $created_by;
		if (!empty($rating)) $arg["rating"] = $rating;
		if (!empty($order)) $arg["order"] = $order;

		return $this->xml_rpc( 'pages.select', $arg );
	}

	/**
	 * 記事の情報を取得( 本文無し、複数可 )
	 * $api->pagesGetMeta( "scp-jp", array("scp-549-jp") );
	 *
	 * @param $site string
	 * @param $pages array
	 * @return array|bool
	 */
	public function pagesGetMeta( $site, $pages ) {
		$arg = array(
			"site" => $site,
			"pages" => $pages,
		);
		return $this->xml_rpc( 'pages.get_meta', $arg );
	}

	/**
	 * 記事の情報を取得
	 * $api->pagesGetOne( "sugoi-chirimenjako-pain", "test-2016-09-02-api" )
	 * $api->pagesGetOne( "scp-jp", "scp-549-jp" );
	 *
	 * @param $site string
	 * @param $page string
	 * @return array|bool
	 */
	public function pagesGetOne( $site, $page ) {
		$arg = array(
			"site" => $site,
			"page" => $page,
		);
		return $this->xml_rpc( 'pages.get_one', $arg );
	}

	/**
	 * 記事投稿
	 * $api->pagesSaveOne(
	"sugoi-chirimenjako-pain",
	"test-2016-09-03-pages-save-one-001",
	$title = "Test Nijiru System Post Page 001",
	$content = "API , masaka no multi byte hi taiou toka dare ga yosou sita darou ka\n文字化けしちゃうのおお\n".date("Y-m-d H:i:s")."\n".$_SERVER['HTTP_USER_AGENT'],
	$tags = array("njr-sys", "test"),
	$revision_comment = "NijiruSystem Post Test by ikr_4185"
	);
	 * ※ MultiByteに非対応の模様…wikidot～～～！！！
	 *
	 * @param $site string
	 * @param $page string
	 * @param string $title
	 * @param string $content
	 * @param array $tags
	 * @param string $parent_fullname
	 * @param string $save_mode default:"create_or_update"
	 * @param string $rename_as
	 * @param string $revision_comment
	 * @param bool $notify_watchers
	 * @return array|bool
	 */
	public function pagesSaveOne( $site, $page, $title, $content, $tags, $revision_comment="", $parent_fullname=null, $save_mode="", $rename_as="", $notify_watchers=false ) {

		$arg = array(
			"site" => $site,
			"page" => $page,
			"title" => $title,
			"content" => $content,
			"tags" => $tags,
		);

		// optional
		if (!empty($revision_comment))  $arg["revision_comment"] = $revision_comment;
		if (!empty($parent_fullname))   $arg["parent_fullname"] = $parent_fullname;
		if (!empty($save_mode))         $arg["save_mode"] = $save_mode;
		if (!empty($rename_as)) $arg["rename_as"] = $rename_as;
		if ($notify_watchers) $arg["notify_watchers"] = true;

		return $this->xml_rpc( 'pages.save_one', $arg );
	}

	/**
	 * 条件検索: ポストIDを取得
	 * $api->postsSelect( "sugoi-chirimenjako-pain", null, "2543560" );
	 *
	 * @param $site string
	 * @param string $page page to get comments from
	 * @param string $reply_to only select comments/posts that are direct replies to this one ("-" means not replies to other posts/comments)
	 * @param string $created_by select posts by this user
	 * @param string $thread thread to get posts from — not yet implemented
	 * @return array|bool
	 */
	public function postsSelect( $site, $page="", $reply_to="", $created_by="", $thread=null ) {

		$arg = array(
			"site" => $site,
		);

		// optional
		if (!empty($page))          $arg["page"] = $page;
		if (!empty($reply_to))      $arg["reply_to"] = $reply_to;
		if (!empty($created_by))    $arg["created_by"] = $created_by;

		return $this->xml_rpc( 'posts.select', $arg );
	}

	/**
	 * ポストを取得
	 * $api->postsGet( "sugoi-chirimenjako-pain", array("2543560") );
	 *
	 * @param $site string
	 * @param array $posts list of IDs of posts/comments to get (max 10 of them)
	 * @return array|bool
	 */
	public function postsGet( $site, $posts ) {
		$arg = array(
			"site" => $site,
			"posts" => $posts,
		);
		return $this->xml_rpc( 'posts.get', $arg );
	}

	/**
	 * API Key の所有アカウントを取得
	 * $api->usersGetMe()
	 *
	 * @return array|bool
	 */
	public function usersGetMe() {
		$arg = array(
		);
		return $this->xml_rpc( 'users.get_me', $arg );
	}

}