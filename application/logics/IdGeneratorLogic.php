<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Models\UsersModel;
use Models\FoundationIdModel;

/**
 * Class IdGeneratorLogic
 * @package Logics
 */
class IdGeneratorLogic extends AbstractLogic {
	
	const BASE_IMG = "/views/assets/img/idgen/base-d.png";
	const TMP_DIR = "/views/assets/img/idgen/tmp";
	const FONT_TTF = "/views/assets/font/itc-bauhaus-lt-demi.ttf";
	
	private $appDir;    // /home/njr-sys/public_html/application
	private $tmpDir;    // /home/njr-sys/public_html/application/views/assets/img/idgen/tmp
	private $fileName; // ファイル名(時刻のタイムスタンプ)
	
	/**
	 * @var UsersModel
	 */
	protected $Users;
	
	/**
	 * @var FoundationIdModel
	 */
	protected $FoundationId;
	
	
	protected function getModel() {
		$this->Users = UsersModel::getInstance();
		$this->FoundationId = FoundationIdModel::getInstance();
	}
	
	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		
		parent::__construct();
		
		// 初期値設定
		$this->appDir = \Cores\Config\Config::load("dir.app");
		$this->tmpDir = $this->appDir . self::TMP_DIR; // /home/njr-sys/public_html/application/views/assets/img/idgen/tmp
		$this->fileName = time();

		// 60秒以上前のファイル名は削除対象
		$deleteFile = intval($this->fileName) - 60;

		//ディレクトリ存在確認
		if ( is_dir($this->tmpDir) ) {

			//ディレクトリハンドル設定
			if ( $dh = opendir($this->tmpDir) ) {

				//ディレクトリから全ファイル読み込み
				while ( ($file = readdir($dh)) !== false ) {

					//ファイル名マッチ
					if ( preg_match('/^(\d+?)(\.png)$/', $file, $existFileName) ) {
						
						//拡張子以外をint型で取得
						$existFileName = intval($existFileName[1]);
						
						//ファイル名が60秒より昔なら削除
						if ($existFileName < $deleteFile) {
							unlink( $this->tmpDir."/{$file}" );
						}
						
					}

				}
				closedir($dh);
			}
		}
		//メッセージ初期化
		$this->msg = "";
	}


	/**
	 * フォーム初期値の設定
	 * @param $id   $_SESSION["id"]
	 * @param $idData   $_SESSION["id_data"]
	 * @return array|mixed
	 */
	public function initForm($id, $idData) {

		// フォーム初期値の設定
		if ( empty($idData) ) {
			
			// データベース検索
			$idData = $this->FoundationId->getIdgenData($id);

			// データベースに情報が無いなら、フォームに初期値を入れる
			if( !$idData ){
				$idData = array(
					'staff'     =>  'Field Agent',
					'name'      =>  'Keiichiro Ikura',
					'idnum'     =>  'ikr_4185',
					'scl'       =>  '2',
					'duty'      =>  'information collection and intelligence',
					'locate'    =>  'ContainmentSite-8120'
				);
			}
			
		}

		return $idData;
	}
	
	/**
	 * 画像生成・保存
	 */
	public function createImg($id_data) {

		// POSTされた各情報を取得する
		$img = ImageCreateFromPNG( $this->appDir.self::BASE_IMG ); // /home/njr-sys/public_html/application/views/assets/img/idgen/base-d.png

		$name = $id_data["staff"] . ": " . $id_data["name"] . " / No. " . $id_data["idnum"];
		$scl = "Security Clearance Level: " . $id_data["scl"];
		$duty = "Duty: " . $id_data["duty"];
		$locate = "Location: " . $id_data["locate"];
		
		// エンコーディングコンバート
		$name = mb_convert_encoding($name, 'UTF-8', 'auto');
		$scl = mb_convert_encoding($scl, 'UTF-8', 'auto');
		$duty = mb_convert_encoding($duty, 'UTF-8', 'auto');
		$locate = mb_convert_encoding($locate, 'UTF-8', 'auto');
		
		// 色の設定
		$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);
		
		// テキストの書き込み
		$fontTtf = $this->appDir.self::FONT_TTF; // /home/njr-sys/public_html/application/views/assets/font/itc-bauhaus-lt-demi.ttf
		ImageTTFText($img, 34, 0, 35, 450, $black, $fontTtf, $name);
		ImageTTFText($img, 34, 0, 35, 500, $black, $fontTtf, $scl);
		ImageTTFText($img, 34, 0, 35, 550, $black, $fontTtf, $duty);
		ImageTTFText($img, 34, 0, 35, 600, $black, $fontTtf, $locate);
		
		// 画像生成・保存
		ImagePNG($img, $this->tmpDir."/{$this->fileName}.png");
		imagedestroy($img);
	}
	
	/**
	 * 保存された画像のパス取得
	 * @return string
	 */
	public function getImg() {
		
		// /home/njr-sys/public_html/application/views/assets/img/idgen/tmp/{$this->fileName}.png
		$path = $this->tmpDir."/{$this->fileName}.png";
		
		// パス取得
		if (file_exists($path)) {
			// http://njr-sys.net/application/views/assets/img/idgen/tmp/{$this->fileName}.png
			return \Cores\Config\Config::load("path.app").self::TMP_DIR."/{$this->fileName}.png";
		}
		
		// 画像が生成されていない場合、元画像を表示する
		// http://njr-sys.net/application/views/assets/img/idgen/base-d.png
		return \Cores\Config\Config::load("path.app")."/views/assets/img/idgen/base-d.png";
	}
	
	/**
	 * ユーザーのIDgenデータを書き込む
	 * @param $id
	 * @param $idData
	 * @return bool
	 */
	public function saveData($id, $idData)  {
			
		// バリデート
		$id     = htmlspecialchars($id);
		$staff  = htmlspecialchars($idData["staff"]);
		$name   = htmlspecialchars($idData["name"]);
		$idnum  = htmlspecialchars($idData["idnum"]);
		$scl    = htmlspecialchars($idData["scl"]);
		$duty   = htmlspecialchars($idData["duty"]);
		$locate = htmlspecialchars($idData["locate"]);
		
		// 更新日時取得
		$date = date("Y-m-d H:i:s");
		
		// foundation_idテーブルに、データがあるかチェック
		$foundation_number = $this->FoundationId->checkIdgenData($id);
		
		// 新規保存の場合
		if ( $foundation_number === false ) {
			
			// ユニークナンバーを取得
			$number = $this->Users->getNumberById($id);
			
			// ユニークID取得失敗
			if(false === $number){
				$this->setError("ユーザーナンバー取得失敗");
				return false;
			}
			
			// インサート
			if ( !$this->FoundationId->insertRecord( $number["number"], $name, $staff, $idnum, $scl, $duty, $locate, $date ) ) {
				$this->setError("書き込み失敗");
				return false;
			}
		}
		
		// 上書き保存の場合
		if (!$this->FoundationId->updateRecord( $name, $staff, $idnum, $scl, $duty, $locate, $date, $id )) {
			$this->setError("書き込み失敗");
			return false;
		}

		$this->setMsg("保存しました");
		return true;
	}
	
}