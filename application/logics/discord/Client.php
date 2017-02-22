<?php
namespace Logics\Discord;

use Logics\Discord\WebSocketClientInterface;

/**
 * Class Client
 * @package Logics\Discord
 * @see https://github.com/emersion/php-websocket-client
 */
class Client implements WebSocketClientInterface
{
    private $client;
    
    public function onMessage($data)
    {
    }
    
    public function sendData($data)
    {
        $this->client->sendData($data);
    }
    
    public function setClient(WebSocketClient $client)
    {
        $this->client = $client;
    }
}