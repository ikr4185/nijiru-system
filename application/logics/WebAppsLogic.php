<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Models\WhiteBoardModel;

/**
 * Class WebAppsLogic
 * @package Logics
 */
class WebAppsLogic extends AbstractLogic {
    
    /**
     * @var WhiteBoardModel
     */
    private $WhiteBoard = null;
    
	protected function getModel() {
        $this->WhiteBoard = WhiteBoardModel::getInstance();
	}
    
    // ==========================================================================================
    // ScpSearch
    // ==========================================================================================
	
	public function validateScpSearch( $inputNumber ) {
		
		if(empty($inputNumber)){
			$this->setError("Empty");
			return false;
		}
		return true;
	}

    // ==========================================================================================
    // foundation_whiteBoard
    // 財団絵チャ
    // ==========================================================================================
    
    /**
     * 財団絵チャ : Tokenバリデーション
     * @param $token
     */
    public function validateFwbToken($token)
    {
        if (empty($token)) {
            echo "Token Empty. please insert your id.";
            exit;
        }

        if (!preg_match("/^[a-zA-Z0-9]+$/", $token)) {
            echo "Token Error. please check your id.";
            exit;
        }
    }
    
    /**
     * 財団絵チャ : 読み込み
     * @param $token
     * @return array|string
     */
    public function loadFwbImage($token)
    {
        return $this->WhiteBoard->getImage($token);
    }
    
    /**
     * 財団絵チャ : 保存
     * @param $token
     * @param $data
     * @param $pass
     * @return array|bool|string
     */
    public function saveFwbImage($token, $data, $pass)
    {
        // レコード有無のチェック
        $recordArray = $this->WhiteBoard->getImage($token);
        $recordPass = $recordArray[0]["pass"];

        // レコードが存在した場合
        if (!empty($recordArray)) {

            // パスワードをチェック
            if ( $pass != $recordPass) {
                return false;
            }
    
            // 上書き保存
            return $this->WhiteBoard->update($token, $data);
        }

        // 新規保存
        return $this->WhiteBoard->insert($token, $data, $pass);
    }
	
}