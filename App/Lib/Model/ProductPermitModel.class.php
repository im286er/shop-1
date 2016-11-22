<?php
class ProductPermitModel extends Model
{
	/**
	 * @var DBF
	 */
	protected $DBF;
	/**
	 * @var DBF_ProductPermit
	 */
	public $F;
	
	function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->ProductPermit;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}
	
	function getList($Page = 0,$Count = 50)
	{ return $this->page($Page)->limit($Count)->order($this->F->DispWeight . ' DESC')->select(); }
	
	function getPermitByType($Type)
	{	return $this->where($this->F->Type . "='" . $Type . "'")->order($this->F->DispWeight . ' DESC')->select(); }
	
	function getAllPermit()
	{ return $this->order($this->F->DispWeight . ' DESC')->select(); }
	
	function deleteByID($ID)
	{
		$this->{$this->F->ID} = $ID;
		return $this->delete();
	}
}
?>