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


        // 認証チェック
        $isStaff = $this->auth();
        
        $channels = array();
        foreach ($subDirs as $subDir) {
            
            $isPrivate = true;

            // 除外設定
            if (self::isOmitDir($subDir)) {
                continue;
            }
            // 非公開設定
            if ($isStaff || self::isPublicDir($subDir)) {
                $isPrivate = false;
            }
            
            $channelName = preg_replace('/^\d*?_(.*?)$/', '$1', $subDir);
            
            // ログの日付リストを生成
            $channels[$channelName] = array(
                "logs" => $this->DiscordLogic->getDiscordLogArray($subDir),
                "isPrivate" => $isPrivate,
            );
        }
        
        ksort($channels);

        $result = array(
            "channels" => $channels,
            "msg" => $this->DiscordLogic->getMsg(),
        );
        $this->getView("index", "Discord Log", $result);
    }

    public function logAction($channelName)
    {
        $date = $this->input->getRequest("date", true);
        if (empty($date)) {
            $this->getView("index", "404", array());
        }
        
        // 認証チェック
        $isStaff = $this->auth();

        // 該当チャンネルの該当ディレクトリを探す
        $subDirs = $this->DiscordLogic->getSubDirList(Config::load("dir.logs") . "/discord/messages/");
        $dir = "";
        foreach ($subDirs as $subDir) {

            // 除外設定
            if (self::isOmitDir($subDir)) {
                continue;
            }
            // 非公開設定
            if (!$isStaff && !self::isPublicDir($subDir)) {
                continue;
            }

            // 該当チャンネル名があったら
            if (strpos($subDir, $channelName) !== false) {
                $dir = $subDir;
                break;
            }
        }

        if (empty($dir)) {
            $this->attackRecord();
            die("bad request.");
        }
        
        $logDir = Config::load("dir.logs") . "/discord/messages/{$dir}";

        
        // 該当日のログファイルの取得
        $jsons = file("{$logDir}/{$date}.log");
        
        // ログファイル中のjson展開
        $datas = $this->DiscordLogic->parseLogJsons($jsons);
        
        // 前後のログを取得
        $before_date = ("2017-02-25" == $date) ? null : $this->DiscordLogic->getRecentLog($logDir, $date, true);
        $after_date = (date('Y-m-d') == $date) ? null : $this->DiscordLogic->getRecentLog($logDir, $date, false);
        
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
    
    /**
     * パスワード認証
     */
    public function authAction()
    {
        if (!$this->auth()) {
            $this->attackRecord();
            die("bad request.");
        }
        $this->redirect("discord");
    }

    protected function auth()
    {
        $pass = $this->input->getRequest("staff_pass");
        if (!$pass) {
            $pass = $this->input->getSession("staff_pass");
        }

        if ($pass == Config::load("staff.pass")) {
            $this->input->setSession("staff_pass", $pass);
            return true;
        }
        return false;
    }

    /**
     * 完全非公開の判定（テスト用チャンネル等）
     * @param $subDir
     * @return bool
     */
    protected static function isOmitDir($subDir)
    {
        $omit = array(
            "282762114962161666_general",
            "284683472940040192_test_channel",
        );
        if (in_array($subDir, $omit)) {
            return true;
        }
        return false;
    }

    /**
     * 外部公開の判定
     * @param $subDir
     * @return bool
     */
    protected static function isPublicDir($subDir)
    {
        $public = array(
            "297652880943349770_event",
            "297638542346158081_general",
            "297641545358901251_guidelines",
            "297649519510945794_propsal",
            "297652209481547777_small_talk",
        );
        if (in_array($subDir, $public)) {
            return true;
        }
        return false;
    }

    /**
     * アクセス記録
     */
    private function attackRecord()
    {
        $data['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
        $data['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
        $data['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
        $fileName = Config::load("dir.logs") . "/discord/system/attack.log";
        file_put_contents($fileName, json_encode($data) . "\n", FILE_APPEND);
    }
}