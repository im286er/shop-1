<?php
/**
 * @author miaomin
 */
// Web3d的一些配置
return array (
		// 允许转换Web3d的文件格式
		'ALLOW_CONVERT_TYPE' => array (
				'.obj',
				'.fbx',
				'.stl' 
		),
		
		// 允许转换Web3d的最大文件尺寸
		// 50M
		'ALLOW_CONVERT_SIZE' => 52428800,
		
		// 保存路径起始地址
		'SAVE_FILEPATH_PREFIX' => '\/home\/wwwroot\/default\/'
);
?>