<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {front_user} function plugin
 *
 * Type: function<br>
 * Name: front_user<br>
 * Date: March 13, 2013<br>
 * Purpose: Display FrontUser Login Infomation.<br>
 * Params:
 * <pre>
 * - NULL - NULL NULL
 * </pre>
 * Examples:<br>
 * <pre>
 * {admin_user}
 * </pre>
 *
 * @author Miao Min
 * @version 1.0
 * @return string null
 */
function smarty_function_front_user($params, $template) {
	$res = '';
	
	if ($_SESSION ['f_userid']) {
		$Users = D ( 'Users' );
		$Users->find ( $_SESSION ['f_userid'] );
		$res .= "<ul class=\"topnav fr\">";
		$res .= "<li><a href=\"__APP__/users/index\" class=\"b\">".$Users->u_dispname."</a><a href=\"__APP__/users/index\">控制面板</a><a href=\"__APP__/users/logout\">注销</a></li><li><a href=\"__DOC__/bbs\">论坛</a></li>";
		$res .= "</ul>";
	} else {
		$res .= "<ul class=\"topnav fr\">";
		$res .= "<li><a href=\"__APP__/users/register\">新用户注册</a></li>";
		$res .= "<li class=\"str\"><a href=\"__APP__/login/index\">登录</a></li>";
		$res .= "<li><a href=\"__DOC__/bbs\">论坛</a></li>";
		$res .= "</ul>";
	}
	
	return $res;
}

?>