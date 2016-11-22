<?php
require_once 'DBF.php';
require_once 'PVC2.php';
require_once 'Paging.php';

/**
 * 判断浏览器名称和版本
 */
function get_user_browser(){
    if (empty($_SERVER['HTTP_USER_AGENT'])){
        return '';
    }

    $agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = '';
    $browser_ver = '';
    
    if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)){
        $browser = 'Internet Explorer';
        $browser_ver = $regs[1];
    }elseif (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)){
        $browser = 'FireFox';
        $browser_ver = $regs[1];
    }elseif (preg_match('/Maxthon/i', $agent, $regs)){
        $browser = '(Internet Explorer ' .$browser_ver. ') Maxthon';
        $browser_ver = '';
    }elseif (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)){
        $browser = 'Opera';
        $browser_ver = $regs[1];
    }elseif (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)){
        $browser = 'OmniWeb';
        $browser_ver = $regs[2];
    }elseif (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)){
        $browser = 'Netscape';
        $browser_ver = $regs[2];
    }elseif (preg_match('/safari\/([^\s]+)/i', $agent, $regs)){
        $browser = 'Safari';
        $browser_ver = $regs[1];
    }elseif (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)){
        $browser = '(Internet Explorer ' .$browser_ver. ') NetCaptor';
        $browser_ver = $regs[1];
    }elseif (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)){
        $browser = 'Lynx';
        $browser_ver = $regs[1];
    }elseif (preg_match('/rv:11.0/i', $agent, $regs)){
        $browser = 'IE11';
        $browser_ver = $regs[1];
    }

    if (!empty($browser)){
        return addslashes($browser . ' ' . $browser_ver);
    }else{
        return 'Unknow browser';
    }
}

/**
 * 微信浏览器判断
 *
 * @return boolean
 */
function is_weixin() {
	if (strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MicroMessenger' ) !== false) {
		return true;
	}
	return false;
}

/**
 * 移动端浏览器判断
 *
 * @return boolean
 */
function is_mobile() {
	$useragent = isset ( $_SERVER ['HTTP_USER_AGENT'] ) ? $_SERVER ['HTTP_USER_AGENT'] : '';
	$useragent_commentsblock = preg_match ( '|\(.*?\)|', $useragent, $matches ) > 0 ? $matches [0] : '';
	function CheckSubstrs($substrs, $text) {
		foreach ( $substrs as $substr )
			if (false !== strpos ( $text, $substr )) {
				return true;
			}
		return false;
	}
	$mobile_os_list = array (
			'Google Wireless Transcoder',
			'Windows CE',
			'WindowsCE',
			'Symbian',
			'Android',
			'armv6l',
			'armv5',
			'Mobile',
			'CentOS',
			'mowser',
			'AvantGo',
			'Opera Mobi',
			'J2ME/MIDP',
			'Smartphone',
			'Go.Web',
			'Palm',
			'iPAQ' 
	);
	$mobile_token_list = array (
			'Profile/MIDP',
			'Configuration/CLDC-',
			'160×160',
			'176×220',
			'240×240',
			'240×320',
			'320×240',
			'UP.Browser',
			'UP.Link',
			'SymbianOS',
			'PalmOS',
			'PocketPC',
			'SonyEricsson',
			'Nokia',
			'BlackBerry',
			'Vodafone',
			'BenQ',
			'Novarra-Vision',
			'Iris',
			'NetFront',
			'HTC_',
			'Xda_',
			'SAMSUNG-SGH',
			'Wapaka',
			'DoCoMo',
			'iPhone',
			'iPod' 
	);
	
	$found_mobile = CheckSubstrs ( $mobile_os_list, $useragent_commentsblock ) || CheckSubstrs ( $mobile_token_list, $useragent );
	
	if ($found_mobile && $useragent !=="Mozilla/5.0(Linux;Android 5.1.1;SA02 Build/LMY49F) AppleWebKit/537.36(KHTML,like Gecko) Chrome/39.0.2171.71 Mobile Crosswalk/10.39.235.15 Safari/537.36") {
		return true;
	} else {
		return false;
	}
}

