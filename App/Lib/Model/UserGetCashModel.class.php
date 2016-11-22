<?php

class UserGetCashModel extends Model
{

	protected $tableName = 'user_getcash';

	public $TotalCount = 0;

	protected $fields = array(
			'ug_id', 
			'u_id', 
			'ug_amount', 
			'ug_appdate', 
			'ug_applydate', 
			'ug_admin', 
			'ug_remark', 
			'ug_status', 
			'_pk' => 'u_id', 
			'_autoinc' => TRUE);

	function addRequest($UID, $Amount, $Remark, $Status)
	{
		$DBF_UGC = new DBF_UserGetCash();
		$data = array(
				$DBF_UGC->UserID => $UID, 
				$DBF_UGC->Amount => $Amount, 
				$DBF_UGC->AppDate => get_now(), 
				$DBF_UGC->Remark => $Remark, 
				$DBF_UGC->Status => $Status);
		return $this->add($data);
	}

	function getList($Status, $Page = 0, $DispCount = 20)
	{
		$DBF_UGC = new DBF_UserGetCash();
		$this->TotalCount = $this->where($DBF_UGC->Status . "='" . $Status . "'")->count();
		return $this->page($Page)->limit($DispCount)->where(
															$DBF_UGC->Status . "='" .
															 $Status . "'")->select();
	}

	function getListByUserID($UID)
	{
		$DBF_UGC = new DBF_UserGetCash();
			// $this->TotalCount = $this->where($DBF_UGC->UserID . "='" . $UID .
		// "'")->count();
		return $this->where($DBF_UGC->UserID . "='" . $UID . "'")->select();
	}
	
	function getRecordByID($WID)
	{	
		$DBF_UGC = new DBF_UserGetCash();
		return $this->where($DBF_UGC->ID . "='" . $WID . "'")->find();
	}
	
	function setStatusByID($ID, $Status)
	{
		$DBF_UGC = new DBF_UserGetCash();
		$data = array($DBF_UGC->Status => $Status,
									$DBF_UGC->ApplyDate => get_now());
		return $this->where($DBF_UGC->ID . "='" . $ID . "'")->save($data);
	}
}