<?php
namespace Models;

use Models\Commons\AbstractModel;

/**
 * Class NjcAddressModel
 * ニジルコインアドレス管理
 *
 * @package Models
 */
class NjcAddressModel extends AbstractModel
{
    const TABLE = "njc_address";
    
    const ID = "id";
    const ADDRESS = "address";
    const WIKIDOT_ID = "wikidot_id";
    const USERS_NUMBER = "users_number";
    const IS_DISABLED = "is_disabled";
    const CREATED_AT = "created_at";
    
    /**
     * WikidotID で検索
     * @param $wikidot_id
     * @return bool
     */
    public function getAddressByWikidotId($wikidot_id)
    {
        $record = $this->execSql('SELECT address FROM ' . self::TABLE . ' WHERE wikidot_id = ? AND is_disabled = 0', array($wikidot_id), true);
        if (!$record) {
            return false;
        }
        return $record[0]["address"];
    }
    
    /**
     * UserNumber で検索
     * @param $users_number
     * @return bool
     */
    public function getAddressByUserNumber($users_number)
    {
        $record = $this->execSql('SELECT address FROM ' . self::TABLE . ' WHERE users_number = ? AND is_disabled = 0', array($users_number), true);
        if (!$record) {
            return false;
        }
        return $record[0]["address"];
    }
    
    /**
     * Address の登録有無を確認
     * @param $address
     * @return bool
     */
    protected function checkAddress($address)
    {
        $record = $this->execSql('SELECT address FROM ' . self::TABLE . ' WHERE address = ? AND is_disabled = 0', array($address), true);
        if (!$record) {
            return false;
        }
        return true;
    }

    /**
     * Address の新規登録
     * @param string $address
     * @param string $wikidot_id
     * @param null|string $users_number
     * @return bool
     */
    public function insertAddress($address, $wikidot_id, $users_number = null)
    {
        // 既にある場合は false
        if ($this->checkAddress($address)) {
            return false;
        }

        // 新規作成
        $sql = 'INSERT INTO ' . self::TABLE . '(address, wikidot_id, users_number) VALUES (?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array($address, $wikidot_id, $users_number));
    }

    /**
     * userIdAddress に wikidotID を紐付け
     * @param $wikidot_id
     * @param $users_number
     * @return array|bool|string
     */
    public function updateUserIdAddress($wikidot_id, $users_number)
    {
        // userIdAddress に WikidotID を紐付け
        return $this->execUpdate('UPDATE ' . self::TABLE . ' SET wikidot_id = ? WHERE users_number = ?', array(
            $wikidot_id,
            $users_number,
        ));
    }

    /**
     * userIdAddress に wikidotID を紐付け
     * @param $wikidot_id
     * @return array|bool|string
     */
    public function disableWikidotIdAddress($wikidot_id)
    {
        // userIdAddress に WikidotID を紐付け
        return $this->execUpdate('UPDATE ' . self::TABLE . ' SET is_disabled = 1 WHERE wikidot_id = ?', array($wikidot_id));
    }
}