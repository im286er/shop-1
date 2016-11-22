<?php

class StatsLoginModel extends Model
{
	/**
	 * @var DBF_LogUserLogin
	 */
	public $F;
	
	public $TotalCount = 0;
	private $SI = array();
	
	function __construct($SI)
	{
		load('@.WhereBuilder');
		$this->F = new DBF_LogUserLogin();
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		
		$this->SI = $SI;
		if(!isset($this->SI['page'])) { $this->SI['page'] = 1; }
		if(!isset($this->SI['count'])) { $this->SI['count'] = 50; }
		
		parent::__construct();
	}
	
	function getList()
	{
		$WB = new WhereBuilder();
		$Where = $WB->addRange($this->F->LogDate, $this->SI['f_startdate'], $this->SI['f_enddate'], true, true)
								->getWhere();
		$this->TotalCount = $this->where($Where)->count('DISTINCT(' . $this->F->UserID . ')');
		if($this->TotalCount === false) { return false; }
		
		$field = array($this->F->UserID, 'count(' . $this->F->UserID . ') AS user_l_count');
		
		$List = $this->field($field)->where($Where)
									->group($this->F->UserID)->order('user_l_count DESC')
									->limit($this->SI['count'])->page($this->SI['page'])->select();
		return $List;
	}
	
	function getListByUser()
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->UserID, $this->SI['uid'])
								->addRange($this->F->LogDate, $this->SI['f_startdate'], $this->SI['f_enddate'], true, true)
								->getWhere();
		$this->TotalCount = $this->where($Where)->count($this->F->UserID);
		if($this->TotalCount === false) { return false; }
		$List = $this->where($Where)->order($this->F->LogDate . ' DESC')
									->limit($this->SI['count'])->page($this->SI['page'])->select();
		return $List;
	}
}
?>