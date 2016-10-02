<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\ScpreaderLogic;
use Inputs\BasicInput;

/**
 * Class TourController
 * みよしのに捧ぐネタ
 * @package Controllers
 */
class TourController extends AbstractController{
	
	/**
	 * @var ScpreaderLogic
	 */
	protected $logic;
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->logic = new ScpreaderLogic();
	}
	
	protected function getInput() {
		$this->input = new BasicInput();
	}

	/**
	 * 404
	 */
	public function indexAction() {
		$result = array(
		);
		$this->getView( "index", "404", $result );
	}
	
	public function __call($name, $arguments){
		$this->_2016Action();
	}
	public function _2016Action() {
		
		// 記事読み込み
		$scpArray = $this->logic->getScpArray( 609 );
		
		$result = array(
			"scpArray"  => $scpArray,
			"msg"   => $this->logic->getMsg(),
		);
		$this->getView( "2016", "Sacrifice Curse Prayer", $result );
	}
}