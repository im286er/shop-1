<?php

// require 'AbstractCartItem.class.php';

/**
 * CG打印件购物条目类
 *
 * @author miaomin
 *         Nov 3, 2014 4:39:39 PM
 *        
 *         $Id$
 */ 
class CartItemVirtualPrintModel extends AbstractCartItem {
	
	/**
	 * 购物车条目参数
	 *
	 * @var unknown_type
	 */
	protected $_args = array (
			// ==============必要参数==============
			
			// UCID
			'ucid' => null,
			// PID
			'pid' => null,
			// Product Type
			'producttype' => null,
			// UID
			'uid' => null,
			// Is Real Object
			'realobject' => null,
			// 单价
			'unitprice' => null,
			// 数量
			'count' => null,
			// 折扣
			'discount' => null,
			// 总价
			'amount' => null,
			// 实际支付
			'realamount' => null,
			// Need Shipping Address
			'needaddress' => null 
	);
	
	/**
	 * 映射关系
	 *
	 * @var unknown_type
	 */
	protected $_map = array (
			// VAL值为$_REQUEST
			'pid' => 'pid',
			'realobject' => 'isreal',
			'ucid' => 'itemid',
			'count' => 'count' 
	);
	
	/**
	 * CG打印件购物条目类
	 */
	public function __construct() {
	}
	
	/**
	 * 价格计算
	 *
	 * @see AbstractCartItem::calcprice()
	 */
	public function calcprice(){
	
		return 13.33;
	}
	
	/**
	 * 增加一个CG打印件到购物车
	 *
	 * @see AbstractCartItem::add()
	 */
	public function add() {
		$UCM = new UserCartModel ();
		$UCM->create ( $this->_args );
		$addRes = $UCM->add ();
		
		return $addRes;
	}
	
	/**
	 * 移除一个CG打印件到购物车
	 *
	 * @see AbstractCartItem::remove()
	 */
	public function remove() {
		$removeRes = null;
		if ($this->_args ['ucid'] && $this->_args ['uid']) {
			$UCM = new UserCartModel ();
			
			$condition = array (
					$UCM->F->ID => $this->_args ['ucid'],
					$UCM->F->UserID => $this->_args ['uid'] 
			);
			$removeRes = $UCM->where ( $condition )->delete ();
		}
		
		return $removeRes;
	}
	
	/**
	 * 是否已经添加
	 *
	 * @see AbstractCartItem::isAlreadyAdd()
	 */
	public function isAlreadyAdd() {
		$res = null;
		
		$UCM = new UserCartModel ();
		$condition = array (
				$UCM->F->UserID => $this->_args ['uid'],
				$UCM->F->ProductID => $this->_args ['pid'],
				$UCM->F->IsReal => $this->_args ['realobject'] 
		);
		
		$res = $UCM->where ( $condition )->select ();
		
		return $res;
	}
	
	/**
	 * 生成一段购物车界面
	 */
	public function renderIndex() {
		$templatesArr = array ();
		
		$UCM = new UserCartModel ();
		$condition = array (
				$UCM->F->ID => $this->_args ['ucid'],
				$UCM->F->UserID => $this->_args ['uid'] 
		);
		
		$ucmRes = $UCM->where ( $condition )->find ();
		
		$templatesArr ['count'] = $ucmRes [$UCM->F->Count];
		$templatesArr ['removelink'] = 'cart/removeitem/itemid/' . $this->_args ['ucid'];
		
		return $templatesArr;
	}
	
	/**
	 * 价格小计
	 */
	public function amount() {
		$amount = 0;
		
		$UCM = new UserCartModel ();
		$condition = array (
				$UCM->F->ID => $this->_args ['ucid'],
				$UCM->F->UserID => $this->_args ['uid'] 
		);
		$ucmRes = $UCM->where ( $condition )->find ();
		
		$Product = new ProductModel ();
		$productRes = $Product->find ( $ucmRes [$UCM->F->ProductID] );
		
		$amount += $productRes [$Product->F->Price];
		return $amount;
	}
}
?>