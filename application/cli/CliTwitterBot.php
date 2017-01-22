<?php
namespace Cli;

use Abraham\TwitterOAuth\TwitterOAuth;
use Cli\Commons\Console;
use Cores\Config\Config;


/**
 * 枯れ果てた大地(PHP5.3)からTwitter投稿するテスト
 * @see http://qiita.com/mpyw/items/b59d3ce03f08be126000
 * @see https://www.softel.co.jp/blogs/tech/archives/5181
 */
class CliTwitterBot
{

//	/**
//	 * @var ScpreaderLogic
//	 */
//	protected $ScpreaderLogic;

    public function __construct()
    {
        $this->getLogic();
    }

    protected function getLogic()
    {
//		$this->ScpreaderLogic = new ScpreaderLogic();
    }

    public function indexAction()
    {
//        $url = 'https://api.twitter.com/1.1/account/verify_credentials.json';
//        $url = 'https://api.twitter.com/1.1/statuses/update.json';
        $url = 'https://api.twitter.com/1.1/search/tweets.json';

        // oauth_signature生成まで使われる追加パラメータ
        $params = array(
//            'status' => 'はじめてのNijiruSystemのツイート',
//            'in_reply_to_status_id' => 'リプライ先ステータスID',
            'q' => '育良',
            'lang' => 'ja',
        );

        $result = $this->get($url,$params);

        $array =array();
        foreach ($result->statuses as $tweet) {
            $array[] = array(
                $tweet->text,
                $tweet->user->name,
            );
        }

        var_dump($array);
    }

    protected function get($url,$additional_params)
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url . '?' . http_build_query($additional_params, '', '&'),
            CURLOPT_HTTPHEADER => array($this->auth($url,"GET", $additional_params)),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'gzip',
        ));

        return json_decode(curl_exec($ch));
    }

    protected function post($url,$additional_params)
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($additional_params, '', '&'),
            CURLOPT_HTTPHEADER => array($this->auth($url, "POST", $additional_params)),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'gzip',
        ));

        return json_decode(($ch));
    }

    protected function auth($url, $method = "GET", $additional_params = array())
    {
        // 最後まで使われる基本パラメータ
        $oauth_params = array(
            'oauth_consumer_key' => Config::load("twitter.consumer_key"),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0a',
            'oauth_nonce' => bin2hex(openssl_random_pseudo_bytes(16)),
            'oauth_token' => Config::load("twitter.access_token"),
        );

        // ベース
        $base = $oauth_params + $additional_params;
        uksort($base, 'strnatcmp');

        // ベースをRFC3986に従ってクエリストリングにする
//        $base = str_replace(array('+', '%7E'), array('%20', '~'), http_build_query($base));
        $base = str_replace('+', '%20', http_build_query($base));

        // リクエストメソッド、URL、ベースからなる配列を作る
        $base = array($method, $url, $base);

        // RFC3986に従ったURLエンコードを適用
        $base = array_map('rawurlencode', $base);

        // 「&」で結合する
        $base = implode('&', $base);

        // キー
        $key = array(Config::load("twitter.consumer_secret"), Config::load("twitter.access_token_secret"));

        // RFC3986に従ったURLエンコードを適用
        $key = array_map('rawurlencode', $key);

        // 「&」で結合する
        $key = implode('&', $key);

        // oauth_signature を生成して基本パラメータに追加する
        $oauth_params['oauth_signature'] = base64_encode(hash_hmac('sha1', $base, $key, true));

        $items = array();
        foreach ($oauth_params as $name => $value) {
            $items[] = sprintf('%s="%s"', urlencode($name), urlencode($value));
        }

        return 'Authorization: OAuth ' . implode(', ', $items);
    }

    public function indexOldAction()
    {
        Console::log("Start.");

        // Consumer key
        $consumer_key = Config::load("twitter.consumer_key");
        // Consumer secret
        $consumer_secret = Config::load("twitter.consumer_secret");
        // Access token
        $access_token = Config::load("twitter.access_token");
        // Access token secret
        $access_token_secret = Config::load("twitter.access_token_secret");

        $message = "はじめてのツイート";

        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
        var_dump($connection);

        $content = $connection->get("account/verify_credentials");
        var_dump($content);

//		$statuses = $connection->get("search/tweets", array("q" => "NijiruSystem", "count" => 10));
//		var_dump($statuses);

//		$statuses = $connection->get("statuses/home_timeline", array("count" => 25, "exclude_replies" => true));
//		var_dump($statuses);

        // つぶやく
//		$statues = $connection->post("statuses/update", array("status" => $message));
//		var_dump($statues);

        Console::log("Done.");

    }

}

