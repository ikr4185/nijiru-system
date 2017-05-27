<?php
namespace Cli;

use Cli\Commons\CliAbstract;
use Logics\CliEnTopRateCheckerLogic;
use Cli\Commons\Console;

/**
 * Class CliEnTopRateChecker
 * @package Cli
 */
class CliEnTopRateChecker extends CliAbstract
{
    /**
     * @var CliEnTopRateCheckerLogic
     */
    protected $logic = null;

    protected function getLogic()
    {
        $this->logic = new CliEnTopRateCheckerLogic();
    }

    public function indexAction()
    {
        Console::log("Start.");

        // top-rate-pages 解析 x 6 ページ
        // list生成
        for ($i = 1; $i <= 6; $i++) {
            Console::log("page {$i}.");
            $this->logic->scraping($i)->cutOff()->parsing()->renderList();
            sleep(2);
        }

        // 序文を生成して、テーブルを組み合わせて投稿
        Console::log("Posting.");

        $text = "ここではサイトで最も好まれているSCP記事,Tale記事を見ることができます。組織票や多重投票を避けるため、投票はサイトメンバーに限定されます。
今月作成された中で最も支持されたページを見るには[[[Top Rated Pages This Month|評価の高い記事(今月)]]]を参照してください。\n\n
それぞれのタグで評価の高い記事を確認したい際は[[[top-rated-scp|評価の高いSCP]]]、[[[Top Rated Tale|評価の高いTale]]]、
[[[Top Rated Other|その他の評価の高い記事]]]を参照してください。\n";

        $this->logic->createHeadText($text)->posting();

        Console::log("Done.");
    }

}

