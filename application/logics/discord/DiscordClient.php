<?php

namespace Logics\Discord;

//use WebSocket\Client;
use WebSocket\ConnectionException;

use Logics\Commons\Api;
use Logics\IrcLogic;

use Cores\Config\Config;
use Cli\Commons\Console;

class DiscordClient
{
    const END_POINT_BASE_URL = "https://discordapp.com/api";
    
    /**
     * @var Api
     */
    protected $Api = null;

    /**
     * @var IrcLogic
     */
    protected $Irc = null;

    /**
     * @var ClientWrapper
     */
    protected $client = null;

    /**
     * @var array Channel IDs
     */
    protected $channels = array();

    /**
     * デバッグ判定
     * @var bool
     */
    protected $isDebugMode = true;

    /**
     * タイマー設定
     * @var array
     */
    protected $timers = array();

    public function __construct()
    {
        $this->Api = new Api();
        $this->Irc = new IrcLogic();
    }

    public function __destruct()
    {
        Console::log("Disconnect.", "SYSTEM");
        $this->client->close();
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
        //{"url": "wss://gateway.discord.gg", "shards": 1}"
        $connection = json_decode($response["body"]);
        
        return $connection;
    }
    
    /**
     * @see http://qiita.com/rhosoi/items/a11682fae0d9beceedb8
     * @param $url
     */
    public function connectWebSocket($url)
    {
        Console::log(" Connection Start. ========================================", "SYSTEM");

        $url = $url . "?v=5&encoding=json";

        // タイムアウト
        $options = array('timeout' => 30);

        try {

            // 接続開始
            Console::log("Connecting {$url}", "SYSTEM");
            $this->client = new ClientWrapper($url, $options);

            $json = $this->client->receive();
            $this->parseReceive($json);

            // HeartBeat 送信時刻を記録
            $heartbeatInterval = json_decode($json)->d->heartbeat_interval;
            $lastHeartBeat = time();

            // WebSocket接続後即時に、OP 1 Heartbeat ペイロードを送信
            Console::log("Send OP 1 Heartbeat payloads.", "SYSTEM");
            $this->gatewayHeartbeat();

            $json = $this->client->receive();
            $this->parseReceive($json);

            // OP 2 Identify ペイロードを送信
            // 'heartbeat_interval' => 41250
            Console::log("Send OP 2 Identify payloads.", "SYSTEM");
            $this->gatewayIdentify();

            // [READY] check
            $json = $this->client->receive();
            $this->parseReceive($json);

            // 接続完了
            Console::log("Connect Success.", "SYSTEM");

        } catch (ConnectionException $e) {
            Console::log("Connect Failed.", "SYSTEM");
            die($e->getMessage());
        } catch (\Exception $e) {
            Console::log("Failed.", "SYSTEM");
            die($e->getMessage());
        }

        $flag = true;
        while ($flag) {

            // タイマー機能__時刻チェック
            $this->checkTimer();

            // 受信開始
            $json = $this->client->receive();
            $isEvent = $this->parseReceive($json);

            // イベントパース失敗、またはHeartBeat期限直前だったら
            if (!$isEvent || time() > $heartbeatInterval + $lastHeartBeat - 10) {

                // OP 1 Heartbeat 送信
                Console::log("Send OP 1 Heartbeat payloads.", "SYSTEM");
                $this->gatewayHeartbeat();

                // 受信
                $json = $this->client->receive();
                $this->parseReceive($json);

                // HeartBeat 送信時刻を記録
                $lastHeartBeat = time();
            }
        }
    }

    /**
     * OP 1 Heartbeat 送信
     * @throws \WebSocket\BadOpcodeException
     */
    protected function gatewayHeartbeat()
    {
        $payload = new \stdClass();
        $payload->op = 1;
        $payload->d = 251;
        $payload = json_encode($payload);

        $this->client->send($payload);
    }

    /**
     * OP 2 Identify ペイロードを送信
     * @throws \WebSocket\BadOpcodeException
     */
    protected function gatewayIdentify()
    {
        $data = (object)array(
            'token' => Config::load("discord.token"),
            'properties' => (object)array(
                '$os' => 'linux',
                '$browser' => 'njr-sys.net',
                '$device' => 'njr-sys.net',
                '$referrer' => '',
                '$referring_domain' => '',
            ),
            'compress' => false,
            "large_threshold" => 250,
            'shard' => array(0, 1),
        );

        $payload = (object)array(
            'op' => 2,
            'd' => $data,
        );

        $payload = json_encode($payload);
//        Console::log($payload,"SYSTEM");

        $this->client->send($payload);
    }

