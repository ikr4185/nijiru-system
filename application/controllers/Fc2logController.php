<?php
namespace Controllers;

use Controllers\Commons\AbstractController;
//use Logics\DiscordLogic;
use Inputs\BasicInput;
use Cores\Config\Config;

class Fc2logController extends AbstractController {

    protected function getLogic(){

    }

    protected function getInput()
    {
        $this->input = new BasicInput();
    }
    public function indexAction(){
        
        // 認証チェック
        $isStaff = $this->auth();
        
        $result = array(
            "isStaff" => $isStaff,
        );
        $this->getView("index", "FC2 Log", $result);
    }

    public function viewAction($logName){

        // 認証チェック
        $isStaff = $this->auth();

        $logName = Config::load("dir.logs") . "/fc2wiki/pages/".str_replace("-","_",$logName).".html";
        $log = "not found";
        if ($isStaff && file_exists($logName)) {
            $log = file_get_contents($logName);
        }

        echo $log;
    }

    /**
     * パスワード認証
     */
    public function authAction()
    {
        if (!$this->auth()) {
            die("bad request.");
        }
        $this->redirect("fc2log");
    }

    protected function auth()
    {
        $pass = $this->input->getRequest("staff_pass");
        if (!$pass) {
            $pass = $this->input->getSession("staff_pass");
        }

        if ($pass == Config::load("staff.pass")) {
            $this->input->setSession("staff_pass", $pass);
            return true;
        }
        return false;
    }

}