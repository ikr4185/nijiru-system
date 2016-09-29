<?php
namespace Logics\Commons;
use Cores\Config\Config;

/**
 * Class Api
 * @package Logics\Commons
 */
class Api {

	/**
	 * XML-RPC Wrapper
	 * @param $option
	 * @param $arg
	 * @return bool | array
	 */
	public function xml_rpc( $option, $arg ) {

		$request = xmlrpc_encode_request($option, $arg);
		$context = stream_context_create(array('http' => array(
			'method' => "POST",
			'header' => "Content-Type: text/xml",
			'content' => $request
		)));
		$file = file_get_contents( 'https://njr-sys:'.Config::load("api.key").'@www.wikidot.com/xml-rpc-api.php', false, $context );
		$response = xmlrpc_decode($file);

		if ($response && xmlrpc_is_fault($response)) {
			trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
			die;
		} else {
			return $response;
		}
	}

}