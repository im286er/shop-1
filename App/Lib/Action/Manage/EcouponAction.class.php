<?php
/**
 * 优惠券模块
 *
 * @author miaomin
 * Sep 23, 2013 4:58:05 PM
 */
class EcouponAction extends CommonAction {
	
	/**
	 * 首页
	 */
	public function index() {
	    $Ecoupon = M("ecoupon");
	    $count = $Ecoupon->count();
	    import('ORG.Util.Page');
	    $Page = new Page($count,15);
	    $show = $Page->show();
	    $list = $Ecoupon->limit($Page->firstRow.','.$Page->listRows)->select();
	    $this->assign('data',$list);
	    $this->assign('page',$show);
	    
	    $this->display();
	}
	
	/**
	 * Blocklist
	 */
	public function blocklist() {
		$md5 = '6cdf6d923a9404d010c14595c30dc368';
		$sha1 = '5b57b632ec30df8e2bf75f597edad2ecfd5d24cb';
		$BFM = new BlockFilesModel ();
		$res = $BFM->getBlockList ( $md5, $sha1 );
		pr ( $res );
	}
	
	/**
	 * Blockmerge
	 */
	public function blockmerge() {
		$md5 = '6cdf6d923a9404d010c14595c30dc368';
		$sha1 = '5b57b632ec30df8e2bf75f597edad2ecfd5d24cb';
		$BFM = new BlockFilesModel ();
		$res = $BFM->mergeBlockList ( $md5, $sha1, 8 );
		pr ( $res );
	}
	
	/**
	 * 日志用
	 */
	public function log() {
		try {
			$log = LogFactoryModel::init ( 'client' );
			$data = array (
					'u_id' => 1 
			);
			$res = $log->insertLog ( $data );
			if ($res) {
				// 记录日志成功
			} else {
				// 失败
			}
		} catch ( Exception $e ) {
			echo $e->getMessage ();
			exit ();
		}
	}
	
	/**
	 * 测试用
	 */
	public function visa() {
		$username = 'miaomin';
		$password = strtoupper ( md5 ( 'gdi+2012' ) );
		$ciphertxt = base64_encode ( $username . ' ' . $password );
		echo $ciphertxt;
	}
	
	/**
	 * 解压缩ZIP
	 */
	public function zip() {
		$filename = './524fac306d6d4.zip';
		$res = extractZip ( $filename );
		var_dump ( $res );
	}
	
	/**
	 * 解压缩RAR
	 */
	public function rar() {
		/*
		 * $filename = './524fac306d6d4.rar'; $rar_file = rar_open ( $filename )
		 * or die ( "Can't open Rar archive" ); $entries = rar_list ( $rar_file
		 * ); var_dump ( $entries );
		 */
	}
	public function pospos() {
		$subject = '1-1';
		$pattern = '/^[1-9]*-[1-9]*$/';
		$res2 = preg_match ( $pattern, $subject );
		dump ( $res2 );
	}
	public function hash() {
		$file = './uploads/yun/6c/df/6d/6cdf6d923a9404d010c14595c30dc368/5b57b632ec30df8e2bf75f597edad2ecfd5d24cb.rar';
		echo md5_file ( $file );
		echo '<br><br>';
		echo sha1_file ( $file );
	}
	
