<?php
/**
 * @author miaomin
 */
return array (
		
		'LOAD_EXT_CONFIG' => array (
				'../config',
				'web3d' => 'web3d' 
		),
		
		// 打小写自动转换
		'URL_CASE_INSENSITIVE' => true,
		// Session
		'SESSION_AUTO_START' => true,
		
		// 启用Trace
		'SHOW_PAGE_TRACE' => true,
		// 启用运行状态
		'SHOW_RUN_TIME' => true,
		'SHOW_ADV_TIME' => true,
		'SHOW_DB_TIMES' => true,
		'SHOW_CACHE_TIMES' => true,
		'SHOW_USE_MEM' => true,
		'SHOW_LOAD_FILE' => true,
		'SHOW_FUN_TIMES' => true,
		
		// 启用分组模式
		'APP_GROUP_LIST' => 'Web3d',
		'DEFAULT_GROUP' => 'Web3d',
		
		// 模版缓存
		'TMPL_CACHE_ON' => false,
		
		// 模版引擎
		'TMPL_ENGINE_TYPE' => 'Smarty',
		'HEADPAGE_START' => '../../../pageheader_start.html', // 头文件包含变量
		'HEADPAGE_END' => '../../../pageheader_end.html', // 头文件包含变量
		'FOOTPAGE' => '../../../pagefooter.html', // 底部头文件包含变量
		                                          
		// 模版配置
		'TMPL_ENGINE_CONFIG' => array (
				'template_dir' => TMPL_PATH . 'Web3d/default',
				'compile_dir' => APP_PATH . "templates_c/",
				'left_delimiter' => '<{',
				'right_delimiter' => '}>' 
		),
		
		// 模版替换
		'TMPL_PARSE_STRING' => array (
				'__PUBLIC__' => WEBROOT_PATH . '/static',
				'__APP__' => WEBROOT_PATH . '/web3d.php',
				'__AVATAR_UPLOAD_PATH__' => WEBROOT_PATH . '/upload/avatar',
				'__DOC__' => WEBROOT_PATH,
				'__UPLOAD__' => WEBROOT_PATH . '/upload',
				// nginx环境下
				// '__APP__' => '/3DF/Admin',
				'__TEMPLATES__' => TMPL_PATH . 'Admin' 
		),
		
		'DEFAULT_THEME' => 'default',
		
		// 错误跳转页面
		'TMPL_ACTION_ERROR' => TMPL_PATH . 'Web3d/default/error.html',
		
		'TMPL_ACTION_SUCCESS' => TMPL_PATH . 'Web3d/default/success.html',
		
		// 页面跳转等待时间
		'JUMP_URL_WAIT_SECONDS' => 5 
);
?>