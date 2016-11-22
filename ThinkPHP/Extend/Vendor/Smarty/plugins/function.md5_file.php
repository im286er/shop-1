<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {md5_file} function plugin
 *
 * Type: function<br>
 * Name: md5_file<br>
 * Params:
 * Examples:<br>
 * <pre>
 * {md5_file filename="abc.php"}
 * </pre>
 *
 * @author Weifu Zheng
 * @version 1.0
 * @return hash string
 */
function smarty_function_md5_file($params) {
	$fileName = $params['filename'];
	$res = md5_file($fileName);
	return $res;
}

?>