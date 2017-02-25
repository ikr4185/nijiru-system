<?php
namespace Controllers;

use Controllers\Commons\AbstractController;
use Inputs\BasicInput;

use Cores\Config\Config;

/**
 * Class DiscordController
 * @package Controllers
 */
class DiscordController extends AbstractController
{

//    /**
//     * @var IrcLogic
//     */
//    protected $IrcLogic;
//    /**
//     * @var Irc81Logic
//     */
//    protected $Irc81Logic;
    /**
     * @var BasicInput
     */
    protected $input;
    
    protected function getLogic()
    {
//        $this->IrcLogic = new IrcLogic();
//        $this->Irc81Logic = new Irc81Logic();
    }
    
    protected function getInput()
    {
        $this->input = new BasicInput();
    }
    
    public function indexAction()
    {
//        // ログの日付リストを生成
//        $logArray = $this->IrcLogic->getIrcLogArray();
//
//        $result = array(
//            "logs" => $logArray,
//            "msg" => $this->IrcLogic->getMsg(),
//        );
//        $this->getView("index", "IRC-Reader", $result);
        
    }
    
    public function logAction($date)
    {
        $channel_id = 282762114962161666;
        $logDir = Config::load("dir.logs") . "/discord/messages/{$channel_id}";
        $jsons = file("{$logDir}/{$date}.log");

        $datas = array();
        $users = array();
        foreach ($jsons as $json) {

            $data = json_decode($json);

            // ユーザIDとニックネームの紐付け配列
            if (!isset($users[$data[2]])) {
                $users[$data[2]] = $data[1];
            }

            // データ格納
            $datas[] = array(
                "datetime" => date("H:i:s", strtotime($data[0])),
                "nick" => $data[1],
                "message" => htmlspecialchars($data[3]),
            );
        }

        $userIds = array_keys($users);

        foreach ($userIds as $userId) {

            // ＠付き返信の変換
            foreach ($datas as &$data) {

                if (strpos($data["message"], (string)$userId) !== false) {
                    $data["message"] = str_replace("@{$userId}", "<span style=\"color:blue;\">@{$users[$userId]}</span>", $data["message"]);
                }
            }
            unset($data);

        }

        $timestamp = strtotime($date);
        $before_date = ("2017-02-25" == $date) ? null : date('Y-m-d', strtotime('-1 day', $timestamp));
        $after_date = (date('Y-m-d') == $date) ? null : date('Y-m-d', strtotime('+1 day', $timestamp));

        $result = array(
            "datas" => $datas,
            "date" => $date,
            "before_date" => $before_date,
            "after_date" => $after_date,
            "logsLink" => "/discord",
//            "msg" => $this->IrcLogic->getMsg(),
            "msg" => "",
        );
        $this->getView("log", "Discord Log", $result);
    }
}