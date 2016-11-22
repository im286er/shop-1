<?php
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


//根据系统编码转换中文
function iconv_code($str){
	$sys_code=C('is_utf8');
	if(!$sys_code){//系统编码是GB的
		$result=iconv('utf-8','gb2312',$str);
	}else{
		$result=$str;
	}
	return $result;
}

function get_isadmin($str){
	if($str){
		$result="是";
	}else{
		$result="否";
	}
	return $result;
}
/**
 * 生成一个随机数
 *
 * @param unknown_type $start
 * @param unknown_type $end
 * @return number

function genvcode($start, $end) {
	$start = intval ( $start );
	$end = intval ( $end );
	return mt_rand ( $start, $end );
}
 */
/**
 * 生成一个签名
 *
 * @param string $parameter
 * @param $vcode //
 *        	必须是1-28
 * @param string $pubkey
 *        	// 必须是32位

function gensign($parameter, $vcode, $pubkey) {
	$cutstart = $vcode - 1;
	return md5 ( md5 ( $parameter ) . substr ( $pubkey, $cutstart, 4 ) );
} */

function version_type($v){
	if($v==1){
		$result="正式版本";
	}elseif($v==2){
		$result="测试版本";
	}
	return $result;
}
function version_level($v){
	if($v==1){
		$result="必须更新";
	}elseif($v==2){
		$result="非必须更新";
	}
	return $result;
}


function unlimitedForLayer($date, $name = 'child', $cid = 0){
	
	$arr = array();
	
	foreach ($date as $v){
		
		if($v['pma_id'] == $cid){
			
			$v[$name] = unlimitedForLayer($date, $name, $v['pma_id']);
			
			$arr[] = $v;
		}
	}
	return $arr;
}
?>