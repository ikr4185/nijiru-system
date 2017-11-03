<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\AdminLogic;
use Cores\Config\Config;

class AdminController extends WebController
{
    /**
     * @var AdminLogic
     */
    protected $logic;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new AdminLogic();
    }
    
    private function sessionCheck()
    {
        $adminToken = $this->input->getSession("admin_token");
        if ($adminToken) {
            if ($adminToken !== crypt(Config::load("staff.pass"), "bEIEefxv")) {
                echo "token error";
                exit;
            }
        } else {
            header('Location: http://njr-sys.net/admin/login');
            exit;
        }
    }
    
    public function loginAction()
    {
        // ログイン認証(超簡易版)
        $pass = $this->input->getRequest("pass");
        
        if (Config::load("staff.pass") == $pass) {
            $this->input->setSession("admin_token", crypt(Config::load("staff.pass"), "bEIEefxv"));
            header('Location: http://njr-sys.net/admin/');
            exit;
        }
        
        $this->getView("login", "管理画面ログイン");
    }
    
    public function indexAction()
    {
        $this->sessionCheck();
        
        $LowVotes = $this->logic->showLowVote();
        
        $result = array(
            "LowVotes" => $LowVotes,
            "msg" => $this->logic->getMsg(),
        );
        $this->getView("index", "Njr-Sys Admin", $result);
    }
    
    /**
     * LVCユーザー管理
     */
    public function lvcUsersAction()
    {
        $this->sessionCheck();
        
        // 登録
        if ($this->input->checkRequest("registerLvc")) {
            
            // POST取得
            $name = $this->input->getRequest("name");
            $mail = $this->input->getRequest("mail");
            
            // 登録
            $this->logic->registerLvcUsers($name, $mail);
        }
        
        // 削除
        if ($this->input->checkRequest("delLvc")) {
            
            // POST取得
            $id = $this->input->getRequest("id");
            
            // 削除実行
            $this->logic->deleteLvcUsers($id);
        }
        
        // 読み込み
        $LvcUsers = $this->logic->showLvcUsers();
        
        $result = array(
            "LvcUsers" => $LvcUsers,
            "msg" => $this->logic->getMsg(),
        );
        $this->getView("lvc_users", "メール配信設定 | Njr-Sys Admin", $result);
    }
    
    
    public function confirmAction()
    {
        $this->sessionCheck();
        
        // Request取得
        $lowVotesNumber = $this->input->getRequest("lowVotesNumber");
        $confirm = $this->input->getRequest("confirm");
        
        // もしconfirmの送信があれば、セッションに格納してdoneへリダイレクト
        if ($confirm) {
            $this->input->setSession("admin_token", crypt(Config::load("staff.pass"), "bEIEefxv"));
            $this->input->setSession("lowVotesNumber", $lowVotesNumber);
            header('Location: http://njr-sys.net/admin/done');
            exit;
        }
        
        // DB検索
        $record = $this->logic->searchLowVoteById($lowVotesNumber);
        
        // View表示
        $result = array(
            "record" => $record,
        );
        $this->getView("confirm", "確認画面", $result);
    }
    
    
    public function doneAction()
    {
        $this->sessionCheck();
        
        // セッション取得
        $lowVotesNumber = $this->input->getSession("lowVotesNumber");
        
        // セッション取得失敗でエラー吐いて終了
        if (is_null($lowVotesNumber)) {
            echo "unknown error (error code: 040)";
            exit;
        }
        
        // 削除実行
        $this->logic->delLowVote($lowVotesNumber);
        
        // View表示
        $result = array(
            "msg" => $this->logic->getMsg(),
        );
        $this->getView("done", "確認画面", $result);
    }
    
    /**
     * カシマ管理画面
     */
    public function kashimaAction()
    {
        $this->sessionCheck();
        
        $order = $this->input->getRequest("order");
        if ($order) {
            switch ($order) {
                case 'start':
                    $this->logic->kashimaStart();
                    break;
                case 'stop':
//					$this->logic->kashimaStop();      // TODO httpdが落ちる問題
                    break;
                case 'reboot':
//					$this->logic->kashimaReboot();    // TODO httpdが落ちる問題
                    break;
                default:
            }
        }
        
        $result = array(
            "kashimaStatus" => $this->logic->getKashimaStatus(),
            "memoryUsed" => $this->logic->getMemoryUsedLog(),
            "pass" => $this->logic->getQuitPass(),
            "msg" => $this->logic->getMsg(),
        );
        $this->getView("kashima", "KASHIMA-EXE Controller Panel", $result);
    }
    
    /**
     * デバッグ
     * ログチェックの状態を監視
     */
    public function logCheckAction()
    {
        echo "<pre>";
        echo file_get_contents("/home/njr-sys/public_html/cli/logs/low_vote_check_log.log");
        echo "</pre>";
    }
    
}