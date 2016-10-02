<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;
use Models\SiteMembersModel;
use Models\SiteActivityModel;
use Cli\Commons\Console;

class CliUserActivityLogic extends AbstractLogic {
	
	/**
	 * @var SiteMembersModel
	 */
	protected $SiteMembers = null;

	/**
	 * @var SiteActivityModel
	 */
	protected $SiteActivity = null;
	
	protected function getModel() {
		$this->SiteMembers = SiteMembersModel::getInstance();
		$this->SiteActivity = SiteActivityModel::getInstance();
	}

	/**
	 * 最近の更新 スクレイピング
	 * @param $page
	 * @return mixed
	 */
	public function getRecentChangesHtml($page) {
		$url = "http://ja.scp-wiki.net/system:recent-changes/?page={$page}";
		$contents = Scraping::run($url);

		// 必要箇所の抜き出し
		$contents = mb_strstr($contents, '<div class="changes-list" id="site-changes-list">', false);
		return mb_strstr($contents, '<div id="page-info-break"></div>', true);
	}
	
	/**
	 * 最近の更新 マッチング処理
	 * @param $html
	 * @return mixed
	 */
	public function matchHtml( $html ) {
		preg_match_all('@((<div class="changes-list-item">)((.|\s)*?)(</div>))@', $html, $users);
		return $users;
	}
	
	/**
	 * 最近の更新 情報の抽出
	 * @param $recentChange
	 * @return array
	 */
	public function matchRecentChanges( $recentChange ) {
		
		// returnするやつの初期化
		$result = array();
		
		// ユーザー名
		preg_match('@onclick="(.*?)return false;" >(.*?)</a>@', $recentChange, $tmp);
		$result["name"] = str_replace("http://www.wikidot.com/user:info/","",$tmp[2]);

		// 更新日
		preg_match('@<span class="odate time_(.*?)">((.|\s)*?)</span>@', $recentChange, $tmp);
		$result["mod-date"] = date("Y-m-d H:i:s", Scraping::convertWikidotDateToTimestamp($tmp[2]));
		
		unset($tmp);
		return $result;
	}

	/**
	 * データベース保存
	 * @param $userActivity
	 * @return bool
	 */
	public function saveData( $userActivity ) {

		foreach ( $userActivity as $name=>$recent_date ) {

			$result = false;

//			// DBを検索
			$record = $this->SiteActivity->get( $name );

			// もしユーザーが登録済みなら、更新日時を比較
			if ( $record ) {

				// もし更新日時が新しいものになっていれば、DBを更新
				if ( $record[0]["recent_date"] != $recent_date ) {
					$result = $this->SiteActivity->update( $name, $recent_date );
				}

			} else {

				// 未登録ユーザーなら、レコードを追加
				$result = $this->SiteActivity->insert( $name, $recent_date );
			}

			if ($result == false) {
				return false;
			}
		}

		return true;
	}
}