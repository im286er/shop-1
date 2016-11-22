<?php
/**
 * Curl通用方法类
 *
 * @author miaomin 
 * Feb 19, 2014 3:44:31 PM
 *
 * $Id: Ncurl.php 1243 2014-02-20 08:34:53Z miaomiao $
 */
class Ncurl {
	
	// Curl URL
	private $_url = '';
	
	// User-Agent
	private $_ua = 'Ncurl-agent/1.0';
	
	// 公钥
	private $_publicKey = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
	
	// Post数据结构
	private $_postDataHeader = array (
			'method' => '',
			'visa' => '',
			'format' => '',
			'vcode' => '',
			'sign' => '',
			'debug' => 0 
	);
	
	/**
	 * Curl通用方法类
	 */
	public function __construct($Url) {
		if (filter_var ( $Url, FILTER_VALIDATE_URL )) {
			$this->_url = $Url;
		} else {
			die ( 'Not validation curl url!' );
		}
	}
	
	/**
	 * 生成Visa信息
	 *
	 * 一般使用用户名和密码组成Visa信息
	 *
	 * @param array $visaData        	
	 * @return string
	 */
	private function _genVisa(array $visaData) {
		$user = $visaData ['user'];
		$pass = $visaData ['pass'];
		$visaBase64 = base64_encode ( $user . ' ' . $pass );
		$visa = $this->_pubEncodePass ( $visaBase64, $this->_publicKey, "encode" );
		return $visa;
	}
	
	/**
	 * 生成Vcode信息
	 *
	 * 一般生成一个1-28之间的整数
	 *
	 * @return int
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
	 * @return string
	 */
	private function _genSign(array $curlPost) {
		$postStr = 'method=' . $curlPost ['method'] . '&visa=' . $curlPost ['visa'] . '&format=' . $curlPost ['format'] . '';
		$vcode = $curlPost ['vcode'] >= 1 ? $curlPost ['vcode'] - 1 : 1;
		$sign = md5 ( md5 ( $postStr ) . substr ( $this->_publicKey, $vcode, 4 ) );
		return $sign;
	}
	
