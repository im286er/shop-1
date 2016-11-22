<?php
/**
 * API RESTaction
 *
 * @author miaomin 
 * Oct 11, 2013 7:40:46 PM
 * 
 * $Id: RestAction.class.php 1239 2014-02-20 06:51:24Z miaomiao $
 */
class RestAction extends CommonAction {

	// 公钥
	protected $PUBKEY = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
	
	// 默认输出格式
	protected $DEFAULT_FORMAT = 'json';
	
	// 允许接受的类名
	protected $ALLOW_CLASS_NAME = array (
			'models',
			'users',
			'orders',
            'demo',
			'webgl',
			'front'
	);
	
	// debug
	protected $DEBUG = 0;
	
	/**
	 * API RESTaction
	 *
	 * @throws Exception
	 */
	public function __construct() {
		// @TODO
		
		header('Access-Control-Allow-Origin: *');//跨域提交
		header('Access-Control-Allow-Headers: Content-Type');
		header('Access-Control-Allow-Methods: *');
		// 需要客户端做一个HEAD AGENT信息
		/*
		 * 对参数做一个校验(完整、有效、安全)
		 */
		
		// API_KEY的校验
		// $this->checkApiKey ( $this->_request ( 'api_key' ) );
		
		// 具体的REST服务
		
		// 返还结果
		$res = array ();
		$http_accept=$_SERVER['HTTP_ACCEPT'];
		if(strpos($http_accept,'application/json')!==false){
			$requestDatas=json_decode(file_get_contents('php://input'),true);
		}else{
			$requestDatas=$_REQUEST;
		}

		try {
			// debug模式打开直接返回请求参数

			$this->DEBUG = ($this->_request ( 'debug' ) == 1) ? 1 : 0;
			
			// $this->DEBUG = 0;
			
			if ($this->DEBUG) {
				pr ( $_REQUEST );
				pr ( $_FILES );

				// TODO
				// 直接计入数据库
				exit ();
			}

			// 验证参数口令校验
			$this->validRequest ( $requestDatas );

			$op_array = explode ( '.', $requestDatas ['method'] );
			$klass = ucwords ( $op_array [0] ) . 'Action';
         	$method = $op_array [1];
            $reflector = new ReflectionClass ( $klass );
            $inst = $reflector->newInstanceArgs();

			// 不能使用$this->_request()
			$args = $this->encodeArguments ( $requestDatas );
			// 返回结果
			$res = call_user_func_array ( array (
					$inst,
					$method 
			), $args );
			// 处理返回结果
			$this->processResult ( $res );
			$res = $this->addStatCode ( $res, $this->RES_CODE_TYPE ['OK'], $this->RES_MESSAGE [$this->RES_CODE_TYPE ['OK']] );
			// 记录日志
			$this->addRestLog ( $this->RES_CODE_TYPE ['OK'] );
			// 返回结果
			$this->outputResult ( $res, $requestDatas['format']);
			exit ();
		} catch ( Exception $e ) {
			// 异常日志记录
			$this->addRestLog ( $e->getMessage () );
			// 输出格式
			$format = $this->validFormat ( $requestDatas['format'] ) ? $requestDatas['format'] : $this->DEFAULT_FORMAT;
			$res = $this->addStatCode ( $res, $e->getMessage (), $this->RES_MESSAGE [$e->getMessage ()] );
			$this->outputResult ( $res, $format );
			exit ();
		}
	}
	
	/**
	 * 添加日志
	 */
	private function addRestLog($responseCode) {
		$logindata = $this->parseRequestUserHandle ( $this->_request ( 'visa' ) );
		if ($logindata) {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if ($userinfo) {
				$logData ['u_id'] = $userinfo [0] ['u_id'];
			} else {
				$logData ['u_id'] = 0;
			}
		} else {
			$logData ['u_id'] = 0;
		}
		$logData ['log_method'] = $this->_request ( 'method' ) ? $this->_request ( 'method' ) : '';
		$logData ['log_request'] = serialize ( $_REQUEST );
		$logData ['log_files'] = serialize ( $_FILES );
		$logData ['log_response'] = $responseCode;
		$res = $this->addLog ( $logData );
		return $res;
	}
	
