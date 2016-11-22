<?php
// Global Config
// @formatter:off
return array (
		//文件下载路径 0为网站服务器文件下载  1为先判断OSS存储路径优先
		'DOWNTYPE'=>true,

		'ENABLE_ACCESSCODE' => false,

		// 验证码字体路径
		'CAPTCHA_FONTS_PATH' => array (
				//Windows版本：
				// 'C:/Windows/Fonts/Tahoma.ttf'
				//Nginx版本：
				'/tahoma.ttf'
		),
		
		// 大小写自动转换
		'URL_CASE_INSENSITIVE' => true,
		// Session
		'SESSION_AUTO_START' => false,
		'LANG_SWITCH_ON' 	=> true,   	//开启语言包功能
		'LANG_AUTO_DETECT' 	=> false,	//自动侦测语言
		'DEFAULT_LANG'		=> 'zh-cn',
		'LANG_LIST'        	=> 'zh-cn,en-us',
		
		// Url模式
		'URL_MODEL' => 1,
		
		//'URL_PATHINFO_DEPR' =>'',
		// 伪静态后缀
		//'URL_HTML_SUFFIX' => 'html',
		
		// 启用Trace
		'SHOW_PAGE_TRACE' => false,
		// 启用运行状态
		'SHOW_RUN_TIME' => false,
		'SHOW_ADV_TIME' => false,
		'SHOW_DB_TIMES' => false,
		'SHOW_CACHE_TIMES' => false,
		'SHOW_USE_MEM' => false,
		'SHOW_LOAD_FILE' => false,
		'SHOW_FUN_TIMES' => false,
		
		// 页面跳转等待时间
		'JUMP_URL_WAIT_SECONDS' => 10,
		
		// 产品临时文件上传路径
		'PRODUCT_TEMPFILE_PATH' => './upload/temp/productfile/',
		// 产品文件上传路径
		'PRODUCT_FILE_PATH' => './upload/productfile/',
		
		// 文件上传路径
		'UPLOAD_PAHT' => array (
				'PRODUCT' => './upload/productfile/',
				'PRODUCT_TEMP' => './upload/temp/productfile/',
				'PRODUCT_WEB' => '/upload/productfile/',
				'PRODUCT_PHOTO' => './upload/productphoto/',
				'PRODUCT_PHOTO_WEB' => '/upload/productphoto/',
				'PROJECT'=>'./upload/project' //简笔画文件存储位置(文件和图片)
		),
		
		// 数据库配置信息
		'DB_TYPE' => 'pdo',
		'DB_PREFIX' => 'tdf_',
		'DB_USER' => 'root',
		'DB_PWD' => 'gdi2012',
		'DB_DSN' => 'mysql:host=192.168.52.17;dbname=zizhu;charset=utf8',

		// 邮件配置
		'THINK_EMAIL' => array (
			'SMTP_HOST'     => '', // SMTP服务器
			'SMTP_PORT'     => '', // SMTP服务器端口
			'SMTP_USER'     => '', // SMTP服务器用户名
			'SMTP_PASS'     => '', // SMTP服务器密码
			'FROM_EMAIL'    => '', // 发件人EMAIL
			'FROM_NAME'     => '', // 发件人名称
			'REPLY_EMAIL'   => '', // 回复EMAIL（留空则为发件人EMAIL）
			'REPLY_NAME'    => ''  // 回复名称（留空则为发件人名称）
		),
		
		// 用户邮件验证
		'USER_ACTIVE' => array (
				'URL' => WEBROOT_URL . '/user.php/userconf/active/?sv=',
				'TITLE' => 'active_title',
				'CONTENT' => 'active_content'
		),
		
		// 用户邮件验证有效时长(毫秒)
		'USER_ACTIVE_VALID_TIME' => 31536000000,
		
		// 用户重置密码
		'USER_RESET' => array (
				'URL' => WEBROOT_URL . '/user.php/mail_validate/resetpass/?code=',
				'TITLE' => 'reset_title',
				'CONTENT' => 'reset_content'
		),
		
		// 用户重置密码有效时长(秒)
		'USER_RESET_VALID_TIME' => 3600,
		
		// PYTHON WEB3D Service Port
		'PY_WEB3D_PORT' => 8002,
    //SMS配置
        'SMS_SERVER'=>array(
            'accountSid'    =>'',
	        'accountToken'  =>'',
	        'appId'         =>'',
	        'serverIP'      =>'',
            'serverPort'    =>'',
            'softVersion'   =>'',
        ),
        //对于每个用户限制每天发送的个数
        'SMS_LIMIT'=>'',
    
);
?>