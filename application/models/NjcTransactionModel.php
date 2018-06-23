<?php
namespace Models;

use Models\Commons\AbstractModel;

/**
 * Class NjcTransactionModel
 * ニジルコイン取引履歴
 *
 * @package Models
 */
class NjcTransactionModel extends AbstractModel
{
    
    const TABLE = "njc_transaction";
    
    const ID = "id";
    const TO = "to_address";
    const FROM = "from_address";
    const AMOUNT = "amount";
    const CREATED_AT = "created_at";
    
    //             1,000,000,000,000;
    const MAX_AMOUNT = 1000000000000;
    const LOOP_LIMIT = 100;
    
    /**
     * 特定アドレスの全トランザクションの取得
     * @param string $address
     * @param int $limit
     * @param bool $isDesc
     * @return array|bool|string
     */
    public function getTransactions($address, $limit = 50, $isDesc = true)
    {
        $sql = 'SELECT * FROM ' . self::TABLE . ' WHERE ' . self::TO . ' = ?';

        if ($isDesc) {
            $sql .= " ORDER BY " . self::CREATED_AT . " DESC";
        } else {
            $sql .= " ORDER BY " . self::CREATED_AT . " ASC";
        }

        $sql .= " LIMIT {$limit}";

        $transactions = $this->execSql($sql, array($address), true);
        
        if (!$transactions) {
            return false;
        }
        return $transactions;
    }
    
    /**
     * 特定アドレスの全数量の取得
     * @param $address
     * @return array|bool|string
     */
    public function getAmount($address)
    {
        $transactions = $this->execSql('SELECT sum(amount) AS totalAmount FROM ' . self::TABLE . ' WHERE ' . self::TO . ' = ?', array($address), true);
        
        if (!$transactions) {
            return false;
        }
        return $transactions[0]["totalAmount"];
    }
    
    /**
     * ニジルポイント加算
     * @param $to
     * @param $from
     * @param $amount
     * @return array|bool|string
     */
    public function createTransaction($to, $from, $amount)
    {
        $count = 0;
        $amount = (int)$amount;
        
        // MAX_AMOUNT を超える場合
        if ($amount >= self::MAX_AMOUNT) {
            
            $isOver = true;
            
            // MAX_AMOUNT 以下になるまでループ
            while ($isOver) {
                
                // ループしすぎな時は強制終了
                if ($count > self::LOOP_LIMIT) {
                    return false;
                }
                
                $amount = $amount - self::MAX_AMOUNT;
                $count++;
                
                // MAX_AMOUNT 以下になったら処理開始
                if ($amount <= self::MAX_AMOUNT) {
                    $isOver = false;
                }
            }
        }
        
        // count の数だけ最大値の追加レコードを入れる
        for ($i = 0; $i < $count; $i++) {
            // 出金
            $sql = 'INSERT INTO ' . self::TABLE . '(' . self::TO . ', ' . self::FROM . ', amount) VALUES (?, ?, ?)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array($from, $to, -(self::MAX_AMOUNT)));
            
            // 入金
            $sql = 'INSERT INTO ' . self::TABLE . '(' . self::TO . ', ' . self::FROM . ', amount) VALUES (?, ?, ?)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array($to, $from, self::MAX_AMOUNT));
        }
        
        // 残りの Amount を追加する
        
        // 出金
        $sql = 'INSERT INTO ' . self::TABLE . '(' . self::TO . ', ' . self::FROM . ', amount) VALUES (?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($from, $to, -($amount)));
        
        // 入金
        $sql = 'INSERT INTO ' . self::TABLE . '(' . self::TO . ', ' . self::FROM . ', amount) VALUES (?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($to, $from, $amount));
        
        return true;
    }
    
}