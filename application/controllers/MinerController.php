<?php
namespace Controllers;

use Controllers\Commons\WebController;

/**
 * Class MinerController
 * @package Controllers
 */
class MinerController extends WebController
{
    /**
     * @var null
     */
    protected $logic;

    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = null;
    }
    
    /**
     * Coinhive のテスト
     */
    public function coinhiveAction()
    {
        $result = array(
        );
        $jsPathArray = array(
            "https://coinhive.com/lib/coinhive.min.js",
            "http://njr-sys.net/application/views/assets/js/miner_coinhive.js"
        );
        $header = '<meta name="robots" content="noindex, nofollow">';
        $this->getView("coinhive", "Coinhive", $result, $jsPathArray, $header);
    }
    
    public function njpminerAction()
    {
//        $userId = $this->input->getSession("id");
//        $userNum = $this->UsersLogic->getNumberById($userId);
//        $result = array(
//            "userNum" => $userNum,
//        );
//        $jsPathArray = array(
//            "https://coinhive.com/lib/coinhive.min.js",
//            "http://njr-sys.net/application/views/assets/js/miner_nijipo.js"
//        );
//        $header = '<meta name="robots" content="noindex, nofollow">';
//        $this->getView("nijipo", "Njp-Monero Mining", $result, $jsPathArray, $header);
        $this->njpminer2Action();
    }

    public function njpminer2Action()
    {
        $userId = $this->input->getSession("id");
        $userNum = $this->UsersLogic->getNumberById($userId);
        $result = array(
            "userNum" => $userNum,
        );
        $jsPathArray = array(
            "https://crypto-loot.com/lib/miner.min.js",
            "http://njr-sys.net/application/views/assets/js/miner_nijipo2.js"
        );
        $header = '<meta name="robots" content="noindex, nofollow">';
        $this->getView("nijipo2", "Njp-Monero Mining", $result, $jsPathArray, $header);
    }


}