    /**
     * Receiveペイロード毎の分岐処理
     * @param $json
     * @return bool
     */
    protected function parseReceive($json)
    {

        $receive = json_decode($json);

        // ex) Timeout
        if ($receive === null) {
            Console::log("Receive Null", "RECEIVE");
            return false;
        }

        if ($this->isDebugMode) {
            Console::log("DEBUG DUMP", "SYSTEM");
            var_dump($receive);
        }

        // 非イベント時の動作(!OP 0 Dispatch)
        if (!isset($receive->t)) {

            // Gateway OP Codes
            if ($receive->op === 1) {
                Console::log("OP 1 Gateway Heartbeat", "RECEIVE");
            } elseif ($receive->op === 2) {
                Console::log("OP 2 Identify", "RECEIVE");
            } elseif ($receive->op === 3) {
                Console::log("OP 3 Status Update", "RECEIVE");
            } elseif ($receive->op === 4) {
                Console::log("OP 4 Voice State Update", "RECEIVE");
            } elseif ($receive->op === 5) {
                Console::log("OP 5 Voice Server Ping", "RECEIVE");
            } elseif ($receive->op === 6) {
                Console::log("OP 6 Resume", "RECEIVE");
            } elseif ($receive->op === 7) {
                Console::log("OP 7 Reconnect", "RECEIVE");
            } elseif ($receive->op === 8) {
                Console::log("OP 8 Request Guild Members", "RECEIVE");
            } elseif ($receive->op === 9) {
                Console::log("OP 9 Invalid Session", "RECEIVE");
            } elseif ($receive->op === 10) {
                Console::log("OP 10 Gateway Hello", "RECEIVE");
            } elseif ($receive->op === 11) {
                Console::log("OP 11 Gateway Heartbeat ACK", "RECEIVE");
            } else {
                // それ以外の時は詳細を表示
                var_dump($receive);
                return false;
            }

            return true;
        }

        // イベント毎の分岐
        switch ($receive->t) {
            case "READY":
                Console::log("[READY]", "RECEIVE");
                break;
            case "RESUMED":
                Console::log("[RESUMED]", "RECEIVE");
                break;
            case "GUILD_CREATE":
                Console::log("[GUILD_CREATE]", "RECEIVE");

                // Get Channels
                foreach ($receive->d->channels as $channel) {
                    $this->channels[] = $channel->id;

                    // 起動メッセージ

                    if ($this->isDebugMode) {
                        $this->sendMessage($channel->id, "[SYSTEM] KASHIMA DEBUG MODE");
                    }
                    $this->sendMessage($channel->id, "[SYSTEM] KASHIMA 起動しました");
                }

                break;
            case "GUILD_UPDATE":
                Console::log("[GUILD_UPDATE]", "RECEIVE");
                break;
            case "GUILD_DELETE":
                Console::log("[GUILD_DELETE]", "RECEIVE");
                break;
            case "GUILD_BAN_ADD":
                Console::log("[GUILD_BAN_ADD]", "RECEIVE");
                break;
            case "GUILD_BAN_REMOVE":
                Console::log("[GUILD_BAN_REMOVE]", "RECEIVE");
                break;
            case "GUILD_EMOJIS_UPDATE":
                Console::log("[GUILD_EMOJIS_UPDATE]", "RECEIVE");
                break;
            case "GUILD_INTEGRATIONS_UPDATE":
                Console::log("[GUILD_INTEGRATIONS_UPDATE]", "RECEIVE");
                break;
            case "GUILD_MEMBER_ADD":
                Console::log("[GUILD_MEMBER_ADD]", "RECEIVE");
                break;
            case "GUILD_MEMBER_REMOVE":
                Console::log("[GUILD_MEMBER_REMOVE]", "RECEIVE");
                break;
            case "GUILD_MEMBER_UPDATE":
                Console::log("[GUILD_MEMBER_UPDATE]", "RECEIVE");
                break;
            case "GUILD_MEMBERS_CHUNK":
                Console::log("[GUILD_MEMBERS_CHUNK]", "RECEIVE");
                break;
            case "GUILD_ROLE_CREATE":
                Console::log("[GUILD_ROLE_CREATE]", "RECEIVE");
                break;
            case "GUILD_ROLE_UPDATE":
                Console::log("[GUILD_ROLE_UPDATE]", "RECEIVE");
                break;
            case "GUILD_ROLE_DELETE":
                Console::log("[GUILD_ROLE_DELETE]", "RECEIVE");
                break;
            case "MESSAGE_CREATE":

                $nick = $receive->d->author->username;
                $content = $receive->d->content;
                $channel_id = $receive->d->channel_id;
                $user_id = $receive->d->author->id;
                Console::log("[MESSAGE_CREATE] {$nick}: {$content}", "RECEIVE");

                // 問答無用でデレる
                $this->getCute($channel_id, $user_id, $content);

                $this->getScp($channel_id, $user_id, $content);
                $this->getScpJp($channel_id, $user_id, $content);
                $this->getSandbox($channel_id, $user_id, $content);
                $this->getDraftReserve($channel_id, $user_id, $content);
                $this->setTimer($channel_id, $user_id, $content);

                break;
            case "MESSAGE_UPDATE":
                Console::log("[MESSAGE_UPDATE]", "RECEIVE");

                if (isset($receive->d->author)) {
                    $content = $receive->d->content;
                    $channel_id = $receive->d->channel_id;
                    $user_id = $receive->d->author->id;

                    // 問答無用でデレる
                    $this->getCute($channel_id, $user_id, $content);

                    $this->getScp($channel_id, $user_id, $content);
                    $this->getScpJp($channel_id, $user_id, $content);
                    $this->getSandbox($channel_id, $user_id, $content);
                    $this->getDraftReserve($channel_id, $user_id, $content);
                    $this->setTimer($channel_id, $user_id, $content);
                }

                break;
            case "MESSAGE_DELETE":
                Console::log("[MESSAGE_DELETE]", "RECEIVE");
                break;
            case "MESSAGE_DELETE_BULK":
                Console::log("[MESSAGE_DELETE_BULK]", "RECEIVE");
                break;
            case "PRESENCE_UPDATE":
                Console::log("[PRESENCE_UPDATE]", "RECEIVE");

                $user_id = $receive->d->user->id;
                $status = $receive->d->status;
                if ($status == "dnd") {
                    $status = "[Do Not Disturb]";
                } else {
                    $status = "[" . ucfirst($status) . "]";
                }

                foreach ($this->channels as $channel_id) {
                    $this->sendMessage($channel_id, "[SYSTEM] <@{$user_id}> {$status}");
                }

                break;
            case "GAME_OBJECT":
                Console::log("[GAME_OBJECT]", "RECEIVE");
                break;
            case "TYPING_START":
                Console::log("[TYPING_START] {$receive->d->user_id}", "RECEIVE");
                break;
            case "USER_SETTINGS_UPDATE":
                Console::log("[USER_SETTINGS_UPDATE]", "RECEIVE");
                break;
            case "USER_UPDATE":
                Console::log("[USER_UPDATE]", "RECEIVE");
                break;
            case "VOICE_STATE_UPDATE":
                Console::log("[VOICE_STATE_UPDATE]", "RECEIVE");
                break;
            case "VOICE_SERVER_UPDATE":
                Console::log("[VOICE_SERVER_UPDATE]", "RECEIVE");
                break;
            default:
                var_dump($receive);
                return false;
        }
        return true;
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
     * メッセージ送信
     * @param $channel_id
     * @param $msg
     * @return bool|mixed
     */
    protected function sendMessage($channel_id, $msg)
    {
        if (!is_numeric($channel_id)) {
            Console::log("sendMessage channel_id error.", "FAILED");
            return false;
        }

        $data = array(
            'content' => $msg,
        );
        $result = $this->request("/channels/{$channel_id}/messages", true, $data);
        return $result;
    }

    /**
     * SCP記事出力
     * @param $channel_id
     * @param $user_id
     * @param $content
     * @return bool
     */
    protected function getScp($channel_id, $user_id, $content)
    {
        preg_match('/^(\.SCP-)(\d*)$/i', $content, $match);
        // 再試行
        if (empty($match)) {
            preg_match('/^(\.SCP )(\d*)$/i', $content, $match);
        }
        if (empty($match)) {
            return false;
        }

        $url = "http://scpjapan.wiki.fc2.com/wiki/SCP-{$match[2]}/";
        $html = $this->Api->curl($url);
        if (!$html) {
            $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 取得に失敗しました");
            return false;
        }

        preg_match('@(<div><span style="font-weight: bold;">)(.*?)(</span></div>)@i', $html["body"], $matches);
        unset($html); // メモリ節約
        if (!isset($matches[2])) {
            $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 記事が見つかりませんでした");
            return false;
        }

        $title = str_replace('<span style="font-style: italic;">', '', $matches[2]);
        $title = str_replace('<span style="font-weight: bold;">', '', $title);
        $title = str_replace('</span>', '', $title);
        $title = htmlspecialchars_decode($title);

        $msg = "[SCP] <@{$user_id}> {$title} {$url}";

        // 発言
        $this->sendMessage($channel_id, $msg);
        return true;
    }

    /**
     * SCP-JP記事出力
     * @param $channel_id
     * @param $user_id
     * @param $content
     * @return bool
     */
    protected function getScpJp($channel_id, $user_id, $content)
    {
        preg_match('/^(\.SCPJP-)(\d*)$/i', $content, $match);
        // 再試行
        if (empty($match)) {
            preg_match('/^(\.SCPJP )(\d*)$/i', $content, $match);
        }
        if (empty($match)) {
            return false;
        }

        $num = $match[2];
        if ($num >= 1000) {
            $url = "http://ja.scp-wiki.net/scp-series-jp-2";
        } else {
            $url = "http://ja.scp-wiki.net/scp-series-jp";
        }

        $html = $this->Api->curl($url);
        if (!$html) {
            $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 取得に失敗しました");
            return false;
        }

        preg_match("@(<li><a href=\"/scp-{$num}-jp\">SCP-{$num}-JP</a> - )(.*?)(</li>)@i", $html["body"], $matches);
        unset($html); // メモリ節約
        if (!isset($matches[2]) && $num != 242) {
            $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 記事が見つかりませんでした");
            return false;
        }

        $title = htmlspecialchars_decode($matches[2]);

        $msg = "[SCP-JP] <@{$user_id}> SCP-{$num}-JP \"{$title}\" http://ja.scp-wiki.net/scp-{$num}-jp";

        // 発言
        $this->sendMessage($channel_id, $msg);
        return true;
    }

    /**
     * Wikidot内マルチURL出力
     * @param $channel_id
     * @param $user_id
     * @param $content
     * @return bool
     */
    protected function getWiki($channel_id, $user_id, $content)
    {
        preg_match('/^(\.wiki )(.*)$/i', $content, $match);

        if (empty($match)) {
            return false;
        }

        $page = $match[2];
        $url = "http://ja.scp-wiki.net/{$page}";
        $html = $this->Api->curl($url);
        if (!$html) {
            $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 取得に失敗しました");
            return false;
        }

        preg_match('/(<title>)(.*?)(<\/title>)/i', $html["body"], $matches);
        unset($html); // メモリ節約

        $title = str_replace(' - SCP財団', '', $matches[2]);
        $title = htmlspecialchars_decode($title);

        $msg = "[WIKI] <@{$user_id}> http://ja.scp-wiki.net/{$page} \"{$title}\"";

        // 発言
        $this->sendMessage($channel_id, $msg);
        return true;
    }

    /**
     * @param $channel_id
     * @param $user_id
     * @param $content
     * @return bool
     */
    protected function getDraftReserve($channel_id, $user_id, $content)
    {
        preg_match('/^(\.draft )(.*)$/i', $content, $match);

        if (empty($match)) {
            return false;
        }

        if (!isset($match[2])) {
            $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 日付を指定してください");
            return false;
        } else {
            $date = $match[2];
            if ($date !== date("Y-m-d", strtotime($date))) {
                $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 日付は yyyy-mm-dd 形式で指定してください");
                return false;
            }
        }

        $drafts = $this->Irc->getDraftReserve($date);

        if (empty($drafts)) {
            $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 該当データが存在しません");
            return false;
        }

        $draftsStr = "";
        foreach ($drafts as $draft) {
            $draftsStr .= "{$draft[1]} - {$draft[2]} - " . trim($draft[3]) . "\n";
        }

        $msg = "[DraftReserve] <@{$user_id}> {$draftsStr}";

        // 発言
        $this->sendMessage($channel_id, $msg);
        return true;
    }

    /**
     * サンドボックス出力
     * @param $channel_id
     * @param $user_id
     * @param $content
     * @return bool
     */
    protected function getSandbox($channel_id, $user_id, $content)
    {
        preg_match('/^(\.sb )(.*)$/i', $content, $match);
        // 再試行
        if (empty($match)) {
            preg_match('/^(\.sandbox )(.*)$/i', $content, $match);
        }
        if (empty($match)) {
            return false;
        }

        $user = $match[2];
        $user = str_replace("_", "-", $user);

        $url = "http://scp-jp-sandbox2.wikidot.com/{$user}/";
        $html = $this->Api->curl($url);
        if (!$html) {
            $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 取得に失敗しました");
            return false;
        }

        preg_match('@(<div id="page-title">)([\s|\S]*?)(</div>)@i', $html["body"], $matches);
        var_dump($matches);
        unset($html); // メモリ節約

        $title = $matches[2];
        $title = preg_replace('/^[ 　]+/u', '', $title);
        $title = preg_replace('/[ 　]+$/u', '', $title);
        $title = trim($title);

        $msg = "[SANDBOX] <@{$user_id}> {$url} - \"{$title}\"";

        // 発言
        $this->sendMessage($channel_id, $msg);
        return true;
    }

    /**
     * パスコード生成
     * @param int $length
     * @return mixed
     */
    private function random($length = 8)
    {
        return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
    }

    /**
     * かしまちゃんかわいい
     * @param $channel_id
     * @param $content
     * @return bool
     */
    protected function getCute($channel_id, $user_id, $content)
    {
        if (strpos($content, "かしまちゃんかわいい") === false || $user_id == Config::load("discord.id")) {
            return false;
        }

        $a = array("あ", "う", "い", "び", "お", "ひゃ", "ほ", "わ");
        $b = array("う", "い", "お", "ひゃ", "ほ", "わ", "ん", "む", "ぬ");
        $c = array("ゃ", "ゅ", "ょ", "ぁ", "ぃ", "ぅ", "ぇ", "ぉ", "い", "び", "お", "ひゃ", "ほ", "わ", "ん", "む", "ぬ", "");

        $msg = $a[array_rand($a)];
        $msg .= $b[array_rand($b)];
        $msg .= $c[array_rand($c)];

        // ツン
        if (rand(0, 100) == 1) {
            $msg = "は？";
        }

        // 発言
        $this->sendMessage($channel_id, $msg);
        return true;
    }

    /**
     * タイマー機能
     * @param $channel_id
     * @param $user_id
     * @param $content
     * @return bool
     */
    protected function setTimer($channel_id, $user_id, $content)
    {
        preg_match('/^(\.timer )(\d*)$/i', $content, $match);
        if (empty($match)) {
            return false;
        }
        if (!isset($match[2])) {
            $this->sendMessage($channel_id, "[ERROR] <@{$user_id}> 時間を指定してください(単位:分)");
            return false;
        }
        $minutes = $match[2];

        $this->timers[] = array(
            "user_id" => $user_id,
            "channel_id" => $channel_id,
            "time" => time() + ($minutes * 60),
        );

        // 発言
        $this->sendMessage($channel_id, "[TIMER] <@{$user_id}> ".date("m/d H:i:s")." でタイマーセットしました");
        return true;
    }

    /**
     * タイマー機能__経過確認
     * @return bool
     */
    protected function checkTimer()
    {
        $now = time();

        foreach ($this->timers as $key=>$timer){

            if ($now > $timer["time"]) {
                $this->sendMessage($timer["channel_id"], "[TIMER] <@{$timer["user_id"]}> ".date("m/d H:i:s")." を過ぎました");
                unset($this->timers[$key]);
            }

        };
        $this->timers = array_values($this->timers);

        return true;
    }
}