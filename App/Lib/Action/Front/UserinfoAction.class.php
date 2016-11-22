<?php
/**
 * 
 *

 */
class UserinfoAction extends CommonAction {
	
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct ();
		
		
	} 
	
	/**
	 * 首页
	 */
	public function index() {
		
		$this->_renderPage();
	}
	
	
}