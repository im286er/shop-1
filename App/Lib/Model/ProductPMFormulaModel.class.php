<?php
/**
 * ProductPMFormula基本类
 *
 * @author miaomin 
 * Nov 17, 2014 11:24:50 AM
 *
 * $Id$
 */
class ProductPMFormulaModel extends Model {
	
	/**
	 *
	 * @var DBF_ProductPMFormula
	 */
	public $F;
	
	/**
	 * 构造
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->ProductPMFormula;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 获取当前的公式
	 */
	public function getCurrent() {
		$condition = array (
				$this->F->ISDEFAULT => 1 
		);
		
		return $this->where ( $condition )->find ();
	}
	
	/**
	 * 获取当前的公式
	 *
	 * @param string $newformula        	
	 */
	public function saveFormula($newformula) {
		$condition = array (
				$this->F->ISDEFAULT => 1 
		);
		
		$data = array (
				$this->F->FORMULA => $newformula 
		);
		
		return $this->where ( $condition )->save ( $data );
	}
}
?>