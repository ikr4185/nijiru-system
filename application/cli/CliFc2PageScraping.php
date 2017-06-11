<?php
namespace Cli;

use Cli\Commons\CliAbstract;
use Logics\CliFc2PageScrapingLogic;
use Cli\Commons\Console;

/**
 * Class CliFc2PageScraping
 *
 * php /home/njr-sys/public_html/application/cli/commons/cli_load.php CliFc2PageScraping
 *
 *
 * @package Cli
 */
class CliFc2PageScraping extends CliAbstract
{
    /**
     * @var CliFc2PageScrapingLogic
     */
    protected $logic = null;
    
    protected function getLogic()
    {
        $this->logic = new CliFc2PageScrapingLogic;
    }
    
    public function indexAction()
    {
        Console::log("Start.");
        
        Console::log("loading page_list.dat");
        $pages = $this->logic->loadPageList()->getPageList();
                
        foreach ($pages as $page) {

            Console::log("----------------------------------------");
            Console::log("Scrape - $page[0]");
            $content = $this->logic->scraping($page[1])->cutOff()->getSource();

            Console::log("parsing page link ...");
            $this->logic->parseLinks($content);

            Console::log("check original URL ...");
            $this->logic->checkOriginalUrl()->checkJpTransfer();

            Console::log("Saving ...");
            $this->logic->saving($page[0]);

            Console::log("Sleep.");
            sleep(3);
        }
        
        Console::log("Done.");
    }
    
}