<?php
/**
 * 从数据库中生成下拉菜单控件
 * 
 * @param unknown $findRes
 * @param unknown $title
 * @param unknown $value
 * @param string $select
 * @return string
 */
function genDBOptionCtrl($findRes,$title,$value,$select=null){
    
    $optArr = transDBarrToOptarr ( $findRes, $title, $value );
     
    if ($select !== null) {
        $optRes = get_dropdown_option ( $optArr, $select );
    } else {
        $optRes = get_dropdown_option ( $optArr );
    }

    return $optRes;
}
/**
 * 全排列组合
 * 
 * @param unknown_type $data
 * @param unknown_type $all
 * @param unknown_type $group
 * @param unknown_type $val
 * @param unknown_type $i
 * @return unknown
 */
function combos($data, &$all = array(), $group = array(), $val = null, $i = 0){
	if (isset($val))
	{
		array_push($group, $val);
	}

	if ($i >= count($data))
	{
		array_push($all, $group);
	}
	else
	{
		foreach ($data[$i] as $v)
		{
			combos($data,$all,$group, $v, $i + 1);
		}
	}

	return $all;
}

/**
 * 将数据库转为OPTION支持的数组
 * 
 * @param array $srcArr
 * @param string $keyName
 * @param string $valueName
 * @return array
 */
function transDBarrToOptarr($srcArr, $keyName, $valueName) {
	$res = array ();
	foreach ( $srcArr as $key => $val ) {
		$tmp = array ();
		$tmp ['key'] = $val [$keyName];
		$tmp ['value'] = $val [$valueName];
		$res [] = $tmp;
	}
	return $res;
}

/**
 * 带间隔符的字符串保存为数组格式的JSON字符串
 * 
 * @param string $delistr
 * @param string $delimiter
 * @return string
 */
function delimiterStrTransToJson($delistr,$delimiter=','){
	
	return json_encode(explode($delimiter, $delistr));
}

/**
 * JSON字串转换为普通字符串
 *
 * @param string $jsonstr
 * @param string $delimiter
 * @return string
 */
function jsonstrTransToStr($jsonstr,$delimiter=','){

	$res = '';

	$jsonArr = json_decode($jsonstr,1);
		
	$res = arrayTransToStr($jsonArr, $delimiter);

	return $res;
}

/**
 * 取得管理中心左栏菜单定义数组
 *
 * @access public
 * @return array
 */
function get_menu_arr() {
	require 'inc_menu.php';
	return $modules;
}



/**
 * 创建一个JSON格式的错误信息
 *
 * @access public
 * @param string $msg        	
 * @return void
 */
function make_json_error($msg) {
	make_json_response ( '', 1, $msg );
}

/**
 * 创建一个JSON格式的数据
 *
 * @access public
 * @param string $content        	
 * @param integer $error        	
 * @param string $message        	
 * @param array $append        	
 * @return void
 */
function make_json_response($content = '', $error = "0", $message = '', $append = array()) {
	vendor ( 'Ecs.cls_json' );
	$json = new JSON ();
	
	$res = array (
			'error' => $error,
			'message' => $message,
			'content' => $content 
	);
	
	if (! empty ( $append )) {
		foreach ( $append as $key => $val ) {
			$res [$key] = $val;
		}
	}
	
	$val = $json->encode ( $res );
	
	exit ( $val );
}

/**
 * 创建一个JSON格式的返回结果
 *
 * @access public
 * @param        	
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * @return void
 */
function make_json_result($content, $message = '', $append = array()) {
	make_json_response ( $content, 0, $message, $append );
}

/**
 * 对模版中一些公用元素进行赋值操作
 *
 * @access public
 * @param        	
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * @return void
 */
function tpl_common_assign() {
}

/**
 * 取得上次的过滤条件
 *
 * @access public
 * @param string $param_str
 *        	参数字符串，由list函数的参数组成
 * @return 如果有，返回array('filter' => $filter, 'sql' => $sql)；否则返回false
 */
