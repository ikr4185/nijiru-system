<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Logics\Commons\WikidotApi;

/**
 * Class DevelopLogic
 * @package Logics
 */
class DevelopLogic extends AbstractLogic {
	
	protected function getModel() {
	}

	public function validateScpSearch( $inputNumber ) {

		if(empty($inputNumber)){
			$this->setError("Empty");
			return false;
		}
		return true;
	}

	public function getApi() {
		$api = new WikidotApi();
		return $api->pagesGetOne( "sugoi-chirimenjako-pain", "test-2016-09-02-api" );
	}

	public function test() {
		$api = new WikidotApi();
		return $api->pagesGetMeta( "sugoi-chirimenjako-pain", array("moromoro") );
//		return $api->pagesGetOne( "scp-jp", "scp-549-jp" );

	}

} 