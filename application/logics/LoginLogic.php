<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use Models\UsersModel;
use Models\NjrAssetModel;

/**
 * Class LoginLogic
 * @package Logics
 */
class LoginLogic extends AbstractLogic
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
    
    // /////////////////////////////////////////////////////////////////
    // ▼login /////////////////////////////////////////////////////////
    // /////////////////////////////////////////////////////////////////
    
    /**
     * ログイン: ユーザ情報の取得
     * @param $id
     * @return bool|mixed|string
     */
    public function getIdInfo($id)
    {
        $idInfo = $this->Users->getIdInfo($id);
        
        if (!$idInfo) {
            $this->setMsg('指定されたIDは登録されていません');
            return false;
        }
        return $idInfo;
    }
    
    /**
     * ログイン: ログイン時刻記録
     * @param $id
     * @return bool
     */
    public function setLastLogin($id)
    {
        if (!$this->Users->setLastLogin($id)) {
            $this->setMsg("最終ログイン日時の書き込みに失敗しました");
            return false;
        }
        return true;
    }
    
    /**
     * ログイン: Welcomeメッセージの生成
     * @param $user_name
     * @param $asset
     */
    public function setWelcomeMsg($user_name, $asset)
    {
        if (!empty($asset["tera_point"])) {
            $this->setMsg("Welcome User {$user_name} @ {$asset["tera_point"]}T {$asset["point"]} Njp.");
            return;
        }
        $this->setMsg("Welcome User {$user_name} @ {$asset["point"]} Njp.");
    }
    
    /**
     * ニジポ取得
     * @param string $id :login_name
     * @return array
     */
    public function getAssets($id)
    {
        $userNumber = $this->Users->getNumberById($id);
        return $this->NjrAsset->getAssets($userNumber);
    }
        
    /**
     * ログインボーナスの付与
     * @param string $id :login_name
     * @return bool
     */
    public function setLoginBonus($id)
    {
        $userNumber = $this->Users->getNumberById($id);
        
        // 最終ログイン日時取得
        $lastLogin = $this->Users->getLastLogin($id);
        if (!$lastLogin) {
            $this->setMsg("最終ログイン日時の読み込みに失敗しました");
            return false;
        }
        $lastLogin = $lastLogin["last_login"];
        
        // 現在の時刻との差分計算
        $lastLoginDate = new \DateTime($lastLogin);
        $now = new \DateTime(date('Y-m-d H:i:s'));
        
        $intervalObj = $lastLoginDate->diff($now);
        $intervalStr = $intervalObj->format('%i');
        
        // 前回ログインから5分以上経過しているならログインボーナス付与
        if ($intervalStr >= 5) {
            $this->NjrAsset->addPoint(1, $userNumber);
        }
        return true;
    }
    
    /**
     * ログイン: パスワード照合
     * @param $pass
     * @param $hashedPass
     * @return bool
     */
    public function checkPass($pass, $hashedPass)
    {
        if (!$this->checkHash($pass, $hashedPass)) {
            $this->setMsg("ユーザIDあるいはパスワードに誤りがあります");
            return false;
        }
        return true;
    }
    
    /**
     * ログイン: バリデーション
     * @param $id
     * @param $pass
     * @return bool
     */
    public function validate($id, $pass)
    {
        if (empty($id)) {
            $this->setMsg("ユーザーIDが未入力です");
            return false;
        } elseif (!preg_match('/\A[a-z\d\_\-]{4,100}+\z/i', $id)) {
            $this->setMsg("ユーザーIDは半角英数字4文字以上で入力してください");
            return false;
        } else {
            if (empty($pass)) {
                $this->setMsg("パスワードが未入力です");
                return false;
            } elseif (!preg_match('/\A[a-z\d\_\-]{4,100}+\z/i', $pass)) {
                $this->setMsg("パスワードは半角英数字4文字以上で入力してください");
                return false;
            }
        }
        // バリデートクリア
        return true;
    }
    
    // /////////////////////////////////////////////////////////////////
    // ▼logout ////////////////////////////////////////////////////////
    // /////////////////////////////////////////////////////////////////
    
    /**
     * ログイン: see youメッセージの生成
     * @param $userName
     */
    public function setSeeYouMsg($userName)
    {
        $this->setMsg("see you {$userName}");
    }
    
    // /////////////////////////////////////////////////////////////////
    // ▼register //////////////////////////////////////////////////////
    // /////////////////////////////////////////////////////////////////
    
    /**
     * 新規登録: ID登録
     * @param $id
     * @param $pass
     * @param $checkPass
     * @param $name
     * @return bool
     */
    public function register($id, $pass, $checkPass, $name)
    {
        // 各項目が入力されていたら登録
        if ($this->registerValidate($id, $pass, $checkPass, $name)) {
            
            // パスワードハッシュ化
            $hashedPass = $this->convertHash($pass);
            
            // 既存IDとかぶっていないかチェック
            $checkID = $this->Users->checkAlreadyRegistered($id);
            
            if ($checkID !== false) {
                $this->setMsg("指定されたIDは既に使用されています");
                return false;
            }
            
            // 登録
            $flag = $this->Users->setIdInfo($id, $hashedPass, $name);
            
            //書き込みチェック
            if (!$flag) {
                $this->setMsg("書き込みに失敗しました");
                return false;
            }
            // ニジルポイントボーナス加算
            $userNumber = $this->Users->getNumberById($id);
            $this->NjrAsset->addPoint(100, $userNumber);
            
            // メッセージ生成
            $this->setMsg("Registored.<br>Welcome {$name} ( {$id} )");
            
            return true;
        }
        return false;
    }
    
    /**
     * 新規登録: バリデート
     * @param $id
     * @param $pass
     * @param $checkPass
     * @param $name
     * @return bool
     */
    protected function registerValidate($id, $pass, $checkPass, $name)
    {
        if (empty($id)) {
            $this->setMsg("ユーザーIDが未入力です");
            return false;
        } elseif (preg_match('/^(guest|admin|o5)$/i', $id)) {
            $this->setMsg("このユーザIDはご利用いただけません");
            return false;
        } elseif (!preg_match('/\A[a-z\d\_\-]{4,100}+\z/i', $id)) {
            $this->setMsg("パスワードは半角英数字4文字以上で入力してください");
            return false;
        } else {
            if (empty($pass)) {
                $this->setMsg("パスワードが未入力です");
                return false;
            } elseif (!preg_match('/\A[a-z\d\_\-]{4,100}+\z/i', $pass)) {
                $this->setMsg("パスワードは半角英数字4文字以上で入力してください");
                return false;
            } else {
                if ((empty($checkPass)) || ($pass != $checkPass)) {
                    $this->setMsg("パスワードをお確かめの上、もう一度入力してください");
                    return false;
                } else {
                    if (empty($name)) {
                        $this->setMsg("名前が未入力です");
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    // /////////////////////////////////////////////////////////////////
    // ▼update ////////////////////////////////////////////////////////
    // /////////////////////////////////////////////////////////////////
    
    /**
     * 登録情報修正: ユーザ情報変更
     * @param $id
     * @param $pass
     * @param $newName
     * @return bool
     */
    public function updateUserData($id, $pass, $newName)
    {
        // 入力チェック
        if ($this->updateValidate($pass)) {
            
            // ID情報の取得
            $idInfo = $this->Users->getIdInfo($id);
            
            // パスワード照合
            if ($this->checkPass($pass, $idInfo["password"])) {
                
                // 変更があれば修正
                if (isset($newName)) {
                    
                    $flag = $this->Users->updateData('user_name', $newName, $id);
                    
                    //書き込みチェック
                    if ($flag !== true) {
                        $this->setMsg("書き込みに失敗しました");
                        return false;
                    }
                    
                    $this->setMsg("アカウント情報を修正しました");
                    return true;
                }
            }
            $this->setMsg('パスワードが異なります');
            return false;
        }
        return false;
    }
    
    /**
     * 登録情報修正: バリデーション
     * @param $pass
     * @return bool
     */
    protected function updateValidate($pass)
    {
        if (empty($pass)) {
            $this->setMsg("パスワードが未入力です");
            return false;
        } elseif (!preg_match('/\A[a-z\d]{4,100}+\z/i', $pass)) {
            $this->setMsg("パスワードは半角英数字4文字以上で入力してください");
            return false;
        }
        return true;
    }
    
    /**
     * 登録情報修正: ユーザーの公開/非公開情報を取得
     * @param $id
     * @return bool
     */
    public function getPublication($id)
    {
        // ID情報の取得
        $idInfo = $this->Users->getIdInfo($id);
        
        // 公開状態取得
        if (2 == $idInfo["publication"]) {
            return true;
        }
        return false;
    }
    
    /**
     * 登録情報修正: ユーザ公開度設定
     * @param $id
     * @param $publication
     * @return bool
     */
    public function updateUserPublication($id, $publication)
    {
        $flag = $this->Users->setUserPublication($publication, $id);
        
        //書き込みチェック
        if ($flag !== true) {
            $this->setMsg("書き込みに失敗しました");
            return false;
        }
        
        $this->setMsg("アカウント情報を修正しました");
        return true;
    }
    
}