<?php
/**
 * 首页类
 *
 * @author miaomin 
 * Jul 8, 2013 1:34:10 PM 
 */
class CartAction extends CommonAction {
	private $UrlMyFavor = "/user.php/myfavor/add/id/";
	private $UrlHome = "/cart/";
	private $UrlProduct = "/models/detail/pid";
	private $UrlDelete = "/cart/delete/pid";
	private $UrlPay = "/cart/pay";
	public function __construct() {
		parent::__construct ();
		$this->UrlMyFavor = WEBROOT_PATH . $this->UrlMyFavor;
		$this->UrlHome = U ( $this->UrlHome );
		$this->UrlProduct = U ( $this->UrlProduct );
		$this->UrlDelete = U ( $this->UrlDelete );
		$this->UrlPay = U ( $this->UrlPay );
		// echo $this->UrlPay;
		$this->assign ( 'U_MyFavor', $this->UrlMyFavor );
		$this->assign ( 'U_Home', $this->UrlHome );
		$this->assign ( 'U_Product', $this->UrlProduct );
		$this->assign ( 'U_Delete', $this->UrlDelete );
		$this->assign ( 'U_Pay', $this->UrlPay );
		$this->assign ( 'U_MyFavor', $this->UrlMyFavor );
		$cart = cookie ( 'user_product_cart' );
		if ($this->_isLogin () && isset ( $cart )) {
			$this->moveCookieToDB ();
		}
	}
	
