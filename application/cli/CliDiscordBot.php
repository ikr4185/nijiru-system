<?php
namespace Cli;

use Cli\Commons\Console;
use Logics\Discord\DiscordClient;

class CliDiscordBot
{
    /**
     * @var DiscordClient
     */
    protected $DiscordClient = null;

    public function __construct()
    {
        $this->getLogic();
    }

    protected function getLogic()
    {
        $this->DiscordClient = new DiscordClient();
    }

    public function testAction()
    {
        // 接続の確立
        $result = $this->DiscordClient->connectGateway();
        
        // WebSocket接続, BOT起動
        $this->DiscordClient->connectWebSocket($result->url);
    }

    public function run()
    {
        echo "say ho!";
    }
    
}