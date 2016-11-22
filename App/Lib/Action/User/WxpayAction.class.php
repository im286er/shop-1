<?php
class WxpayAction extends Action {
	public function _initialize() {
		Vendor ( 'Wxpay.WxPayPubHelper.WxPayPubHelper');
	}

	function wxpayto_del($out_trade_no, $total_fee, $go_subject = "ignjewelry.com", $go_body = "ignjewelry.com 支付", $paym = "", $bankc = "") {
		//$wxpayshow_url = "/wxpay/wxpayshow/";	
		//$this->redirect ( $wxpayshow_url );
	}



function wx_app(){
    echo "aabb";
}

	/**
	 * Native（原生）支付-模式二-demo
	 * ====================================================
	 * 商户生成订单，先调用统一支付接口获取到code_url，
	 * 此URL直接生成二维码，用户扫码后调起支付。
	 *
	 */
	//显示微信二维码页面
	function wxpayshow($out_trade_no,$up_amount,$show_name){
		//使用统一支付接口
		$unifiedOrder = new UnifiedOrder_pub();
		$total_fee=$up_amount*100;
		//设置统一支付接口参数
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//spbill_create_ip已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$unifiedOrder->setParameter("body",$show_name);//商品描述
		//自定义订单号，此处仅作举例
		$timeStamp = time();
		//$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
		$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
		$unifiedOrder->setParameter("total_fee",$total_fee);//总金额
		$unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址
		$unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
		//非必填参数，商户可根据实际情况选填
		//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
		//$unifiedOrder->setParameter("device_info","XXXX");//设备号
		//$unifiedOrder->setParameter("attach","XXXX");//附加数据
		//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
		//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
		//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
		//$unifiedOrder->setParameter("openid","XXXX");//用户标识
		//$unifiedOrder->setParameter("product_id","XXXX");//商品ID
		//var_dump($unifiedOrder);
		//exit;
		//获取统一支付接口结果
		$unifiedOrderResult = $unifiedOrder->getResult();
		
		
		//商户根据实际情况设置相应的处理流程
		if ($unifiedOrderResult["return_code"] == "FAIL")
		{
			//商户自行增加处理流程
			echo "通信出错：".$unifiedOrderResult['return_msg']."<br>";
		}
		elseif($unifiedOrderResult["result_code"] == "FAIL")
		{
			//商户自行增加处理流程
			echo "错误代码：".$unifiedOrderResult['err_code']."<br>";
			echo "错误代码描述：".$unifiedOrderResult['err_code_des']."<br>";
		}
		elseif($unifiedOrderResult["code_url"] != NULL)
		{
			//从统一支付接口获取到code_url
			$code_url = $unifiedOrderResult["code_url"];
			//商户自行增加处理流程
			//......
		}
		//var_dump($unifiedOrderResult);
		$en_trade_no=pub_encode_pass( $out_trade_no, $_SESSION ['f_userid'] ); // 加密orderid$out_trade_no;
		$this->assign('total_fee',$up_amount);
		$this->assign('out_trade_no',$out_trade_no);
		$this->assign('en_trade_no',$en_trade_no); //加密后的订单号
		$this->assign('code_url',$code_url);
		$this->assign('unifiedOrderResult',$unifiedOrderResult);
		$this->display("Wxpay:wxpayshow");
	}
	
	
	/**
	 * 通用通知接口demo
	 * ====================================================
	 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
	 * 商户接收回调信息后，根据需要设定相应的处理流程。
	 *
	 * 这里举例使用log文件形式记录回调信息。
	 */
	function notify_url(){
		
		//$dbarr['out_trade_no']='142249596490167070';
		//$dbarr['transaction_id']='1245511';
		//$dbarr['total_fee']='0.01';
		//$this->update_prepaid($dbarr);		
		//exit;
		//使用通用通知接口
		$notify = new Notify_pub();
		
		//存储微信的回调
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$notify->saveData($xml);
		$xml_arr=$notify->data;
		
		//验证签名，并回应微信。
		//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		//尽可能提高通知的成功率，但微信不保证通知最终能成功。
		if($notify->checkSign() == FALSE){
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}else{
			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
		}
		$returnXml = $notify->returnXml();
		//echo $returnXml;
		
		//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
		//以log文件形式记录回调信息

		//$log_name="./logs/wxpay/notify_url.log";//log文件路径
		writelog("wxpay","【接收到的notify通知】:\n".$xml_arr['out_trade_no']."total:".$xml_arr['total_fee']."\n");
		
		if($notify->checkSign() == TRUE){
			if ($notify->data["return_code"] == "FAIL") {
				//此处应该更新一下订单状态，商户自行增删操作
				writelog("wxpay","【通信出错】:\n".$xml."\n");
			}elseif($notify->data["result_code"] == "FAIL"){
				//此处应该更新一下订单状态，商户自行增删操作
				writelog("wxpay","【业务出错】:\n".$xml."\n");
			}else{
				//此处应该更新一下订单状态，商户自行增删操作
				$this->update_prepaid($xml_arr);//更新订单状态
				//writelog("wxpay","【支付成功】:\n".$xml."\n");
			}
			//商户自行增加处理流程,
			//例如：更新订单状态
			//例如：数据库操作
			//例如：推送支付完成信息
		}
	}
	