function get_filter($param_str = '') {
	$filterfile = basename ( PHP_SELF, '.php' );
	if ($param_str) {
		$filterfile .= $param_str;
	}
	$cookie_lastfilterfile = cookie ( 'lastfilterfile' );
	if (isset ( $_GET ['uselastfilter'] ) && isset ( $cookie_lastfilterfile ) && $cookie_lastfilterfile == sprintf ( '%X', crc32 ( $filterfile ) )) {
		return array (
				'filter' => unserialize ( urldecode ( cookie ( 'lastfilter' ) ) ),
				'sql' => base64_decode ( cookie ( 'lastfilter' ) ) 
		);
	} else {
		return false;
	}
}

/**
 * 将JSON传递的参数转码
 *
 * @access public
 * @param string $str        	
 * @return string
 */
function json_str_iconv($str) {
	if (C ( DEFAULT_CHARSET ) != 'utf-8') {
		if (is_string ( $str )) {
			return sys_iconv ( 'utf-8', C ( DEFAULT_CHARSET ), $str );
		} elseif (is_array ( $str )) {
			foreach ( $str as $key => $value ) {
				$str [$key] = json_str_iconv ( $value );
			}
			return $str;
		} elseif (is_object ( $str )) {
			foreach ( $str as $key => $value ) {
				$str->$key = json_str_iconv ( $value );
			}
			return $str;
		} else {
			return $str;
		}
	}
	return $str;
}

/**
 * sys_iconv
 *
 * @access private
 */
function sys_iconv($source_lang, $target_lang, $source_string = '') {
	static $chs = NULL;
	
	/* 如果字符串为空或者字符串不需要转换，直接返回 */
	if ($source_lang == $target_lang || $source_string == '' || preg_match ( "/[\x80-\xFF]+/", $source_string ) == 0) {
		return $source_string;
	}
	
	if ($chs === NULL) {
		vendor ( 'Ecs.cls_iconv' );
		$chs = new Chinese ();
	}
	
	return $chs->Convert ( $source_lang, $target_lang, $source_string );
}

/**
 * 根据过滤条件获得排序的标记
 *
 * @access public
 * @param array $filter        	
 * @return array
 */
function sort_flag($filter) {
	$flag ['tag'] = 'sort_' . preg_replace ( '/^.*\./', '', $filter ['sort_by'] );
	$flag ['img'] = '<img src="__PUBLIC__/Admin/images/' . ($filter ['sort_order'] == "DESC" ? 'sort_desc.gif' : 'sort_asc.gif') . '"/>';
	
	return $flag;
}

/**
 * 分页的信息加入条件的数组
 *
 * @access public
 * @return array
 */
function page_and_size($filter) {
	$cookie_page_size = cookie ( page_size );
	if (isset ( $_REQUEST ['page_size'] ) && intval ( $_REQUEST ['page_size'] ) > 0) {
		$filter ['page_size'] = intval ( $_REQUEST ['page_size'] );
	} elseif (isset ( $cookie_page_size ) && intval ( $cookie_page_size ) > 0) {
		$filter ['page_size'] = intval ( $cookie_page_size );
	} else {
		$filter ['page_size'] = 15;
	}
	
	/* 每页显示 */
	$filter ['page'] = (empty ( $_REQUEST ['page'] ) || intval ( $_REQUEST ['page'] ) <= 0) ? 1 : intval ( $_REQUEST ['page'] );
	/* 总数 */
	$filter ['page_count'] = (! empty ( $filter ['record_count'] ) && $filter ['record_count'] > 0) ? ceil ( $filter ['record_count'] / $filter ['page_size'] ) : 1;
	
	/* 边界处理 */
	if ($filter ['page'] > $filter ['page_count']) {
		$filter ['page'] = $filter ['page_count'];
	}
	
	$filter ['start'] = ($filter ['page'] - 1) * $filter ['page_size'];
	return $filter;
}

/**
 * 生成分页选择器
 *
 * @access public
 * @param array $params        	
 * @return string
 */
