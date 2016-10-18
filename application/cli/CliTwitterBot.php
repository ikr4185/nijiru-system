<?php
namespace Cli;
use Abraham\TwitterOAuth\TwitterOAuth;
use Cli\Commons\Console;
use Cores\Config\Config;


/**
 * サイトメンバー情報の取得
 */
class CliTwitterBot {

//	/**
//	 * @var ScpreaderLogic
//	 */
//	protected $ScpreaderLogic;

	public function __construct(  ) {
		$this->getLogic();
	}

	protected function getLogic() {
//		$this->ScpreaderLogic = new ScpreaderLogic();
	}

	public function indexAction(){

		Console::log("Start.");

		// Consumer key
		$consumer_key = Config::load("twitter.consumer_key");
		// Consumer secret
		$consumer_secret = Config::load("twitter.consumer_secret");
		// Access token
		$access_token = Config::load("twitter.access_token");
		// Access token secret
		$access_token_secret = Config::load("twitter.access_token_secret");

		$message = "はじめてのツイート";
		
		$connection = new TwitterOAuth($consumer_key,$consumer_secret, $access_token, $access_token_secret);
		var_dump($connection);
		
		$content = $connection->get("account/verify_credentials");
		var_dump($content);

//		$statuses = $connection->get("search/tweets", array("q" => "NijiruSystem", "count" => 10));
//		var_dump($statuses);

//		$statuses = $connection->get("statuses/home_timeline", array("count" => 25, "exclude_replies" => true));
//		var_dump($statuses);

		// つぶやく
//		$statues = $connection->post("statuses/update", array("status" => $message));
//		var_dump($statues);

		Console::log("Done.");

	}
	
}

