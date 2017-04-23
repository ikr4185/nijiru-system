<?php
namespace Logics;

use Logics\IrcLogic;
use \Cores\Config\Config;

/**
 * ログ閲覧に関するロジック
 * Class DiscordLogic
 * @package Logics
 */
class DiscordLogic extends IrcLogic
{
    
    protected $logsDir;
    
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        
        // 初期設定
        $this->logsDir = Config::load("dir.logs");
    }
    
    protected function getModel()
    {
    }

    public function getDiscordLogArray($channel_id)
    {
        $logDir = $this->logsDir."/discord/messages/{$channel_id}";
        $extension = "log";

        $logs = $this->getLogList($logDir,$extension);
    
        // 発言数バーを格納
        $logs = $this->renderBar($logs);
        
        return $logs;
    }
    
    public function parseLogJsons($jsons)
    {
        // 配列の生成
        $datas = array();
        $users = array();
        foreach ($jsons as $json) {
        
            $data = json_decode($json);
        
            // ユーザIDとニックネームの紐付け配列
            if (!isset($users[$data[2]])) {
                $users[$data[2]] = $data[1];
            }
        
            // 色設定
            $color = $this->getColor($data[1]);
            $isBot = false;
            if (strpos($data[1], "KASHIMA") !== false) {
                $color = "KASHIMA-EXE";
                $isBot = true;
            }
            $operators = array("Holy_nova", "kasyu-maki", "unReGret", "jet0620");
            if (in_array($data[1], $operators)) {
                $color = "irc-color-op";
            }
        
            // データ格納
            $datas[] = array(
                "datetime" => date("H:i:s", strtotime($data[0])),
                "nick" => $data[1],
                "color" => $color,
                "isBot" => $isBot,
                "message" => htmlspecialchars($data[3]),
            );
        }
    
        $userIds = array_keys($users);
    
        // 生成された配列の調整
        foreach ($datas as &$data) {
        
            // URLリンク生成
            $data["message"] = preg_replace('/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w-.\/?%&=:;|#,]*)?/', "<a href=\"$0\">$0</a>", $data["message"]);
        
            // ＠付き返信の変換
            foreach ($userIds as $userId) {
            
                if (strpos($data["message"], (string)$userId) !== false) {
                    $data["message"] = str_replace("@{$userId}", "<span class=\"" . $this->getColor($users[$userId]) . "\">@{$users[$userId]}</span>", $data["message"]);
                }
            
            }
        
        }
        unset($data);
        
        // 結果を返す
        return $datas;
    }
    
    /**
     * @param string $logDir
     * @param string $date
     * @param bool $isPre
     * @return mixed
     */
    public function getRecentLog($logDir, $date, $isPre)
    {
        $extension = "log";
        $logs = $this->getLogList($logDir,$extension);
    
        foreach ($logs as $key=>$log) {
            if ($log["timestamp"] === strtotime($date)) {
    
                if ($isPre) {
    
                    if ($key===0) {
                        return $date;
                    }
                    return $logs[$key-1][0];
                    
                }
    
                if (isset($logs[$key+1])) {
                    return $logs[$key+1][0];
                }
                return $date;
                
                break;
            }
        }
        return $date;
    }
    
}