function create_pages($params) {
	$page = $params ['page'];
	$count = $params ['count'];
	
	if (empty ( $page )) {
		$page = 1;
	}
	
	if (! empty ( $count )) {
		$str = "<option value='1'>1</option>";
		$min = min ( $count - 1, $page + 3 );
		for($i = $page - 3; $i <= $min; $i ++) {
			if ($i < 2) {
				continue;
			}
			$str .= "<option value='$i'";
			$str .= $page == $i ? " selected='true'" : '';
			$str .= ">$i</option>";
		}
		if ($count > 1) {
			$str .= "<option value='$count'";
			$str .= $page == $count ? " selected='true'" : '';
			$str .= ">$count</option>";
		}
	} else {
		$str = '';
	}
	
	return $str;
}

/**
 * 对 MYSQL LIKE 的内容进行转义
 *
 * @access public
 * @param string $str        	
 * @return string
 */
function mysql_like_quote($str) {
	/*
	return strtr ( $str, array (
			"\\\\" => "\\\\\\\\",
			'_' => '\_',
			'%' => '\%',
			"\'" => "\\\\\'" 
	) );
	*/
	return strtr ( $str, array (
			"\\\\" => "\\\\\\\\",
			'_' => '\_',
			'%' => '\%'
	) );
}

/**
 * 保存过滤条件
 *
 * @param array $filter
 *        	过滤条件
 * @param string $sql
 *        	查询语句
 * @param string $param_str
 *        	参数字符串，由list函数的参数组成
 *        	
 */
function set_filter($filter, $sql, $param_str = '') {
	$filterfile = basename ( PHP_SELF, '.php' );
	if ($param_str) {
		$filterfile .= $param_str;
	}
	
	cookie ( 'lastfilterfile', sprintf ( '%X', crc32 ( $filterfile ) ), time () + 600 );
	cookie ( 'lastfilter', urlencode ( serialize ( $filter ) ), time () + 600 );
	cookie ( 'lastfiltersql', base64_encode ( $sql ), time () + 600 );
}

/**
 * 根据二维数组获取一个OPTION字符串
 *
 * @param array $arr
 * @param string $select
 * @return string
 */
function get_dropdown_option($arr, $select = 1) {
	$res = '';
	foreach ( $arr as $key => $val ) {
		$disabled = isset($arr[$key]['disabled']) && $arr[$key]['disabled'] ? 'disabled="disabled"' : '';
		if ($val ['value'] == $select) {
			$res .= '<option value="' . $val ['value'] . '" selected="selected" '.$disabled.'>' . $val ['key'] . '</option>';
		} else {
			$res .= '<option value="' . $val ['value'] . '" '.$disabled.'>' . $val ['key'] . '</option>';
		}
	}
	return $res;
}

/**
 * 字节格式化 把字节数格式为 B K M G T 描述的大小
 * @return string
 */
function byte_format($size, $dec=2) {
	$a = array("B", "KB", "MB", "GB", "TB", "PB");
	$pos = 0;
	while ($size >= 1024) {
		$size /= 1024;
		$pos++;
	}
	return round($size,$dec)." ".$a[$pos];
}

/**
 * 生成一个随机数
 *
 * @param unknown_type $start
 * @param unknown_type $end
 * @return number
 */
/*function genvcode($start, $end) {
 $start = intval ( $start );
$end = intval ( $end );
return mt_rand ( $start, $end );
}*/
/**
 * 生成一个签名
 *
 * @param string $parameter
 * @param $vcode //
 *        	必须是1-28
 * @param string $pubkey
 *        	// 必须是32位
 */
/*function gensign($parameter, $vcode, $pubkey) {
 $cutstart = $vcode - 1;

return md5 ( md5 ( $parameter ) . substr ( $pubkey, $cutstart, 4 ) );
}*/
/**
 * 切除WHERE语句最后多余的AND
 *
 * @param String $sql
 * @return String
 */
function cutWhereTail(String $sql) {
	if (substr ( trim ( $sql ), - 3 ) == 'AND') {
		$sql = substr ( $sql, 0, strlen ( $sql ) - 4 );
	}
	return $sql;
}

