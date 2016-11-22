<?php
/**
 * 用户标签索引表
 *
 * @author miaomin 
 * Mar 13, 2014 11:09:26 AM
 *
 * $Id$
 */
class UserTagsIndexModel extends Model {
	
	/**
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 * @var DBF_UserTagsIndex
	 */
	public $F;
	
	/**
	 * 用户标签索引表
	 */
	function __construct(){
		$this->DBF = new DBF();
		$this->F = $this->DBF->UserTagsIndex;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}
}
?>