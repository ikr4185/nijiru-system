<?php
namespace Models;

use Models\Commons\AbstractModel;

class LowVoteLogModel extends AbstractModel
{

    /**
     * DBにある全低評価記事の情報を取得する(ソフトデリートされた記事は含まない)
     * @return mixed
     */
    public function getAllLowVotes()
    {
        $low_votes_log = $this->execSql('SELECT *
FROM low_votes_log
WHERE del_flg = 0', array(), true);

        return $low_votes_log;
    }

    /**
     * 低評価記事の情報をidから取得する
     * @param $low_votes_number
     * @return mixed
     */
    public function searchLowVotesById($low_votes_number)
    {
        $low_votes_log = $this->execSql('SELECT *
FROM low_votes_log
WHERE low_votes_number = ?', array($low_votes_number));

        return $low_votes_log;
    }

    /**
     * 低評価記事の情報を取得する(過去にソフトデリートされた物も含む)
     * @param string $url 記事URL
     * @param string $post_date 投稿日
     * @return mixed
     */
    public function searchLowVotes($url, $post_date)
    {
        $low_votes_log = $this->execSql('SELECT *
FROM low_votes_log
WHERE url = ?
AND post_date = ?', array($url, $post_date));

        return $low_votes_log;
    }

    /**
     * 低評価記事の情報を取得する(ソフトデリートされた物は含まない)
     * @param string $url 記事URL
     * @param string $post_date 投稿日
     * @return mixed
     */
    public function searchLowVotesNonDel($url, $post_date)
    {
        $low_votes_log = $this->execSql('SELECT *
FROM low_votes_log
WHERE url = ?
AND post_date = ?
AND del_flg = 0', array($url, $post_date));

        return $low_votes_log;
    }

    /**
     * 猶予済みフラグを更新する
     * @param string $url 記事URL
     * @param string $post_date 投稿日
     * @param $is_notified
     * @return bool
     */
    public function updateNotified($url, $post_date, $is_notified)
    {
        return $this->execUpdate('UPDATE low_votes_log SET is_notified = ? WHERE url = ? AND post_date = ?', array($is_notified, $url, $post_date));
    }

    /**
     * ソフトデリート
     * @param int $del_flg
     * @param string $url 記事URL
     * @param string $post_date 投稿日
     * @return bool
     */
    public function setSoftDeleteLowVotes($del_flg, $url, $post_date)
    {
        return $this->execUpdate('UPDATE low_votes_log SET del_flg = ? WHERE url = ? AND post_date = ?', array($del_flg, $url, $post_date));
    }

    /**
     * 汎用UPDATE文
     * @param string $url 記事URL
     * @param string $post_date 投稿日
     * @param string $name 対象カラム名
     * @param $var
     * @return bool
     */
    public function updateMulti($url, $post_date, $name, $var)
    {
        return $this->execUpdate("UPDATE low_votes_log SET {$name} = ? WHERE url = ? AND post_date = ?", array($var, $url, $post_date));
    }

    /**
     * 新規低評価記事の情報を追加する
     * @param $name
     * @param string $url 記事URL
     * @param string $post_date 投稿日
     * @param $del_date
     * @return string
     */
    public function insertLowVotes($name, $url, $post_date, $del_date)
    {
        // インサート
        $sql = 'insert into low_votes_log(
name, url, del_flg, is_notified, post_date, fall_date, del_date
) values (
?, ?, ?, ?, ?, ?, ?
)';
        $stmt = $this->pdo->prepare($sql);

        $post_date = new \DateTime($post_date);  // 投稿日時 → 投稿日時
        $add_date = new \DateTime();             // 記録日時 → 基準超え日時として記録
        $del_date = new \DateTime($del_date);    // 削除予定日 → 現時点から数えた規定の猶予期限

        $param = array(
            $name,
            $url,
            0,
            0,
            $post_date->format('Y-m-d H:i:s'),
            $add_date->format('Y-m-d H:i:s'),
            $del_date->format('Y-m-d H:i:s'),
        );
//        var_dump($param);

        $flag = $stmt->execute($param);

        if (!$flag) {
            return false;
        }
        return true;
    }

    /**
     * 猶予期限+基準超え日時を更新する
     * @param string $url 記事URL
     * @param string $post_date 投稿日
     * @param $del_date
     * @param $name
     * @return bool
     */
    public function updateLowVotes($url, $post_date, $del_date, $name)
    {
        return $this->execUpdate('UPDATE low_votes_log SET name = ?, del_date = ?, fall_date = now() WHERE url = ? AND post_date = ?', array(
            $name,
            $del_date,
            $url,
            $post_date,
        ));
    }

    /**
     * 有効なLVCユーザーの取得
     * @return mixed
     */
    public function getAvailableLvcUsers()
    {
        return $this->execSql('SELECT *
FROM admin_lvc_users
WHERE is_available = 1', array(), true);
    }
}