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
			"http://njr-sys.net/application/views/assets/js/webapps/scp_search.js",
		);
		$this->getViewWebApps( "scp_search", "WebApps", $result, $jsPathArray );

	}

    public function foundation_wbAction($id)
    {
        // id バリデーション
        $this->validateFwbId($id);

        $result = array(
            "msg"   => $this->logic->getMsg(),
            "id"    => htmlspecialchars($id),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/webapps/foundation_wb.js",
        );
        $this->getViewWebApps( "foundation_wb", "WebApps", $result, $jsPathArray );
    }

    protected function validateFwbId($id)
    {
        if (empty($id)) {
            echo "ID Empty. please insert your id.";
            exit;
        }

        if (!preg_match("/^[a-zA-Z0-9]+$/", $id)) {
            echo "ID Error. please check your id.";
            exit;
        }
    }



}