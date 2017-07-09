<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use \Cores\Config\Config;

/**
 * Class AuthLogic
 * @package Logics
 */
class AuthLogic extends AbstractLogic
{
    protected $njrHash = "";
    protected $njrSalt = "";

    public function getModel()
    {
        $this->njrHash = Config::load("njrApi.hash");
        $this->njrSalt = Config::load("njrApi.salt");
    }

    public function createHash()
    {
        $timeHash = floor(time() / 60);
        $hash = crypt($timeHash . $this->njrHash, $this->njrSalt);
        return $hash;
    }

    public function checkHash($argHash)
    {
        $systemHash = $this->createHash();
        if ($argHash === $systemHash) {
            return true;
        }
        return false;
    }
}