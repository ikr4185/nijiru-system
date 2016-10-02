<?php
namespace Cli;
use Logics\CliUserGetterLogic;

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
		
		// TODO ページ数を取得する処理

		for ( $page=1; $page<=14; $page++ ) {

			echo "{$page},";

			$html = $this->logic->getHtml($page);

			$users = $this->logic->matchHtml($html);
			unset($html);

			// 各ユーザーの情報を抽出・保存
			foreach ($users[3] as $user){
				$userInfo = $this->logic->matchUsers( $user );
				$this->logic->save( $userInfo["name"], $userInfo["wikidot_id"], $userInfo["since"] );
			}
			unset($users);

		}

		echo "Done.\n";
	}
	
}

