<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\SiteMembersLogic;

/**
 * Class SitemembersController
 * @package Controllers
 */
class SitemembersController extends WebController
{
    /**
     * @var SiteMembersLogic
     */
    protected $logic;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new SiteMembersLogic();
    }
    
    public function indexAction()
    {
        // 全サイトメンバーを取得
        $siteMembers = $this->logic->getAllJoinStatistics();
        
        // メンバー総数
        $count = count($siteMembers);
        
        // アクティブメンバー: 直近1ヶ月
        $activeMembers = $this->logic->getActiveUser(30);
        
        // アクティブメンバー: 直近1週間
        $activeMembersWeek = $this->logic->getActiveUser(7);
        
        // ソートの指定
        $sortBy = "";
        if ($this->input->getRequest("max", true) == "desc") {
            // Max Vote
            $this->logic->sortArrayByKey($siteMembers, "vote", SORT_DESC);
            $sortBy = "max";
        } elseif ($this->input->getRequest("ave", true) == "desc") {
            // Average Vote
            $this->logic->sortArrayByKey($siteMembers, "average_vote", SORT_DESC);
            $sortBy = "ave";
        } elseif ($this->input->getRequest("count", true) == "desc") {
            // Article Count
            $this->logic->sortArrayByKey($siteMembers, "article_count", SORT_DESC);
            $sortBy = "count";
        } elseif ($this->input->getRequest("date", true) == "desc") {
            // Since
            $this->logic->sortArrayByKey($siteMembers, "since", SORT_DESC);
            $sortBy = "date";
        } elseif ($this->input->getRequest("recent", true) == "desc") {
            // Recent Date
            $this->logic->sortArrayByKey($siteMembers, "recentDate", SORT_DESC);
            $sortBy = "recent";
        }
        
        $result = array(
            "siteMembers" => $siteMembers,
            "activeMembers" => $activeMembers,
            "activeMembersWeek" => $activeMembersWeek,
            "count" => $count,
            "sortBy" => $sortBy,
            "msg" => $this->logic->getMsg(),
        );
        $jsPathArray = array(
            "http://njr-sys.net/application/views/assets/js/nijiru_accordion.js",
        );
        $this->getView("index", "サイトメンバー一覧", $result, $jsPathArray);
        
    }
    
    /**
     * サイトメンバーに関する統計
     */
    public function memberHistoryAction()
    {
        // 最新記事を取得、月日へ変換
        $newestArticle = $this->logic->getNewestMember();
        $newestDate = date("Y-m", strtotime($newestArticle[0]["since"]));
        
        // 最古参メンバー「Dr Devan」の登録時刻を、月日へ変換
        $oldestDate = date("Y-m", strtotime("2013-07-08 20:09:00"));
        
        // 結果として出力するデータ
        $memberHistory = array();
        
        // 累計メンバー数計上
        $allMemberCount = 0;
        
        $i = 0;
        while (1) {
            
            // 安全装置
            if ($i > 120) {
                exit;
            }
            
            // 調査月を設定
            if ($i == 0) {
                $date = date("Y-m", strtotime($oldestDate));
            } else {
                $date = date("Y-m", strtotime($oldestDate . "-01 +{$i} month"));
            }
            
            // 月間の新人職員のアカウント名配列
            $allMemberName = $this->logic->getNewbiesInDateRange($date);
            
            // 月間のアクティブメンバー名配列
            $activeMemberName = $this->logic->getActiveUserInDateRange($date);
            
            // 月間新規メンバー数
            $count = count($allMemberName);
            
            // 累計メンバー数
            $allMemberCount = $allMemberCount + $count;
            
            // 月間アクティブメンバー数
            $activeMemberCount = count($activeMemberName);
            
            // 結果を格納
            $memberHistory[$i] = array(
                "date" => $date,
                "count" => $count,
                "allMemberCount" => $allMemberCount,
                "activeMemberCount" => $activeMemberCount,
                "newbies" => implode("/", $allMemberName),
            );
            
            // カウントを進める
            $i++;
            
            // 日付が現在に至ったら終了
            if ($date == $newestDate) {
                break;
            }
        }
        
        $result = array(
            "memberHistory" => $memberHistory,
            "msg" => $this->logic->getMsg(),
        );
        $jsPathArray = array(
            "https://www.google.com/jsapi",
            "http://njr-sys.net/application/views/assets/js/member_history_chart.js",
            "http://njr-sys.net/application/views/assets/js/jquery.balloon.js",
        );
        $this->getView("memberhistory", "Site Member History Statistics", $result, $jsPathArray);
    }
    
    /**
     * 過去Voteの履歴と統計的分析
     */
    public function voteHistoryAction()
    {
        // 最新記事を取得、月日へ変換
        $newestArticle = $this->logic->getNewestArticle();
        $newestDate = date("Y-m", strtotime($newestArticle[0]["created_at"]));
        
        // 最古の記事「稲穂」の投稿時刻を、月日へ変換
        $oldestDate = date("Y-m", strtotime("2013-10-15 00:36:04"));
        
        // 結果として出力するデータ
        $voteHistory = array();
        $totalCount = 0;
        $totalVote = 0;
        
        $i = 0;
        while (1) {
            
            // 安全装置
            if ($i > 120) {
                exit;
            }
            
            // 調査月を設定
            if ($i == 0) {
                $date = date("Y-m", strtotime($oldestDate));
            } else {
                $date = date("Y-m", strtotime($oldestDate . "-01 +{$i} month"));
            }
            
            // 月間の全Voteの配列
            $allVote = $this->logic->getVotesInDateRange($date);
            
            // 記事数
            $count = count($allVote);
            
            // 月間平均Voteを求める
            $avg = floor($this->logic->average($allVote));
            
            // 月間の中央値を求める
            $med = floor($this->logic->median($allVote));
            
            // 結果を格納
            $voteHistory[$i] = array(
                "date" => $date,
                "count" => $count,
                "avg" => $avg,
                "med" => $med,
            );
            
            // 総数
            $totalCount = $totalCount + $count;
            $totalVote = $totalVote + array_sum($allVote);
            
            // カウントを進める
            $i++;
            
            // 日付が現在に至ったら終了
            if ($date == $newestDate) {
                break;
            }
        }
        
        // 全体での平均評価
        $totalAverageVote = floor($totalVote / $totalCount);
        
        $result = array(
            "voteHistory" => $voteHistory,
            "totalCount" => $totalCount,
            "totalAverageVote" => $totalAverageVote,
            "msg" => $this->logic->getMsg(),
        );
        $jsPathArray = array(
            "https://www.google.com/jsapi",
            "http://njr-sys.net/application/views/assets/js/vote_history_chart.js",
        );
        $this->getView("votehistory", "Vote History Statistics", $result, $jsPathArray);
    }
    
}