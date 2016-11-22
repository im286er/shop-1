<?php
/**
 * 商品主分类基本类
 *
 * @author miaomin 
 * Dec 12, 2014 11:38:42 AM
 *
 * $Id$
 */
class ProductMainTypeModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_InfoProductMainType
	 */
	public $F;
	
	/**
	 * 映射关系
	 */
	protected $_map = array (
			'maintype_name' => 'ipt_name',
			'maintype_desc' => 'ipt_intro' 
	);
	
	/**
	 * 商品主分类基本类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->InfoProductType;
		
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
	
	/**
	 * 更新属性集
	 */
	public function updateProps() {
		
		// 获取属性
		$PMPM = new ProductMainPropModel ();
		$selectRes = $PMPM->getPropByMainType ( $this->{$this->F->ID} );
		
		if ($selectRes) {
			$propRes = array ();
			foreach ( $selectRes as $key => $val ) {
				$propRes [] = $val [$PMPM->F->PROPNAME];
			}
			
			$condition = array (
					$this->F->ID => $this->{$this->F->ID} 
			);
			
			$data = array (
					$this->F->TYPEPROPS => json_encode ( $propRes ) 
			);
			
			$this->where ( $condition )->save ( $data );
		}
	}
	
	/**
	 * 获取商品主类型选项
	 *
	 * @param array $whereArr        	
	 * @param string $select        	
	 */
	public function getOptionCtrl($whereArr = array(), $select = '') {
		
		// find
		$findRes = $this->where ( $whereArr )->select ();
		
		$optArr = transDBarrToOptarr ( $findRes, $this->F->TYPENAME, $this->F->ID );
		
		if ($select) {
			$optRes = get_dropdown_option ( $optArr, $select );
		} else {
			$optRes = get_dropdown_option ( $optArr );
		}
		
		return $optRes;
	}
}