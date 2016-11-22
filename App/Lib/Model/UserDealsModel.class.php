<?php

class UserDealsModel extends Model
{

	/**
	 *
	 * @var DBF
	 */
	protected $DBF;

	protected $tableName = 'user_deals';

	protected $fields = array(
			'ud_id', 
			'ud_buyer', 
			'ud_seller', 
			'ud_pid', 
			'ud_pprice', 
			'ud_dealdate', 
			'ud_ipaddress', 
			'ud_status', 
			'_pk' => 'ud_id', 
			'_autoinc' => TRUE);

	public function __construct()
	{
		parent::__construct();
		$this->DBF = new DBF();
	}

	public function getDealListByBuyer($BuyerID)
	{
		$DBF_UD = $this->DBF->UserDeals;
		return $this->where($DBF_UD->Buyer . "='" . $BuyerID . "'")->select();
	}

	public function getDealListBySeller($SellerID)
	{
		$DBF_UD = $this->DBF->UserDeals;
		return $this->where($DBF_UD->Seller . "='" . $SellerID . "'")->select();
	}

	public function getDealListByProduct($PID)
	{
		$DBF_UD = $this->DBF->UserDeals;
		return $this->where($DBF_UD->ProductID . "='" . $PID . "'")->select();
	}

	public function getIsbuyByUidPid($UID,$PID){
		$DBF_UD = $this->DBF->UserDeals;
		$ISH		= $this->where($DBF_UD->Buyer . "='" . $UID . "' and ".$DBF_UD->PID."='" . $PID."'" )->select();
		if($ISH){
			$result=1;
		}else{
			$result=0;
		}
		return $result;
	}
	
	public function addRecord($BuyerID, $SellerID=0, $Product, $IP, $Status)
	{
		$DBF_UD = $this->DBF->UserDeals;
		$DBF_P = $this->DBF->Product;
		$data = array(
				$DBF_UD->Buyer => $BuyerID, 
				$DBF_UD->Seller => $Product[$DBF_P->Creater], 
				$DBF_UD->PID => $Product[$DBF_P->ID], 
				$DBF_UD->ProductPrice => $Product[$DBF_P->Price], 
				$DBF_UD->DealDate => get_now(), 
				$DBF_UD->IPAddress => $IP, 
				$DBF_UD->Status => $Status);
		return $this->add($data);
	}
}