	/**
	 * 首页
	 */
	/**
	 * miaomin edited @2014.11.8
	 *
	 * 处理实体打印模型
	 */
	public function index() {
		$type = I('type','','string');
		$mobileAgent_session=$_SESSION ['mobileAgent'];
		if($mobileAgent_session!==3){
			$mmode =1;
		}else{
			$mmode = I ( "mmode", 0, 'intval' );
		}

        $curMenu=I('curMenu');
        $delresult = I ( "delresult", 0, 'intval' );
		$this->assign ( "delresult", $delresult );
		//var_dump($this->DBF->Product);
		$this->assign ( 'DBF_P', $this->DBF->Product );

		// -----------------------------------zhangzhibin
		$SA = A ( "User/Sales" ); // 调用user分组下的sales模块
		$temp_orderid = $SA->get_umorderid ();
		$oid = $SA->encode_pass ( $temp_orderid, $_SESSION ['f_userid'] ); // 加密orderid
		$this->assign ( 'oid', $oid );
		$jurl = pub_encode_pass ( I ( 'jurl' ), "3dcity", "decode" );
		// var_dump($mmode);
		
		/*****************新增参数glx**********************************************************************/
		//--新增参数--start//
		//$con=array('28381','30051','30998','27587','28260','30854','30715','28203');

		//192.168.52.19上用这个--
		$res = M('help_doc')->where("cate =25")->select();
		$con=explode(' ',$res[0]['intro']);
		//192.168.52.19上用这个

		//www.ignjewelry.com上用这个--
		//$res = M('help_doc')->where("cate =13")->select();
		//$con=explode(' ',$res[0]['content']);
		//www.ignjewelry.com上用这个--

		foreach($con as $k=>$v){
			$info[$k]=D('product')->where("p_id=$v")->select();
		}
		$arr=array_rand($info,4);
		foreach($arr as $k=>$v){
			$info1[$k]=$info[$v];
		}
		$this->assign('info1',$info1);
		/*****************新增参数glx******************************************************************/


		$this->assign ('type', $type);
        $this->assign ( 'curMenu', $curMenu );
        $this->assign ( 'mmode', $mmode );
		$this->assign ( 'jurl', $jurl );
		// -----------------------------------zhangzhibin
		$this->_renderPage ();

	}
	// ----------------------------------------zhangzhibin
	/**
	 * miaomin edited @2014.11.10
	 *
	 * @return void boolean
	 */
	public function pay() 	// 购物车到支付
	{
		//dump($_POST);
		//exit;
		// miaomin edited start
		import ( 'App.Model.CartItem.AbstractCartItem' );
		import ( 'App.Model.CartItem.FactoryCartItemModel' );
		import ( 'App.Model.CartItem.CartItemRealPrintModel' );
		import ( 'App.Model.CartItem.CartItemVirtualPrintModel' );
		import ( 'App.Model.CartItem.CartItemNoneDiyModel' );
		import ( 'App.Model.CartItem.CartItemDiyModel' );
		$producttypeArr = C ( 'PRODUCT.TYPE' );
		$productrealArr = C ( 'PRODUCT.ISREAL' );
		// miaomin edited end

		/*****************新增参数glx**********************************************************************/
		//$con=array('28381','30051','30998','27587','28260','30854','30715','28203');

		//192.168.52.19上用这个--
		$res = M('help_doc')->where("cate =25")->select();
		$con=explode(' ',$res[0]['intro']);
		//192.168.52.19上用这个

		//www.ignjewelry.com上用这个--
		//$res = M('help_doc')->where("cate =13")->select();
		//$con=explode(' ',$res[0]['content']);
		//www.ignjewelry.com上用这个--

		foreach($con as $k=>$v){
			$info[$k]=D('product')->where("p_id=$v")->select();
		}
		$arr=array_rand($info,4);
		foreach($arr as $k=>$v){
			$info1[$k]=$info[$v];
		}
		$this->assign('info1',$info1);
		/*****************新增参数glx**********************************************************************/

		// init order parameter start
		$oid = I ( "oid", 0, string );    //加密后的订单ID,如果没有$oid数据异常则报错退出
		if (!$oid) {
			$this->error ( '很抱歉！', $this->UrlHome );
			return false;
		}
		$UID = $_SESSION ['f_userid'];
		$accountpost_url = WEBROOT_URL . "/user.php/order/getpayresult/";
		$posttype = I ( "posttype", 0, intval );
		$SA = A ( "User/Sales" ); // 调用user分组下的sales模块
		$up_orderid = $SA->encode_pass ( $oid, $UID, "decode" ); // 解密后的订单ID
		$payaction = I ( "payaction", 0, intval ); // 支付操作：0来自购物车 1来自订单
		$mmode = I ( "mmode", 0, intval ); // 接收post过来的mmode参数，1为手机模式
		$mtype = I('mtype','','string');
		$up_type = I ( "up_type", 0, intval );
		$uaid = I ( "uaid", 0, intval );  //收货地址ID如果有此ID则订单显示为默认收货地址
		$up_clearcart = I ( "up_clearcart", 0, intval );  //是否清空购物车
		$TotalPrice = 0;  //支付总价
		$up_express = 1;  //是否需要快递 1表示需要
		$up_efee = C ( 'UP_EFEE' );   //初始化运费
		// init order parameter end

		// 这里先做一个判断是第一次发起订单还是二次发起订单
		$UPM = new UserPrepaidModel ();
		$UPM_info = $UPM->getPrepaidListByOrderid ( $up_orderid );
		if ($UPM_info){
			/* -- 在订单表中已经保存数据的处理方式   开始--------------------------------- */
			$TotalPrice = $UPM_info [0] ['up_amount'];
			$upid = $UPM_info [0] ['up_id'];
			$up_amount_account = $UPM_info [0] ['up_amount_account'];
			$show_account = 0;
			if (! $up_amount_account == 0) {
				$show_account = 1;
				$up_amount_total = $UPM_info [0] ['up_amount_total'];
				$this->assign ( 'amount_coupon', $UPM_info [0] ['up_amount_coupon'] );
				$this->assign ( 'show_account', $show_account );
				$this->assign ( 'up_amount_account', $up_amount_account );
				$this->assign ( 'up_amount_total', $up_amount_total );
			}
			/* -- 在订单表中已经保存数据的处理方式   结束--------------------------------- */
		}else{
			//exit;
			/* -- 处理购物车中的订单数据   开始----------------------------------------- */
			$UCM = new UserCartModel ();
			$ProductList = $UCM->getProduct ( $UID ); // 购买的模型集合
			//dump($ProductList);
			//var_dump($ProductList);
			/* -- 如果是首次生成订单这里的数据处理后将会直接保存入数据库但是如果是重新发起提交则这部分逻辑不用再次执行 -- */
			$ProductList = $UCM->processPay2OrderProducts($ProductList);
			foreach ($ProductList as $key=>$val){
				$TotalPrice += $val['p_price'] * $val['p_count'];
				$pid_array [] = $val['p_id'] . "," . $val['p_count']; // product的id数组
			}

			/******清理购物车******/
			$UCM->clearCart($UID);
			/************/

			/* -- 处理购物车中的订单数据   结束----------------------------------------- */
			$up_product_info = serialize ( $ProductList ); // 存储到订单商品快照中的商品信息
			$this->assign ( "ProductList", $ProductList );
			$up_amount_save = $TotalPrice;   // 存入订单表的订单金额
			$IP = get_client_ip ();
			$UPD = new UserPrepaidDetailModel ();
			$UPM->startTrans (); // 在d模型中启动事务
			$upid = $UPM->addRecord ( $UID, $up_amount_save, $IP, 0, $up_orderid, 0, serialize ( $pid_array ), $up_type, $up_express );
			$upd_id = $UPD->addRecord ( $upid, $up_product_info );
			if ($upid && $upd_id) {
				$UPM->commit (); // 提交事务
			} else {
				$UPM->rollback (); // 事务回滚
			}
		}
		// <<------------------------------------获取用户有效优惠券
		/* miaomin disabled@2015.10.27 优惠券全部手动填写，不再提供点选
		$Coupon = new CouponModel ();
		$couponlist = $Coupon->getcodes($ProductList);
		$this->assign ( 'couponlist', $couponlist );
		*/
		$Account = M('user_prepaid');
		$Account->getByUp_orderid($up_orderid);
		$paystatus = ($Account->up_amount_account != 0) ? true : false;
		$this->assign ( 'paystatus', $paystatus );
		// ------------------------------------>>
		// <<----------------------------------------获取用户可用余额
		$Users = new UsersModel ();
		$Users->find ( $UID );
		$UA = $Users->getUserAcc ();
		$this->assign ( 'userAcc_rmb_js', $UA->u_rcoin_av );
		// ------------------------------------------------->>
		// <<----------------------------------------支付方式
		$PT = new PayTypeModel ();
		$paytype_arr = $PT->get_paytype ( $mmode );
		$this->assign ( 'pt_arr', $paytype_arr );
		// ------------------------------------------------->>
		// <<---------------------------------------//返回用户是否有地址
		$HA = new UserAddressModel ();
		$HaveAddress = $HA->getAddressByUserID ( $UID );
		$isaddr = $HaveAddress ? 1 : 0;
		if ($isaddr) { // 如果用户有地址，获得用户的默认地址用于在订单中显示
			$UserDefaultAddress = $HA->getDefaultAddressByUserID ( $UID );
			//dump($UserDefaultAddress);
			$this->assign ( "UserDefaultAddress", $UserDefaultAddress );
		}
		$this->assign ( "isaddr", $isaddr );
		// ------------------------------------------------->>
		$AddressList = $HA->getAddressByUserID ($UID,'address');
		$AIPM = new AreaInfoPickerModel ();
		$AddressList = $this->setDispArea ( $AddressList, $AIPM );
		$this->assign ( 'HtmlCtrl', $this->getHtmlCtrl ( $AIPM ) );

		$this->assign ( 'AddrssList', $AddressList );
		$AddressID = $this->getID ();
		if ($AddressID) {
			$AddressList = array_column ( $AddressList, null, $HA->F->ID );
			if (! $AddressList [$AddressID]) {
				return $this->error ( '当前地址不存在或已被删除', U ( 'User/address' ) );
			}
			$this->assign ( 'Post', $this->mapAddressPost ( $HA->F, $AddressList [$AddressID] ) );
		}
		// <<---------------------------------------//获取地址选择表单
		$addressfrom = $this->get_chooseaddress ( $UID, $uaid, $mmode, $oid );
		//dump($addressfrom);
		$this->assign ( 'addressfrom', $addressfrom );
		// ------------------------------------------------->>

		// 重新获取ProductList
		$OD = $UPM->processOrder2payProducts($up_orderid,$UID);
//		echo "<pre/>";
//		print_r($OD['detail']);
		$this->assign ( 'orderdetail', $OD );
		$this->assign ( 'up_express', $up_express );
		$this->assign ( 'up_efee', $up_efee );
		$this->assign ( 'u_id', $UID );
		$this->assign ( 'showoid', $up_orderid );
		$this->assign ( 'temp_oid', $oid );
		$this->assign ( 'totalprice', $TotalPrice );
		$this->assign ( 'oid', $oid );
		$this->assign ( "payaction", $payaction );
		$this->assign ( "mmode", $mmode );
		$this->assign ( "mtype", $mtype );
//		dump($mtype);
//		exit;
		$this->assign ( 'up_type', $up_type );

		// <<---------------------------------------------------账户余额支付--------------
		$accountpay = I ( "accountpay", 0, 'intval' );
		if ($accountpay == 1) {
			$data = array (
				"payquery" => 1,
				"uid" => $UID,
				"oid" => $up_orderid,
				"payprice" => $TotalPrice
			);
			$imginfo = $this->postBycurl ( $data, $accountpost_url );
			if ($imginfo == 1) {
				$this->success ( "支付成功！", "__DOC__/user.php/sales/orderdetail/orderid/" . $oid . "" );
			} else {
				$this->success ( "订单支付失败！", "__DOC__/user.php/sales/orderdetail/orderid/" . $oid . "" );
			}
		}
		// ---------------------------------------------------账户余额支付-------------->>
		if (($mmode == 1) && (!$isaddr)) {   // 手机模式并且用户无地址
			redirect ( WEBROOT_URL . "/user.php/iwxuser/addressadd/orderid/" . $oid . "" );
		}
		$mmode ? $this->_renderPage ("./App/templates/User/micky/Cart/paymob.html") : $this->_renderPage ("./App/templates/User/micky/Cart/pay.html");
	}
	