/**
 * 数组转换为字符串
 *
 * @param array $array        	
 * @param string $delimiter        	
 * @return string
 */
function arrayTransToStr($arr, $delimiter = ',') {
	$res = '';
	
	if (count ( $arr )) {
		foreach ( $arr as $key => $val ) {
			$res .= $val . $delimiter;
		}
		
		$res = substr ( $res, 0, - strlen ( $delimiter ) );
	}
	
	return $res;
}

/**
 * master ArrayUnique
 * now compare all types
 *
 * @param array $array        	
 * @return array
 */
function arrayUnique($array) {
	foreach ( $array as $k => $v ) {
		foreach ( $array as $k2 => $v2 ) {
			if (($v2 == $v) && ($k != $k2)) {
				unset ( $array [$k] );
			}
		}
	}
	return $array;
}

// 字频统计
function utf8_str_word_count($string, $format = 0, $charlist = null) {
	$result = array ();
	
	if (preg_match_all ( '~[\p{L}\p{Mn}\p{Pd}\'\x{2019}' . preg_quote ( $charlist, '~' ) . ']+~u', $string, $result ) > 0) {
		if (array_key_exists ( 0, $result ) === true) {
			$result = $result [0];
		}
	}
	
	if ($format == 0) {
		$result = count ( $result );
	}
	
	return $result;
}

// 对单个对象根据映射关系赋值
function assignSingleObjectFromMapArr($obj, array $assignArr, array $mapArr) {
	foreach ( $mapArr as $k => $v ) {
		
		if (isset ( $obj->{$v} ) && array_key_exists ( $k, $assignArr )) {
			
			$obj->{$v} = $assignArr [$k];
		}
	}
	return $obj;
}

/**
 * 获取Http请求中Head里的useragent信息
 *
 * @return string
 */
function get_client_agent() {
	return $_SERVER ['HTTP_USER_AGENT'];
}
/**
 * PRINT_R
 *
 * @param unknown_type $para        	
 */
function pr($para) {
	print_r ( $para );
}

/**
 * VAR_DUMP
 *
 * @param unknown_type $para        	
 */
function vd($para) {
	var_dump ( $para );
}
/**
 * PHP 5 >=5.2.0
 */
function datetime_diff($datetime) {
	$datetime = is_string ( $datetime ) ? new DateTime ( $datetime ) : $datetime;
	$diff = date_create ( now )->diff ( $datetime );
	$suffix = ($diff->invert ? '之前' : '后');
	$diff_str = '';
	
	$years = $diff->y ? $diff->y . '年' : null;
	$months = $diff->m ? $diff->m . '个月' : null;
	$days = $diff->d ? $diff->d . '天' : null;
	$hours = $diff->h ? $diff->h . '小时' : null;
	$minutes = $diff->i ? $diff->i . '分钟' : null;
	$seconds = $diff->s ? $diff->s . '秒' : null;
	
	if ($years)
		$diff_str = $years . $months;
	elseif ($months)
		$diff_str = $months . $days;
	elseif ($days)
		$diff_str = $days . $hours;
	elseif ($hours)
		$diff_str = $hours . $minutes;
	else
		$diff_str = $minutes . $seconds;
	
	return $diff_str . $suffix;
}

/**
 * 判断一个URL地址中参数是否带有PAGE，如果有的话，去除
 *
 * @param string $url        	
 * @return string
 */
function process_filter_page_url($url) {
	return trim_slash_url ( preg_replace ( '|-page-(\d*)|', '', $url ) );
}

/**
 * 判断一个URL地址中最后一个参数是filter,如果是判断结尾是否有"/"符号,没有的话加上返回
 *
 * @param string $url        	
 * @return string
 */
function process_filter_slash_url($url) {
	$url_arr = explode ( '/', $url );
	$url_count = count ( $url_arr );
	if ($url_count >= 3) {
		$key = $url_count - 2;
		if ($url_arr [$key] == 'filter') {
			if (substr ( $url, - 1 ) != '/') {
				$url .= '/';
			}
		}
	}
	return $url;
}

