<?php
/**
 * 模型WebglCapture Model类
 *
 * @author miaomin 
 * Jul 8, 2014 7:31:12 PM
 *
 * $Id$
 */
class ProductWebglCaptureModel extends Model {
	
	/**
	 *
	 * @var DBF_ProductWebglCapture
	 */
	public $F;
	
	/**
	 * 模型WebglCapture Model类
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->ProductWebglCapture;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
}
?>