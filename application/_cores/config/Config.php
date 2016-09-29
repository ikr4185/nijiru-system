<?php
namespace Cores\Config;

/**
 * Class Config
 * 
 * 設定ファイル読み込み
 * Config::load("path.app");
 * 
 * @package Cores\Config
 */
class Config {

	public static function load($arg) {

		$arg = explode(".",$arg);
		
		$path = "/home/njr-sys/public_html/application/_cores/config/config.ini";
		$ini = parse_ini_file($path,true);

		return $ini[$arg[0]][$arg[1]];

	}

}