<?php
class LogUserLoginModel extends Model
{
	/**
	 * @var DBF_LogUserLogin
	 */
	public $F;
	
	public function __construct()
	{
		$this->F = new DBF_LogUserLogin();
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}
	
	public function addLog($UID,$type=0)
	{
		$this->{$this->F->UserID} = $UID;
		$this->{$this->F->LogDate} = get_now();
		$this->{$this->F->LogTime} = time();
		$this->{$this->F->IP} = $_SERVER["REMOTE_ADDR"];
		$this->{$this->F->Type} = $type;
		
		return $this->add();
	}
}
?>