	/**
	 * 找回密码
	 */
	public function findpwd() {
		$user = 'wow730@gmail.com';
		$pass = md5 ( 'wow730' );
		$visa = base64_encode ( $user . ' ' . $pass );
		
		// 配置信息
		$CFG_PUBLIC_KEY = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
		$CFG_REMOTE_HOST = "http://192.168.52.72/3DPrinter/";
		// $CFG_REMOTE_HOST = "http://www.ab3d.net/";
		// CURL调用地址
		$remote_url = $CFG_REMOTE_HOST . "api.php/services/rest";
		
		// @formatter:off
		// $curlPost = 'format=' . urlencode ( 'json' ) . '&method=' . urlencode ( 'users.getuserinfo' ) . '&visa=' . urlencode ( 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==' ) . '';
	
		$curlPost = 'method=' . 'users.findpwd' . '&visa=' . $visa . '&format=' . 'xml' . '';
		// @formatter:on
		
		$vcode = genvcode ( 1, 28 );
		$sign = gensign ( $curlPost, $vcode, $CFG_PUBLIC_KEY );
		
		//
		$curlPost = array (
				'method' => 'users.findpwd',
				'visa' => $visa,
				'format' => 'xml',
				'vcode' => $vcode,
				'sign' => $sign 
		);
		
		// CURL
		$ch = curl_init ();
		curl_setopt_array ( $ch, array (
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $remote_url,
				CURLOPT_POSTFIELDS => $curlPost 
		) );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		
		// 这句话不能拿掉啊拿掉就返回不到结果啦！！！
		print_r ( $response );
	}
	
	/**
	 * 用户注册
	 */
	public function login() {
		$user = 'wow730@gmail.com';
		$pass = md5 ( 'wow730' );
		$visa = base64_encode ( $user . ' ' . $pass );
		
		// 配置信息
		$CFG_PUBLIC_KEY = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
		$CFG_REMOTE_HOST = "http://192.168.52.72/3DPrinter/";
		// $CFG_REMOTE_HOST = "http://www.ab3d.net/";
		// CURL调用地址
		$remote_url = $CFG_REMOTE_HOST . "api.php/services/rest";
		
		// @formatter:off
		// $curlPost = 'format=' . urlencode ( 'json' ) . '&method=' . urlencode ( 'users.getuserinfo' ) . '&visa=' . urlencode ( 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==' ) . '';
	
		$curlPost = 'method=' . 'users.login' . '&visa=' . $visa . '&format=' . 'xml' . '';
		// @formatter:on
		
		$vcode = genvcode ( 1, 28 );
		$sign = gensign ( $curlPost, $vcode, $CFG_PUBLIC_KEY );
		
		//
		$curlPost = array (
				'method' => 'users.login',
				'visa' => $visa,
				'format' => 'xml',
				'vcode' => $vcode,
				'sign' => $sign 
		);
		
		// CURL
		$ch = curl_init ();
		curl_setopt_array ( $ch, array (
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $remote_url,
				CURLOPT_POSTFIELDS => $curlPost 
		) );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		
		// 这句话不能拿掉啊拿掉就返回不到结果啦！！！
		print_r ( $response );
	}
	
	/**
	 * 用户注册
	 */
	public function register() {
		$user = 'wow730@gmail.com';
		$pass = md5 ( 'wow730' );
		$visa = base64_encode ( $user . ' ' . $pass );
		
		// 配置信息
		$CFG_PUBLIC_KEY = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
		$CFG_REMOTE_HOST = "http://192.168.52.72/3DPrinter/";
		// $CFG_REMOTE_HOST = "http://www.ab3d.net/";
		// CURL调用地址
		$remote_url = $CFG_REMOTE_HOST . "api.php/services/rest";
		
		// @formatter:off
		// $curlPost = 'format=' . urlencode ( 'json' ) . '&method=' . urlencode ( 'users.getuserinfo' ) . '&visa=' . urlencode ( 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==' ) . '';
		
		$curlPost = 'method=' . 'users.register' . '&visa=' . $visa . '&format=' . 'xml' . '';
		// @formatter:on
		
		$vcode = genvcode ( 1, 28 );
		$sign = gensign ( $curlPost, $vcode, $CFG_PUBLIC_KEY );
		
		//
		$curlPost = array (
				'method' => 'users.register',
				'visa' => $visa,
				'format' => 'xml',
				'vcode' => $vcode,
				'sign' => $sign 
		);
		
		// CURL
		$ch = curl_init ();
		curl_setopt_array ( $ch, array (
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $remote_url,
				CURLOPT_POSTFIELDS => $curlPost 
		) );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		
		// 这句话不能拿掉啊拿掉就返回不到结果啦！！！
		print_r ( $response );
	}
	
