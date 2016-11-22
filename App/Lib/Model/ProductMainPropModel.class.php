<?php
/**
 * 商品主分类属性类
 *
 * @author miaomin 
 * Dec 12, 2014 2:20:21 PM
 *
 * $Id$
 */
class ProductMainPropModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_InfoProductMainProp
	 */
	public $F;
	
	/**
	 * 映射关系
	 */
	protected $_map = array (
			'prop_name' => 'ipp_name',
			'prop_weight' => 'ipp_weight',
			'type_id' => 'ipt_id' 
	);
	
	/**
	 * 商品主分类基本类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->InfoProductProp;
		
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
	
	/**
	 * 根据主类型ID获取属性列表
	 *
	 * @param int $typeId        	
	 */
	public function getPropByMainType($typeId) {
		// 条件
		$condition = array (
				$this->F->MAINTYPE => $typeId 
		);
		
		return $this->where ( $condition )->order ( $this->F->WEIGHT . ' desc, ' . $this->F->ID . ' asc' )->select ();
	}
}