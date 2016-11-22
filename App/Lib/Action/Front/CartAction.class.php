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
	public function index() {
		$mmode=I("mmode",1,'intval');
		
		$UID = session ( 'f_userid' );
		$UCM = new UserCartModel ();
	
		$Users = new UsersModel ();
		$Users->find ( $_SESSION ['f_userid'] );
		
		$ProductList = $UCM->getProduct ( $UID );
		$TotalPrice = 0;
		foreach ( $ProductList as $Product ) {
			$TotalPrice += $Product [$this->DBF->Product->Price];
		}
		
		$this->assign ( 'ProductList', $ProductList );
		$this->assign ( 'TotalPrice', $TotalPrice );
		$this->assign ( 'DBF_P', $this->DBF->Product );
		
		// -----------------------------------zhangzhibin
		$SA = A ( "User/Sales" ); // 调用user分组下的sales模块
		$temp_orderid = $SA->get_umorderid ();
		$oid = $SA->encode_pass ( $temp_orderid, $_SESSION ['f_userid'] ); // 加密orderid
		
		
		$this->assign ( 'oid', $oid );
		
		// -----------------------------------zhangzhibin
		
		$this->_renderPage();
		
	}
	
	// ----------------------------------------zhangzhibin
	public function pay() 	// 购物车到支付
	{
		$accountpost_url = WEBROOT_URL . "/user.php/order/getpayresult/";
		// echo $accountpost_url;
		$UID = session ( 'f_userid' );
		$UCM = new UserCartModel ();
		$ProductList = $UCM->getProduct ( $UID ); // 购买的模型集合
		$TotalPrice = 0;

		foreach ( $ProductList as $Product ) {
			$TotalPrice += $Product [$this->DBF->Product->Price];
			$pid_array [] = $Product [$this->DBF->Product->ID]; // product的id数组
		} // 总金额
		  // var_dump($TotalPrice);
		if (IS_POST) {
			$oid = $_POST ['oid'];
			
			$SA = A ( "User/Sales" ); // 调用user分组下的sales模块
			$enoid = $SA->encode_pass ( $oid, $_SESSION ['f_userid'], "decode" ); // orderid
			$up_orderid = $enoid;
			$up_amount_save = $TotalPrice;
			$IP = get_client_ip ();
			$UPM = new UserPrepaidModel ();
			$UPM_info = $UPM->getPrepaidListByOrderid ( $up_orderid );
			if (! $UPM_info) {
				$upid = $UPM->addRecord ( $UID, $up_amount_save, $IP, 0, $up_orderid, 0, serialize ( $pid_array ), 1 );
			} else {
				$upid = $UPM_info [0] ['up_id'];
			}
			$this->assign ( 'u_id', $UID );
			$this->assign ( 'showoid', $enoid );
			$this->assign ( 'temp_oid', $oid );
			$this->assign ( 'totalprice', $TotalPrice );
			// $alipay_order = new OrderAction ();
			// $alipay_order->alipayto ( $up_orderid, $up_amount );
			// <<----------------------------------------支付方式
			$PT = new PayTypeModel ();
			$paytype_arr = $PT->get_paytype ();
			$this->assign ( 'pt_arr', $paytype_arr );
			// ------------------------------------------------->>
			$accountpay = I ( "accountpay", 0, int );
			if ($accountpay == 1) {
				$data = array (
						"payquery" => 1,
						"uid" => $_SESSION ['f_userid'],
						"oid" => $enoid,
						"payprice" => $TotalPrice 
				);
				// $this->redirect(__DOC__."/user.php/");
				$imginfo = $this->postBycurl ( $data, $accountpost_url );
				if ($imginfo == 1) {
					$this->success ( "支付成功！", "__DOC__/user.php/sales/orderdetail/up_id/" . $upid . "" );
				} else {
					$this->success ( "订单支付失败！", "__DOC__/user.php/sales/orderdetail/up_id/" . $upid . "" );
				}
				// redirect("__DOC__/user.php/sales/orderdetail/up_id/".$upid."");
				// exit;
			}
			$this->_renderPage ();
		} else {
			$this->error ( '很抱歉！', $this->UrlHome );
			return false;
		}
	}
	public function pay_goalipay() {
		if (IS_POST) {
			$oid = I ( 'up_orderid', 0, 'string' );
			$up_amount = $_POST ['up_amount'];
			$SA = A ( "User/Sales" ); // 调用user分组下的sales模块
			$up_orderid = $SA->encode_pass ( $oid, $_SESSION ['f_userid'], "decode" ); // orderid
			
			$UID = session ( 'f_userid' );
			$UCM = new UserCartModel ();
			$ProductList = $UCM->getProduct ( $UID ); // 购买的模型集合
			$paytypeid = I ( 'paytype', 0, 'int' );
			
			// <<---------------------------------------------------获得支付方式
			$PT = new PayTypeModel ();
			$paytype_arr = $PT->get_paytypeByPtid ( $paytypeid );
			$paym = $paytype_arr [0] ['paymethodcode'];
			$dbank = $paytype_arr [0] ['bankcode'];
			// ---------------------------------------------------获得支付方式-->>
			
			$UP = new UserPrepaidModel ();
			$UP->updatePaytypeByOrderid ( $up_orderid, $paytypeid ); // 更新支付方式
			
			foreach ( $ProductList as $Product ) {
				$buy_pname .= $Product [$this->DBF->Product->Name]; // product的名称数组
			}
			
			$buy_pname = "购买模型文件:" . $buy_pname;
			header ( "Content-type: text/html; charset=utf-8" );
			
			$alipay_order = A ( "User/Order" ); // 调用order模块
			$alipay_order->alipayto ( $up_orderid, $up_amount, "购买模型", $buy_pname, $paym, $dbank );
		} else {
			$this->error ( 'Error!', $this->UrlHome );
			return false;
		}
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
	
	/**
	 * 增加一个商品去购物车(新)
	 *
	 * @author miaomin
	 *        
	 *         2014.11.03
	 *        
	 * @param -- $_REQUEST必须包含pid
	 *        	-- 如果是打印件类型则必须要包含isreal参数
	 *        	
	 *        	-- 1:虚拟打印件
	 *        	-- 2:实物打印件
	 *        	
	 * @return void boolean
	 */
	public function additem() {
		
		// 需要用户登录的判断
		import ( 'App.Model.CartItem.AbstractCartItem' );
		import ( 'App.Model.CartItem.FactoryCartItemModel' );
		import ( 'App.Model.CartItem.CartItemRealPrintModel' );
		import ( 'App.Model.CartItem.CartItemVirtualPrintModel' );
		import ( 'App.Model.CartItem.CartItemNoneDiyModel' );
		import ( 'App.Model.CartItem.CartItemDiyModel' );
		
		// 处理主商品
		$CIF = FactoryCartItemModel::init ( $_GET );
		$CIF->transMap ( $_GET );
		
		$UCM = new UserCartModel ();
		$addMainItemRes = $UCM->addItem ( $CIF, session ( 'f_userid' ) );
		
		// 处理捆绑商品
		if (($addMainItemRes) && ($_GET ['bindids'] != '')) {
			$bindIdsArr = explode ( ',', $_GET ['bindids'] );
			if (is_array ( $bindIdsArr )) {
				foreach ( $bindIdsArr as $key => $val ) {
					// 构造一个捆绑商品的初始化信息结构
					$slaveItemArr = array (
							'pid' => $val,
							'isreal' => 1,
							'count' => $_GET['count'],
							'masterid' => $_GET ['pid'],
							'handleuc' => $addMainItemRes
					);
					$CIF = FactoryCartItemModel::init ( $slaveItemArr );
					$CIF->transMap ( $slaveItemArr );
					$addSlaveItemRes = $UCM->addItem ( $CIF, session ( 'f_userid' ) );
				}
			}
		}
		
		if ($addMainItemRes || $addSlaveItemRes){
            $data['code']=I('code');
           if($data['code']){
               $data['ip']=get_client_ip();
               $data['url']=__SELF__ ;
               $data['type']=1;
               $data['sessionid']=session_id();
               M('log_active')->add($data);
           }
			echo '{"isSuccess":true}';
		}else{
			echo '{"isSuccess":false}';
		}
	}
	
	/**
	 * 试算价格
	 *
	 * @author miaomin
	 *        
	 *         2014.11.17
	 *        
	 * @param -- $_REQUEST必须包含pid
	 *        	-- 如果是打印件类型则必须要包含isreal参数
	 *        	
	 *        	-- 1:虚拟打印件
	 *        	-- 2:实物打印件
	 *        	
	 * @return void boolean
	 */
	public function calc() {
		import ( 'App.Model.CartItem.AbstractCartItem' );
		import ( 'App.Model.CartItem.FactoryCartItemModel' );
		import ( 'App.Model.CartItem.CartItemRealPrintModel' );
		import ( 'App.Model.CartItem.CartItemVirtualPrintModel' );
		import ( 'App.Model.CartItem.CartItemDiyModel' );
		
		$CIF = FactoryCartItemModel::init ( $_REQUEST );
		$CIF->transMap ( $_REQUEST );
		
		$calcRes = $CIF->calcprice ();
		
		// echo $_REQUEST['jsoncallback'] . '({"isSuccess":true, "Message":"' .
		// $calcRes . '"})';
		
		echo '{"isSuccess":true, "Message":"' . $calcRes . '"}';
	}
	
	// test json format
	public function jformat() {
		$calcRes = '622.08';
		
		$res = array (
				'isSuccess' => true,
				'Message' => $calcRes 
		);
		
		// echo '{"isSuccess":true, "Message":"' . $calcRes . '"}';
		
		// echo json_encode ( $res );
		
		$this->ajaxReturn ( $calcRes, 'OK', 1 );
	}
	
	// test count how often the words occurs in the text
	public function jcount() {
		$text = "What is the cause of this warning: 'Warning: Cannot modify header information - headers already sent', and what is a good practice to prevent it?";
		
		$words = utf8_str_word_count ( $text, 1 );
		
		$frequency = array_count_values ( $words );
		
		arsort ( $frequency );
		
		echo $text;
		
		echo '<br><br>';
		
		print_r ( $frequency );
	}
	
	/**
	 * 增加一个商品去购物车
	 *
	 * @return void boolean
	 */
	public function addProduct() {
		$isAdded = false;
		
		$PID = $this->getProductID ();
		
		if (! $PID) {
			
			return false;
		}
		
		// 随着购物车里商品类型的日益增多这样的方式维护起来会变得很麻烦
		if ($this->_isLogin ()) {
			
			$UID = session ( 'f_userid' );
			$UCM = new UserCartModel ();
			$isAdded = $UCM->addProduct ( $PID, $UID );
		} else {
			
			$isAdded = $this->addProductCookie ( $PID );
		}
		// echo $PID;
		// echo $UID;
		// var_dump($isAdded);
		// exit;
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
	public function delete() {
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
		$this->success ( '删除成功', $this->UrlHome );
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
		// var_dump($errorinfo);
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
			var_dump ( $result );
		}
		return $error ? $error : $result;
	}
	

}