<?php
namespace Models\Commons;
use Cores\Abstracts\SingletonAbstract;
use \PDO;
use Cores\Config\Config;

/**
 * Class AbstractModel
 * 抽象モデル
 * @package Models\Commons
 */
abstract class AbstractModel extends SingletonAbstract
{
	
	/**
	 * @var PDO
	 */
	protected $pdo;
	
	protected function __construct() {
		$this->getPdo(Config::load("db.dsn"),Config::load("db.user"),Config::load("db.pass"));
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
			return false;
		}
		return $result;
	}
	
	/**
	 * SQL文実行処理ラッパー(UPDATE)
	 * @param $sql
	 * @param $executeArray
	 * @return array|string
	 */
	public function execUpdate( $sql, $executeArray ){
		
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($executeArray);
	}
	
	// TODO ソフトデリート処理の抽象化
//	abstract protected function setSoftDelete( $del_flg, $id ); 
	
}