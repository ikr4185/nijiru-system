<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use Models\UsersModel;
use Models\NjcTransactionModel;
use Models\NjcAddressModel;
use \Cores\Config\Config;


class NjcWalletLogic extends AbstractLogic
{
    /**
     * @var UsersModel
     */
    protected $Users;
    
    /**
     * @var NjcTransactionModel
     */
    protected $NjcTransaction;
    
    /**
     * @var NjcAddressModel
     */
    protected $NjcAddress;
    
    protected $njcSalt = "";
    protected $userAmount = 0;
    
    protected function getModel()
    {
        $this->Users = UsersModel::getInstance();
        $this->NjcTransaction = NjcTransactionModel::getInstance();
        $this->NjcAddress = NjcAddressModel::getInstance();
        
        $this->njcSalt = Config::load("njrApi.salt");
    }

    /**
     * @param $seed
     * @param $additionalSalt
     * @return mixed
     */
    public function createAddress($seed, $additionalSalt="")
    {
        $hash = hash("sha256", $seed . $this->njcSalt . $additionalSalt);
        $address = $hash;
        
        return $address;
    }

    /**
     * ユーザ名から Address を取得する
     * @param $userId
     * @return bool
     */
    public function getAddress($userId)
    {
        $usersNumber = $this->Users->getNumberById($userId);
        $address = $this->NjcAddress->getAddressByUserNumber($usersNumber);

        if (!$address) {
            $address = $this->createAddress($userId);
            $this->NjcAddress->insertAddress($address, null, $usersNumber);
        }

        return $address;
    }

    /**
     * WikidotID から Address を取得する
     * @param $wikidotId
     * @return mixed
     */
    public function getAddressByWikidotId($wikidotId)
    {    
        $address = $this->NjcAddress->getAddressByWikidotId($wikidotId);
    
        if (!$address) {
            $address = $this->createAddress($wikidotId, "wikidot");
            $this->NjcAddress->insertAddress($address, $wikidotId, null);
        }
        
        return $address;
    }
    
    /**
     * wikidotIdAddress を userIdAddress にまとめる
     * @param $wikidot_id
     * @param $users_number
     */
    public function consolidateAddress($wikidot_id, $users_number)
    {
        $userIdAddress = $this->NjcAddress->getAddressByUserNumber($users_number);
        $wikidotIdAddress = $this->NjcAddress->getAddressByWikidotId($wikidot_id);
        
        if (!empty($userIdAddress) && !empty($wikidotIdAddress)) {
    
            // wikidotIdAddress 保有の全ニジコを userIdAddress へ移動する
            $wikidotIdAmount = $this->NjcTransaction->getAmount($wikidotIdAddress);
            $this->NjcTransaction->createTransaction($userIdAddress, $wikidotIdAddress, $wikidotIdAmount);
            
            // userIdAddress に wikidotID を紐付け
            $this->NjcAddress->updateUserIdAddress($wikidot_id, $users_number);
            
            // wikidotIdAddress を無効化する
            $this->NjcAddress->disableWikidotIdAddress($wikidot_id);
            
        }
    }

    /**
     * 特定 Address の持つ全ニジコ数量を算出して、プロパティに格納する
     * @param string $address
     * @return $this
     */
    public function setUserAmount($address)
    {
        $this->userAmount = $this->NjcTransaction->getAmount($address);
        return $this;
    }

    /**
     * ユーザーのニジコ数量を取得する
     * @return int
     */
    public function getUserAmount()
    {
        return $this->userAmount;
    }
    
    /**
     * ユーザの保有ニジコと指定数量を比較する
     * @param $amount
     * @return bool
     */
    public function checkUserAmount($amount)
    {
        if ($this->userAmount < $amount) {
            $this->setMsg("保有額が不足しています");
            return false;
        }
        return true;
    }

    /**
     * transaction を作成
     * @param string $to
     * @param string $from
     * @param int $amount
     * @return array|bool|string
     */
    public function createTransaction($to, $from, $amount)
    {
        if ($amount == 0) {
            $this->setMsg("送金額が不正です");
            return false;
        }

        if (empty($to) || empty($from) || $to == $from) {
            $this->setMsg("アドレスが不正です");
            return false;
        }

        return $this->NjcTransaction->createTransaction($to, $from, $amount);
    }

    /**
     * 過去の transaction の一覧を取得する
     * @param string $address
     * @param int $limit
     * @return array|bool|string
     */
    public function getTransactions($address, $limit=50)
    {
        return $this->NjcTransaction->getTransactions($address, $limit);
    }
}