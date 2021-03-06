<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\IrcLogic;
use Logics\Irc81Logic;

/**
 * Class IrcController
 * @package Controllers
 */
class IrcController extends WebController
{
    /**
     * @var IrcLogic
     */
    protected $IrcLogic;
    
    /**
     * @var Irc81Logic
     */
    protected $Irc81Logic;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->IrcLogic = new IrcLogic();
        $this->Irc81Logic = new Irc81Logic();
    }
    
    public function indexAction()
    {
        // ログの日付リストを生成
        $logArray = $this->IrcLogic->getIrcLogArray();
        
        $result = array(
            "logs" => $logArray,
            "msg" => $this->IrcLogic->getMsg(),
        );
        $this->getView("index", "IRC-Reader", $result);
    }
    
    public function logAction($date)
    {
        // バリデーション
        $this->IrcLogic->validateDate($date);
        
        // 記事読み込み +  パース
        $html = $this->IrcLogic->getLog($date);
        
        // 日付の調整
        $timestamp = strtotime($date);
        $before_date = ("2014-06-28" == $date) ? null : date('Y-m-d', strtotime('-1 day', $timestamp));
        $after_date = (date('Y-m-d') == $date) ? null : date('Y-m-d', strtotime('+1 day', $timestamp));
        
        $result = array(
            "html" => $html,
            "logsLink" => "/irc",
            "date" => $date,
            "before_date" => $before_date,
            "after_date" => $after_date,
            "msg" => $this->IrcLogic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/irc_log_search.js",
        );
        $this->getView("log", "IRC-Reader", $result, $jsPathArray);
    }
    
    public function logs81Action()
    {
        // ログの日付リストを生成
        $logArray = $this->Irc81Logic->getIrcLog81Array();
        
        $result = array(
            "logs" => $logArray,
            "msg" => $this->IrcLogic->getMsg(),
        );
        $this->getView("index_81", "IRC-Reader #site8181", $result);
    }
    
    public function log81Action($date)
    {
        // バリデーション
        $this->IrcLogic->validateDate($date);
        
        // DB読み込み
        $logs = $this->Irc81Logic->getLog81($date);
        
        // 日付の調整
        $timestamp = strtotime($date);
        $before_date = ("2014-06-28" == $date) ? null : date('Y-m-d', strtotime('-1 day', $timestamp));
        $after_date = (date('Y-m-d') == $date) ? null : date('Y-m-d', strtotime('+1 day', $timestamp));
        
        $result = array(
            "logs" => $logs,
            "logsLink" => "/irc/logs81",
            "date" => $date,
            "before_date" => $before_date,
            "after_date" => $after_date,
            "msg" => $this->IrcLogic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/irc_log_search.js",
        );
        $this->getView("log_81", "IRC-Reader #site8181", $result, $jsPathArray);
    }
    
    /**
     * 下書き批評予約
     * @param $date
     */
    public function draftReserveAction($date)
    {
        if (empty($date)) {
            $this->draftReserveIndex();
            exit;
        }
        
        // バリデーション
        $this->IrcLogic->validateDate($date);
        
        // 予約内容の取得と追記
        $data = array(
            "name" => "",
            "title" => "",
            "url" => "",
        );
        if ($this->input->checkRequest("submit")) {
            
            $name = $this->input->getRequest("name");
            $title = $this->input->getRequest("title");
            $url = $this->input->getRequest("url");
            
            $this->IrcLogic->setDraftReserve($date, $name, $title, $url);
            $data = array(
                "name" => $name,
                "title" => $title,
                "url" => $url,
            );
            
            // リダイレクト
            $this->redirect("irc", "draftReserve", $date);
        }
        
        // 予約一覧読み込み
        $reserve = $this->IrcLogic->getDraftReserve($date);
        
        $result = array(
            "reserve" => $reserve,
            "data" => $data,
            "date" => $date,
            "before_date" => date("Y-m-d", strtotime($date . '-1 day')),
            "after_date" => date("Y-m-d", strtotime($date . '+1 day')),
            "msg" => $this->IrcLogic->getMsg(),
        );
        $jsPathArray = array();
        $this->getView("draft_reserve", "IRC-Reader draft_reserve", $result, $jsPathArray);
        
    }
    
    /**
     * 下書き批評予約 -- 一覧
     */
    public function draftReserveIndex()
    {
        $logs = $this->IrcLogic->getDraftReserveFiles();
        
        $result = array(
            "logs" => $logs,
            "msg" => $this->IrcLogic->getMsg(),
        );
        $this->getView("draft_reserve_index", "IRC-Reader draft_reserve", $result);
    }
    
    /**
     * IRCログ 検索
     */
    public function searchAction()
    {
        // POST取得
        $searchStr = $this->input->getRequest("search");
        
        // 全ログ検索
        $searchResult = array();
        if (!empty($searchStr)) {
            $searchResult = $this->IrcLogic->searchIrc($searchStr);
        }
        
        $result = array(
            "searchLimit" => $this->IrcLogic->getSearchLimit(),
            "searchStr" => $searchStr,
            "searchResult" => $searchResult,
            "msg" => $this->IrcLogic->getMsg(),
        );
        $jsPathArray = array();
        $this->getView("search", "IRC-Reader Search", $result, $jsPathArray);
    }
}