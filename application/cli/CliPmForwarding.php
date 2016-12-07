<?php
namespace Cli;

use Cli\Commons\Console;
use Logics\Commons\Mail;
use Cores\Config\Config;

/**
 * PMの転送
 */
class CliPmForwarding
{
    /**
     * @var Mail
     */
    protected $Mail;
    
    public function __construct()
    {
        $this->getLogic();
    }
    
    protected function getLogic()
    {
        $this->Mail = new Mail("/home/njr-sys/public_html/application/views/mail_templates/test.tpl");
    }
    
    protected function getPm($password)
    {
        $result = shell_exec("casperjs /home/njr-sys/public_html/application/cli/casper/GetPm.js {$password}");
        return $result;
    }
    
    public function indexAction()
    {
        Console::log("Start.");

        $result = $this->getPm(Config::load("wikidot.pass"));

        $result = preg_replace('/(<div class="btn-group">)(.*?)(<\/div>)(.*?)$/s', "$4", $result);
        $result = preg_replace('/(<div class="message-actions text-center">)(.*?)(<\/div>)(.*?)$/s', "$4", $result);
        $message1 = trim(strip_tags($result));

        if ($result !== "error") {
            // 送信する
            $this->Mail->send('ikr_4185@njr-sys.net', array(
                "user" => "育良 啓一郎",
                "now" => date("Y-m-d H:i:s"),
                "message1" => $message1
            ));
        }

//        var_dump($message1);
        
        Console::log("Done.");
    }
    
}

