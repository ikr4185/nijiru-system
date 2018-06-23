<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\WebAppsLogic;
use Logics\AuthLogic;
use Logics\PointLogic;

/**
 * Class ApiController
 * @package Controllers
 */
class ApiController extends WebController
{
    /**
     * @var WebAppsLogic
     */
    protected $logic;
    
    /**
     * @var AuthLogic
     */
    protected $auth;

    /**
     * @var PointLogic
     */
    protected $PointLogic;

    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new WebAppsLogic();
        $this->auth = new AuthLogic();
        $this->PointLogic = new PointLogic();
    }
    
    /**
     * 財団絵チャ: 保存
     */
    public function saveWhiteBoardAction()
    {
        $token = $this->input->getRequest("token");
        $data = $this->input->getRequest("data");
        $pass = $this->input->getRequest("pass");
        
        if (empty($token) || empty($data) || empty($pass)) {
            echo json_encode("Error. please fill pass.");
            exit;
        }
        
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $token)) {
            echo "Error. please check URL. you can use a~z, 0~9, and - (hyphen).";
            exit;
        }
        
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $pass)) {
            echo "Error. please check password. you can use a~z, 0~9, and - (hyphen).";
            exit;
        }
        
        if (!preg_match('/^[a-zA-Z0-9\-]{1,100}+$/', $pass)) {
            echo "Error. password must be 100 characters or less";
            exit;
        }
        
        // DBに保存
        $result = $this->logic->saveFwbImage($token, $data, $pass);
        
        if ($result) {
            echo json_encode("ok");
        } else {
            echo json_encode("error");
        }
    }
    
    /**
     * 財団絵チャ : 読み込み
     * @param $token
     */
    public function loadWhiteBoardAction($token)
    {
        $record = $this->logic->loadFwbImage($token);
        echo json_encode($record);
    }

    /**
     * トークン生成
     */
    public function gettokenAction()
    {
        echo json_encode($this->auth->createHash());
    }

    /**
     * ニジポふやすAPI
     */
    public function addNjpAction()
    {
        $hash = $this->input->getRequest("hash");
        $njp = $this->input->getRequest("njp");
        $userNum = $this->input->getRequest("un");

        if (empty($hash) || empty($njp) || empty($userNum)) {
            echo json_encode(array("request error."));
            exit;
        }

        if ($this->auth->checkHash($hash)) {

            $id = $this->UsersLogic->getIdByNumber($userNum);

            if (!empty($id)) {
                $this->PointLogic->add_point($id, $njp);
                echo json_encode(array("200 ok.", $id, $njp));
                exit;
            }

            echo json_encode(array("user not found.", $id, $njp));
            exit;
        }
        
        echo json_encode(array("token error."));
    }

    public function monaAction()
    {
        echo $this->logic->getMonaFromZaif();
    }

    public function testAction()
    {
        $hash = $this->input->getRequest("hash");
        if ($this->auth->checkHash($hash)) {
            echo json_encode("ok");
        } else {
            echo json_encode("failed");
        }
    }
    
}