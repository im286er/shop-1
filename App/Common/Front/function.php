<?php
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
 * utf-8 转unicode
 *
 * @param string $name
 * @return string
 */
function utf8_unicode($name){
	$name = iconv('UTF-8', 'UCS-2', $name);
	$len  = strlen($name);
	$str  = '';
	for ($i = 0; $i < $len - 1; $i = $i + 2){
		$c  = $name[$i];
		$c2 = $name[$i + 1];
		if (ord($c) > 0){   //两个字节的文字
			$str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
			//$str .= base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
		} else {
			$str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
			//$str .= str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
		}
	}
	$str = strtoupper($str);//转换为大写
	return $str;
}

/**
 * unicode 转 utf-8
 *
 * @param string $name
 * @return string
 */
function unicode_decode($name)
{
	$name = strtolower($name);
	// 转换编码，将Unicode编码转换成可以浏览的utf-8编码
	$pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
	preg_match_all($pattern, $name, $matches);
	if (!empty($matches))
	{
		$name = '';
		for ($j = 0; $j < count($matches[0]); $j++)
		{
		$str = $matches[0][$j];
			if (strpos($str, '\\u') === 0)
			{
			$code = base_convert(substr($str, 2, 2), 16, 10);
			$code2 = base_convert(substr($str, 4), 16, 10);
			$c = chr($code).chr($code2);
			$c = iconv('UCS-2', 'UTF-8', $c);
			$name .= $c;
			}
			else
			{
			$name .= $str;
			}
			}
			}
			return $name;
			}

//得到唯一不重复随机数
function getRandOnlyId() 	// 产生32位唯一ID
{
	$tempid =generate_rand_code (11). time () . generate_rand_code ( 11 );
	return $tempid;
}
function generate_rand_code($l) { // 产生随机数$l为多少位
	//$c = "0123456789";
	$c= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	srand ( ( double ) microtime () * 1000000 );
	for($i = 0; $i < $l; $i ++) {
		$rand .= $c [rand () % strlen ( $c )];
	}
	return $rand;
}

?>