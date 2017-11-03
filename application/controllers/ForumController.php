<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\ForumLogic;
use Logics\DraftsLogic;

/**
 * Class ForumController
 * @package Controllers
 */
class ForumController extends WebController
{    
    /**
     * @var ForumLogic
     */
    protected $ForumLogic;
    
    /**
     * @var DraftsLogic
     */
    protected $DraftsLogic;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->ForumLogic = new ForumLogic();
        $this->DraftsLogic = new DraftsLogic();
    }
    
    public function indexAction()
    {
        // RSS取得
        $ItemsArray = $this->ForumLogic->getRss('http://ja.scp-wiki.net/feed/forum/posts.xml');
        
        $result = array(
            "items" => $ItemsArray,
            "msg" => $this->ForumLogic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/toggle.js",
        );
        $this->getView("index", "JPフォーラム最新投稿", $result, $jsPathArray);
    }
    
    public function draftsAction()
    {
        // RSS取得
        $ItemsArray = $this->ForumLogic->getRss('http://ja.scp-wiki.net/feed/forum/cp-790921.xml');
        
        $result = array(
            "items" => $ItemsArray,
            "msg" => $this->ForumLogic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/toggle.js",
        );
        $this->getView("drafts", "下書きフォーラム最新投稿", $result, $jsPathArray);
    }
    
    public function draftsThreadAction()
    {
        // html取得
        $ItemsArray = $this->DraftsLogic->getDrafts();
        
        $result = array(
            "items" => $ItemsArray,
            "msg" => $this->ForumLogic->getMsg(),
        );
        $jsPathArray = array(
            "https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js",
        );
        $this->getView("draftsThread", "下書きスレッド一覧", $result, $jsPathArray);
    }
}