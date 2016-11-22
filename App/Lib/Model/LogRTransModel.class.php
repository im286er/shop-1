<?php

class LogRTransModel extends Model
{

	/**
	 *
	 * @var DBF
	 */
	protected $DBF;

	protected $tableName = 'log_rtrans';

	protected $fields = array(
			'rl_id', 
			'u_id', 
			'rl_bef_rc', 
			'rl_aft_rc', 
			'rl_bef_rcav', 
			'rl_aft_rcav', 
			'rl_rc', 
			'rl_type', 
			'rl_dealtype', 
			'rl_dealid', 
			'rl_adddate', 
			'_pk' => 'rl_id', 
			'_autoinc' => TRUE);

	public function __construct()
	{
		parent::__construct();
		$this->DBF = new DBF();
	}

	
	/*
	 * rcoin操作日志
	 * @param $Type 0减少， 1增加
	 * 
	 */
	public function addLog($User, $RCoin, $Type, $DealType, $DealID)
	{

		$DBF_U = $this->DBF->UserAccount;
		$DBF_LRT = $this->DBF->LogRTrans;
		$AfterRC = $User[$DBF_U->Rcoin];
		$AfterRC_av = $User[$DBF_U->Rcoin_av];

		if($Type == 2) {
			$AfterRC_av = $AfterRC_av - $RCoin; 
		}else{
			$AfterRC = $Type == 0 ? $AfterRC - $RCoin : $AfterRC + $RCoin;
			$AfterRC_av = $Type == 0 ? $AfterRC_av - $RCoin : $AfterRC_av + $RCoin;
		}
		$data = array(
				$DBF_LRT->UserID => $User[$DBF_U->ID],
				$DBF_LRT->Bef_RC => $User[$DBF_U->Rcoin],
				$DBF_LRT->Aft_RC => $AfterRC,
				$DBF_LRT->Bef_RCav =>  $User[$DBF_U->Rcoin_av],
				$DBF_LRT->Aft_RCav => $AfterRC_av,
				$DBF_LRT->RCoin => $RCoin,
				$DBF_LRT->Type  => $Type,
				$DBF_LRT->DealType => $DealType,
				$DBF_LRT->DealID => $DealID,
				$DBF_LRT->AddDate => get_now());

		return $this->add($data);
	}
}
?>