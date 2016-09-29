<?php
namespace Models;
use Models\Commons\AbstractModel;

class FavoriteScpModel extends AbstractModel
{
	
	/**
	 * お気に入り登録のチェック
	 * @param $user_id string "ikr_4185"
	 * @param $itemNumber int "040"
	 * @return int
	 */
	public function checkFavoriteScp( $user_id, $itemNumber ){

		$count = $this->execSql(
			'SELECT COUNT(*) as is_favorite
FROM favorite_scp as a
INNER JOIN users as b ON b.number = a.users_number
WHERE b.id = ?
AND a.item_number = ?',
			array( $user_id, $itemNumber)
		);

		return $count["is_favorite"];
	}

	/**
	 * お気に入り登録済みかのチェック
	 * @param $user_id string "ikr_4185"
	 * @param $itemNumber int "040"
	 * @return int
	 */
	public function checkEnableFavoriteScp( $user_id, $itemNumber ){

		$count = $this->execSql(
			'SELECT COUNT(*) as is_favorite
FROM favorite_scp as a
INNER JOIN users as b ON b.number = a.users_number
WHERE b.id = ?
AND a.item_number = ?
AND a.is_enable = 1',
			array( $user_id, $itemNumber)
		);

		return $count["is_favorite"];
	}

	/**
	 * お気に入りデータの修正
	 * @param $is_enable
	 * @param $users_number
	 * @param $itemNumber
	 * @return bool|string
	 */
	public function updateFavoriteScp( $is_enable, $users_number, $itemNumber ){

		return  $this->execUpdate(
			'UPDATE favorite_scp
SET is_enable = ?, modified_date = CURRENT_TIMESTAMP
WHERE users_number = ?
AND item_number = ?',
			array( $is_enable, $users_number, $itemNumber )
		);
	}


	/**
	 * お気に入りデータの追加
	 * @param $users_number
	 * @param $itemNumber int "040"
	 * @param $is_enable int 1/0
	 * @return string
	 */
	public function insertFavoriteScp( $users_number, $itemNumber, $is_enable=1 ) {
		
		// SQL
		$sql = 'INSERT INTO favorite_scp( users_number, item_number, is_enable, modified_date ) VALUES (?, ?, ?, CURRENT_TIMESTAMP)';
		$stmt = $this->pdo->prepare($sql);

		return $stmt->execute(array( $users_number, $itemNumber, $is_enable ));
	}
	
	/**
	 * お気に入り情報の読み込み
	 * @param $users_number
	 * @return array
	 */
	public function selectFavoriteScp( $users_number ) {
		
		return $this->execSql(
			'SELECT item_number, modified_date
FROM favorite_scp
WHERE users_number = ?
AND is_enable = 1
ORDER BY modified_date DESC',
			array( $users_number ),
			true
		);
	}
}