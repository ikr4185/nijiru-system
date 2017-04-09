<?php
namespace Cli;

use Cli\Commons\CliAbstract;
use Cli\Commons\Console;
use Logics\SiteMembersLogic;

/**
 * Class CliSitMemberStatistics
 *
 * php /home/njr-sys/public_html/application/cli/commons/cli_load.php CliSitMemberStatistics
 *
 * @package Cli
 */
class CliSitMemberStatistics extends CliAbstract
{
    /**
     * @var SiteMembersLogic
     */
    protected $logic = null;
    
    protected $is_debug = false;
    
    protected function getLogic()
    {
        $this->logic = new SiteMembersLogic();
        $this->is_debug = false;
    }
    
    public function indexAction()
    {
        Console::log("Start.");
        
        // 全サイトメンバーを取得
        Console::log("all user");
        $siteMembers = $this->logic->getUserIndex();
        
        // 各メンバーの情報
        foreach ($siteMembers as $member) {
            Console::log("");
            Console::log("user [{$member["name"]}] ...");
            
            // 執筆数
            $article_count = $this->logic->getUserArticlesCount($member["name"]);
            Console::log("articleCount: {$article_count}");
            
            // 最高評価
            $maxVote = $this->logic->getMaxVote($member["name"]);
            Console::log("maxVote: {$maxVote["vote"]} / {$maxVote["item_number"]}");
            
            // 平均評価
            $average_vote = $this->logic->getAverageVote($member["name"], $article_count);
            Console::log("averageVote: {$average_vote}");
            
            // 最後の活動時刻
            $recentActivity = $this->logic->getUserRecentActivity($member["name"]);
            Console::log("recentActivity: {$recentActivity["type"]} / {$recentActivity["recent_date"]}");
            
            // 保存
            Console::log("member_id: {$member["id"]}, article_count: {$article_count}, max_vote_id: {$maxVote["id"]}, average_vote: {$average_vote}, recent_activity_id: {$recentActivity["id"]}");
            
            $oldRecord = $this->logic->getStatistics($member["id"]);

            if (isset($oldRecord["id"])) {
                $this->logic->updateStatistics($oldRecord["id"], $member["id"], $article_count, $maxVote["id"], $average_vote, $recentActivity["id"]);
            } else {
                $this->logic->insertStatistics($member["id"], $article_count, $maxVote["id"], $average_vote, $recentActivity["id"]);
            }
            
            usleep(500000);
        }
        
        Console::log("End.");
    }
    
}