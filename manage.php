<?php
if(isset($_POST["PHPSESSID"])) { session_id($_POST['PHPSESSID']); }

session_start();
ob_start();

ini_set('date.timezone', 'Asia/Shanghai');
require 'ServerConf.inc';
define('THINK_PATH', './ThinkPHP/');
define('APP_NAME', 'Manage');
define('APP_PATH', './App/');
define('LIB_PATH', APP_PATH . 'Lib/');
define('BASE_PATH', dirname(__FILE__));
// define('WEB_ROOT', dirname(__FILE__) . "/");

// define('WEBROOT_PATH', '' );	//上传服务器需修

define('CONF_PATH', APP_PATH . 'Config/Manage/');
define('WEB_CACHE_PATH', WEB_ROOT."App/Runtime/Manage/Cache/");//网站当前路径
define("RUNTIME_PATH", WEB_ROOT . "App/Runtime/Manage/");
define("DatabaseBackDir", WEB_ROOT . "Databases/"); //系统备份数据库文件存放目录
define('COMMON_PATH', APP_PATH . 'Common/Manage/');
define('TMPL_PATH', APP_PATH . 'templates/');
define ( 'TMP_UPLOAD_PATH', WEBROOT_PATH . '/upload' );

// define ( 'DOMAIN', 'http://' . $_SERVER ["HTTP_HOST"] );

define ( 'IMG_FILE_PATH', DOMAIN . WEBROOT_PATH );

//miaomin addedgit
// 自定义
// 重要！！！ 上传前请修改以下两处设置
//define('WEBROOT_PATH', '/3dprinter');
//define('WEB_ROOT', dirname(__FILE__) . "/");
// define('WEBROOT_URL', DOMAIN.'');

//require 'ThinkPHP/ThinkPHP.php';
require(THINK_PATH . "ThinkPHP.php");

/*define('RUNTIME_PATH', APP_PATH . 'Runtime/Admin/');
require 'ThinkPHP/ThinkPHP.php';*/
?>