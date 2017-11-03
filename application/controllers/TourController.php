<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\ScpreaderLogic;

/**
 * Class TourController
 * みよしのに捧ぐネタ
 * @package Controllers
 */
class TourController extends WebController
{
    /**
     * @var ScpreaderLogic
     */
    protected $logic;

    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new ScpreaderLogic();
    }

    /**
     * 404
     */
    public function indexAction()
    {
        $result = array();
        $this->getView("index", "404", $result);
    }

    public function __call($name, $arguments)
    {
        $this->_2016Action();
    }

    public function _2016Action()
    {
        // 記事読み込み
        $scpArray = $this->logic->getScpArray(609);

        $result = array(
            "scpArray" => $scpArray,
            "msg" => $this->logic->getMsg(),
        );
        $this->getView("2016", "Sacrifice Curse Prayer", $result);
    }
}