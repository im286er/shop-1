<?php
class OrdermobAction extends CommonAction {
	public function _initialize() {
        Vendor('AlipayMob.config');
        Vendor('AlipayMob.alipay_core');
        Vendor('AlipayMob.alipay_rsa');
        Vendor('AlipayMob.alipay_notify');
	}


	/* 移动端 APP
	 * 功能：支付宝服务器异步通知
	 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
	 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
	 */
	public function notifyurl() {
		$aliapy_config = alicofingsMob ();
		$alipayNotify = new AlipayNotify ( $aliapy_config );
		$verify_result = $alipayNotify->verifyNotify();

		if ($verify_result) { // 验证成功
			$out_trade_no = $_POST ['out_trade_no']; // 获取订单号
			$trade_no = $_POST ['trade_no']; // 获取支付宝交易号
			$total_fee = $_POST ['total_amount']; // 获取总价格
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
			logResult ( "<br>" . date ( 'Y-m-d H:i:s', NOW_TIME ) . "执行异步通知，未成功<br>" );
		}
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
	
	
	
}

?>