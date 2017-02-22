<?php

namespace Logics\Discord;

use WebSocket\Client;
use WebSocket\ConnectionException;

use Cores\Config\Config;
use Cli\Commons\Console;

class DiscordClient
{
    /**
     * @var Client
     */
    protected $client = null;

    public function __destruct()
    {
        Console::log("Disconnect.");
        $this->client->close();
    }

    /**
     * @see http://qiita.com/rhosoi/items/a11682fae0d9beceedb8
     * @param $url
     */
    public function connectWebSocket($url)
    {
        Console::log("[connectWebSocket]========================================");


        $url = $url . "?v=5&encoding=json";
        $options = array('timeout' => 180);
        $this->client = new Client($url, $options);

        $isConnect = false;
        try {

            // 接続開始
            if (!$isConnect) {
                Console::log("Connecting {$url}");
                $json = $this->client->receive();
                Console::log("Receive: {$json}");

                /*
                HTTP/1.1 101 Switching Protocols
                Date: Sun, 19 Feb 2017 15:21:40 GMT
                Connection: upgrade
                Set-Cookie: __cfduid=d0690478c452a3c62a9a5ee130961d33b1487517699; expires=Mon, 19-Feb-18 15:21:39 GMT; path=/; domain=.discord.gg; HttpOnly
                upgrade: websocket
                sec-websocket-accept: Lcp8Q60yGT5Ztz13sQtiTguQrzM=
                Server: cloudflare-nginx
                CF-RAY: 333ab5b8ee980b50-NRT
                */

                // WebSocket接続後即時に、OP 1 Heartbeat ペイロードを送信
                Console::log("Send OP 1 Heartbeat payloads.");
                $this->gatewayHeartbeat();

                $json = $this->client->receive();
                Console::log("Receive: {$json}");

                // OP 2 Identify ペイロードを送信
                // 'heartbeat_interval' => 41250
                Console::log("Send OP 2 Identify payloads.");
                $this->gatewayIdentify();

                $json = $this->client->receive();
                Console::log("Receive: {$json}");
            }

            // 接続完了
            Console::log("Connect Success.");
            $isConnect = true;

        } catch (ConnectionException $e) {
            Console::log("Connect Failed.");
            die($e->getMessage());
        } catch (\Exception $e) {
            Console::log("Failed.");
            die($e->getMessage());
        }

        // debug ////////////////////////////////////////
        $flag = true;
        while ($flag) {

            sleep(3);

            // OP 1 Heartbeat 定期送信
            Console::log("Send OP 1 Heartbeat payloads.");
            $this->gatewayHeartbeat();

            // debug ////////////////////////////////////////
            $flag = false;
        }
    }

    protected function gatewayHeartbeat()
    {
        $payload = new \stdClass();
        $payload->op = 1;
        $payload->d = 251;
        $payload = json_encode($payload);
        Console::log($payload);

        $this->client->send($payload);
    }

    protected function gatewayIdentify()
    {

        $data = (object)array(
            'token' => Config::load("discord.token"),
            'properties' =>   (object)array(
                '$os' =>   'linux',
                '$browser' =>   'njr-sys.net',
                '$device' =>   'njr-sys.net',
                '$referrer' =>   '',
                '$referring_domain' =>   '',
            ),
            'compress' =>   false,
            "large_threshold" => 250,
            'shard' =>   array(0, 1),
        );

        $payload = (object)array(
            'op' => 2,
            'd' => $data,
        );

        $payload = json_encode($payload);
        Console::log($payload);

        $this->client->send($payload);
    }
}