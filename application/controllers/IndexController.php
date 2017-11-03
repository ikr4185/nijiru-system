<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\IndexLogic;

class IndexController extends WebController
{
    /**
     * @var IndexLogic
     */
    protected $logic;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new IndexLogic();
    }
    
    public function indexAction()
    {
        $user_id = $this->input->getSession("id");
        $userName = "";
        if (!empty($user_id)) {
            $userName = $this->logic->getIdFromName($user_id);
        }
        
        $resultArray = array(
            "hello" => "hello ",
            "world" => $userName,
        );
        $this->getView("index", "", $resultArray);
    }
    
}