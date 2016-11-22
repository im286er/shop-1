<?php
/**
 * 活动主类型基本类
 * 
 * @author miaomin
 * Jul 15, 2015 11:33:01 AM
 *
 */
class SPTypeModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_InfoSPType
	 */
	public $F;
	
	/**
	 * 活动主类型基本类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->InfoSPType;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
}