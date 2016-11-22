<?php
// User Config
// @formatter:off
return array (
		// 通用配置
		'LOAD_EXT_CONFIG' => array(
				'../config',
				'SEARCH' => '../search',
				'PRODUCT' => '../product'
		),

		// 启用分组模式
		'APP_GROUP_LIST' 	=> 'User',
		'DEFAULT_GROUP' 	=> 'User',
		
		'URL_PATHINFO_DEPR' =>'/',
		// 伪静态后缀
		'URL_HTML_SUFFIX' => '',
			
		// 模版引擎
		'TMPL_ENGINE_TYPE' => 'Smarty',
		'HEAD_START' => '../../../header_start.html', // 头文件包含变量
		'HEAD_END' => '../../../header_end.html', // 头文件包含变量
		'FOOTPAGE' => '../../../footer.html', // 底部头文件包含变量
			
		// 模版配置
		'TMPL_ENGINE_CONFIG' => array (
			'template_dir' => TMPL_PATH . 'User/default',
				'compile_dir' => APP_PATH . "templates_c/",
				'left_delimiter' => '<{',
				'right_delimiter' => '}>' 
		),
		// 模版替换
		'TMPL_PARSE_STRING' => array (
			'__IMG__' => IMG_FILE_PATH,
			'__UPLOAD__' => WEBROOT_PATH . '/upload',
			'__FRONTIDX__' => WEBROOT_PATH . '/index.php',
			'__STATIC__' => WEBROOT_PATH . '/static',
			'__PUBLIC__' => WEBROOT_PATH . '/static/User',
			'__APP__' => WEBROOT_PATH . '/user.php',
			'__DOC__' => WEBROOT_PATH,
			'__TEMPLATES__' => TMPL_PATH . 'User' ,
			'__DOMAIN__' => DOMAIN,
			'__WEB3D__' => WEB3D_SERVER,
			'__USERIDX__' => WEBROOT_PATH . '/user.php',
        ),
		
		// 默认的模板目录
	'DEFAULT_THEME' => 'default',

		// 错误跳转页面
		//'TMPL_ACTION_ERROR' => TMPL_PATH . 'User/default/error.html',
		'TMPL_ACTION_ERROR' => TMPL_PATH . 'User/default/error.html',
		//'TMPL_ACTION_SUCCESS' => TMPL_PATH . 'User/default/success.html',
		'TMPL_ACTION_SUCCESS' => TMPL_PATH . 'User/default/success.html',
		
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
		'USER_RESET_VALID_TIME' => 3600
);
?>