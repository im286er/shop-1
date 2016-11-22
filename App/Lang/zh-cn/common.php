<?php
// zh_cn
return array(
		'billtype'=>array(
			1=>'个人',
			2=>'公司',
		),
		'billcontent'=>array(
			0=>'明细',
		),
		
		'sex'=>array(
			'0'=>'male',
			'1'=>'female',
		),

        'categroup'=>array(
            '1'=>'戒指',
            '2'=>'项链',
            '3'=>'袖扣',
        ),

		
		'necklaceStyle' =>array( //现在默认全是1为O形链 ，以后加多链子需要修改对应的值
			'1'=>'珠子链',
			'2'=>'O形链',
			'3'=>'十字链',
			'4'=>'盒子链',
		),
		'lengthName' =>array(
				'1'=>'颈链',
				'2'=>'吊坠',
				'3'=>'毛衣链',
				'4'=>'毛衣链70',
		),
		'up_done_status'=>array(
			'0'=>'处理中',
			'1'=>'打印制作中',
			'2'=>'已发快递',
			'4'=>'用户已签收',	
		),
		
		'process'=>array(
			'1'=>'您提交了订单,系统正在生成3D文件',
			'2'=>'您的订单文件正在检查,下一步送往3D打印中心制作',
			'3'=>'您的订单正在3D打印中',
			'4'=>'您的订单已3D打印完成，正在后期处理',
			'5'=>'您的订单正在打包中',
			'6'=>'您的订单已发货',
			'7'=>'订单交易完成',
			'8'=>'感谢您在IGNITE定制个性化产品，欢迎再次光临',
		),

        'processSimple'=>array( //用于显示在用户订单order中
            '1'=>'正在3D打印',
            '2'=>'正在3D打印',
            '3'=>'正在3D打印',
            '4'=>'正在3D打印',
            '5'=>'正在3D打印',
            '6'=>'等待收货',
            '7'=>'已购买',
            '8'=>'已购买',
        ),

    'express_com'=>array(
			'shunfeng'=>'顺丰速运',
			'shentong'=>'申通',
			'debangwuliu'=>'德邦物流',
			'huitongkuaidi'=>'百世汇通',
			'ems'=>'EMS',
			'tiandihuayu'=>'华宇物流',
			'tiantian'=>'天天快递',
			'yuantong'=>'圆通快递'			
		),
		
		'pubtext'=>array(
			'menu'=>array(
				'0'=>'首页',
				'1'=>'3D模型',
				'2'=>'3D打印模型',
				'3'=>'3D学院',
			),
			'searchinput'=>'搜索3D模型、资源、创意设计...',
			'search'=>'搜索',
			'user'=>array(
				'0'=>'新用户注册',
				'1'=>'登录',
				'2'=>'积分',
				'3'=>'金额',
				'4'=>'退出',
				'5'=>'购物车',
			),
			'helptext'=>array(
				'0'=>'如何获得3D模型',
				'1'=>'如何上传',
				'2'=>'交易流程',
				'3'=>'积分制度',
				'4'=>'更多信息',
			),
			
			'footer_article'=>array(
				'0'=>'下载免费3D模型',
				'1'=>'购买3D模型',
				'2'=>'模型分享规范',
				'3'=>'出售模型规格',
				'4'=>'出售协议',
				'5'=>'交易流程',
				'6'=>'如何提现',
				'7'=>'获取积分',
				'8'=>'消费积分',
				'9'=>'关于我们',
				'10'=>'版权声明',
				'11'=>'意见反馈',
			),
			'footer'=>array(
				'0'=>'©2013 Bitmap3D  上海思柯其软件有限公司',
				'1'=>'隐私政策 ',
				'2'=>'网站地图',
				'3'=>'沪ICP备08005943号-1',
			)
			
		),
		
		'DB_CONN_ERROR' => '数据库连接失败', 
		'CONN_TIMEOUT' => '连接超时', 
		
		'POST_VALUE_ERROR' => '页面传值错误', 
		'ITEM_NOT_EXIST' => '当前项不存在', 
		'SAVE_SUCCESS' => '保存成功', 
		'ACTION_SUCCESS' => '操作成功', 
		'ACTION_FAIL' => '操作失败', 
		
		'ACCOUNT_UNIT'=>'元',//用户账户余额单位
		
		'geo_type' => array(
				array('key' => '未知', 'value' => '0'), 
				array('key' => 'Polygonal', 'value' => '1'), 
				array('key' => 'NURBS', 'value' => '2'), 
				array('key' => 'Subdivision', 'value' => '3'), 
				array('key' => 'Polygonal Quads/Tris', 'value' => '4'), 
				array('key' => 'Polygonal Quads only', 'value' => '5'), 
				array('key' => 'Polygonal Tris only', 'value' => '6'), 
				array('key' => 'Polygonal Ngons used', 'value' => '7')), 
		
		'unwrappeduvs_type' => array(
				array('key' => '请选择', 'value' => '0'),
				array('key' => 'Unknown', 'value' => '1'), 
				array('key' => 'Yes, non-overlapping', 'value' => '2'), 
				array('key' => 'Yes, overlapping', 'value' => '3'), 
				array('key' => 'Mixed', 'value' => '4'), 
				array('key' => 'No', 'value' => '5')),
				
		'diamond_style'=> array( //定义镶钻类的宝石样式值对应webgl中的显示ID
            'diamond_common_white'  =>85,
            'diamond_common_red'    =>86,
            'diamond_common_blue'   =>87,
            'diamond_common_pink'   =>88,
            'diamond_common_yellow' =>89,
            'diamond_common_green'  =>90,
        ),
        'pendant_style'=> array( //定义吊坠的样式值对应webgl中的显示ID
            'pendant_style_water_drop'   =>1,
            'pendant_style_circle'       =>2,
            'pendant_style_heart'        =>3,
        )
);

	
?>