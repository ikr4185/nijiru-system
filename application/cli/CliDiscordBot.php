<?php
namespace Cli;

use Cli\Commons\Console;
use Logics\Discord\DiscordClient;

/**
 * Class CliDiscordBot
 *
 * php /home/njr-sys/public_html/application/cli/commons/cli_load.php CliDiscordBot test
 *
 * @package Cli
 */
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
        while (true) {

            try {

                // 接続の確立
                $result = $this->DiscordClient->connectGateway();

                // WebSocket接続, BOT起動
                $this->DiscordClient->connectWebSocket($result->url);

            } catch (\Exception $e) {

                Console::log("{$e->getMessage()} / {$e->getFile()} / {$e->getLine()}");

                sleep(10);
                continue;

            }
        }
    }

    public function run()
    {
        echo "say ho!";
    }
    
}