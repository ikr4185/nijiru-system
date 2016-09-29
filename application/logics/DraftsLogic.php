<?php
namespace Logics;
use Logics\Commons\Scraping;

/**
 * Class DraftsLogic
 * @package Logics
 */
class DraftsLogic extends ForumLogic {

	/**
	 * データ配列の取得
	 * @return array|void
	 */
	public function getDrafts(){

		$html = Scraping::run("http://ja.scp-wiki.net/forum/c-790921/" , "24811-66582"); // byte数はおおざっぱに

		$draftsItemArray = $this->convertDrafts( $html );

		return $draftsItemArray;
	}

	/**
	 * ソース整形
	 * @param $html
	 * @return array
	 */
	public function convertDrafts( $html ){

		// 記事抽出
		$resultBool = preg_match_all('@(<tr class="">)([\s\S]*?)(</tr>)@', $html, $rawArray);
		$rawArray = $rawArray[0];
		// メモリ節約
		unset($html);

		// 失敗時はエラーを返す
		if ( !$resultBool ) {
			$resultsArray["error"] = "match failed";
		}

		// 要素数のカウント
		$count = count($rawArray);

		// 配列を準備
		$resultsArray = array();
		$matched = array();

		foreach ( $rawArray as $key=>$value ) {

			preg_match('@(<div class="title">)((.|\n)*?)(</div>)@', $value, $matched[$key]["title"]);
			// title 整形
			preg_match('@(<a href="/forum/t-)(.*)(">)(.*)(</a><br/>)@', $matched[$key]["title"][0], $matched[$key]["title"]);

			preg_match('@(<div class="description">)((.|\n)*?)(</div>)@', $value, $matched[$key]["description"]);

			preg_match('@(<td class="started">)((.|\n)*?)(</td>)@', $value, $matched[$key]["started"]);
			// started 整形
			preg_match('@(return false;" >)(.*)(</a></span>)@', $matched[$key]["started"][0], $matched[$key]["started-by"]);
			preg_match('@(<span class="odate time_)(.*)(">)((.|\s)*?)(</span>)@', $matched[$key]["started"][0], $matched[$key]["started-time"]);

			preg_match('@(<td class="posts">)((.|\n)*?)(</td>)@', $value, $matched[$key]["posts"]);

			preg_match('@(<td class="last">)((.|\n)*?)(</td>)@', $value, $matched[$key]["last"]);
			// last 整形
			preg_match('@(return false;" >)(.*)(</a></span>)@', $matched[$key]["last"][0], $matched[$key]["last-by"]);
			preg_match('@(<span class="odate time_)(.*)(">)((.|\s)*?)(</span>)@', $matched[$key]["last"][0], $matched[$key]["last-time"]);

		}
		// メモリ節約
		unset($rawArray);

		// 不要なものを削除
		for ( $key=0; $key<$count; $key++ ) {
			$resultsArray[$key]["title"] = $matched[$key]["title"][4];
			$resultsArray[$key]["title-link"] = 'http://ja.scp-wiki.net/forum/t-'.$matched[$key]["title"][2];
			$resultsArray[$key]["description"] = $matched[$key]["description"][2];
			$resultsArray[$key]["started-by"] = $matched[$key]["started-by"][2];
			$resultsArray[$key]["started-time"] = date( "Y-m-d H:i:s", Scraping::convertWikidotDateToTimestamp( $matched[$key]["started-time"][4] ) );
			$resultsArray[$key]["posts"] = $matched[$key]["posts"][2];

			if ( !empty($matched[$key]["last-time"][4]) ) {
				$resultsArray[$key]["last-by"] = $matched[$key]["last-by"][2];
//				$resultsArray[$key]["last-time"] = $matched[$key]["last-time"][4];
				$lastTime = $matched[$key]["last-time"][4];
				$comparedLastTime = $this->compareTimestamp( Scraping::convertWikidotDateToTimestamp( $lastTime ), time() );
				$resultsArray[$key]["last-time"] = $comparedLastTime;

				// HOT の判定
				$resultsArray[$key]["hot"] = $this->is_hot( Scraping::convertWikidotDateToTimestamp( $matched[$key]["started-time"][4] ), Scraping::convertWikidotDateToTimestamp( $lastTime ), $resultsArray[$key]["posts"] );

			}else{
				$resultsArray[$key]["last-by"] = "N/A";
				$resultsArray[$key]["last-time"] = "N/A";
			}
		}

		// 最初のスレッドを闇に葬る
		unset($resultsArray[0]);
		$resultsArray = array_values($resultsArray);

		return $resultsArray;

	}

