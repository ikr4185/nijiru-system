<?php
/**
 * php /home/njr-sys/public_html/cli/KASHIMA-test.php
 */

// debug //////////
echo "script start\n";
echo memory_get_usage(true) . "\n";

require_once('/home/njr-sys/public_html/lib/kashima-core/kashima-core.php');

// debug //////////
echo "kashima-core loaded\n";
echo memory_get_usage(true) . "\n";

define("LOG_PATH", "/home/njr-sys/public_html/cli/logs/irc/"); //ログの保存先
define("IRC_HOST", "irc.synirc.net"); //ホスト名
define("IRC_PORT", "6667"); //ポート番号

define("IRC_NAME", "KASHIMA-EXE-test"); // おなまえ
define("EXTENSION", "_test.dat"); // ログファイルの末尾

$irc_channel = "#ikr4185-elke-test"; // チャンネル名

$irc_pass = null;

class Kashima
{

	function welcome($irc, $data)
	{
		if ( IRC_NAME == $data->nick ) {
			$msg = IRC_NAME.' 起動しました';
		} elseif( "hal-aki" == $data->nick || "hal_aki" == $data->nick ) {

			$hariyamaArray = array(
				"貼山",
				"晴山",
				"春山",
				"梁山",
				"花山",
				"原山",
				"イエローストーン博士",
				"ハリー",
				"ニド",
				"板東英二",
				"堂本光一",
				"なんとか山",
				"音MAD素材博士",
				"名前を呼んではいけないあの人",
				"スレンディ",
				"字が下手",
				"毛虫ばらまきマン",
				"毛虫",
				"毛",
				"葉加瀬博士",
				"ハンス=ウルリッヒ=ルーデル博士"
			);
			$rand = mt_rand(0, count($hariyamaArray));
			$msg = $hariyamaArray[$rand].'さんが入室しました';

		}else{
			$msg = $data->nick.'さんが入室しました';
		}

		// 発言
		$this->botMsg( $irc, $data, $msg );
	}

	function bye($irc, $data)
	{
		if( "hal-aki" == $data->nick || "hal_aki" == $data->nick ) {

			$hariyamaArray = array(
				"貼山",
				"晴山",
				"春山",
				"梁山",
				"花山",
				"原山",
				"イエローストーン博士",
				"ハリー",
				"ニド",
				"板東英二",
				"堂本光一",
				"なんとか山",
				"音MAD素材博士",
				"名前を呼んではいけないあの人",
				"スレンディ",
				"字が下手",
				"毛虫ばらまきマン",
				"毛虫",
				"毛",
				"葉加瀬博士",
				"ハンス=ウルリッヒ=ルーデル博士"
			);
			$rand = mt_rand(0, count($hariyamaArray));
			$msg = $hariyamaArray[$rand].'さんが退室しました';

		}else{
			$msg = $data->nick.'さんが退室しました';
		}

		// 発言
		$this->botMsg( $irc, $data, $msg );
	}

	// 停止中
	function url($irc,$data)
	{
		preg_match_all('|http://\w+(?:-\w+)*(?:\.\w+(?:-\w+)*)+(?::\d+)?(?:[/\?][\w%&=~\-\+/;\.\?]*(?:#[^]*)?)?|', $data->message, $match);

		$url=$match[0][0];
		$urldata = file_get_contents($url);

		$urldata = mb_convert_encoding($urldata, "UTF-8", "auto" );
		preg_match( "/<title>(.*?)/i", $urldata, $matches);

		$msg = $matches[1] . ' - ' . $url;

		// 発言
		$this->botMsg( $irc, $data, $msg );
	}

	/**
	 * ログ保存
	 * @param $irc
	 * @param $data
	 */
	function getlog( $irc, $data )
	{
		$data->message;
		$file_name = "irc-logs_".date("Y-m-d").EXTENSION;

		$fp = fopen(LOG_PATH.$file_name, "a+");
		fwrite($fp, date("H:i:s") . ' - (' . $data->nick . ') - ' . "$data->message \n");
		fclose($fp);
	}

	/**
	 * ログ保存(入退室)
	 * @param $irc
	 * @param $data
	 */
	function getlog_inout( $irc, $data )
	{
		$data->message;
		$file_name = "irc-logs_".date("Y-m-d").EXTENSION;

		$fp = fopen(LOG_PATH.$file_name, "a+");
		fwrite($fp, date("H:i:s") . ' - (' . $data->nick . ') - ' . "$data->message \n");
		fclose($fp);
	}