	/**
	 * 模拟提交
	 *
	 * //TODO
	 *
	 * 如果传送参数有问题需要考虑URL_ENCODE
	 */
	public function cpost() {
		// 配置信息
		$CFG_PUBLIC_KEY = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
		$CFG_REMOTE_HOST = "http://192.168.52.72/3DPrinter/";
		// $CFG_REMOTE_HOST = "http://www.ab3d.net/";
		// CURL调用地址
		$remote_url = $CFG_REMOTE_HOST . "api.php/services/rest";
		
		// @formatter:off
		// $curlPost = 'format=' . urlencode ( 'json' ) . '&method=' . urlencode ( 'users.getuserinfo' ) . '&visa=' . urlencode ( 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==' ) . '';
		
		$curlPost = 'method=' . 'users.getuserinfo' . '&visa=' . 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==' . '&format=' . 'xml' . '';
		// @formatter:on
		
		$vcode = genvcode ( 1, 28 );
		$sign = gensign ( $curlPost, $vcode, $CFG_PUBLIC_KEY );
		
		//
		$curlPost = array (
				'method' => 'users.getuserinfo',
				'visa' => 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==',
				'format' => 'xml',
				'vcode' => $vcode,
				'sign' => $sign,
				'debug' => 0 
		);
		
		// CURL
		$ch = curl_init ();
		curl_setopt_array ( $ch, array (
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $remote_url,
				CURLOPT_POSTFIELDS => $curlPost,
				CURLOPT_USERAGENT => 'phpCurl-agent/1.0' 
		) );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		
		// 这句话不能拿掉啊拿掉就返回不到结果啦！！！
		print_r ( $response );
	}
	
	/**
	 * 快速上传块文件
	 */
	public function qcbpost() {
		for($i = 0; $i < 7; $i ++) {
			// 配置信息
			$CFG_PUBLIC_KEY = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
			$CFG_REMOTE_HOST = "http://127.0.0.1/3DPrinter/";
			// $CFG_REMOTE_HOST = "http://127.0.0.1/3DF/";
			// $CFG_REMOTE_HOST = "http://www.ab3d.net/";
			// CURL调用地址
			$remote_url = $CFG_REMOTE_HOST . "api.php/services/rest";
			
			// TODO
			// 可能会有中文字符的问题
			// $filename1 = 'C:\Users\miaomin\Desktop\因春花开\公开.rar';
			// $filename1 = iconv('UTF-8', 'GB2312', $filename1);
			// $filename1 = 'D:\Zend\WorkSpace\3DPrinter\public.rar';
			// $filename1 = 'D:\Zend\WorkSpace\3DPrinter\cut\123.007';
			$filename1 = 'D:\Zend\WorkSpace\3DPrinter\cut\123.00' . $i;
			// $filename1 = './public.rar';
			// $filename2 = './private.rar';
			
			$curlPost = 'method=' . 'models.upfile' . '&visa=' . 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==' . '&format=' . 'json' . '';
			// @formatter:on
			
			$vcode = genvcode ( 1, 28 );
			$sign = gensign ( $curlPost, $vcode, $CFG_PUBLIC_KEY );
			
			// 必须是以数组的方式才能提交文件哦
			$blockpos = $i + 1 . '-8';
			$curlPost = array (
					'method' => 'models.upfile',
					'visa' => 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==',
					'format' => 'xml',
					'filename1' => '@' . $filename1,
					// 'filename2' => '@' . $filename2,
					'vcode' => $vcode,
					'sign' => $sign,
					'md5' => '6cdf6d923a9404d010c14595c30dc368',
					'sha1' => '5b57b632ec30df8e2bf75f597edad2ecfd5d24cb',
					'uptype' => 2,
					'blockpos' => $blockpos,
					'targetname' => 'Div_123.rar',
					'targetext' => 'rar' 
			);
			
			// CURL
			$ch = curl_init ();
			curl_setopt_array ( $ch, array (
					CURLOPT_POST => 1,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => $remote_url,
					CURLOPT_POSTFIELDS => $curlPost 
			) );
			$response = curl_exec ( $ch );
			curl_close ( $ch );
			
			// 这句话不能拿掉啊拿掉就返回不到结果啦！！！
			print_r ( $response );
		}
	}
	
