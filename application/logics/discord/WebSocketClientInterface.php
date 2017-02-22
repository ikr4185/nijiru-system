<?php

namespace Logics\Discord;

interface WebSocketClientInterface
{
    /**
     * @param array $data
     */
    public function onMessage($data);
    
    /**
     * @param WebSocketClient $client
     */
    function setClient(WebSocketClient $client);
}