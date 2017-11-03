<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\StorageLogic;

/**
 * Class StorageController
 * @package Controllers
 */
class StorageController extends WebController
{
    
    /**
     * @var StorageLogic
     */
    protected $logic;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new StorageLogic();
    }
    
    public function indexAction()
    {
        // フラッシュデータとして、ファイルアップ結果とロジックメッセージを取得(あれば)
        $storage_session = $this->input->getFlash("storage_session");
        
        // ファイルアップ処理
        if (empty($storage_session) && $this->input->isPost()) {
            
            // ファイルアップロード
            $is_done = $this->fileUpload($this->input->getSession("id"));
            
            // セッション発行
            $this->input->setSession("storage_session", array("is_done" => $is_done, "msg" => $this->logic->getMsg()));
            
            // 強制的にリロード
            $this->redirect("Storage");
        }
        
        // ファイル一覧読み込み
        $fileArray = $this->logic->getFileInfo();
        
        // ロジックメッセージの抽出
        $msg = "";
        if (isset($storage_session["msg"])) {
            $msg = $storage_session["msg"];
        }
        
        // テンプレート生成
        $result = array(
            "fileArray" => $fileArray,
            "msg" => $msg,
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/freewall.js",
            "http://njr-sys.net/application/views/assets/js/storage_js_init.js",
        );
        $this->getView("index", "Nijiru Storage", $result, $jsPathArray);
        
    }
    
    /**
     * ファイルのソフトデリート
     * @param $file_id
     */
    public function delAction($file_id)
    {
        // ファイル情報の取得
        $fileArray = $this->logic->getFileInfo($file_id);
        
        // テンプレート生成
        $result = array(
            "fileArray" => $fileArray,
            "file_id" => $file_id,
            "msg" => $this->logic->getMsg(),
        );
        
        $this->getView("del", "Nijiru Storage", $result);
    }
    
    public function deleteAction($file_id)
    {
        // 削除実行
        $this->logic->softDelete($file_id);
        
        // セッション発行
        $this->input->setSession("storage_session", array("is_done" => true, "msg" => "削除しました"));
        
        // 強制的にリロード
        $this->redirect("Storage");
    }
    
    /**
     * ファイルアップロード
     * @param $id
     * @return bool
     */
    protected function fileUpload($id)
    {
        // アップロードチェック
        $tmpFileArray = $this->input->getFile("upload");
        if (empty($tmpFileArray)) {
            return false;
        }
        
        // ユーザーのユニークナンバー取得
        $user_id = $this->Users->getNumberById($id);
        
        // クレジット名を取得
        $credit = $this->input->getRequest("credit");
        
        // ファイルアップロード
        return $this->logic->upload($tmpFileArray, $user_id, $credit);
    }
    
    /**
     * サムネイルの動的生成
     */
    public function outimgAction()
    {
        $this->logic->outimg();
    }
}