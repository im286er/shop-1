<?php

class StatsPrepaidModel extends Model
{
	/**
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 * @var DBF_UserPrepaid
	 */
	public $F;
	
	public $TotalCount = 0;
	private $SI = array();
	
	function __construct($SI)
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->UserPrepaid;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		
		$this->SI = $SI;
		if(!isset($this->SI['page'])) { $this->SI['page'] = 1; }
		if(!isset($this->SI['count'])) { $this->SI['count'] = 50; }
		
		parent::__construct();
	}
	
	function getList()
	{
		load('@.WhereBuilder');
		$WB = new WhereBuilder();
		$Where = $WB->addRange($this->F->DealDate, $this->SI['f_startdate'], $this->SI['f_enddate'])
								->addEq($this->F->UserID, $this->SI['uid'])
								->addEq($this->F->Status, 1)
								->addNotEq($this->F->Type, 4)
								->getWhere();
		$this->TotalCount = $this->where($Where)->count($this->F->ID);
		if($this->TotalCount === false) { return false; }
		
		$List = $this->where($Where)->order($this->F->ID . ' DESC')
									->limit($this->SI['count'])->page($this->SI['page'])->select();
		return $List;
	}
	
	function getTotal()
	{
		$WB = new WhereBuilder();
		$Where = $WB->addRange($this->F->DealDate, $this->SI['f_startdate'], $this->SI['f_enddate'])
								->getWhere();
		$Result = $this->where($Where)->count($this->F->ProductID);
		return $Result;
	}
}
?>