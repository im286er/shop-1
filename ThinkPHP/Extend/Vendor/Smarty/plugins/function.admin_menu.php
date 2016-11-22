<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {admin_menu} function plugin
 *
 * Type: function<br>
 * Name: admin_menu<br>
 * Date: March 5, 2013<br>
 * Purpose: Generate 3D Factory Central Administration Menu<br>
 * Params:
 * <pre>
 * - location - hightlight location
 * </pre>
 * Examples:<br>
 * <pre>
 * {admin_menu location="dashboard"}
 * </pre>
 *
 * @author Miao Min
 * @version 1.0
 * @return string null
 */
function smarty_function_admin_menu($params, $template) {
	$res = '';
	$menus = array ();
	$pattern = '/' . $params ['location'] . '/';
	$front_label = L ( 'front_label' );
	
	$modules = get_menu_arr ();
	
	foreach ( $modules as $key => $value ) {
		ksort ( $modules [$key] );
	}
	ksort ( $modules );
	foreach ( $modules as $key => $val ) {
		$menus [$key] ['label'] = L ( $key );
		if (is_array ( $val )) {
			foreach ( $val as $k => $v ) {
				$menus [$key] ['children'] [$k] ['label'] = L ( $k );
				$menus [$key] ['children'] [$k] ['action'] = $v;
			}
		} else {
			$menus [$key] ['action'] = $val;
		}
		// 如果children的子元素长度为0则删除该组
		if (empty ( $menus [$key] ['children'] )) {
			unset ( $menus [$key] );
		}
	}
	
	
	//$res = "<div class=\"sidebar\"><div class=\"ffsearch\"><form method=\"post\" action=\"ffsearch.html\"><input type=\"text\" id=\"ffsearch\" /><button type=\"submit\">" . $front_label['search'] . "</button></form></div><ul id=\"globalnav\">";
	
	$res = "<div class=\"sidebar\"><ul id=\"globalnav\">";
	
	foreach ( $menus as $key => $val ) {
		if (preg_match ( $pattern, $key )) {
			$res .= "<li><a href=\"#\" class=\"st\">" . $val ['label'] . "</a>";
		} else {
			$res .= "<li><a href=\"#\">" . $val ['label'] . "</a>";
		}
		$res .= "<ul>";
		foreach ( $val ['children'] as $k => $v ) {
			$res .= "<li><a href=\"" . $v ['action'] . "\">" . $v ['label'] . "</a></li>";
		}
		$res .= "</ul>";
		$rer .= "</li>";
	}
	$res .= "</ul></div>";
	return $res;
}

?>