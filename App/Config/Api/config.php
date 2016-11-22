<?php
return array (
		'LOAD_EXT_CONFIG' => array (
				'WEBGL' => 'webgl',
				'API' => 'api',
				'../config',
            'SEARCH' => './search',
            'PRODUCT' => '../product',

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
		
		// 语言
		'LANG_SWITCH_ON' => true,
		
		// 启用分组模式
		'APP_GROUP_LIST' => 'Api',
		'DEFAULT_GROUP' => 'Api',
);
?>