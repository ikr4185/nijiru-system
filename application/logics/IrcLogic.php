<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use \Cores\Config\Config;

/**
 * Class IrcLogic
 * @package Logics
 */
class IrcLogic extends AbstractLogic {

	protected $cliDir;

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();

		// 初期設定
		$this->cliDir = Config::load("dir.cli");
	}

	protected function getModel() {
	}
	
	/**
	 * 引数の日付のバリデーション
	 * @param $date
	 */
	public function validateDate( $date ) {
		if( !preg_match( '/(\d{4})-(\d{2})-(\d{2})/', $date ) ) die("Error: Invalid URL");
		$dateArray = explode("-",$date);
		if(!checkdate($dateArray[1],$dateArray[2],$dateArray[0])) die("Error: Invalid Date");
	}

	/**
	 * IRCのログ記録が始まった時からの日付+記事数の配列を返す
	 * @return array
	 */
	public function getIrcLogArray() {

		// irc-logs_sizes.dat をチェックして配列に格納
		$logArray = $this->getSizes();

		// 発言数バーを格納
		$logArray = $this->renderBar($logArray);

		// 当日の分を追加する( TODO 暫定対応 )
		$logArray[] = array(
			date('Y-m-d', time() ),
			null,
			"集計中"
		);

		return array_reverse($logArray);
	}

	/**
	 * irc-logs_sizes.dat を読み込んで配列を返す
	 */
	protected function getSizes() {

		$rawData = file( $this->cliDir."/logs/irc/irc-logs_sizes.dat" );

		$dataArray = array();
		foreach ( $rawData as $key=>$data) {
			$dataArray[$key] = explode("\t", $data);
			$dataArray[$key][2] = trim($dataArray[$key][1]); // 改行が入っちゃうのでトリム
		}

		return $dataArray;
	}

	/**
	 * @param $logArray
	 * @return mixed
	 */
	protected function renderBar($logArray) {

		// 最大発言数
		$max = $this->getMax( $logArray );

		// バーの一単位あたりの発言数
		$unit = floor( $max / 100 );

		// ゼロ除算阻止
		if ($unit === 0) {
			$unit = 1;
		}

		// ログ配列でループ
		foreach ($logArray as &$log) {

			// その日のログ発言数は、何単位分か計算
			$bar_count = floor( intval(trim($log[1])) / $unit );

			// 単位数分だけ、█を追加
			$log[2] = '<div style="background:#333;width:'.$bar_count.'%;color:#888;padding:0 5px;">'.$log[1].'</div>';
		}
		unset($log);

		return $logArray;
	}

	/**
	 * ログの投稿数について最大数を求める
	 * @param $logArray
	 * @return mixed
	 */
	protected function getMax( $logArray ) {

		$sizes = array();
		foreach ($logArray as $log) {
			$sizes[] = intval(trim($log[1]));
		}
		return max($sizes);
	}

	// ログ内容を出力
	public function getLog( $date )	{

		$html = "";

		// 記事読み込み
		$fp = @fopen( $this->cliDir."/logs/irc_old/irc-logs_{$date}.log", 'r' );
		if (!$fp) {
			$fp = fopen( $this->cliDir."/logs/irc/irc-logs_{$date}.dat", 'r' );
		}
		if (!$fp) {
			die('ファイルが存在しません');
		}

		$i=0;
		if (flock($fp, LOCK_SH)){

			while (!feof($fp)) {

				// 一行ずつ読み込んで$htmlに格納
				$buffer = fgets($fp);
				$buffer = htmlspecialchars($buffer);

				// パース処理
				$buffer = $this->pearseLog( $buffer, $i );
				$i++;

				// $html 追記
				$html .= $buffer;
			}
			flock($fp, LOCK_UN);

		}else{
			print('ファイルロックに失敗しました');
		}

		fclose($fp);
		return $html;
	}

	/**
	 * ログデータのパース
	 * @param $html
	 * @param $i
	 * @return mixed
	 * @TODO 一行ずつこれを回すのでめっちゃ重い → かしまちゃんにやらせる？
	 */
	public function pearseLog( $html, $i )
	{
		// 改行を<br>に
		$html = str_replace( "\n", "<br>", $html );

		// 名前から配色を取得
		preg_match('/( - \()(.*?)(\) - )/', $html, $matches);

		// 配色を設定
		$color="";
		if (isset($matches[2])) {
			$color = $this->getColor( $matches[2] );

			// bot
			if ( "KASHIMA-EXE" == $matches[2] ) {
				$color = "KASHIMA-EXE";
			}

			// オペレータ
			if ( "Holy_nova" == $matches[2] || "kasyu-maki" == $matches[2] ) {
				$color = "irc-color-op";
			}
		}

		// botのワードを抽出
		if (
			false!==strpos($html,'[SCP-JP]')
			||  false!==strpos($html,'[SCP]')
			||  false!==strpos($html,'[WIKI]')
			||  false!==strpos($html,'[SANDBOX]')
			|| false!==strpos($html,'起動しました')
			|| false!==strpos($html,'さんが入室しました')
			|| false!==strpos($html,'さんが退室しました')
		) {
			$html = "<tr id=\"js_irc_log_{$i}\" class=\"js_irc_log irc-bot\"><td class=\"nowrap\">".$html."</td></tr>\n";
		}else{
			$html = "<tr id=\"js_irc_log_{$i}\" class=\"js_irc_log\"><td class=\"nowrap\">".$html."</td></tr>\n";
		}

		// なまえ
		$html = preg_replace('/( - \()(.*?)(\) - )/', "<td class=\"nowrap\"><span class=\"b {$color}\">&lt;$2&gt;</span></td><td class=\"wrap irc-table__message\">\t", $html);

		// URLリンク生成
		$html = preg_replace('/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w-.\/?%&=]*)?/', "<a href=\"$0\">$0</a>", $html);

		// 年月日を削除
		$html = preg_replace('/(\d{4})-(\d{2})-(\d{2}) /', "", $html);

		return $html;
	}

	/**
	 * 名前を読み込んで適当な配色のクラス名を返す
	 * @param $name
	 * @return string
	 */
	protected function getColor( $name ){
		$head = mb_strtolower(substr( $name, 0, 1));
		return "irc-color-{$head}";
	}
}