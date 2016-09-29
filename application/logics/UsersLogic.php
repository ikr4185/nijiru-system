<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Models\UsersModel;

/**
 * Class UsersLogic
 *
 * ユーザー管理の共通ロジック
 *
 * @package Logics
 */
class UsersLogic extends AbstractLogic {
	
	/**
	 * @var UsersModel
	 */
	protected $Users;
	
	protected function getModel() {
		$this->Users = UsersModel::getInstance();
	}
	
	/**
	 * 現在のニジルポイントを取得
	 * @param $id
	 * @return mixed|string
	 */
	public function getPoint($id) {
		return $this->Users->getPoint($id);
	}
	
	/**
	 * IDからユニークナンバーを取得
	 * @param $id
	 * @return array|string
	 */
	public function getNumberById( $id ) {
		return $this->Users->getNumberById($id);
		
	}
}