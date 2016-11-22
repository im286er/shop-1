<?php

class UserMailVaildateModel extends Model
{

	/**
	 * @var DBF
	 */
	protected $DBF;

	/**
	 * @var DBF_UserMailVaildate
	 */
	public $F;

	public function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->UserMailVaildate;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		
		parent::__construct();
	}
	
	public function getActiveInfo($VCode, $Type)
	{
		$ActInfo = $this->where($this->F->Code . "='" . $VCode . "' AND " . $this->F->Type . "='" . $Type . "'")->select();
		if($ActInfo === false) { return  false; }
		if($ActInfo === null) { return null; }
		if(count($ActInfo) == 0) { return null; }
		return $ActInfo[0];
	}
	
	public function deleteActiveInfo($UserID, $Type)
	{ return $this->where($this->F->UserID . "='" . $UserID . "' AND " . $this->F->Type . "='" . $Type . "'")->delete(); }
}