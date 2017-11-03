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


}