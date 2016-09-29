<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\ContactLogic;
use Inputs\BasicInput;


/**
 * Class ContactController
 */
class ContactController extends AbstractController
{
	
	/**
	 * @var ContactLogic
	 */
	protected $logic;
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->logic = new ContactLogic();
	}
	
	protected function getInput() {
		$this->input = new BasicInput();
	}
	
	public function indexAction() {

		// POSTを受け取ったらconfirmへリダイレクト
		if ( $this->input->isPost() ){
			
			// 配列作成
			$contact = array(
				"name"  =>  $this->input->getRequest("name"),
				"mail"  =>  $this->input->getRequest("mail"),
				"subject"  =>  $this->input->getRequest("subject"),
				"text"  =>  $this->input->getRequest("text"),
			);
			// バリデーション
			$contact = $this->logic->validation($contact);

			if ($contact) {

				// セッション格納
				$this->input->setSession("contact",$contact);

				// リダイレクト
				$this->redirect("contact","confirm");
			}
		}
		
		// セッション読み込み
		$contact = $this->input->getSession("contact");
		
		$result = array(
			"contact"   => $contact,
			"msg"       => $this->logic->getMsg(),
		);
		$this->getView( "index", "お問い合わせ", $result );
	}
	
	public function confirmAction() {
		
		// セッションが無ければ、リダイレクト
		if ( !$this->input->checkSession("contact") ) {
			$this->redirect("contact");
		}
		
		// セッション読み込み
		$contact = $this->input->getSession("contact");
		
		// 送信確定時、メール送信してリダイレクト
		if ( $this->input->isPost() ) {

			// メール送信
			$contact_id = $this->logic->sendMail( $contact );

			// セッション格納
			$this->input->setSession("contact",$contact);

			// リダイレクト
			$this->redirect("contact","done",$contact_id);
		}
		
		$result = array(
			"contact"   => $contact,
		);
		$this->getView( "confirm", "確認画面", $result );
	}
	
	public function doneAction($contact_id) {
		
		// セッションが無ければ、リダイレクト
		if ( !$this->input->checkSession("contact") ) {
			$this->redirect("contact");
		}
		
		// セッション削除
		$this->input->delSession("contact");

		$result = array(
			$contact_id   => $contact_id,
		);
		$this->getView( "done", "送信しました", $result );
	}
	
}