	/**
	 * 数据加解密算法
	 *
	 *
	 * @param unknown_type $tex        	
	 * @param unknown_type $key        	
	 * @param unknown_type $type        	
	 * @return boolean Ambigous boolean>
	 */
	private function _pubEncodePass($tex, $key, $type = "encode") {
		$chrArr = array (
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z',
				'A',
				'B',
				'C',
				'D',
				'E',
				'F',
				'G',
				'H',
				'I',
				'J',
				'K',
				'L',
				'M',
				'N',
				'O',
				'P',
				'Q',
				'R',
				'S',
				'T',
				'U',
				'V',
				'W',
				'X',
				'Y',
				'Z',
				'0',
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9' 
		);
		if ($type == "decode") {
			if (strlen ( $tex ) < 14)
				return false;
			$verity_str = substr ( $tex, 0, 8 );
			$tex = substr ( $tex, 8 );
			if ($verity_str != substr ( md5 ( $tex ), 0, 8 )) {
				// 完整性验证失败
				return false;
			}
		}
		$key_b = $type == "decode" ? substr ( $tex, 0, 6 ) : $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62];
		$rand_key = $key_b . $key;
		$rand_key = md5 ( $rand_key );
		$tex = $type == "decode" ? base64_decode ( substr ( $tex, 6 ) ) : $tex;
		$texlen = strlen ( $tex );
		$reslutstr = "";
		for($i = 0; $i < $texlen; $i ++) {
			$reslutstr .= $tex {$i} ^ $rand_key {$i % 32};
		}
		if ($type != "decode") {
			$reslutstr = trim ( $key_b . base64_encode ( $reslutstr ), "==" );
			$reslutstr = substr ( md5 ( $reslutstr ), 0, 8 ) . $reslutstr;
		}
		return $reslutstr;
	}
	
	/**
	 * 设置PublicKey
	 *
	 * @param unknown_type $key        	
	 */
	public function setPublicKey($key) {
		if (strlen ( $key ) >= 32) {
			$this->_publicKey = $key;
		}
	}
	
	/**
	 * CurlPost2
	 *
	 * 供3DCity内部使用
	 *
	 * @param string $method        	
	 * @param string $format        	
	 * @param array $userinfo        	
	 * @param array $postdata        	
	 * @param int $return        	
	 * @param int $debug        	
	 */
	public function curlPost2($method, $format, $userinfo, $postdata, $return, $debug = 0) {
		// 生成Visa
		$visa = $this->_genVisa ( $userinfo );
		// 生成Vcode
		$vcode = $this->_genVcode ();
		// 组织postData参数
		$curlPost = $this->_postDataHeader;
		$curlPost ['method'] = $method;
		$curlPost ['visa'] = $visa;
		$curlPost ['format'] = $format;
		$curlPost ['vcode'] = $vcode;
		$curlPost ['sign'] = '';
		$curlPost ['debug'] = $debug;

		// 拼接POST数据
		$curlPost = array_merge ( $curlPost, $postdata );
		// 生成签名
		$sign = $this->_genSign ( $curlPost );
		$curlPost ['sign'] = $sign;
		if ($return) {
			return $this->curlPost ( $curlPost, $return );
		} else {
			$this->curlPost ( $curlPost, $return );
		}
	}
	
	/**
	 * CurlPost
	 *
	 * @param array $curlReq        	
	 * @param int $return        	
	 * @return mixed
	 */
	public function curlPost($curlReq, $return = 1) {
		// CURL
		$ch = curl_init ();
		curl_setopt_array ( $ch, array (
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $this->_url,
				CURLOPT_POSTFIELDS => http_build_query ( $curlReq ),
				CURLOPT_USERAGENT => $this->_ua 
		) );
		$response = curl_exec ( $ch );
		
		$curlinfo = curl_getinfo ( $ch );
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
	 * CurlWX
	 *
	 * @param int $return        	
	 * @return mixed
	 */
	static public function curlWX($url, $data, $cookie = false, $isPost = TRUE) {
		$dataStr = "";
		if ($data && is_array ( $data )) {
			foreach ( $data as $key => $value ) {
				$dataStr .= "$key=$value&";
			}
		}
		
		// 启动一个CURL会话
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // 对认证证书来源的检查
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 1 ); // 从证书中检查SSL加密算法是否存在
		curl_setopt ( $curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] ); // 模拟用户使用的浏览器
		curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
		
		if ($isPost) {
			curl_setopt ( $curl, CURLOPT_POST, 0 ); // 发送一个常规的Post请求
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $dataStr ); // Post提交的数据包
		}
		
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制 防止死循环
		curl_setopt ( $curl, CURLOPT_HEADER, 1 ); // 显示返回的Header区域内容
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
		
		if ($cookie) {
			curl_setopt ( $curl, CURLOPT_COOKIE, $cookie );
		}
		$tmpInfo = curl_exec ( $curl ); // 执行操作
		if (curl_errno ( $curl )) {
			echo 'Errno' . curl_error ( $curl ); // 捕抓异常
			return;
		}
		curl_close ( $curl ); // 关闭CURL会话
		                      
		// 解析HTTP数据流
		list ( $header, $body ) = explode ( "\r\n\r\n", $tmpInfo );
		if (! $cookie) {
			// 解析COOKIE
			$cookie = "";
			preg_match_all ( "/set\-cookie: (.*)/i", $header, $matches );
			if (count ( $matches ) == 2) {
				foreach ( $matches [1] as $each ) {
					$cookie .= trim ( $each ) . ";";
				}
			}
		}
		return array (
				"cookie" => $cookie,
				"body" => trim ( $body ) 
		);
	}
}
?>