	/**
	 * 此方法是订单支付的入口
	 * 2015/3/4
	 *
	 * @return boolean
	 */
	public function pay_goalipay() {

	    // 需要发票
	    if($this->_post('needpb') != NULL){
	        $data ['u_id'] = $this->_session ( 'f_userid' );
	        $data ['up_orderid'] = $this->_post ( 'up_orderid_' );
	        $data ['status'] = 1;	        
	        $data ['asktime'] = get_now ();
	        
	        $Userbill = M ( 'user_paybill' );
	        if ($this->_post ( 'no_iv' ) == "1_") {
	            $data ['billtype'] = 1;
	            $data['billcompany'] = "0";
	        } elseif ($this->_post ( 'no_iv' ) == "2_") {
	            $data ['billtype'] = 2;
	            $data ['billcompany'] = $this->_post ( 'iv_company' );
	        } else {
	            $Userbill->find ( $this->_post ( 'no_iv' ) );
	            $data ['billtype'] = 2;
	            $data['billcompany'] = $Userbill->billcompany;
	        }
	        $arr = M('user_paybill')->where("up_orderid = '{$this->_post('up_orderid_')}' AND status='1'")->select();
	        if($arr == NULL){
	            $Userbill->add($data);
	        }
	        else{
	            $Userbill->where("id = '{$arr[0]['id']}'")->save($data);
	        }
	    }
		if (IS_POST) {
			$mmode = I('mmode',0,'intval');
			$oid = I ( 'up_orderid', 0, 'string' );
			$uaid = I ( 'addressid', 0, 'intval' );
			$up_type = I ( 'up_type', 0, 'intval' );
			$order_bz = I ( 'order_bz', '', 'string' );
			$pay_useraccount = I ( "pay_useraccount", 0, intval );
			
			$SA = A ( "User/Sales" ); // 调用user分组下的sales模块
			$up_orderid = $SA->encode_pass ( $oid, $_SESSION ['f_userid'], "decode" ); // orderid
			
			$UP = new UserPrepaidModel ();
			$up_info = $UP->getPrepaidListByOrderid ( $up_orderid );
			
			$up_amount = $up_info [0] ['up_amount'] + $up_info [0] ['up_efee'];
			
			$UID = session ( 'f_userid' );
			$UCM = new UserCartModel ();
			$ProductList = $UCM->getProduct ( $UID ); // 购买的模型集合
			$paytypeid = I ( 'paytype', 0, 'int' );
			
			// <<---------------------------------------------------获得支付方式
			$PT = new PayTypeModel ();
			$paytype_arr = $PT->get_paytypeByPtid ( $paytypeid );
			$paym = $paytype_arr [0] ['paymethodcode'];
			$dbank = $paytype_arr [0] ['bankcode'];
			
			// ----------------------------------------------------获得支付方式-->>

			// ------------------------账户余额抵扣部分款项---------------start-<<<
			// var_dump($up_info);
			$up_amount_account = intval ( $up_info [0] ['up_amount_account'] );
			$up_amount_coupon = $up_info [0] ['up_amount_coupon']; // 优惠券字段
			$TU = D ( "Users" );
			$UA = new UserAccountModel ();
			$UserAccountInfo = $UA->getUserAccountByUid ( $_SESSION ['f_userid'] );

			if ($pay_useraccount && ! $up_amount_account && $up_amount_coupon == 0) { // 使用余额支付并且该订单的up_amount_account不为0
				if ($UserAccountInfo ['u_rcoin_av'] >= intval ( $up_info [0] ['up_amount'] )) { // 如果余额大于等于订单金额
					$accountpost_url = WEBROOT_URL . "/user.php/order/getpayresult/";
					$data = array (
							"payquery" => 1,
							"uid" => $_SESSION ['f_userid'],
							"oid" => $up_orderid,
							"payprice" => $up_amount 
					);
					$imginfo = $this->postBycurl ( $data, $accountpost_url );
					if ($imginfo == 1) {
						$this->success ( "支付成功！", "__DOC__/user.php/sales/orderdetail/orderid/" . $oid . "" );
					} else {
						$this->success ( "订单支付失败！", "__DOC__/user.php/sales/orderdetail/orderid/" . $oid . "" );
					}
					$paytypeid = 153;
				} else { // 如果余额小于订单金额
					$data_up ['up_amount_account'] = $UserAccountInfo ['u_rcoin_av'];
					$data_up ['up_amount_total'] = $up_amount;
					$up_amount = $up_amount - $UserAccountInfo ['u_rcoin_av'];
					$data_up ['up_amount'] = $up_amount;
					$up_status = $UP->updateAmountByOrderid ( $up_orderid, $data_up, $UserAccountInfo );
				}
			}
			header ( "Content-type: text/html; charset=utf-8" );
			$UP->updatePaytypeByOrderid ( $up_orderid, $paytypeid, $uaid, $order_bz ); // 更新支付方式和收货地址
			foreach ( $ProductList as $Product ) {
				$buy_pname .= $Product [$this->DBF->Product->Name]; // product的名称数组
			}
			$buyNameArr = $this->getAlipayProdNameArr ( $up_info [0] ['up_type'], $up_info [0] ['up_express'] );
			
			// 根据支付方式正式开始走支付流程
			if ($paytype_arr [0] ['paygateway'] == 'alipay') { // 支付方式网关为支付宝
				/**
				 * 更新orderid新增orderid_new保证可重复支付
				 */
				$up_orderid=$this->update_orderid($up_orderid);
				if($mmode){
					$alipay_order = A ( "User/Orderpage" ); // 调用order模块
					$alipay_order->alipayPage ( $up_orderid, $up_amount, $buyNameArr ['productName'], $buyNameArr ['productNameDes'], $paym, $dbank);
				}else{
					$alipay_order = A ( "User/Order" ); // 调用order模块
					$alipay_order->alipayto ( $up_orderid, $up_amount, $buyNameArr ['productName'], $buyNameArr ['productNameDes'], $paym, $dbank);
				}
			} elseif ($paytype_arr [0] ['paygateway'] == 'wxpay_web') { // 网页版微信支付，显示二维码
			    /**
                 * miaomin added@2015.8.26 start
                 * 更新orderid保证重复支付可以被微信接受
			     */
			    $up_orderid=$this->update_orderid($up_orderid);
				$wxpay_order = A ( "User/Wxpay" );
				$wxpay_order->wxpayshow ( $up_orderid, $up_amount, $buyNameArr ['productName'] );
			} elseif ($paytype_arr [0] ['paygateway'] == 'wxpay_m') { // 手机版微信支付
				$up_orderid=$this->update_orderid($up_orderid);

				/*
                $up_orderid=$this->update_orderid($up_orderid);
                $wxpay_order = A ( "User/Wxpay" );
                $wxpay_order->wxpayshow ( $up_orderid, $up_amount, $buyNameArr ['productName'] );
                */
				redirect ( WEBROOT_URL . "/user.php/wxuser/sendbill/orderid/" . $oid . "" );
			} elseif ($paytype_arr [0] ['paygateway'] == 'cmbpay') { // 招商银行直付
			                                                         // miaomin
			                                                         // added@2015.3.2
			                                                         // 加载Cmbsdk操作类
				import ( 'Common.Cmbsdk', APP_PATH, '.php' );
				$CMBPAY = new Cmbsdk ();
				// 生成招行用订单号和时间戳
				$cmbpaydate = date ( 'Ymd' );
				$timestampArr = explode ( ' ', microtime () );
				$cmbpayorderid = substr ( $timestampArr [1], - 8 ) . substr ( $timestampArr [0], 2, 2 );
				// 更新招行用订单号和时间戳
				$cmbpaydata = array (
						'up_cmbpay_date' => $cmbpaydate,
						'up_cmbpay_orderid' => $cmbpayorderid
				);
				$UP->where ( "up_orderid='" . $up_orderid . "'" )->setField ( $cmbpaydata );
				// 获取最新的订单信息
				$updatePrepaid = $UP->where ( "up_orderid='" . $up_orderid . "'" )->find();
				// 如果已有折扣了则不再享受额外的折扣
				if ($updatePrepaid['up_amount_coupon'] > 0){
					// 记录LOG
					$LOGCOUPON = new LogCouponModel ();
					$condition = array(
							'log_orderid' => $up_orderid,
							'log_eccode' => $CMBPAY->getCouponCode()
					);
					$logRes = $LOGCOUPON->where($condition)->count();
					if ($logRes == 1){
						$LOGCOUPON->where($condition)->delete();
					}
					$logData = array (
							'log_eccode' => $CMBPAY->getCouponCode(),
							'log_ecamount' => 0,
							'log_orderid' => $up_orderid,
							'log_usedate' => get_now ()
					);
					$LOGCOUPON->add ( $logData );
					// 跳转招行支付
					if (($_REQUEST ['mmode']) && ($up_amount)) {
						echo $CMBPAY->genTestNetPayWapForm ( $cmbpayorderid, $updatePrepaid['up_amount'], $cmbpaydate );
					} else {
						echo $CMBPAY->genTestNetPayForm ( $cmbpayorderid, $updatePrepaid['up_amount'], $cmbpaydate );
					}
				}else{
					// 没有折扣
					// 记录LOG
					$LOGCOUPON = new LogCouponModel ();
					$condition = array(
							'log_orderid' => $up_orderid,
							'log_eccode' => $CMBPAY->getCouponCode()
					);
					$logRes = $LOGCOUPON->where($condition)->count();
					if ($logRes == 1){
						$LOGCOUPON->where($condition)->delete();
					}
					$logData = array (
							'log_eccode' => $CMBPAY->getCouponCode(),
							'log_ecamount' => $CMBPAY->calcDiscountPrice($updatePrepaid['up_amount']),
							'log_orderid' => $up_orderid,
							'log_usedate' => get_now ()
					);
					$LOGCOUPON->add ( $logData );
					// 跳转招行支付
					if (($_REQUEST ['mmode']) && ($up_amount)) {
						echo $CMBPAY->genTestNetPayWapForm ( $cmbpayorderid, $CMBPAY->calcDiscountAmount($updatePrepaid['up_amount']), $cmbpaydate );
					} else {
						echo $CMBPAY->genTestNetPayForm ( $cmbpayorderid, $CMBPAY->calcDiscountAmount($updatePrepaid['up_amount']), $cmbpaydate );
					}
				}
			}
			exit ();
		} else {
			$this->error ( 'Error!', $this->UrlHome );
			return false;
		}
	}
	private function getAlipayProdNameArr($uptype, $express) { // 根据订单类型获取购买物品名称
		if ($uptype == 4) {
			$result ['productName'] = '定制首饰';
			$result ['productNameDes'] = '个性化定制饰品';
		} else {
			if ($express) {
				$result ['productName'] = '商品';
				$result ['productNameDes'] = '在线购买';
			} else {
				$result ['productName'] = '模型';
				$result ['productNameDes'] = '模型文件';
			}
		}
		return $result;
	}
	
