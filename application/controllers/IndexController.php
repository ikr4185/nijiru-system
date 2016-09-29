<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\IndexLogic;
use Inputs\BasicInput;

class IndexController extends AbstractController {
	
	/**
	 * @var IndexLogic
	 */
	protected $logic;	
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->logic = new IndexLogic();
	}
	
	protected function getInput() {
		$this->input = new BasicInput();
	}
	
	
	public function indexAction() {

		$id = $this->logic->getIdFromName('ikr_4185');

		$resultArray = array(
			"hello" =>  "hello ",
			"world" =>  $id,
		);
		$this->getView( "index", "", $resultArray );
	}

}