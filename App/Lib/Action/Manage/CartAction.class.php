<?php
/**
 * 首页类
 *
 * @author miaomin 
 * Jul 8, 2013 1:34:10 PM
 */
class CartAction extends CommonAction {
	private $UrlMyFavor = "/user.php/myfavor/add/id/";
	private $UrlHome = "Cart/index";
	private $UrlProduct = "front/models/detail/pid";
	private $UrlDelete = "front/cart/delete/pid";
	private $UrlPay = "front/cart/pay";
	public function __construct() {
		parent::__construct ();
		$this->UrlMyFavor = WEBROOT_PATH . $this->UrlMyFavor;
		$this->UrlHome = U ( $this->UrlHome );
		$this->UrlProduct = U ( $this->UrlProduct );
		$this->UrlDelete = U ( $this->UrlDelete );
		$this->UrlPay = U ( $this->UrlPay );
		$this->assign ( 'U_MyFavor', $this->UrlMyFavor );
		$this->assign ( 'U_Home', $this->UrlHome );
		$this->assign ( 'U_Product', $this->UrlProduct );
		$this->assign ( 'U_Delete', $this->UrlDelete );
		$this->assign ( 'U_Pay', $this->UrlPay );
		$this->assign ( 'U_MyFavor', $this->UrlMyFavor );
		$cart = cookie ( 'user_product_cart' );
		// if($this->_isLogin() && isset($cart)) { $this->moveCookieToDB(); }
	}
	
	/**
	 * 首页
	 */
	public function index() {
		$myinfo = $this->_session ( 'my_info' );
		$UID = $myinfo ['aid'];
		
		$UCM = new UserCartModel ();
		$ProductList = $UCM->getProduct ( $UID );
		
		/*
		 * $ProductList = $this->getProductCookie (); pr ( $ProductList );
		 */
		
		$this->assign ( 'totalcount', count ( $ProductList ) );
		$this->assign ( 'filelist', $ProductList );
		$this->display ();
	}
	
	// ----------------------------------------zhangzhibin
	public function pay() 	// 购物车到支付
	{
		$UID = session ( 'f_userid' );
		$UCM = new UserCartModel ();
		$ProductList = $UCM->getProduct ( $UID ); // 购买的模型集合
		$TotalPrice = 0;
		
		// var_dump($ProductList);
		foreach ( $ProductList as $Product ) {
			$TotalPrice += $Product [$this->DBF->Product->Price];
			$pid_array [] = $Product [$this->DBF->Product->ID]; // product的id数组
		} // 总金额
		
		if (IS_POST) {
			$oid = $_POST ['oid'];
			$SA = A ( "User/Sales" ); // 调用user分组下的sales模块
			$enoid = $SA->encode_pass ( $oid, $_SESSION ['f_userid'], "decode" ); // orderid
			$up_orderid = $enoid;
			$up_amount_save = $TotalPrice / C ( 'CRATE' );
			$IP = get_client_ip ();
			$UPM = new UserPrepaidModel ();
			$UPM_info = $UPM->getPrepaidListByOrderid ( $up_orderid );
			if (! $UPM_info) {
				$UPM->addRecord ( $UID, $up_amount_save, $IP, 0, $up_orderid, 0, serialize ( $pid_array ), 1 );
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
			$this->_renderPage ();
		} else {
			$this->error ( '很抱歉！', $this->UrlHome );
			return false;
		}
		//分配模板值，使优惠券验证码3次错误后出现
		$this->assign("errnum",session('errnum'));
		
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
			$alipay_order = A ( "User/Order" ); // 调用order模块
			$alipay_order->alipayto ( $up_orderid, $up_amount, "购买模型", $buy_pname, $paym, $dbank );
		} else {
			$this->error ( 'Error!', $this->UrlHome );
			return false;
		}
	}
	
	// ----------------------------------------zhangzhibin miaomin
	public function addProduct() {
		$YF_ID = $this->_get ( 'yf_id' );
		$PMA_ID = $this->_get ( 'pma_id' );
		
		$isAdded = false;
		if ($this->checkLogin ()) {
			$myinfo = $this->_session ( 'my_info' );
			$UID = $myinfo ['aid'];
			$UCM = new UserCartModel ();
			$isAdded = $UCM->addProduct ( $this->_get (), $UID );
		} else {
			// 商品
			$isAdded = $this->addProductCookie ( $YF_ID, $PMA_ID );
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
	 * 从购物车中删除
	 */
	public function delete() {
		$PMA_ID = $this->_get ( 'pma_id' );
		$YF_ID = $this->_get ( 'yf_id' );
		$isDeleteded = false;
		if ($this->checkLogin ()) {
			$myinfo = $this->_session ( 'my_info' );
			$UID = $myinfo ['aid'];
			$UCM = new UserCartModel ();
			$isDeleteded = $UCM->deleteProduct ( $YF_ID, $PMA_ID, $UID );
		} else {
			$isDeleteded = $this->deleteProductCookie ( $YF_ID, $PMA_ID );
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
	
	/**
	 * 非登录用户加入购物车记入Cookie中
	 *
	 * @param int $YFID        	
	 * @param int $PMAID        	
	 * @return boolean
	 */
	private function addProductCookie($YFID, $PMAID) {
		$ProductList = $this->getProductCookie ();
		if (isset ( $ProductList [$YFID . '_' . $PMAID] )) {
			return true;
		}
		$ProductList [$YFID . '_' . $PMAID] = 1;
		return $this->setProductCookie ( $ProductList );
	}
	private function deleteProductCookie($YFID, $PMAID) {
		$ProductList = $this->getProductCookie ();
		if (! isset ( $ProductList [$YFID . '_' . $PMAID] )) {
			return true;
		}
		unset ( $ProductList [$YFID . '_' . $PMAID] );
		return $this->setProductCookie ( $ProductList );
	}
	
	/**
	 * 向Cookie获取购物车信息
	 * 
	 * @return Ambigous <multitype:, mixed>
	 */
	private function getProductCookie() {
		$cart = cookie ( 'user_product_cart' );
		return isset ( $cart ) ? unserialize ( $cart ) : array ();
	}
	
	/**
	 * 向Cookie记入购物车
	 *
	 * @param array $ProductList        	
	 * @return boolean
	 */
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
}