<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Models\StorageModel;
use Models\UsersModel;

/**
 * Class StorageLogic
 * @package Logics
 */
class StorageLogic extends AbstractLogic {

	const STORAGE_PATH = "/home/njr-sys/public_html/storage/";

	/**
	 * @var StorageModel
	 */
	protected  $Storage = null;

	/**
	 * @var UsersModel
	 */
	protected $Users;

	protected function getModel() {
		$this->Storage = StorageModel::getInstance();
		$this->Users = UsersModel::getInstance();
	}

	/**
	 * ファイル情報の取得
	 * @param null $id
	 * @return array|string
	 */
	public function getFileInfo( $id=null ) {

		// DBから情報を取得
		if ($id==null) {
			$records = $this->Storage->getFileInfoAll();
		}else{
			$records = $this->Storage->getFileInfo($id);
		}

		// 情報の処理
		foreach ($records as &$record) {
			$record["user_name"] = $this->Users->getIdByNumber($record["user_id"]);
			$record["url"] = str_replace("/home/njr-sys/public_html", "", $record["file_path"]);
		}
		unset($record);

		return $records;
	}

	/**
	 * ファイルアップロード処理
	 * @param $tmpFileArray
	 * @param $user_id
	 * @param $credit
	 * @return bool
	 */
	public function upload($tmpFileArray, $user_id, $credit) {

		if ( empty($credit) ) {
			$this->setError("CC BY-SA 3.0に基づくクレジット名を入力してください");
			return false;
		}

		if ( is_uploaded_file($tmpFileArray["tmp_name"]) ) {

			// ファイル存在可否のチェック
			$file_name = $tmpFileArray["name"];
			if ( $this->Storage->checkFileInfo($tmpFileArray["name"]) ) {
				// リネーム
				$file_name = date("Ymd_His")."_".$tmpFileArray["name"];
			}

			// パス
			$date = date("Y-m-d");
			$dir_path = $this::STORAGE_PATH . $date;
			$file_path = $dir_path . "/" . $file_name;

			// ディレクトリチェック
			if(!file_exists($dir_path)){

				if(mkdir($dir_path, 0777)){
					chmod($dir_path, 0777);
				}else{
					$this->setError("ディレクトリ作成に失敗しました");
					return false;
				}
			}

			// ファイル保存 + DBインサート
			if ( move_uploaded_file( $tmpFileArray["tmp_name"], $file_path ) ) {
				chmod( $file_path, 0644);
				$this->Storage->saveFileInfo( $file_name, $file_path, $user_id, $credit, $tmpFileArray["size"] );

				$this->setMsg( $file_name . "をアップロードしました") ;
				return true;
			}

			$this->setError("ファイルアップロードに失敗しました");
			return false;

		}

		$this->setError("ファイルが選択されていません");
		return false;

	}
	
	public function softDelete( $file_id ) {
		return $this->Storage->setSoftDelete(true,$file_id);
	}

	public function outimg() {
		/**
		 * http://sterfield.co.jp/designer/php%E3%82%92%E6%89%8B%E8%BB%BD%E3%81%AB%E4%BD%BF%E3%81%A3%E3%81%A6%E3%80%81%E7%94%BB%E5%83%8F%E3%81%AE%E3%82%B5%E3%83%A0%E3%83%8D%E3%82%A4%E3%83%AB%E3%82%92%E5%87%BA%E5%8A%9B%E3%81%99%E3%82%8B/
		 */
		header('Content-type: image/jpeg');

		$image_file = "/home/njr-sys/public_html/application/views/assets/img/common/nijiru-icon.png";
		if (!empty($_GET['url'])) {
			$image_file = $_GET['url'];
		}

		$new_width = 100;
		if (!empty($_GET['width'])) {
			$new_width = $_GET['width'];
		}

		// 元画像のファイルサイズを取得
		list($original_width, $original_height) = getimagesize($image_file);

		//元画像の比率を計算し、高さを設定
		$proportion = $original_width / $original_height;
		$new_height = $new_width / $proportion;

		//高さが幅より大きい場合は、高さを幅に合わせ、横幅を縮小
		if($proportion < 1){
			$new_height = $new_width;
			$new_width = $new_width * $proportion;
		}

		$file_type = strtolower(end(explode('.', $image_file)));

		if ($file_type === "jpg" || $file_type === "jpeg") {

			$original_image = \ImageCreateFromJPEG($image_file); //JPEGファイルを読み込む
			$new_image = \ImageCreateTrueColor($new_width, $new_height); // 画像作成

		} elseif ($file_type === "gif") {

			$original_image = \ImageCreateFromGIF($image_file); //GIFファイルを読み込む
			$new_image = \ImageCreateTrueColor($new_width, $new_height); // 画像作成

			/* ----- 透過問題解決 ------ */
			$alpha = imagecolortransparent($original_image);  // 元画像から透過色を取得する
			imagefill($new_image, 0, 0, $alpha);       // その色でキャンバスを塗りつぶす
			imagecolortransparent($new_image, $alpha); // 塗りつぶした色を透過色として指定する

		} elseif ($file_type === "png") {

			$original_image = \ImageCreateFromPNG($image_file); //PNGファイルを読み込む
			$new_image = \ImageCreateTrueColor($new_width, $new_height); // 画像作成

			/* ----- 透過問題解決 ------ */
			imagealphablending($new_image, false);  // アルファブレンディングをoffにする
			imagesavealpha($new_image, true);       // 完全なアルファチャネル情報を保存するフラグをonにする

		} else {
			// 何も当てはまらなかった場合の処理は書いてませんので注意！
			return;

		}

		// 元画像から再サンプリング
		\ImageCopyResampled($new_image,$original_image,0,0,0,0,$new_width,$new_height,$original_width,$original_height);

		// 画像をブラウザに表示
		if ($file_type === "jpg" || $file_type === "jpeg") {
			\ImageJPEG($new_image);
		} elseif ($file_type === "gif") {
			\ImageGIF($new_image);
		} elseif ($file_type === "png") {
			\ImagePNG($new_image);
		}

		// メモリを開放する
		imagedestroy($new_image);
		imagedestroy($original_image);
	}

}