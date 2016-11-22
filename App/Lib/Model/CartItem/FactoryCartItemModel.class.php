<?php
/**
 * 购物车条目工厂类
 *
 * @author miaomin 
 * Nov 3, 2014 5:12:45 PM
 *
 * $Id$
 */
class FactoryCartItemModel extends Model {
	
	/**
	 * 初始化时一些映射关系的设定
	 *
	 * @var unknown_type
	 */
	private static $_initMap = array (
			'p_id' => 'pid',
			'uc_isreal' => 'isreal',
			'uc_id' => 'itemid' 
	);
	
	/**
	 * 根据参数获取购物车条目类
	 *
	 * @param array $args        	
	 * @param int $isInitData        	
	 */
	private static function getCartItemModel(array $args, $isInitData = 1) {
		
		// 返回结果
		$res = null;
		
		$productType = C ( 'PRODUCT.TYPE' );
		
		$realStat = C ( 'PRODUCT.ISREAL' );
		
		/**
		 * 获取必要的参数
		 */
		$pid = ( int ) $args ['pid'];
		$isReal = ( int ) $args ['isreal'];
		
		// 根据producttype获取相应的CartItem类
		
		$PM = new ProductModel ();
		
		$pmRes = $PM->find ( $pid );
		
		if (! $pmRes) {
			
			throw new Exception ( 'not_invalid_product' );
		}


		// 开始逻辑处理
		switch ($pmRes [$PM->F->ProductType]) {
			
			// 暂未处理
			case $productType ['CG'] :
				$res = new stdClass ();
				break;
			
			// 暂未处理
			case $productType ['PRINTER'] :
				$res = new stdClass ();
				break;
			
			// 打印件处理
			case $productType ['PRINTMODEL'] :
				
				if ($isReal == $realStat ['REAL']) {
					
					$res = new CartItemRealPrintModel ( $args );
					if ($isInitData) {
						$res->transMap ( $args );
					}
				} elseif ($isReal == $realStat ['VIRTUAL']) {
					
					$res = new CartItemVirtualPrintModel ( $args );
					if ($isInitData) {
						$res->transMap ( $args );
					}
				} else {
					
					// 默认虚拟物品
					$res = new CartItemVirtualPrintModel ( $args );
					if ($isInitData) {
						$res->transMap ( $args );
					}
				}
				break;
			
			// DIY类商品
			case $productType ['DIY'] :
			    $res = new CartItemDiyModel ( $args );
			    if ($isInitData) {
			        $res->transMap ( $args );
			    }
				break;
			
			// 垂直类商品
			case $productType ['NDIY'] :

				if ($isReal == $realStat ['REAL']) {
					
					$res = new CartItemNoneDiyModel ( $args );

					if ($isInitData) {
						$res->transMap ( $args );
					}
				} elseif ($isReal == $realStat ['VIRTUAL']) {
					
					$res = new CartItemNoneDiyModel ( $args );
					if ($isInitData) {
						$res->transMap ( $args );
					}
				} else {
					
					$res = new stdClass ();
				}
				break;
				
			// DIY生成的垂直类商品
			case 7 :
				
				    if ($isReal == $realStat ['REAL']) {
				        	
				        $res = new CartItemNoneDiyModel ( $args );
				
				        if ($isInitData) {
				            $res->transMap ( $args );
				        }
				    } elseif ($isReal == $realStat ['VIRTUAL']) {
				        	
				        $res = new CartItemNoneDiyModel ( $args );
				        if ($isInitData) {
				            $res->transMap ( $args );
				        }
				    } else {
				        	
				        $res = new stdClass ();
				    }
				    break;
			
			// 其他状况
			default :
				$res = new stdClass ();
				break;
		}
		
		// 返回结果
		if ($res instanceof AbstractCartItem) {
			
			return $res;
		} else {
			
			throw new Exception ( 'caritem_type_undefined' );
		}
	}
	
	/**
	 * init Map
	 *
	 * @param array $args        	
	 * @return array
	 */
	private static function initMap(array $args) {
		foreach ( $args as $key => $val ) {
			if (array_key_exists ( $key, self::$_initMap ) && ! array_key_exists ( self::$_initMap [$key], $args )) {
				$args [self::$_initMap [$key]] = $val;
			}
		}
		return $args;
	}
	
	/**
	 * 购物车条目工厂类
	 *
	 * @param array $args        	
	 *
	 * @param int $isInitData        	
	 *
	 * @return Model
	 */
	public static function init(array $args, $isInitData = 1) {
		
		// init map
		$args = self::initMap ( $args );
		
		if (! isset ( $args ['pid'] )) {
			
			throw new Exception ( 'not_invalid_pid' );
		}
		
		return self::getCartItemModel ( $args, $isInitData );
	}
}
?>