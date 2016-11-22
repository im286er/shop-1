<?php
class IndexAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct ();
		

	}
	
	/**
	 * 用户首页
	 *
	 * @access public
	 * @return null
	 */
	public function index() {
		echo "abc";
		
		$this->display();
	}

	
}