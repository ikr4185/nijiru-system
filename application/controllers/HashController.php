<?php
namespace Controllers;

use Controllers\Commons\AbstractController;
use Inputs\BasicInput;
use Cores\Config\Config;


/**
 * Class HashController
 * @package Controllers
 */
class HashController extends AbstractController
{
    
    /**
     * @var null
     */
    protected $logic;
    /**
     * @var BasicInput
     */
    protected $input;
    
    protected function getLogic()
    {
        $this->logic = null;
    }
    
    protected function getInput()
    {
        $this->input = new BasicInput();
    }
    
    public function indexAction()
    {
        // TODO 未使用
        var_dump(PHP_INT_MAX);
    }
    
    /**
     * SHA256 hash
     */
    public function createAction()
    {
        $hash = "";

        if ($this->input->isPost()) {
            // POST取得
            $seed = $this->input->getRequest("seed");
            $salt = Config::load("njrApi.salt");
            $hash = hash('SHA256', $seed . $salt);
        }

        $result = array(
            "msg" => "",
            "hash" => $hash,
        );
        $this->getView("create", "Create Hash", $result);
        
    }
    
    
}