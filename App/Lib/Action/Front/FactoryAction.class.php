<?php
/**
 * Curl行为类(Client)
 * 
 * @author miaomin 
 * Nov 20, 2013 2:01:50 PM
 *
 * $Id: FactoryAction.class.php 1262 2014-07-03 11:20:57Z miaomiao $
 */
class FactoryAction extends CommonAction {
	
	// 公钥
	private $_publicKey = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
	
	// 远程主机地址
	private $_remoteHost = 'http://192.168.20.73/city/';
	
	// REST服务地址
	private $_serviceUrl = 'api.php/services/rest';
	
	// REST服务调用地址
	private $_restUrl;
	
	// User-Agent
	private $_ua = 'phpCurl-agent/1.0';
	
	/**
	 *
	 * @var UsersModel
	 */
	protected $_user;
	
	/**
	 * Curl行为类(Client)
	 */
	public function __construct() {
		
		// 父类继承
		parent::__construct ();
		
		// Curl
		$this->_restUrl = $this->_remoteHost . $this->_serviceUrl;
	}
	
	/**
	 * 发送邮件(Client)
	 */
	public function qsendmail() {
		$method = 'demo.sendmail';
		$format = 'xml';
		$debug = 0;
		$user = 'miaomin@bitmap3d.com.cn';
		$pass = md5 ( '123456' );
		$visa = base64_encode ( $user . ' ' . $pass );
		$vcode = $this->_genVcode ();
		
		// 发送邮件必要的参数
		$uid = 25;
		$mail = 'wow730@gmail.com';
		
		$curlPost = array (
				'method' => $method,
				'visa' => $visa,
				'format' => $format,
				'vcode' => $vcode,
				'sign' => '',
				'debug' => $debug,
				'uid' => $uid,
				'mail' => $mail 
		);
		$curlPost ['sign'] = $this->_genSign ( $curlPost );
		$this->_curlPost ( $curlPost );
	}
	
	/**
	 * 保存webgl截图(Client)
	 *
	 * 2014.7.9
	 */
	public function savewebgl() {
		$method = 'webgl.savewebgl';
		$format = 'xml';
		$debug = 0;
		$user = 'miaomin@bitmap3d.com.cn';
		$pass = md5 ( '123456' );
		$visa = base64_encode ( $user . ' ' . $pass );
		$vcode = $this->_genVcode ();
		
		// 必要的参数
		$pyArgs = array ();
		$pyArgs ['pmwid'] = 50;
		$pyArgs ['webglpath'] = '/home/wwwroot/default/upload/productfile/40/f5/21544/30f711e109f23435/texture/';
		$pyArgs ['webglfilename'] = 'Camion_g.js';
		$pyArgs ['webgldata'] = 'xxxxxxxxxxxxxxxxxxxxx';
		
		$curlPost = array (
				'method' => $method,
				'visa' => $visa,
				'format' => $format,
				'vcode' => $vcode,
				'sign' => '',
				'debug' => $debug,
				'pmwid' => $pyArgs ['pmwid'],
				'webglpath' => $pyArgs ['webglpath'],
				'webglfilename' => $pyArgs ['webglfilename'],
				'webgldata' => $pyArgs ['webgldata'] 
		);
		$curlPost ['sign'] = $this->_genSign ( $curlPost );
		$this->_curlPost ( $curlPost );
	}
	
	/**
	 * 保存webgl贴图(Client)
	 *
	 * 2014.7.31
	 */
	public function savetexture() {
		$method = 'webgl.savetexture';
		$format = 'xml';
		$debug = 0;
		$user = 'miaomin@bitmap3d.com.cn';
		$pass = md5 ( '123456' );
		$visa = base64_encode ( $user . ' ' . $pass );
		$vcode = $this->_genVcode ();
		
		// 必要的参数
		$pyArgs = array ();
		$pyArgs ['pid'] = 21544;
		$pyArgs ['texturedata'] = base64_encode ( file_get_contents ( '3.png' ) );
		
		$curlPost = array (
				'method' => $method,
				'visa' => $visa,
				'format' => $format,
				'vcode' => $vcode,
				'sign' => '',
				'debug' => $debug,
				'pid' => $pyArgs ['pid'],
				'texturedata' => $pyArgs ['texturedata'] 
		);
		$curlPost ['sign'] = $this->_genSign ( $curlPost );
		$this->_curlPost ( $curlPost );
	}
	
