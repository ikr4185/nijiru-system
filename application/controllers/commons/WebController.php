<?php
namespace Controllers\Commons;

use Inputs\BasicInput;
use Logics\UsersLogic;

/**
 * Class AbstractController
 * 抽象コントローラ
 * indexメソッドを強制
 */
class WebController extends AbstractController
{
    /**
     * @var UsersLogic
     */
    protected $UsersLogic;

    public function __construct()
    {
        parent::__construct();
    }

    protected function getLogic()
    {
        $this->UsersLogic = new UsersLogic();
    }

    protected function getInput()
    {
        $this->input = new BasicInput();
    }

    public function indexAction()
    {
        // do something
    }

    /**
     * ビューの読み込みラッパー: 継承
     * @param $tpl string           ページのファイル名(拡張子なし)
     * @param $page_title string    ページタイトル
     * @param $resultArray array    Controller内の処理結果
     * @param $jsPathArray array    JSファイルのパス
     * @param $header string    ヘッダーの追記
     */
    protected function getView($tpl, $page_title = "", $resultArray = array(), $jsPathArray = array(), $header = "")
    {
        parent::getView($tpl, $page_title, $resultArray, $jsPathArray, $header);
    }

    /**
     * ビューの配列を生成する: 上書き
     * @param null $page_title
     * @param null $jsPathArray
     * @param null $header
     * @return array
     */
    protected function getViewArray($page_title = null, $jsPathArray = null, $header = null)
    {
        $userId = $this->input->getSession("id");
        $asset = array();
        if (!empty($userId)) {
            $asset = $this->UsersLogic->getAssets($userId);
        }
        
        $view = array(
            "serverName" => 'njr-sys.net',
            "page_title" => $page_title,
            "css" => 'application/views/assets/css/nijiru.css',
            "icon" => 'application/views/assets/img/common/nijiru-icon.ico',
            "imgDir" => 'application/views/assets/img/',
            "jsPathArray" => $jsPathArray,
            "id" => $userId,
            "user_name" => $this->input->getSession("user_name"),
            "point" => (isset($asset["point"])) ? $asset["point"] : null,
            "tera_point" => (isset($asset["point"])) ? $asset["tera_point"] : null,
            "loginRedirect" => \Cores\Helper\UrlHelper::convertGetParam(),
            "header" => $header,
        );

        return $view;
    }
}