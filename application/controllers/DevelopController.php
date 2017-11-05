<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\DevelopLogic;

/**
 * Class DevelopController
 * 開発中の機能
 * @package Controllers
 */
class DevelopController extends WebController
{
    /**
     * @var DevelopLogic
     */
    protected $logic;

    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new DevelopLogic();
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
    
    /**
     * Riot.jsのテスト
     */
    public function riotAction()
    {
        $result = array(
            "msg" => $this->logic->getMsg(),
        );
        $jsPathArray = array(
            "https://cdnjs.cloudflare.com/ajax/libs/riot/3.7.3/riot+compiler.min.js",
        );
        $header = '<script src="http://njr-sys.net/application/views/assets/riot_templates/develop/ajax_test.tag" type="riot/tag"></script>' . "<script>riot.mount('*')</script>";
        $this->getViewDev("riot", "Develop", $result, $jsPathArray, $header, true);
    }
}