	/**
	 * Block模拟提交
	 */
	public function cbpost() {
		// 配置信息
		$CFG_PUBLIC_KEY = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
		$CFG_REMOTE_HOST = "http://127.0.0.1/3DPrinter/";
		// $CFG_REMOTE_HOST = "http://127.0.0.1/3DF/";
		// $CFG_REMOTE_HOST = "http://www.ab3d.net/";
		// CURL调用地址
		$remote_url = $CFG_REMOTE_HOST . "api.php/services/rest";
		
		// TODO
		// 可能会有中文字符的问题
		// $filename1 = 'C:\Users\miaomin\Desktop\因春花开\公开.rar';
		// $filename1 = iconv('UTF-8', 'GB2312', $filename1);
		// $filename1 = 'D:\Zend\WorkSpace\3DPrinter\public.rar';
		$filename1 = 'D:\Zend\WorkSpace\3DPrinter\cut\123.007';
		// $filename1 = './public.rar';
		// $filename2 = './private.rar';
		
		$curlPost = 'method=' . 'models.upfile' . '&visa=' . 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==' . '&format=' . 'json' . '';
		// @formatter:on
		
		$vcode = genvcode ( 1, 28 );
		$sign = gensign ( $curlPost, $vcode, $CFG_PUBLIC_KEY );
		
		// 必须是以数组的方式才能提交文件哦
		$curlPost = array (
				'method' => 'models.upfile',
				'visa' => 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==',
				'format' => 'xml',
				'filename1' => '@' . $filename1,
				// 'filename2' => '@' . $filename2,
				'vcode' => $vcode,
				'sign' => $sign,
				'md5' => '6cdf6d923a9404d010c14595c30dc368',
				'sha1' => '5b57b632ec30df8e2bf75f597edad2ecfd5d24cb',
				'uptype' => 2,
				'blockpos' => '8-8',
				'targetname' => 'Div_123.rar',
				'targetext' => 'rar' 
		);
		
		// CURL
		$ch = curl_init ();
		curl_setopt_array ( $ch, array (
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $remote_url,
				CURLOPT_POSTFIELDS => $curlPost 
		) );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		
		// 这句话不能拿掉啊拿掉就返回不到结果啦！！！
		print_r ( $response );
	}
	
	/**
	 * 文件模拟提交
	 */
	public function cfpost() {
		// 配置信息
		$CFG_PUBLIC_KEY = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
		$CFG_REMOTE_HOST = "http://127.0.0.1/3DPrinter/";
		// $CFG_REMOTE_HOST = "http://127.0.0.1/3DF/";
		// $CFG_REMOTE_HOST = "http://www.ab3d.net/";
		// CURL调用地址
		$remote_url = $CFG_REMOTE_HOST . "api.php/services/rest";
		
		// TODO
		// 可能会有中文字符的问题
		// $filename1 = 'C:\Users\miaomin\Desktop\因春花开\公开.rar';
		// $filename1 = iconv('UTF-8', 'GB2312', $filename1);
		// $filename1 = 'D:\Zend\WorkSpace\3DPrinter\public.rar';
		$filename1 = 'D:\12.zip';
		// $filename1 = './public.rar';
		// $filename2 = './private.rar';
		
		$curlPost = 'method=' . 'models.upfile' . '&visa=' . 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==' . '&format=' . 'json' . '';
		// @formatter:on
		
		$vcode = genvcode ( 1, 28 );
		$sign = gensign ( $curlPost, $vcode, $CFG_PUBLIC_KEY );
		
		// 必须是以数组的方式才能提交文件哦
		$curlPost = array (
				'method' => 'models.upfile',
				'visa' => 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==',
				'format' => 'xml',
				'filename1' => '@' . $filename1,
				// 'filename2' => '@' . $filename2,
				'vcode' => $vcode,
				'sign' => $sign 
		);
		
		// CURL
		$ch = curl_init ();
		curl_setopt_array ( $ch, array (
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $remote_url,
				CURLOPT_POSTFIELDS => $curlPost 
		) );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		
		// 这句话不能拿掉啊拿掉就返回不到结果啦！！！
		print_r ( $response );
	}
	
