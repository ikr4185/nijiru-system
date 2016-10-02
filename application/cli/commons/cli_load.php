<?php
require_once __DIR__ . "/../../vendor/autoload.php";

/**
 * Nijiru Command Line Interfaces Loader
 * [njr-sys@leviathan ~]$ cd public_html/application/
 * [njr-sys@leviathan application]$ php composer.phar ncl CliScpJpScraping index 1
 */

// コントローラー名を必ず要求
if (!isset($argv[1])) {
	echo "[NCL Error] Please Enter Cli Controller Name\n";
	exit;
}

// パラメーター[1]をコントローラー名として取得
$controller = "index";
if (isset($argv[1])) $controller = $argv[1];

// コントローラー名によりコントローラークラス振分け
$className = 'Cli\\' . $controller;

// コントローラークラスインスタンス生成
$controllerInstance = new $className();

// パラメーター[2]をアクション名として取得
$action= 'index';
if (isset($argv[2])) $action = $argv[2];

// パラメーター[3]を引数として取得(あれば)
$argument= null;
if (isset($argv[3])) $argument = $argv[3];


// アクションメソッドを実行
$actionMethod = $action . 'Action';
$controllerInstance->$actionMethod($argument);

exit;