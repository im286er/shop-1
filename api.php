<?php

require 'ServerConf.inc';

define ( 'MODE_NAME', 'rest' );
define ( 'APP_PATH', './App/' );
define ( 'LIB_PATH', APP_PATH . 'Lib/' );
define ( 'COMMON_PATH', APP_PATH . 'Common/' );
define ( 'RUNTIME_PATH', APP_PATH . 'Runtime/Api/' );
define ( 'CONF_PATH', APP_PATH . 'Config/Api/' );
define ( 'BASE_PATH', dirname ( __FILE__ ) );

// miaomin added
// 自定义
// 重要！！！ 上传前请修改以下设置


require 'ThinkPHP/ThinkPHP.php';
?>