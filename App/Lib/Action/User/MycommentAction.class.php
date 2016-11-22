<?php
class MyCommentAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		//
		parent::__construct ();
		
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
	}
	
	/**
	 * 我的评论
	 *
	 * @access public
	 * @return mixed
	 */
	public function index() {
		try {
			// 分页
			$page_limit = 20;
			$nowPage = $this->_get ( 'page' );
			(empty ( $nowPage )) ? $nowPage = 1 : $nowPage = $this->_get ( 'page' );
			// 起始页
			$start = ($nowPage - 1) * $page_limit;
			$UC = D ( 'UserComment' );
			$where = array (
					'u_id' => $this->_session ( 'f_userid' ) 
			);
			$res = $UC->where ( $where )->limit ( $start, $page_limit )->select ();
			$this->assign ( 'CommentResult', $res );
			// 评论分页
			$comment_count = $UC->where ($where)->count ();
			@load ( '@.Paging' );
			$this->assign ( 'Paging', getPagingInfo2 ( $comment_count, $this->_get ( 'page' ), $page_limit, 4, __SELF__ ) );
			// 渲染
			$this->_renderPage ();
		} catch ( Exception $e ) {
			//
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 删除评论
	 *
	 * @access public
	 * @return null
	 */
	public function del() {
		try {
			$id = $this->_get ( 'id' );
			$UC = D ( 'UserComment' );
			$UC->find ( $id );
			if ($UC->u_id === $this->_session ( 'f_userid' )) {
				$UC->delete ();
				// TODO
				// 跳转路径以后都可以整理入配置文件
				$this->redirect ( '/mycomment' );
			}
		} catch ( Exception $e ) {
			// 异常处理
			echo $e->getMessage ();
		}
	}
}
?>