<?php
namespace Logics\Commons;

//use Cli\Commons\Console;
use Cores\Config\Config;

/**
 * Class ChatWorkApi
 * @package Logics\Commons
 */
class ChatWorkApi extends Api
{
    const UA = "Nijiru System (njr-sys.net, 7.0)";

    public function execApi($task, $arg = null, $postData = null)
    {
        // リクエストURL
        if (!strpos($task, '.')) {
            $requestUrl = "{$task}";
        } else {
            $tmp = explode(".", $task);
            $requestUrl = "{$tmp[0]}/{$tmp[1]}";
        }

        // 引数がある場合
        if (!empty($arg)) {
            $requestUrl .= "/{$arg}";
        }

        // リクエスト生成
        $endpoint = Config::load("chatwork.endpoint");
        $apiToken = Config::load("chatwork.key");
        $header = array("X-ChatWorkToken: {$apiToken}");

        // POST/GET判定
        $isPost = false;
        if (!empty($postData)) {
            $isPost = true;
        }

        // 実行
        $response = Api::curl("{$endpoint}/{$requestUrl}", $header, self::UA, $isPost, $postData, false);

        // 整形
        $response["head"] = explode("\r\n", trim($response["head"]));
        $response["body"] = json_decode($response["body"]);

        return $response;
    }

    /**
     * @return mixed
     */
    public function me()
    {
        return $this->execApi("me");
    }

    /**
     * @param $task
     * @return bool|mixed
     */
    public function my($task)
    {
        if ($task != "status" || $task != "tasks") {
            return false;
        }
        return $this->execApi("my.{$task}");
    }

    /**
     * @return mixed
     */
    public function contacts()
    {
        return $this->execApi("contacts");
    }

    /**
     * @see http://developer.chatwork.com/ja/endpoint_rooms.html#POST-rooms-room_id-messages
     * @param string $roomId
     * @param string $arg
     * @param array $postData
     * @return mixed
     */
    public function rooms($roomId = null, $arg = null, $postData = null)
    {
        if (empty($roomId)) {

            if (empty($arg)) {
                // チャット一覧の取得
                return $this->execApi("rooms");
            }
            // グループチャットの新規作成
            return $this->execApi("rooms", null, $postData);

        }

        if (empty($arg)) {
            // チャットの名前、アイコン、種類(my/direct/group)を取得
            return $this->execApi("rooms.{$roomId}");
        } elseif ($arg == "messages") {

            if (empty($postData)) {
                // チャットのメッセージ一覧を取得。パラメータ未指定だと前回取得分からの差分のみを返します。(最大100件まで取得)
                return $this->execApi("rooms.{$roomId}", $arg);
            }

            // チャットに新しいメッセージを追加
            return $this->execApi("rooms.{$roomId}", $arg, $postData);
        }

        return false;
    }

}
