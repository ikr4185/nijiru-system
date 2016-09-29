<?php
namespace Models\Commons;
use Cores\Abstracts\SingletonAbstract;
use PDO;

/**
 * Class Database
 * DB接続
 * singleton / Database::getInstance()
 */
class Database extends SingletonAbstract {
	
	private $pdo;
	
	/**
	 * MySQL用に文字列をエスケープ
	 * @param $string
	 * @return string
	 */
	public function validate( $string ) {
		return "'" . htmlspecialchars($string) . "'";
	}
	
	/**
	 * PDOインスタンス取得
	 * @param $dsn
	 * @param $user
	 * @param $password
	 */
	public function getPdo($dsn, $user, $password) {
		
		$this->pdo = new PDO($dsn, $user, $password);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		if ($this->pdo == null) {
			die("接続に失敗しました。");
		}
		
	}

	/**
	 * 文字列ハッシュ化
	 * @param $string
	 * @param int $cost
	 * @return string
	 */
	public function convertHash( $string, $cost = 4 ) {

		$char = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		$salt = "";

		for($i = 0; $i < 22; $i++){
			$r = mt_rand(0, strlen($char) - 1);
			$salt .= substr($char, $r, 1);
		}

		if($cost < 4){
			$cost = 4;
		}else if($cost > 31){
			$cost = 31;
		}

		return crypt( $string, "$2y$".sprintf("%02d", $cost)."$" . $salt );
	}
	
	/**
	 * ハッシュ照合
	 * @param $string
	 * @param $hashedString
	 * @return bool
	 */
	public function checkHash($string, $hashedString){
		return crypt($string, $hashedString) == $hashedString;
	}


	/**
	 * SQL文実行処理ラッパー
	 * @param $sql
	 * @param $executeArray
	 * @param bool $isFetchAll
	 * @return array|string
	 */
	public function execSql( $sql, $executeArray, $isFetchAll=false ){
		
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute( $executeArray );
		
		if ($isFetchAll) {
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}else{
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		
		if(!$result){
			return 'execSql Error';
		}
		return $result;
	}
	
}