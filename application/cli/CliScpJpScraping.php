<?php
namespace Cli;
use Logics\CliScpJpScrapingLogic;
use Logics\ScpreaderLogic;

/**
 * SCP-JP記事スクレイピング
 */
class CliScpJpScraping {

	/**
	 * @var CliScpJpScrapingLogic
	 */
	protected $logic;

	/**
	 * @var ScpreaderLogic
	 */
	protected $ScpreaderLogic;

	public function __construct(  ) {
		$this->getLogic();
	}

	protected function getLogic() {
		$this->ScpreaderLogic = new ScpreaderLogic();
		$this->logic = new CliScpJpScrapingLogic();
	}

	public function indexAction($start){

		// 開始～終了の設定
		if (empty($start)) {
			$start = 1;
		}
		$end = $start + 500;
		
		// SCP-JP記事の存在可否をチェック
		for ($i=$start;$i<$end;$i++) {

			// ゼロ埋め
			$scpNum = $this->logic->wrapStrPad( $i );

			// debug //////////
			echo $scpNum.":loading_now,";

			// HTMLの取得
			$html = $this->logic->getHtml($scpNum);

			// 記事の存在可否チェック
			if ($this->logic->matchNotFound( $html )) {

				// ソフトデリート(レコードがアレば)
				$this->ScpreaderLogic->setSoftDelete( 1, $scpNum );

				echo "[data-expunged]\n";
				sleep(1);

				continue;
			}

			// debug //////////
			echo "scraping_now( ";

			// 記事読み込み
			$scpArray = $this->ScpreaderLogic->getScpArray( $scpNum, $html );
			unset($html);

			// debug //////////
			echo "),database_update,";

			// 読み込んだ記事をDBに保存しておく
			if ( is_numeric($scpArray["vote"]) ) {

				$this->ScpreaderLogic->saveScpArray(
					$scpArray["scp_num"],
					$scpArray["title"],
					$scpArray["item_number"],
					$scpArray["class"],
					$scpArray["protocol"],
					$scpArray["description"],
					$scpArray["vote"],
					$scpArray["created_by"],
					serialize($scpArray["tags"]),
					$scpArray["created_at"]
				);

				// ソフトデリート解除
				$this->ScpreaderLogic->setSoftDelete( 0, $scpNum );
			}else{

				// Voteが数字以外なら、記事読み込み失敗と判断してソフトデリートする
				$this->ScpreaderLogic->setSoftDelete( 1, $scpNum );
			}

			// debug //////////
			echo "done,";

			// debug //////////
			echo $scpArray["title"]."\n";

			sleep(2);
		}

		echo "Done.\n";
	}
	
}

