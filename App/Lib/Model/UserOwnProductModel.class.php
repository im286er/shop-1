<?php

class UserOwnProductModel extends Model
{

	/**
	 *
	 * @var DBF
	 */
	protected $DBF;

	protected $tableName = 'user_own_product';

	protected $fields = array('u_id', 'p_id', 'p_creater', 'uop_type');

	public function __construct()
	{
		parent::__construct();
		$this->DBF = new DBF();
	}

	public function IsUserBuyProduct($UID, $PID)
	{
		if($UID){
			$BDF_UOP = $this->DBF->UserOwnProduct;
			$Result = $this->where(
					$BDF_UOP->UserID . "=" . $UID . " AND " . $BDF_UOP->ProductID .
					"='" . $PID . "'")->select();
		}else{
			$Result=0;
		}
		return $Result;
	}

	//!
	public function getOwnProductListByUser($UID)
	{
		$BDF_UOP = $this->DBF->UserOwnProduct;
		return $this->where($BDF_UOP->UserID . "='" . $UID . "'")->select();
	}

	public function addRecord($UID, $PID, $CreaterID, $Type)
	{
		$BDF_UOP = $this->DBF->UserOwnProduct;
		$data = array(
				$BDF_UOP->UserID => $UID, 
				$BDF_UOP->ProductID => $PID, 
				$BDF_UOP->Creater => $CreaterID, 
				$BDF_UOP->Type => $Type);
		if(!$this->IsUserBuyProduct($UID, $PID)){
			return $this->add($data);
		}else{
			return 1;
		}

	}
}
?>