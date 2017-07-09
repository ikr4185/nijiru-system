<?php
namespace Controllers;

use Controllers\Commons\AbstractController;
use Logics\WebAppsLogic;
use Logics\AuthLogic;
use Inputs\BasicInput;

/**
 * Class ApiController
 * @package Controllers
 */
class ApiController extends AbstractController
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
     * @var BasicInput
     */
    protected $input;

    public function __construct()
    {
        parent::__construct();
    }


    protected function getLogic()
    {
        $this->logic = new WebAppsLogic();
        $this->auth = new AuthLogic();
    }

    protected function getInput()
    {
        $this->input = new BasicInput();
    }

    public function indexAction()
    {
        echo json_encode("error");
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