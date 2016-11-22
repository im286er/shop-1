<?php
/**
 * 生成一个一周过期的时间戳
 */
function genWeekTimeout() {
	return time () + genWeekTime ();
}
/**
 * 返回一个一周的时间戳
 */
function genWeekTime() {
	return 60 * 60 * 24 * 7;
	//return 60;
}
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
function genCHNCity($province_id) {
	$City = D ( 'City' );
	$res = $City->where ( 'pi_id = ' . $province_id )->select ();
	$res = transDBarrToOptarr ( $res, 'ci_name', 'pi_no' );
	return $res;
}
function genCHNProvince() {
	$Province = D ( 'Province' );
	$res = $Province->where ( '1=1' )->select ();
	$res = transDBarrToOptarr ( $res, 'pi_name', 'pi_fid' );
	return $res;
}
function genDescYear($s = 2014, $e = 1970) {
	$res = array ();
	for($i = $s; $i >= $e; $i --) {
		$arr = array ();
		$arr ['key'] = $i;
		$arr ['value'] = $i;
		$res [] = $arr;
	}
	return $res;
}
function genBirthYear($s = 1970, $e = 2013) {
	$res = array ();
	for($i = $s; $i <= $e; $i ++) {
		$arr = array ();
		$arr ['key'] = $i;
		$arr ['value'] = $i;
		$res [] = $arr;
	}
	return $res;
}
function genBirthMonth($s = 1, $e = 12) {
	$res = array ();
	for($i = $s; $i <= $e; $i ++) {
		$arr = array ();
		$arr ['key'] = $i;
		$arr ['value'] = $i;
		$res [] = $arr;
	}
	return $res;
}
function genBirthDay($s = 1, $e = 31) {
	$res = array ();
	for($i = $s; $i <= $e; $i ++) {
		$arr = array ();
		$arr ['key'] = $i;
		$arr ['value'] = $i;
		$res [] = $arr;
	}
	return $res;
}
function get_search_opt($type) {
	$res = array ();
	switch ($type) {
		case 'thumb' :
			$opt = C ( 'SEARCH.RES_THUMB_OPTION' );
			$disp = L ( 'RES_THUMB_DISP' );
			foreach ( $opt as $key => $val ) {
				$item ['opt'] = $val;
				$item ['name'] = $disp [$key];
				$res [] = $item;
			}
			break;
		case 'count' :
			$opt = C ( 'SEARCH.RES_COUNT_OPTION' );
			$disp = L ( 'RES_COUNT_DISP' );
			foreach ( $opt as $key => $val ) {
				$item ['opt'] = $val;
				$item ['name'] = $disp [$key];
				$res [] = $item;
			}
			break;
		case 'order' :
			$opt = C ( 'SEARCH.RES_ORDER_OPTION' );
			$disp = L ( 'RES_ORDER_DISP' );
			foreach ( $opt as $key => $val ) {
				$item ['opt'] = $val;
				$item ['name'] = $disp [$key];
				$res [] = $item;
			}
			break;
		case 'disp' :
			$opt = C ( 'SEARCH.RES_TYPE_OPTION' );
			$opt_css = C ( 'SEARCH.RES_TYPE_CSS_OPTION' );
			$disp = L ( 'RES_TYPE_DISP' );
			foreach ( $opt as $key => $val ) {
				$item ['opt'] = $val;
				$item ['name'] = $disp [$key];
				$item ['css'] = $opt_css [$key];
				$res [] = $item;
			}
			break;
	}
	return $res;
}

/**
 * 根据数组以及定位符生成一个Select控件的Option选项组
 *
 * 数组的格式如下：
 *
 * $arr = array(0=>array('value'=1,'key'=>'火星'),1=>array('value'=2,'key'=>'地球'))
 *
 *
 * @param array $arr        	
 * @param int $select        	
 * @return string
 */
function get_dropdown_option($arr, $select = 1) {
	$res = '';
	foreach ( $arr as $key => $val ) {
		$disabled = isset ( $arr [$key] ['disabled'] ) && $arr [$key] ['disabled'] ? 'disabled="disabled"' : '';
		if ($val ['value'] == $select) {
			$res .= '<option value="' . $val ['value'] . '" selected="selected" ' . $disabled . '>' . $val ['key'] . '</option>';
		} else {
			$res .= '<option value="' . $val ['value'] . '" ' . $disabled . '>' . $val ['key'] . '</option>';
		}
	}
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
 * @return void
 */
function make_json_result($content, $message = '', $append = array()) {
	make_json_response ( $content, 0, $message, $append );
}

/**
 * 对模版中一些公用元素进行赋值操作
 *
 * @access public
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
	 * return strtr ( $str, array ( "\\\\" => "\\\\\\\\", '_' => '\_', '%' =>
	 * '\%', "\'" => "\\\\\'" ) );
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
 * 增加WHERE语句中的ADD
 *
 * @param String $sql        	
 * @return string
 */
function addWhereStr($sql) {
	return $sql .= ' WHERE ';
}

// 根据系统编码转换中文
function iconv_code($str) {
	$sys_code = C ( 'is_utf8' );
	if (! $sys_code) { // 系统编码是GB的
		$result = iconv ( 'utf-8', 'gb2312', $str );
	} else {
		$result = $str;
	}
	return $result;
}
// 函数名：is_valid_email
// 作 用：判断是否为有效邮件地址
// 参 数：$email（待检测的邮件地址）
// 返回值：布尔值
function is_valid_email($email, $test_mx = false)
{
    if(eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email))
        if($test_mx)
        {
            list($username, $domain) = split("@", $email);
            return getmxrr($domain, $mxrecords);
        }
        else
            return true;
    else
        return false;
}


/**
 * JS提示跳转
 * @param  $tip  弹窗口提示信息(为空没有提示)
 * @param  $type 设置类型 close = 关闭 ，back=返回 ，refresh=提示重载，jump提示并跳转url
 * @param  $url  跳转url
 */
function phpalert($tip = "", $type = "", $url = "") {
    echo header ( "Content-Type:text/html; charset=utf-8" );
    $js = "<script>";
    if ($tip)
        $js .= "alert('" . $tip . "');";
    switch ($type) {
        case "close" : //关闭页面
            $js .= "window.close();";
            break;
        case "back" : //返回
            $js .= "history.back(-1);";
            break;
        case "refresh" : //刷新
            $js .= "parent.location.reload();";
            break;
        case "top" : //框架退出
            if ($url)
                $js .= "top.location.href='" . $url . "';";
            break;
        case "jump" : //跳转
            if ($url)
                $js .= "window.location.href='" . $url . "';";
            break;
        default :
            break;
    }
    $js .= "</script>";
    echo $js;
    if ($type) {
        exit();
    }
}
?>