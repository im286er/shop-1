<?php
// 检索的一些页面配置
return array (
		// 每页显示结果数量
		'RES_COUNT_OPTION' => array (
				20,
				30,
				50,
				100
		),
		
		// 每页显示结果的缩略图大小
		'RES_THUMB_OPTION' => array (
				'1',					//中等
				'2' 					//小图
		),
		
		// 每页显示结果的排序方式
		'RES_ORDER_OPTION' => array (
			'score_desc',					//最高星级
			'createdate_desc',		//最近更新
			'createdate_asc',			//最早更新
		),
		
		// 每页显示结果的样式
		'RES_TYPE_OPTION' => array (
			'1',						//图片
			'2'							//列表
		),

		'RES_TYPE_CSS_OPTION' => array (
				'grida',
				'lista'
		)
);
?>