	// ----------------------------------------zhangzhibin
	
	/**
	 * 获取购物车数量
	 *
	 * @author miaomin
	 * @return mixed
	 */
	public function cartnum() {
		$UID = session ( 'f_userid' );
		$UCM = new UserCartModel ();
		$res = $UCM->getCartNum ( $UID );
		if ($res === false) {
			echo '{"isSuccess":false,"Message":""}';
			return;
		}
		if ($res === null) {
			echo '{"isSuccess":false,"Message":""}';
			return;
		}
		echo '{"isSuccess":true,"result":"' . $res . '"}';
	}
	public function addProduct() {




        $PID = $this->getProductID ();
		if (! $PID) {
			return false;
		}
		$isAdded = false;
		if ($this->_isLogin ()) {
			$UID = session ( 'f_userid' );
			$UCM = new UserCartModel ();
			$isAdded = $UCM->addProduct ( $PID, $UID );
		} else {
			$isAdded = $this->addProductCookie ( $PID );
		}
		
		if ($isAdded === false) {
			echo '{"isSuccess":false,"Message":"添加失败"}';
			return;
		}
		if ($isAdded === null) {
			echo '{"isSuccess":false,"Message":"当前产品不存在"}';
			return;
		}
		echo '{"isSuccess":true}';
	}
	
