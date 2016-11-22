<?php
function get_globals($globaltype) {
	require 'globals.php';
	return $globaltype;
}

function genDVSReturnStr($arr, $separator = '#', $keyname = null) {
	$res = '';
	if (is_array ( $arr )) {
		foreach ( $arr as $key => $val ) {
			if (! empty ( $keyname )) {
				$res .= $val [$keyname] . $separator;
			} else {
				$res .= $val . $separator;
			}
		}
		if (substr ( $res, - 1 ) === $separator) {
			$res = substr ( $res, 0, - 1 );
		}
	}
	return $res;
}



function getname($pcname){//获取文件名称，去掉|以及前面部分
	$t=strpos($pcname,"|",0);
	if($t){
		$result=substr($pcname,$t+1,strlen($pcname));
	}else{
		$result=$pcname;
	}
	return $result;
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


?>