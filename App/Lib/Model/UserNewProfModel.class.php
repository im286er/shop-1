<?php
class UserNewProfModel extends Model {
	protected $tableName = 'user_newprof';
	
	
	/*
	 * 返回所有的专注领域数组
	 * @返回数组格式  arr[$id]=$profname   数组的key为id,值为profname
	 * by zhangzhibin 2014-12-23
 	 */
	public function getAllProfArr(){
		$profInfo=M('user_newprof')->where("1=1")->select();
		foreach($profInfo as $key => $value){
			$result[$value['id']]=$value['profname'];
		}
		return $result;
	}
	
	/*
	 * 根据u_newprof得到专注领域
	 */
	public function getProfByNewprof($profstr){
		$profArr=explode(",", $profstr);
		$proConf=$this->getAllProfArr();
		foreach($profArr as $key => $value){
			$newprof[$key]=$proConf[$value];
		}
		$result=implode(',',$newprof);
		return $result;
	}
}
?>