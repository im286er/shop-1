<?php
class LogoutAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		//
		parent::__construct ();
	}
	
	/**
	 * 登出
	 *
	 * @access public
	 * @return mixed
	 */
	public function index() {
		try {
			// 永久登录
			cookie ( 'b_m0', null );
			session ( null );
			$from_url = I ( 'fromurl' );
			$type = I('type');
			// 登录成功后的跳转路径
			if($type == 'iwx'){
				$jump_url = WEBROOT_URL.'/index.php/iwx';
			}else{
				($from_url) ? $jump_url = pub_encode_pass ( $from_url, "url", "decode" ) : $jump_url = WEBROOT_URL;
			}
			redirect ( $jump_url );
		} catch ( Exception $e ) {
			$this->error ( $e->getMessage (), '' );
		}
	}
}
?>