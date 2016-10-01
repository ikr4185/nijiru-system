<?php
/**
 * Class Dispatcher
 * コントローラー振り分けクラス
 */
class Dispatcher
{
    private $sysRoot;

    /**
     * システムルート設定
     * @param $path string
     */
    public function setSystemRoot($path)
    {
        // 単純にケツの / をとったやつをルートとして指定する
        $this->sysRoot = rtrim($path, '/');
    }

    /**
     * コントローラーインスタンス・アクションメソッドの生成・実行
     */
    public function dispatch()
    {
        // パラメーター取得（末尾の / は削除）
        $param = ereg_replace('/?$', '', $_SERVER['REQUEST_URI']);

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
        $className = ucfirst(strtolower($controller)) . 'Controller';

        // コントローラークラスファイル読込
        require_once $this->sysRoot . '/Controllers/' . $className . '.php';

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
    }
}

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