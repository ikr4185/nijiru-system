<?php
namespace Cli;

use Cli\Commons\Console;
use Logics\DiscordLogic;


use Logics\Discord\DiscordClient;

class CliDiscordBot
{
    /**
     * @var DiscordLogic
     */
    protected $DiscordLogic = null;

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
        $this->DiscordLogic = new DiscordLogic();
        $this->DiscordClient = new DiscordClient();
    }

    public function testAction()
    {
        // debug ////////////////////////////////////////
        // #kashima-test create msg
        $data = array(
            'content' => 'hello',
        );

        // Gateway connect
        $result = $this->DiscordLogic->connectGateway();
    
        $this->DiscordClient->connectWebSocket($result->url);

//        // url整形
//        $url = parse_url($result->url);
//        $port = 443;
//        $path = "/?v=5&encoding=json";
//        $host = $url["host"];
//
//        $result = $this->DiscordLogic->runBot($host, $port, $path);

        var_dump($result);
    }

    public function run()
    {
        echo "say ho!";
    }
    
}