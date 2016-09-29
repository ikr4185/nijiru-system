<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;

class CliScpJpScrapingLogic extends AbstractLogic {
		
	protected function getModel() {
	}
	
	/**
	 * @param $i
	 * @return mixed
	 */
	public function wrapStrPad( $i ) {
		if ( $i >= 1000 ) {
			return str_pad($i, 4, 0, STR_PAD_LEFT);
		}
		return str_pad($i, 3, 0, STR_PAD_LEFT);
	}

	public function getHtml($scpNum) {
				
		$url = "http://ja.scp-wiki.net/scp-{$scpNum}-jp";
		$contents = Scraping::run($url);

		// 記事始まり～記事終わりまでを取得
		$contents = mb_strstr($contents, '<div id="page-content">', false);
		return mb_strstr($contents, '<div id="page-info-break"></div>', true);
	}

	public function matchNotFound( $html ) {
		// マッチング処理
		preg_match('@(<h1 id="toc0"><span>このページはまだ存在しません。</span></h1>)@', $html, $users);

		// 未作成ページならtrue
		if (isset($users[0])) {
			return true;
		}
		return false;
	}

}