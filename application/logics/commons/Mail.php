<?php
namespace Logics\Commons;

mb_internal_encoding("UTF-8");

/**
 * テンプレートに対応したメール送信クラス
 * @see http://tilfin.hatenablog.com/entry/20080714/1216108930
 */
class Mail {
	
	protected $content;
	protected $lang;
	protected $subject;
	protected $options;
	
	/**
	　 * コンストラクタ
	 * @access    public
	 * @param     String    $tplfile    テンプレートファイルのパス
	 * @param     String    $lang       メール送信時の言語指定
	 */
	public function __construct($tplfile, $lang = "ja") {
		$this->content = file_get_contents($tplfile);
		$this->lang = $lang;
	}
	
	protected function replace_options($matches) {
		if (array_key_exists($matches[1], $this->options)) {
			return $this->options[$matches[1]];
		} else {
			return "";
		}
	}
	
	protected function extract_subject($matches) {
		$this->subject = trim($matches[1]);
		return "";
	}
	
	/**
	　 * テンプレートにマップの値をセットしてメールを指定された先に送信します。
	 * @access    public
	 * @param     String    $to    メール送信先
	 * @param     array     $opts  テンプレートに当てはまるマップ
	 * @return    bool      送信結果
	 */
	public function send($to, $opts) {
		$this->options = $opts;
		
		// テンプレートにマップの値をセット
		$content = preg_replace_callback('/\{\$([a-z0-9]+)\}/',
			array($this, "replace_options"), $this->content);
		
		// メールのヘッダとボディを切り分ける
		list($headers, $body) = preg_split("/\n\n/", $content, 2);
		
		// ヘッダから件名を抜き取る
		$headers = preg_replace_callback('/Subject\:(.*)/',
			array($this, "extract_subject"), $headers);
		
		mb_language($this->lang);
		$result = mb_send_mail($to, $this->subject, $body, $headers);
		
		$this->options = NULL;
		$this->subject = NULL;
		
		return $result;
	}
}