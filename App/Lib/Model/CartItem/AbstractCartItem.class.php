<?php
/**
 * CartItem抽象类
 *
 * @author miaomin 
 * Nov 3, 2014 3:57:50 PM
 *
 * $Id$
 */
abstract class AbstractCartItem {
	
	/**
	 * 映射关系
	 *
	 * @var unknown_type
	 */
	protected $_map = array ();
	
	/**
	 * 购物车条目参数
	 *
	 * @var unknown_type
	 */
	protected $_args = array ();
	
	/**
	 * 增加购物车
	 */
	abstract protected function add();
	
	/**
	 * 移除购物车
	 */
	abstract protected function remove();
	
	/**
	 * 计算价格
	 */
	abstract protected function calcprice();
	
	/**
	 * 是否已经添加
	 */
	public function isAlreadyAdd() {
		$res = null;
		
		$UCM = new UserCartModel ();
		$condition = array (
				$UCM->F->UserID => $this->_args ['uid'],
				$UCM->F->ProductID => $this->_args ['pid'] 
		);
		$res = $UCM->where ( $condition )->select ();
		
		return $res;
	}
	
	/**
	 * 生成一段购物车界面
	 */
	public function renderIndex() {
		$templatesArr = array ();
		return $templatesArr;
	}
	
	/**
	 * setArgs
	 *
	 * @param array $args        	
	 */
	public function setArgs(array $args) {
		$this->_args = $args;
	}
	
	/**
	 * 转换
	 *
	 * @author miaomin
	 * @param array/object $map
	 *        	- 单维数组或者数据对象
	 * @param int $hasMap        	
	 * @return multitype:NULL
	 */
	public function transMap($map, $hasMap = 1) {
		if (is_array ( $map )) {
			$this->_args = $this->_transMapArr ( $map, $hasMap );
		} elseif (is_object ( $map )) {
			$this->_args = $this->_transMapObj ( $map, $hasMap );
		}
		
		return $this->_para;
	}
	
	// 获取参数
	public function getArgs() {
		return $this->_args;
	}
	
	// 对象转换
	private function _transMapObj($mapObj, $hasMap = 1) {
		return $hasMap ? $this->_singleObjectToArray ( $mapObj, $this->_args, $this->_map ) : $this->_singleObjectToArray ( $mapObj, $this->_args );
	}
	
	// 数组转换
	private function _transMapArr($mapArr, $hasMap = 1) {
		return $hasMap ? $this->_singleArrayToArray ( $mapArr, $this->_args, $this->_map ) : $this->_singleArrayToArray ( $mapArr, $this->_args );
	}
	
	// 单个对象根据映射关系转换为单维数组
	private function _singleObjectToArray($obj, $target, $map) {
		$target = ( array ) $target;
		foreach ( $target as $k => $v ) {
			if ($map) {
				if (isset ( $obj->{$map [$k]} )) {
					$target [$k] = $obj->{$map [$k]};
				}
			} else {
				if (isset ( $obj->{$k} )) {
					$target [$k] = $obj->{$k};
				}
			}
		}
		return $target;
	}
	
	// 单维数组根据映射关系转换为单维数组
	private function _singleArrayToArray($array, $target, $map) {
		$target = ( array ) $target;
		foreach ( $target as $k => $v ) {
			if ($map) {
				if (array_key_exists ( $map [$k], $array )) {
					$target [$k] = $array [$map [$k]];
				}
			} else {
				if (array_key_exists ( $k, $array )) {
					$target [$k] = $array [$k];
				}
			}
		}
		return $target;
	}
}
?>