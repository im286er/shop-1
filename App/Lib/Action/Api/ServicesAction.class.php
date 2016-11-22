<?php
/**
 * Services Action
 * 处理所有API的请求
 *
 * @author miaomin 
 * Oct 15, 2013 10:46:55 AM
 * 
 * $Id: ServicesAction.class.php 1239 2014-02-20 06:51:24Z miaomiao $
 */
class ServicesAction extends CommonAction {
	public function __call($method, $args) {
		switch ($method) {
			case 'rest' :
				$rest = A ( 'Api/Rest' );
				break;
			case 'ex':
				$ex = A ( 'Api/External' );
				break;
		}
	}
}
?>