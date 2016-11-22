<?php
class LitterUserModel extends Model
{
	/**
	 * @var DBF_LitterUser
	 */
	public $F;
	
	public $TotalCount = 0;
	
	public function __construct()
	{
		load('@.WhereBuilder');
		$this->F = DBF_LitterUser::construct();
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}
	
	function getInBox($SI)
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->To, $SI['uid'])
								->addNotEq($this->F->DelTo, 1)
								->getWhere();
		$this->TotalCount = $this->where($Where)->count($this->F->ID);
		if($this->TotalCount === false) { return false; }
		
		$List = $this->where($Where)->order($this->F->ID . ' DESC')
		->limit($SI['count'])->page($SI['page'])->select();
		return $List;
	}
	
	function getOutBox($SI)
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->From, $SI['uid'])
								->addNotEq($this->F->DelFrom, 1)
								->getWhere();
		$this->TotalCount = $this->where($Where)->count($this->F->ID);
		if($this->TotalCount === false) { return false; }
	
		$List = $this->where($Where)->order($this->F->ID . ' DESC')
		->limit($SI['count'])->page($SI['page'])->select();
		return $List;
	}
	
	function getDetail($LUID, $UID)
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->ID, $LUID)
								->addEq($this->F->To, $UID)
								->addNotEq($this->F->DelTo, 1)
								->getWhere();
		return $this->where($Where)->find();
	}
	
	public function getUnreadCount($UID)
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->To, $UID)
								->addEq($this->F->IsRead, 0)
								->getWhere();
		return $this->where($Where)->count($this->F->ID);
	}
	
	function deleteInvalidItem()
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->DelFrom, 1)
								->addEq($this->F->DelTo, 1)
								->getWhere();
		return $this->where($Where)->delete();
	}
}
?>