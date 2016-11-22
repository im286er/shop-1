<?php
/**
 * User分组使用的CommonAction类
 *
 * @author miaomin 
 * Jul 2, 2013 6:15:11 PM
 */
class CommonAction extends Action {
	/**
	 * 构造函數
	 */
	public function __construct() {
		parent::__construct ();
		session_start ();
		
		if (C ( 'ENABLE_ACCESSCODE' ) && ! (isset ( $_SESSION ['AccessCode'] ) && $_SESSION ['AccessCode'] == C ( 'ACCESSCODE' ))) {
			redirect ( WEBROOT_PATH . '/index.php/access' );
		}
	}
	
	/**
	 * 判断登录
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function _isLogin() {
		if ($this->_session ( 'f_userid' ) && $this->_session ( 'f_logindate' )) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 渲染页面
	 *
	 * @access protected
	 * @return null
	 */
	protected function _renderPage() {
		if ($this->_isLogin ()) {
			$Users = new UsersModel ();
			$Users->find ( $_SESSION ['f_userid'] );
			$UA = $Users->getUserAcc ();
			$this->assign ( 'userBasic', $Users->data () );
			$this->assign ( 'isLogin', 1 );
			$this->assign ( 'userAcc', $UA->u_vcoin_av );
			$this->assign ( 'userAcc_rmb', $UA->u_rcoin_av );
			$this->assign ( 'u', $this->_session () );
		}
		$this->display ();
	}
	
	/**
	 * 跳转至登录界面
	 *
	 * @access protected
	 * @return null
	 */
	protected function _needLogin() {
		// TODO
		// 跳转路径以后都可以整理入配置文件
		redirect ( '/user.php' . '/login/?from_url=' . __SELF__ );
	}
}
?>