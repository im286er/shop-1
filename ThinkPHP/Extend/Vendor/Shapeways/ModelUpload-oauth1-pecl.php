
<?php

require "consumer_key.php";
require "access_token.php";
require "api_url_base.php";
require "error.php";

try {
	  $oauth = new Oauth($consumer_key, $consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);
    $oauth->enableDebug();
    $res = $oauth->setToken($access_token, $access_secret);
	//var_dump($res);

} catch(OAuthException $E) {
    Error("setup exception", $E->getMessage(), null, null, $E->debugInfo);
	
}


try {
    $filename = "boat.obj";
    $file = file_get_contents("./". $filename);
	echo filesize("./". $filename);
    $data = array("fileName" => "$filename",
                  "file" => rawurlencode(base64_encode($file)),
                  "hasRightsToModel" => 1,
                  "acceptTermsAndConditions" => 1,
				 		// "isPublic" => 1,
     			  //"viewState" => 1,
                  );
    $data_string = json_encode($data);
    $oauth->fetch($api_url_base ."/models/v1", $data_string, OAUTH_HTTP_METHOD_POST, array("Accept" => "application/json"));
    $response = $oauth->getLastResponse();
	var_dump($response);
    $json = json_decode($response);    
    if (null == $json) {
		echo 'JSON IS NULL';
        //PrintJsonLastError();
        //var_dump($response);
    } else {
		echo 'JSON NOT NULL';
        print_r($json);
    }
	
} catch(OAuthException $E) {
	echo 'Catch Exception';
    Error("fetch exception", $E->getMessage(), null, $oauth->getLastResponseInfo(), $E->debugInfo);
}

?>

