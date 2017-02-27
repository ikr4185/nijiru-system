<?php
namespace Logics\Commons;

use Cores\Config\Config;

/**
 * Class Api
 * @package Logics\Commons
 */
class Api
{
    /**
     * XML-RPC Wrapper
     * @param $option
     * @param $arg
     * @return bool | array
     */
    public function xml_rpc($option, $arg)
    {
        $request = xmlrpc_encode_request($option, $arg);
        $context = stream_context_create(array(
            'http' => array(
                'method' => "POST",
                'header' => "Content-Type: text/xml",
                'content' => $request,
            ),
        ));
        $file = file_get_contents('https://njr-sys:' . Config::load("api.key") . '@www.wikidot.com/xml-rpc-api.php', false, $context);
        $response = xmlrpc_decode($file);

        if ($response && xmlrpc_is_fault($response)) {
            trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
            die;
        } else {
            return $response;
        }
    }

    /**
     * APIを叩くcurl
     * @param $url
     * @param array $header
     * @param bool $isPost
     * @param array $data
     * @param string $ua
     * @param bool $isJsonData
     * @return array
     */
    public function curl($url, $header = array(), $ua = "njr-sys", $isPost = false, $data = array(), $isJsonData = true)
    {
        // エンドポイント
        $ch = curl_init($url);

        // メソッド
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($isJsonData) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // jsonデータを送信
            }else{
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // HTTP Queryデータを送信
            }

//            curl_setopt($ch, CURLOPT_HEADER, true);
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        // ヘッダ
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // UA
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);

        // その他
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
//        curl_setopt($ch, CURLOPT_SSLVERSION, '6');

        $body = curl_exec($ch);

        $errNo = curl_errno($ch);
        $error = curl_error($ch);

        $return = array(
            "body" => $body,
            "errNo" => $errNo,
            "error" => $error,
        );

        curl_close($ch);
        return $return;
    }

}