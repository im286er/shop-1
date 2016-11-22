<?php
require 'ServerConf.inc';
define ( 'APP_PATH', './App/' );
define ( 'LIB_PATH', APP_PATH . 'Lib/' );
define ( 'COMMON_PATH', APP_PATH . 'Common/' );
define ( 'TMPL_PATH', APP_PATH . 'templates/' );
define ( 'RUNTIME_PATH', APP_PATH . 'Runtime/User/' );
define ( 'CONF_PATH', APP_PATH . 'Config/User/' );
// 带盘符的路径
define ( 'BASE_PATH', dirname ( __FILE__ ) );
define ( 'HOMEPAGE', DOMAIN . WEBROOT_PATH );
define ( 'IMG_FILE_PATH', WEBROOT_PATH );
define ( 'FRONT_PAGE', WEBROOT_PATH . '/index.php' );
define ( 'TMP_UPLOAD_PATH', WEBROOT_PATH . '/upload' );
require 'ThinkPHP/ThinkPHP.php';
?>