	/**
	 * 重写删除购物车项
	 *
	 * @author miaomin
	 *         2014-11-6
	 *        
	 */
	public function removeitem() {
		import ( 'App.Model.CartItem.AbstractCartItem' );
		import ( 'App.Model.CartItem.FactoryCartItemModel' );
		import ( 'App.Model.CartItem.CartItemRealPrintModel' );
		import ( 'App.Model.CartItem.CartItemVirtualPrintModel' );
		import ( 'App.Model.CartItem.CartItemNoneDiyModel' );
		import ( 'App.Model.CartItem.CartItemDiyModel' );
		
		// ThinkPHP的I方法是3.1.3版本新增的
		$UID = I ( 'session.f_userid', 0, 'int' );
		$UCID = I ( 'get.itemid', 0, 'int' );
		$MMODE = I ( 'get.mmode', 0, 'int' );
		$TYPE = I('get.type',0,'string');

		$isRemoved = null;
		
		if ($UCID && $UID) {
			
			$UCM = new UserCartModel ();
			$ucmRes = $UCM->find ( $UCID );
			
			if ($ucmRes) {
				
				$initData = array (
						'pid' => $ucmRes [$UCM->F->ProductID],
						'isreal' => $ucmRes [$UCM->F->IsReal],
						'itemid' => $UCID 
				);
				
				$CIF = FactoryCartItemModel::init ( $initData );
				$CIF->transMap ( $initData );
				
				$isRemoved = $UCM->removeItem ( $CIF, $UID );
			}
		}
		
		// $this->success ( '删除成功', $this->UrlHome );
		// var_dump ( $isRemoved );
		if ($MMODE) {
			if($TYPE == 'iwx'){
				redirect ( $this->UrlHome . '/index/mmode/1/type/iwx' );
			}
			redirect ( $this->UrlHome . '/index/mmode/1' );
		} else {
			redirect ( $this->UrlHome );
		}
	}
	
