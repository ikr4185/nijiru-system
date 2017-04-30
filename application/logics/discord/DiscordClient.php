<?php

namespace Logics\Discord;

//use WebSocket\Client;
use WebSocket\ConnectionException;

use Logics\Commons\Api;
use Logics\IrcLogic;
use Logics\ScpreaderLogic;

use Cores\Config\Config;
use Cli\Commons\Console;

class DiscordClient
{
    const END_POINT_BASE_URL = "https://discordapp.com/api";
    const TIMEOUT = 10;
    const IS_DEBUG_MODE = false;

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
     * @var array Channels Data
     */
    protected $channels = array();

    /**
     * HeartBeat 最終送信時刻
     * @var int
     */
    protected $lastHeartBeat = 0;

    /**
     * HeartBeat インターバル
     * @var int
     */
    protected $heartbeatInterval = 0;

    /**
     * タイマー設定
     * @var array
     */
    protected $timers = array();

    /**
     * Readyイベント時に渡される session id
     * @var string
     */
    protected $sessionId = "";

    /**
     * 最新のシーケンス番号
     * @var string
     */
    protected $seq = "";

    public function __construct()
    {
        $this->Api = new Api();
        $this->Irc = new IrcLogic();

        // ここでMySQLに接続するとタイムアウトが発生する
//        $this->Scpreader = new ScpreaderLogic();
    }

