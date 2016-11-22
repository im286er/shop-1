<?php
/**
 * ProductPMMaterial基本类
 *
 * @author miaomin 
 * Oct 20, 2014 3:43:34 PM
 *
 * $Id$
 */
class ProductPMMaterialModel extends Model {
	
	/**
	 *
	 * @var DBF_ProductPMMaterial
	 */
	public $F;
	
	/**
	 * 构造
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->ProductPMMaterial;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
}
?>