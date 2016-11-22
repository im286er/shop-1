<?php

class ShapewaysApi {
	var $consumer_key    = "c31558c9f85d32536bf69c6a3b6bc9d012e4b39f";
  var	$consumer_secret = "10e02348409386691089cc80e7805806cee7ec20";
  
  var $access_token 	= 'a08535453991625c05e4535c141588ebd879a795';
  var $access_secret 	= 'bd16262b0a19ebf3a406fb74b8463ba03771a73f';
  
  var $api_url_base  = "http://api.shapeways.com/";
  
  var $verbose_debug = FALSE;
  
  protected $oauth;
  
 
 
  function __construct(){
  	$this->oauth = new Oauth($this->consumer_key, $this->consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);
  	$this->oauth->enableDebug();
  	$res =$this->oauth->setToken($this->access_token, $this->access_secret);
  }
  
  function modelget($modelid){
  	try {
  		$this->oauth->fetch($this->api_url_base ."/models/$modelid/v1", null, OAUTH_HTTP_METHOD_GET, array("Accept" => "application/json"));
  		$response = $this->oauth->getLastResponse();
  		$json = json_decode($response);
  		return $json;
  	} catch(OAuthException $E) {
  		Error("fetch exception", $E->getMessage(), null, $this->oauth->getLastResponseInfo(), $E->debugInfo);
  	}
  }
  
  function modelpropget($page = 1){
  	try {
  		$this->oauth->fetch($this->api_url_base ."/models/v1", null, OAUTH_HTTP_METHOD_GET, array("Accept" => "application/json"));
  		$response = $this->oauth->getLastResponse();
  		$json = json_decode($response);
  		return $json;
  	} catch(OAuthException $E) {
  		Error("fetch exception", $E->getMessage(), null, $this->oauth->getLastResponseInfo(), $E->debugInfo);
  	}
  }
  
  
  function uploadmodel($fileurl){//上传模型到shapeways 
  	/*try {
  		$oauth = new Oauth($this->consumer_key, $this->consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);
  		$oauth->enableDebug();
  		$res = $oauth->setToken($this->access_token, $this->access_secret);
  		//var_dump($res);
  	
  	} catch(OAuthException $E) {
  		Error("setup exception", $E->getMessage(), null, null, $E->debugInfo);
  	}*/
  	 	
  	try {
  			//$filename = "boat.obj";
  			//$file = file_get_contents("./". $filename);
  			
  		$file = file_get_contents( WEBROOT.$fileurl);
  		$filename=substr($fileurl,-9);  		
	

  		echo filesize(WEBROOT.$fileurl);
  		$data = array("fileName" => "$filename",
  				"file" => rawurlencode(base64_encode($file)),
  				"hasRightsToModel" => 1,
  				"acceptTermsAndConditions" => 1,
  				//"isPublic" => 1,
  				//"viewState" => 1,
  		);
  		$data_string = json_encode($data);
  		//var_dump($data_string);
  		//exit;
  		$this->oauth->fetch($this->api_url_base ."/models/v1", $data_string, OAUTH_HTTP_METHOD_POST, array("Accept" => "application/json"));
  		$response = $this->oauth->getLastResponse();
  		//echo "<br>response:";
  		//var_dump($response);
  		echo "<br>";
  		$json = json_decode($response);
  	} catch(OAuthException $E) {
  		echo 'Catch Exception';
  		$this->Error("fetch exception", $E->getMessage(), null, $this->oauth->getLastResponseInfo(), $E->debugInfo);
  	}
  	return $json;
  }
  
  
  function printerlist(){//返回??列表
  	try {
  		$this->oauth->fetch($this->api_url_base ."/printers/v1", null, OAUTH_HTTP_METHOD_GET, array("Accept" => "application/json"));
  		$response = $this->oauth->getLastResponse();
  		$json = json_decode($response);
  		return $json;
  	} catch(OAuthException $E) {
  		Error("fetch exception", $E->getMessage(), null, $this->oauth->getLastResponseInfo(), $E->debugInfo);
  	}
  }
  
  function apilist(){
  	try {
  		$this->oauth->fetch($this->api_url_base ."/api/v1", null, OAUTH_HTTP_METHOD_GET, array("Accept" => "application/json"));
  		$response = $this->oauth->getLastResponse();
  		$json = json_decode($response);
  		return $json;
  	} catch(OAuthException $E) {
  			Error("fetch exception", $E->getMessage(), null, $this->oauth->getLastResponseInfo(), $E->debugInfo);
  	}
  }
  
