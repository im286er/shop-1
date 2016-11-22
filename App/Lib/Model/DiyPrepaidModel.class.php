<?php
/**
 * diy_unit
 *
 */
class DiyPrepaidModel extends Model {

	public function getUdinfoByUpid($upid,$pid){ //通过订单ID和产品ID获取user_diy的信息
		$UPD=M("user_prepaid_detail")->field("up_product_info")->where("up_id=".$upid)->find();
		$upd_arr=unserialize($UPD['up_product_info']);
        //var_dump($upd_arr);
		foreach($upd_arr as $key => $value){
			//var_dump(unserialize($value['diy_unit_info']));
			if($value['p_id']==$pid){$productArr=$value;/*获得product的详细信息数组*/}
		}
		$udinfo=$this->get_udinfo($productArr);
		return $udinfo;
	}
	
	function get_udinfo($udinfo){//根据udinfo数组来返回整个表单数据
		$diy_unit_info=unserialize($udinfo['diy_unit_info']);
       //var_dump($diy_unit_info);
		$cid=$udinfo['p_cate_4'];
		$UD=M("diy_unit")->where("cid=".$cid)->order("sort")->select();
       //var_dump($UD);

        foreach($UD as $key =>$value){
			$udinfo[$value['unit_name']]=$diy_unit_info[$value['id']];
		}
        //var_dump($udinfo);
        //exit;
		return $udinfo;
	}


	

}
?>