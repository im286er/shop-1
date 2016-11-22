<?php
/**
 * 用户帮助类
 *
 * @author miaomin 
 * Apr 18, 2014 10:00:22 AM
 *
 * $Id$
 */
class UserHelper extends Model {
	
	/**
	 *
	 * @var UsersModel
	 */
	public $users;
	
	/**
	 * 用户帮助类
	 */
	public function __construct($users) {
		parent::__construct ();
		$this->users = $users;
	}
	
	/**
	 * 返回一个用户昵称
	 */
	public function getUserName() {
		return $this->users->u_dispname;
	}
	
	/**
	 * 生成一个唯一标识用户的字串
	 */
	public function genIdentifier() {
		return md5 ( $this->users->u_salt . md5 ( $this->users->u_email . $this->users->u_salt ) );
	}
	
	/**
	 * 为identifier加密
	 *
	 * @param string $identifier        	
	 */
	public function encodeIdentifier($identifier) {
		return base64_encode ( $identifier );
	}
	
	/**
	 * 获取一个用户ID的混淆值
	 */
	public function genUIDChaos() {
		return $this->users->u_id + $this->users->getIdChaos ();
	}
	
	/**
	 * 生成一个永久登录用的Token值
	 *
	 * @return string
	 */
	public function genLoginToken() {
		return md5 ( uniqid ( rand (), TRUE ) );
	}
}
?>