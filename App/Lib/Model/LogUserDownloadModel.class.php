<?php
class LogUserDownloadModel extends Model
{
	/**
	 * @var DBF_LogUserDownload
	 */
	public $F;
	
	public function __construct()
	{
		$this->F = new DBF_LogUserDownload();
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}
	
	public function addLog($PID, $UID)
	{
		$this->{$this->F->ProductID} = $PID;
		$this->{$this->F->UserID} = $UID;
		$this->{$this->F->LogDate} = get_now();
		$this->{$this->F->LogTime} = time();
		$this->{$this->F->IP} = $_SERVER["REMOTE_ADDR"];
		
		return $this->add();
	}
}
?>