	/**
	 * 公钥私钥
	 */
	public function rsa() {
		try {
			$ming = 'Miss';
			
			Vendor ( 'Rsa.Rsa' );
			$RSA = new Rsa ();
			
			// $RSA->genKeyFile ();
			
			$res = $RSA->encoding ( $ming );
			echo $res;
			
			$res = $RSA->decoding ( $res );
			echo $res;
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 使用优惠码
	 */
	public function used() {
		try {
			if ($this->isPost ()) {
				$totalamount = $this->_post ( 'order_amount' );
				$orderid = $this->_post ( 'order_id' );
				$ecode = $this->_post ( 'ecoupon_code' );
				// 校验
				if (! empty ( $ecode )) {
					$Ecoupon = new EcouponModel ();
					$Ecoupon->helper->verifyEcouponValid ( $ecode );
				}
				$myinfo = $this->_session ( 'my_info' );
				$uid = $myinfo ['aid'];
				
				$EC_TYPE = C ( 'ECOUPON.ECOUPON_TYPE' );
				$EC_STATUS = C ( 'ECOUPON.ECOUPON_STATUS' );
				
				$Ecoupon->getByec_code ( $ecode );
				if ($Ecoupon->ec_type == $EC_TYPE ['AMOUNT']) {
					// 金额抵扣
					$saveamount = $Ecoupon->ec_amount;
				} else {
					// 折扣
					$saveamount = round ( $totalamount * (1 - $Ecoupon->ec_amount) );
				}
				
				if ($totalamount >= $saveamount) {
					$finalamount = $totalamount - $saveamount;
				} else {
					$finalamount = 0;
				}
				
				$data = array (
						'uid' => $uid,
						'ecoupon_id' => $Ecoupon->ec_id,
						'ecoupon_code' => $Ecoupon->ec_code,
						'ecoupon_type' => $Ecoupon->ec_type,
						'orderid' => $orderid,
						'totalamount' => $totalamount,
						'saveamount' => $saveamount,
						'finalamount' => $finalamount,
						'ec_status' => $EC_STATUS ['USED'],
						'ec_usecount' => $Ecoupon->ec_usecount + 1 
				);
				
				$Ecoupon->helper->usedEcoupon ( $data );
				
				$Log = new LogEcouponModel ();
				$Log->insertLog ( $data );
			}
			$this->display ();
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 生成优惠码
	 */
	public function generate() {
		try {
			if ($this->isPost ()) {
				$num = intval ( $this->_post ( 'ecoupon_num' ) );
				if (($num) && ($num <= 200)) {
					$Ecoupon = new EcouponModel ();
					//$Ecoupon->helper->verifyCreateEcoupon ( $this->_post () );
					
					for($i = 1; $i <= $num; $i ++) {
						$Ecoupon->helper->createEcoupon ( $this->_post());
					}
				}
				else {
					throw new Exception ( 'ecoupon_num_err' );
				}
			}
			$this->display ();
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	/*
	 * 使用优惠券
	 */
	
	public function recharge(){
	    $Ecoupon = M('ecoupon');
	    $condition['ec_code'] = $this->_get('id');
	    $data1['ec_status'] = 21;
	    $Ecoupon->where($condition)->save($data1);// or die('优惠码不存在');
	    $Ecoupon->where($condition)->find();
	    
	    $Log_ecoupon = M('log_ecoupon');

	    $data['ec_id'] = $Ecoupon->ec_id;
	    $data['ec_code'] = $Ecoupon->ec_code;
	    $data['u_id'] = $this->_session('f_userid');
	    $data['save_amount'] = $Ecoupon->ec_amount;
	    $data['eclog_usedate'] = get_now();
	    $Log_ecoupon->add($data);
	    
	    echo "#########";
	    
	    
	}
	
}
?>