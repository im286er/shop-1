<?php

class UserPrepaidDetailModel extends Model
{
	/**
	 * @var DBF
	 */
	protected $DBF;

	protected $tableName = 'user_prepaid_detail';

	protected $fields = array(
			'id', 
			'up_id', 
			'up_product_info', 
			'ctime', 
			'isdel', 
			'_pk' => 'id', 
			'_autoinc' => TRUE);

	public function __construct()
	{
		parent::__construct();
		$this->DBF = new DBF();
	}


	public function getPrepaidDetailByUpid($up_id){
		$DBF_UP = $this->DBF->UserPrepaidDetail;
		return $this->where($DBF_UP->UpID . "=" . $up_id )->find();
	}
	
	public function addRecord($up_id,$up_product_info)//新增订单商品快照信息
	{
		$DBF_UP = $this->DBF->UserPrepaidDetail;
		$data = array(
				$DBF_UP->UpID => $up_id, 
				$DBF_UP->ProductInfo => $up_product_info, 
				$DBF_UP->Isdel => 0
				);
		return $this->add($data);
	}
	
	public function updatePrepaidDetailByUpid($upid,$upProductInfo){//更新支付方式和收获地址
		$datas['up_id']             = $upid;
		$datas['up_product_info']   = serialize($upProductInfo);
		return $this->where("up_id=".$upid)->save($datas);
	}
	
	/* 根据upid(订单处理单号)和pid(产品id)获得用户diy的数据
	 * by zhangzhibin 2014.11.02
	 * param int @upid  订单处理单号
	 * param int @pid   产品id */
	private function getUdinfoByUpid($upid,$pid){//
		$UPD=M("user_prepaid_detail")->field("up_product_info")->where("up_id=".$upid)->find();
		$upd_arr=unserialize($UPD['up_product_info']);
		//var_dump($upd_arr);
		foreach($upd_arr as $key => $value){
			if($value['p_id']==$pid){
				$productArr=$value;/*获得product的详细信息数组*/
			}
		}
		$udinfo=$this->get_udinfo($productArr);
		return $udinfo;
	}
	
	/*根据udinfo数组来返回整个表单数据 zhangzhibin 2014.11.02
	 * param array @udinfo 产品信息数组
	 * 输出产品信息数组，附加DIY的属性信息和属性的值*/
	private function get_udinfo($udinfo){//根据udinfo数组来返回整个表单数据
		$diy_unit_info=unserialize($udinfo['diy_unit_info']);
		$UD=M("diy_unit")->where("cid=".$udinfo['p_cate_4']." and ishidden=0")->order("sort")->select();
		foreach($UD as $key =>$value){
			//echo "ID:".$value['id'];
			$udinfo[$value['unit_name']]=$diy_unit_info[$value['id']];
		}
		return $udinfo;
	}
	

	
}