/**
 * 判断一个URL地址后面是否有"/"符号,有的话去除返回URL
 *
 * @param string $url        	
 * @return string
 */
function trim_slash_url($url) {
	if (substr ( $url, - 1 ) == '/') {
		$url = substr ( $url, 0, - 1 );
	}
	return $url;
}

/**
 * 系统邮件发送函数
 *
 * @param string $to
 *        	接收邮件者邮箱
 * @param string $name
 *        	接收邮件者名称
 * @param string $subject
 *        	邮件主题
 * @param string $body
 *        	邮件内容
 * @param string $attachment
 *        	附件列表
 * @return boolean
 */
function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null) {
	$config = C ( 'THINK_EMAIL' );
	vendor ( 'PHPMailer.class#phpmailer' ); // 从PHPMailer目录导class.phpmailer.php类文件
	$mail = new PHPMailer (); // PHPMailer对象
	$mail->CharSet = 'UTF-8'; // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
	$mail->IsSMTP (); // 设定使用SMTP服务
	$mail->SMTPDebug = 1; // 关闭SMTP调试功能
	                      // 1 = errors and messages
	                      // 2 = messages only
	$mail->SMTPAuth = true; // 启用 SMTP 验证功能
	//$mail->SMTPSecure = 'tls'; // 使用安全协议
	$mail->Host = $config ['SMTP_HOST']; // SMTP 服务器
	$mail->Port = $config ['SMTP_PORT']; // SMTP服务器的端口号
	$mail->Username = $config ['SMTP_USER']; // SMTP服务器用户名
	$mail->Password = $config ['SMTP_PASS']; // SMTP服务器密码
	$mail->SetFrom ( $config ['FROM_EMAIL'], $config ['FROM_NAME'] );
	$replyEmail = $config ['REPLY_EMAIL'] ? $config ['REPLY_EMAIL'] : $config ['FROM_EMAIL'];
	$replyName = $config ['REPLY_NAME'] ? $config ['REPLY_NAME'] : $config ['FROM_NAME'];
	$mail->AddReplyTo ( $replyEmail, $replyName );
	$mail->Subject = $subject;
	$mail->MsgHTML ( $body );
	$mail->AddAddress ( $to, $name );
	if (is_array ( $attachment )) { // 添加附件
		foreach ( $attachment as $file ) {
			is_file ( $file ) && $mail->AddAttachment ( $file );
		}
	}
	return $mail->Send () ? true : $mail->ErrorInfo;
}

/**
 * 生成随机字符串
 *
 * @access public
 * @param int $length
 *        	长度
 *        	
 * @return string
 */
function generate_password($length = 8) {
	// 密码字符集，可任意添加你需要的字符
	// $chars =
	// 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$password = '';
	for($i = 0; $i < $length; $i ++) {
		// 这里提供两种字符获取方式
		// 第一种是使用 substr 截取$chars中的任意一位字符；
		// 第二种是取字符数组 $chars 的任意元素
		// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
		$password .= $chars [mt_rand ( 0, strlen ( $chars ) - 1 )];
	}
	return $password;
}

/**
 * 将从数据库中查询的结果，主键作为数组的Key
 *
 * @access public
 * @param array $item_list        	
 * @param string $pk_name
 *        	主键字段名称
 *        	
 * @return array
 */
function trans_pk_to_key($item_list, $pk_name) {
	$res = array ();
	foreach ( $item_list as $key => $val ) {
		$res [$val [$pk_name]] = $val;
	}
	return $res;
}
function transPKtoKeyArray($ItemList, $PK) {
	$Result = array ();
	foreach ( $ItemList as $Key => $Val ) {
		if (! isset ( $Result [$Val [$PK]] )) {
			$Result [$Val [$PK]] = array ();
		}
		$Result [$Val [$PK]] [] = $Val;
	}
	return $Result;
}

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access public
 * @param mix $item_list
 *        	列表数组或字符串
 * @param string $field_name
 *        	字段名称
 *        	
 * @return void
 */
