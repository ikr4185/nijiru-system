<?php
/**
 * システム設定ファイル
 * Created by PhpStorm.
 * User: th1nk0
 * Date: 2015/05/17
 * Time: 18:03
 */

if (isset($_SERVER['SERVER_NAME'])) {
    define('SERVER_NAME', $_SERVER['SERVER_NAME']);
}else{
    define('SERVER_NAME', "njr-sys.net");
}
define('BASIC_HOST_NAME', 'http://njr-sys.net/');
define('SYSTEM_ROOT','/home/njr-sys/public_html/');
define('DEFAULT_ROOT','/home/njr-sys/public_html');

define('DISPATCHER_PHP', '/home/njr-sys/public_html/mvc/Dispatcher.php');

define('STATICS', '/home/njr-sys/public_html/class/common/Statics.php');

define('CLASS_DIR', '/home/njr-sys/public_html/class');
    define('ABST_CNTLR', '/home/njr-sys/public_html/class/common/AbstractController.php');
    define('CMMN_CNTLR', '/home/njr-sys/public_html/class/common/CommonController.php');
    define('CMMN_MDL', '/home/njr-sys/public_html/class/common/CommonModel.php');
    define('REDIRECT', '/home/njr-sys/public_html/class/common/Redirect.php');
    define('ACCESS_LOG', '/home/njr-sys/public_html/class/AccessLog/AccessLog.php');
    define('DB_CLS', '/home/njr-sys/public_html/class/common/DB_users.php');
    define('DB_F_SCP_CLS', '/home/njr-sys/public_html/class/common/DB_favorite_scp.php');
    define('MAIL_CLS', '/home/njr-sys/public_html/class/common/MailModel.php');

define('TEMPLATE_DIR', '/home/njr-sys/public_html/template');
    define('CSS_CSS','/template/assets/css/_old/css.css');//旧CSS
    define('NIJIRU_CSS','/template/assets/css/nijiru.css');// Nijiru System CSS
    define('TABLE_CSS','/template/assets/css/_old/table.css');

define('DOWNLOAD_DIR', '/home/njr-sys/public_html/template/assets/img/download');
define('DOWNLOAD_PATH', '/template/assets/img/download');

define('INCLUDE_DIR', '/home/njr-sys/public_html/template/include');

define('LOG_DIR', '/home/njr-sys/log');

define('RESORCE_DIR', '/home/njr-sys/public_html/template/assets/resource');
define('ID_PNG', 'http://njr-sys.net/template/assets/img/idgen');