/**
 * +----------------------------------------------------------
 * 原样输出print_r的内容
 * +----------------------------------------------------------
 *
 * @param string $content
 *        	待print_r的内容
 *        	+----------------------------------------------------------
 */
function pre($content) {
	echo "<pre>";
	print_r ( $content );
	echo "</pre>";
}

/**
 * +----------------------------------------------------------
 * 加密密码
 * +----------------------------------------------------------
 *
 * @param string $data
 *        	待加密字符串
 *        	+----------------------------------------------------------
 * @return string 返回加密后的字符串
 */
function encrypt($data) {
	return md5 ( C ( "AUTH_CODE" ) . md5 ( $data ) );
}

/**
 * +----------------------------------------------------------
 * 将一个字符串转换成数组，支持中文
 * +----------------------------------------------------------
 *
 * @param string $string
 *        	待转换成数组的字符串
 *        	+----------------------------------------------------------
 * @return string 转换后的数组
 *         +----------------------------------------------------------
 */
function strToArray($string) {
	$strlen = mb_strlen ( $string );
	while ( $strlen ) {
		$array [] = mb_substr ( $string, 0, 1, "utf8" );
		$string = mb_substr ( $string, 1, $strlen, "utf8" );
		$strlen = mb_strlen ( $string );
	}
	return $array;
}

/**
 * +----------------------------------------------------------
 * 生成随机字符串
 * +----------------------------------------------------------
 *
 * @param int $length
 *        	要生成的随机字符串长度
 * @param string $type
 *        	随机码类型：0，数字+大写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
 *        	+----------------------------------------------------------
 * @return string +----------------------------------------------------------
 */
function randCode($length = 5, $type = 0) {
	$arr = array (
			1 => "0123456789",
			2 => "abcdefghijklmnopqrstuvwxyz",
			3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
			4 => "~@#$%^&*(){}[]|"
	);
	if ($type == 0) {
		array_pop ( $arr );
		$string = implode ( "", $arr );
	} else if ($type == "-1") {
		$string = implode ( "", $arr );
	} else {
		$string = $arr [$type];
	}
	$count = strlen ( $string ) - 1;
	for($i = 0; $i < $length; $i ++) {
		$str [$i] = $string [rand ( 0, $count )];
		$code .= $str [$i];
	}
	return $code;
}

/**
 * +-----------------------------------------------------------------------------------------
 * 删除目录及目录下所有文件或删除指定文件
 * +-----------------------------------------------------------------------------------------
 *
 * @param str $path
 *        	待删除目录路径
 * @param int $delDir
 *        	是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
 *        	+-----------------------------------------------------------------------------------------
 * @return bool 返回删除状态
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *         +-----------------------------------------------------------------------------------------
 */
function delDirAndFile($path, $delDir = FALSE) {
	$handle = opendir ( $path );
	if ($handle) {
		while ( false !== ($item = readdir ( $handle )) ) {
			if ($item != "." && $item != "..")
				is_dir ( "$path/$item" ) ? delDirAndFile ( "$path/$item", $delDir ) : unlink ( "$path/$item" );
		}
		closedir ( $handle );
		if ($delDir)
			return rmdir ( $path );
	} else {
		if (file_exists ( $path )) {
			return unlink ( $path );
		} else {
			return FALSE;
		}
	}
}

/**
 * +----------------------------------------------------------
 * 将一个字符串部分字符用*替代隐藏
 * +----------------------------------------------------------
 *
 * @param string $string
 *        	待转换的字符串
 * @param int $bengin
 *        	起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int $len
 *        	需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int $type
 *        	转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string $glue
 *        	分割符
 *        	+----------------------------------------------------------
 * @return string 处理后的字符串
 *         +----------------------------------------------------------
 */