function db_create_in($item_list, $field_name = '') {
	if (empty ( $item_list )) {
		return $field_name . " IN ('') ";
	} else {
		if (! is_array ( $item_list )) {
			$item_list = explode ( ',', $item_list );
		}
		$item_list = array_unique ( $item_list );
		$item_list_tmp = '';
		foreach ( $item_list as $item ) {
			if ($item !== '') {
				$item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
			}
		}
		if (empty ( $item_list_tmp )) {
			return $field_name . " IN ('') ";
		} else {
			return $field_name . ' IN (' . $item_list_tmp . ') ';
		}
	}
}
function gen_db_in($arr, $field) {
	$res = '';
	if (is_array ( $arr )) {
		$res .= '(';
		foreach ( $arr as $key => $val ) {
			$res .= $val [$field] . ',';
		}
		$res = substr ( $res, 0, - 1 );
		$res .= ')';
	}
	return $res;
}

/**
 * 获得当前时间
 *
 * @access public
 * @param string $fmt        	
 * @return string
 */
function get_now($fmt = 'Y-m-d H:i:s') {
	return date ( $fmt );
}

/**
 * 获得MySQL版本号
 *
 * @access public
 * @return string
 */
function get_sql_version() {
	$M = M ();
	$sql = 'select version()';
	$res = $M->query ( $sql );
	return get_one ( $res );
}

/**
 * 获得query返回的单个值
 *
 * @access public
 * @param array $res        	
 * @return mix
 */
function get_one($res) {
	return array_shift ( $res [0] );
}

/**
 * 获得服务器上的 GD 版本
 *
 * @access public
 * @return int 可能的值为0，1，2
 */
function gd_version() {
	static $version = - 1;
	
	if ($version >= 0) {
		return $version;
	}
	
	if (! extension_loaded ( 'gd' )) {
		$version = 0;
	} else {
		// 尝试使用gd_info函数
		if (PHP_VERSION >= '4.3') {
			if (function_exists ( 'gd_info' )) {
				$ver_info = gd_info ();
				preg_match ( '/\d/', $ver_info ['GD Version'], $match );
				$version = $match [0];
			} else {
				if (function_exists ( 'imagecreatetruecolor' )) {
					$version = 2;
				} elseif (function_exists ( 'imagecreate' )) {
					$version = 1;
				}
			}
		} else {
			if (preg_match ( '/phpinfo/', ini_get ( 'disable_functions' ) )) {
				/* 如果phpinfo被禁用，无法确定gd版本 */
				$version = 1;
			} else {
				// 使用phpinfo函数
				ob_start ();
				phpinfo ( 8 );
				$info = ob_get_contents ();
				ob_end_clean ();
				$info = stristr ( $info, 'gd version' );
				preg_match ( '/\d/', $info, $match );
				$version = $match [0];
			}
		}
	}
	
	return $version;
}
function gd_support() {
	$gd = gd_version ();
	$res = '';
	if ($gd == 0) {
		$res = 'N/A';
	} else {
		if ($gd == 1) {
			$res = 'GD1';
		} else {
			$res = 'GD2';
		}
		$res .= ' (';
		
		/* 检查系统支持的图片类型 */
		if ($gd && (imagetypes () & IMG_JPG) > 0) {
			$res .= ' JPEG';
		}
		
		if ($gd && (imagetypes () & IMG_GIF) > 0) {
			$res .= ' GIF';
		}
		
		if ($gd && (imagetypes () & IMG_PNG) > 0) {
			$res .= ' PNG';
		}
		
		$res .= ')';
	}
	return $res;
}

/**
 * 文件大小的单位显示(比如xx KB/xx MB)
 *
 * @access public
 * @param int $filesize        	
 * @return string
 */
function get_file_size($filesize) {
	// 如果大于999KB则以MB为单位显示
	if ($filesize > 999999) {
		$theDiv = $filesize / 1000000;
		$theFileSize = round ( $theDiv, 1 ) . " MB";
	} else {
		// 其余情况以KB为单位显示
		$theDiv = $filesize / 1000;
		$theFileSize = round ( $theDiv, 1 ) . " KB";
	}
	return $theFileSize;
}

