<?php

class StatsUserModel extends Model
{
	/**
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 * @var DBF_Users
	 */
	public $F;
	
	public $TotalCount = 0;
	private $SI = array();
	
	function __construct($SI)
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->Users;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		
		$this->SI = $SI;
		if(!isset($this->SI['page'])) { $this->SI['page'] = 1; }
		if(!isset($this->SI['count'])) { $this->SI['count'] = 50; }
		
		parent::__construct();
	}
}
?>