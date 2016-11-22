<?php
// WEBGL的一些配置
return array (
		// 截图生成缩略图尺寸
		'CAPTURE_THUMB_WIDTH' => array (
				'64',
				'100',
				'220'

		),
		
		// 截图规范尺寸如果不满足该尺寸将会以白图填充
		'CAPTURE_NORMAL_WIDTH' => 600,
		'CAPTURE_NORMAL_HEIGHT' => 600,
		
		// 贴图允许的MIME格式
		'TEXTURE_ALLOW_TYPE' => array (
				'png',
				'jpeg' 
		) 
);
?>