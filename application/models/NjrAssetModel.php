<?php
namespace Models;

use Models\Commons\AbstractModel;

/**
 * Class NjrAssetModel
 * ニジポ管理モデル
 *
 * @package Models
 */
class NjrAssetModel extends AbstractModel
{

    const TABLE = "njr_asset";

    const ID = "id";
    const USERS_NUMBER = "users_number";
    const POINT = "point";
    const TERA_POINT = "tera_point";
    const IS_FULL = "is_full";
    const CREATED_AT = "created_at";
    const UPDATED_AT = "updated_at";

    //      1,000,000,000,000,000;
    const TERA = 1000000000000000;

    /**
     * 現在の総ニジポを取得
     * @param $users_number
     * @return array
     */
    public function getAssets($users_number)
    {
        $assets = $this->execSql('SELECT * FROM ' . self::TABLE . ' WHERE users_number = ?', array($users_number), true);

        if (!$assets) {
            return false;
        }

        $teraPoint = 0;
        $point = 0;
        foreach ($assets as $k => $asset) {
            $teraPoint += $asset[self::TERA_POINT];
            $point += $asset[self::POINT];
        }

        return array(
            self::TERA_POINT => $teraPoint,
            self::POINT => $point,
        );
    }

    /**
     * ニジルポイント加算
     * @param $add
     * @param $users_number
     * @return array|bool|string
     */
    public function addPoint($add, $users_number)
    {
        $teraPoint = 0;
        $point = 0;

        // 現在の加算対象レコード
        $assets = $this->execSql('SELECT * FROM ' . self::TABLE . ' WHERE users_number = ? and is_full = 0', array($users_number));

        // レコードが無ければ一旦値0で新規追加
        if (!$assets) {
            $sql = 'insert into ' . self::TABLE . '(users_number, point, tera_point, is_full, created_at) values (?, ?, ?, 0, now())';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array($users_number, 0, 0));
        } else {
            // 現在の量
            $teraPoint = $assets[self::TERA_POINT];
            $point = $assets[self::POINT];
        }
        
        // 合算値が1テラを超える場合
        if ($point + $add >= self::TERA) {

            // 合算値に含まれるテラニジポ量を算出 = テラニジポ追加量
            $addTera = floor(($point + $add) / self::TERA);

            // 合算値をテラで割った余りを算出 = ニジポ追加量
            $add = ($point + $add) % self::TERA;

            // テラニジポ合計値も1テラテラニジポを超える場合
            if ($teraPoint + $addTera >= self::TERA) {

                // 現在使用しているレコードをテラテラニジポで満タンにしてロックする
                $this->execUpdate('UPDATE ' . self::TABLE . ' SET point = 0, tera_point = ?, is_full = 1 WHERE users_number = ? and is_full = 0', array(
                    self::TERA,
                    $users_number,
                ));

                // 満タンにした残りのテラニジポを新規レコードへ
                $addTera = $addTera + ($teraPoint - self::TERA);

                // 新規レコードの追加
                $sql = 'insert into ' . self::TABLE . '(users_number, point, tera_point, is_full, created_at) values (?, ?, ?, 0, now())';
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute(array($users_number, $point, $addTera));
            }

            // 1テラ繰り上げて保存する
            return $this->execUpdate('UPDATE ' . self::TABLE . ' SET point = ?, tera_point = tera_point + ? WHERE users_number = ? and is_full = 0', array(
                $add,
                $addTera,
                $users_number,
            ));
        }

        // 通常のニジポ追加
        return $this->execUpdate('UPDATE ' . self::TABLE . ' SET point = point + ? WHERE users_number = ? and is_full = 0', array(
            $add,
            $users_number,
        ));
    }

    /**
     * ニジルポイント減算
     * @param $del
     * @param $users_number
     * @return array|bool|string
     */
    public function delPoint($del, $users_number)
    {
        $teraLimit = 10000;

        // 現在の減算対象レコード
        $assets = $this->execSql('SELECT * FROM ' . self::TABLE . ' WHERE users_number = ? and is_full = 0', array($users_number));

        // レコードが無ければエラー
        if (!$assets) {
            return false;
        }

        // 現在の量
        $teraPoint = $assets[self::TERA_POINT];
        $point = $assets[self::POINT];

        // 減算後のニジポが0を下回る場合
        if (($point - $del) < 0) {

            $delTera = 0;

            // テラニジポを保有している場合
            if ($teraPoint > 0) {

                // 1テラニジポずつ借りてきて、引ける様になったらbreak
                for ($i = 1; $i <= $teraLimit; $i++) {
                    if ((self::TERA * $i) + $point >= $del) {
                        $del -= (self::TERA * $i);
                        $delTera = $i;
                        break;
                    }
                }

                // 借りる処理に失敗した場合エラー終了
                if (!$delTera) {
                    return false;
                }

                // TODO 現在の減算対象レコードに、減算後のニジポ/テラニジポを追加
                return $this->execUpdate('UPDATE ' . self::TABLE . ' SET point = point - ?, tera_point = tera_point - ? WHERE users_number = ? and is_full = 0', array(
                    $del,
                    $delTera,
                    $users_number,
                ));

            }else{

                // ロック済みテラテラニジポレコードの存在チェック
                $fullAssets = $this->execSql('SELECT * FROM ' . self::TABLE . ' WHERE users_number = ? and is_full = 1', array($users_number));

                // TODO もしロック済みテラテラニジポレコードがある場合、そちらを試してみる
                if (!empty($fullAssets)) {
                    $teraPoint = $fullAssets[self::TERA_POINT];
                    $fullAssetsId = $fullAssets[self::ID];

                    // 1テラニジポずつ借りてきて、引ける様になったらbreak
                    for ($i = 1; $i <= $teraLimit; $i++) {
                        if ((self::TERA * $i) + $point >= $del) {
                            $del -= (self::TERA * $i);
                            $delTera = $i;
                            break;
                        }
                    }

                    // 借りる処理に失敗した場合エラー終了
                    if (!$delTera) {
                        return false;
                    }

                    // 該当ロック済みテラテラレコードを削除
                    $this->execSql('DELETE FROM ' . self::TABLE . ' WHERE id = ?', array($fullAssetsId));

                    // 現在の減算対象レコードからニジポを減算 + 減算後ののテラニジポを追加
                    $newTeraPoint = $teraPoint - $delTera;
                    return $this->execUpdate('UPDATE ' . self::TABLE . ' SET point = point - ?, tera_point = ? WHERE users_number = ? and is_full = 0', array(
                        $del,
                        $newTeraPoint,
                        $users_number,
                    ));
                }

                // 借りるのも出来ないのでエラー終了
                return false;
            }
        }

        // 通常のニジポ減算
        return $this->execUpdate('UPDATE ' . self::TABLE . ' SET point = point - ? WHERE users_number = ? and is_full = 0', array(
            $del,
            $users_number,
        ));
    }

}