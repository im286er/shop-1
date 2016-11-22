<?php
/**
 * API EXTERNALaction
 *
 * @author miaomin 
 * Feb 20, 2014 2:39:39 PM
 *
 * $Id: ExternalAction.class.php 1242 2014-02-20 08:34:39Z miaomiao $
 */
class ExternalAction extends CommonAction {
	
	// 默认输出格式
	protected $DEFAULT_FORMAT = 'json';
	
	// 允许接受的类名
	protected $ALLOW_CLASS_NAME = array (
			'demo' 
	);
	
	// debug
	protected $DEBUG = 0;
	
	/**
	 * API EXTERNALaction
	 *
	 * @throws Exception
	 */
	public function __construct() {
		// 返还结果
		$res = array ();
		
		try {
			// debug模式打开直接返回请求参数
			$this->DEBUG = ($this->_request ( 'debug' ) == 1) ? 1 : 0;
			
			if ($this->DEBUG) {
				pr ( $_REQUEST );
				pr ( $_FILES );
				
				// TODO
				// 直接计入数据库
				exit ();
			}
			
			// 验证参数口令校验
			$this->validRequest ( $_REQUEST );
			
			$op_array = explode ( '.', $this->_request ( 'method' ) );
			$klass = ucwords ( $op_array [0] ) . 'Action';
			$method = $op_array [1];
			$reflector = new ReflectionClass ( $klass );
			$inst = $reflector->newInstanceArgs ();
			// 不能使用$this->_request()
			$args = $this->encodeArguments ( $_REQUEST );
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
			$this->outputResult ( $res, $this->DEFAULT_FORMAT );
			exit ();
		} catch ( Exception $e ) {
			// 异常日志记录
			$this->addRestLog ( $e->getMessage () );
			$res = $this->addStatCode ( $res, $e->getMessage (), $this->RES_MESSAGE [$e->getMessage ()] );
			$this->outputResult ( $res, $this->DEFAULT_FORMAT );
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
	 * 加识别码
	 *
	 * @param mixed $result        	
	 * @param string $code        	
	 * @param string $msg        	
	 * @return unknown
	 */
	private function addStatCode($result, $code, $msg) {
		$arr = array (
				'code' => $code,
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
}
?>