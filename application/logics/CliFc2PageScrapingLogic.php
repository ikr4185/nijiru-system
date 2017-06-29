<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;

//use Logics\Commons\WikidotApi;
//use Logics\Commons\Mail;
use Cli\Commons\Console;
use \Cores\Config\Config;
use Logics\Commons\WikidotApi;

/**
 * Class CliFc2PageScrapingLogic
 * @package Logics
 */
class CliFc2PageScrapingLogic extends AbstractLogic
{
    /**
     * @var \Logics\Commons\WikidotApi
     */
    protected $api = null;
    
    protected $pageList = array();
    protected $source = "";
    protected $pageLinks = array();
    protected $originalUrls = array();
    protected $jpTransferStatus = array();

    protected $runDate = "";

    public function getPageList()
    {
        return $this->pageList;
    }
    
    public function setPageList($pageList)
    {
        $this->pageList = $pageList;
        return $this;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    public function getPageLinks()
    {
        return $this->pageLinks;
    }

    public function setPageLinks($pageLinks)
    {
        $this->pageLinks = $pageLinks;
        return $this;
    }

    public function getOriginalUrls()
    {
        return $this->originalUrls;
    }

    public function setOriginalUrls($originalUrls)
    {
        $this->originalUrls = $originalUrls;
        return $this;
    }

    public function getJpTransferStatus()
    {
        return $this->jpTransferStatus;
    }

    public function setJpTransferStatus($jpTransferStatus)
    {
        $this->jpTransferStatus = $jpTransferStatus;
        return $this;
    }
    
    /**
     * モデル・モジュール読み込み、初期化
     */
    protected function getModel()
    {
        // 実行開始日時の取得
        $this->runDate = date("Ymd_His");
        $this->api = new WikidotApi();
    }
    
    /**
     * 対象ページのリストの読み込み
     * @return CliFc2PageScrapingLogic
     */
    public function loadPageList()
    {
        $tmp = file(Config::load("dir.logs") . "/fc2wiki/page_list.dat");
        
        $pageList = array();
        foreach ($tmp as $line) {
            $line = trim(str_replace('"', '', $line));
            $pageList[] = explode("\t", $line);
        }
        
        return $this->setPageList($pageList);
    }
    
    /**
     * データ取得
     * @param $url
     * @return CliFc2PageScrapingLogic
     */
    public function scraping($url)
    {
        $html = Scraping::run('http://scpjapan.wiki.fc2.com/wiki/' . $url);
        return $this->setSource($html);
    }
    
    /**
     * 不要部分の削除
     * @return CliFc2PageScrapingLogic
     */
    public function cutOff()
    {
        $source = $this->getSource();
        $source = substr($source, strpos($source, '<div id="container">'));
        $source = strstr($source, '<div id="menu" class="menubox">', true);
        $source = trim($source);
        return $this->setSource($source);
    }

    /**
     * ページ内に含まれるリンクの取得
     * @param $content
     * @return CliFc2PageScrapingLogic
     */
    public function parseLinks($content)
    {
        $pageLinks = array();

        preg_match_all('@\<a title\="http://(.*?)" href\=\"@', $content, $matches);
        if (isset($matches[1])) {
            foreach ($matches[1] as $links) {

                if (strpos($links, "scpjapan.wiki.fc2.com/wiki/") === false) {
                    $pageLinks[] = $links;
                }

            }
        }
        return $this->setPageLinks($pageLinks);
    }

    /**
     * 該当ページの元記事URLを割り出す
     * @return $this
     */
    public function checkOriginalUrl()
    {
        $originalUrls = array();
        $pageLinks = $this->getPageLinks();

        if (!empty($pageLinks)) {

            foreach ($pageLinks as $pageLink) {

                // もしSCP-ENのリンクだったら
                if (strpos($pageLink, "www.scp-wiki.net/") === 0 || strpos($pageLink, "scp-wiki.wikidot.com/") === 0) {

                    // ホスト名の部分を削除
                    $parsedLink = str_replace("www.scp-wiki.net/", "", $pageLink);
                    $parsedLink = str_replace("scp-wiki.wikidot.com/", "", $parsedLink);

                    // empty以外ならページURLとして記録
                    if (!empty($parsedLink)) {
                        $originalUrls[] = $parsedLink;
                    }
                }
            }

        }
        return $this->setOriginalUrls($originalUrls);
    }

    /**
     * JP転載状況の確認
     */
    public function checkJpTransfer()
    {
        $jpTransferStatus = array();
        $originalUrls = $this->getOriginalUrls();

        if (!empty($originalUrls)) {
            foreach ($originalUrls as $originalUrl) {

                // SCP-JPの該当記事のHTTPステータスをチェックしに行く
                $status = Scraping::getStatusCode('ja.scp-wiki.net/' . $originalUrl);

                sleep(3);

                // SCP-JPの該当記事メタ情報を取得する
                $pageMeta = $this->api->pagesGetMeta("scp-jp", array($originalUrl));

                var_dump($pageMeta);

                if (!empty($pageMeta[$originalUrl])) {

                    $jpTransferStatus[] = array(
                        $originalUrl,
                        $status,
                        $pageMeta[$originalUrl]["created_at"],
                        $pageMeta[$originalUrl]["created_by"],
                        $pageMeta[$originalUrl]["updated_at"],
                        $pageMeta[$originalUrl]["updated_by"],
                        $pageMeta[$originalUrl]["title"],
                    );
                } else {
                    $jpTransferStatus[] = array($originalUrl, $status);
                }
                
                sleep(3);
            }
        }

        return $this->setJpTransferStatus($jpTransferStatus);
    }
    
    public function saving($pageTitle)
    {
        $date = date("Ymd_His");

        // スクレイピング結果の保存
        $fileName = "{$date}.html";
        file_put_contents(Config::load("dir.logs") . "/fc2wiki/pages/{$fileName}", $this->getSource());

        // 実行ログの保存
        $jpTransferStatuses = "";
        $jpTransferStatusArray = $this->getJpTransferStatus();

        // ページメタの取得成功時の処理
        if (!empty($jpTransferStatusArray)) {
            foreach ($jpTransferStatusArray as $jpTransferStatus) {

                // 最初のカラムを追加
                $jpTransferStatuses .= "\"{$jpTransferStatus[0]}\"";
                foreach ($jpTransferStatus as $key => $val) {

                    // 最初のカラムは飛ばす
                    if ($key === 0) {
                        continue;
                    }
                    $jpTransferStatuses .= ",\"{$val}\"";
                }
            }
            $jpTransferStatuses = trim($jpTransferStatuses, ",");
        }

        // ExcelCSV形式に修正
        $pageTitle = str_replace('"', '""', $pageTitle);
        if (!empty($jpTransferStatuses)) {
            $lineStr = "\"{$date}\",\"{$pageTitle}\",{$jpTransferStatuses}\n";
        } else {
            $lineStr = "\"{$date}\",\"{$pageTitle}\"\n";
        }

        file_put_contents(Config::load("dir.logs") . "/fc2wiki/run_pages_{$this->runDate}.log", $lineStr, FILE_APPEND);
    }
}