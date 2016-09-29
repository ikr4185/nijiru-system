<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\WebAppsLogic;
use Inputs\BasicInput;


/**
 * Class WebAppsController
 * ニジルシステムWEBアプリケーション
 * @package Controllers
 */
class WebAppsController extends AbstractController {
	
	/**
	 * @var WebAppsLogic
	 */
	protected $logic;
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->logic = new WebAppsLogic();
	}
	
	protected function getInput() {
		$this->input = new BasicInput();
	}
	
	public function indexAction() {

		// 国へ帰るんだな
		$this->redirect("index");
		
	}

	/**
	 * SCP-Search
	 */
	public function scpSearchAction() {
		
		// ポストされたらリダイレクト
		if ( $this->input->isPost() ) {
			
			$inputNumber = $this->input->getRequest("scp_search");
			
			if($this->logic->validateScpSearch( $inputNumber )){
				$url = "http://scpjapan.wiki.fc2.com/wiki/SCP-" . $inputNumber;
				$this->redirectTo($url);
			}
		}
		
		$result = array(
			"msg"   => $this->logic->getMsg(),
		);
		$jsPathArray = array(
			"http://njr-sys.net/application/views/assets/js/scp_search.js",
		);
		$this->getViewWebApps( "scp_search", "WebApps", $result, $jsPathArray );
		
	}
	
	
	
	
}