  function materiallist(){//返回材料列表
		try {
			$this->oauth->fetch($this->api_url_base ."/materials/v1", null, OAUTH_HTTP_METHOD_GET, array("Accept" => "application/json"));
			$response = $this->oauth->getLastResponse();
			    $json = json_decode($response);    
			  	return $json;
			} catch(OAuthException $E) {
			    Error("fetch exception", $E->getMessage(), null, $this->oauth->getLastResponseInfo(), $E->debugInfo);
			}
  }
  
  function modeldownload($modelId){
  	try {
  		//$modelId = 1234567; # CHANGEME
  		$modelVersion = 0; # CHANGEME
  		$this->oauth->fetch($this->api_url_base ."/models/$modelId/files/$modelVersion/v1?file=1", null, OAUTH_HTTP_METHOD_GET, array("Accept" => "application/json"));
  		$response = $this->oauth->getLastResponse();
  		$json = json_decode($response);
  		return $json;
  	} catch(OAuthException $E) {
  		Error("fetch exception", $E->getMessage(), null, $this->oauth->getLastResponseInfo(), $E->debugInfo);
  	}
  }
  
  function materialinfo($materialId){
  	try {
  		//$materialId = 26; # CHANGEME
  		//echo $materialId;
  		//exit;
  		$this->oauth->fetch($this->api_url_base ."/materials/$materialId/v1", null, OAUTH_HTTP_METHOD_GET, array("Accept" => "application/json"));
  		$response = $this->oauth->getLastResponse();
  		$json = json_decode($response);
  		return $json;
  	} catch(OAuthException $E) {
  		Error("fetch exception", $E->getMessage(), null, $this->oauth->getLastResponseInfo(), $E->debugInfo);
  	}  	
  }
  
  function pricemodel(){
  	try {
  		$volume = 1 / (100*100*100); # 1 cm^3 in m^2
  		$area = 600 / (1000*1000); # 600 mm^2 (6 cm^2) in m^2
  		$xBoundMin = 0;
  		$yBoundMin = 0;
  		$zBoundMin = 0;
  		$xBoundMax = 0.01; # 1 cm in m
  		$yBoundMax = 0.01;
  		$zBoundMax = 0.01;
  		$data = array("volume" => "$volume",
  				"area" => $area,
  				"xBoundMin" => $xBoundMin,
  				"xBoundMax" => $xBoundMax,
  				"yBoundMin" => $yBoundMin,
  				"yBoundMax" => $yBoundMax,
  				"zBoundMin" => $zBoundMin,
  				"zBoundMax" => $zBoundMax,
  		);
  		$data_string = json_encode($data);
  	
  		$this->oauth->fetch($this->api_url_base ."/price/v1", $data_string, OAUTH_HTTP_METHOD_POST, array("Accept" => "application/json"));
  		$response = $this->oauth->getLastResponse();
  		$json = json_decode($response);
  		return $json;
  	} catch(OAuthException $E) {
  		Error("fetch exception", $E->getMessage(), null, $this->oauth->getLastResponseInfo(), $E->debugInfo);
  	}
  	 
  	
  }
  
  
  
	function test(){//测试方法
		echo "1111";
		var_dump( $this->verbose_debug);
	}
  

  
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
  
  
  
  function Error($component, $exception, $info, $LastResponseInfo, $debugInfo) {
  	global $verbose_debug;
  	echo "A fatal error occurred during $component\n";
  	if ($exception) {
  	echo "Exception : $exception\n";
  	}
  	if ($info) {
  	if (array_key_exists('oauth_problem', $info)) {
            echo "oauth_problem : [". $info['oauth_problem'] . "]\n";
        }
              		if ($verbose_debug) {
              		echo "Query response body :\n";
            var_dump($info);
              		}
              		}
              		if ($verbose_debug && $LastResponseInfo) {
              		echo "Query response headers :\n";
              		var_dump($LastResponseInfo);
  	}
  	if ($verbose_debug && $debugInfo) {
  		echo "Oauth debugInfo :\n";
  		var_dump($debugInfo);
  	}
  	exit(1);
  	}
  
  	function PrintJsonLastError() {
  	switch (json_last_error()) {
  	case JSON_ERROR_NONE:
  		echo ' - No errors';
  		break;
  		case JSON_ERROR_DEPTH:
  		echo ' - Maximum stack depth exceeded';
  		break;
  		case JSON_ERROR_STATE_MISMATCH:
  				echo ' - Underflow or the modes mismatch';
  				break;
  				case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
              break;
              case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
              break;
              case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
              break;
        default:
            echo ' - Unknown error';
              break;
  	}
  	echo PHP_EOL;
  	}
  
	
	
}




