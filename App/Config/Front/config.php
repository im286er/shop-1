<?php
// Front Config
return array (
		
		'LOAD_EXT_CONFIG' => array (
				'../config',
				'SEARCH' => './search',
				'PRODUCT' => '../product'
		),
		
		'URL_PATHINFO_DEPR' =>'-',
		// 伪静态后缀
		'URL_HTML_SUFFIX' => '.html',
		
		// 启用分组模式
		'APP_GROUP_LIST' => 'Front',
		'DEFAULT_GROUP' => 'Front',
		
		// 模版引擎
		'TMPL_ENGINE_TYPE' => 'Smarty',
		'HEADPAGE_START' => '../../../pageheader_start.html', // 头文件包含变量
		'HEADPAGE_END' => '../../../pageheader_end.html', // 头文件包含变量
		'FOOTPAGE' => '../../../pagefooter.html', // 底部头文件包含变量
		
		
		'HEAD_START' => '../../../header_start.html', // 头文件包含变量
		'HEAD_END' => '../../../header_end.html', // 头文件包含变量
		'FOOT' => '../../../footer.html', // 底部头文件包含变量
		
		'AHEAD_START' => '../../../aheader_start.html', // 头文件包含变量
		'AHEAD_END' => '../../../aheader_end.html', // 头文件包含变量
		'AFOOT' => '../../../afooter.html', // 底部头文件包含变量
		



        // 模版配置
		'TMPL_ENGINE_CONFIG' => array (
				//'template_dir' => TMPL_PATH . 'Front/kitty',
				'template_dir' => TMPL_PATH . 'Front/micky',
				// 'template_dir' => TMPL_PATH . 'Front/fun', // ’Front/kitty‘ [zhengweifu 2016-05-24]
				//'template_dir' => TMPL_PATH . 'Front/green',
				'compile_dir' => APP_PATH . "templates_c/",
				'left_delimiter' => '<{',
				'right_delimiter' => '}>' 
		),
		
		// 默认错误跳转对应的模板文件
		'TMPL_ACTION_ERROR' => TMPL_PATH . 'Front/micky/error.html',
		// 默认成功跳转对应的模板文件
		'TMPL_ACTION_SUCCESS' => TMPL_PATH . 'Front/micky/success.html',
		
		// 模版替换
	'TMPL_PARSE_STRING' => array (
				'__PUBLIC__' => WEBROOT_PATH . '/micky',
		'__KITTYPUBLIC__' => WEBROOT_PATH . '/kitty',
				'__APP__' => WEBROOT_PATH . '/index',
				'__DOC__' => WEBROOT_PATH,
				'__IMG__' => IMG_FILE_PATH,
				'__NEOSTATIC__' => WEBROOT_PATH . '/doge',
				'__MSTATIC__' => WEBROOT_PATH . '/m/doge',
		
		        '__KITTYMSTATIC__' => WEBROOT_PATH . '/m/kitty',
		        '__KITTYSTATIC__' => WEBROOT_PATH . '/kitty',

				'__MICKYMOBILESTATIC__' => WEBROOT_PATH . '/m/micky',
				'__MICKYSTATIC__' => WEBROOT_PATH . '/micky',

				// nginx环境下
				// '__APP__' => '/3DF/Admin',
				'__TEMPLATES__' => TMPL_PATH . 'kitty',
				'__USERIDX__' => WEBROOT_PATH . '/user.php',
				'__UPLOAD__' => WEBROOT_PATH . '/upload',
				'__WEB3D__' => WEB3D_SERVER,
		),
		
		//'DEFAULT_THEME' => 'kitty',
		'DEFAULT_THEME' => 'micky',
		// 语言
		'LANG_SWITCH_ON' => true,
       // 'DEFAULT_MODULE'        => 'jewelry', // 默认操作名称    
		'ERROR_PAGE' =>'/public/404.html'
);
?>