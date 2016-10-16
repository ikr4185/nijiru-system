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

		// 出力する結果の配列
		$exports = array();
		// 行番号
		$i = 0;

		$search = "機動部隊";
		$results = $this->ScpreaderLogic->searchScpJpNoLimit( $search );

		echo "\nprotocol\n\n";

		foreach ($results["protocol"] as $result) {

			preg_match_all("/(機動部隊)(.{1,30})/u",$result["protocol"],$matches);
			echo $result["item_number"]."\t".$result["title"]."\t";

			$exports[$i] = array(
				"protocol",
				$result["item_number"],
				$result["title"],
			);

			foreach ($matches[0] as $match) {
				echo $match."\t";
				$exports[$i][] = $match;
			}

			$i++;
			echo "\n";
		}

		echo "\ndescription\n\n";

		foreach ($results["description"] as $result) {

			preg_match_all("/(機動部隊)(.{1,30})/u",$result["description"],$matches);
			echo $result["item_number"]."\t".$result["title"]."\t";

			$exports[$i] = array(
				"description",
				$result["item_number"],
				$result["title"],
			);

			foreach ($matches[0] as $match) {
				echo $match."\t";
				$exports[$i][] = $match;
			}

			$i++;
			echo "\n";
		}

		// debug //////////////////////////////////////////////////////
		ob_start();

		foreach ($exports as $line) {

			foreach ( $line as $element ) {
				echo htmlspecialchars_decode($element).",";
			}

			echo "\n";

		}

		$ob_data =ob_get_contents();
		ob_end_clean();

		var_dump ( file_put_contents( "/home/njr-sys/public_html/application/cli/logs/cli_mtf_checker.log", $ob_data ) );
		// debug //////////////////////////////////////////////////////


		Console::log("Done.");

	}
	
}

