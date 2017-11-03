<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use Models\UsersModel;
use Models\NjrAssetModel;

/**
 * Class UsersLogic
 *
 * ユーザー管理の共通ロジック
 *
 * @package Logics
 */
class UsersLogic extends AbstractLogic
{
    /**
     * @var UsersModel
     */
    protected $Users;
    /**
     * @var NjrAssetModel
     */
    protected $NjrAsset;
    
    protected function getModel()
    {
        $this->Users = UsersModel::getInstance();
        $this->NjrAsset = NjrAssetModel::getInstance();
    }
    
    /**
     * ユーザー所有のニジポ額を取得
     * @param $id
     * @return array
     */
    public function getAssets($id)
    {
        $usersNum = $this->Users->getNumberById($id);
        return $this->NjrAsset->getAssets($usersNum);
    }
    
    /**
     * ユニークナンバーからIDを取得
     * @param $number
     * @return array|string
     */
    public function getIdByNumber($number)
    {
        return $this->Users->getIdByNumber($number);
    }

    /**
     * IDからユニークナンバーを取得
     * @param $id
     * @return array|string
     */
    public function getNumberById($id)
    {
        return $this->Users->getNumberById($id);
    }
}