<?php
namespace Controllers;

use Controllers\Commons\AbstractController;
use Inputs\BasicInput;


/**
 * Class MinerController
 * @package Controllers
 */
class MinerController extends AbstractController
{

    /**
     * @var null
     */
    protected $logic;
    /**
     * @var BasicInput
     */
    protected $input;

    protected function getLogic()
    {
        $this->logic = null;
    }

    protected function getInput()
    {
        $this->input = new BasicInput();
    }

    public function indexAction()
    {
        // TODO 未使用
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