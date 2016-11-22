<?php
//User Config
return array(
		'LOAD_EXT_CONFIG'=>'../config',
		// 启用分组模式
		'APP_GROUP_LIST' => 'Test',
		'DEFAULT_GROUP' => 'Test',
		
		// 模版引擎
		'TMPL_ENGINE_TYPE' => 'Smarty',
		// 模版配置
		'TMPL_ENGINE_CONFIG' => array (
				'template_dir' => TMPL_PATH . 'Test/default',
				'compile_dir' => APP_PATH . "templates_c/",
				'left_delimiter' => '<{',
				'right_delimiter' => '}>'
		),
		// 模版替换
		'TMPL_PARSE_STRING' => array (
				'__PUBLIC__' => WEBROOT_PATH . '/static',
				'__APP__' => WEBROOT_PATH . '/index.php',
				'__DOC__' => WEBROOT_PATH,
				//nginx环境下
				//'__APP__' => '/3DF/Admin',
				'__TEMPLATES__' => TMPL_PATH . 'Test',
		),
		
		'DEFAULT_THEME' => 'default'
);
?>