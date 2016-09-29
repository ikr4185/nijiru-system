<?php
namespace Cores\Abstracts;


/**
 * Class SingletonAbstract
 * 抽象化したシングルトンパターン
 * @package Cores
 */
abstract class SingletonAbstract
{
	private static $instance = array();
	
	protected function __construct() {
	}
	
	public static function getInstance() {
		
		$class = get_called_class();
		if (!isset(self::$instance[$class])) self::$instance[$class] = new $class;
		
		return self::$instance[$class];
	}
	
	public final function __clone()
	{
		throw new \Exception('Clone is not allowed against' . get_class($this));
	}
}