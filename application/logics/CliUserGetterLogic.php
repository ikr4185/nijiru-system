<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;
use Models\SiteMembersModel;

class CliUserGetterLogic extends AbstractLogic {

	/**
	 * @var SiteMembersModel
	 */
	protected $SiteMembers = null;
	
	protected function getModel() {
		$this->SiteMembers = SiteMembersModel::getInstance();
	}
	
	public function test() {
		
		$url = "http://ja.scp-wiki.net";
		
		// 未翻訳排除　----------------------------
		$contents = Scraping::run($url, 300);

		return $contents;
	}


	public function getHtml($page) {
		$url = "http://ja.scp-wiki.net/system:members/?page={$page}";
		$contents = Scraping::run($url);

		// 一般メンバーまでを取得
		return mb_strstr($contents, '<h1 id="toc1">', true);
	}

	public function matchHtml( $html ) {
		// マッチング処理
		preg_match_all('@((<td><span class="printuser avatarhover">)((.|\s)*?)(</tr>))@', $html, $users);
		return $users;
	}

	public function getPageNumber( $html ) {

		// ページャーの抽出
		preg_match('@<div class="pager">.*?</a></span></div>@',$html,$matches);
		unset($html);
		
		// ページの最大数を抽出
		$pager = $matches[0];
		preg_match_all('@updateMemberList\d*?\((\d*?)\)@',$pager,$matches);
		return max($matches[1]);
	}

	public function matchUsers( $users ) {

		// returnするやつの初期化
		$userInfo = array();

//		// ユーザー情報URL
//		preg_match('@(<a href=")(.*?)(")@', $users, $userInfoTmp);
//		// ユーザー名の抽出
//		$userInfo["name"] = str_replace("http://www.wikidot.com/user:info/","",$userInfoTmp[2]);

		// ユーザー名
		preg_match('@onclick="(.*?)return false;" >(.*?)</a>@', $users, $userInfoTmp);
		$userInfo["name"] = str_replace("http://www.wikidot.com/user:info/","",$userInfoTmp[2]);

//		var_dump($userInfo);exit;

		// WikidotID
		preg_match('@(userid=)(\d*?)(&)@', $users, $userInfoTmp);
		$userInfo["wikidot_id"] = $userInfoTmp[2];

		// サイト登録日
		preg_match('@since(.*?)">(.*?)</span>@', $users, $userInfoTmp);
		$userInfo["since"] = date("Y-m-d H:i:s", Scraping::convertWikidotDateToTimestamp($userInfoTmp[2]));

		unset($userInfoTmp);
		return $userInfo;
	}

	public function getUserInfo($url) {
		$contents = Scraping::run($url);

		// 一般メンバーまでを取得
		return mb_strstr($contents, '<h1 id="toc1">', true);
	}

	public function save( $name, $wikidot_id, $since ) {

		// チェック
		if ( ! $this->SiteMembers->check($name) ) {

			// insert
			$this->SiteMembers->insert($name, $wikidot_id, $since);

		}
	}
	
	public function checkDeletedUser( $allUsers ) {
		
		// 最新一覧から名前だけの配列をつくって
		$allUserNames = array();
		foreach ($allUsers as $user){
			$allUserNames[] =  $user["name"];
		}

		// DBの値(非ソフトデリート)を取ってきて
		$records = $this->SiteMembers->getAll();
		
		// 保存済みユーザーが最新一覧に居なければ、ソフトデリート
		foreach ($records as $record) {
			
			if (!in_array($record["name"],$allUserNames)) {
				$this->SiteMembers->setSoftDelete( 1, $record["id"] );
			}

		}
		
	}

}