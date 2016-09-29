<?php
namespace Logics;
use Logics\Commons\AbstractLogic;

/**
 * Class WebAppsLogic
 * @package Logics
 */
class WebAppsLogic extends AbstractLogic {
	
	
	protected function getModel() {
	}
	
	public function validateScpSearch( $inputNumber ) {
		
		if(empty($inputNumber)){
			$this->setError("Empty");
			return false;
		}
		return true;
	}
	
}