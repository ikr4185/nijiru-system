<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\DownloadLogic;

/**
 * Class DownloadController
 * @package Controllers
 */
class DownloadController extends WebController
{
    /**
     * @var DownloadLogic
     */
    protected $logic;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new DownloadLogic();
    }
    
    public function indexAction()
    {
        // POSTを受け取ったらconfirmへリダイレクト
        $dl = $this->input->getRequest("dl");
        if ($dl) {
            // 画像IDのセッション発行
            $this->input->setSession("fileId", key($dl));
            $this->redirect("Download", "confirm");
        }
        
        // エラーメッセージセッションから値取得
        // TODO いい方法思いついたら切り替える
        $msg = $this->logic->getMsg();
        $downloadErrorMsg = $this->input->getErrorMsgSession();
        if (!empty($downloadErrorMsg)) {
            $msg = $downloadErrorMsg;
        }
        
        $result = array(
            "data" => $this->logic->getAll(),
            "msg" => $msg,
        );
        $this->getView("index", "ニジルダウンローダー", $result);
    }
    
    /**
     * ダウンロード確認画面
     */
    public function confirmAction()
    {
        // ユーザID・ファイルIDの取得
        $userId = $this->input->getSession("id");
        $fileId = $this->input->getSession("fileId");
        
        // リロード禁止
        if ($this->input->checkSession("confirm_download")) {
            
            // リダイレクト
            $this->input->setErrorMsgSession("invalid session");
            $this->input->delSession("confirm_download");
            $this->redirect("Download");
        }
        
        // POSTを受け取ったら各種処理開始
        $dl = $this->input->getRequest("dl");
        if ($dl) {
            
            // ポイント減算
            $consuming = $this->logic->consumePoint($userId, $fileId);
            if (false == $consuming) {
                $this->input->setErrorMsgSession("consume point error");
                $this->redirect("Download");
            }
            
            // pointセッションの更新
            $assets = $this->UsersLogic->getAssets($userId);
            $this->input->setSession("point", $assets["point"]);
            $this->input->setSession("tera_point", $assets["tera_point"]);
            
            // 決済バリデート用セッション発行
            $this->input->setSession("confirm_download", key($dl));
            
            // リダイレクト
            $this->redirect("Download", "done");
        }
        
        $result = array(
            "fileId" => $fileId,
            "data" => $this->logic->searchRecordById($fileId),
            "msg" => $this->logic->getMsg(),
        );
        $this->getView("confirm", "確認画面", $result);
    }
    
    public function doneAction()
    {
        // セッション確認
        $fileId = $this->input->getSession("fileId");
        $confirmDownload = $this->input->getSession("confirm_download");
        
        // 決済バリデート用セッションの確認
        if ($confirmDownload === $fileId) {
            
            // POSTを受け取ったら各種処理開始
            if ($this->input->checkRequest("submit")) {
                
                // セッション破棄
                $this->input->delSession("confirm_download");
                
                // ダウンロード実行
                $this->logic->downloadMethod($fileId);
                
            }
            
            $result = array(
                "fileId" => $fileId,
                "msg" => $this->logic->getMsg(),
            );
            $this->getView("done", "支払い完了", $result);
            
        } else {
            
            // セッション破棄
            $this->input->delSession("confirm_download");
            
            // 不正なセッション
            $this->input->setErrorMsgSession("invalid session");
            $this->redirect("Download");
        }
        
    }
}