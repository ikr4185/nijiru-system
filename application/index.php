<?php
require_once __DIR__ . "/vendor/autoload.php";

// エラー表示
ini_set( 'display_errors', 1 );

// debug //////////
//if (isset($argv)) {
//	$class = 'Controllers\\'.$argv[1]."Controller";
//	$action = $argv[2]."Action";
//
//	$app = new $class();
//	$app->$action();
//
//	exit;
//}// debug //////////

// デバッグ用便利関数

/**
 * var_dump拡張
 * @param $var
 */
function vD($var){
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
}

/**
 * var_exportラッパー
 * @param $var
 * @return mixed
 */
function vE($var){
	return var_export($var,true);
}

/**
 * Class Dispatch
 */
class Dispatch
{

	/**
	 * コントローラーインスタンス・アクションメソッドの生成・実行
	 */
	public function dispatch()
	{

		// パラメーター取得（末尾の / は削除）
		$param = ereg_replace('/?$', '', $_SERVER['REQUEST_URI']);

		// debug //////////
		$param = str_replace("/application", "", $param);

		$params = array();
		if ('' != $param) {
			// パラメーターを / で分割
			$params = explode('/', $param);
		}
		
		// パラメーター[1]をコントローラー名として取得
		$controller = "index";
		if (1 < count($params)) {
			$controller = $params[1];
		}
		
		// コントローラー名によりコントローラークラス振分け
		$className = 'Controllers\\' . ucfirst(strtolower($controller)) . 'Controller';
				
		// コントローラークラスインスタンス生成
		$controllerInstance = new $className();
		
		// パラメーター[2]をアクション名として取得
		$action= 'index';
		if (2 < count($params)) {
			$action= $params[2];
		}
		
		// パラメーター[3]を引数として取得(あれば)
		$argument= null;
		if (3 < count($params)) {
			$argument= $params[3];
		}
		
		// アクションメソッドを実行
		$actionMethod = $action . 'Action';
		$controllerInstance->$actionMethod($argument);

		exit; // TODO Apacheのリライトが上手くいってないらしく、二重に読み込まれてしまう？
	}
}
$instance = new Dispatch;
$instance->dispatch();