function hideStr($string, $bengin = 0, $len = 4, $type = 0, $glue = "@") {
	if (empty ( $string ))
		return false;
	$array = array ();
	if ($type == 0 || $type == 1 || $type == 4) {
		$strlen = $length = mb_strlen ( $string );
		while ( $strlen ) {
			$array [] = mb_substr ( $string, 0, 1, "utf8" );
			$string = mb_substr ( $string, 1, $strlen, "utf8" );
			$strlen = mb_strlen ( $string );
		}
	}
	switch ($type) {
		case 1 :
			$array = array_reverse ( $array );
			for($i = $bengin; $i < ($bengin + $len); $i ++) {
				if (isset ( $array [$i] ))
					$array [$i] = "*";
			}
			$string = implode ( "", array_reverse ( $array ) );
			break;
		case 2 :
			$array = explode ( $glue, $string );
			$array [0] = hideStr ( $array [0], $bengin, $len, 1 );
			$string = implode ( $glue, $array );
			break;
		case 3 :
			$array = explode ( $glue, $string );
			$array [1] = hideStr ( $array [1], $bengin, $len, 0 );
			$string = implode ( $glue, $array );
			break;
		case 4 :
			$left = $bengin;
			$right = $len;
			$tem = array ();
			for($i = 0; $i < ($length - $right); $i ++) {
				if (isset ( $array [$i] ))
					$tem [] = $i >= $left ? "*" : $array [$i];
			}
			$array = array_chunk ( array_reverse ( $array ), $right );
			$array = array_reverse ( $array [0] );
			for($i = 0; $i < $right; $i ++) {
				$tem [] = $array [$i];
			}
			$string = implode ( "", $tem );
			break;
		default :
			for($i = $bengin; $i < ($bengin + $len); $i ++) {
				if (isset ( $array [$i] ))
					$array [$i] = "*";
			}
			$string = implode ( "", $array );
			break;
	}
	return $string;
}

/**
 * +----------------------------------------------------------
 * 功能：字符串截取指定长度
 * leo.li hengqin2008@qq.com
 * +----------------------------------------------------------
 *
 * @param string $string
 *        	待截取的字符串
 * @param int $len
 *        	截取的长度
 * @param int $start
 *        	从第几个字符开始截取
 * @param boolean $suffix
 *        	是否在截取后的字符串后跟上省略号
 *        	+----------------------------------------------------------
 * @return string 返回截取后的字符串
 *         +----------------------------------------------------------
 */
function cutStr($str, $len = 100, $start = 0, $suffix = 1) {
	$str = strip_tags ( trim ( strip_tags ( $str ) ) );
	$str = str_replace ( array (
			"\n",
			"\t"
	), "", $str );
	$strlen = mb_strlen ( $str );
	while ( $strlen ) {
		$array [] = mb_substr ( $str, 0, 1, "utf8" );
		$str = mb_substr ( $str, 1, $strlen, "utf8" );
		$strlen = mb_strlen ( $str );
	}
	$end = $len + $start;
	$str = '';
	for($i = $start; $i < $end; $i ++) {
		$str .= $array [$i];
	}
	return count ( $array ) > $len ? ($suffix == 1 ? $str . "&hellip;" : $str) : $str;
}

/**
 * +----------------------------------------------------------
 * 功能：检测一个目录是否存在，不存在则创建它
 * +----------------------------------------------------------
 *
 * @param string $path
 *        	待检测的目录
 *        	+----------------------------------------------------------
 * @return boolean +----------------------------------------------------------
 */
function makeDir($path) {
	return is_dir ( $path ) or (makeDir ( dirname ( $path ) ) and @mkdir ( $path, 0777 ));
}

/**
 * +----------------------------------------------------------
 * 功能：检测一个字符串是否是邮件地址格式
 * +----------------------------------------------------------
 *
 * @param string $value
 *        	待检测字符串
 *        	+----------------------------------------------------------
 * @return boolean +----------------------------------------------------------
 */
function is_email($value) {
	return preg_match ( "/^[0-9a-zA-Z]+(?:[\_\.\-][a-z0-9\-]+)*@[a-zA-Z0-9]+(?:[-.][a-zA-Z0-9]+)*\.[a-zA-Z]+$/i", $value );
}

/**
 * +----------------------------------------------------------
 * 功能：系统邮件发送函数
 * +----------------------------------------------------------
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
 *        	+----------------------------------------------------------
 * @return boolean +----------------------------------------------------------
 */
