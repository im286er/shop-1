<?php
/**
 *阿里云的 oss 引入新版SDK
 *2016.10.19
 *@author zhangzhibin
 */

class AliyunossModel extends Model {
	public function __construct() {
		Vendor('AliyunOss.autoload');
		$accessKeyId    = "LTAIwGwOIVwAgAuR";
		$accessKeySecret= "tiSSFPT4qhbcbGsWLYlpgRmyn9h9AW";
		$endpoint       = OSSPATH;      //OSS内网域名
		$endpoint_in    = OSSPATH_IN;   //OSS外网域名
		$this->ossClient= new \OSS\OssClient($accessKeyId,$accessKeySecret,$endpoint);
		$this->ossBucket= 'ignite';
		parent::__construct ();
	}
	/**
	 * 把本地变量的内容到文件
	 * 简单上传,上传指定变量的内存值作为object的内容
	 * @param $object 存储文件的路径(带文件名的完整路径)
	 * @param string $content 存储文件的内容
	 * @return null
	 */
	public function putObject($object,$content){
		$object=preg_replace('/^[.]/','',$object);	//正则去除路径第一个'.'
		$object=preg_replace('/^[\/]/','',$object);   //正则去除路径第一个'/'
		try {
			$result=$this->ossClient->putObject($this->ossBucket, $object,$content);
		} catch (OssException $e) {
			printf($e->getDetails() . "\n");
			return;
		}

	}

	/**
	 * 判断object是否存在
	 * @param object $object 存储文件的路径(带文件名的完整路径)
	 * @return null
	 */
	function doesObjectExist($object){
		return $this->ossClient->doesObjectExist($this->ossBucket, $object);
	}











	
	

}