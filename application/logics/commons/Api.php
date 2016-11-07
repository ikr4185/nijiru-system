<?php
namespace Logics\Commons;
use Cores\Config\Config;

/**
 * Class Api
 * @package Logics\Commons
 */
class Api {


	/**
	 * XML-RPC Wrapper
	 * @param $option
	 * @param $arg
	 * @return bool | array
	 */
	public function xml_rpc( $option, $arg ) {

		$request = xmlrpc_encode_request($option, $arg);
		$context = stream_context_create(array('http' => array(
			'method' => "POST",
			'header' => "Content-Type: text/xml",
			'content' => $request
		)));
		$file = file_get_contents( 'https://njr-sys:'.Config::load("api.key").'@www.wikidot.com/xml-rpc-api.php', false, $context );
		$response = xmlrpc_decode($file);

		if ($response && xmlrpc_is_fault($response)) {
			trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
			die;
		} else {
			return $response;
		}
	}

    /**
     * Api::curl
     * @param $url
     * @param array $header
     * @param null $postData
     * @return mixed
     */
    public static function curl($url, $header=array(), $postData = null)
    {
        if (!empty( $postData )) {
            return self::execCurl($url, $header, null, $postData);
        }
        return self::execCurl($url, $header);
    }

    /**
     * @param $url
     * @param null $byte
     * @param array $header
     * @param null $postData
     * @return mixed
     */
    protected static function execCurl($url, $header=array(), $byte = null, $postData = null)
    {
        //htmlソースの取得
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

//        curl_setopt($ch, CURLOPT_USERAGENT, "ikuraAPi");
//        curl_setopt($ch, CURLOPT_REFERER, "local");

        // ヘッダ情報　Token等
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // POST
        if (!empty( $postData )) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }

        // 取得Byte数制限
        if (isset( $byte )) {
            curl_setopt($ch, CURLOPT_RANGE, $byte);
        }

        // サーバのHHPS証明書を信頼
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // 実行
        $content = curl_exec($ch);

        $errNum = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        // エラー処理
        if (CURLE_OK !== $errNum) {
            die("{$error}__{$errNum}");
        }

        return $content;
    }

}