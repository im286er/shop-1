<?php
/**
 * Queue通用方法类
 *
 * @author miaomin 
 * Feb 20, 2014 10:29:48 AM
 *
 * $Id: Nqueue.php 1253 2014-02-21 09:05:29Z miaomiao $
 */
class Nqueue {
	
	/**
	 * Queue通用方法类
	 */
	public function __construct() {
	}
	
	/**
	 * 向队列增加一个任务
	 *
	 * @param string $que        	
	 * @param string $job        	
	 * @param array $args        	
	 */
	public function addQueue(AbstractPqueue $queObj, $args = array(), $return = 1) {
		if ($args) {
			$queObj->setArgs ( $args );
		}
		
		return $queObj->add ( $return );
	}
}
?>