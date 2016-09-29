<?php
namespace Inputs;
use Inputs\Commons\AbstractInput;

/**
 * Class BasicInput
 * NijiruSystemの基本Inputクラス
 * @package Inputs
 */
class BasicInput extends AbstractInput {
	
	// TODO 何故AbstractInputで定義済みメソッドを、再定義しないとエラーがでるのか？

	public function setSession($name, $param) {
		parent::setSession($name, $param);
	}

	public function getSession($name) {
		return parent::getSession($name);
	}
	
	/**
	 * セッションを取得して即削除する
	 * @param $name
	 * @return null
	 */
	public function getFlash( $name ) {
		$data = $this->getSession( $name );
		$this->delSession( $name );
		return $data;
	}

	public function checkSession($name) {
		return parent::checkSession($name);
	}
	
	public function delSession($name) {
		parent::delSession($name);		
	}
	
	public function setErrorMsgSession( $param ) {
		$this->setSession("system_error_msg", "[Error] ".$param);
	}
	
	public function getErrorMsgSession() {
		$errorMsg = $this->getSession("system_error_msg");
		$this->delSession("system_error_msg");
		return $errorMsg;
	}
	
	public function getRequest($name,$isGet=false) {
		return parent::getRequest($name,$isGet);
	}
	
	public function checkRequest($name,$isGet=false) {
		return parent::checkRequest($name,$isGet);
	}

	public function isPost() {
		return parent::isPost();
	}
	
	public function delCookie() {
		parent::delCookie();
	}
	
	public function checkLogin() {
		if ($this->getSession("id")) {
			return true;
		}
		return false;
	}
	
	public function getFile($arg) {
		return parent::getFile($arg);
	}

}