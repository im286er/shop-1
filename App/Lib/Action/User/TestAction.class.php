<?php
/**
 * Test类
 *
 * @author miaomin 
 * Jun 21, 2013 2:58:39 PM
 */
class testAction extends CommonAction {
	/**
	 * 首页
	 *
	 * @access public
	 * @return null
	 */
	public function index() {
		$this->_renderPage ();
	}
	
	/**
	 */
	public function bookdetail() {
		$this->_renderPage ();
	}
	
	/**
	 * 测试UA自动提示
	 */
	public function ua() {
		$id = $this->_get ( 'uid' );
		$Users = new UsersModel ();
		$Users->find ( $id );
		$username = $Users->helper->getUserName();
		echo '$Username: ' . $username;
	}
}