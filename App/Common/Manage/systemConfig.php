<?php

//include_once WEB_ROOT . 'App/Common/Manage/Functions/functions.php';
include_once WEB_ROOT . 'App/Common/Manage/function.php';

include_once WEB_ROOT . 'App/Common/common.php';
include_once WEB_ROOT . 'App/Common/SearchParser.php';


return array (
		'SITE_INFO' => array (
				'name' => '后台管理',
				'version' => '1',
				'icp' => '2',
				'service' => '3',
				'tel' => '',
				'fax' => '',
				'address' => '',
				'postcode' => '',
				'keyword' => '后台管理系统',
				'description' => '3Dcity网站' 
		),
		//'WEB_ROOT' => 'http://192.168.52.72/3dprinter/',
		'WEB_ROOT' => 'http://localhost/city/',
		'AUTH_CODE' => '03jNmc',
		'ADMIN_AUTH_KEY' => 'nxbin@163.com',
		'DB_HOST' => '192.168.52.17',
		'DB_NAME' => 'citydvs',
		'DB_USER' => 'root',
		'DB_PWD' => 'gdi2012',
		'DB_PORT' => '3306',
		'DB_PREFIX' => 'tdf_',
		'webPath' => '/city/',
		'TOKEN' => array (
				'admin_marked' => 'city',
				'admin_timeout' => '360000',
				//'member_marked' => 'http://192.168.52.72/3dprinter',//外网可以访问
				'member_marked' => 'http://localhost/city',
				'member_timeout' => '360000' 
		) 
);
?>
