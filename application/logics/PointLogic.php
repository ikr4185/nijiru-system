<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use Models\UsersModel;
use Models\PointLogModel;
use Models\NjrAssetModel;

/**
 * Class PointLogic
 * @package Logics
 */
class PointLogic extends AbstractLogic
{
    /**
     * @var UsersModel
     */
    protected $Users;

    /**
     * @var NjrAssetModel
     */
    protected $NjrAsset;

    /**
     * @var PointLogModel
     */
    protected $PointLog;
    
    protected function getModel()
    {
        $this->Users = UsersModel::getInstance();
        $this->PointLog = PointLogModel::getInstance();
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
        $assets = $this->NjrAsset->getAssets($usersNum);
    
        if (is_null($assets)) {
            $this->setError("ニジポ取得に失敗しました");
        }
        return $assets;
    }
    
    /**
     * HTMLバリデーション
     * @param $val
     * @return string
     */
    public function validate($val)
    {
        $val = htmlspecialchars($val, ENT_QUOTES);
        return $val;
    }
    
    /**
     * 自己送信防止
     * @param $userId
     * @param $to
     * @return bool
     */
    public function checkMatchPomp($userId, $to)
    {
        if (mb_strtolower($userId) == mb_strtolower($to)) {
            $this->setError("自分自身にポイント付与は出来ませんし育良がさせるとおもったか");
            return false;
        }
        return true;
    }

    
    /**
     * ポイントの移譲
     * @param $from
     * @param $to
     * @param $point
     * @return bool
     * @throws \Exception
     */
    public function sendPoint($from, $to, $point)
    {
        // 送り手からポイント減算
        if (!$this->reduce_point($from, $point)) {
            $this->setError("ポイント減算失敗");
            return false;
        }
        
        // 受け手にポイント加算
        if (!$this->add_point($to, $point)) {
            $this->setError("ポイント付与失敗");
            return false;
        }
        
        return true;
    }
    
    /**
     * ポイント加算
     * @param $id
     * @param $add_point
     * @return bool
     */
    public function add_point($id, $add_point)
    {
        // ID情報の取得
        $idInfo = $this->Users->getIdInfo($id);
        
        if (!$idInfo) {
            $this->setError("指定されたIDは登録されていません");
            return false;
        }
        
        // ニジルポイント加算
        $userNumber = $this->Users->getNumberById($id);
        $return = $this->NjrAsset->addPoint($add_point, $userNumber);
        
        // 成否判定
        if ($return === false) {
            $this->setError("ポイント付与に失敗しました");
            return false;
        }
        return true;
    }
    
    /**
     * ポイント減算
     * @param $id
     * @param $reduce_point
     * @return bool
     */
    public function reduce_point($id, $reduce_point)
    {
        // ニジルポイント減算
        $usersNum = $this->Users->getNumberById($id);
        $return = $this->NjrAsset->delPoint($reduce_point, $usersNum);
        
        // 成否判定
        if ($return === false) {
            $this->setError("ポイント減算に失敗しました");
            return false;
        }
        return true;
    }
    
    /**
     * ポイント移動ログの書き込み
     * @param $users_id
     * @param $given_users_id
     * @param $moved_point
     * @return bool
     */
    public function setPointLog($users_id, $given_users_id, $moved_point)
    {
        $usersNum = $this->Users->getNumberById($users_id);
        $givenUsersNum = $this->Users->getNumberById($given_users_id);
        
        // 該当ユーザー無し
        if (!$usersNum || !$givenUsersNum) {
            $this->setError("ログ書き込みに失敗しました");
            return false;
        }
        
        $flag = $this->PointLog->savePointLog($usersNum, $givenUsersNum, $moved_point);
        
        if (!$flag) {
            $this->setError("ログ書き込みに失敗しました");
            return false;
        }

        $movedStr = number_format($moved_point);
        $this->setMsg("{$given_users_id} さんへ {$movedStr} ニジポを贈りました");
        return true;
    }
    
    public function getUserNum($users_id)
    {
        $usersNum = $this->Users->getNumberById($users_id);
        if (!$usersNum) {
            $this->setError("指定されたIDは登録されていません");
            return false;
        }
        return true;
    }
    
    public function getAllUsers()
    {
        return $this->Users->getAllUsers();
    }
    
}