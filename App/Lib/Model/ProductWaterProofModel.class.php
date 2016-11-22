<?php
/**
 * 商品促销水印基本类
 * 
 * @author miaomin
 * Jun 25, 2015 9:17:01 AM
 *
 */
class ProductWaterProofModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_ProductWaterProof
	 */
	public $F;
	
	/**
	 * 商品主分类基本类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->ProductWaterProof;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
	
	/**
	 * 获取商品促销水印选项
	 *
	 * @param array $whereArr
	 * @param string $select
	 */
	public function getOptionCtrl($whereArr = array(), $select = null) {
	    // find
	    $findRes = $this->where ( $whereArr )->select ();
	    
	    $optArr = transDBarrToOptarr ( $findRes, $this->F->TITLE, $this->F->ID );
	    
	    if ($select !== null) {
	        $optRes = get_dropdown_option ( $optArr, $select );
	    } else {
	        $optRes = get_dropdown_option ( $optArr );
	    }
	
	    return $optRes;
	}
}