<?php
class LitterSysIndexModel extends Model
{
	/**
	 * @var DBF_LitterSys_Index
	 */
	public $F;
	
	public function __construct()
	{
		load('@.WhereBuilder');
		$this->F = DBF_LitterSys_Index::construct();
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}

	public function addLitterIndex($LitterID)
	{
		$UM = new UsersModel();
		$IDList = $UM->field($UM->F->ID)->select();
		if($IDList === false) { return false; }
		$IDList = array_column($IDList, $UM->F->ID);
		foreach($IDList as $ID)
		{
			$data = array(
				$this->F->LitterID => $LitterID,
				$this->F->UserID => $ID,
			);
			if($this->add($data) === false) { return false; }
		}
		return true;
	}
	
	public function getUnreadCount($UID)
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->UserID, $UID)
								->addEq($this->F->isRead, 0)
								->getWhere();
		return $this->where($Where)->count($this->F->LitterID);
	}
	
	function deleteLitter($LSID, $UID)
	{
		$WB = new WhereBuilder();
		$Where = $WB->addEq($this->F->LitterID, $LSID)
								->addEq($this->F->UserID, $UID)
								->getWhere();
		return $this->where($Where)->delete();
	}
}
?>