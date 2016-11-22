<?php
class PayTypeModel extends Model
{
	/**
	 * @var DBF
	 */
	protected $DBF;
	/**
	 * @var DBF_PayType
	 */
	public $F;
	
	public function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->PayType;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}
	
	public function get_paytype($showtype=0){
		if(!$showtype){//默认是网页端的支付方式
			$where=" and webmode=1 "; //网页端的支付方式
		}else{
			$where=" and mmode=1 ";   //手机端支付方式
		}
		return $this->where($this->F->IsUsed."=1".$where)->order('sort')->select();
	}
	

	public function get_paytypeByPtid($ptid){
		return $this->where($this->F->PtID."='" . $ptid . "'")->select();
	}
	
	
}
?>