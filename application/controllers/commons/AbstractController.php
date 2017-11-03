<?php
namespace Controllers\Commons;

use Inputs\BasicInput;
use Logics\Commons\AbstractLogic;
use Inputs\Commons\AbstractInput;
use Smarty;

/**
 * Class AbstractController
 * 抽象コントローラ
 * indexメソッドを強制
 */
abstract class AbstractController
{
    /**
     * @var AbstractLogic
     */
    protected $logic;
    /**
     * @var AbstractInput|BasicInput
     */
    protected $input;
    
    /**
     * @var Smarty
     */
    protected $smarty;
    
    /**
     * AbstractController constructor.
     */
    public function __construct()
    {
        // セッションが無ければ、ここで開始
        if (!isset($_SESSION["id"])) {
            session_start();
        } else {
            // セッション延長
            setcookie(session_name(), session_id(), time() + 172800);
        }
        
        $this->smarty = new Smarty();
        $this->getLogic();
        $this->getInput();
    }
    
    /**
     * ロジックインスタンスの生成
     * @return mixed $LogicInstance
     */
    abstract protected function getLogic();
    
    /**
     * インプットインスタンスの生成
     * @return mixed $LogicInstance
     */
    abstract protected function getInput();
    
    /**
     * indexメソッドを強制
     * @return mixed
     */
    abstract public function indexAction();
    
    /**
     * ビューの読み込みラッパー
     * @param $tpl string           ページのファイル名(拡張子なし)
     * @param $page_title string    ページタイトル
     * @param $resultArray array    Controller内の処理結果
     * @param $jsPathArray array    JSファイルのパス
     * @param $header string    ヘッダーの追記
     */
    protected function getView($tpl, $page_title = "", $resultArray = array(), $jsPathArray = array(), $header = "")
    {
        // 読み込み先ディレクトリの設定
        $directory = strtolower(str_replace("s\\", "", str_replace("Controller", "", get_called_class())));
        $this->smarty->compile_dir = '/home/njr-sys/public_html/application/views/_compiles';
        
        // 変数準備
        $view = $this->getViewArray($page_title, $jsPathArray, $header);
        
        // サニタイズ
        $this->smarty->default_modifiers = array("escape:'html'");
//		$this->smarty->default_modifiers = array("escape:'html'",'nl2br');
        
        // 変数のアサイン
        $this->smarty->assign('template', "/home/njr-sys/public_html/application/views/templates/{$directory}/{$tpl}.tpl");
        $this->smarty->assign('view', $view);
        $this->smarty->assign('result', $resultArray);
        
        // キャッシュ有効/無効
        $this->smarty->caching = 0;
        
        // 表示
        $this->smarty->display('file:/home/njr-sys/public_html/application/views/template.tpl');
    }
    
    /**
     * ビューの読み込みラッパー(WebApps)
     * @param $tpl string           ページのファイル名(拡張子なし)
     * @param $page_title string    ページタイトル
     * @param $resultArray array    Controller内の処理結果
     * @param $jsPathArray array    JSファイルのパス
     * @param $noJquery bool    Jquery無効化
     */
    protected function getViewWebApps($tpl, $page_title = "", $resultArray = array(), $jsPathArray = array(), $noJquery = false)
    {
        // 読み込み先ディレクトリの設定
        $directory = strtolower(str_replace("s\\", "", str_replace("Controller", "", get_called_class())));
        $this->smarty->compile_dir = '/home/njr-sys/public_html/application/views/_compiles';
        
        // View変数準備
        $view = $this->getViewArray($page_title, $jsPathArray);
        
        // View変数の書き換え・追加
        $view["css"] = "application/views/assets/css/web_apps.css";
        $view["appClass"] = $tpl;
        $view["noJquery"] = $noJquery;
        
        // サニタイズ
        $this->smarty->default_modifiers = array("escape:'html'");
        
        // 変数のアサイン
        $this->smarty->assign('template', "/home/njr-sys/public_html/application/views/templates/{$directory}/{$tpl}.tpl");
        $this->smarty->assign('view', $view);
        $this->smarty->assign('result', $resultArray);
        
        // キャッシュ有効/無効
        $this->smarty->caching = 0;
        
        // 表示
        $this->smarty->display('file:/home/njr-sys/public_html/application/views/web_apps_template.tpl');
    }
    
    /**
     * ビューの読み込みラッパー(Develop)
     * @param $tpl string           ページのファイル名(拡張子なし)
     * @param $page_title string    ページタイトル
     * @param $resultArray array    Controller内の処理結果
     * @param $jsPathArray array    JSファイルのパス
     */
    protected function getViewDev($tpl, $page_title = "", $resultArray = array(), $jsPathArray = array())
    {
        // 読み込み先ディレクトリの設定
        $directory = strtolower(str_replace("s\\", "", str_replace("Controller", "", get_called_class())));
        $this->smarty->compile_dir = '/home/njr-sys/public_html/application/views/_compiles';
        
        // View変数準備
        $view = $this->getViewArray($page_title, $jsPathArray);
        
        // View変数の書き換え・追加
        $view["css"] = "application/views/assets/css/dev_nijiru.css";
        $view["appClass"] = $tpl;
        
        // サニタイズ
        $this->smarty->default_modifiers = array("escape:'html'");
        
        // 変数のアサイン
        $this->smarty->assign('template', "/home/njr-sys/public_html/application/views/templates/{$directory}/{$tpl}.tpl");
        $this->smarty->assign('view', $view);
        $this->smarty->assign('result', $resultArray);
        
        // キャッシュ有効/無効
        $this->smarty->caching = 0;
        
        // 表示
        $this->smarty->display('file:/home/njr-sys/public_html/application/views/dev_template.tpl');
    }
    
    /**
     * ビューの配列を生成する
     * @param null $page_title
     * @param null $jsPathArray
     * @param null $header
     * @return array
     */
    protected function getViewArray($page_title = null, $jsPathArray = null, $header = null)
    {
        $view = array(
            "serverName" => 'njr-sys.net',
            "page_title" => $page_title,
            "css" => 'application/views/assets/css/nijiru.css',
            "icon" => 'application/views/assets/img/common/nijiru-icon.ico',
            "imgDir" => 'application/views/assets/img/',
            "jsPathArray" => $jsPathArray,
            "id" => $this->input->getSession("id"),
            "user_name" => $this->input->getSession("user_name"),
            "point" => $this->input->getSession("point"),
            "tera_point" => $this->input->getSession("tera_point"),
            "loginRedirect" => \Cores\Helper\UrlHelper::convertGetParam(),
            "header" => $header,
        );
        return $view;
    }
    
    /**
     * Redirect
     * @param $prefix
     * @param $action
     * @param null $arg
     */
    public function redirect($prefix, $action = null, $arg = null)
    {
        if ($action) {
            $action = '/' . $action;
        }
        if ($arg) {
            $arg = '/' . $arg;
        }
        
        header('Location: http://njr-sys.net/' . $prefix . $action . $arg);
        exit;
    }
    
    /**
     * 汎用Redirect
     * @param $url
     */
    public function redirectTo($url)
    {
        header('Location: ' . $url);
        exit;
    }
}