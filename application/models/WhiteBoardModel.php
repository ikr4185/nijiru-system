<?php
namespace Models;

use Models\Commons\AbstractModel;

/**
 * 財団絵チャ
 * Class WhiteBoardModel
 * @package Models
 */
class WhiteBoardModel extends AbstractModel
{
    
    /**
     * ファイル情報の保存
     * @param $token
     * @param $data
     * @param $pass
     * @return bool
     */
    public function insert($token, $data, $pass)
    {
        $sql = 'insert into white_board(token, data, pass) values ( ?, ?, ? )';
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(array($token, $data, $pass));
    }
    
    /**
     * 情報の更新
     * @param $token
     * @param $data
     * @return array|string
     */
    public function update($token, $data)
    {
        return $this->execUpdate(
            'UPDATE white_board SET data = ? WHERE token = ?',
            array($data, $token)
        );
    }
    
    /**
     * ファイル情報の取得
     * @param $token
     * @return array|string
     */
    public function getImage($token)
    {
        return $this->execSql('SELECT * FROM white_board WHERE token = ?', array($token), true);
    }
    
}