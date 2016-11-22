<?php
/**
 * 招商银行网上支付sdk操作类
 *
 * @author miaomin 
 * Feb 12, 2015 4:37:40 PM
 *
 * $Id$
 */
class Cmbsdk {
	
	// web支付Url
	private $_payUrlWeb;
	
	// wap支付Url
	private $_payUrlWap;
	
	// BranchID-商户开户分行号
	private $_branchId;
	
	// CoNo-商户号6位数字
	private $_coNo;
	
	// 返回Url
	private $_callbackUrl;
	
	// 支付折扣
	private $_discount;
	
	// 支付折扣券码
	private $_couponCode;
	
	/**
	 * 招商银行网上支付sdk操作类
	 */
	public function __construct() {
		
		// 注意测试用Url,上线需要将Test字样移除
		$this->_payUrlWeb = "https://netpay.cmbchina.com/netpayment/BaseHttp.dll?PrePayC1";
		$this->_payUrlWap = "https://netpay.cmbchina.com/netpayment/BaseHttp.dll";
		
		$this->_branchId = '0021';
		
		// 测试账号
		// $this->_coNo = '000056';
		$this->_coNo = '004697';
		
		$this->_callbackUrl = 'http://www.3dcity.com/index/curl-getcmbpayres';
		
		$this->_discount = 0.99;
		
		$this->_couponCode = '5CFC86C351';
		
		// 加载Ncurl基本库
		// import ( 'Common.Ncurl', APP_PATH, '.php' );
	}
	
	/**
	 * 获取折扣
	 */
	public function getDiscount(){
		return $this->_discount;
	}
	
	/**
	 * 获取折扣
	 */
	public function getCouponCode(){
		return $this->_couponCode;
	}
	
	/**
	 * 计算折扣额
	 */
	public function calcDiscountPrice($up_amount){
		$cmbDiscount = 100;
		$cmbDiscountPrice = 0;
	
		$COUPON = new CouponModel ();
		$condition = array (
				'tdf_coupon.ec_code' => $this->getCouponCode ()
		);
	
		$couponRes = $COUPON->join ( 'tdf_coupon_type ON (tdf_coupon_type.et_id=tdf_coupon.etId)' )->where ( $condition )->find ();
	
		if ($couponRes) {
			$cmbDiscount = $couponRes ['et_percent'] / 100;
			$cmbDiscountPrice = round ( $up_amount * (1-$cmbDiscount), 2 );
		}
	
		return $cmbDiscountPrice;
	}
	
	/**
	 * 计算折扣后的应付款总额
	 */
	public function calcDiscountAmount($up_amount){
		$cmbDiscount = 100;
		$cmbDiscountPrice = 0;
		
		$COUPON = new CouponModel ();
		$condition = array (
				'tdf_coupon.ec_code' => $this->getCouponCode ()
		);
		
		$couponRes = $COUPON->join ( 'tdf_coupon_type ON (tdf_coupon_type.et_id=tdf_coupon.etId)' )->where ( $condition )->find ();
		
		if ($couponRes) {
			$cmbDiscount = $couponRes ['et_percent'] / 100;
			$cmbDiscountPrice = round ( $up_amount * (1-$cmbDiscount), 2 );
			$up_amount = $up_amount - $cmbDiscountPrice;
		}
		
		return $up_amount;
	}
	
	/**
	 * 生成测试支付页面
	 *
	 * @param string $billno
	 *        	-6位或10位数字一天内不能重复
	 * @param string $amount
	 *        	-xxxx.xx
	 * @param string $date
	 *        	-YYYYMMDD
	 * @param string $returnurl        	
	 * @return mixed
	 */
	public function goTestNetPay($billno, $amount, $date, $returnurl) {
		redirect ( $this->genTestNetPayUri ( $billno, $amount, $date, $returnurl ) );
	}
	
	/**
	 * 生成支付链接URI(支付方式2)
	 *
	 * @param string $billno        	
	 * @param string $amount        	
	 * @param string $date        	
	 * @param string $returnurl        	
	 * @return string
	 */
	public function genTestNetPayUri($billno, $amount, $date, $returnurl) {
		return $this->_payUrl . "?BranchID=" . $this->_branchId . "&CoNo=" . $this->_coNo . "&BillNo=" . $billno . "&Amount=" . $amount . "&Date=" . $date . "&MerchantUrl=" . urlencode ( $returnurl );
	}
	
	/**
	 * 生成支付链接表单
	 *
	 * @param string $billno        	
	 * @param string $amount        	
	 * @param string $date        	
	 * @param string $returnurl        	
	 * @return string
	 */
	public function genTestNetPayForm($billno, $amount, $date, $returnurl = '') {
		$billno = $billno ? $billno : '000000';
		$amount = $amount ? $amount : '8888.88';
		$date = $date ? $date : date ( 'Ymd' );
		$returnurl = $returnurl ? $returnurl : $this->_callbackUrl;
		return "<form style='display:none;' id='form1' name='form1' method='post' action='" . $this->_payUrlWeb . "'><input name='BranchID' type='hidden' value='" . $this->_branchId . "' /><input name='CoNo' type='hidden' value='" . $this->_coNo . "'/><input name='BillNo' type='hidden' value='" . $billno . "'/><input name='Amount' type='hidden' value='" . $amount . "'/><input name='Date' type='hidden' value='" . $date . "'/><input name='MerchantUrl' type='hidden' value='" . $returnurl . "'/></form><script type='text/javascript'>function load_submit(){document.form1.submit()}load_submit();</script>";
	}
	
	/**
	 * 生成支付链接表单(wap)
	 *
	 * @param string $billno        	
	 * @param string $amount        	
	 * @param string $date        	
	 * @param string $returnurl        	
	 * @return string
	 */
	public function genTestNetPayWapForm($billno, $amount, $date, $returnurl = '') {
		$this->_payUrl = $billno = $billno ? $billno : '000000';
		$amount = $amount ? $amount : '8888.88';
		$date = $date ? $date : date ( 'Ymd' );
		$returnurl = $returnurl ? $returnurl : $this->_callbackUrl;
		return "<form style='display:none;' id='form1' name='form1' method='post' action='" . $this->_payUrlWap . "'><input name='MfcISAPICommand' type='hidden' value='PrePayWAP' /><input name='BranchID' type='hidden' value='" . $this->_branchId . "' /><input name='CoNo' type='hidden' value='" . $this->_coNo . "'/><input name='BillNo' type='hidden' value='" . $billno . "'/><input name='Amount' type='hidden' value='" . $amount . "'/><input name='Date' type='hidden' value='" . $date . "'/><input name='MerchantUrl' type='hidden' value='" . $returnurl . "'/></form><script type='text/javascript'>function load_submit(){document.form1.submit()}load_submit();</script>";
	}
}
?>