	/**
	 * 保存webgl截图(Client)
	 *
	 * 2014.7.9
	 */
	public function savecapture() {
		$method = 'webgl.savecapture';
		$format = 'xml';
		$debug = 0;
		$user = 'miaomin@bitmap3d.com.cn';
		$pass = md5 ( '123456' );
		$visa = base64_encode ( $user . ' ' . $pass );
		$vcode = $this->_genVcode ();
		
		// 必要的参数
		$pyArgs = array ();
		$pyArgs ['pid'] = 21544;
		$pyArgs ['capturedata'] = base64_encode ( file_get_contents ( '3.png' ) );
		
		$curlPost = array (
				'method' => $method,
				'visa' => $visa,
				'format' => $format,
				'vcode' => $vcode,
				'sign' => '',
				'debug' => $debug,
				'pid' => $pyArgs ['pid'],
				'capturedata' => $pyArgs ['capturedata'] 
		);
		$curlPost ['sign'] = $this->_genSign ( $curlPost );
		$this->_curlPost ( $curlPost );
	}
	
	/**
	 * 模擬Python请求API(Client)
	 *
	 * 2014.7.3
	 */
	public function pycall() {
		$method = 'webgl.pycall';
		$format = 'xml';
		$debug = 0;
		$user = 'miaomin@bitmap3d.com.cn';
		$pass = md5 ( '123456' );
		$visa = base64_encode ( $user . ' ' . $pass );
		$vcode = $this->_genVcode ();
		
		// 必要的参数
		$pyArgs = array ();
		$pyArgs ['id'] = 1066;
		$pyArgs ['status'] = 1;
		$pyArgs ['webfile'] = '/3DPrinter/Uploads/goods.rar';
		$pyArgsJson = json_encode ( $pyArgs );
		
		$curlPost = array (
				'method' => $method,
				'visa' => $visa,
				'format' => $format,
				'vcode' => $vcode,
				'sign' => '',
				'debug' => $debug,
				'pyargs' => $pyArgsJson 
		);
		$curlPost ['sign'] = $this->_genSign ( $curlPost );
		$this->_curlPost ( $curlPost );
	}
	
	/**
	 * webgl转码(Client)
	 *
	 * 2014.4.24
	 */
	public function webgl() {
		$method = 'webgl.fbx2webgl';
		$format = 'xml';
		$debug = 0;
		$user = 'miaomin@bitmap3d.com.cn';
		$pass = md5 ( '123456' );
		$visa = base64_encode ( $user . ' ' . $pass );
		$vcode = $this->_genVcode ();
		
		// 必要的参数
		$pid = 1066;
		$fbxpath = '/3DPrinter/Uploads/goods.rar';
		
		$curlPost = array (
				'method' => $method,
				'visa' => $visa,
				'format' => $format,
				'vcode' => $vcode,
				'sign' => '',
				'debug' => $debug,
				'pid' => $pid,
				'fbxpath' => $fbxpath 
		);
		$curlPost ['sign'] = $this->_genSign ( $curlPost );
		$this->_curlPost ( $curlPost );
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
		
		// $curlinfo = curl_getinfo ( $ch );
		// pr ( $curlinfo );
		curl_close ( $ch );
		
		// 这句话不能拿掉啊拿掉就返回不到结果啦！！！
		if ($return) {
			return $response;
		} else {
			print_r ( $response );
		}
	}
	
	/**
	 * 获取一个Vcode
	 */
	private function _genVcode() {
		$min = 1;
		$max = 28;
		return mt_rand ( $min, $max );
	}
	
	/**
	 * 根据Post数据生成一个签名
	 *
	 * @param array $curlPost        	
	 */
	private function _genSign(array $curlPost) {
		$postStr = 'method=' . $curlPost ['method'] . '&visa=' . $curlPost ['visa'] . '&format=' . $curlPost ['format'] . '';
		$vcode = $curlPost ['vcode'] >= 1 ? $curlPost ['vcode'] - 1 : 1;
		$sign = md5 ( md5 ( $postStr ) . substr ( $this->_publicKey, $vcode, 4 ) );
		return $sign;
	}
}