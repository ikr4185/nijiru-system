<?php
namespace Logics\Commons;


/**
 * Class Database
 * スクレイピング ツールクラス
 */
class Scraping
{

    /**
     * スクレイピング
     * @param $url
     * @param null $byte
     * @return mixed|string
     */
    public static function run($url, $byte = null)
    {
        //htmlソースの取得
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_USERAGENT, "Nijiru System - SCP_Foundation");
        curl_setopt($ch, CURLOPT_REFERER, "http://ja.scp-wiki.net/");

        // 取得Byte数制限
        if (isset($byte)) {
            curl_setopt($ch, CURLOPT_RANGE, $byte);
        }

        $result = curl_exec($ch);

//		if (curl_errno($ch)) {
//			$result =  "Error: " . curl_error($ch);
//		} else {
//		}

        curl_close($ch);
        return $result;
    }

    /**
     * HTTPステータスだけの確認
     * @param $url
     * @return null
     */
    public static function getStatusCode($url)
    {
        $header = null;

        //htmlソースの取得
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        curl_setopt($ch, CURLOPT_USERAGENT, "Nijiru System - SCP_Foundation");
        curl_setopt($ch, CURLOPT_REFERER, "http://ja.scp-wiki.net/");

        $result = curl_exec($ch);
        unset($result);

        if (!curl_errno($ch)) {
            $header = curl_getinfo($ch);
        }
        curl_close($ch);

        if (isset($header['http_code'])) {
            return $header['http_code'];
        }
        return null;
    }

    /**
     * Wikidotの日付を日本時間に直してTimestampに変換
     * Scraping::convertWikidotDateToTimestamp
     * @param $dateStr
     * @return int
     */
    public static function convertWikidotDateToTimestamp($dateStr)
    {
        /*
        array(4) {
          [0]=>
          string(2) "04"
          [1]=>
          string(3) "Mar"
          [2]=>
          string(4) "2016"
          [3]=>
          string(5) "16:41"
        }
        ↓
        "10-Oct 2000" . "16:41:00"
        ↓
        timestamp
         */

        $dateArray = explode(" ", $dateStr);
        $timeArray = explode(":", $dateArray[3]);
        $dateStr = $dateArray[0] . "-" . $dateArray[1] . " " . $dateArray[2];

        $timestamp = strtotime($dateStr) + $timeArray[0] * 60 * 60 + $timeArray[1] * 60;

        // Wikidotのソースに含まれる時刻はグリニッジ時間なので、9時間足す
        $timestamp = $timestamp + 9 * 60 * 60;

        $datetime = $timestamp;

        return $datetime;
    }

}