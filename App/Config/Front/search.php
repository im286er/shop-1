<?php
// 检索的一些页面配置
// @formatter:off
return array (
		// 每页显示结果数量(大图模式)
		'RES_COUNT_OPTION3' => array (
				array (
						'key' => '20个',
						'value' => 20
				),
				array (
						'key' => '40个',
						'value' => 40
				)
		),
		
		// 每页显示结果数量(小图模式)
		'RES_COUNT_OPTION2' => array (
				array (
						'key' => '30个',
						'value' => 30 
				),
				array (
						'key' => '50个',
						'value' => 50 
				),
				array (
						'key' => '100个',
						'value' => 100 
				) 
		),
		
		// 每页显示结果数量(旧版)
		'RES_COUNT_OPTION' => array (
				20,
				30,
				50,
				100 
		),
		
		// 每页显示结果的缩略图大小
		'RES_THUMB_OPTION' => array (
				'1', // 中等
				'2'  // 小图
		),
		
		// 每页显示结果的缩略图大小(新版)
		'RES_THUMB_OPTION2' => array (
				array (
						'key' => '大',
						'value' => '1'
				),
				array (
						'key' => '中等',
						'value' => '2'
				)
		),
		
		// 每页显示结果的排序方式
		'RES_ORDER_OPTION' => array (
				'createdate_desc', // 最近更新
				'createdate_asc' // 最早更新
		),
		
		// 每页显示结果的排序方式(新版)
		'RES_ORDER_OPTION2' => array (
			array (
					'key' => '质量最高',
					'value' => 'score_desc'
			),
			array (
					'key' => '质量最低',
					'value' => 'score_asc'
			),
			array (
					'key' => '最近更新',
					'value' => 'createdate_desc'
			),
			array (
					'key' => '最早更新',
					'value' => 'createdate_asc'
			)
		),
		
		// 每页显示结果的排序方式(新版带价格排序)
		'RES_ORDER_OPTION3' => array (
				array (
					'key' => '质量最高',
					'value' => 'score_desc'
				),
				array (
						'key' => '质量最低',
						'value' => 'score_asc'
				),
				array (
						'key' => '最近更新',
						'value' => 'createdate_desc'
				),
				array (
						'key' => '最早更新',
						'value' => 'createdate_asc'
				),
				array (
						'key' => '价格最高',
						'value' => 'price_desc'
				),
				array (
						'key' => '价格最低',
						'value' => 'price_asc'
				)
		),
		
		// 每页显示结果的星级筛选(新版)
		'RES_WHERE_STAR' => array (
				array (
					'key' => '全部',
					'value' => 0
				),
				array (
						'key' => '一星',
						'value' => 1
				),
				array (
						'key' => '二星',
						'value' => 2
				),
				array (
						'key' => '三星',
						'value' => 3
				),
				array (
						'key' => '四星',
						'value' => 4
				),
				array (
						'key' => '五星',
						'value' => 5
				)
		),
		
		
		// 每页显示结果的样式
		'RES_TYPE_OPTION' => array (
				'1', // 图片
				'2'  // 列表
		),
		
		'RES_TYPE_CSS_OPTION' => array (
				'grida',
				'lista' 
		)
);
?>