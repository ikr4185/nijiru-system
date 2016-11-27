<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\WebAppsLogic;
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
     * @var BasicInput
     */
    protected $input;

    protected function getLogic() {
        $this->logic = new WebAppsLogic();
    }

    protected function getInput() {
        $this->input = new BasicInput();
    }

    public function indexAction() {
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
            echo json_encode("Empty! please fill data.");
            exit;
        }
        
        // DBに保存
        $result = $this->logic->saveFwbImage($token, $data, $pass);
    
        if ($result) {
            echo json_encode("ok");
        } else{
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

}