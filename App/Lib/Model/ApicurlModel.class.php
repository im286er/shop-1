<?php
/**
 * 网页向rp360请求(curl)api接口
 * 
 * @author zhangzhibin 
 *
 *
 * $Id$
 */

class ApicurlModel extends Model {
	
	private $_publicKey = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';// 公钥
	private $_remoteHost = 'http://192.168.20.73/rp360';	// 远程主机地址
	private $_serviceUrl = '/api.php/services/rest';		// REST服务地址
	private $_restUrl;										// REST服务调用地址
	private $_ua = 'phpCurl-agent/1.0';						// User-Agent
	public function __construct() {
		$this->_restUrl = $this->_remoteHost . $this->_serviceUrl;
	}
	
	/*获取一个Vcode*/
	private function _genVcode() {
		$min = 1;
		$max = 28;
		return genvcode ( $min, $max );
	}
	
	
	/**
	 * CurlPost
	 *
	 * @param array $curlReq
	 * @param int $return
	 * @return mixed
	 */
	private function _curlPost($curlReq, $return = 0) {
		// CURL
		$ch = curl_init ();
		curl_setopt_array ( $ch, array (
		CURLOPT_POST => 1,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $this->_restUrl,
		CURLOPT_POSTFIELDS => $curlReq,
		CURLOPT_USERAGENT => $this->_ua
		) );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
	
		// 这句话不能拿掉啊拿掉就返回不到结果啦！！！
		if ($return) {
			return $response;
		} else {
			print_r ( $response );
		}
	}
	
//用户注册到rp360
	public function register($userarr) {
		echo header ( "Content-Type:text/html; charset=utf-8" );
		$method = 'users.register';
		$format = 'nodata';
		$debug = 0;
		$user 		=$userarr['u_email'];
		$pass 		=$userarr['u_pass'];
		$uarr 		=array('dispname'=>$userarr['u_dispname'],'salt'=>$userarr['u_salt']);
		$userinfo	=pub_encode_pass(json_encode($uarr),$this->_publicKey);  //加密地址参数
		$visa = base64_encode ( $user . ' ' . $pass );
		$visa = pub_encode_pass($visa,$this->_publicKey,"encode");
		$curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
		$vcode = $this->_genVcode();
		$sign = gensign ( $curlPost, $vcode, $this->_publicKey );
		$curlPost = array (
			'method' 	=> $method,
			'visa' 		=> $visa,
			'format' 	=> $format,
			'vcode' 	=> $vcode,
			'sign' 		=> $sign,
			'userinfo'	=> $userinfo,
			'debug' 	=> $debug
		);
		$this->_restUrl = RP360_API_URL;
		$this->_curlPost ( $curlPost );
	}
	
	public function getversion() { //获得版本信息
		$method = 'users.getversion';
		$format = 'json';
		$debug = 0;
		$user = 'rp360@bitmap.com.cn';
		$pass ='cannotlogin';
		$visa = base64_encode ( $user . ' ' . $pass );
		$visa = pub_encode_pass($visa,$this->_publicKey,"encode");
		//$visa ="05ef957brdVT4FBlQKAXtPL0VjJQFCO2YKEHRUK0RRYyRcfDB6XWx+LE0tMmIHbFgnA301I187XDNSdlQwRX1kAl18DnpYeX0GTTg3Dg8=";
		$curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
		$vcode = $this->_genVcode ();
		$vcode=21;
		$sign = gensign ( $curlPost, $vcode, $this->_publicKey );
		//echo $sign;
		$curlPost = array (
				'method' => $method,
				'visa' => $visa,
				'format' => $format,
				'vcode' => $vcode,
				'sign' => $sign,
				'debug' => $debug
		);
		return $this->_curlPost ( $curlPost,1 );
	}

	public function getdiyapi($up_id,$p_id){
		$method = 'webgl.diyinfo';
		$format = 'json';
		$debug = 0;
		$user = 'wow730@gmail.com';
		$pass = '123456' ;
		$visa = base64_encode ( $user . ' ' . $pass );
		$visa = pub_encode_pass($visa,$this->_publicKey,"encode");
		//$visa ="05ef957brdVT4FBlQKAXtPL0VjJQFCO2YKEHRUK0RRYyRcfDB6XWx+LE0tMmIHbFgnA301I187XDNSdlQwRX1kAl18DnpYeX0GTTg3Dg8=";
		$curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
		$vcode = $this->_genVcode ();
		$vcode=21;
		$sign = gensign ( $curlPost, $vcode, $this->_publicKey );
		//echo $sign;
		$curlPost = array (
				'method' => $method,
				'visa' => $visa,
				'format' => $format,
				'vcode' => $vcode,
				'sign' => $sign,
				'debug' => $debug,
				'up_id'=>$up_id,
				'p_id'=>$p_id
		);
		return $this->_curlPost ( $curlPost,1 );
	}
	
	
	
	
}