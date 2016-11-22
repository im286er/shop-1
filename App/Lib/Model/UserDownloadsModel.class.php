<?php
/**
 * 用户下载记录类
 *
 * @author miaomin 
 * Jul 22, 2013 11:38:30 AM
 */
class UserDownloadsModel extends Model {
	protected $tableName = 'user_downloads';
	protected $fields = array (
			'udl_id',
			'u_id',
			'p_id',
			'p_author',
			'p_vp',
			'p_rp',
			'udl_downdate',
			'udl_ipaddress',
			'udl_status',
			'_pk' => 'udl_id',
			'_autoinc' => TRUE 
	);
	
	/**
	 * 增加一条记录
	 *
	 * @param Object $ua        	
	 * @param Object $p        	
	 * @return boolean
	 */
	public function addRecord($ua, $p) {
	
		
		$this->create ();
		$this->u_id = $ua->u_id;
		$this->p_id = $p->p_id;
		$this->p_author = $p->p_creater;
		$this->p_vp = $p->p_vprice;
		$this->udl_downdate = get_now ();
		$this->udl_ipaddress = get_client_ip ();
		return $this->add();
	}
	
	/*
	 * 根据数组增加一条记录
	 * $UID:用户ID  $Model：模型信息数组
	 * By zhangzhibin
	 */
	public function addRecordByArr($UID,$Model){
		//$this->create();
		$UDF=$this->where("u_id=".$UID." and p_id=".$Model['p_id']."")->find();
		if(!$UDF){
			$this->u_id 		= $UID;
			$this->p_id 		= $Model['p_id'];
			$this->p_author = $Model['p_creater'];
			$this->p_vp 		= $Model['p_vprice'];
			$this->p_rp 		= $Model['p_price'];
			$this->udl_downdate = get_now ();
			$this->udl_ipaddress = get_client_ip ();
			//$this->add();
			//echo "aaabbbbbbbbbbbbbbbbbbccccccccccccc";
			return $this->add();
		}else{
			return 0;
		}
	}
	
	public function getModelByUid($UID){//根据用户ID得到用户拥有的模型    2013-11-01
		$UM=$this->where("u_id=".$UID."")->select();
		if($UM){
			return $UM;
		}else{
			return 0;
		}
	}
	

	
}
?>