function send_mail($to, $name, $subject = '', $body = '', $attachment = null, $config = '') {
	$config = is_array ( $config ) ? $config : C ( 'SYSTEM_EMAIL' );
	import ( 'PHPMailer.phpmailer', VENDOR_PATH ); // 从PHPMailer目录导class.phpmailer.php类文件
	$mail = new PHPMailer (); // PHPMailer对象
	$mail->CharSet = 'UTF-8'; // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
	$mail->IsSMTP (); // 设定使用SMTP服务
	// $mail->IsHTML(true);
	$mail->SMTPDebug = 0; // 关闭SMTP调试功能 1 = errors and messages2 = messages only
	$mail->SMTPAuth = true; // 启用 SMTP 验证功能
	if ($config ['smtp_port'] == 465)
		$mail->SMTPSecure = 'ssl'; // 使用安全协议
	$mail->Host = $config ['smtp_host']; // SMTP 服务器
	$mail->Port = $config ['smtp_port']; // SMTP服务器的端口号
	$mail->Username = $config ['smtp_user']; // SMTP服务器用户名
	$mail->Password = $config ['smtp_pass']; // SMTP服务器密码
	$mail->SetFrom ( $config ['from_email'], $config ['from_name'] );
	$replyEmail = $config ['reply_email'] ? $config ['reply_email'] : $config ['reply_email'];
	$replyName = $config ['reply_name'] ? $config ['reply_name'] : $config ['reply_name'];
	$mail->AddReplyTo ( $replyEmail, $replyName );
	$mail->Subject = $subject;
	$mail->MsgHTML ( $body );
	$mail->AddAddress ( $to, $name );
	if (is_array ( $attachment )) { // 添加附件
		foreach ( $attachment as $file ) {
			if (is_array ( $file )) {
				is_file ( $file ['path'] ) && $mail->AddAttachment ( $file ['path'], $file ['name'] );
			} else {
				is_file ( $file ) && $mail->AddAttachment ( $file );
			}
		}
	} else {
		is_file ( $attachment ) && $mail->AddAttachment ( $attachment );
	}
	return $mail->Send () ? true : $mail->ErrorInfo;
}

/**
 * +----------------------------------------------------------
 * 功能：剔除危险的字符信息
 * +----------------------------------------------------------
 *
 * @param string $val
 *        	+----------------------------------------------------------
 * @return string 返回处理后的字符串
 *         +----------------------------------------------------------
 */
