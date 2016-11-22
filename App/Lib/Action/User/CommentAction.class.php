<?php
/**
 * 评论类
 *
 * @author miaomin 
 * Jul 15, 2013 3:55:47 PM
 */
class CommentAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct ();
		
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
		
		// load ( "@.DBF" );
		$this->DBF = new DBF ();
	}
	
	/**
	 * 发表
	 *
	 * @access public
	 * @return null
	 */
	public function post() {
		try {
			if ($this->isPost ()) {
				// 数据验证
				$this->_validComment ( $this->_post () );
				
				$Users = D ( 'Users' );
				$Users->find ( $this->_session ( 'f_userid' ) );
				
				$UC = D ( 'UserComment' );
				$UC->create ();
				$UC->u_id = $Users->u_id;
				$UC->uc_content = $this->_post ( 'comment' );
				$UC->uc_pid = $this->_post ( 'pid' );
				$UC->uc_type = $this->_post ( 'type' );
				$UC->uc_createdate = get_now ();
				$UC->uc_replyid = $this->_post ( 'reply_id' );
				// 测试用
				$UC->uc_slabel = 1;
				$UC->add ();
				
				redirect ( $this->_post ( 'from' ) );
			} else {
				// 跳转页面
				if ($this->_get ( 'pid' )) {
					redirect ( FRONT_PAGE . '/models/detail/id/' . $this->_get ( 'pid' ) );
				} else {
					redirect ( FRONT_PAGE );
				}
			}
		} catch ( Exception $e ) {
			$this->error ( $e->getMessage (), $this->_post ( 'from' ) );
		}
	}
	
	/**
	 * 验证评论信息
	 *
	 * @param array $req        	
	 * @throws Exception
	 * @return boolean
	 */
	protected function _validComment(array $req) {
		$PVC = new PVC2 ();
		$PVC->setStrictMode ( true )->setModeArray ()->SourceArray = $req;
		// 评论验证
		$PVC->Length ( 1, 255 )->Error ( L ( 'no_comment' ) )->add ( 'comment' );
		
		if (strtolower ( $req['verifycode'] ) != strtolower ( $this->_session ( 'php_captcha' ) )) {
			throw new Exception ( '验证码错误！' );
		}
		
		if (! $PVC->verifyAll ()) {
			throw new Exception ( $PVC->Error );
		}
		return true;
	}
	protected function displayError($Error, $Key = 'ErrInfo') {
		$this->assign ( $Key, $Error );
		$this->display ();
	}
}