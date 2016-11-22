<?php

class UserDealsVcoinModel extends Model
{

	/**
	 * 积分交易
	 * @var DBF
	 */
	protected $DBF;

	protected $tableName = 'user_deals_vcoin';

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
		$DBF_UD = $this->DBF->UserDealsVcoin;
		return $this->where($DBF_UD->Buyer . "='" . $BuyerID . "'")->select();
	}

	public function getDealListBySeller($SellerID)
	{
		$DBF_UD = $this->DBF->UserDealsVcoin;
		return $this->where($DBF_UD->Seller . "='" . $SellerID . "'")->select();
	}
	
	public function getIsbuyByUidPid($UID,$PID){
		
		$DBF_UD = $this->DBF->UserDealsVcoin;
		$ISH		= $this->where($DBF_UD->Buyer . "='" . $UID . "' and ".$DBF_UD->PID."='" . $PID."'" )->select();
		if($ISH){
			$result=1;
		}else{
			$result=0;
		}
		return $result;
	}

	public function getDealListByProduct($PID)
	{
		$DBF_UD = $this->DBF->UserDealsVcoin;
		return $this->where($DBF_UD->ProductID . "='" . $PID . "'")->select();
	}

	public function addRecord($BuyerID, $SellerID=0, $Product, $IP, $Status){//新增记录
		$DBF_UD = $this->DBF->UserDealsVcoin;
		$DBF_P = $this->DBF->Product;
		$temp_is=$this->where($DBF_UD->PID."=".$Product[$DBF_P->ID]." and ".$DBF_UD->Buyer."=".$BuyerID."")->select();
		if(!$temp_is){
			$data = array(
					$DBF_UD->Buyer => $BuyerID,
					$DBF_UD->Seller => $Product[$DBF_P->Creater],
					$DBF_UD->PID => $Product[$DBF_P->ID],
					$DBF_UD->ProductPrice => $Product[$DBF_P->VPrice],
					$DBF_UD->DealDate => get_now(),
					$DBF_UD->IPAddress => $IP,
					$DBF_UD->Status => $Status);
			return $this->add($data);
		}else{
			return 0;
		}
	
	}
}