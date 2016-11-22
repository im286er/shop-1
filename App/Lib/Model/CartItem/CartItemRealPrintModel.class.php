<?php

// require 'AbstractCartItem.class.php';

/**
 * 实物打印件购物条目类
 *
 * @author miaomin
 *         Nov 3, 2014 4:11:54 PM
 *        
 *         $Id$
 */
class CartItemRealPrintModel extends AbstractCartItem {
	
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
			'needaddress' => null,
			
			// ==============业务参数==============
			
			// 打印材料ID
			'pma_id' => null,
			// 打印材料名
			'pma_name' => null,
			// 精度ID
			'pmd_id' => null,
			// 精度名
			'pmd_title' => null,
			// 打印件长度
			'print_length' => null,
			// 打印件宽度
			'print_width' => null,
			// 打印件高度
			'print_height' => null,
			// 原始长度
			'original_length' => null,
			// 原始宽度
			'original_width' => null,
			// 原始高度
			'original_height' => null,
			// 打印件体积
			'print_volume' => null,
			// 原始体积
			'original_volume' => null,
			// 打印件凸包体积
			'print_convex' => null,
			// 打印件表面积
			'print_surface' => null,
			// 打印件修复系数
			'print_repairlv' => null,
			// 打印件设计费
			'print_designfee' => 0 
	);
	
	/**
	 * 映射关系
	 *
	 * @var unknown_type
	 */
	protected $_map = array (
			// VAL值为$_REQUEST
			'pid' => 'pid',
			'ucid' => 'itemid',
			'pma_id' => 'pmaId',
			'pmd_id' => 'pmdId',
			'count' => 'count',
			'print_length' => 'length',
			'print_width' => 'width',
			'print_height' => 'height',
			'print_volume' => 'volume',
			'realobject' => 'isreal' 
	);
	
	/**
	 * 实物打印件购物条目类
	 */
	public function __construct() {
	}
	
	/**
	 * 价格计算
	 *
	 * @see AbstractCartItem::calcprice()
	 */
	public function calcprice() {
		$PPMM = new ProductPrintModelsModel ();
		
		$ppmmRes = $PPMM->getByp_id ( $this->_args ['pid'] );
		
		// 设计费用
		$PM = new ProductModel ();
		$pmRes = $PM->find ( $this->_args ['pid'] );
		if ($pmRes) {
			$this->_args ['print_designfee'] = $pmRes [$PM->F->DesignPrice];
		}
		
		// 需要将参数替代入对象值中去(映射)
		$this->_args ['print_convex'] = $this->_args ['print_volume'];
		$PPMM = assignSingleObjectFromMapArr ( $PPMM, $this->_args, $PPMM->getMapCartItem () );
		
		return $PPMM->calcPrice ( $this->_args );
	}
	
	/**
	 * 增加一个实物打印件到购物车
	 *
	 * @see AbstractCartItem::add()
	 */
	public function add() {
		// var_dump ( $this->_args );
		
		// 添加user_cart
		$UCM = new UserCartModel ();
		$UCM->create ( $this->_args );
		$addRes = $UCM->add ();
		
		// 添加user_printmodel
		$UPMM = new UserPrintModelsModel ();
		$PPMM = new ProductPrintModelsModel ();
		
		// 获取精度信息
		$mpRes = $PPMM->getMaterialsPrecision ();
		if ($mpRes) {
			foreach ( $mpRes as $key => $val ) {
				if ($this->_args ['pmd_id'] == $val ['pmd_id']) {
					$this->_args ['pmd_title'] = $val ['pmd_title'];
				}
			}
		}
		
		// 获取材质信息
		$mRes = $PPMM->getMaterials ();
		if ($mRes) {
			foreach ( $mRes as $key => $val ) {
				foreach ( $val ['Child'] as $k => $v ) {
					if ($this->_args ['pma_id'] == $v ['pma_id']) {
						$this->_args ['pma_name'] = $v ['pma_name'];
					}
				}
			}
		}
		
		$UPMM->create ( $this->_args );
		
		$UPMM->{$UPMM->F->UCID} = $addRes;
		
		// 计算价格
		// 需要是把传递过来的价格同实际计算的值比较一下
		$ppmmRes = $PPMM->getByp_id ( $this->_args ['pid'] );
		// 需要将参数替代入对象值中去(映射)
		// $this->_args ['print_volume'] = $PPMM->getVolume ( $this->_args
		// ['print_length'], $this->_args ['print_width'], $this->_args
		// ['print_height'] );
		$this->_args ['print_convex'] = $this->_args ['print_volume'];
		$PPMM = assignSingleObjectFromMapArr ( $PPMM, $this->_args, $PPMM->getMapCartItem () );
		$UPMM->{$UPMM->F->UNITPRICE} = $this->calcPrice ();
		$UPMM->{$UPMM->F->CONVEX_PRINT} = $this->_args ['print_convex'];
		$addRes = $UPMM->add ();
		
		return $addRes;
	}
	
	/**
	 * 移除一个实物打印件到购物车
	 *
	 * @see AbstractCartItem::remove()
	 */
	public function remove() {
		$removeRes = null;
		if ($this->_args ['ucid'] && $this->_args ['uid']) {
			
			// 删除user_cart
			$UCM = new UserCartModel ();
			$condition = array (
					$UCM->F->ID => $this->_args ['ucid'],
					$UCM->F->UserID => $this->_args ['uid'] 
			);
			$removeRes = $UCM->where ( $condition )->delete ();
			
			// 删除user_printmodel
			$UPMM = new UserPrintModelsModel ();
			$condition = array (
					$UPMM->F->UID => $this->_args ['uid'],
					$UPMM->F->INCART => 0,
					$UPMM->F->UCID => $this->_args ['ucid'] 
			);
			$removeRes = $UPMM->where ( $condition )->delete ();
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
		
		if ($res) {
			$UPMM = new UserPrintModelsModel ();
			$condition = array (
					$UPMM->F->PID => $this->_args ['pid'],
					$UPMM->F->UID => $this->_args ['uid'],
					$UPMM->F->INCART => 0,
					$UPMM->F->PMAID => $this->_args ['pma_id'],
					$UPMM->F->PMDID => $this->_args ['pmd_id'],
					$UPMM->F->LENGTH_PRINT => $this->_args ['print_length'],
					$UPMM->F->WIDTH_PRINT => $this->_args ['print_width'],
					$UPMM->F->HEIGHT_PRINT => $this->_args ['print_height'] 
			);
			$res = $UPMM->where ( $condition )->select ();
		}
		
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
		
		$UPMM = new UserPrintModelsModel ();
		$condition = array (
				$UPMM->F->UCID => $this->_args ['ucid'],
				$UPMM->F->UID => $this->_args ['uid'] 
		);
		$upmmRes = $UPMM->where ( $condition )->find ();
		
		$templatesArr ['pmaname'] = $upmmRes [$UPMM->F->PMANAME];
		$templatesArr ['print_length'] = $upmmRes [$UPMM->F->LENGTH_PRINT];
		$templatesArr ['print_width'] = $upmmRes [$UPMM->F->WIDTH_PRINT];
		$templatesArr ['print_height'] = $upmmRes [$UPMM->F->HEIGHT_PRINT];
		$templatesArr ['count'] = $ucmRes [$UCM->F->Count];
		$templatesArr ['unitprice'] = $upmmRes [$UPMM->F->UNITPRICE];
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
		
		$UPMM = new UserPrintModelsModel ();
		$condition = array (
				$UPMM->F->UCID => $this->_args ['ucid'],
				$UPMM->F->UID => $this->_args ['uid'] 
		);
		$upmmRes = $UPMM->where ( $condition )->find ();
		
		$amount += $ucmRes [$UCM->F->Count] * $upmmRes [$UPMM->F->UNITPRICE];
		return $amount;
	}
}
?>