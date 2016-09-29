<?php
namespace Logics\Commons;

/**
 * Class AbstractLogic
 * 抽象ロジック
 * @package Controllers\Logics
 */
abstract class AbstractLogic{
	
	/**
	 * @var string
	 */
	protected $msg = "";

	public function __construct()	{
		//メッセージ初期化
		$this->msg = "";
		$this->getModel();
	}
	
	/**
	 * モデルインスタンスの生成
	 * // モデルは全てシングルトンなので Database::getInstance() を用いる必要がある
	 * @return mixed
	 */
	abstract protected function getModel();
	
	/**
	 * メッセージ表示
	 * @return string
	 */
	public function getMsg(){
		return $this->msg;
	}

	/**
	 * メッセージ格納
	 * ※あくまで logic のレスポンス用なので、publicには絶対にしない
	 * @param $msg string
	 */
	protected function setMsg($msg){
		$this->msg = $msg;
	}
	
	/**
	 * エラーメッセージ
	 * @param $msg
	 */
	protected function setError($msg) {
		$this->setMsg("[Error]".get_class($this).":".$msg);
	}

	/**
	 * 文字列ハッシュ化
	 * @param $string
	 * @param int $cost
	 * @return string
	 */
	public function convertHash( $string, $cost = 4 ) {
		
		$char = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		$salt = "";
		
		for($i = 0; $i < 22; $i++){
			$r = mt_rand(0, strlen($char) - 1);
			$salt .= substr($char, $r, 1);
		}
		
		if($cost < 4){
			$cost = 4;
		}else if($cost > 31){
			$cost = 31;
		}
		
		return crypt( $string, "$2y$".sprintf("%02d", $cost)."$" . $salt );
	}
	
	/**
	 * ハッシュ照合
	 * @param $string       string  入力された検証対象の文字列
	 * @param $hashedString string  DB上にハッシュ化して保存されたデータ  
	 * @return bool
	 */
	public function checkHash($string, $hashedString){
		return crypt($string, $hashedString) == $hashedString;
	}
	
	/**
	 * MySQL用に文字列をエスケープ
	 * @param $string
	 * @return string
	 */
	public function validate( $string ) {
		return "'" . htmlspecialchars($string) . "'";
	}

}