	//更新订单状态
	private function update_prepaid($dbarr){
		//$log_name="./logs/wxpay/notify_url.log";//log文件路径

		$UPM=new UserPrepaidModel();
		$UPM_info=$UPM->getPrepaidListByOrderidNew($dbarr['out_trade_no']);


		//writelog(  "wxpay", date ( 'Y-m-d H:i:s', NOW_TIME ) . "wx_back:".$dbarr['total_fee']."wx_back_orderid".$dbarr['out_trade_no']."\n" );

		if($UPM_info[0]['up_status'] == 0 && $UPM_info[0]['up_amount'] == $dbarr['total_fee']*0.01){
			$UAM = new UserAccountModel ();
			$out_trade_no=$UPM_info[0]['up_orderid'];
			$UM_result = $UAM->addLogAndRCoin ( $out_trade_no, $dbarr['transaction_id'], $dbarr['total_fee'] );
			writelog(  "wxpay",date ( 'Y-m-d H:i:s', NOW_TIME ) . "执行异步通知，" . $out_trade_no . "成功 \n" );
			//$pay_done_url = "/user.php/sales/payresult/otype/0/mmode/1/out_trade_no/" . $out_trade_no;
			//redirect( WEBROOT_URL . $pay_done_url);
			//redirect ("http://www.ignjewelry.com");
			echo("<script>window.location.href='http://www.ignjewelry.com';</script>");

/*			redirect(WEBROOT_URL ."/user.php/wxuser/ordermanage/");*/

		}else{
			// 验证失败
			echo "fail";
			// 调试用，写文本函数记录程序运行情况是否正常
			writelog(  "wxpay", date ( 'Y-m-d H:i:s', NOW_TIME ) . "执行异步通知，未成功 \n" );
		}
	}
	
	
	// 打印log
	function  log_result($file,$word)
	{
		$fp = fopen($file,"a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"执行日期：".strftime("%Y-%m-%d-%H：%M：%S",time())."\n".$word."\n\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	
	//
	function get_prepaid_status(){
		session_start ();
		$trade_no=pub_encode_pass(I('out_trade_no','0',string),$_SESSION["f_userid"], "decode" );
		$UPM=new UserPrepaidModel();
		$up_info=$UPM->getPrepaidListByOrderidNew($trade_no);
		if($up_info[0]['up_status']==1){
			$arr=array('state'=>"1");
			
		}else{
			$arr=array('state'=>"0");
		}
		echo json_encode($arr);
		/* while(true){
			usleep(500000);
			$i++;//若得到数据则马上返回数据给客服端,并结束本次请求
			$randa=rand(1,50);
			 if($randa<=5){
				$arr=array('success'=>"1",'text'=>$randa);
				echo json_encode($arr);
				exit();
			}
			if($i==$_POST['time']){
				$arr=array('success'=>"0",'text'=>$randa);
				echo json_encode($arr);
				exit();
			}
		} */
	}
	
	

	
	
	
}

?>