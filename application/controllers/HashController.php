<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Cores\Config\Config;


/**
 * Class HashController
 * @package Controllers
 */
class HashController extends WebController
{
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