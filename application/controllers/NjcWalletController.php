<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\NjcWalletLogic;
use Logics\AuthLogic;


class NjcwalletController extends WebController
{
    /**
     * @var NjcWalletLogic
     */
    protected $logic;
    
    /**
     * @var AuthLogic
     */
    protected $Auth;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new NjcWalletLogic();
        $this->Auth = new AuthLogic();
    }
    
    public function indexAction()
    {
        $address = "";
        $totalAmount = 0;
        $transactions = array();

        $userId = $this->input->getSession("id");
        if ($userId) {
            $address = $this->logic->createAddress($userId);
            $totalAmount = $this->logic->setUserAmount($address)->getUserAmount();
            $transactions = $this->logic->getTransactions($address);
        }

        $result = array(
            "address" => $address,
            "totalAmount" => $totalAmount,
            "transactions" => $transactions,
            "msg" => "",
        );
        $this->getView("index", "NjcWallet", $result);
    }
    
    public function qrAction()
    {
        $address = "";
        $totalAmount = 0;
        $transactions = array();
        
        $userId = $this->input->getSession("id");
        if ($userId) {
            $address = $this->logic->getAddress($userId);
            $totalAmount = $this->logic->setUserAmount($address)->getUserAmount();
            $transactions = $this->logic->getTransactions($address);
        }
        
        $result = array(
            "address" => $address,
            "totalAmount" => $totalAmount,
            "transactions" => $transactions,
            "msg" => "",
        );
        $this->getViewWebApps("qr-code", "NjcWallet", $result);
    }

    public function sendAction($toAddress)
    {
        $fromAddress = "";
        $totalAmount = 0;

        // ユーザのウォレット情報
        $userId = $this->input->getSession("id");
        if ($userId) {
            $fromAddress = $this->logic->getAddress($userId);
            $totalAmount = $this->logic->setUserAmount($fromAddress)->getUserAmount();
        }

        if ($this->input->checkRequest("send")) {

            $amount = $this->input->getRequest("amount");
            if (empty($toAddress)) {
                $toAddress = $this->input->getRequest("to");
            }
            
            // ユーザー保有ニジコチェック
            if ($this->logic->checkUserAmount($amount)) {

                // セッションへの情報格納
                $this->input->setSession("from", $fromAddress);
                $this->input->setSession("to", $toAddress);
                $this->input->setSession("amount", $amount);
                $this->input->setSession("token", $this->Auth->createHash());
                $this->input->setSession("send", true);

                // 決済処理実行
                $this->redirect("njcwallet", "done");
            }
        }

        $result = array(
            "fromAddress" => $fromAddress,
            "toAddress" => $toAddress,
            "totalAmount" => $totalAmount,
            "msg" => $this->logic->getMsg(),
        );
        $this->getView("send", "送金 | NjcWallet", $result);
    }

    public function send2wkidAction()
    {
        $fromAddress = "";
        $toAddress = "";
        $totalAmount = 0;

        // ユーザのウォレット情報
        $userId = $this->input->getSession("id");
        if ($userId) {
            $fromAddress = $this->logic->getAddress($userId);
            $totalAmount = $this->logic->setUserAmount($fromAddress)->getUserAmount();
        }

        if ($this->input->checkRequest("send")) {

            $amount = $this->input->getRequest("amount");
            if (empty($toAddress)) {
                $wikidotId = $this->input->getRequest("wikidotId");
                $toAddress = $this->logic->getAddressByWikidotId($wikidotId);
            }

            // ユーザー保有ニジコチェック
            if ($this->logic->checkUserAmount($amount)) {

                // セッションへの情報格納
                $this->input->setSession("from", $fromAddress);
                $this->input->setSession("to", $toAddress);
                $this->input->setSession("amount", $amount);
                $this->input->setSession("token", $this->Auth->createHash());
                $this->input->setSession("send", true);

                // 決済処理実行
                $this->redirect("njcwallet", "done");
            }
        }

        $result = array(
            "fromAddress" => $fromAddress,
            "toAddress" => $toAddress,
            "totalAmount" => $totalAmount,
            "msg" => $this->logic->getMsg(),
        );
        $this->getView("send-to-wikidot-id", "wikidotID送金 | NjcWallet", $result);
    }
    
    public function doneAction()
    {
        // 二重決済防止
        if (!$this->input->checkSession("send")) {
            $this->renderErrorView("", "", "不正なセッション");
        }
        $this->input->delSession("send");

        // セッション情報
        $from = $this->input->getFlash("from");
        $to = $this->input->getFlash("to");
        $amount = $this->input->getFlash("amount");
        $token = $this->input->getFlash("token");

        // tokenチェック
        if (!$this->Auth->checkHash($token)) {
            $this->renderErrorView($to, $amount, "トークンの有効期限が過ぎています");
        }

        // 送金
        if (!$this->logic->createTransaction($to, $from, $amount)) {
            $this->renderErrorView($to, $amount, $this->logic->getMsg());
        }

        $result = array(
            "toAddress" => $to,
            "amount" => $amount,
            "msg" => $this->logic->getMsg(),
            "isValid" => true,
        );
        $this->getView("done", "送金完了 | NjcWallet", $result);
    }

    protected function renderErrorView($to, $amount, $msg)
    {
        $result = array(
            "toAddress" => $to,
            "amount" => $amount,
            "msg" => $msg,
            "isValid" => false,
        );
        $this->getView("done", "エラー | NjcWallet", $result);
        exit;
    }
}