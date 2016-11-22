<?php

class LogVTransModel extends Model
{

	/**
	 *
	 * @var DBF
	 */
	protected $DBF;

	protected $tableName = 'log_vtrans';

	protected $fields = array(
			'vl_id', 
			'u_id', 
			'vl_bef_vc', 
			'vl_aft_vc', 
			'vl_bef_vcav', 
			'vl_aft_vcav', 
			'vl_vc', 
			'vl_type', 
			'vl_dealtype', 
			'vl_dealid', 
			'vl_adddate', 
			'_pk' => 'vl_id', 
			'_autoinc' => TRUE);

	public function __construct()
	{
		parent::__construct();
		$this->DBF = new DBF();
	}

	public function addLog($User, $VCoin, $Type, $DealType, $DealID)
	{//$type 0减少 1增加 2冻结
		$DBF_U 			= $this->DBF->Users;
		$DBF_LVT 		= $this->DBF->LogVTrans;
		$AfterVC 		= $User[$DBF_U->Vcoin];
		$AfterVC_av = $User[$DBF_U->Vcoin_av];
		if($Type == 2){
			$AfterVC_av -= $VCoin; 
		}else{
			$AfterVC		= $Type == 0 ? $AfterVC - $VCoin : $AfterVC + $VCoin;
			$AfterVC_av = $Type == 0 ? $AfterVC_av - $VCoin : $AfterVC_av + $VCoin;
		}
		$data = array(
				$DBF_LVT->UserID 		=> $User[$DBF_U->ID],
				$DBF_LVT->Bef_VC 		=> $User[$DBF_U->Vcoin],
				$DBF_LVT->Aft_VC		=> $AfterVC,
				$DBF_LVT->Bef_VCav 	=> $User[$DBF_U->Vcoin_av],
				$DBF_LVT->Aft_VCav 	=> $AfterVC_av,
				$DBF_LVT->VCoin 		=> $VCoin,
				$DBF_LVT->Type 			=> $Type,
				$DBF_LVT->DealType 	=> $DealType,
				$DBF_LVT->DealID 		=> $DealID,
				$DBF_LVT->AddDate 	=> get_now());
		return $this->add($data);
	}
}
?>