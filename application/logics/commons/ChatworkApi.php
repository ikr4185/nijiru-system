<?php
namespace Logics\Commons;
// use Logics\Commons\Api;
use Cores\Config\Config;

/**
 * Class ChatWorkApi
 * @package Logics\Commons
 */
class ChatWorkApi extends Api {

    public function execApi($task, $arg = null, $postData = null)
    {
        // �ꥯ������URL
        if (!strpos($task, '.')) {
            $requestUrl = "{$task}";
        } else {
            $tmp = explode(".", $task);
            $requestUrl = "{$tmp[0]}/{$tmp[1]}";
        }

        // ������������
        if (!empty( $arg )) {
            $requestUrl .= "/{$arg}";
        }

        // �ꥯ����������
        $endpoint = Config::load("api.endpoint");
        $apiToken = Config::load("api.token");
        $header = array("X-ChatWorkToken: {$apiToken}");

        // �¹�
        $response = Api::curl("{$endpoint}/{$requestUrl}", $header, $postData);

        // ����
        $response[ "head" ] = explode("\r\n", trim($response[ "head" ]));
        $response[ "body" ] = json_decode($response[ "body" ]);

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
     * @param null $roomId
     * @param null $arg
     * @param $postData
     * @return mixed
     */
    public function rooms($roomId = null, $arg = null, $postData = null)
    {
        if (empty( $roomId )) {

            if (empty( $arg )) {
                // ����åȰ����μ���
                return $this->execApi("rooms");
            }
            // ���롼�ץ���åȤο�������
            return $this->execApi("rooms", null, $postData);

        }


        if (empty( $arg )) {
            // ����åȤ�̾�����������󡢼���(my/direct/group)�����
            return $this->execApi("rooms.{$roomId}");
        }elseif ($arg == "messages") {

            if (empty($postData)) {
                // ����åȤΥ�å�����������������ѥ�᡼��̤��������������ʬ����κ�ʬ�Τߤ��֤��ޤ���(����100��ޤǼ���)
                return $this->execApi("rooms.{$roomId}", $arg);
            }

            // ����åȤ˿�������å��������ɲ�
            return $this->execApi("rooms.{$roomId}", $arg, $postData);
        }

        return false;


    }

}