	/**
	 * タイムスタンプの比較
	 * @param $timestampBfr
	 * @param $timestampAft
	 * @return string
	 */
	public function compareTimestamp( $timestampBfr, $timestampAft ){

		$relative_time = $timestampAft - $timestampBfr;

		// ゼロ除算回避
		if ($relative_time == 0) {
			return "";
		}

		// 日付に変換
		$date = floor( $relative_time / (60*60*24) );
		$hh = floor( ( $relative_time - $date * (60*60*24) ) / (60*60) );
		$mm = floor( ( $relative_time - $date * (60*60*24) - $hh * (60*60) ) / 60 );
//		$ss = $relative_time - $date * (60*60*24) - $hh * (60*60) - $mm * (60);

		if( $relative_time<60 ){//ss
			return '1分以内';
		}elseif( $relative_time>=60 && $relative_time<(60*60) ){//mm
			return $mm.'分前';
		}elseif($relative_time>=(60*60) && $relative_time<(60*60*24)){//hh
			return $hh.'時間 '.$mm.'分前';
		}elseif($relative_time>=(60*60*24)){//日付
			return $date.'日 '.$hh.'時間 '.$mm.'分前';
		}

		return "";

	}

	public function is_hot( $timestampBfr, $timestampAft, $posts ){

		$relative_time = $timestampAft - $timestampBfr;
		$relative_time_by_now = time() - $timestampBfr;
		$speed = floor($relative_time/$posts);

		$relative_time = time() - $timestampAft;

		$return = array();

		// ゼロ除算回避
		if ($relative_time == 0) {
			return "";
		}

		// 開始～
		// 日付に変換
		$datetime = $this->convertTimestampToDatetime($relative_time_by_now);

		if( $relative_time_by_now<60 ){//ss
			$return[0] = '<span style="color:red" class="b">開始から 1分以内</span>';
		}elseif( $relative_time_by_now>=60 && $relative_time_by_now<(60*60) ){//mm
			$return[0] = '開始から <span style="color:red" class="b">'.$datetime[2].'分</span>';
		}elseif($relative_time_by_now>=(60*60) && $relative_time_by_now<(60*60*24)){//hh
			$return[0] = '開始から <span style="color:red">'.$datetime[1].'時間 '.$datetime[2].'分</span>';
		}elseif($relative_time_by_now>=(60*60*24)){//日付
			$return[0] = '開始から '.$datetime[0].'日 '.$datetime[1].'時間 '.$datetime[2].'分';
		}else{
			$return[0] = '';
		}

		// レスの勢い
		// 日付に変換
		$datetime = $this->convertTimestampToDatetime($speed);

		if( $speed<60 ){//ss
			$return[1] = '<span style="color:red" class="b">'.$datetime[3].'秒</span>';
		}elseif( $speed>=60 && $speed<(60*60) ){//mm
			$return[1] = '<span style="color:red">'.$datetime[2].'分</span>';
		}elseif($speed>=(60*60) && $speed<(60*60*24)){//hh
			$return[1] = $datetime[1].'時間 '.$datetime[2].'分';
		}elseif($speed>=(60*60*24)){//日付
			$return[1] = $datetime[0].'日 '.$datetime[1].'時間 '.$datetime[2].'分';
		}else{
			$return[1] = '';
		}

		// 最終レス～
		// 日付に変換
		$datetime = $this->convertTimestampToDatetime($relative_time);

		if( $relative_time<60 ){//ss
			$return[2] = '<span style="color:red" class="b">最終レスから 1分以内</span>';
		}elseif( $relative_time>=60 && $relative_time<(60*60) ){//mm
			$return[2] = '最終レス <span style="color:red" class="b">'.$datetime[2].'分前</span>';
		}elseif($relative_time>=(60*60) && $relative_time<(60*60*24)){//hh
			$return[2] = '最終レス <span style="color:red">'.$datetime[1].'時間 '.$datetime[2].'分前</span>';
		}elseif($relative_time>=(60*60*24)){//日付
			$return[2] = '最終レス '.$datetime[0].'日 '.$datetime[1].'時間 '.$datetime[2].'分前';
		}else{
			$return[2] = '';
		}

		return $return;

	}

	private function convertTimestampToDatetime( $relative_time ){

		$return = array (0, 0, 0, 0);

		$date = floor( $relative_time / (60*60*24) );
		$hh = floor( ( $relative_time - $date * (60*60*24) ) / (60*60) );
		$mm = floor( ( $relative_time - $date * (60*60*24) - $hh * (60*60) ) / 60 );

		if( $relative_time<60 ){//ss
			$return = array (0, 0, 0, 1);
		}elseif( $relative_time>=60 && $relative_time<(60*60) ){//mm
			$return = array (0, 0, $mm, 0);
		}elseif($relative_time>=(60*60) && $relative_time<(60*60*24)){//hh
			$return = array (0, $hh, $mm, 0);
		}elseif($relative_time>=(60*60*24)){//日付
			$return = array ($date, $hh, $mm, 0);
		}

		return $return;

	}

	/**
	 * メモリ監視
	 * @param $html
	 * @return string
	 */
	public function limitMemory( $html ){

		$error = "";

		// メモリリーク防止
		$volume = strlen(bin2hex($html)) / 2;
		$volume = $volume - 35228;
		if ( $volume > 20000 ) {
			$error =  " (system: ".$volume." byte over)";
		}
		if ( $volume >= 24500) {
			$error = "System Error: Article is too long: ".$volume;
		}

		return $error;

	}

}