<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\PointLogic;
use Inputs\BasicInput;

/**
 * Class PointController
 * @package Controllers
 */
class PointController extends WebController
{
    /**
     * @var PointLogic
     */
    protected $logic;
    
    /**
     * @var BasicInput
     */
    protected $input;

    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new PointLogic();
    }

    /**
     * ポイント贈与
     */
    public function giveAction()
    {
        // 処理振り分け
        if ($this->input->checkRequest("give")) {

            // 各種値を取得
            $userId = $this->input->getSession("id");
            $toId = $this->logic->validate($this->input->getRequest("to"));
            $point = intval($this->input->getRequest("point"));

            // ユーザーのチェック
            if ($this->logic->getUserNum($toId)) {

                // ポイント移譲
                $this->sendPoint($userId, $toId, $point);
            }
        }

        // 全ユーザーの取得
        $allUsers = $this->logic->getAllUsers();

        // セッションに格納していたメッセージを取得
        $msg = $this->input->getFlash("msg");
        if (empty($msg)) {
            $msg = $this->logic->getMsg();
        }

        $result = array(
            "allUsers" => $allUsers,
            "msg" => $msg,
        );
        $this->getView("give", "ニジポ喜捨", $result);
    }
    
    /**
     * @param $userId
     * @param $toId
     * @param $point
     */
    protected function sendPoint($userId, $toId, $point)
    {
        // 自分に対してポイント付与はできなくする
        if (!$this->logic->checkMatchPomp($userId, $toId)) {
            return;
        }

        // ポイントの移譲
        if ($this->logic->sendPoint($userId, $toId, $point)) {

//            // ワケマエ付与実行
//            $wakemae = $point / 2;
//            if (!$this->logic->add_point($userId, $wakemae)) {
//                return;
//            }

            // ポイント移動ログの書き込み
            if (!$this->logic->setPointLog($userId, $toId, $point)) {
                return;
            }
        }

        // セッションの更新
        $assets = $this->logic->getAssets($userId);
        $this->input->setSession("point", $assets["point"]);
        $this->input->setSession("tera_point", $assets["tera_point"]);
        $this->input->setSession("msg", $this->logic->getMsg());

        // POST終了、リダイレクト
        $this->redirect("point", "give");
    }

}