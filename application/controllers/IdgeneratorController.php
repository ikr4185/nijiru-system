<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
use Logics\IdGeneratorLogic;
use Inputs\BasicInput;


/**
 * Class IdgeneratorController
 * @package Controllers
 */
class IdgeneratorController extends AbstractController {
	
	/**
	 * @var IdGeneratorLogic
	 */
	protected $logic;
	
	/**
	 * @var BasicInput
	 */
	protected $input;
	
	protected function getLogic() {
		$this->logic = new IdGeneratorLogic();
	}
	
	protected function getInput() {
		$this->input = new BasicInput();
	}
	
	/**
	 * $_SESSION["id_data"] に、POSTされた情報を格納する
	 * @return array
	 */
	protected function getPostIdData() {
		$id_data = array(
			'staff'     =>  $this->input->getRequest("staff"),
			'name'      =>  $this->input->getRequest("name"),
			'idnum'     =>  $this->input->getRequest("idnum"),
			'scl'       =>  $this->input->getRequest("scl"),
			'duty'      =>  $this->input->getRequest("duty"),
			'locate'    =>  $this->input->getRequest("locate"),
		);
		return $id_data;
	}
	
	public function indexAction() {
		
		// セッション取得
		$id = $this->input->getSession("id");
		$idData = $this->input->getSession("id_data");

		// 値設定
		$idData = $this->logic->initForm($id, $idData);

		// POSTを受け取ったら各種処理開始
		if ( $this->input->isPost() ) {
			
			$id_data = $this->getPostIdData();
			$this->input->setSession("id_data", $id_data);
			
			// 画像生成
			if ( $this->input->checkRequest("generate") ) {
				$this->logic->createImg($id_data);
			}
			
			// 入力データ保存
			if ( $this->input->checkRequest("save") ) {
				$this->logic->saveData($id, $id_data);
			}
			
			$this->logic->createImg($id_data);
		}
		
		$result = array(
			"filePath"  => $this->logic->getImg(),   // 画像パス
			"id_data"   => $this->logic->initForm($id, $idData), // フォーム値
			"msg"   => $this->logic->getMsg(),
		);
		
		$this->getView( "index", "SCP財団ID証 ジェネレーター", $result );
		
	}
	
}