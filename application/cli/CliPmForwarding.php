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
        $this->Mail = new Mail("/home/njr-sys/public_html/application/views/mail_templates/pm_forwarding.tpl");
    }
    
    protected function getPm($password)
    {
        $result = shell_exec("casperjs /home/njr-sys/public_html/application/cli/casper/GetPm.js {$password}");
        return $result;
    }
    
    public function indexAction()
    {
        exit;

        Console::log("Start.");

        $result = $this->getPm(Config::load("wikidot.pass"));

        var_dump($result);

        if ($result) {

            Console::log("ok.");

            $result = preg_replace('/(<div class="btn-group">)(.*?)(<\/div>)(.*?)$/s', "$4", $result);
            $result = preg_replace('/(<div class="message-actions text-center">)(.*?)(<\/div>)(.*?)$/s', "$4", $result);
            $message1 = trim(strip_tags($result));

            if (!empty($message1) && $message1 !== "error") {
                // 送信する
                $this->Mail->send('ikr.4185@gmail.com', array(
                    "user" => "ikr_4185",
                    "now" => date("Y-m-d H:i:s"),
                    "message1" => $message1,
                ));
                Console::log("MailSend");
            }

        }
        
        Console::log("Done.");
    }
    
}

