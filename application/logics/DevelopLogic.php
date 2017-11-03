<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use Logics\Commons\WikidotApi;
use Models\NjrAssetModel;

/**
 * Class DevelopLogic
 * @package Logics
 */
class DevelopLogic extends AbstractLogic
{

    /**
     * @var NjrAssetModel
     */
    protected $model = null;
    
    protected function getModel()
    {
        $this->model = NjrAssetModel::getInstance();
    }
    
    public function validateScpSearch($inputNumber)
    {
        if (empty($inputNumber)) {
            $this->setError("Empty");
            return false;
        }
        return true;
    }
    
    public function getApi()
    {
        $api = new WikidotApi();
        return $api->pagesGetOne("sugoi-chirimenjako-pain", "test-2016-09-02-api");
    }
    
    public function test()
    {
        $api = new WikidotApi();
        return $api->pagesGetMeta("sugoi-chirimenjako-pain", array("moromoro"));
//		return $api->pagesGetOne( "scp-jp", "scp-549-jp" );
    }
    
    public function njrAssetTest($users_number)
    {
        $add = 200000000000000;

        echo '<pre>';

        $test = $this->model->getAssets($users_number);
        var_dump($test);

        echo '</pre>';
        echo "<hr>";
        echo "<hr>";
//        echo '<pre>';
//
//        $this->model->addPoint($add, $users_number);
//        $test = $this->model->getAssets($users_number);
//        var_dump($test);
//
//        echo '</pre>';
//        echo "<hr>";
//        echo '<pre>';
//
//        $this->model->addPoint($add, $users_number);
//        $test = $this->model->getAssets($users_number);
//        var_dump($test);
//
//        echo '</pre>';
//        echo "<hr>";
        echo '<pre>';

        $result = $this->model->delPoint($add, $users_number);
        $test = $this->model->getAssets($users_number);
        var_dump($result, $test);

        echo '</pre>';
        echo "<hr>";
        echo '<pre>';

        $result = $this->model->delPoint($add, $users_number);
        $test = $this->model->getAssets($users_number);
        var_dump($result, $test);

        echo '</pre>';
//        echo "<hr>";
//        echo '<pre>';
//
//        $this->model->addPoint($add, $users_number);
//        $test = $this->model->getAssets($users_number);
//        var_dump($test);
//
//        echo '</pre>';
//        echo "<hr>";
//        echo '<pre>';
//
//        $this->model->addPoint($add, $users_number);
//        $test = $this->model->getAssets($users_number);
//        var_dump($test);
//
//        echo '</pre>';

        exit;
    }
    
} 