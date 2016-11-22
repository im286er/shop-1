<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {admin_user} function plugin
 *
 * Type: function<br>
 * Name: admin_user<br>
 * Date: March 5, 2013<br>
 * Purpose: Display Administrator Login Infomation.<br>
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
function smarty_function_admin_user($params, $template) {
	$res = '';
	
	$Users = D ( 'Users' );
	$Users->find ( $_SESSION ['userid'] );
	
	$top_label = L ( 'top_label' );
	
	$res = "<div class=\"userpl fr\"><div class=\"info\">";
	$res .= "<h4><a href=\"__APP__/users/profile\">" . $Users->u_dispname . "</a></h4>";
	$res .= "<span>" . $Users->u_title . "</span>";
	$res .= "<a href=\"__APP__/users/profile\" class=\"btn\">" . $top_label ['myaccount'] . "</a>";
	$res .= "<a href=\"__APP__/users/logout\" class=\"btn\">" . $top_label ['signout'] . "</a>";
	$res .= "</div>";
	$res .= "<div class=\"avatar\">";
	$res .= "<a href=\"__APP__/users/profile\"><img src=\"__PUBLIC__/images/_fortestonly_/avater.jpg\" alt=\"" . $Users->u_realname . "\" /></a></div></div>";
	
	return $res;
}

?>