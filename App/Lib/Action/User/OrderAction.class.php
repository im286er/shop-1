<?php
class OrderAction extends CommonAction {
	public function _initialize() {
		Vendor ( 'Alipay.Corefunction' );
		Vendor ( 'Alipay.Notify' );
		Vendor ( 'Alipay.Service' );
		Vendor ( 'Alipay.Submit' );
		Vendor ( 'Alipay.Alipayconfig' );
	}
	function alipayto($out_trade_no, $total_fee, $go_subject = "ignjewelry.com", $go_body = "ignjewelry.com 支付", $paym = "", $bankc = "") {
		//$this->alipay_close($out_trade_no);
		$out_trade_no=$this->update_orderid($out_trade_no);
	//var_dump($out_trade_no);
	//exit;
		$alipay_config = alicofings ();
		$total_jf = $total_fee;
		$subject = $go_subject;
		$body = $go_body;
		$paymethod = $paym;
		$defaultbank = $bankc;
		
		$anti_phishing_key = '';
		$exter_invoke_ip = '';
		$show_url = '';
		$extra_common_param = '';
		$royalty_type = "";
		$royalty_parameters = "";
		
		$parameter = array (
				"service" => "create_direct_pay_by_user",
				"payment_type" => "1",
				"partner" => trim ( $alipay_config ['partner'] ),
				"notify_url" => trim ( $alipay_config ['notify_url'] ),
				"return_url" => trim ( $alipay_config ['return_url'] ),
				"seller_email" => trim ( $alipay_config ['seller_email'] ),
				"out_trade_no" => $out_trade_no,
				"subject" => $subject,
				"body" => $body,
				"total_fee" => $total_fee,
				"paymethod" => $paymethod,
				"defaultbank" => $defaultbank,
				"anti_phishing_key" => $anti_phishing_key,
				"exter_invoke_ip" => $exter_invoke_ip,
				"show_url" => $show_url,
				"_input_charset" => trim ( strtolower ( $alipay_config ['input_charset'] ) ) 
		);
		
		$alipayService = new AlipayService ( $alipay_config );
		$html_text = $alipayService->create_direct_pay_by_user ( $parameter );
		// $this->assign('alipay', $html_text);
		// $this->assign('total_fee', $total_fee);
	}
	
	/*
	 * 功能：支付宝页面跳转同步通知页面 版本：3.3 日期：2012-07-23
	 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见vender下的alipay下的notify.php中的函数verifyReturn
	 */
	public function returnurl() { //
		$aliapy_config = alicofings ();
		$alipayNotify = new AlipayNotify ( $aliapy_config );
		$verify_result = $alipayNotify->verifyReturn ();
		
		$out_trade_no = $_GET ['out_trade_no']; // 获取订单号
		$trade_no = $_GET ['trade_no']; // 获取支付宝交易号
		$total_fee = $_GET ['total_fee']; // 获取总价格
		if ($verify_result) { // 验证成功
			$d = D ( 'User_prepaid' );
			$result = $d->where ( "up_orderid_new='" . $out_trade_no . "'" )->select (); // 查询对应order_id的记录
			$status = $result [0] ['up_status'];
			$up_amount = $result [0] ['up_amount'];
			$out_trade_no_orderid= $result [0] ['up_orderid'];
			if ($_GET ['trade_status'] == 'TRADE_FINISHED' || $_GET ['trade_status'] == 'TRADE_SUCCESS') {
				// -------------------------------------------更新支付状态和回单号、回单时间、
				// 并增加积分、日志、和已购买的模型^
				if ($status == 0 && $up_amount == $total_fee) {
					$UM = new UserAccountModel ();
					$UM->addLogAndRCoin ( $out_trade_no_orderid, $trade_no, $total_fee ); // 包含up_type为0,1,3 的操作判断
				}
				// -------------------------------------------更新支付状态和回单号、回单时间
				//日志
			}else{
				echo "trade_status=" . $_GET ['trade_status'];
				$this->assign ( 'msg', '充值失败!' );
			}
			
			echo "验证成功<br />";
			echo "trade_no=" . $trade_no;
			
			$pay_done_url = "/sales/payresult/otype/0/out_trade_no/" . $out_trade_no_orderid;
			$this->redirect ( $pay_done_url );
		} else {
			$this->assign ( 'msg', '验证失败' );
			echo "False";
		}
		
		// $this->display();
	}
	