// @formatter:off
function getSavePathByID($ID) {
	$ID = intval($ID);
	$level_1 = $ID % 64;
	$level_2 = substr ( md5 ( $ID ), 0, 2 );
	return $level_1 . '/' . $level_2 . '/' . strval($ID) . '/';
}

/**
 * 重写 URL 地址
 *
 * @access public
 * @param string $app
 *        	执行程序
 * @param array $params
 *        	参数数组
 * @param string $append
 *        	附加字串
 * @param integer $page
 *        	页数
 * @param string $keywords
 *        	搜索关键词字符串
 * @return void
 */
function build_uri($app, $params, $append = '', $page = 0, $keywords = '', $size = 0) {
	$uri = '#';
	return $uri;
}
function getMD5File16($FilePath) {
	return substr ( md5_file ( $FilePath ), 8, 16 );
}
// @formatter:on
function getWebRootUrl() {
	$http = (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] != 'off') ? 'https://' : 'http://';
	$port = $_SERVER ["SERVER_PORT"] == 80 ? '' : ':' . $_SERVER ["SERVER_PORT"];
	$url = $http . $_SERVER ['HTTP_HOST'] . $port . '/' . APP_NAME . '/';
	return $url;
}
function getWebHost() {
	$http = (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] != 'off') ? 'https://' : 'http://';
	$port = $_SERVER ["SERVER_PORT"] == 80 ? '' : ':' . $_SERVER ["SERVER_PORT"];
	$url = $http . $_SERVER ['HTTP_HOST'] . $port;
	return $url;
}
function check_valid_password($password) {
	if (strlen ( $password ) < 6) {
		return false;
	}
	return true;
}
function check_cfm_password($password, $cfm_password) {
	if ($password !== $cfm_password) {
		return false;
	}
	return true;
}
function reg_mail($mail) {
	if (preg_match ( '/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $mail )) {
		return true;
	} else {
		return false;
	}
}
/**
 * 替换字符串中的变量
 *
 * 变量命名 {vars}
 *
 * @param string $content        	
 * @param array $replacement        	
 * @return string $res;
 */
function replace_string_vars($str, $replacement) {
	$res = '';
	if (is_string ( $str ) && is_array ( $replacement )) {
		$res = $str;
		foreach ( $replacement as $key => $val ) {
			$pattern = '/{' . $key . '}/';
			$res = preg_replace ( $pattern, $val, $res );
		}
	}
	return $res;
}

/**
 * 根据传入参数返回状态
 *
 * 变量命名 {vars}
 *
 * @param int $replacement        	
 * @return string $res;
 */
function replace_int_vars($replacement) {
	if ($replacement == 1) {
		$res = "已支付";
	} else {
		$res = "未支付";
	}
	return $res;
}

/**
 * 根据传入参数返回订单类型
 *
 * 变量命名 {vars}
 *
 * @param int $replacement        	
 * @return string $res;
 */
function replace_int_vars_ordertype($replacement) {
	if ($replacement == 1) {
		$res = "购物车订单";
	} elseif ($replacement == 2) {
		$res = "管理充值";
	} elseif ($replacement == 0) {
		$res = "付款充值";
	} elseif ($replacement == 3) {
		$res = "充值兑换积分";
	}
	return $res;
}

/**
 * 根据用户ID生成一个激活码
 *
 * @param int $point_salt        	
 * @return string
 */
function genactivecode(int $point_salt) {
	$rdm_salt = generate_password ( 8 );
	$org_str = $point_salt . $rdm_salt . microtime ();
	return strtoupper ( md5 ( $org_str ) );
}

if (! function_exists ( 'array_column' )) {
	
	/**
	 * Returns the values from a single column of the input array, identified by
	 * the $columnKey.
	 * Optionally, you may provide an $indexKey to index the values in the
	 * returned array by the values from the $indexKey column in the input
	 * array.
	 *
	 * @param array $input
	 *        	A multi-dimensional array (record set) from which to pull a
	 *        	column
	 *        	of values.
	 * @param mixed $columnKey
	 *        	The column of values to return. This value may be the integer
	 *        	key
	 *        	of the column you wish to retrieve, or it may be the string
	 *        	key
	 *        	name for an associative array.
	 * @param mixed $indexKey
	 *        	(Optional.) The column to use as the index/keys for the
	 *        	returned
	 *        	array. This value may be the integer key of the column, or it
	 *        	may
	 *        	be the string key name.
	 * @return array
	 */
	function array_column($input = null, $columnKey = null, $indexKey = null) {
		// Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc = func_num_args ();
		$params = func_get_args ();
		
		if ($argc < 2) {
			trigger_error ( "array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING );
			return null;
		}
		
		if (! is_array ( $params [0] )) {
			trigger_error ( 'array_column() expects parameter 1 to be array, ' . gettype ( $params [0] ) . ' given', E_USER_WARNING );
			return null;
		}
		
		if (! is_int ( $params [1] ) && ! is_float ( $params [1] ) && ! is_string ( $params [1] ) && $params [1] !== null && ! (is_object ( $params [1] ) && method_exists ( $params [1], '__toString' ))) {
			trigger_error ( 'array_column(): The column key should be either a string or an integer', E_USER_WARNING );
			return false;
		}
		
		if (isset ( $params [2] ) && ! is_int ( $params [2] ) && ! is_float ( $params [2] ) && ! is_string ( $params [2] ) && ! (is_object ( $params [2] ) && method_exists ( $params [2], '__toString' ))) {
			trigger_error ( 'array_column(): The index key should be either a string or an integer', E_USER_WARNING );
			return false;
		}
		
		$paramsInput = $params [0];
		$paramsColumnKey = ($params [1] !== null) ? ( string ) $params [1] : null;
		
		$paramsIndexKey = null;
		if (isset ( $params [2] )) {
			if (is_float ( $params [2] ) || is_int ( $params [2] )) {
				$paramsIndexKey = ( int ) $params [2];
			} else {
				$paramsIndexKey = ( string ) $params [2];
			}
		}
		
		$resultArray = array ();
		
		foreach ( $paramsInput as $row ) {
			
			$key = $value = null;
			$keySet = $valueSet = false;
			
			if ($paramsIndexKey !== null && array_key_exists ( $paramsIndexKey, $row )) {
				$keySet = true;
				$key = ( string ) $row [$paramsIndexKey];
			}
			
			if ($paramsColumnKey === null) {
				$valueSet = true;
				$value = $row;
			} elseif (is_array ( $row ) && array_key_exists ( $paramsColumnKey, $row )) {
				$valueSet = true;
				$value = $row [$paramsColumnKey];
			}
			
			if ($valueSet) {
				if ($keySet) {
					$resultArray [$key] = $value;
				} else {
					$resultArray [] = $value;
				}
			}
		}
		
		return $resultArray;
	}
}

/**
 * 获得去掉"."的模型文件路径
 */
function getfilepath($filepath) {
	if (strval ( strpos ( $filepath, '.', 0 ) ) == "0") {
		$result = substr ( $filepath, 1 );
	} else {
		$result = $filepath;
	}
	return $result;
}

/**
 * 日志记录，按照"Ymd.log"生成当天日志文件
 * 日志路径为：入口文件所在目录/logs/$type/当天日期.log.php，例如 /logs/error/20120105.log.php
 *
 * @param string $type
 *        	日志类型，对应logs目录下的子文件夹名
 * @param string $content
 *        	日志内容
 * @return bool true/false 写入成功则返回true
 */
function writelog($type = "", $content = "") {
	if (! $content || ! $type) {
		return FALSE;
	}
	$dir = getcwd () . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $type;
	if (! is_dir ( $dir )) {
		if (! mkdir ( $dir )) {
			return false;
		}
	}

	$filename = $dir . DIRECTORY_SEPARATOR . date ( "Ymd", time () ) . '.txt';
	$logs = include $filename;
	if ($logs && ! is_array ( $logs )) {
		unlink ( $filename );
		return false;
	}
	$logs [] = array (
			"time" => date ( "Y-m-d H:i:s" ),
			"content" => $content 
	);
	$str = "<?php \r\n return " . var_export ( $logs, true ) . ";";
	if (! $fp = @fopen ( $filename, "wb" )) {
		return false;
	}
	if (! fwrite ( $fp, $str ))
		return false;
	fclose ( $fp );
	return true;
}
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
	if (function_exists ( "mb_substr" )) {
		if ($suffix && strlen ( $str ) > $length)
			return mb_substr ( $str, $start, $length, $charset ) . "...";
		else
			return mb_substr ( $str, $start, $length, $charset );
	} elseif (function_exists ( 'iconv_substr' )) {
		if ($suffix && strlen ( $str ) > $length)
			return iconv_substr ( $str, $start, $length, $charset ) . "...";
		else
			return iconv_substr ( $str, $start, $length, $charset );
	}
	$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all ( $re [$charset], $str, $match );
	$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	if ($suffix)
		return $slice . "…";
	return $slice;
}
function pub_encode_pass($tex, $key, $type = "encode") {
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
	//echo "key_b:".$key_b." ";
    $rand_key = $key_b . $key;
	$rand_key = md5 ( $rand_key );

	//echo substr ( $tex, 6 );
	$tex = $type == "decode" ? base64_decode ( substr ( $tex, 6 ) ) : $tex;

	$texlen = strlen ( $tex );
	$reslutstr = "";
    //echo" texlen:".$texlen;
    //exit;
	for($i = 0; $i < $texlen; $i ++) {
		$reslutstr .= $tex {$i} ^ $rand_key {$i % 32};
	}

    //echo $reslutstr;

	if ($type != "decode") {
		$reslutstr = trim ( $key_b . base64_encode ( $reslutstr ), "==" );
		$reslutstr = substr ( md5 ( $reslutstr ), 0, 8 ) . $reslutstr;
	}
	return $reslutstr;
}
function get_url() { // 获取当前URl
	$sys_protocal = isset ( $_SERVER ['SERVER_PORT'] ) && $_SERVER ['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER ['PHP_SELF'] ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME'];
	$path_info = isset ( $_SERVER ['PATH_INFO'] ) ? $_SERVER ['PATH_INFO'] : '';
	$relate_url = isset ( $_SERVER ['REQUEST_URI'] ) ? $_SERVER ['REQUEST_URI'] : $php_self . (isset ( $_SERVER ['QUERY_STRING'] ) ? '?' . $_SERVER ['QUERY_STRING'] : $path_info);
	return $sys_protocal . (isset ( $_SERVER ['HTTP_HOST'] ) ? $_SERVER ['HTTP_HOST'] : '') . $relate_url;
}

/**
 * 清理路径最前面的点号
 *
 * @param string $path        	
 * @return string
 */
function getDropDotPath($path) {
	if (substr ( $path, 0, 1 ) == '.') {
		return substr ( $path, 1 );
	} else {
		return $path;
	}
}

/**
 * 获取当天时间戳的起点和终点
 *
 * @return array $res
 */
function getTodayTS() {
	$res = array ();
	$t = time ();
	$s1 = mktime ( 0, 0, 0, date ( 'm', $t ), date ( 'd', $t ), date ( 'Y', $t ) );
	$e1 = mktime ( 23, 59, 59, date ( 'm', $t ), date ( 'd', $t ), date ( 'Y', $t ) );
	$res ['s'] = $s1;
	$res ['e'] = $e1;
	return $res;
}

/**
 * 生成一个随机数
 *
 * @param unknown_type $start        	
 * @param unknown_type $end        	
 * @return number
 */
function genvcode($start, $end) {
	$start = intval ( $start );
	$end = intval ( $end );
	return mt_rand ( $start, $end );
}

/**
 * 生成一个签名
 *
 * @param string $parameter        	
 * @param $vcode 必须是1-28        	
 * @param string $pubkey
 *        	必须是32位
 */
function gensign($parameter, $vcode, $pubkey) {
	$cutstart = $vcode - 1;
	return md5 ( md5 ( $parameter ) . substr ( $pubkey, $cutstart, 4 ) );
}

/*
 * 根据输入电话号码值返回电话号码 @$pre区号 $phone电话 $ext分机号
 */
function getphonenum($pre, $phone, $ext) {
	$result = empty ( $pre ) ? "" : $pre . "-";
	$result .= empty ( $phone ) ? "" : $phone;
	$result .= empty ( $ext ) ? "" : "-" . $ext;
	return $result;
}
function show_uptype($t) {
	switch ($t) {
		case 0 :
			return '充值';
			break;
		case 1 :
			return '购买';
			break;
		case 2 :
			return '后台管理充值';
			break;
		case 3 :
			return '积分';
			break;
		case 4 :
			return '余额支付';
			break;
		default :
			return 'no data';
	}
}
/*
 * 根据producttype获得产品类型
 */
function show_product_type($ptypeid) {
	switch ($ptypeid) {
		case 2 :
			return '实物';
			break;
		case 4 :
			return 'DIY首饰';
			break;
		case 5 :
			return '首饰';
			break;
		default :
			return '数模';
	}
}

/*
 * 去掉表名前缀
 */
function cutprefix($tballname) { // 去掉表名前缀
	$temp = mb_substr ( $tballname, strpos ( $tballname, "_" ) + 1, 50, 'utf-8' );
	return $temp;
}

/*
 *
 */
function cutproductname($pname) {
	if (strpos ( $pname, "|" ) > 0) {
		$temp = mb_substr ( $pname, strpos ( $pname, "|" ) + 1, 100, 'utf-8' );
	} else {
		$temp = $pname;
	}
	return $temp;
}

/**
 * 根据层级获得空格
 *
 * @access public
 * @param int $level        	
 * @return string
 */
function get_spaces($level) {
	$space = '';
	for($i = 0; $i < $level; $i ++) {
		$space .= '|&nbsp;';
	}
	return $space;
}
function J($str) {
	return str_replace ( './', '', str_replace ( '//', '/', $str ) );
}
function getimgbyID($id) { // 根据图片id得到图片路径
	$TI = M ( 'image' )->where ( 'id=' . $id )->find ();
	$result = WEBROOT_URL . $TI ['path'];
	return $result;
}
/*
 * @param string $to 手机号码
 * @param array $datas $datas[0]验证码 
 * @return Bool 1成功 0失败
 * @param string $tempid 模板ID
 */
//发送模板短信
function smssent($to,$datas,$tempid){
     
    $server = C("SMS_SERVER");
    $accountSid = $server['accountSid'];
    $accountToken = $server['accountToken'];
    $appId = $server['appId'];
    $serverIP = $server['serverIP'];
    $serverPort = $server['serverPort'];
    $softVersion = $server['softVersion'];
    $tempId = $tempid;
     
    Vendor('SMS.SDK.CCPRestSmsSDK');
    $rest = new REST($serverIP,$serverPort,$softVersion);
    $rest->setAccount($accountSid,$accountToken);
    $rest->setAppId($appId);
    
    // 发送模板短信
    //echo "Sending TemplateSMS to $to <br/>";
   $result = $rest->sendTemplateSMS($to,$datas,$tempId);
    if($result == NULL ) {
    //echo "result error!";
	  //      break;
    return false;
    }
    if($result->statusCode!=0) {

       echo "error code :" . $result->statusCode . "<br>";
	    echo "error msg :" . $result->statusMsg . "<br>";
	    //die();
	    //添加错误处理逻辑
    	    return false;
    }else{
    //echo "Sendind TemplateSMS success!<br/>";
        // 获取返回信息
	     //$smsmessage = $result->TemplateSMS;
        //echo "dateCreated:".$smsmessage->dateCreated."<br/>";
        //echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
        // 添加成功处理逻辑
        return true;
    }









}
?>