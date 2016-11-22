<?php
class LitterSysModel extends Model
{
	/**
	 * @var DBF_LitterSys
	 */
	public $F;
	
	public $TotalCount = 0;
	
	public function __construct()
	{
		load('@.WhereBuilder');
		$this->F = DBF_LitterSys::construct();
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}
	
	function getInboxList($SI)
	{
		$WB = new WhereBuilder();
		$Where = $WB->addRange($this->F->DownDate, $SI['f_startdate'], $SI['f_enddate'], true, true)
								->getWhere();
		$this->TotalCount = $this->where($Where)->count($this->F->ID);
		if($this->TotalCount === false) { return false; }
		
		$List = $this->where($Where)->order($this->F->ID . ' DESC')
									->limit($this->SI['count'])->page($this->SI['page'])->select();
		return $List;
	}
	
	function getListByUser($SI, $UID)
	{
		$F_I = DBF_LitterSys_Index::construct();
		$this->F->dispPrefix(true); $F_I->dispPrefix(true);
		$WB = new WhereBuilder();
		$Where = $WB->addEq($F_I->UserID, $UID)
								->getWhere();
		$this->TotalCount = $this->join($F_I->_Table . ' ON ' . $F_I->LitterID . '=' . $this->F->ID)
															->where($Where)->count($this->F->ID);
		if($this->TotalCount === false) { return false; }
		
		$List = $this->join($F_I->_Table . ' ON ' . $F_I->LitterID . '=' . $this->F->ID)
									->where($Where)->order($this->F->ID . ' DESC')
									->limit($this->SI['count'])->page($this->SI['page'])
									->select();
		return $List;
	}
	
	function getDetail($LSID, $UID)
	{
		$F_I = DBF_LitterSys_Index::construct();
		$this->F->dispPrefix(true); $F_I->dispPrefix(true);
		$WB = new WhereBuilder();
		$Where = $WB->addEq($F_I->UserID, $UID)
								->addEq($this->F->ID, $LSID)
								->getWhere();
		return $this->where($Where)->find();
	}
}
?>