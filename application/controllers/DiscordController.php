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
        $subDirs = $this->DiscordLogic->getSubDirList(Config::load("dir.logs") . "/discord/messages/");

        $channels = array();
        foreach ($subDirs as $subDir) {

            // 除外設定
            $omits = array(
                "282762114962161666_general",
                "284683472940040192_test_channel",
//                "297638542346158081_general",
                "297640858646347777_community",
                "297641545358901251_guidelines",
                "297641900222185472_membership",
                "297648958078058499_site_technical",
                "297649290417930240_extracurricular",
                "297649519510945794_propsal",
                "297652209481547777_small_talk",
                "297652880943349770_event",
            );
            if (in_array($subDir, $omits)) {
                continue;
            }
            
            $channelName = preg_replace('/^\d*?_(.*?)$/', '$1', $subDir);
            
            // ログの日付リストを生成
            $channels[$channelName] = $this->DiscordLogic->getDiscordLogArray($subDir);
        }

        
        $result = array(
            "channels" => $channels,
            "msg" => $this->DiscordLogic->getMsg(),
        );
        $this->getView("index", "Discord Log", $result);
    }

    public function logAction($channelName)
    {

        $date = $this->input->getRequest("date",true);
        if (empty($date)) {
            $this->getView("index", "404", array());
        }

        // 該当チャンネルの該当ディレクトリを探す
        $subDirs = $this->DiscordLogic->getSubDirList(Config::load("dir.logs") . "/discord/messages/");
        $dir = "";
        foreach ($subDirs as $subDir) {

            // 除外設定
            $omits = array(
                "282762114962161666_general",
                "284683472940040192_test_channel",
            );
            if (in_array($subDir, $omits)) {
                continue;
            }

            // 該当チャンネル名があったら
            if (strpos($subDir, $channelName) !== false) {
                $dir = $subDir;
                break;
            }
        }
        
        $logDir = Config::load("dir.logs") . "/discord/messages/{$dir}";
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