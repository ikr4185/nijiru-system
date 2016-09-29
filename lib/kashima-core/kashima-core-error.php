<?php
class Kashima_Core_Error
{
	private $error_msg;
	
	public function __construct($message) {
		$this->error_msg = $message;
	}
	
	public function getMessage() {
		return $this->error_msg;
	}
}
