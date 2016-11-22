<?php

require_once (dirname(__FILE__).'/HttpNetwork.php');

/**
 * 如果您的 PHP 没有安装 cURL 扩展，请先安装 
 */
if (!function_exists('curl_init'))
{
	throw new Exception('OpenAPI needs the cURL PHP extension.');
}

/**
 * 如果您的 PHP 不支持JSON，请升级到 PHP 5.2.x 以上版本
 */
if (!function_exists('json_decode'))
{
	throw new Exception('OpenAPI needs the JSON PHP extension.');
}

/**
 * 错误码定义
 */
define('OPENAPI_ERROR_RESPONSE_DATA_INVALID', 1803); // 返回包格式错误
define('OPENAPI_ERROR_CURL', 1900); // 网络错误, 偏移量1900, 详见 http://curl.haxx.se/libcurl/c/libcurl-errors.html

/**
 * 提供访问捞3D企业版API的接口
 */
class Lao3dApiV1
{	
	private $appkey = '';	
    private $server_name = 'www.lao3d.com';
	
	/**
	 * 构造函数
     * 
	 * @param string $appkey 企业版的密钥
	 */
	function __construct($appkey)
	{		
		$this->appkey = $appkey;
	}
	
    
    public function setServerName($server_name)
	{
		$this->server_name = $server_name;
	}
    
	
	/**
	 * 执行API调用，返回结果数组
	 *
	 * @param string $script_name 调用的API方法，比如/openApi/uploadModel，参考 http://e.lao3d.com/developer.html
	 * @param array $params 调用API时带的参数
	 * @param string $method 请求方法 post / get
	 * @param string $protocol 协议类型 http / https
	 * @return array 结果数组。成功结果为{"ret": 0, "xx":"data"}，失败 {"ret": errorcode, "msg": "error msg"}
	 */
	public function api($script_name, $params, $method='post', $protocol='http')
	{			
		$params['appkey'] = $this->appkey;				
	
		$url = $protocol . '://' . $this->server_name . $script_name;
		$cookie = array();       	
		
		$ret = HttpNetwork::makeRequest($url, $params, $cookie, $method, $protocol);
		
		if (false === $ret['result'])
		{
			$result_array = array(
				'ret' => OPENAPI_ERROR_CURL + $ret['errno'],
				'msg' => $ret['msg'],
			);
			return $result_array;
		}
		
		$result_array = json_decode($ret['msg'], true);
		
		// 远程返回的不是 json 格式, 说明返回包有问题
		if (is_null($result_array)) {
			$result_array = array(
				'ret' => OPENAPI_ERROR_RESPONSE_DATA_INVALID,
				'msg' => $ret['msg']
			);
		}
        
		return $result_array;
	}
	
	/**
	 * 执行上传文件API调用，返回结果数组
	 *
	 * @param string $script_name 调用的API方法，比如/openApi/uploadModel，参考 http://e.lao3d.com/developer.html
	 * @param array $params 调用API时带的参数，必须是array
	 * @param array $array_files 调用API时带的文件，必须是array，key为openapi接口的参数，value为"@"加上文件全路径的字符串
	 *															  举例 array('pic'=>'@/home/xxx/hello.jpg',...);
	 * @param string $protocol 协议类型 http / https
	 * @return array 结果数组。 成功结果为{"ret": 0, "xx":"data"}，失败 {"ret": errorcode, "msg": "error msg"}
	 */
	public function apiUploadFile($script_name, $params, $array_files, $protocol='http')
	{		
		$params['appkey'] = $this->appkey;
		
		foreach($array_files as $k => $v)
		{
			$params[$k] = $v ;
		}
		
		$url = $protocol . '://' . $this->server_name . $script_name;
		$cookie = array(); 
		
		$ret = HttpNetwork::makeRequestWithFile($url, $params, $cookie, $protocol);
		
		if (false === $ret['result'])
		{
			$result_array = array(
				'ret' => OPENAPI_ERROR_CURL + $ret['errno'],
				'msg' => $ret['msg'],
			);
			return $result_array;
		}
		
		$result_array = json_decode($ret['msg'], true);
		
		// 远程返回的不是 json 格式, 说明返回包有问题
		if (is_null($result_array)) {
			$result_array = array(
				'ret' => OPENAPI_ERROR_RESPONSE_DATA_INVALID,
				'msg' => $ret['msg']
			);
		}
        
		return $result_array;
	}

	/**
	 * 打印出请求串的内容，当API中的这个函数的注释放开将会被调用。
	 *
	 * @param string $url 请求串内容
	 * @param array $params 请求串的参数，必须是array
	 * @param string $method 请求的方法 get / post
	 */
	private function printRequest($url, $params,$method)
	{
		$query_string = HttpNetwork::makeQueryString($params);
		if($method == 'get')
		{
			$url = $url."?".$query_string;
		}
		echo "\n============= request info ================\n\n";
		print_r("method : ".$method."\n");
		print_r("url    : ".$url."\n");
		if($method == 'post')
		{
			print_r("query_string : ".$query_string."\n");
		}
		echo "\n";
		print_r("params : ".print_r($params, true)."\n");
		echo "\n";
	}
	
	/**
	 * 打印出返回结果的内容，当API中的这个函数的注释放开将会被调用。
	 *
	 * @param array $array 待打印的array
	 */
	private function printRespond($array)
	{
		echo "\n============= respond info ================\n\n";
		print_r($array);
		echo "\n";
	}
    
}

// end of script