	/**
	 * かしまちゃんに喋らせる
	 * @param $irc
	 * @param $data
	 * @param $msg
	 */
	private function botMsg( $irc, $data, $msg )
	{
		$irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, $msg);
		$this->saveKashimaLog( IRC_NAME, $msg );
	}

	/**
	 * かしまcの発言保存
	 * @param $nick
	 * @param $msg
	 */
	private function saveKashimaLog( $nick, $msg )
	{
		$file_name = "irc-logs_".date("Y-m-d").EXTENSION;

		$fp = fopen( LOG_PATH.$file_name, "a+" );
		fwrite( $fp, date("H:i:s") . ' - (' . $nick . ') - ' . "$msg \n" );
		fclose($fp);
	}

	/**
	 * こんちは
	 * @param $irc
	 * @param $data
	 */
	function hello($irc, $data)
	{
		$msg = 'こんにちは';

		// 発言
		$this->botMsg( $irc, $data, $msg );
	}

	/**
	 * SCP記事出力
	 * @param $irc
	 * @param $data
	 */
	function scp($irc, $data)
	{
		// めざせ
		// haruharu: http://scpjapan.wiki.fc2.com/wiki/SCP-173/ - 彫刻 - オリジナル

		preg_match('/^(\.SCP-)(\d*)$/i', $data->message, $match);
		// 再試行
		if (empty($match)) {
			preg_match('/^(\.SCP )(\d*)$/i', $data->message, $match);
		}
		if (empty($match)) {
			$irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 認識に失敗しました');
			return;
		}

		$url = "http://scpjapan.wiki.fc2.com/wiki/SCP-{$match[2]}/";
		$html = $this->curl( $url, "0-3200" );
		if (!$html) {
			$irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 接続に失敗しました');
		}

		preg_match( '@(<div><span style="font-weight: bold;">)(.*?)(</span></div>)@i', $html, $matches);
		unset($html); // メモリ節約

		$title = str_replace('<span style="font-style: italic;">','',$matches[2]);
		$title = str_replace('<span style="font-weight: bold;">','',$title);
		$title = str_replace('</span>','',$title);

		$msg = "{$data->nick}: [SCP] {$url} \" {$title} \"";

		// 発言
		$this->botMsg( $irc, $data, $msg );
	}

	/**
	 * SCP-JP記事出力
	 * @param $irc
	 * @param $data
	 */
	function scpjp($irc, $data)
	{
		// めざせ
		// haruharu: http://scpjapan.wiki.fc2.com/wiki/SCP-173/ - 彫刻 - オリジナル

		preg_match('/^(\.SCPJP-)(\d*)$/i', $data->message, $match);
		// 再試行
		if (empty($match)) {
			preg_match('/^(\.SCPJP )(\d*)$/i', $data->message, $match);
		}
		if (empty($match)) {
			$irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 認識に失敗しました');
			return;
		}

		$num = $match[2];
		$url = "http://ja.scp-wiki.net/scp-series-jp";
		$html = $this->curl( $url );
		if (!$html) {
			$irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 接続に失敗しました');
		}

		preg_match( "@(<li><a href=\"/scp-{$num}-jp\">SCP-{$num}-JP</a> - )(.*?)(</li>)@i", $html, $matches);
		unset($html); // メモリ節約

		$title = htmlspecialchars_decode($matches[2]);

		$msg = "{$data->nick}: [SCP-JP] http://ja.scp-wiki.net/scp-{$num}-jp SCP-{$num}-JP \"{$title}\"";

		// 発言
		$this->botMsg( $irc, $data, $msg );
	}

	/**
	 * Wikidot内マルチURL出力
	 * @param $irc
	 * @param $data
	 */
	function wiki($irc, $data)
	{
		// めざせ
		// haruharu: http://scpjapan.wiki.fc2.com/wiki/SCP-173/ - 彫刻 - オリジナル

		preg_match('/^(\.wiki )(.*)$/i', $data->message, $match);

		if (empty($match)) {
			$irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 認識に失敗しました');
			return;
		}

		$page = $match[2];
		$url = "http://ja.scp-wiki.net/{$page}";
		$html = $this->curl( $url, "0-1000" );
		if (!$html) {
			$irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 接続に失敗しました');
		}

		preg_match( '/(<title>)(.*?)(<\/title>)/i', $html, $matches);
		unset($html); // メモリ節約

		$title = str_replace(' - SCP財団','',$matches[2]);
		$title = htmlspecialchars_decode($title);

		$msg = "{$data->nick}: [WIKI] http://ja.scp-wiki.net/{$page} \"{$title}\"";

		// 発言
		$this->botMsg( $irc, $data, $msg );
	}

	/**
	 * サンドボックス出力
	 * @param $irc
	 * @param $data
	 */
	function sandbox($irc, $data){

		preg_match('/^(\.sb )(.*)$/i', $data->message, $match);
		// 再試行
		if (empty($match)) {
			preg_match('/^(\.sandbox )(.*)$/i', $data->message, $match);
		}
		if (empty($match)) {
			$irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 認識に失敗しました');
			return;
		}

		$user = $match[2];

		$url = "http://scp-jp-sandbox2.wikidot.com/{$user}/";
		$html = $this->curl( $url, "27000-36000" );
		if (!$html) {
			$irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, '[ERROR] 接続に失敗しました');
		}

		preg_match( '@(<div id="page-title">)([\s|\S]*?)(</div>)@i', $html, $matches);
		unset($html); // メモリ節約

		$title = $matches[2];
		$title = preg_replace('/^[ 　]+/u', '', $title);
		$title = preg_replace('/[ 　]+$/u', '', $title);
		$title = trim($title);

		$msg = "{$data->nick}: [SANDBOX][{$user}] {$url} - \"{$title}\"";

		// 発言
		$this->botMsg( $irc, $data, $msg );
	}

	function quit ( $irc, $data )
	{

		$pass = file_get_contents("/home/njr-sys/public_html/cli/logs/KASHIMA_quit.log" );

		preg_match('/^(\.quit )(.*)$/i', $data->message, $match);

		var_dump($pass);
		var_dump($match);

		if ($pass == $match[2]) {

			file_put_contents( "/home/njr-sys/public_html/cli/logs/KASHIMA_quit.log", $this->random(8) );
			$msg = 'さようなら';
			$irc->quit($msg);

		} else {
			$msg = "警告: 不正なコマンド";

			// 発言
			$this->botMsg( $irc, $data, $msg );
		}
	}

	private function random($length = 8)
	{
		return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
	}

	function dere (&$irc, &$data)
	{
		$a = array("あ","う","い","び","お","ひゃ","ほ","わ");
		$b = array("う","い","お","ひゃ","ほ","わ");
		$c = array("ゃ","ゅ","ょ","ぁ","ぃ","ぅ","ぇ","ぉ","い","び","お","ひゃ","ほ","わ","");

		$msg	=	$a[array_rand($a)];
		$msg	.=	$b[array_rand($b)];
		$msg	.=	$c[array_rand($c)];

		// 発言
		$this->botMsg( $irc, $data, $msg );
	}

	protected function curl( $url, $byte=null ){
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_USERAGENT, "Nijiru System - SCP_Foundation" );
		curl_setopt( $ch, CURLOPT_REFERER, "http://ja.scp-wiki.net/");
		if (!empty($byte)) {// 取得Byte数制限
			curl_setopt( $ch, CURLOPT_RANGE, $byte);
		}
		$result = curl_exec( $ch );
		curl_close($ch);
		return $result;
	}

}

