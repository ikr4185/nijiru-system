<?php
namespace Cli;
use Logics\ScpreaderLogic;
use Cli\Commons\Console;

/**
 * サイトメンバー情報の取得
 */
class CliMtfChecker {

	/**
	 * @var ScpreaderLogic
	 */
	protected $ScpreaderLogic;

	public function __construct(  ) {
		$this->getLogic();
	}

	protected function getLogic() {
		$this->ScpreaderLogic = new ScpreaderLogic();
	}

	public function indexAction(){

		Console::log("Start.");

		$search = "機動部隊";
		$results = $this->ScpreaderLogic->searchScpJpNoLimit( $search );


		echo "\nprotocol\n\n";

		foreach ($results["protocol"] as $result) {

			preg_match_all("/(機動部隊)(.{1,30})/u",$result["protocol"],$matches);
			echo $result["item_number"]."\t".$result["title"]."\t";

			foreach ($matches[0] as $match) {
				echo $match."\t";
			}

			echo "\n";
		}

		echo "\ndescription\n\n";

		foreach ($results["description"] as $result) {

			preg_match_all("/(機動部隊)(.{1,30})/u",$result["description"],$matches);
			echo $result["item_number"]."\t".$result["title"]."\t";

			foreach ($matches[0] as $match) {
				echo $match."\t";
			}

			echo "\n";
		}

		Console::log("Done.");

	}
	
}

