<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Models\LowVoteLogModel;
use Models\UsersModel;
use Models\AdminLvcUsersModel;

/**
 * Class ContactLogic
 * @package Logics
 */
class AdminLogic extends AbstractLogic {
	
	/**
	 * @var AdminLvcUsersModel
	 */
	protected $AdminLvcUsers;
	
	/**
	 * @var LowVoteLogModel
	 */
	protected $LowVoteLog;
	
	/**
	 * @var string
	 */
	protected $msg = "";
	
	protected function getModel() {
//		$this->User = UsersModel::getInstance();
		$this->AdminLvcUsers = AdminLvcUsersModel::getInstance();
		$this->LowVoteLog =  LowVoteLogModel::getInstance();
	}
	
	// ==========================================================================================
	// LowVotes
	// ==========================================================================================
	
	/**
	 * DBのデータを表示させる
	 * @return mixed
	 */
	public function showLowVote() {
		$delInfoArray = $this->LowVoteLog->getAllLowVotes();
		return $delInfoArray;
	}
	
	/**
	 * IDからDB検索
	 * @param $low_votes_number
	 * @return mixed
	 */
	public function searchLowVoteById($low_votes_number)
	{
		$record = $this->LowVoteLog->searchLowVotesById($low_votes_number);
		return $record;
	}
	
	/**
	 * DBのデータをソフトデリート
	 * @param $low_votes_number
	 */
	public function delLowVote( $low_votes_number )
	{
		$post = $this->LowVoteLog->searchLowVotesById($low_votes_number);
		$this->LowVoteLog->setSoftDeleteLowVotes( 1, $post["url"] );
		$this->setMsg( $post["name"]." を削除しました。" );
	}
	
	// ==========================================================================================
	// LvcUsers
	// ==========================================================================================
	
	/**
	 * LVC 配送先リストの表示
	 * @return mixed
	 */
	public function showLvcUsers() {
		$delInfoArray = $this->AdminLvcUsers->getAvailable();
		return $delInfoArray;
	}
	
	/**
	 * LVC 配送先リストの追加
	 * @param $name
	 * @param $mail
	 * @return bool
	 */
	public function registerLvcUsers($name,$mail) {

		// 登録状況の確認
		$lvcUser = $this->AdminLvcUsers->searchRecordByMail($mail);

		// 登録済み
		if ($lvcUser) {

			// 非アクティブなユーザーの差し戻し
			if ( $lvcUser["is_available"] == 0 ) {
				$this->setMsg( $lvcUser["name"]."を再度有効化しました" );
				return $this->AdminLvcUsers->softDelete( $lvcUser["id"], 1 );
			}

			// 有効なアカウントならfalse
			$this->setMsg( $lvcUser["name"]."として既に登録済みです" );
			return false;

		}

		// 新規登録
		$this->setMsg( $name."を登録しました" );
		return $this->AdminLvcUsers->register($name,$mail);
	}

	/**
	 * LVC 配送先の削除
	 * @param $id
	 * @return array|bool|string
	 */
	public function deleteLvcUsers($id) {

		// idが存在しない時はエラー
		$lvcUser = $this->AdminLvcUsers->searchRecordById($id);
		if ( !$lvcUser ) {
			$this->setError( "検索エラー" );
			return false;
		}
		
		if (!$this->AdminLvcUsers->softDelete( $id )) {
			$this->setError( "削除エラー" );
			return false;
		}
		
		$this->setMsg( $lvcUser["name"]."を削除しました" );
		return true;
	}
	
	// ==========================================================================================
	// Kashima
	// ==========================================================================================
	
	/**
	 * カシマ起動
	 */
	public function kashimaStart(){
		exec('sh /home/njr-sys/public_html/cli/sh/kashima_start.sh');
		$this->setMsg( "KASHIMA-EXE 起動しました" );
	}
	
//	public function kashimaStop(){
//		exec('sh /home/njr-sys/public_html/cli/sh/kashima_stop.sh');
//		$this->setMsg( "KASHIMA-EXE 停止しました" );
//	}
//	
//	public function kashimaReboot(){
//		exec('sh /home/njr-sys/public_html/cli/sh/kashima_reboot.sh');
//		$this->setMsg( "KASHIMA-EXE 再起動しました" );
//	}
	
	/**
	 * カシマ実行状況の取得
	 * @return array
	 */
	public function getKashimaStatus(){
		$kashimaStatus = array();
		$kashimaStatus[] = $this->convertPsAux(exec('ps aux | grep KASHIMA-EXE2.php | grep -v grep | grep -v kashima_check.sh'));
		$kashimaStatus[] = $this->convertPsAux(exec('ps aux | grep KASHIMA-EXE-site8181.php | grep -v grep | grep -v kashima_check.sh'));
		
		return $kashimaStatus;
	}
	
	/**
	 * カシマメモリ使用状況ログ
	 * @return mixed
	 */
	public function getMemoryUsedLog() {
		return file_get_contents("/home/njr-sys/public_html/cli/logs/KASHIMA_memory_used.log");
		
	}
	
	private function convertPsAux( $str ) {
		return $str;
//		return preg_replace('(\s+)', "</td><td>",$str);
	}
	
	/**
	 * カシマ停止パスの取得
	 * @return mixed
	 */
	public function getQuitPass(){
		return file_get_contents("/home/njr-sys/public_html/cli/logs/KASHIMA_quit.log");
	}
	
}