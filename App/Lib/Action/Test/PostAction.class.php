<?php
/**
 * 模拟POST提交类
*
* @author miaomin
* Aug 13, 2013 5:54:26 PM
*/
class PostAction extends Action {
	/**
	 * Ensure that you have cURL installed by uncommenting
	 * ;extension=php_curl.dll
	 * in your php.ini
	 */
	public function index() { 
		try {
			$url = "https://api.sketchfab.com/v1/models";
			$path = "./";
			$filename = "VICTORY.3DS";
			$description = "Test of the api with a simple model";
			$token_api = "b5dad6cb320141d190c65d3e7a77a6a2";
			$title = "VICTORY_aaa";
			$tags = "test collada glasses";
			$private = 0;
			$password = "11111";
			$data = array (
					"title" => $title,
					"description" => $description,
					"fileModel" => "@" . $path . $filename,
					"filenameModel" => $filename,
					"tags" => $tags,
					"token" => $token_api,
					"private" => $private,
					"password" => $password 
			);
			$ch = curl_init ();
			curl_setopt_array ( $ch, array (
					CURLOPT_SSL_VERIFYPEER => FALSE,
					CURLOPT_SSL_VERIFYHOST => FALSE,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => $url,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => $data 
			) );
			$response = curl_exec ( $ch );
			curl_close ( $ch );
			echo $response;
			
			// var_dump ( filesize ( $path . $filename ) );
		} catch ( Exception $e ) {
			print_r ( $e->getMessage () );
		}
	}
}
?>