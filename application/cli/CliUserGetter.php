<?php
namespace Cli;
use Logics\CliUserGetterLogic;
use Cli\Commons\Console;

/**
 * サイトメンバー情報の取得
 */
class CliUserGetter {

	/**
	 * @var CliUserGetterLogic
	 */
	protected $logic;

	public function __construct(  ) {
		$this->getLogic();
	}

	protected function getLogic() {
		$this->logic = new CliUserGetterLogic();
	}

	public function indexAction(){

		Console::log("Start.");
		
		// ページ数を取得する処理
		$html = $this->logic->getHtml(1);
		$max = $this->logic->getPageNumber( $html );
		if (!$max) {
			Console::log("ERR");
			exit;
		}

		// 全ユーザーの配列
		$allUsers = array();

		for ( $page=1; $page<=$max; $page++ ) {
			
			if ($page > 100) {
				Console::log("too long loop");
				Console::errorLog("too long loop".__CLASS__);
				exit;
			}

			Console::log("{$page},");

			$html = $this->logic->getHtml($page);

			$users = $this->logic->matchHtml($html);
			unset($html);

			// 各ユーザーの情報を抽出
			foreach ($users[3] as $user){
				$userInfo = $this->logic->matchUsers( $user );
				$allUsers[] = array(
					"name" => $userInfo["name"],
					"wikidot_id" => $userInfo["wikidot_id"],
					"since" => $userInfo["since"]
				);
			}
			unset($users);

		}
		
		// 退会ユーザーを記録
		$this->logic->checkDeletedUser( $allUsers );

		// 各ユーザーの情報を保存
		foreach ($allUsers as $user){
			$this->logic->save( $user["name"], $user["wikidot_id"], $user["since"] );
		}

		Console::log("Done.");
	}
	
}

