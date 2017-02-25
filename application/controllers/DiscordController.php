<?php
namespace Controllers;

use Controllers\Commons\AbstractController;
use Logics\DiscordLogic;
use Inputs\BasicInput;
use Cores\Config\Config;

/**
 * Class DiscordController
 * @package Controllers
 */
class DiscordController extends AbstractController
{
    /**
     * @var DiscordLogic
     */
    protected $DiscordLogic = null;

    /**
     * @var BasicInput
     */
    protected $input;
    
    protected function getLogic()
    {
        $this->DiscordLogic = new DiscordLogic();
    }
    
    protected function getInput()
    {
        $this->input = new BasicInput();
    }
    
    public function indexAction()
    {
        // TODO 暫定
        $channelName = "general";
        
        // ログの日付リストを生成
        $logArray = $this->DiscordLogic->getDiscordLogArray($channelName);

        $result = array(
            "logs" => $logArray,
            "channelName" => $channelName,
            "msg" => $this->DiscordLogic->getMsg(),
        );
        $this->getView("index", "Discord Log", $result);
    }
    
    public function logAction($date)
    {
        // TODO 暫定
        $channelName = "general";

        $logDir = Config::load("dir.logs") . "/discord/messages/{$channelName}";
        $jsons = file("{$logDir}/{$date}.log");

        // ログ・ファイル中のjson展開
        $datas = $this->DiscordLogic->parseLogJsons($jsons);

        $timestamp = strtotime($date);
        $before_date = ("2017-02-25" == $date) ? null : date('Y-m-d', strtotime('-1 day', $timestamp));
        $after_date = (date('Y-m-d') == $date) ? null : date('Y-m-d', strtotime('+1 day', $timestamp));

        $result = array(
            "datas" => $datas,
            "channelName" => $channelName,
            "date" => $date,
            "before_date" => $before_date,
            "after_date" => $after_date,
            "logsLink" => "/discord",
            "msg" => $this->DiscordLogic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/irc_log_search.js",
        );
        $this->getView("log", "Discord Log", $result, $jsPathArray);
    }
}