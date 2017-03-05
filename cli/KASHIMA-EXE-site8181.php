<?php
/*
 * 起動 :
 * sh /home/njr-sys/public_html/cli/sh/kashima_81_start.sh
 *
 * 停止 :
 * sh /home/njr-sys/public_html/cli/sh/kashima_81_stop.sh
 *
 * 再起動 :
 * sh /home/njr-sys/public_html/cli/sh/kashima_81_reboot.sh
 *
 * （手動起動）
 * nohup php /home/njr-sys/public_html/cli/KASHIMA-EXE-site8181.php &
 *
 */

/**
 * テストフラグ
 */
define("IS_TEST", 0);

require_once('/home/njr-sys/public_html/lib/kashima-core/kashima-core.php');
require_once('/home/njr-sys/public_html/cli/AbstractKashima.php');

define("IRC_HOST", "irc.synirc.net"); //ホスト名
define("IRC_PORT", "6667"); //ポート番号

if (IS_TEST == 1) {
    define("LOG_PATH", "/home/njr-sys/public_html/cli/logs/irc/test/"); //ログの保存先
    define("EXTENSION", "_test.log"); // ログファイルの末尾
    define("IRC_NAME", "KASHIMA-EXE-test"); // おなまえ
    $irc_channel = "#ikr4185-elke-test"; // チャンネル名
} else {
    define("LOG_PATH", "/home/njr-sys/public_html/cli/logs/irc/"); //ログの保存先
    define("EXTENSION", ".log"); // ログファイルの末尾
    define("IRC_NAME", "KASHIMA-EXE-81"); // おなまえ
    $irc_channel = "#site8181"; // チャンネル名
}

$irc_pass = null;

require_once "/home/njr-sys/public_html/application/_cores/config/Config.php";

/**
 * Class KashimaExe_81
 * @see  AbstractModel
 */
class KashimaExe_81 extends AbstractKashima
{
    /**
     * DBへ情報を保存
     * @param $nick
     * @param $msg
     * @return int
     */
    protected function saveLog($nick, $msg)
    {
        // fix Mysql timeout
        unset($this->pdo);
        $pdo = new PDO(\Cores\Config\Config::load("db.dsn"), \Cores\Config\Config::load("db.user"), \Cores\Config\Config::load("db.pass"));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sql = 'insert into irc_log_81( nick, message, date) values ( ?, ?, now() )';
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute(array($nick, $msg));
    }
}

$bot = &new KashimaExe_81();
$irc = &new Kashima_Core();

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '.*', $bot, 'getLog');

$irc->registerActionHandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'welcome');
$irc->registerActionHandler(SMARTIRC_TYPE_PART | SMARTIRC_TYPE_QUIT, '.*', $bot, 'bye');

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.ping$', $bot, 'ポン');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^hi$', $bot, 'hello');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, 'かしまちゃんかわいい', $bot, 'dere');

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.scp-\d*$', $bot, 'scp');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.scp \d*$', $bot, 'scp'); // エイリアス

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.scpjp-\d*$', $bot, 'scpjp');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.scpjp \d*$', $bot, 'scpjp'); // エイリアス

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.tale-(.*)$', $bot, 'tale');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.tale (.*)$', $bot, 'tale'); // エイリアス

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.talejp-(.*)$', $bot, 'talejp');
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.talejp (.*)$', $bot, 'talejp'); // エイリアス

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.sb .*$', $bot, 'sandbox'); // エイリアス
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.sandbox .*$', $bot, 'sandbox'); // エイリアス
$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.quit .*$', $bot, 'quit');

$irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^\.wiki .*$', $bot, 'wiki');

// 接続
$irc->connect(IRC_HOST, IRC_PORT);
$irc->login(IRC_NAME, IRC_NAME, 0, IRC_NAME, $irc_pass);
$irc->join(array($irc_channel));
$irc->listen();
$irc->disconnect();
