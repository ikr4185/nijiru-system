<?php
namespace Models;

use Models\Commons\AbstractModel;


class SiteMembersStatisticsModel extends AbstractModel
{
    /**
     * site_members_idで検索して、情報の取得
     * @param $id
     * @return array|string
     */
    public function get($site_members_id) {
        return $this->execSql( 'SELECT * FROM site_members_statistics WHERE site_members_id = ?', array($site_members_id), true );
    }
    
    /**
     * 情報の保存
     * @param $site_members_id
     * @param $article_count
     * @param $max_vote_scpjp_id
     * @param $average_vote
     * @param $recent_site_activity_id
     * @return bool
     */
    public function insert($site_members_id, $article_count, $max_vote_scpjp_id, $average_vote, $recent_site_activity_id)
    {
        $sql = 'insert into site_members_statistics(site_members_id, article_count, max_vote_scpjp_id, average_vote, recent_site_activity_id, created_at) values ( ?, ?, ?, ?, ?, ? )';
        $stmt = $this->pdo->prepare($sql);
        
        $created_at = date("Y-m-d H:i:s");
        return $stmt->execute(array(
            $site_members_id,
            $article_count,
            $max_vote_scpjp_id,
            $average_vote,
            $recent_site_activity_id,
            $created_at,
        ));
    }
    
    public function update($id, $site_members_id, $article_count, $max_vote_scpjp_id, $average_vote, $recent_site_activity_id)
    {
        $sql = 'UPDATE site_members_statistics 
SET site_members_id = ?, 
article_count = ?, 
max_vote_scpjp_id = ?, 
average_vote = ?, 
recent_site_activity_id = ?,
WHERE id = ?';
        
        $stmt = $this->pdo->prepare($sql);
        $flag = $stmt->execute(array(
            $site_members_id,
            $article_count,
            $max_vote_scpjp_id,
            $average_vote,
            $recent_site_activity_id,
            $id,
        ));
        
        if (!$flag) {
            return false;
        }
        return true;
    }
}