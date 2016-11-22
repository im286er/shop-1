<?php
class UsersafeAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		//
		parent::__construct ();
	}
	
	public function index(){
		$this->_renderPage();
	}
	
	
}
?>