<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\WebAppsLogic;

/**
 * Class WebAppsController
 * ニジルシステムWEBアプリケーション
 * @package Controllers
 */
class WebAppsController extends WebController
{
    
    /**
     * @var WebAppsLogic
     */
    protected $logic;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new WebAppsLogic();
    }
    
    public function indexAction()
    {
        // 国へ帰るんだな
        $this->redirect("index");
    }
    
    /**
     * SCP-Search
     */
    public function scpSearchAction()
    {
        // ポストされたらリダイレクト
        if ($this->input->isPost()) {
            
            $inputNumber = $this->input->getRequest("scp_search");
            
            if ($this->logic->validateScpSearch($inputNumber)) {
                $url = "http://ja.scp-wiki.net/scp-" . $inputNumber;
                $this->redirectTo($url);
            }
        }
        
        $result = array(
            "msg" => $this->logic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/webapps/scp_search.js",
        );
        $this->getViewWebApps("scp_search", "WebApps", $result, $jsPathArray);
    }
    
    /**
     * 財団絵チャ
     * @param $token
     */
    public function foundation_wbAction($token)
    {
        // id バリデーション
        $this->logic->validateFwbToken($token);
        
        $result = array(
            "isWhiteBoard" => true,
            "msg" => $this->logic->getMsg(),
            "token" => htmlspecialchars($token),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/webapps/foundation_wb.js",
        );
        $this->getViewWebApps("foundation_wb", "WebApps", $result, $jsPathArray);
    }
    
    public function gohwAction($chapter)
    {
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/gohw/Chapter_{$chapter}.js",
        );
        $this->getViewWebApps("gohw", "WebApps", null, $jsPathArray);
    }
    
    public function documentAction($page)
    {
        if ($page !== "001") {
            $this->redirectTo("http://ja.scp-wiki.net");
            exit;
        }
        $this->getViewDev("document_" . $page, "WebApps", null);
    }
    
    public function fc2Action()
    {
        $logs = file_get_contents("/home/njr-sys/public_html/logs/fc2wiki/updates.log");
        $result = array("logs" => $logs);
        $this->getViewDev("fc2", "WebApps", $result);
    }
    
    /**
     * IkrScpEditor
     */
    public function ikrScpEditorAction() {
        
        $result = array(
            "msg"   => null,
        );
        $jsPathArray = array(
            "https://cdnjs.cloudflare.com/ajax/libs/riot/3.6.1/riot+compiler.min.js",
            "http://njr-sys.net/application/views/assets/js/webapps/scp_editor.js",
        );
        $this->getViewWebApps( "ikr_scp_editor", "WebApps", $result, $jsPathArray, true );
        
    }
    
    public function ipAction()
    {
        header('Content-type: image/jpeg');

        $referer = $_SERVER["HTTP_REFERER"];

        // 面倒なのでお断りするやつ
        $hosts = array(
            "http://njr-sys.net",
            "http://sugoi-chirimenjako-pain.wikidot.com",
            "http://ja.scp-wiki.net",
        );
        if (strpos($referer, $hosts[0]) === false && strpos($_SERVER["HTTP_REFERER"], $hosts[1]) === false && strpos($_SERVER["HTTP_REFERER"], $hosts[2]) === false) {
            echo base64_decode('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');
            exit;
        }

        header('Content-type: image/jpeg');
        echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

        // フォーラムじゃなければ特に取らない
        if (strpos($referer, "/forum/") === false) {
            exit;
        }
        // /scp- はページディスカッションだから要らない
        if (preg_match('/\/forum\/(.*?)\/scp-\d+?/',$referer)) {
            exit;
        }

        // UA判定
        // @see http://web-pixy.com/php-device-browser/
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $platform = "";
        $browser_name = "";

        //Browser
        if (preg_match('/Edge/i', $ua)) {
            $browser_name = 'Edge';
        } elseif (preg_match('/(MSIE|Trident)/i', $ua)) {
            $browser_name = 'IE';
        } elseif (preg_match('/Presto|OPR|OPiOS/i', $ua)) {
            $browser_name = 'Opera';
        } elseif (preg_match('/Firefox/i', $ua)) {
            $browser_name = 'Firefox';
        } elseif (preg_match('/Chrome|CriOS/i', $ua)) {
            $browser_name = 'Chrome';
        } elseif (preg_match('/Safari/i', $ua)) {
            $browser_name = 'Safari';
        }

        //Platform
        if (preg_match('/ipod/i', $ua)) {
            $platform = 'iPod';
        } elseif (preg_match('/iphone/i', $ua)) {
            $platform = 'iPhone';
        } elseif (preg_match('/ipad/i', $ua)) {
            $platform = 'iPad';
        } elseif (preg_match('/android/i', $ua)) {
            $platform = 'Android';
        } elseif (preg_match('/windows phone/i', $ua)) {
            $platform = 'Windows Phone';
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os/i', $ua)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows/i', $ua)) {
            $platform = 'Windows';
        }

        $data = array(
            date("H:i:s"),
            $_SERVER["REMOTE_ADDR"],
            str_replace("http://ja.scp-wiki.net/forum", "", $referer),
            $_SERVER['QUERY_STRING'],
            $platform,
            $browser_name,
        );
        $dataStr = implode("\t", $data). PHP_EOL;
        $fileName = date("Y-m-d") . ".log";
        touch($fileName);
        file_put_contents("/home/njr-sys/public_html/logs/webapps/ip/{$fileName}", $dataStr, FILE_APPEND);
    }
}