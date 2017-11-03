<?php
namespace Models;

use Models\Commons\AbstractModel;

class UsersModel extends AbstractModel
{
    // /////////////////////////////////////////////////////////////////
    // ▼ユーザ情報関連処理 ////////////////////////////////////////////
    // /////////////////////////////////////////////////////////////////

    // 注意 ややこしい名前にした自分を殴りたい
    // number : users_id
    // id : login_name
    // user_name : nickname
    const NUMBER = "number";
    const ID = "id";
    const PASSWORD = "password ";
    const USER_NAME = "user_name ";
    const POINT = "point ";
    const PUBLICATION = "publication ";
    const ADD_DATE = "add_date ";
    const LAST_LOGIN = "last_login";
    const LAST_OGAMI = "last_ogami ";

    const TABLE = "users";

    /**
     * 公開設定のユーザー一覧を取得 + njr_assets をJOIN
     * @return array|string
     */
    public function getAllUsers()
    {
        return $this->execSql('SELECT a.id, a.user_name, b.point, b.tera_point FROM users as a LEFT JOIN ' . NjrAssetModel::TABLE . ' as b ON  a.number = b.users_number WHERE a.publication = 2', array(), true);
    }

    /**
     * ユーザー名からIDを取得
     * @param $name
     * @return mixed|string
     */
    public function getIdFromName($name)
    {
        $id = $this->execSql('SELECT id FROM users WHERE user_name = ?', array($name));

        if (!$id) {
            $error = '指定されたユーザーは登録されていません';
            return $error;
        }
        return $id["id"];
    }

    /**
     * IDからユニークナンバーを取得
     * @param $id
     * @return array|string
     */
    public function getNumberById($id)
    {
        $users = $this->execSql('SELECT number FROM users WHERE id LIKE ?', array($id));
        return $users["number"];
    }

    /**
     * ユニークナンバーからIDを取得
     * @param $number
     * @return array|string
     */
    public function getIdByNumber($number)
    {
        $users = $this->execSql('SELECT id FROM users WHERE number LIKE ?', array($number));
        return $users["id"];
    }

    /**
     * 最終ログイン日時取得
     * @param $id
     * @return bool|string
     */
    public function getLastLogin($id)
    {
        return $this->execSql('SELECT last_login FROM users WHERE id = ?', array($id));
    }

    /**
     * 最終ログイン日時記録
     * @param $id
     * @return bool|string
     */
    public function setLastLogin($id)
    {
        $date = date("Y-m-d H:i:s");
        return $this->execUpdate('UPDATE users SET last_login = ? WHERE id = ?', array($date, $id));
    }

    /**
     * ID情報を取得
     * @param $id
     * @return mixed|string
     */
    public function getIdInfo($id)
    {
        return $this->execSql('SELECT * FROM users WHERE id = ?', array($id));
    }

    // /////////////////////////////////////////////////////////////////
    // ▼新規登録関連処理 //////////////////////////////////////////////
    // /////////////////////////////////////////////////////////////////

    /**
     * 新規登録: 既存のIDとかぶっていないかチェック
     * @param $id
     * @return bool|string
     */
    public function checkAlreadyRegistered($id)
    {
        return $this->execSql('SELECT id FROM users WHERE id = ?', array($id));
    }

    /**
     * 新規登録
     * @param $id
     * @param $pass
     * @param $name
     * @return bool|string
     */
    public function setIdInfo($id, $pass, $name)
    {

        $sql = 'insert into users(id, password, user_name, point, last_login) values (?, ?, ?, 0, now())';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(array($id, $pass, $name));
    }

    /**
     * 登録情報修正：マルチ
     * @param $column string 修正するカラム名　※ユーザー入力不可にすること
     * @param $data string 修正したいデータ
     * @param $id string 対象ユーザーID
     * @return bool|string
     */
    public function updateData($column, $data, $id)
    {
        return $this->execUpdate('UPDATE users SET ' . $column . ' = ? WHERE id = ?', array($data, $id));
    }

    /**
     * 登録情報修正：ユーザー公開度設定
     * @param $flag
     * @param $id
     * @return bool|string
     */
    public function setUserPublication($flag, $id)
    {
        return $this->execUpdate('UPDATE users SET publication = ? WHERE id = ?', array($flag, $id));
    }

}