	/**
	 * 校验REQUEST
	 */
	private function validRequest($req) {
		if (! $this->validMethod ( $req ['method'] )) {
			// 命令校验
			throw new Exception ( $this->RES_CODE_TYPE ['PARAMETER_METHOD_ERR'] );
		}
		
		/*if (! $this->validFormat ( $req ['format'] )) {
			// 输出格式校验
			throw new Exception ( $this->RES_CODE_TYPE ['PARAMETER_FORMAT_ERR'] );
		}*/
		// @formatter:off
		if (! $this->validSign ( $req )) {
			// 口令校验
			throw new Exception ($this->RES_CODE_TYPE ['SIGN_ERR'] ); 
		}
		// @formatter:on
	}
	
	/**
	 * 校验操作方法
	 */
	private function validMethod($method) {
		$methodArr = explode ( '.', $method );
		if (is_array ( $methodArr ) && (count ( $methodArr ) == 2) && in_array ( $methodArr [0], $this->ALLOW_CLASS_NAME )) {
			return true;
		}
		return false;
	}
	
	/**
	 * 校验输出格式
	 */
	private function validFormat($format) {
		$formatArr = C ( 'API.OUTPUT_FORMAT_TYPE' );
		if (in_array ( $format, $formatArr )) {
			return true;
		}
		return false;
	}
	
	/**
	 * 加识别码
	 *
	 * @param mixed $result        	
	 * @param string $code        	
	 * @param string $msg        	
	 * @return unknown
	 */
	private function addStatCode($result, $code, $msg) {
        $str_code=strval($code);
		$arr = array (
				'code' => $str_code,
				'msg' => $msg 
		);
		array_unshift ( $result, $arr );
		return $result;
	}
	
	/**
	 * 输出结果
	 *
	 * @param mixed $result        	
	 * @param string $format        	
	 */
	private function outputResult($result, $format = 'xml') {
		switch ($format) {
			case 'xml' :
				header ( 'Content-type: text/xml' );
				break;
			case 'json' :
				header ( 'Content-type: application/json' );
				break;
		}
		$this->response ( $result, $format );
	}
	
	/**
	 * 处理返回结果
	 *
	 * @param mixed $result        	
	 * @return mixed
	 */
	private function processResult($result) {
		if (! $result) {
			throw new Exception ( $this->RES_CODE_TYPE ['RESULT_ERR'] );
		}
	}
	
	/**
	 * 口令校验
	 *
	 * @return bool
	 */
	private function validSign($data) {
		//var_dump($data);

		$res = false;
		
		foreach ( $data as $key => $val ) {
			if ($key === 'vcode') {
				$vcode = intval ( $val );
			} elseif ($key === 'sign') {
				$sign = $val;
			} elseif ($key !== '_URL_') {
				$parameter .= $key . '=' . $val . '&';
			}
		}
		
		// TODO
		// 简易的参数生成以后还是要去掉的
		$parameter = 'method=' . $data ['method'] . '&visa=' . $data ['visa'] . '&format=' . $data ['format'] . '&';
		//echo $parameter;
		if ((! empty ( $vcode )) && (! empty ( $sign )) && (! empty ( $parameter ))) {
			$parameter = substr ( $parameter, 0, - 1 );
			$genSign = $this->genSign ( $parameter, $vcode, $this->PUBKEY );
			//writelog ("api", "<br>vcode:".$vcode. "<br>genSign:" . $genSign."<br>postSign:".$sign."<br>".$data['visa']);

			if ($genSign === $sign) {
				$res = true;
			}
		}
		
		return $res;
	}
	
	/**
	 * 生成一个签名
	 *
	 * @param string $parameter        	
	 * @param $vcode //
	 *        	必须是1-28
	 * @param string $pubkey
	 *        	// 必须是32位
	 */
	private function genSign($parameter, $vcode, $pubkey) {
		$cutstart = $vcode - 1;
		// echo 'CutKey:' . substr ( $pubkey, $cutstart, 4 );
		return md5 ( md5 ( $parameter ) . substr ( $pubkey, $cutstart, 4 ) );
	}
	
	/**
	 * 将文件信息并入REQUEST中
	 *
	 * @param array $file        	
	 * @param array $request        	
	 */
	private function fileMergeInRequest($file, $request) {
		foreach ( $file as $key => $val ) {
			foreach ( $val as $k => $v ) {
				$request ['UP_' . $key . '_' . $k] = $v;
			}
		}
		return $request;
	}
}
?>