<?php
require 'ServerConf.inc';

define ( 'APP_PATH', './App/' );
define ( 'LIB_PATH', APP_PATH . 'Lib/' );
define ( 'COMMON_PATH', APP_PATH . 'Common/' );
define ( 'TMPL_PATH', APP_PATH . 'templates/' );
define ( 'RUNTIME_PATH', APP_PATH . 'Runtime/Front/' );
define ( 'CONF_PATH', APP_PATH . 'Config/Front/' );
// 带盘符的路径
define ( 'BASE_PATH', dirname ( __FILE__ ) );

// 自定义
// 重要！！！ 上传前请修改以下两处设置



define ( 'WEB3D_SERVER', 'http://192.168.20.16' );

//
define ( 'IMG_FILE_PATH', DOMAIN . WEBROOT_PATH );

require 'ThinkPHP/ThinkPHP.php';

?>