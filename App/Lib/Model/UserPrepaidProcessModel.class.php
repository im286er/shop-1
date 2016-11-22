<?php
/*
*
*   时间：2014-11-11
*	用户订单后台处理操作类
*/
class UserPrepaidProcessModel extends Model
{
	public function getProcessByUpid($up_id){//由订单up_id得到订单处理记录集
		$sql="select TUPP.id,TUPP.up_id,TUPP.done_process,TUPP.done_time,TUPP.done_remark,TUPP.done_usermail,TUPP.express_number,TUPP.done_name from tdf_user_prepaid_process as TUPP ";
		$sql.="where TUPP.up_id=".$up_id." order by TUPP.done_process";	
		$result=M()->query($sql);
    	return $result;
	}
	
	
	public function getCurrentProcess($processArr){
		$result=0;
		if($processArr){
			foreach($processArr as $key=>$value){
				if($value['done_process']>$result){
					$result=$value['done_process'];
				}
			}
		}else{
			$result=0;
		}
		return $result;
	}
		
	//根据处理单号和处理状态返回结果
	public function getProcessByUpidPro($up_id,$done_process){
		$result=M("user_prepaid_process")->where("up_id=".$up_id." and done_process=".$done_process."")->find();
		return $result;
	}
	
	public function updateProcessByUpidPro($up_id,$postinfo){ //更新or新增操作
		$doneuser=$_SESSION['my_info'];
		$data['done_usermail']	= $doneuser['email'];
		$data['done_name']		= $doneuser['nickname'];
		$data['done_process']	= $postinfo['done_process'];
		$data['done_time']		= get_now();
		$data['done_remark']	= $postinfo['done_remark'];
		if($this->getProcessByUpidPro($up_id,$postinfo['done_process'])){
			$result=M("user_prepaid_process")->where("up_id=".$up_id." and done_process=". intval($postinfo['done_process'])."")->save($data);
		}else{
			$data['up_id']	= $up_id;
			$result=M("user_prepaid_process")->add($data);
		}
		if($postinfo['done_process']==6){//如果操作步骤为6
			$this->updateExpress($up_id,$postinfo);
		}
		return $result;
	}
	
	public function updateExpress($up_id,$postinfo){ //更新快递信息
		$UPEM=M("user_prepaid_express");
		$res=$UPEM->where("up_id=".$up_id)->find();
		$data['up_id']			=$postinfo['up_id'];
		$data['done_name']		=$postinfo['done_name'];
		$data['express_com']	=$postinfo['express_com'];
        $data['express_number']	=$postinfo['express_number'];
        $data['express_time']	=$postinfo['express_time'];
		if($res){
			$result=$UPEM->where("up_id=".$up_id)->save($data);
		}else{
			$result=$UPEM->add($data);
		}
		return $data;
	}
	

	public function getCurrentProcessByUpid($up_id){//返回当前的订单处理进程
		$sql="SELECT done_process FROM tdf_user_prepaid_process WHERE up_id=".$up_id." ORDER BY done_process DESC LIMIT 1";
		$result=$this->query($sql);
		return intval($result[0]['done_process']);
	}
	
	public function delProcessByID($ID){
		$result=M("user_prepaid_process")->where("id=".$ID."")->delete();
		return $result;
	}
	
	
}
?>