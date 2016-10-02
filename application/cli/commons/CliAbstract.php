<?php
namespace Cli\Commons;

/**
 * Class ACliAbstract
 * 抽象コントローラ
 */
abstract class CliAbstract {

	protected $logic;

	/**
	 * AbstractController constructor.
	 */
	public function __construct(){
		$this->getLogic();
	}
	
	/**
	 * ロジックインスタンスの生成
	 * @return mixed $LogicInstance
	 */
	abstract protected function getLogic();

	
	/**
	 * indexメソッドを強制
	 * @return mixed
	 */
	abstract public function indexAction();

	
}