$bot = new Kashima();
$irc = new Kashima_Core();

// debug //////////
echo "KashimaExe loaded\n";
echo memory_get_usage(true) . "\n";

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '.*', $bot, 'getlog');

$irc->registerActionHandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'welcome');
$irc->registerActionHandler(SMARTIRC_TYPE_PART|SMARTIRC_TYPE_QUIT, '.*', $bot, 'bye');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^hi$', $bot, 'hello');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, 'かしまちゃんかわいい', $bot, 'dere');

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.SCP-\d*$', $bot, 'scp');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.scp-\d*$', $bot, 'scp');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.SCP \d*$', $bot, 'scp'); // エイリアス
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.scp \d*$', $bot, 'scp'); // エイリアス

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.SCPJP-\d*$', $bot, 'scpjp');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.scpjp-\d*$', $bot, 'scpjp');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.SCPJP \d*$', $bot, 'scpjp'); // エイリアス
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.scpjp \d*$', $bot, 'scpjp'); // エイリアス

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.sb .*$', $bot, 'sandbox'); // エイリアス
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.sandbox .*$', $bot, 'sandbox'); // エイリアス
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.quit .*$', $bot, 'quit');

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.wiki .*$', $bot, 'wiki');

//$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '(?:^|[\s　]+)((?:https?|ftp):\/\/[^\s　]+)', $bot, 'url');

// 接続
$irc->connect(IRC_HOST, IRC_PORT);
$irc->login(IRC_NAME, IRC_NAME, 0, IRC_NAME, $irc_pass);
$irc->join(array($irc_channel));

echo "listen start\n";
echo memory_get_usage(true) . "\n";

$irc->listen();

echo "disconnect start\n";
echo memory_get_usage(true) . "\n";

$irc->disconnect();