    public function __destruct()
    {
        Console::log("Disconnect.", "CONNECTION");
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
        Console::log("Start. ========================================", "CONNECTION");

        $url = $url . "?v=5&encoding=json";

        // タイムアウト
        $options = array('timeout' => self::TIMEOUT);

        try {

            // 接続開始
            Console::log("Connecting {$url}", "CONNECTION");
            $this->client = new ClientWrapper($url, $options);

            $json = $this->client->receive();
            $this->parseReceive($json);

            // HeartBeat 送信時刻を記録
            $this->heartbeatInterval = json_decode($json)->d->heartbeat_interval / 1000;
            $this->lastHeartBeat = time();

            // WebSocket接続後即時に、OP 1 Heartbeat ペイロードを送信
            $this->gatewayHeartbeat();

            $json = $this->client->receive();
            $this->parseReceive($json);

            // OP 2 Identify ペイロードを送信
            // 'heartbeat_interval' => 41250 を想定
            $this->gatewayIdentify();

            // [READY] check
            $json = $this->client->receive();
            $this->parseReceive($json);

            // 接続完了
            Console::log("Success. ========================================", "CONNECTION");

        } catch (ConnectionException $e) {
            Console::log("[FAILED] {$e->getMessage()} ========================================", "CONNECTION");
            die();
        } catch (\Exception $e) {
            Console::log("[FAILED] UNKNOWN ERROR. ========================================", "SYSTEM");
            die($e->getMessage());
        }

        $flag = true;
        while ($flag) {

            // タイマー機能__時刻チェック
            $this->checkTimer();

            // 受信開始
            $json = $this->client->receive();
            $op = $this->parseReceive($json);

            // send "OP 1 Gateway Heartbeat" -> if "OP Closed" received -> "OP 10 Gateway Hello"
            if ($op === 10) {
                // OP 6 Resume 送信
                $this->gatewayResume();
                continue;
            }

            // HeartBeat 送信
            // しきい値は Receiveタイムアウト より parseReceiveの処理分だけ長めに取る
            if (time() > $this->lastHeartBeat + $this->heartbeatInterval - (self::TIMEOUT + 5)) {

                // OP 1 Heartbeat 送信
                $this->gatewayHeartbeat();

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

        Console::log("OP 1 Heartbeat payloads.", "SEND");
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

        Console::log("OP 2 Identify payloads.", "SEND");
        $payload = json_encode($payload);
        $this->client->send($payload);
    }

    /**
     * OP 6 Resume 送信
     * @throws \WebSocket\BadOpcodeException
     */
    protected function gatewayResume()
    {
        $data = (object)array(
            'token' => Config::load("discord.token"),
            'session_id' => $this->sessionId,
            'seq' => $this->seq,
        );

        $payload = (object)array(
            'op' => 6,
            'd' => $data,
        );
        $payload = json_encode($payload);

        Console::log("OP 6 Resume payloads.", "SEND");
        $this->client->send($payload);
    }

    /**
     * Receiveペイロード毎の分岐処理
     * @param $json
     * @return mixed
     */
    protected function parseReceive($json)
    {

        $receive = json_decode($json);

        // ex) Timeout
        if ($receive === null) {
//            Console::log("[TIMEOUT] Receive Null", "RECEIVE");
            return 999;
        }

        if (self::IS_DEBUG_MODE) {
            Console::log("[DEBUG] DUMP", "SYSTEM");
            var_dump($receive);
        }

        // シーケンス番号の保持
        if (!isset($receive->s)) {
            $this->seq = $receive->s;
        }

        // 非イベント時の動作(!OP 0 Dispatch)
        if (!isset($receive->t)) {

            // Gateway OP Codes
            if ($receive->op === 1) {
                Console::log("[OPERATION] OP 1 Gateway Heartbeat", "RECEIVE");
            } elseif ($receive->op === 2) {
                Console::log("[OPERATION] OP 2 Identify", "RECEIVE");
            } elseif ($receive->op === 3) {
                Console::log("[OPERATION] OP 3 Status Update", "RECEIVE");
            } elseif ($receive->op === 4) {
                Console::log("[OPERATION] OP 4 Voice State Update", "RECEIVE");
            } elseif ($receive->op === 5) {
                Console::log("[OPERATION] OP 5 Voice Server Ping", "RECEIVE");
            } elseif ($receive->op === 6) {
                Console::log("[OPERATION] OP 6 Resume", "RECEIVE");
            } elseif ($receive->op === 7) {
                Console::log("[OPERATION] OP 7 Reconnect", "RECEIVE");
            } elseif ($receive->op === 8) {
                Console::log("[OPERATION] OP 8 Request Guild Members", "RECEIVE");
            } elseif ($receive->op === 9) {
                Console::log("[OPERATION] OP 9 Invalid Session", "RECEIVE");
            } elseif ($receive->op === 10) {
                Console::log("[OPERATION] OP 10 Gateway Hello", "RECEIVE");
            } elseif ($receive->op === 11) {
                // HeartBeat 送信時刻を記録
                $this->lastHeartBeat = time();
                Console::log("[OPERATION] OP 11 Gateway Heartbeat ACK (" . date("Y-m-d H:i:s", $this->lastHeartBeat + $this->heartbeatInterval) . ")", "RECEIVE");
            } else {
                // それ以外の時は詳細を表示
                var_dump($receive);
            }

            return $receive->op;
        }

        // イベント毎の分岐
        switch ($receive->t) {
            case "READY":
                // セッションIDの保持
                $this->sessionId = $receive->d->session_id;
                Console::log("[READY] session_id: {$this->sessionId}", "RECEIVE");

                break;
            case "RESUMED":
                Console::log("[RESUMED]", "RECEIVE");
                break;
            case "CHANNEL_CREATE":

                // チャンネル追加
                $this->channels[] = array(
                    "id" => $receive->d->id,
                    "name" => $receive->d->name,
                );

                Console::log("[CHANNEL_CREATE] {$receive->d->name}", "RECEIVE");
                break;
            case "CHANNEL_UPDATE":

                //チャンネル更新
                $oldName = "UNKNOWN";
                foreach ($this->channels as &$channel) {
                    if ($channel["id"] == $receive->d->id) {
                        $oldName = $channel["name"];
                        $channel = array(
                            "id" => $receive->d->id,
                            "name" => $receive->d->name,
                        );
                        break;
                    }
                }
                unset($channel);

                Console::log("[CHANNEL_UPDATE] {$oldName} -> {$receive->d->name}", "RECEIVE");
                break;
            case "CHANNEL_DELETE":

                // チャンネル削除
                $oldName = "UNKNOWN";
                foreach ($this->channels as $key => &$channel) {
                    if ($channel["id"] == $receive->d->id) {
                        $oldName = $channel["name"];
                        unset($this->channels[$key]);
                        $this->channels = array_values($this->channels);
                    }
                }
                unset($channel);

                Console::log("[CHANNEL_DELETE] {$oldName}", "RECEIVE");
                break;
            case "GUILD_CREATE":
                Console::log("[GUILD_CREATE]", "RECEIVE");

                // Get Channels
                foreach ($receive->d->channels as $channel) {

                    $this->channels[] = array(
                        "id" => $channel->id,
                        "name" => $channel->name,
                    );

                    // 起動メッセージ
                    if (self::IS_DEBUG_MODE) {
                        $this->sendMessage($channel->id, "[SYSTEM] KASHIMA DEBUG MODE");
                    } else {
//                        if ($channel->name == "general") {
//                            $this->sendMessage($channel->id, "[SYSTEM] KASHIMA 起動しました");
//                        }
                    }
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

                $channnel_name = "default";
                foreach ($this->channels as $channel) {
                    if ($channel["id"] == $channel_id) {
                        $channnel_name = $channel["name"];
                        break;
                    }
                }
                
                $user_id = $receive->d->author->id;
                Console::log("[MESSAGE_CREATE] {$nick}: {$content}", "RECEIVE");

                // TODO ログ保存(暫定)
                $logDir = Config::load("dir.logs") . "/discord/messages/{$channel_id}_{$channnel_name}";
                if (!file_exists($logDir)) {
                    if (mkdir($logDir, 0777)) {
                        chmod($logDir, 0777);
                    }
                }
                $data = array(
                    $receive->d->timestamp,
                    $nick,
                    $user_id,
                    $content,
                );
                file_put_contents($logDir . "/" . date("Y-m-d") . ".log", json_encode($data) . "\n", FILE_APPEND);

                // KASHIMAの発言を除外
                if ($user_id == Config::load("discord.id")) {
                    break;
                }

                $this->getCute($channel_id, $content);
                $this->getDraftReserve($channel_id, $user_id, $content);
                $this->getHelp($channel_id, $user_id, $content);
                $this->getSandbox($channel_id, $user_id, $content);
                $this->getScp($channel_id, $user_id, $content);
                $this->getScpJp($channel_id, $user_id, $content);
                $this->getWiki($channel_id, $user_id, $content);
                $this->searchScpJP($channel_id, $user_id, $content);
                $this->setTimer($channel_id, $user_id, $content);
                $this->getServerStatus($channel_id, $user_id, $content);

                break;
            case "MESSAGE_UPDATE":
                Console::log("[MESSAGE_UPDATE]", "RECEIVE");

                if (isset($receive->d->author)) {
                    $content = $receive->d->content;
                    $channel_id = $receive->d->channel_id;
                    $user_id = $receive->d->author->id;

                    // KASHIMAの発言を除外
                    if ($user_id == Config::load("discord.id")) {
                        break;
                    }

                    $this->getCute($channel_id, $content);
                    $this->getDraftReserve($channel_id, $user_id, $content);
                    $this->getHelp($channel_id, $user_id, $content);
                    $this->getSandbox($channel_id, $user_id, $content);
                    $this->getScp($channel_id, $user_id, $content);
                    $this->getScpJp($channel_id, $user_id, $content);
                    $this->getWiki($channel_id, $user_id, $content);
                    $this->setTimer($channel_id, $user_id, $content);
                    $this->searchScpJP($channel_id, $user_id, $content);
                    $this->getServerStatus($channel_id, $user_id, $content);
                }

                break;
            case "MESSAGE_DELETE":
                Console::log("[MESSAGE_DELETE]", "RECEIVE");
                break;
            case "MESSAGE_DELETE_BULK":
                Console::log("[MESSAGE_DELETE_BULK]", "RECEIVE");
                break;
            case "PRESENCE_UPDATE":

                $user_id = $receive->d->user->id;
                $status = $receive->d->status;
                if ($status == "dnd") {
                    $status = "Do Not Disturb";
                } else {
                    $status = ucfirst($status);
                }

                foreach ($this->channels as $channel) {
//                    $this->sendMessage($channel["id"], "[SYSTEM] <@{$user_id}> -> {$status}");
                    Console::log("[PRESENCE_UPDATE] {$user_id} -> {$status}", "RECEIVE");
                    break;
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
                Console::log("[{$receive->t}]", "RECEIVE");
                var_dump($receive);
        }

        return $receive->op;
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
        Console::log("post message {$channel_id} '{$msg}'", "SEND");
        if (!is_numeric($channel_id)) {
            Console::log("[FAILED] sendMessage channel_id error.", "SEND");
            return false;
        }

        $data = array(
            'content' => $msg,
        );
        $result = $this->request("/channels/{$channel_id}/messages", true, $data);
        return $result;
    }

    /**
     * ヘルプ
     * @param $channel_id
     * @param $user_id
     * @param $content
     * @return bool
     */
    protected function getHelp($channel_id, $user_id, $content)
    {
        preg_match('/^(\.help)$/i', $content, $match);
        if (empty($match)) {
            return false;
        }

        $msg = "[HELP] <@{$user_id}> 
.scp XXX / .scp-XXX \t... SCPを表示
.scpjp XXX / .scpjp-XXX \t... SCP-JPを表示
.wiki url-page \t... http://ja.scp-wiki.net/url-page を表示
.sb ikr_4185 / .sandbox ikr_4185 \t... サンドボックスを表示
.draft 2017-02-18 \t... 2/18 の批評待ちリストを表示
.timer 40 \t... 40分後にタイマー通知をセット";

        // 発言
        $this->sendMessage($channel_id, $msg);
        return true;
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
     * SCP-JPあいまい検索
     * @param $channel_id
     * @param $user_id
     * @param $content
     * @return bool
     */
    protected function searchScpJP($channel_id, $user_id, $content)
    {
        preg_match('/^(\.SEARCH-SCPJP )(.*)$/i', $content, $match);
        if (empty($match)) {
            return false;
        }

        $query = $match[2];

        // 検索実行
        $ScpReader = new ScpreaderLogic();
        $records = $ScpReader->searchScpJp($query);
        // modelを即破棄する('SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' 対策)
        unset($ScpReader);
        
        $msg = "";
        $findItems = array();
        $i = 0;
        foreach ($records as $key => $record) {

            if (empty($record)) {
                continue;
            }

            foreach ($record as $item) {

                // 既に検索済みは排除
                if (in_array($item["item_number"], $findItems)) {
                    continue;
                }

                // 検索済み配列に格納
                $findItems[] = $item["item_number"];

                $msg .= "{$item["item_number"]} - {$item["title"]} - http://ja.scp-wiki.net/scp-" . sprintf('%03d', $item["scp_num"]) . "-jp\n";
                $i++;

                // 送信できないのである程度で省略
                if ($i >= 10) {
                    $msg .= "(10件以上は省略されます)";
                    break 2;
                }

            }
        }

        if (empty($msg)) {
            $this->sendMessage($channel_id, "[SEARCH-SCPJP] <@{$user_id}> 該当記事が見つかりませんでした");
            return false;
        }

        // 発言
        $this->sendMessage($channel_id, "[SEARCH-SCPJP] <@{$user_id}> \n" . $msg);
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
     * 下書き批評予約表示
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

        $msg = "[DraftReserve] <@{$user_id}> \n{$draftsStr}";

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
    protected function getCute($channel_id, $content)
    {
        if (strpos($content, "かしまちゃんかわいい") === false) {
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

        $timer = array(
            "user_id" => $user_id,
            "channel_id" => $channel_id,
            "time" => time() + ($minutes * 60),
        );
        $this->timers[] = $timer;

        // 発言
        $this->sendMessage($channel_id, "[TIMER] <@{$user_id}> " . date("m/d H:i:s", $timer["time"]) . " でタイマーセットしました");
        return true;
    }

    /**
     * タイマー機能__経過確認
     * @return bool
     */
    protected function checkTimer()
    {
        $now = time();

        foreach ($this->timers as $key => $timer) {

            if ($now > $timer["time"]) {
                $diff = $now - $timer["time"];
                $this->sendMessage($timer["channel_id"], "[TIMER] <@{$timer["user_id"]}> " . date("m/d H:i:s", $timer["time"]) . " を {$diff} 秒過ぎました");
                unset($this->timers[$key]);
            }

        };
        $this->timers = array_values($this->timers);

        return true;
    }

    /**
     * サーバステータスチェック
     * @param $channel_id
     * @param $user_id
     * @param $content
     * @return bool
     */
    protected function getServerStatus($channel_id, $user_id, $content)
    {
        preg_match('/^(\.uptime)$/i', $content, $match);
        if (empty($match)) {
            return false;
        }

        if ($user_id != Config::load("discord.ikr_id")) {
            $this->sendMessage($channel_id, "Err. not allowed except for developers.");
            return true;
        }

        exec("uptime", $output);
        $msg = implode(",", $output);

        // 発言
        $this->sendMessage($channel_id, $msg);
        return true;
    }
}