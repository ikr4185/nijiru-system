<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Models\UsersModel;

/**
 * Class IndexLogic
 * @package Logics
 */
class IndexLogic extends AbstractLogic {
	
	/**
	 * @var UsersModel
	 */
	protected $model;
	
	/**
	 * @var string
	 */
	protected $msg = "";
	
	protected function getModel() {
		$this->model =  UsersModel::getInstance();
	}
	
	/**
	 * ユーザー名からIDを取得
	 * @param $name
	 * @return mixed|string
	 */
	public function getIdFromName($name){
		return $this->model->getIdFromName($name);
	}
	
	
}