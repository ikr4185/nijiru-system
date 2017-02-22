<?php
namespace Logics;

use Cores\Config\Config;
use Cli\Commons\Console;
use Logics\Commons\AbstractLogic;
use Logics\Commons\Api;

use Ratchet\Wamp\Exception;
use React\EventLoop\Factory;
use Logics\Discord\Client;
use Logics\Discord\WebSocketClient;

class DiscordLogic extends AbstractLogic
{
    const END_POINT_BASE_URL = "https://discordapp.com/api";
    
    /**
     * @var Api
     */
    protected $Api = null;

    protected $client = null;

    /**
     * @var Object
     */
    protected $gatewayConnection = array();

    public function __destruct()
    {
        Console::log("Closing.");
    }
    
    protected function getModel()
    {
        $this->Api = new Api();
    }

    /**
     * リクエスト送信
     * ※デフォルトはGETメソッドを想定
     * @param $url
     * @param bool $isPost
     * @param null $data
     * @return mixed
     */
    public function request($url, $isPost = false, $data = null)
    {
        $url = self::END_POINT_BASE_URL . $url;

        $token = Config::load("discord.token");
        
        $header = array(
            'Authorization: Bot ' . $token,
        );
        
        $ua = "DiscordBot (njr-sys.net, 7.0)";
        
        $response = $this->Api->curl($url, $header, $ua, $isPost, $data);

        if ($response["errNo"] !== 0) {
            return $response["error"];
        }
        return $response["body"];
    }

    /**
     * WebSocket接続確立
     * @return mixed
     */
    public function connectGateway()
    {
        $url = self::END_POINT_BASE_URL . "/gateway/bot";
        $token = Config::load("discord.token");
        $header = array(
            'Authorization: Bot ' . $token,
        );
        $ua = "DiscordBot (njr-sys.net, 7.0)";

        $response = $this->Api->curl($url, $header, $ua);

        // curl接続失敗時の処理
        if ($response["errNo"] !== 0) {
            return $response["error"];
        }

        // Gateway接続情報のキャッシュ
        $this->gatewayConnection = json_decode($response["body"]);
        //{"url": "wss://gateway.discord.gg", "shards": 1}"
        
        return $this->gatewayConnection;
    }

    public function runBot($host, $port, $path)
    {
        try {
            $loop = Factory::create();
            $client = new WebSocketClient(new Client, $loop, $host, $port, $path);
            $loop->run();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}