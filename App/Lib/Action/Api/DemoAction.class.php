<?php
/**
 * Demo相关API
 *
 * @author miaomin 
 * Feb 20, 2014 2:26:06 PM
 *
 * $Id: DemoAction.class.php 1236 2014-02-20 06:27:11Z miaomiao $
 */
class DemoAction extends CommonAction {
	
	// TODO
	// 魔术方法
	public function __call($name, $arguments) {
		throw new Exception ( $this->RES_CODE_TYPE ['METHOD_ERR'] );
	}
	
	/**
	 * 发送邮件API
	 */
	public function sendmail() {
		import ( "App.Action.User.MailValidateAction" );
		
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		$userId = $args ['uid'];
		$userMail = $args ['mail'];
		
		if ($userId && $userMail) {
			$MVA = new MailValidateAction ();
			$res[] = $MVA->sendRegisterMail ( $userId, $userMail );
		} else {
			throw new Exception ( $this->RES_CODE_TYPE ['PARAMETER_METHOD_ERR'] );
		}
		
		return $res;
	}
}
?>