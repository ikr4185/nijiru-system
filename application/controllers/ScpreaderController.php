<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\ScpreaderLogic;
use Inputs\BasicInput;


/**
 * Class ScpreaderController
 * @package Controllers
 */
class ScpreaderController extends AbstractController {
	
	/**
	 * @var ScpreaderLogic
	 */
	protected $logic;
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->logic = new ScpreaderLogic();
	}
	
	protected function getInput() {
		$this->input = new BasicInput();
	}

	// お気に入りデータ
	private $dataArray = array();
	
	// お気に入り済み記事のフラッグ
	private $is_favorite = false;
	
	// ログデータ
	private $logArray = array();
	
	public function indexAction() {
		
		// 記事読み込み
		$ArticleArray = $this->logic->getViewTemplate();
		
		$result = array(
			"ArticleArray"  => $ArticleArray,
			"msg"   => $this->logic->getMsg(),
		);
		$jsPathArray = array(
			"http://njr-sys.net/application/views/assets/js/scp_reader_search.js",
		);
		$this->getView( "index", "SCP-JP-Reader", $result, $jsPathArray );
		
	}
	
	/**
	 * 記事読み込み
	 * @param $itemNumber
	 */
	public function ScpAction($itemNumber) {

		// 不正値対策
		if (! is_numeric($itemNumber)) {
			echo "<h1>Error: invalid value</h1>\n\n<hr>\nnjr-sys.net";
			exit;
		}
		
		// ユーザーIDを取得(非ログイン時はNull)
		$user_id = $this->input->getSession("id");
		
		// ユーザーログイン時の挙動
		$is_favorite = false;
		if ($user_id) {
		
			// お気に入り登録処理
			if ( $this->input->checkRequest("submit_favorite-scp") ) {

				$this->logic->setFavoriteScp(
					$user_id,
					$this->input->getRequest("favorite-scp"),
					$itemNumber
				);
			}
			
			// お気に入り済み記事かのチェック
			$is_favorite = $this->logic->checkFavoriteScp($user_id,$itemNumber);
			
			// 閲覧ログ保存
			$this->logic->saveReaderLog( $user_id, $itemNumber );

		}
		
		// 記事読み込み
		$scpArray = $this->logic->getScpArray( $itemNumber );

		// 読み込んだ記事をDBに保存しておく
		if ( is_numeric($scpArray["vote"]) ) {

			$this->logic->saveScpArray(
				$scpArray["scp_num"],
				$scpArray["title"],
				$scpArray["item_number"],
				$scpArray["class"],
				$scpArray["protocol"],
				$scpArray["description"],
				$scpArray["vote"],
				$scpArray["created_by"],
				serialize($scpArray["tags"]),
				$scpArray["created_at"]
			);

			// ソフトデリート解除
			$this->logic->setSoftDelete( 0, $itemNumber );
		}else{

			// Voteが数字以外なら、記事読み込み失敗と判断してソフトデリートする
			$this->logic->setSoftDelete( 1, $itemNumber );
		}
		
		// 404 を 403 する (ネタ)
		if ("404" == $itemNumber) {
			echo "<h1>Error: Not Found</h1>\nRedirect to SCP_Foundation_JP after 2 seconds\n<hr>\nnjr-sys.net";
			sleep(2);
			header("Location: http://ja.scp-wiki.net/scp-404-jp");
			exit;
		}

		$result = array(
			"scpArray"  => $scpArray,
			"is_favorite"  => $is_favorite,
			"msg"   => $this->logic->getMsg(),
		);
		$jsPathArray = array(
			"http://njr-sys.net/application/views/assets/js/toggle.js",
		);
				
		$this->getView( "scp", "SCP-{$itemNumber}-JP", $result, $jsPathArray );
	}
	
	/**
	 * SCP-JP-Reader利用履歴
	 */
	public function LogAction() {
		
		$records = array();
		
		// ログインチェック
		if( $this->input->checkLogin() ){
			
			// ログ読み込み
			$records = $this->logic->getReaderLog( $this->input->getSession("id") );
			
		}

		$result = array(
			"records"  => $records,
			"msg"   => $this->logic->getMsg(),
		);
		$jsPathArray = array(
			"http://njr-sys.net/application/views/assets/js/toggle.js",
		);
		$this->getView( "log", "SCP-JP Reading Log", $result, $jsPathArray );
	}
	
	/**
	 * お気に入り一覧
	 */
	public function favoritesAction() {
		
		// ユーザーIDを取得(非ログイン時はNull)
		$user_id = $this->input->getSession("id");
			
		// お気に入りデータ取得
		$records = array();
		if ($user_id) {
			$records = $this->logic->loadFavoriteScp($user_id);
		}
				
		$result = array(
			"records"  => $records,
			"msg"   => $this->logic->getMsg(),
		);
		$this->getView( "favorites", "SCP-JP My Favorite SCP", $result );
	}
	
	/**
	 * あいまい検索
	 */
	public function searchAction() {
		
		$records = array();
		
		// 検索クエリを取得
		$search = $this->input->getRequest("search");
		
		if ( $search !== false && $search !== null && $search !== "" ) {
			
			// 検索実行
			$records = $this->logic->searchScpJp( $search );
			
			// セッションにクエリを保存
			$this->input->setSession("search",$search);
		}
		
		// 検索クエリのセッションを取得
		$search = $this->input->getSession("search");
		
		$result = array(
			"records"  => $records,
			"search"  => $search,
			"msg"   => $this->logic->getMsg(),
		);
		$this->getView( "search", "SCP-JP 曖昧検索", $result );
	}
	
}