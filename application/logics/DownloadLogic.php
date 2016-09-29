<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Models\UsersModel;
use Models\DownloadModel;

/**
 * Class ContactLogic
 * @package Logics
 */
class DownloadLogic extends AbstractLogic {
	
	/**
	 * @var UsersModel
	 */
	protected $Users;
	/**
	 * @var DownloadModel
	 */
	protected $Download;
	
	protected function getModel() {
		$this->Users = UsersModel::getInstance();
		$this->Download = DownloadModel::getInstance();
	}
	
	/**
	 * 全レコードの取得
	 * @return mixed
	 */
	public function getAll() {
		return $this->Download->getAll();		
	}
	
	/**
	 * Idから情報取得
	 * @param $id
	 * @return mixed
	 */
	public function searchRecordById( $id ) {
		return $this->Download->searchRecordById($id);
	}

	/**
	 * 必要ポイントの減算
	 * @param $userId
	 * @param $downloadId
	 * @return bool
	 */
	public function consumePoint( $userId, $downloadId ) {

		$price = $this->getPrice($downloadId);
		$havingPoint = $this->Users->getPoint($userId);

		if ($price > $havingPoint) {
			return false;
		}
		return $this->Users->delPoint( $price,$userId );
	}

	/**
	 * 必要額の確認
	 * @param $downloadId
	 * @return mixed
	 */
	private function getPrice($downloadId) {
		$download = $this->Download->searchRecordById($downloadId);
		return $download["price"];
	}

	/**
	 * ファイルオープン
	 * @param $fileId
	 */
	public function downloadMethod($fileId)
	{

		$data = $this->searchRecordById($fileId);

		$fileDir = "/home/njr-sys/public_html/download/";

		// ファイル存在チェック
		if (!$this->isExist( $fileDir.$data["contents_path"] )) return;

		// オープンできるか確認
		if ( !$fp = $this->isOpenable( $fileDir.$data["contents_path"] ) ) return;
		fclose($fp);

		// ファイルサイズの確認
		if ( !$content_length = $this->fileSizeCheck( $fileDir.$data["contents_path"] ) ) return;

		/* ダウンロード用のHTTPヘッダ送信 */
		header("Content-Disposition: inline; filename=\"" . basename($fileDir.$data["contents_path"]) . "\"");
		header("Content-Length: " . $content_length);
		header("Content-Type: application/force-download");

		/* ファイルを読んで出力 */
		if (!$this->readFile( $fileDir.$data["contents_path"] )) return;

	}
	
	// ==========================================================================================
	
	/**
	 * ファイルの存在チェック
	 * @param $file_path
	 * @return bool
	 */
	public function isExist( $file_path ) {
		if (!file_exists($file_path)) {
			$this->setMsg("ファイルが存在しません");
			return false;
		}
		return true;
	}
	
	/**
	 * ファイルが開けるかチェック
	 * @param $file_path
	 * @return bool|resource
	 */
	public function isOpenable( $file_path ) {
		if (!($fp = fopen($file_path, "r"))) {
			$this->setMsg("ファイルが開けません");
			return false;
		}
		return $fp;
	}
	
	/**
	 * ファイルサイズチェック
	 * @param $file_path
	 * @return bool|int
	 */
	public function fileSizeCheck( $file_path ) {
		if (($content_length = filesize($file_path)) == 0) {
			$this->setMsg("ファイルは0バイトです");
			return false;
		}
		return $content_length;
	}
	
	/**
	 * ファイルを読んで出力
	 * @param $file_path
	 * @return bool|void
	 */
	public function readFile( $file_path ) {
		if (!readfile($file_path)) {
			if ( ($content_length = filesize($file_path)) == 0 ) {
				$this->setMsg("ファイルが読み込めませんでした");
				return false;
			}
		}
		return true;
	}
	
}