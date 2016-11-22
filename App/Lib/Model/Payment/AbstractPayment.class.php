<?php
/**
 * Payment抽象类
 *
 * @author miaomin 
 * Mar 2, 2015 1:53:17 PM
 *
 * $Id$
 */
abstract class AbstractPayment {

	/**
	 * 支付
	 */
	abstract protected function pay();
	
	/**
	 * 支付回调
	 */
	abstract protected function callback();
}
?>