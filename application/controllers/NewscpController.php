<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\NewscpLogic;
use Inputs\BasicInput;


/**
 * Class NewscpController
 * @package Controllers
 */
class NewscpController extends AbstractController {
	
	/**
	 * @var NewscpLogic
	 */
	protected $logic;
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->logic = new NewscpLogic();
	}
	
	protected function getInput() {
		$this->input = new BasicInput();
	}
	
	
	public function indexAction() {
		
		// 記事読み込み
		$data=array();
		for ($page = 1; $page <= 3; $page++){
			$data[$page] = $this->logic->getArticleDataArray( 'http://ja.scp-wiki.net/most-recently-created/p/', $page);
		}
		
		$result = array(
			"data"  => $data,
			"msg"   => $this->logic->getMsg(),
		);
		$this->getView( "index", "最近作成された記事", $result );
	}

	
	public function jpAction() {

		// 記事読み込み
		$data=array();
		for ($page = 1; $page <= 3; $page++){
			$data[$page] = $this->logic->getArticleDataArray( 'http://ja.scp-wiki.net/most-recently-created-jp/p/', $page);
		}		

		$result = array(
			"data"  => $data,
			"msg"   => $this->logic->getMsg(),
		);
		$this->getView( "jp", "最新のJP記事一覧", $result );
	}
}