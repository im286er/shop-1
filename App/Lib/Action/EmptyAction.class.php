<?php
class EmptyAction extends Action{
	
	public function index () {
		header("HTTP/1.0 404 Not Found");
		$this->display('Public:404');
		// $this->redirect('/Home','',3,'页面错误，3秒后跳转到本站首页！');
	}
	
	function _empty(){
		header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
		//$this->display('./404.html');
	$this->display('Public:404');
	}
}
?>