<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;
use Logics\Commons\WikidotApi;
use Logics\Commons\Mail;
use Cli\Commons\Console;

/**
 * Class CliEnTopRateCheckerLogic
 * @package Logics
 */
class CliEnTopRateCheckerLogic extends AbstractLogic
{
    /**
     * @var \Logics\Commons\WikidotApi
     */
    protected $api = null;

    protected $source = "";
    protected $parsed = array();
    protected $list = "";
    protected $rendered = "";

    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    public function getParsed()
    {
        return $this->parsed;
    }

    public function setParsed($parsed)
    {
        $this->parsed = $parsed;
        return $this;
    }

    public function getList()
    {
        return $this->list;
    }

    public function setList($list)
    {
        $this->list = $list;
        return $this;
    }

    public function getRendered()
    {
        return $this->rendered;
    }
    
    public function setRendered($rendered)
    {
        $this->rendered = $rendered;
        return $this;
    }
    
    /**
     * モデル・モジュール読み込み
     */
    protected function getModel()
    {
        $this->api = new WikidotApi();
    }
    
    /**
     * 序文の生成
     * @param $text
     * @return CliEnTopRateCheckerLogic
     */
    public function createHeadText($text)
    {
        $date = date("Y年m月d日 H:i:s");
        $text = "更新日時 {$date}\n\n\n{$text}";
        return $this->setRendered($text);
    }

    /**
     * データ取得
     * @param $page
     * @return CliEnTopRateCheckerLogic
     */
    public function scraping($page)
    {
        $html = Scraping::run('http://www.scp-wiki.net/top-rated-pages/p/' . $page);
        
        // curlのrangeオプションが死んでるっぽいので暫定対応
        $html = substr($html, 30580, 15000);
        return $this->setSource($html);
    }

    /**
     * 不要部分の削除
     * @return CliEnTopRateCheckerLogic
     */
    public function cutOff()
    {
        $source = $this->getSource();
        $source = substr($source, strpos($source, '<div class="list-pages-box">'));
        $source = strstr($source, '<div class="pager">', true);
        $source = trim($source);
        return $this->setSource($source);
    }

    /**
     * パース
     * @return CliEnTopRateCheckerLogic
     */
    public function parsing()
    {
        $source = $this->getSource();
        preg_match_all('@<a href="/(.*?)">(.*?)</a> \(rating: (.*?), comments: (.*?)\)@', $source, $matches);

        $return = array();
        if (isset($matches[1][0]) && isset($matches[2][0]) && isset($matches[3][0]) && isset($matches[4][0])) {

            foreach ($matches[1] as $key => $url) {
                $return[$key] = array(
                    "url" => $url,
                    "title" => htmlspecialchars_decode($matches[2][$key]),
                    "vote" => $matches[3][$key],
                    "comments" => $matches[4][$key],
                );
            }
        }

        return $this->setParsed($return);
    }
    
    /**
     * ページのメタデータから日本語タイトルを取得
     * @param $url
     * @param $title
     * @return mixed
     */
    public function translateParsed($url, $title)
    {
        // SCP記事は飛ばす
        if ($url === strtolower($title) && strpos($title, "SCP-") === 0) {
            return $title;
        }

        // 特殊な文字も飛ばす
        if (strpos($title, "½") !== false) {
            return $title;
        }

        $pageMeta = $this->api->pagesGetMeta("scp-jp", array($url));

        // 未翻訳の場合
        if (!isset($pageMeta[$url])) {
            return $title;
        }

        $newTitle = $pageMeta[$url]["title"];

        // Wiki構文のエスケープ
        $newTitle = str_replace(array("[", "]"), "", $newTitle);

        // debug ////////////////////////////////////////
        echo "translate {$title} / {$newTitle}\n";
        usleep(500000);

        return trim($newTitle);
    }

    /**
     * リストの生成
     * @return CliEnTopRateCheckerLogic
     */
    public function renderList()
    {
        $rows = "";
        foreach ($this->getParsed() as $key => $line) {

            $title = $this->translateParsed($line['url'], $line['title']);

            $rows .= "# [[[{$line['url']}|{$title}]]] (rating: {$line['vote']}, comments: {$line['comments']})\n";
        }
        
        return $this->setList($this->getList() . $rows);
    }
    
    
    /**
     * 投稿"
     * @param string $title
     * @param string $revision_comment
     * @return $this
     */
    public function posting($title = "評価の高い記事-EN", $revision_comment = "[njr-sys]automated update")
    {
        $site = "scp-jp";
        $page = "top-rated-pages-en";
        $content = $this->getRendered() . $this->getList();
        $tags = array("njr-sys","ハブ");


        $this->api->pagesSaveOne($site, $page, $title, $content, $tags, $revision_comment);
        return $this;
    }
}