	/*
	 * function testsave(){ $UM = new UserAccountModel();
	 * $out_trade_no="138683052809258109"; $trade_no="ceshi123456789";
	 * $total_fee=168; $UM->addLogAndRCoin($out_trade_no,$trade_no,$total_fee);
	 * }
	 */
	public function getpayresult(){
		$total_fee		= $_POST ['payprice'];
		$out_trade_no 	= $_POST ['oid'];
		$payresult 		= $_POST ['payquery'];
		$UID			= $_POST['uid'];
		
		
		// miaomin edited@2014.11.12
		// 扣款金额从数据库中获取
		//$UID = session ( 'f_userid' );
		$UPM = new UserPrepaidModel ();
		
		 $condition = array (
				$UPM->F->OrderID => $out_trade_no,
				$UPM->F->UserID => $UID 
		);  
		//$condition="up_orderid='".$out_trade_no."' and up_uid=".$UID."";
		$upmRes = $UPM->where( $condition )->find();
		if($upmRes){
			$db_total_fee = $upmRes [$UPM->F->Amount] + $upmRes [$UPM->F->Efee];
			if($db_total_fee == $total_fee) {
				//$UPM->updateAccountPaytypeByOrderid($out_trade_no);	//设置支付方式为余额支付
				$UM = new UserAccountModel ();
				$result = $UM->addLogAndRcoin_order ( $out_trade_no, $total_fee );
				echo $result;
			}
		}
		echo false;
	}
	
	/*
	 * 功能：支付宝服务器异步通知
	 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
	 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
	 */
	public function notifyurl() {
		$aliapy_config = alicofings ();
		$alipayNotify = new AlipayNotify ( $aliapy_config );
		$verify_result = $alipayNotify->verifyNotify();

		if ($verify_result) { // 验证成功
			$out_trade_no = $_POST ['out_trade_no']; // 获取订单号
			$trade_no = $_POST ['trade_no']; // 获取支付宝交易号
			$total_fee = $_POST ['total_fee']; // 获取总价格
			$d = D ( 'User_prepaid' );
			$result = $d->where ( "up_orderid_new='" . $out_trade_no . "'" )->select (); // 查询对应order_id的记录
			$status = $result [0] ['up_status'];
			$up_amount = $result [0] ['up_amount'];
			$out_trade_no_orderid= $result [0] ['up_orderid'];
			logResult ( "<br>" . date ( 'Y-m-d H:i:s', NOW_TIME ) . "执行异步通知，" .$resultSQL. $up_amount . "start-----<br>" );
			if ($_POST ['trade_status'] == 'TRADE_FINISHED' || $_POST ['trade_status'] == 'TRADE_SUCCESS') {
				// -------------------------------------------更新支付状态和回单号、回单时间、
				// 并增加积分、日志^
				if ($status == 0 && $up_amount == $total_fee) {
					$UM = new UserAccountModel ();
					$UM_result = $UM->addLogAndRCoin ( $out_trade_no_orderid, $trade_no, $total_fee );
					if ($UM_result) {
						logResult ( "<br>" . date ( 'Y-m-d H:i:s', NOW_TIME ) . "执行异步通知，" . $out_trade_no_orderid . "11111成功<br>" );
					}
				}
				// -------------------------------------------更新支付状态和回单号、回单时间
				// 、日志v
			}
			
			// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		} else {
			// 验证失败
			echo "fail";
			
			// 调试用，写文本函数记录程序运行情况是否正常
			logResult ( "<br>" . date ( 'Y-m-d H:i:s', NOW_TIME ) . "执行异步通知，3333未成功<br>" );
		}
	}
	
	public function paySuccessMail(){
		$out_trade_no='141767007223212727';
		$trade_no='111222333';
		$total_fee=197;
		$UAM=new UserAccountModel();
		$UAM->addLogAndRCoin($out_trade_no, $trade_no, $total_fee);
	}
	
	
	function alipay_close($out_order_no) {
		
		$alipay_config = alicofings ();
		//支付宝交易号与商户订单号不能同时为空
		//$trade_no = $_POST['WIDtrade_no'];			//支付宝交易号
		//$out_order_no = $_POST['WIDout_order_no'];	//商户订单号
		$trade_no="";
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" 		=> "close_trade",
				"partner" 		=> trim($alipay_config['partner']),
				"trade_no"		=> $trade_no,
				"out_order_no"	=> $out_order_no,
				"_input_charset"=> trim(strtolower($alipay_config['input_charset']))
		);
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestHttp($parameter);
		//解析XML
		//注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
		$doc = new DOMDocument();
		$doc->loadXML($html_text);
		
		//请在这里加上商户的业务逻辑程序代码
		//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
		//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
		
		//解析XML
		if( ! empty($doc->getElementsByTagName( "alipay" )->item(0)->nodeValue) ) {
			$alipay = $doc->getElementsByTagName( "alipay" )->item(0)->nodeValue;
			echo $alipay;
		}
		//exit;
	}
	
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

    /*
     * APP单个产品生成订单
     */
    public function apporder(){
        echo "abc";
    }
	

	
	
}

?>