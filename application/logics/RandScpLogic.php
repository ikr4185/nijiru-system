<?php
namespace Logics;

use Logics\Commons\AbstractLogic;
use Logics\Commons\Scraping;

/**
 * Class RandScpLogic
 * @package Logics
 */
class RandScpLogic extends AbstractLogic
{

    protected $context = null;

    public function __construct()
    {
        parent::__construct();

        // UA偽装
        $this->context = stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'header' => 'random SCP URL Checker by ikr_4185',
                ),
            ));

    }

    protected function getModel()
    {
    }

    /**
     * rand wrapper
     * @return mixed
     */
    protected function link0()
    {
        return sprintf("%03d", mt_rand(2, 999));
    }

    protected function link1()
    {
        return mt_rand(1000, 1999);
    }

    protected function link2()
    {
        return mt_rand(2000, 2999);
    }

    protected function link3()
    {
        return mt_rand(3000, 3999);
    }

    /**
     * 翻訳記事URLのランダム取得
     * @return string
     */
    public function getRandScp()
    {

        // 番台のランダム取得
        $linkFunc = 'link' . mt_rand(0, 2);

        // 取得した番台の、ランダムURL生成関数を実行
        $url = "http://ja.scp-wiki.net/scp-" . $this->$linkFunc();

        // 未翻訳排除　----------------------------
        $contents = Scraping::run($url, 300);

        mb_regex_encoding("UTF-8");
        if (preg_match('/(<title>SCP財団<\/title>)/u', $contents)) {
            return "";
        }
        return $url;
    }

    /**
     * JP記事URLのランダム取得
     * @return string
     */
    public function getRandJp()
    {

        // 番台のランダム取得
        $linkFunc = 'link' . mt_rand(0, 1);

        // 取得した番台の、ランダムURL生成関数を実行
        $url = "http://ja.scp-wiki.net/scp-" . $this->$linkFunc() . "-jp";

        // 未翻訳排除　----------------------------
        $contents = Scraping::run($url, 300);

        mb_regex_encoding("UTF-8");
        if (preg_match('/(<title>SCP財団<\/title>)/u', $contents)) {
            return "";
        }

        return $url;
    }

}