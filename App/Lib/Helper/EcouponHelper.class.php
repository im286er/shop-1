<?php
/**
 * 优惠券帮助类
 *
 * @author miaomin 
 * Sep 23, 2013 6:39:14 PM
 */
class EcouponHelper extends Model {
	public $ecoupon;
	
	/**
	 * 构造函数
	 */
	public function __construct($ecoupon) {
		parent::__construct ();
		$this->ecoupon = $ecoupon;
	}
	
	/**
	 * 校验优惠码是否满足有效期条件
	 *
	 * @param string $ecode        	
	 * @return boolean
	 */
	private function _verifyEcouponExpired($ecode) {
		$ecoupon = new EcouponModel ();
		$res = $ecoupon->getByec_code ( $ecode );
		
		if ($res) {
			if (strtotime ( $ecoupon->ec_expiredate ) >= strtotime ( get_now () )) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 校验优惠码是否符合使用限制
	 *
	 * @param unknown_type $ecode        	
	 */
	private function _verifyEcouponUselimit($ecode) {
		$USELIMIT = C ( 'ECOUPON.ECOUPON_USELIMIT' );
		$ecoupon = new EcouponModel ();
		$res = $ecoupon->getByec_code ( $ecode );
		if ($res) {
			if ($ecoupon->ec_uselimit == $USELIMIT ['ANYTIMES']) {
				return true;
			}
			
			if (($ecoupon->ec_uselimit == $USELIMIT ['ONCE']) && ($ecoupon->ec_status == 0)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 校验优惠码有效性
	 *
	 * @param string $ecode        	
	 */
	public function verifyEcouponValid($ecode) {
		
		// 有效期
		$res = $this->_verifyEcouponExpired ( $ecode );
		if (! $res) {
			throw new Exception ( 'ecoupon_expired_err' );
		}
		
		// 使用限制
		$res = $this->_verifyEcouponUselimit ( $ecode );
		if (! $res) {
			throw new Exception ( 'ecoupon_uselimit_err' );
		}
	}
	
	/**
	 * 创建优惠券
	 *
	 * @param array $data        	
	 * @return mixed
	 */
	public function createEcoupon($data) {
		$this->ecoupon->create ( $data );
		$this->ecoupon->add ();
	}
	
	/**
	 * 使用优惠券
	 *
	 * @param array $data        	
	 * @return mixed
	 */
	public function usedEcoupon($data) {
		// 更新Ecoupon表
		return $this->ecoupon->where ( 'ec_id=' . $data ['ecoupon_id'] )->save ( $data );
	}
	
	/**
	 * 校验优惠码
	 */
	public function verifyCreateEcoupon($data) {
		
		// 优惠码类型
		$PVC = new PVC2 ();
		
		$res = $PVC->isInt ()->In ( C ( 'ECOUPON.ECOUPON_TYPE' ) )->verifyValue ( $data ['ecoupon_type'] );
		if (! $res) {
			throw new Exception ( 'ecoupon_type_err' );
		}
		
		$res = $PVC->isInt ()->In ( C ( 'ECOUPON.ECOUPON_USELIMIT' ) )->verifyValue ( $data ['ecoupon_uselimit'] );
		if (! $res) {
			throw new Exception ( 'ecoupon_uselimit_err' );
		}
		
		$res = $PVC->isNum ()->verifyValue ( $data ['ecoupon_amount'] );
		if (! $res) {
			throw new Exception ( 'ecoupon_amount_err' );
		}
		
		$res = $PVC->isDate ()->verifyValue ( $data ['ecoupon_expire'] );
		if (! $res) {
			throw new Exception ( 'ecoupon_expiredate_err' );
		}
	}
}
?>