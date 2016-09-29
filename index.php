<?php
/**
 * ディスパッチ実行スクリプト
 */

// セッションが無ければ、ここで開始
if ( !isset($_SESSION["id"]) ) {
	session_start();
}else{
	// セッション延長
	setcookie( session_name(), session_id(), time() + 172800 );
}

// システム設定ファイルを読み込み
require_once './class/FilePath/FilePath.php';

// 汎用静的関数ラッパーの読み込み
require_once STATICS;

// ディスパッチャークラスファイルを読み込み（system配下のもの）
require_once DISPATCHER_PHP;

// ディスパッチャー・インスタンスの立ち上げ
$dispatcher = new Dispatcher();

// システムルートをシステム設定ファイルで指定されたルートに設定
$dispatcher->setSystemRoot(SYSTEM_ROOT);

// ディスパッチ実行
$dispatcher->dispatch();