function remove_xss($val) {
	// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are
	// allowed
	// this prevents some character re-spacing such as <java\0script>
	// note that you have to handle splits with \n, \r, and \t later since they
	// *are* allowed in some inputs
	$val = preg_replace ( '/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val );

	// straight replacements, the user should never need these since they're
	// normal characters
	// this prevents like <IMG SRC=@avascript:alert('XSS')>
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for($i = 0; $i < strlen ( $search ); $i ++) {
		// ;? matches the ;, which is optional
		// 0{0,7} matches any padded zeros, which are optional and go up to 8
		// chars
		// @ @ search for the hex values
		$val = preg_replace ( '/(&#[xX]0{0,8}' . dechex ( ord ( $search [$i] ) ) . ';?)/i', $search [$i], $val ); // with
		// a
		// ;
		// @
		// @
		// 0{0,7}
		// matches
		// '0'
		// zero
		// to
		// seven
		// times
		$val = preg_replace ( '/(&#0{0,8}' . ord ( $search [$i] ) . ';?)/', $search [$i], $val ); // with
		// a
		// ;
	}

	// now the only remaining whitespace attacks are \t, \n, and \r
	$ra1 = array (
			'javascript',
			'vbscript',
			'expression',
			'applet',
			'meta',
			'xml',
			'blink',
			'link',
			'style',
			'script',
			'embed',
			'object',
			'iframe',
			'frame',
			'frameset',
			'ilayer',
			'layer',
			'bgsound',
			'title',
			'base'
	);
	$ra2 = array (
			'onabort',
			'onactivate',
			'onafterprint',
			'onafterupdate',
			'onbeforeactivate',
			'onbeforecopy',
			'onbeforecut',
			'onbeforedeactivate',
			'onbeforeeditfocus',
			'onbeforepaste',
			'onbeforeprint',
			'onbeforeunload',
			'onbeforeupdate',
			'onblur',
			'onbounce',
			'oncellchange',
			'onchange',
			'onclick',
			'oncontextmenu',
			'oncontrolselect',
			'oncopy',
			'oncut',
			'ondataavailable',
			'ondatasetchanged',
			'ondatasetcomplete',
			'ondblclick',
			'ondeactivate',
			'ondrag',
			'ondragend',
			'ondragenter',
			'ondragleave',
			'ondragover',
			'ondragstart',
			'ondrop',
			'onerror',
			'onerrorupdate',
			'onfilterchange',
			'onfinish',
			'onfocus',
			'onfocusin',
			'onfocusout',
			'onhelp',
			'onkeydown',
			'onkeypress',
			'onkeyup',
			'onlayoutcomplete',
			'onload',
			'onlosecapture',
			'onmousedown',
			'onmouseenter',
			'onmouseleave',
			'onmousemove',
			'onmouseout',
			'onmouseover',
			'onmouseup',
			'onmousewheel',
			'onmove',
			'onmoveend',
			'onmovestart',
			'onpaste',
			'onpropertychange',
			'onreadystatechange',
			'onreset',
			'onresize',
			'onresizeend',
			'onresizestart',
			'onrowenter',
			'onrowexit',
			'onrowsdelete',
			'onrowsinserted',
			'onscroll',
			'onselect',
			'onselectionchange',
			'onselectstart',
			'onstart',
			'onstop',
			'onsubmit',
			'onunload'
	);
	$ra = array_merge ( $ra1, $ra2 );

	$found = true; // keep replacing as long as the previous round replaced
	// something
	while ( $found == true ) {
		$val_before = $val;
		for($i = 0; $i < sizeof ( $ra ); $i ++) {
			$pattern = '/';
			for($j = 0; $j < strlen ( $ra [$i] ); $j ++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra [$i] [$j];
			}
			$pattern .= '/i';
			$replacement = substr ( $ra [$i], 0, 2 ) . '<x>' . substr ( $ra [$i], 2 ); // add
			// in
			// <>
			// to
			// nerf
			// the
			// tag
			$val = preg_replace ( $pattern, $replacement, $val ); // filter out the
			// hex tags
			if ($val_before == $val) {
				// no replacements were made, so exit the loop
				$found = false;
			}
		}
	}
	return $val;
}

/**
 * +----------------------------------------------------------
 * 功能：计算文件大小
 * +----------------------------------------------------------
 *
 * @param int $bytes
 *        	+----------------------------------------------------------
 * @return string 转换后的字符串
 *         +----------------------------------------------------------
 */
function byteFormat($bytes) {
	$sizetext = array (
			" B",
			" KB",
			" MB",
			" GB",
			" TB",
			" PB",
			" EB",
			" ZB",
			" YB"
	);
	return round ( $bytes / pow ( 1024, ($i = floor ( log ( $bytes, 1024 ) )) ), 2 ) . $sizetext [$i];
}
function checkCharset($string, $charset = "UTF-8") {
	if ($string == '')
		return;
	$check = preg_match ( '%^(?:
                                [\x09\x0A\x0D\x20-\x7E] # ASCII
                                | [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
                                | \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
                                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
                                | \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
                                | \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
                                | [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
                                | \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
                                )*$%xs', $string );

	return $charset == "UTF-8" ? ($check == 1 ? $string : iconv ( 'gb2312', 'utf-8', $string )) : ($check == 0 ? $string : iconv ( 'utf-8', 'gb2312', $string ));
}

/*
 * +------------------------------------ 功能：递归拼接后台二级菜单数组 @param int
* $rs	二级菜单数据集数组 @return array 转换后的数组
*/
function submenuArray($rs) {
	foreach ( $rs as $mkey ) {
	}
	return "aaaaaaaaaaaaaa";
}




?>