	/**
	 * 删除购物车项(旧)
	 *
	 * @return void boolean
	 */
	public function delete() {
		$mmode = I ( "mmode", 0, intval );
		$type = I('type','','string');
		$PID = $this->getProductID ();
		if (! $PID) {
			$this->success ( '删除失败', $this->UrlHome );
			return false;
		}
		$isDeleteded = false;
		if ($this->_isLogin ()) {
			$UID = session ( 'f_userid' );
			$UCM = new UserCartModel ();
			$isDeleteded = $UCM->deleteProduct ( $PID, $UID );
		} else {
			$isDeleteded = $this->addProductCookie ( $PID );
		}
		if ($isDeleteded === false) {
			$this->success ( '删除失败', $this->UrlHome );
			return;
		}
		if ($mmode == 1) {
			if($type == 'iwx'){
				redirect ( WEBROOT_URL . "/user.php/cart/index/mmode/1/type/iwx/delresult/1" );
			}else{
				redirect ( WEBROOT_URL . "/user.php/cart/index/mmode/1/delresult/1" );
			}
		} else {
			// $this->success ( '删除成功', $this->UrlHome );
		    redirect ( WEBROOT_URL . "/user.php/cart" );
		}
	}
	private function getProductID() {
		$pvc = new PVC2 ();
		$pvc->setModeGet ();
		$pvc->isInt ()->validateMust ()->add ( 'pid' );
		if ($pvc->verifyAll ()) {
			return $pvc->ResultArray ['pid'];
		} else {
			return false;
		}
	}
	private function addProductCookie($PID) {
		$ProductList = $this->getProductCookie ();
		if (isset ( $ProductList [$PID] )) {
			return true;
		}
		$ProductList [$PID] = 1;
		return $this->setProductCookie ( $ProductList );
	}
	private function deleteProductCookie($PID) {
		$ProductList = $this->getProductCookie ();
		if (! isset ( $ProductList [$PID] )) {
			return true;
		}
		unset ( $ProductList [$PID] );
		return $this->setProductCookie ( $ProductList );
	}
	private function getProductCookie() {
		$cart = cookie ( 'user_product_cart' );
		return isset ( $cart ) ? unserialize ( $cart ) : array ();
	}
	private function setProductCookie($ProductList) {
		$cart = cookie ( 'user_product_cart', serialize ( $ProductList ), 2592000 );
		if ($cart === false) {
			return false;
		}
		return true;
	}
	private function moveCookieToDB() {
		$UID = session ( 'f_userid' );
		if (! $UID) {
			return;
		}
		$ProductList = $this->getProductCookie ();
		$UCM = new UserCartModel ();
		$UCM->startTrans ();
		foreach ( $ProductList as $PID => $Count ) {
			if (! $UCM->addProduct ( $PID, $UID )) {
				$UCM->rollback ();
				return;
			}
		}
		cookie ( 'user_product_cart', null );
		$UCM->commit ();
	}
	
