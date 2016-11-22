<?php
/**
 * Curl行为类(Client)
 *
 * @author miaomin 
 * Feb 19, 2014 4:24:01 PM
 *
 * $Id$
 */
class CurlAction extends CommonAction {
	
	/**
	 * Curl行为类
	 */
	public function __construct() {
		// 父类继承
		parent::__construct ();
		
		// 导入队列类
		// import ( 'Common.Nqueue', APP_PATH, '.php' );
		// import ( '@.Model.Pqueue.SendmailPqueue' );
		// import ( '@.Model.Pqueue.WebglPqueue' );
	}
	
	/**
	 * 招商银行支付测试
	 */
	public function testcmbpay() {
		$billno = I ( 'billno' ) ? I ( 'billno' ) : '000000';
		$amount = I ( 'amount' ) ? I ( 'amount' ) : '0.88';
		$paydate = date ( 'Ymd' );
		
		// 加载Cmbsdk操作类
		import ( 'Common.Cmbsdk', APP_PATH, '.php' );
		
		$CMB = new Cmbsdk ();
		
		$payRes = $CMB->goTestNetPay ( $billno, $amount, $paydate, WX_CALLBACK_DOMAIN . '/index/curl-getcmbpayres' );
	}
	
	/**
	 * 招商银行支付测试回调
	 */
	public function getcmbpayres() {
		// print_r ( $_GET );
		
		// Log 记录
		$netPayLogPath = 'netpay.log';
		$fp = fopen ( $netPayLogPath, 'a' );
		fwrite ( $fp, json_encode ( $_GET ) . "\r\n\r\n" );
		fclose ( $fp );
		// 验证签名
		try {
			require_once ("http://localhost:8080/JavaBridgeTemplate621/java/Java.inc");
			$here = realpath ( dirname ( $_SERVER ['SCRIPT_FILENAME'] ) );
			if (! $here) {
				$here = getcwd ();
			}
			// java_set_library_path("$here/mobile_lib_cmb");
			// java_set_file_encoding("GBK");
			$client = new java ( "cmb.netpayment.Security", $here . "/public.key" );
			$getmsg = str_replace ( 's=curl-getcmbpayres&', '', $_SERVER ['QUERY_STRING'] );
			$signature = $_REQUEST ['Signature'];
			$getmsg = new java ( "java.lang.String", $getmsg );
			$signature = new java ( "java.lang.String", $signature );
			$ret = $client->checkInfoFromBank ( $getmsg->getBytes ( "GB2312" ) );
			$ret = java_values ( $ret );
			
			// 根据返回订单号获取3DCITY订单号
			$UPM = new UserPrepaidModel ();
			$condition = array (
					'up_cmbpay_date' => $_REQUEST ['Date'],
					'up_cmbpay_orderid' => $_REQUEST ['BillNo']
			);
			$upmRes = $UPM->where ( $condition )->find ();
			// 根据3DCITY订单号获取订单信息
			$upmRes = $UPM->where ( $condition )->find ();
			$UPM_info = $UPM->getPrepaidListByOrderid ( $upmRes['up_orderid'] );
			$out_trade_no = $UPM_info [0] ['up_orderid'];
			
			// 订单状态改写
			if ($ret) {
				
				// LOG写入
				$fp = fopen ( $netPayLogPath, 'a' );
				fwrite ( $fp, '------------------Signature Pass!------------------' . "\r\n\r\n" );
				fclose ( $fp );
				
				 // 生成折扣
				import ( 'Common.Cmbsdk', APP_PATH, '.php' );
				$CMBPAY = new Cmbsdk ();
				
				$LOGCOUPON = new LogCouponModel();
				$condition = array (
						'log_orderid' => $out_trade_no,
						'log_eccode' => $CMBPAY->getCouponCode()
				);
				$logCouponRes = $LOGCOUPON->where($condition)->find ();
				
				// 判断金额数
				if (($logCouponRes) && ($UPM_info [0] ['up_status'] == 0) && (((float)$UPM_info [0] ['up_amount'] - (float)$logCouponRes['log_ecamount']) == (float)$_REQUEST ['Amount'])) {
					
					$UAM = new UserAccountModel ();
					$UM_result = $UAM->addLogAndRCoin ( $out_trade_no, $upmRes [$UPM->F->OrderID], $_REQUEST ['Amount'] );
					// 更新订单表金额
					$cmbpaydata = array (
							'up_amount' => $_REQUEST['Amount'],
							'up_amount_coupon' => $UPM_info [0] ['up_amount_coupon'] + $logCouponRes['log_ecamount'],
							'up_amount_total' => $_REQUEST['Amount'] + $UPM_info [0] ['up_amount_coupon'] + $logCouponRes['log_ecamount'] + $UPM_info [0] ['up_amount_account']
					);
					$UPM->where ( "up_orderid='" . $out_trade_no . "'" )->setField ( $cmbpaydata );
				}
			} else {
				// LOG写入
				$fp = fopen ( $netPayLogPath, 'a' );
				fwrite ( $fp, '------------------Signature invalid!------------------' . "\r\n\r\n" );
				fclose ( $fp );
			}
			// 跳转
			$pay_done_url = "/user.php/sales/payresult/otype/0/mmode/1/out_trade_no/" . $out_trade_no;
			redirect( WEBROOT_URL . $pay_done_url);
		} catch ( JavaException $e ) {
			echo $e;
		}
	}
	
	/**
	 * addSendMailQue
	 */
	public function addsendmail() {
		$args = array (
				'time' => time (),
				'uid' => 15,
				'mail' => 'wow730@gmail.com' 
		);
		$nq = new Nqueue ();
		$res = $nq->addQueue ( new SendmailPqueue (), $args );
		var_dump ( $res );
	}
	
	/**
	 * addWebglQue
	 *
	 * 当用户触发webgl转换时
	 * 我们通过CURL向webgl转换队列提交一个请求
	 * 我们需要准备一个队列请求的地址并且将一系列参数提交到这个地址中去
	 */
	public function addwebgl() {
		// 转换Webgl必要的参数
		$args = array (
				'time' => time (),
				'pid' => 10793,
				'fbxpath' => '/home/wwwroot/default/upload/productfile/test1.stl' 
		);
		// 将任务加入队列
		$nq = new Nqueue ();
		$res = $nq->addQueue ( new WebglPqueue (), $args );
		
		// 接收队列系统的返回值,正常情況下应该是一个32位的任务号.
		var_dump ( $res );
		// 做日志处理和后续的库表处理
		if ($res) {
			$JQ = new JobQueueModel ();
			$jobData = array (
					$JQ->F->JOBCODE => ( string ) $res,
					$JQ->F->REID => $args ['pid'],
					$JQ->F->TYPE => 1 
			);
			$logRes = $JQ->addJob ( $jobData );
			var_dump ( $logRes );
		} else {
			exit ( 'Convert Webgl job add queue failed.' );
		}
	}
	
	/**
	 * checkQue
	 */
	public function checkque() {
		$jobId = '0f3a8e46449fd2ff9d080ecac9163faf';
		$res = SendmailPqueue::stat ( $jobId );
		var_dump ( $res );
	}
}