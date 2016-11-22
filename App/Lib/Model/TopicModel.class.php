<?php

class TopicModel extends Model
{
	//@formatter:off
	/**
	 * @var DBF
	 */
	protected $DBF;
	protected $trueTableName = 'tdf_topic';
	protected $fields = array('t_id', 't_name', 't_remark', 't_template', 
														'_pk' => 'p_id', '_autoinc' => true);
	public $TotalCount = 0;
	
	public function __construct()
	{
		parent::__construct();
		$this->DBF = new DBF();
	}
	
	public function getTopicByID($TID)
	{
		$Topic = $this->where($this->DBF->Topic->ID . "='" . $TID . "'")->select(); 
		return $Topic !== false ? $Topic[0] : false;
	}
	
	public function getProductsByTID($TID)
	{
		$Topic = $this->DBF->Topic->Table;
		$T_ID = $Topic . '.' . $this->DBF->Topic->ID;
		
		$TopicIndex = $this->DBF->TopicIndex->Table;
		$TI_TID = $TopicIndex . '.' . $this->DBF->TopicIndex->TopicID;
		$TI_PID = $TopicIndex . '.' . $this->DBF->TopicIndex->ProductID;
		
		$Product = $this->DBF->Product->Table;
		$P_ID= $Product . '.' . $this->DBF->Product->ID;
		
		$this->join($TopicIndex . ' ON ' . $TI_TID . ' = ' . $T_ID)
					->join($Product . ' ON ' . $TI_PID . ' = ' . $P_ID);
		return  $this->where($T_ID."='".$TID."'")->select();
	}

	public function getTopicList($Page = 1, $DispCount = 10)
	{
		$this->TotalCount = $this->count();
		return $this->page($Page)->limit($DispCount)->select();
	}
	
	public function insertTopic($Name, $Remark, $Template)
	{
		$data = array('t_name' => $Name, 
									't_remark' => $Remark, 
									't_template' => $Template);
		$this->startTrans();
		if($this->add($data) !== false) { $this->commit(); return true; }
		else{ $this->rollback(); return false; }
	}
	
	public function updateTopic($TID, $Name, $Remark, $Template)
	{
		$data = array('t_name' => $Name,
									't_remark' => $Remark,
									't_template' => $Template);
		$this->startTrans();
		if($this->where($this->DBF->Topic->ID . "='" . $TID . "'")->save($data) !== false)
		{ $this->commit(); return true; }
		else{ $this->rollback(); return false; }
	}
	
	public function deleteTopic($TID)
	{
		$this->startTrans();
		if($this->where($this->DBF->Topic->ID . "='" . $TID . "'")->delete() !== false)
		{ $this->commit(); return true; }
		else{ $this->rollback(); return false; }
	}
}
?>