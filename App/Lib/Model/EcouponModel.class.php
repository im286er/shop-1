<?php
/**
 * 优惠券Model类
 *
 * @author miaomin 
 * Sep 23, 2013 6:31:34 PM
 */
class EcouponModel extends Model {
	
	/**
	 * 自动完成
	 *
	 * @var unknown_type
	 */
	protected $_auto = array (
			array (
					'ec_createdate',
					'get_now',
					1,
					'function' 
			),
			array (
					'ec_code',
					'generate_password',
					1,
					'function' 
			) 
	);
	
	/**
	 * 映射关系
	 *
	 * @var unknown_type
	 */
	protected $_map = array (
			'ecoupon_type' => 'ec_type',
			'ecoupon_uselimit' => 'ec_uselimit',
			'ecoupon_amount' => 'ec_amount',
			'ecoupon_expire' => 'ec_expiredate',
			'ecoupon_status' => 'ec_status',
			'ecoupon_usecount' => 'ec_usecount' 
	);
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_Ecoupon
	 */
	public $F;
	
	/**
	 * 帮助类
	 *
	 * @var EcouponHelper
	 */
	public $helper;
	
	/**
	 * 构造
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->Ecoupon;
		$this->trueTableName = $this->F->_Table;
		
		parent::__construct ();
		
		import ( "App.helper.EcouponHelper" );
		$this->helper = new EcouponHelper ( $this );
	}
}
?>