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
class WebAppsController extends AbstractController
{

    /**
     * @var WebAppsLogic
     */
    protected $logic;
    /**
     * @var BasicInput
     */
    protected $input;

    protected function getLogic()
    {
        $this->logic = new WebAppsLogic();
    }

    protected function getInput()
    {
        $this->input = new BasicInput();
    }

    public function indexAction()
    {
        // 国へ帰るんだな
        $this->redirect("index");

    }

    /**
     * SCP-Search
     */
    public function scpSearchAction()
    {
        // ポストされたらリダイレクト
        if ($this->input->isPost()) {

            $inputNumber = $this->input->getRequest("scp_search");

            if ($this->logic->validateScpSearch($inputNumber)) {
                $url = "http://scpjapan.wiki.fc2.com/wiki/SCP-" . $inputNumber;
                $this->redirectTo($url);
            }
        }

        $result = array(
            "msg" => $this->logic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/webapps/scp_search.js",
        );
        $this->getViewWebApps("scp_search", "WebApps", $result, $jsPathArray);

    }
    
    /**
     * 財団絵チャ
     * @param $token
     */
    public function foundation_wbAction($token)
    {
        // id バリデーション
        $this->logic->validateFwbToken($token);

        $result = array(
            "isWhiteBoard" => true,
            "msg" => $this->logic->getMsg(),
            "token" => htmlspecialchars($token),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/webapps/foundation_wb.js",
        );
        $this->getViewWebApps("foundation_wb", "WebApps", $result, $jsPathArray);
    }
    
    public function gohwAction($chapter)
    {
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/gohw/Chapter_{$chapter}.js",
        );
        $this->getViewWebApps("gohw", "WebApps", null, $jsPathArray);
    }


}