	/*
	 * 通过curl传数据
	 */
	function postBycurl($post_data, $post_url) {
		$curl = curl_init ();
		// var_dump($post_url);
		curl_setopt ( $curl, CURLOPT_URL, $post_url );
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $post_data );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $curl, CURLOPT_USERAGENT, "Mozilla/4.0" );
		$result = curl_exec ( $curl );
		$error = curl_error ( $curl );
		$errorinfo = curl_getinfo ( $curl );
		// var_dump ( $errorinfo );
		// exit;
		curl_close ( $curl );
		
		if ($result == 1) {
			// $this->redirect("__DOC__/user.php/sales/orderdetail/up_id/".$post_data['oid']."");
			// exit;
			// $this->success("订单支付成功.","__DOC__/user.php/sales/orderdetail/up_id/".$post_data['oid']."");
		} elseif ($result == 2) {
			$this->error ( "订单已经支付过.", "__DOC__/user.php/sales/orderdetail/up_id/" . $post_data ['oid'] . "" );
		} elseif ($result == 3) {
			$this->error ( "账户金额不足.", "__APP__" );
		} elseif ($result == 5) {
			$this->error ( "系统异常，未完成.", "__APP__" );
		} else {
			// var_dump ( $result );
		}
		return $error ? $error : $result;
	}
	private function getAddressPost() {
		$PVC = new PVC2 ();
		$PVC->setModePost ()->setStrictMode ( false );
		$PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'province' );
		$PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'city' );
		$PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'region' );
		$PVC->isString ()->validateMust ()->add ( 'address' );
		// $PVC->isString()->validateMust()->add('zipcode');
		$PVC->isString ()->validateNotNull ()->add ( 'mobile' );
		// $PVC->isString()->validateNotNull()->add('phonepre');
		// $PVC->isString()->validateNotNull()->add('phone');
		// $PVC->isString()->validateNotNull()->add('phoneext');
		$PVC->isString ()->validateExists ()->add ( 'defaultaddress' );
		if (! $PVC->verifyAll ()) {
			return false;
		}
		// if(!isset($PVC->ResultArray['mobile']) &&
		// !isset($PVC->ResultArray['phone'])) { return false; }
		return $PVC->ResultArray;
	}
	private function bulidAddressData($Post) {
		$UAM = new UserAddressModel ();
		$UAM->create ();
		if ($Post ['id']) {
			$UAM->{$UAM->F->ID} = $Post ['id'];
		} else {
			$UAM->{$UAM->F->UserID} = $_SESSION ['f_userid'];
		}
		if (! $Post ['defaultaddress']) {
			$UAM->{$UAM->F->IsDefault} = 0;
		}
		return $UAM;
	}
	private function getDispArea(AreaInfoPickerModel $AIPM, $Province, $City, $Region) {
		$Result .= $AIPM->getItemNameByID ( $Province ) . ' ';
		$Result .= $AIPM->getItemNameByID ( $City ) . ' ';
		$Result .= $AIPM->getItemNameByID ( $Region );
		return $Result;
	}
	public function getHtmlCtrl(AreaInfoPickerModel $AIPM) {
		$HtmlCtrl = array ();
		$HtmlCtrl ['AreaInfo'] = $AIPM->getJsonAreaInfo ();
		$HtmlCtrl ['AreaChildIndex'] = $AIPM->getJsonChildIndex ();
		return $HtmlCtrl;
	}
	private function setDispArea(array $AddressList, AreaInfoPickerModel $AIPM) {
		$F = new DBF_UserAddress ();
		foreach ( $AddressList as &$Address ) {
			$Province = $Address [$F->Province];
			$City = $Address [$F->City];
			$Region = $Address [$F->Region];
			$Address ['area_disp'] = $this->getDispArea ( $AIPM, $Province, $City, $Region );
		}
		
		return $AddressList;
	}
	public function get_chooseaddress($uid, $uaid = 0, $mmode = 0, $orderid) { // 获得选择收货地址的表单
		$UAddress = M ( "user_address" )->field ( "ua_id,ua_addressee,ua_province,ua_city,ua_region,ua_address,ua_zipcode,ua_mobile,ua_phonepre,ua_isdefault" )->where ( "u_id=" . $uid . " and ua_isremove=0")->select ();
		/*$AIPM = new AreaInfoPickerModel ();
		$address_list = "<table class='selectaddress'><tbody>";
		$address_list .= "<tr class=h><th> </th><th>收件人</th><th>所在地</th><th>详细地址</th><th>手机号码</th><th>默认地址</th></tr>";
		$Address_default_disp = L ( 'RES_ADDRESS_DEFAULT' );
		foreach ( $UAddress as $key => $v ) {
			$address_list .= "<tr><td>";
			if ($uaid) {
				if ($v ['ua_id'] == $uaid) {
					$address_list .= "<input type='radio' name='addressid' value='" . $v ['ua_id'] . "' checked=checked/>";
				}
			} else {
				if ($v ['ua_isdefault'] == 1) {
					$address_list .= "<input type='radio' name='addressid' value='" . $v ['ua_id'] . "' checked=checked/>";
				} else {
					$address_list .= "<input type='radio' name='addressid' value='" . $v ['ua_id'] . "'/>";
				}
			}
			$address_list .= "</td>";
			$address_list .= "<td>" . $v ['ua_addressee'] . "</td><td>" . $AIPM->getarea ( $v ['ua_province'], $v ['ua_city'], $v ['ua_region'] ) . "</td><td>" . $v ['ua_address'] . "</td>";
			// $address_list.="<td>".$v['ua_zipcode']."</td><td>".$v['ua_mobile']."&nbsp;".$v['ua_phonepre']."</td><td>".$Address_default_disp[$v['ua_isdefault']]."</td></tr>";
			$address_list .= "<td>" . $v ['ua_mobile'] . "&nbsp;" . $v ['ua_phonepre'] . "</td><td>" . $Address_default_disp [$v ['ua_isdefault']] . "</td></tr>";
		}
		$address_list .= "<tr><td></td><td colspan='5'>";
		if (! $mmode) {
			$address_list .= "<a href='#' onclick=initfrm('newaddress')>使用新地址</a>";
		} else {
			$address_list .= "<a href=" . __DOC__ . "/user.php/wxuser/addressadd/orderid/" . $orderid . "/mmode/1>使用新地址</a>";
		}
		$address_list .= "</td></tr>";

		$address_list .= "</tbody></table>";*/
		return $UAddress;
	}
	private function getID() {
		$PVC = new PVC2 ();
		$PVC->setModeGet ();
		$PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'id' );
		if (! $PVC->verifyAll ()) {
			return false;
		}
		return $PVC->ResultArray ['id'];
	}
	public function addInvoice() {
		// var_dump($this->_post());
		if ($this->isPost ()) {
			$condition ['billtype'] = 2;
			$condition ['up_orderid'] = $this->_post ( 'up_orderid' );
			$data ['u_id'] = $this->_session ( 'f_userid' );
			$data ['up_orderid'] = $this->_post ( 'up_orderid' );
			$data ['billtype'] = 2;
			$data ['status'] = 0;
			$Paybill = M ( 'user_paybill' );
			$data ['billcompany'] = $this->_post ( 'company' );
			$Paybill->add ( $data );
			/*
			 * elseif($this->_post('iv_type')==1){
			 * if(count($Paybill->where($condition)->select()) == 0){
			 * $Paybill->add($data); }
			 * elseif(count($Paybill->where($condition)->select()) == 1){
			 * $Paybill->where($condition)->save($data); } else{ die("非法操作"); }
			 * } else{ die('非法参数'); } //var_dump($data);
			 */
		} else {
			$this->error ( "请求错误" );
		}
	}
	public function showInvoice() {
		$Paybill = M ( "user_paybill" );
		// $condition['up_orderid'] = $this->_post('up_orderid');
		$condition ['u_id'] = $this->_session ( 'f_userid' );
		$condition ['billtype'] = 2;
		//$list = $Paybill->where ( $condition )->select ();
		$list = $Paybill->query("select billcompany,id from tdf_user_paybill where u_id='{$_SESSION['f_userid']}' and billtype='2' group by billcompany;");
		// var_dump($list);
		foreach ( $list as $i => $a ) {
			$data.="<input name=\"no_iv\" type=\"radio\" value=\"".$a['id']."\"  class=\"pers\" checked=\"checked\"/>".$a['billcompany']."<br/>";
		}
		echo $data;
	}
	public function delinvoice($id) {
		$UP = M ( 'user_paybill' );
		$uid = session ( 'f_userid' );
		if ($UP->where ( "id = '{$id}' and u_id = '{$uid}'" )->delete ()) {
			echo "OK";
		} else {
			echo "none";
		}
		
		echo $UP->getLastSql ();
	}
	
	// ---------------------------------------加密、解密函数--------------------------start----
	// *********by zhangzhibin***************
	// *********20130709 ***************
	public function encode_pass($tex, $key, $type = "encode") {
	    $chrArr = array (
	        'a',
	        'b',
	        'c',
	        'd',
	        'e',
	        'f',
	        'g',
	        'h',
	        'i',
	        'j',
	        'k',
	        'l',
	        'm',
	        'n',
	        'o',
	        'p',
	        'q',
	        'r',
	        's',
	        't',
	        'u',
	        'v',
	        'w',
	        'x',
	        'y',
	        'z',
	        'A',
	        'B',
	        'C',
	        'D',
	        'E',
	        'F',
	        'G',
	        'H',
	        'I',
	        'J',
	        'K',
	        'L',
	        'M',
	        'N',
	        'O',
	        'P',
	        'Q',
	        'R',
	        'S',
	        'T',
	        'U',
	        'V',
	        'W',
	        'X',
	        'Y',
	        'Z',
	        '0',
	        '1',
	        '2',
	        '3',
	        '4',
	        '5',
	        '6',
	        '7',
	        '8',
	        '9'
	    );
	    if ($type == "decode") {
	        if (strlen ( $tex ) < 14)
	            return false;
	        $verity_str = substr ( $tex, 0, 8 );
	        $tex = substr ( $tex, 8 );
	        if ($verity_str != substr ( md5 ( $tex ), 0, 8 )) {
	            // 完整性验证失败
	            return false;
	        }
	    }
	    $key_b = $type == "decode" ? substr ( $tex, 0, 6 ) : $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62];
	    $rand_key = $key_b . $key;
	    $rand_key = md5 ( $rand_key );
	    $tex = $type == "decode" ? base64_decode ( substr ( $tex, 6 ) ) : $tex;
	    $texlen = strlen ( $tex );
	    $reslutstr = "";
	    for($i = 0; $i < $texlen; $i ++) {
	        $reslutstr .= $tex {$i} ^ $rand_key {$i % 32};
	    }
	    if ($type != "decode") {
	        $reslutstr = trim ( $key_b . base64_encode ( $reslutstr ), "==" );
	        $reslutstr = substr ( md5 ( $reslutstr ), 0, 8 ) . $reslutstr;
	    }
	    return $reslutstr;
	}
	// $psa=encode_pass("phpcode","taintainxousad");
	// echo $psa;
	// echo encode_pass($psa,"taintainxousad",'decode');
	// ---------------------------------------加密、解密函数--------------------------end----
	
	/*
	 * 更新订单号
	 * @param $orderid 订单号
	 * @return 新的订单号
	 */
	private function update_orderid($orderid){
	    $UPM=new UserPrepaidModel();
	    if($orderid){
	        $orderid_new=$UPM->updateOrderSuffix($orderid);
	    }
	    return $orderid_new;
	}
}

