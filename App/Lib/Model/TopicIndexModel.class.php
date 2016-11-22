<?php

class TopicIndexModel extends Model
{
	/**
	 * @var DBF
	 */
	protected $DBF;
	protected $trueTableName = 'tdf_topic_index';
	protected $fields = array('t_id', 'p_id');

	public function __construct()
	{
		parent::__construct();
		$this->DBF = new DBF();
	}

	public function checkProductExist($TID, $PID)
	{
		$Result = $this->where($this->DBF->TopicIndex->TopicID . "='" . $TID . "' AND " . 
														$this->DBF->TopicIndex->ProductID . "='" . $PID . "'")->select();
		if($Result !== false)
		{ return $Result === null ? false : true; }
		else { return null; }
	}

	public function addProduct($TID, $PID)
	{
		$data = array($this->DBF->TopicIndex->TopicID => $TID, 
									$this->DBF->TopicIndex->ProductID => $PID);
		return $this->add($data);
	}
	
	public function deleteProduct($TID, $PID)
	{
		return $this->where($this->DBF->TopicIndex->TopicID . "='" . $TID . "' AND " . 
												$this->DBF->TopicIndex->ProductID . "='" . $PID . "'")->delete();
	}
}
?>