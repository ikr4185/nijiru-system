<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\IrcLogic;
use Logics\Irc81Logic;
use Inputs\BasicInput;


/**
 * Class IrcController
 * @package Controllers
 */
class IrcController extends AbstractController {
	
	/**
	 * @var IrcLogic
	 */
	protected $IrcLogic;
		/**
	 * @var Irc81Logic
	 */
	protected $Irc81Logic;
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->IrcLogic = new IrcLogic();
		$this->Irc81Logic = new Irc81Logic();
	}

	protected function getInput() {
		$this->input = new BasicInput();
	}
	
	public function indexAction() {
		
		// ログの日付リストを生成
		$logArray = $this->IrcLogic->getIrcLogArray();

		$result = array(
			"logs"  => $logArray,
			"msg"   => $this->IrcLogic->getMsg(),
		);
		$this->getView( "index", "IRC-Reader", $result );
		
	}
	
	public function logAction($date) {

		// バリデーション
		$this->IrcLogic->validateDate($date);

		// 記事読み込み +  パース
		$html = $this->IrcLogic->getLog( $date );

		// 日付の調整
		$timestamp = strtotime($date);
		$before_date = ( "2014-06-28" == $date ) ? null : date( 'Y-m-d', strtotime('-1 day', $timestamp) );
		$after_date = ( date('Y-m-d') == $date ) ? null : date( 'Y-m-d', strtotime('+1 day', $timestamp) );
		
		$result = array(
			"html"  => $html,
			"logsLink"  =>  "/irc",
			"date"  => $date,
			"before_date"  => $before_date,
			"after_date"  => $after_date,
			"msg"   => $this->IrcLogic->getMsg(),
		);
		$this->getView( "log", "IRC-Reader", $result );
	}
	
	public function logs81Action() {
		
		// ログの日付リストを生成
		$logArray = $this->Irc81Logic->getIrcLog81Array();
		
		$result = array(
			"logs"  => $logArray,
			"msg"   => $this->IrcLogic->getMsg(),
		);
		$this->getView( "index_81", "IRC-Reader #site8181", $result );
		
	}
	
	public function log81Action($date) {

		// バリデーション
		$this->IrcLogic->validateDate($date);
		
		// DB読み込み
		$logs = $this->Irc81Logic->getLog81( $date );
		
		// 日付の調整
		$timestamp = strtotime($date);
		$before_date = ( "2014-06-28" == $date ) ? null : date( 'Y-m-d', strtotime('-1 day', $timestamp) );
		$after_date = ( date('Y-m-d') == $date ) ? null : date( 'Y-m-d', strtotime('+1 day', $timestamp) );
		
		$result = array(
			"logs"  => $logs,
			"logsLink"  =>  "/irc/logs81",
			"date"  => $date,
			"before_date"  => $before_date,
			"after_date"  => $after_date,
			"msg"   => $this->IrcLogic->getMsg(),
		);
		$this->getView( "log_81", "IRC-Reader #site8181", $result );
	}
	
}