<?php
/**
 * ProductPMMaterialFilter基本类
 *
 * @author miaomin 
 * Nov 27, 2014 1:13:34 PM
 *
 * $Id$
 */
class ProductPMMaterialFilterModel extends Model {
	
	/**
	 *
	 * @var DBF_ProductPMMaterialFilter
	 */
	public $F;
	
	/**
	 * 构造
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->ProductPMMaterialFilter;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
}
?>