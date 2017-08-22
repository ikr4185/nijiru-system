<?php
namespace Controllers;

use Controllers\Commons\AbstractController;
use Logics\PointLogic;
use Inputs\BasicInput;


/**
 * Class PointController
 * @package Controllers
 */
class PointController extends AbstractController
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
        $this->logic = new PointLogic();
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
        $msg = $this->input->getSession("msg");
        if (empty($msg)) {
            $msg = $this->logic->getMsg();
        }

        $result = array(
            "allUsers" => $allUsers,
            "msg" => $msg,
        );
        $this->getView("give", "ニジポ喜捨", $result);

    }

    protected function sendPoint($userId, $toId, $point)
    {
        // 自分に対してポイント付与はできなくする
        if (!$this->logic->checkMatchPomp($userId, $toId)) {
            return;
        }

        // 送り手ユーザーの既存ポイントを取得
        $userPoint = $this->logic->getPoint($userId);
        if (is_null($userPoint)) {
            return;
        }

        // 送るポイント額と比較、マイナスを防止
        if ($point >= $userPoint) {
            $point = $userPoint;
        }

        // ポイントの移譲
        if ($this->logic->sendPoint($userId, $toId, $point)) {

            // ワケマエ付与実行
            $wakemae = $point / 2;
            if (!$this->logic->add_point($userId, $wakemae)) {
                return;
            }

            // ポイント移動ログの書き込み
            if (!$this->logic->setPointLog($userId, $toId, $point)) {
                return;
            }
        }

        // セッションの更新
        $userPoint = $this->logic->getPoint($userId);
        $this->input->setSession("point", $userPoint);
        $this->input->setSession("msg", $this->logic->getMsg());

        // POST終了、リダイレクト
        $this->redirect("point","give");
    }

}