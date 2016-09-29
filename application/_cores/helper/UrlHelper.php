<?php
namespace Cores\Helper;

/**
 * Class UrlHelper
 * AbstractController,LoginController等で使用
 */
class UrlHelper
{

	/**
	 * URL末尾のコントローラー・メソッド指定部分を返す
	 * @return string
	 */
	public static function getUrl()
	{
		$rawUrl = $_SERVER["REQUEST_URI"];
		return $rawUrl;
	}

	/**
	 * Request_URIを基に、GETパラメータを生成する
	 */
	public static function convertGetParam(){
		$rawUrl = self::getUrl();
		$url = str_replace("/",".",$rawUrl);
		return "?to=" . $url;
	}

	/**
	 * convertGetParamで変換したパラメータを、URL用に戻す
	 * @param $param	string	ここには$_GET["to"]を入れる
	 * @return mixed
	 */
	public static function revertGetParam($param){
		$url = str_replace(".","/",$param);
		return $url;
	}

}