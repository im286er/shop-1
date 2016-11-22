<?php

class StatsDownloadModel extends Model
{
	/**
	 * @var DBF_LogUserDownload
	 */
	public $F;
	
	public $TotalCount = 0;
	private $SI = array();
	
	function __construct($SI)
	{
		load('@.WhereBuilder');
		$this->F = new DBF_LogUserDownload();
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
		$this->TotalCount = $this->where($Where)->count('DISTINCT(' . $this->F->ProductID . ')');
		if($this->TotalCount === false) { return false; }
		
		$field = array($this->F->ProductID, 'count(' . $this->F->ProductID . ') AS model_d_count');
		
		$List = $this->field($field)->where($Where)
									->group($this->F->ProductID)->order('model_d_count DESC')
									->limit($this->SI['count'])->page($this->SI['page'])->select();
		return $List;
	}
	
	function getListByProduct()
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->ProductID, $this->SI['id'])
								->addRange($this->F->LogDate, $this->SI['f_startdate'], $this->SI['f_enddate'], true, true)
								->getWhere();
		$this->TotalCount = $this->where($Where)->count($this->F->ProductID);
		if($this->TotalCount === false) { return false; }
		$List = $this->where($Where)->order($this->F->LogDate . ' DESC')
									->limit($this->SI['count'])->page($this->SI['page'])->select();
		return $List;
	}
	
	function getListByUser()
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->UserID, $this->SI['uid'])
								->addRange($this->F->LogDate, $this->SI['f_startdate'], $this->SI['f_enddate'], true, true)
								->getWhere();
		$this->TotalCount = $this->where($Where)->count($this->F->ProductID);
		if($this->TotalCount === false) { return false; }
		$List = $this->where($Where)->order($this->F->LogDate . ' DESC')
									->limit($this->SI['count'])->page($this->SI['page'])->select();
		return $List;
	}
	
	function getTotal()
	{
		$WB = new WhereBuilder();
		$Where = $WB->addRange($this->F->LogDate, $this->SI['f_startdate'], $this->SI['f_enddate'])
								->getWhere();
		$Result = $this->where($Where)->count($this->F->ProductID);
		return $Result;
	}
	
	
}
?>