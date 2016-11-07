<?php
namespace Logics\Commons;
// use Logics\Commons\Api;
use Cores\Config\Config;

/**
 * Class ChatWorkApi
 * @package Logics\Commons
 */
class ChatWorkApi extends Api {

    // ==========================================================================================
    // Wikidot API Wrappers
    // ----------------------------------------
    // 'system.listMethods', 'system.methodHelp', 'system.methodSignature', 'system.multicall',
    // 'categories.select',
    // 'tags.select',
    // 'pages.select', 'pages.get_meta', 'pages.get_one', 'pages.save_one',
    // 'files.select', 'files.get_meta', 'files.get_one', 'files.save_one',
    // 'users.get_me',
    // 'posts.select', 'posts.get'
    // ==========================================================================================

    public function run($task){

        // リクエストURL
        if (!strpos($task,'.')) {
            $requestUrl = "{$task}";
        }else{
            $arg = explode(".",$task);
            $requestUrl = "{$arg[0]}/{$arg[1]}";
        }

        // リクエスト生成
        $endpoint = Config::load("chatwork.endpoint");
        $apiToken = Config::load("chatwork.token");
        $header = array(
            "X-ChatWorkToken: {$apiToken}"
        );

        // 実行
        $response = Api::curl("{$endpoint}/{$requestUrl}", $header);
        return json_decode($response);
    }

}