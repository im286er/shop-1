<?php
/**
 *阿里云的 oss
 *
 *@author zhangzhibin
 */

class AliossModel extends Model {

	public function __construct() {
		Vendor('Alioss.sdk');
		$this->oss_sdk_service = new ALIOSS();
		$this->oss_sdk_service->set_debug_mode(FALSE);
		$this->bucket="3dcity"; //设置oss上的bucket，如果此类用于其他的bucket，必须修改或者传参。
		//设置是否打开curl调试模式
		
		parent::__construct ();
	}
	
	/*
	 * 文件是否存在 
	 * @file 文件全路径（含文件名称）
	 * return 1  文件存在  0不存在
	 * zhangzhibin 2014-07-10
	 */
	public function file_exist($file){ //文件是否存在
		$patterns ='/^[.]/';
		$ossfile=preg_replace($patterns,'',$file);	//正则去除路径第一个'.'
		$ossfile=preg_replace('/^[\/]/','',$ossfile);//正则去除路径第一个'/'
		
		$existobj=$this->oss_sdk_service->is_object_exist($this->bucket,$ossfile);
		$status=$this->status($existobj);
		return $status == '2' ? 1 : 0;
	}
	
	/**
	 * 因为阿里云OSS返回状态为2XX时，均代表成功，故取返回值的第一位是否为2判断是否成功
	 */
	public function status($response){
		$rt='0';
		$rstatus=$response->status;
		if ($rstatus > ''){
			$rt=substr($rstatus,0,1);
		}
		return $rt;
	}
	
	
	public function getdownurl($filename){//获取下载地址
		if(C('DOWNTYPE')){ //读取默认下载模式设置 如果OSS优先
			if($this->file_exist($filename)){ //如果oss端文件存在
				$result=$this->getFileUrl($filename);
			}else{
				$result=getfilepath($filename);
			}
		}else{
			$result=getfilepath($filename);
		}
		return $result;
	}
	
	
	
	public function getFileUrl($file){ //得到oss的文件下载路径
		$patterns ='/^[.]/';
		$ossfile=preg_replace($patterns,'',$file);	//正则去除路径第一个'.'
		$ossfile=preg_replace('/^[\/]/','',$ossfile);//正则去除路径第一个'/'
		$bucket = $this->bucket;
		$timeout=10;
		$options = array(
				ALIOSS::OSS_CONTENT_TYPE => 'txt/html',
		);
		$response = $this->oss_sdk_service->get_sign_url($this->bucket,$ossfile,$timeout);
		return $response;
	}
	
	
	//通过P_ID上传模型文件到oss
	public function upfileOssByPidSingle($p_id){
		$webpath=substr(str_replace("ThinkPHP/","",THINK_PATH),0,-1);//当前网站目录的物理路径
		$TP_db2				=M("product","tdf_","DB_CONFIG2");
		$TPFile_db2			=M("product_file","tdf_","DB_CONFIG2");
		$sql="select pf_path,pf_filename from tdf_product_file where p_id= ".$p_id."";
		$pf_arr=$TPFile_db2->query($sql);
		//var_dump($pf_arr);
		//exit;
		foreach($pf_arr as $key => $value){
			$temppath=substr(getfilepath($value['pf_path']),0,-1);//如果路径前面带点就去掉
			$ossFilePath= substr($temppath,1,strlen($temppath)).'/'.$value['pf_filename']; //oss文件路径
			$webFilePath=IS_WIN?str_replace("/", "\\", $webpath.$temppath).'\\'.$value['pf_filename']:$webpath.$temppath.'/'.$value['pf_filename'];//webserver 文件路径,如果是linux就为‘\’
			if(file_exists_case($webFilePath)){//文件是否存在
				$file_ext=true;
			}else{
				$ftppath="/".$ossFilePath;
				$file_ext=$this->downloadftp($ftppath, $webFilePath);
			}
			if($file_ext){//如果文件存在
				$local_objectfile_size=filesize($webFilePath); //本地文件大小
				$file_exist_info=$this->oss_sdk_service->is_object_exist($this->bucket,$ossFilePath);
				if(intval($file_exist_info->header['content-length'])!==$local_objectfile_size){
					$response = $this->oss_sdk_service->upload_file_by_file($this->bucket,$ossFilePath,$webFilePath);
					$TP=$TP_db2->where("p_id=".$p_id)->setField("p_oss", 1);
					if($response){$result=true;}
				}
			}
		}
		return $result;
	}
	
	//通过ftp下载文件
	private function downloadftp($ftppath,$webpath){
		import('ORG.Ftp.Ftp');
		$ftp = new Ftp();//实例化对象
		//$data['server'] = '115.29.190.154';//服务器地址(IP or domain)
		$data['server'] = '115.29.230.39';//服务器地址(IP or domain)
		$data['username'] = 'wtftp';//ftp账户
		$data['password'] = 'Bitmap2013gdi';//ftp密码
		$data['port'] = 21;//ftp端口,默认为21
		$data['pasv'] = false;//是否开启被动模式,true开启,默认不开启
		$data['ssl'] = false;//ssl连接,默认不开启
		$data['timeout'] = 60;//超时时间,默认60,单位 s
		$productarr=1;
		if($ftp->start($data)){
			$result=$ftp->download($ftppath,$webpath);
		}
		return $result;
	}
	
	

	
	

}