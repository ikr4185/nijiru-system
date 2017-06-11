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
        if (!$isStaff) {
            die("WARNING: INCORRECT AUTHENTICATION: YOU HAVE SIXTY SECONDS TO ENTER THE CORRECT USER AUTHENTICATION, OR SECURITY PERSONNEL WILL BE SUMMONED TO YOUR LOCATION.");
        }

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
            die("WARNING: INCORRECT AUTHENTICATION: YOU HAVE SIXTY SECONDS TO ENTER THE CORRECT USER AUTHENTICATION, OR SECURITY PERSONNEL WILL BE SUMMONED TO YOUR LOCATION.");
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
    
    public function csvAction()
    {
        // 認証チェック
        $isStaff = $this->auth();
        if (!$isStaff) {
            die("WARNING: INCORRECT AUTHENTICATION: YOU HAVE SIXTY SECONDS TO ENTER THE CORRECT USER AUTHENTICATION, OR SECURITY PERSONNEL WILL BE SUMMONED TO YOUR LOCATION.");
        }

        $fileName = Config::load("dir.logs") . "/fc2wiki/run_pages_20170611_224251.log";

        // ダウンロード開始
        header('Content-Type: application/octet-stream');

        // ここで渡されるファイルがダウンロード時のファイル名になる
        header('Content-Disposition: attachment; filename=run_pages_20170611_224251.csv');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($fileName));
        readfile($fileName);
        exit;
    }

}