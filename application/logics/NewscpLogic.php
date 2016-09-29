<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;

/**
 * Class NewscpLogic
 * @package Logics
 */
class NewscpLogic extends AbstractLogic {
	
	protected function getModel() {
	}

	protected function scrapingWrapper( $url, $regex ) {

		// htmlソースの取得
		$html = Scraping::run( $url, 35000 );

		try {

			// マッチング処理
			$is_match = preg_match_all( $regex, $html, $articles);

			// 例外投げ部
			if (!$is_match) {
				throw new \Exception('Scraping failure');
			}
		} catch (\Exception $e) {
			echo 'Nijiru System Error: ';
			echo $e->getMessage();
			exit;
		}

		// ソースの破棄
		unset($html);

		return $articles;
	}

	/**
	 * 最新記事スクレイピング
	 * @param $rawUrl
	 * @param $pageNum
	 * @return array
	 */
	public function getArticleDataArray( $rawUrl, $pageNum){


		//「最新の記事一覧」
		// 'http://ja.scp-wiki.net/most-recently-created/p/'
		// or 'http://ja.scp-wiki.net/most-recently-created-jp/p/'
		$url = $rawUrl.$pageNum;

		/**
		 * <a href="/scp-xxx-jp">SCP-XXX-JP</a></td>
		 * <td style="vertical-align: top; text-align: center;">
		 * <span class="odate time_xxxxxxxxxx format_%25Y%E5%B9%B4%25m%E6%9C%88%25d%E6%97%A5%20%25H%3A%25M%20">XX Xxx XXXX XX:XX</span></td>
		 */
		$regex = '@(<a href="/)(.*?)("\>)(.*?)(</a></td>)(\n)(<td style="vertical-align: top; text-align: center;"><span class="odate time_)(.*?)( format_)(.*?)(">)(.*?)(</span></td>)@';

		// スクレイピング
		$articles = $this->scrapingWrapper( $url, $regex );

		// 記事の各データ配列を取得
		$articleTitleArray = $articles[4];      // 記事タイトル SCP-XXX-JP
		$articleUrlArray = $articles[2];        // URL          scp-xxx-jp
		$articlePostDateArray = $articles[12];  // 更新日時     %25Y%E5%B9%B4%25m%E6%9C%88%25d%E6%97%A5%20%25H%3A%25M%20

		// ${'articleDataArray'.$pageNum}生成
		$articleDataArray = array();
		$i=0;
		while(list($key, $articleTitleStr) = each($articleTitleArray)) {

			// 各要素の取得
			$itemNumber = "";
			if (preg_match('@^(SCP-)(.*?)(-JP)$@', $articleTitleStr)) {
				$itemNumber = preg_replace( '/[^0-9]/', '', $articleTitleStr );
			}

			// SCP記事/Tale判別
			$isScpArticle = false;
			if ( "" != $itemNumber ){
				$isScpArticle = true;
			}

			// 変数の格納
			$articleDataArray[] = array(
				'id'        =>  $i,'title'=>$articleTitleStr,
				'url'       =>  $articleUrlArray[$i],
				'postDate'  =>  date( "Y-m-d H:i:s", Scraping::convertWikidotDateToTimestamp( $articlePostDateArray[$i]) ),
				'itenNumber'    =>  $itemNumber,
				'isScpArticle'  =>  $isScpArticle,
			);
			$i++;
		}
		unset($articleTitleArray);
		
		// 記事タイトルをキーにした連想配列を返す
		return $articleDataArray;
	}
	
}