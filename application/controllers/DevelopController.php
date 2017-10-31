<?php
namespace Controllers;

use Controllers\Commons\AbstractController;
use Logics\DevelopLogic;
use Inputs\BasicInput;


/**
 * Class DevelopController
 * 開発中の機能
 * @package Controllers
 */
class DevelopController extends AbstractController
{

    /**
     * @var DevelopLogic
     */
    protected $logic;

    /**
     * @var BasicInput
     */
    protected $input;

    protected function getLogic()
    {
        $this->logic = new DevelopLogic();
    }

    protected function getInput()
    {
        $this->input = new BasicInput();
    }

    public function indexAction()
    {
        $result = array(
            "msg" => $this->logic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/marked.min.js",
            "http://njr-sys.net/application/views/assets/js/markdown_parser.js",
        );
        $this->getViewDev("index", "Develop", $result, $jsPathArray);

    }

    public function scpSearchAction()
    {
        // ポストされたらリダイレクト
        if ($this->input->isPost()) {

            $inputNumber = $this->input->getRequest("scp_search");

            if ($this->logic->validateScpSearch($inputNumber)) {
                $url = "http://ja.scp-wiki.net/scp-" . $inputNumber;
                $this->redirectTo($url);
            }
        }

        $result = array(
            "msg" => $this->logic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/scp_search.js",
        );
        $this->getViewDev("scp_search", "Develop", $result, $jsPathArray);

    }

    /**
     * 1から100までの乱数を表示
     */
    public function randAction()
    {
        // スリープ
        sleep(5);

        $rand = mt_rand(0, 100);
        echo (string)$rand;
    }

    /**
     * IPの表示
     */
    public function ipAction()
    {
        echo $_SERVER["REMOTE_ADDR"];
    }


    // 汎用テスト
    public function ikrtestAction()
    {
        $users_number = 15;
        $this->logic->njrAssetTest($users_number);
    }

//	public function apiAction() {
//
//		$result = $this->logic->getApi();
//
//		echo "<pre>";
//		var_dump($result);
//		echo "</pre>";
//
//	}

//	public function apiTestAction() {
//		$result = $this->logic->test();
//
//		echo "<pre>";
//		var_dump($result);
//		echo "</pre>";
//	}


}