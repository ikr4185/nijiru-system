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

        $text = "> [http://www.scp-wiki.net SCP-EN]で最も好まれているSCP記事,Tale記事を見ることができます。投票数はSCP-ENの物です。
> このページは [[*user ikr_4185]] 作成の Nijiru-System Bot により、毎週土曜0:00頃、自動で更新されます(システム都合上、履歴には [[*user nanimono-demonai]]さんが表示されます)。
> 
> SCP-JPで、最も支持されたページを見るには[[[top-rated-pages|評価の高い記事]]]を参照してください。

------
";

        $this->logic->createHeadText($text)->posting("評価の高い記事-EN", "[njr-sys]automatic update.");

        Console::log("Done.");
    }

}

