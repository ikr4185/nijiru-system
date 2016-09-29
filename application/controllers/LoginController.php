<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\LoginLogic;
use Inputs\BasicInput;


/**
 * Class LoginController
 * @package Controllers
 */
class LoginController extends AbstractController {
	
	/**
	 * @var LoginLogic
	 */
	protected $logic;
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->logic = new LoginLogic();
	}
	
	protected function getInput() {
		$this->input = new BasicInput();
	}
	
	public function indexAction() {
		// TODO 未使用
	}
	
	/**
	 * ログイン
	 */
	public function loginAction() {

		// 処理振り分け
		if ($this->input->checkRequest("login")) {

			// POST取得
			$id = $this->input->getRequest("id");
			$pass = $this->input->getRequest("pass");
			$postRedirectTo = $this->input->getRequest("redirectTo");

			// ログイン
			$this->loginFunction($id,$pass);

			// リダイレクト先の指定があれば、リダイレクト実行
			if ( !empty($postRedirectTo) ) {
				$this->redirectTo( "http://njr-sys.net".\Cores\Helper\UrlHelper::revertGetParam($postRedirectTo) );
			}

		}elseif($this->input->checkRequest("logout")){

			$this->logoutFunction();

		}

		$result = array(
			"sessId"    =>  $this->input->getSession("id"),
//			"sessPass"    =>  $this->input->getSession("pass"),
			"redirectTo"  => $this->input->getRequest("to", true), // GET["to"](リダイレクト先の生データ)
			"msg"   => $this->logic->getMsg(),
		);
		$this->getView( "login", "ログイン", $result );
	}

	/**
	 * ログイン: ログイン処理
	 * @param $id
	 * @param $pass
	 * @param bool $is_welcome
	 */
	protected function loginFunction($id,$pass,$is_welcome=true) {

		// 入力値検証
		if ( ! $idInfo = $this->checkPost($id, $pass) ) return;

		// セッションを新規に発行
		$this->createUserSession($idInfo);

		// ログインボーナスの計算と検証
		if ( ! $this->logic->checkLoginBonus($id) ) return;

		// ニジポ取得
		$point = $this->logic->getPoint($id);

		// ニジポをセッションに格納
		$this->input->setSession("point", $point);

		// Welcomeメッセージ生成
		if ($is_welcome) {
			$this->logic->setWelcomeMsg($idInfo["user_name"],$point);
		}

		// 最終ログイン日時保存
		$this->logic->setLastLogin($id);
	}

	/**
	 * ログイン: ユーザーセッションの新規発行
	 * @param $idInfo
	 */
	protected function createUserSession($idInfo) {
		$this->input->setSession("number", $idInfo["number"]);
		$this->input->setSession("id", $idInfo["id"]);
		$this->input->setSession("pass", $idInfo["password"]);
		$this->input->setSession("user_name", $idInfo["user_name"]);
	}

	/**
	 * ログイン: 入力されたユーザ情報の検証
	 * @param $id
	 * @param $pass
	 * @return bool|mixed|string
	 */
	protected function checkPost($id, $pass) {

		// バリデートチェック
		if ( ! $this->logic->validate($id,$pass) ) return false;

		// ユーザー情報の取得
		if ( ! $idInfo = $this->logic->getIdInfo($id) ) return false;

		// パスワード照合
		if ( ! $this->logic->checkPass($pass, $idInfo["password"]) ) return false;

		return $idInfo;
	}

	/**
	 * ログイン: ログアウト処理
	 */
	protected function logoutFunction() {

		// SeeYouメッセージ生成
		$this->logic->setSeeYouMsg($this->input->getSession("id"));

		// ユーザーセッションの破棄
		$this->delUserSession();

		// クッキー削除
		$this->input->delCookie();
	}

	/**
	 * ログイン: ユーザーセッションの破棄
	 */
	protected function delUserSession() {
		$this->input->delSession("number");
		$this->input->delSession("id");
		$this->input->delSession("pass");
		$this->input->delSession("id");
	}
	
	/**
	 * 新規登録
	 */
	public function registerAction() {
		
		if ( $this->input->isPost() ) {
			
			// POST取得
			$id = $this->input->getRequest("id");
			$pass = $this->input->getRequest("pass");
			$checkPass = $this->input->getRequest("checkPass");
			$name = $this->input->getRequest("name");

			if ( $this->logic->register($id,$pass,$checkPass,$name) ) {
				// 早速ログイン
				$this->loginFunction($id,$pass);
			}
		}

		$result = array(
			"msg"   => $this->logic->getMsg(),
		);
		$this->getView( "register", "新規登録", $result );
	}
	
	/**
	 * 設定変更
	 */
	public function updateAction() {
		
		// Session取得
		$id = $this->input->getSession("id");
		
		// POST取得
		$pass = $this->input->getRequest("pass");
		$newName = $this->input->getRequest("newName");
		$publication = $this->input->getRequest("publication");

		// 公開状態の取得
		$is_public = $this->logic->getPublication($id);
		
		// 処理振り分け: 初期値
		$is_success = false;
		
		// 処理振り分け: ユーザ情報設定
		if ( !is_null($this->input->getRequest("submit_user_data")) ) {
			$is_success = $this->logic->updateUserData( $id,$pass,$newName );
		}
		
		// 処理振り分け: 公開度設定
		if ( !is_null($this->input->getRequest("submit_publication")) ) {
			$is_success = $this->logic->updateUserPublication( $id,$publication );
		}
		
		// 処理成功なら再ログイン
		if ($is_success) {
			$this->loginFunction($id,$pass,false);
		}
		
		$result = array(
			"is_public" => $is_public,
			"msg"   => $this->logic->getMsg(),
		);
		$this->getView( "update", "設定変更", $result );
		
	}

}