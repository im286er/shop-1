<?php
// Admin Config
$config_arr0 = array (

		
		'LOAD_EXT_CONFIG' => array('../config', '../yun', 'PR'=>'../printer', 'ECOUPON'=>'../ecoupon', 'PRODUCT' => '../product'),			// 自动载入设置
		// 启用分组模式
		'APP_GROUP_LIST' => 'Manage',
		'DEFAULT_GROUP' => 'Manage',

        'PUBLIC_KEY'=>'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm',

		// 模版引擎
		// 'TMPL_ENGINE_TYPE' => 'Smarty',
		'TMPL_ENGINE_TYPE' => 'Think',
		// 模版配置
		'TMPL_ENGINE_CONFIG' => array (
				'template_dir' => TMPL_PATH . 'Manage/default',
				'compile_dir' => APP_PATH . "templates_c/",
		),
		
		// 默认错误跳转对应的模板文件
		'TMPL_ACTION_ERROR' => TMPL_PATH . 'Manage/default/error.html',
		// 默认成功跳转对应的模板文件
		'TMPL_ACTION_SUCCESS' => TMPL_PATH . 'Manage/default/success.html',
		
		
		//'CRATE'=> 0.01,
		
		// 模版替换
		'TMPL_PARSE_STRING' => array (
				
				'__IMG__' => IMG_FILE_PATH,
				'__PUBLICUPLOAD__' => WEBROOT_PATH . '/upload',
				'__PUBLICSTATIC__' => WEBROOT_PATH . '/static',
				'__PUBLIC__' => WEBROOT_PATH . '/static/Manage',
				'__MIMGSTYLE__' => WEBROOT_PATH . '/static/mimg',
				'__APP__' => WEBROOT_PATH . '/manage.php',
				'__ADMINAPP__' => WEBROOT_PATH . '/admin.php',
				'__DOC__' => WEBROOT_PATH,
				// nginx环境下
				// '__APP__' => '/3DF/Admin',
				'__TEMPLATES__' => TMPL_PATH . 'Manage/default' 
		),
		'DEFAULT_THEME' => 'default',
		
		'TMPL_L_DELIM' => '{',
		'TMPL_R_DELIM' => '}' 
);

// echo "WEB_ROOT:".WEB_ROOT."<br>";
// echo "WEBROOT_PATH:".WEBROOT_PATH."<br>";
// echo "TMPL_PATH:".TMPL_PATH."<br>";
// echo "SITE_PATH:".SITE_PATH."<br>";
// echo "COMMON_PATH:".COMMON_PATH."<br>";

// echo "COMMON_PATH:".COMMON_PATH."<br>";
// echo "<br>THINK_PATH:".THINK_PATH."<br>";

$config_arr1 = include_once WEB_ROOT . 'App/Common/Manage/config.php';
$DB_PREFIX = $config_arr1 ['DB_PREFIX'];
// echo "DB_PREFIX:".$DB_PREFIX."<br>";
// var_dump($config_arr1);
$config_arr2 = array(
		/*
		 * 以下是RBAC认证配置信息
		*/
		'USER_AUTH_ON' => true,
		'USER_AUTH_TYPE' => 1 , // 默认认证类型 1 登录认证 2 实时认证
		'USER_AUTH_KEY' => 'authId', // 用户认证SESSION标记
		                             // 'ADMIN_AUTH_KEY' => '7549268@qq.com',
		'USER_AUTH_MODEL' => 'Admin', // 默认验证数据表模型
		'AUTH_PWD_ENCODER' => 'md5', // 用户认证密码加密方式encrypt
		'USER_AUTH_GATEWAY' => '/manage.php/public/index', // 默认认证网关
		'NOT_AUTH_MODULE' => 'Index,Empty,Public,diy,Endproduct,Cates,tdf_users,notice',  // 默认无需认证模块
		'REQUIRE_AUTH_MODULE' => '', // 默认需要认证模块
		'NOT_AUTH_ACTION' => 'Editprepaid,editprepaid,ContentList,ContentEdit,index', // 默认无需认证操作
		'REQUIRE_AUTH_ACTION' => '', // 默认需要认证操作
		'GUEST_AUTH_ON' => false, // 是否开启游客授权访问
		'GUEST_AUTH_ID' => 0, // 游客的用户ID
		'RBAC_ROLE_TABLE' => $DB_PREFIX . 'role',
		'RBAC_USER_TABLE' => $DB_PREFIX . 'role_user',
		'RBAC_ACCESS_TABLE' => $DB_PREFIX . 'access',
		'RBAC_NODE_TABLE' => $DB_PREFIX . 'node',
		/*
		 * 系统备份数据库时每个sql分卷大小，单位字节
		*/
		/* 定义Category */
		// 'Category' =>WEB_ROOT.'App/Common/Admin/Extend/Category.class.php',
		
		'sqlFileSize' => 5242880, // 该值不可太大，否则会导致内存溢出备份、恢复失败，合理大小在512K~10M间，建议5M一卷
		                          // 10M=1024*1024*10=10485760
		                          // 5M=5*1024*1024=5242880
		                          
		// miaomin added
		
		'LANG_SWITCH_ON' => true, // 语言
		
		// 邮件配置
		'THINK_EMAIL' => array (
				'SMTP_HOST' => 'smtp.exmail.qq.com', // SMTP服务器
				'SMTP_PORT' => '25', // SMTP服务器端口
				'SMTP_USER' => 'bitmap@bitmap3d.com.cn', // SMTP服务器用户名
				'SMTP_PASS' => 'bit2012', // SMTP服务器密码
		
				'FROM_EMAIL' => 'bitmap@bitmap3d.com.cn', // 发件人EMAIL
				'FROM_NAME' => 'Bitmap3D', // 发件人名称
				//'REPLY_EMAIL' => 'no-reply@bitmap3d.com.cn', // 回复EMAIL（留空则为发件人EMAIL）
				'REPLY_EMAIL' => 'bitmap@bitmap3d.com.cn', // 回复EMAIL（留空则为发件人EMAIL）
				'REPLY_NAME' => 'Bitmap3D'  // 回复名称（留空则为发件人名称）
		),
		
		/* 邮件激活 */
		'USER_ACTIVE' => array ( // [用户邮件验证]
				'URL' => WEBROOT_URL . '/user.php/mail_validate/registeractivate/?code=', // 邮件验证地址地址
				'TITLE' => 'active_title',
				'CONTENT' => 'active_content' 
		),
		
		// 邮件激活有效时间
		'USER_ACTIVE_VALID_TIME' => 3600,
		
		/* 模型检查完毕通知  */
		'VERIFY_PRODUCT' => array (
				'TITLE' => 'verify_product_title',
				'CONTENT' => 'verify_product_content' 
		),
		
		/* 订单完成邮件通知  */
		'ORDER_COMPLETE' => array (
				'TITLE' => 'order_complete_title',
				'CONTENT' => 'order_complete_content' 
		),
	
);
$config_arr = array_merge ( $config_arr0, $config_arr1, $config_arr2 );

return $config_arr;

?>