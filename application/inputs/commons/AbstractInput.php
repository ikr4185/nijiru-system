<?php
namespace Inputs\Commons;

/**
 * Class AbstractInput
 * 抽象インプット
 * @package Inputs\Commons
 */
abstract class AbstractInput {
	
	public function setSession($name, $param) {
		$_SESSION[$name] = $param;
	}
	
	/**
	 * @param $name
	 * @return null
	 */
	public function getSession($name) {
		if ($this->checkSession($name)) {
			return $_SESSION[$name];
		}
		return null;
	}

	/**
	 * セッション削除
	 * @param $name
	 */
	public function delSession($name) {
		if ($this->checkSession($name)) {
			unset($_SESSION[$name]);
		}
	}
	
	/**
	 * @param $name
	 * @return bool
	 */
	public function checkSession($name) {
		if (isset($_SESSION[$name])) {
			return true;
		}
		return false;
	}
	
	/**
	 * @param $name
	 * @param bool $isGet
	 * @return null
	 */
	public function getRequest($name,$isGet=false) {
		
		if ($isGet) {
			if ($this->checkRequest($name, true)) {
				return $_GET[$name];
			}
		}else{
			if ($this->checkRequest($name)) {
				return $_POST[$name];
			}
		}
		return null;
		
	}
	
	/**
	 * リクエストの有無をチェック
	 * @param $name
	 * @param bool $isGet
	 * @return bool
	 */
	public function checkRequest($name,$isGet=false) {
		
		if ($isGet) {
			if (isset($_GET[$name])) {
				return true;
			}
		}else{
			if (isset($_POST[$name])) {
				return true;
			}
		}
		return false;
		
	}
	
	/**
	 * POSTリクエストかチェック
	 * @return bool
	 */
	public function isPost() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return true;
		}
		return false;
	}
	
	/**
	 * クッキー削除
	 */
	public function delCookie() {
		if (isset($_COOKIE["PHPSESSID"])) {
			setcookie("PHPSESSID", '', time() - 1800, '/');
		}		
	}
	
	/**
	 * $_FILESのラッパー
	 * @param $arg
	 * @return mixed
	 */
	public function getFile($arg) {
		if (isset($_FILES[$arg])) {
			return $_FILES[$arg];
		}
		return false;
	}
	
}