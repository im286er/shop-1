<?php
class CatesAction extends CommonAction {
	public function mail() {
		$url = 'http://localhost/3DF/index.php/users/active/?sv=BA542F3ADE13E84AFB0A7679A99DCAB8';
		$content = '你好：<br/><br/>感谢注册3D Factory，很高兴您能使用我们的服务。3D Factory是一个简单的交友分享社区，在这里每个人都可以发现自己感兴趣的内容。<br/><br/>点击以下链接就可以激活您的帐号了:<br/><br/> {active_url} <br/><br/> 如果链接无法点击，请完整拷贝到浏览器地址栏里直接访问。<br/><br/><br/><a href="http://www.ab3d.net/" target="_blank">3D Factory团队</a>';
		$content = preg_replace ( '/{active_url}/', $url, $content );
		$title = '欢迎注册3D Factory！请确认您的邮箱';
		$re = think_send_mail ( 'wow730@gmail.com', 'wow730', $title, $content );
		var_dump ( $re );
	}
	private function renderPage() {
		$Cates = D ( 'Cates' );
		$cateCombo = $Cates->getCateCombo ();
		// 模版赋值
		$this->assign ( 'form_combo', $cateCombo );
		$this->assign ( 'front', L ( 'front_label' ) );
		$this->display ();
	}
	
	/**
	 * 分类列表
	 * @access public
	 * @return mix
	 */
	public function index() {
		/* 检查权限 */
		$Users = D ( 'Users' );
		$Users->find ( $this->_session ( 'userid' ) );
		
		$Cates = D ( 'Cates' );
		$cat_list = $Cates->getCateList ( 0, 0, false );

        //var_dump($cat_list['1264']);

		// 模版赋值
		$this->assign ( 'front', L ( 'front_label' ) );
		$this->assign ( 'full_page', 1 );
		$this->assign ( 'cate_total_num', $Cates->count () );
		$this->assign ( 'cat_list', $cat_list );
		$this->assign ( 'catelabel', L ( 'cate_list_label' ) );
		$this->assign ( 'cateop', L ( 'cate_op_label' ) );
		$this->assign ( 'cp_home', L ( 'cp_home' ) );
		$this->assign ( 'ur_here', L ( '20_cates_manage' ) );
		$this->assign ( 'action_link', array (
				'text' => L ( '20_02_cates_add' ),
				'href' => './add' 
		) );
		$this->assign ( 'copyright', L ( 'copyright' ) );
		
		$this->display ();
	}
	/**
	 * 分类编辑
	 *
	 * @access public
	 * @return mix
	 */
	public function edit() {
		// $this->assign ( 'waitSecond', C ( 'JUMP_URL_WAIT_SECONDS' ) ); //
		// 跳转时间设定
		
		
		/* 检查权限 */
		$Users = D ( 'Users' );
		$Users->find ( $this->_session ( 'userid' ) );
		
		
		if ($this->isPost ()) {
			try {
				$id = intval ( $this->_post ( 'cat_id' ) );
				$Cates = D ( 'Cates' );
				$Cates->find ( $id );
				if ($Cates->updateCate ()) {
                    $this->success ( L ( 'success_tips' ), '__APP__/Cates/index' );
				}
			} catch ( Exception $e ) {
				$this->assign ( 'alert_label', 1 );
				$this->assign ( 'alert_info', $e->getMessage () );
				$id = $this->_get ( 'id' );
				$Cates = D ( 'Cates' );
				$Cates->find ( $id );
				$cateCombo = $Cates->getCateCombo_alltype ( $Cates->pc_parentid );
				// 模版赋值
				$this->assign ( 'form_combo', $cateCombo );
				$this->assign ( 'front', L ( 'front_label' ) );
				$this->assign ( 'cat_info', $Cates->data () );
				$this->assign ( 'formlabel', L ( 'form_label' ) );
				$this->assign ( 'catelabel', L ( 'cate_add_label' ) );
				$this->assign ( 'cp_home', L ( 'cp_home' ) );
				$this->assign ( 'ur_here', L ( '20_cates_manage' ) );
				$this->assign ( 'action_link', array (
						'href' => '../../index' 
				));
				$this->assign ( 'copyright', L ( 'copyright' ) );
				$this->assign ( 'js_languages', L ( 'js_languages' ) );
				$this->assign ( 'form_act', 'cateedit' );
				
				$this->display ();
			}
		} else {
			$id = $this->_get ( 'id' );
			$Cates = D ( 'Cates' );
			$Cates->find ( $id );
			$cateCombo = $Cates->getCateCombo_alltype ( $Cates->pc_parentid );
			// 模版赋值
			$this->assign ( 'form_combo', $cateCombo );
			$this->assign ( 'front', L ( 'front_label' ) );
			$this->assign ( 'cat_info', $Cates->data () );
			$this->assign ( 'formlabel', L ( 'form_label' ) );
			$this->assign ( 'catelabel', L ( 'cate_add_label' ) );
			$this->assign ( 'cp_home', L ( 'cp_home' ) );
			$this->assign ( 'ur_here', L ( '20_cates_manage' ) );
			$this->assign ( 'action_link', array (
					'href' => '../../index' 
			) );
			$this->assign ( 'copyright', L ( 'copyright' ) );
			$this->assign ( 'js_languages', L ( 'js_languages' ) );
			$this->assign ( 'form_act', 'cateedit' );
			
			$this->display ();
		}
	}
	/**
	 * 分类添加
	 *
	 * @access public
	 * @return mix
	 */
	function add() {
		// $this->assign ( 'waitSecond', C ( 'JUMP_URL_WAIT_SECONDS' ) ); //
		// 跳转时间设定
		/* 检查权限 */
		$Users = D ( 'Users' );
		$Users->find ( $this->_session ( 'userid' ) );
		//if (! $Users->admin_priv ( 'cates_manage' )) {
		//	$this->error ( L ( 'no_permission' ), '__APP__/index' );
		//}
		
		if ($this->isPost ()) {
			$Cates = D ( 'Cates' );
			$cat_arr=$Cates->getCategoryByCID($_POST['parent_id']);
			$_POST['cat_type']=$cat_arr['pc_type'];
			
			$validate = array (
					array (
							$this->DBF->ProductCategory->Name,
							'require',
							L ( 'catname_empty' ) 
					),
					array (
							$this->DBF->ProductCategory->DispWeight,
							'0,99999',
							L ( 'cat_order_err' ),
							0,
							'between' 
					),
					array (
							$this->DBF->ProductCategory->ParentID,
							'require',
							L ( 'cat_noselect' ) 
					),
					array (
							$this->DBF->ProductCategory->ParentID,
							'1,99999',
							L ( 'cat_noselect' ),
							0,
							'between' 
					),
					array (
							$this->DBF->ProductCategory->$Ptype,
							'1,99999',
							L ( 'cat_noselect' ),
							0,
							'between'
					)
			);
			
			//var_dump($_POST);
			
			$Cates->setProperty ( "_validate", $validate );
			// 			/var_dump($Cates);
			
			
			if (! $Cates->create ()) {
				$this->assign ( 'alert_label', 1 );
				$this->assign ( 'alert_info', $Cates->getError () );
				$this->renderPage ();
			} else {
				// 加一个判断第五层不能添加
				$Cates->add ();
				$this->success ( L ( 'success_tips' ), '__APP__/Cates/index' );
			}
		} else {
			$Cates = D ( 'Cates' );
			$cateCombo = $Cates->getCateCombo_alltype ();
			// 模版赋值
			$this->assign ( 'form_combo', $cateCombo );
			$this->assign ( 'front', L ( 'front_label' ) );
			$this->assign ( 'formlabel', L ( 'form_label' ) );
			$this->assign ( 'catelabel', L ( 'cate_add_label' ) );
			$this->assign ( 'cp_home', L ( 'cp_home' ) );
			$this->assign ( 'ur_here', L ( '20_cates_manage' ) );
			$this->assign ( 'action_link', array (
					'text' => L ( '20_01_cates_list' ),
					'href' => './index' 
			) );
			$this->assign ( 'copyright', L ( 'copyright' ) );
			$this->assign ( 'js_languages', L ( 'js_languages' ) );
			$this->assign ( 'form_act', 'cateadd' );
			
			$this->display ();
		}
	}
	/**
	 * 分类ajax操作
	 *
	 * @access public
	 * @return null
	 */
	public function ajax() {
		switch ($_REQUEST ['act']) {
			case 'query' :
				$this->query ();
				break;
			case 'remove' :
				$this->remove ();
				break;
			case 'edit_cate_ord' :
				$this->edit_cate_ord ();
				break;
		}
	}
	private function edit_cate_ord() {
		try {
			$id = intval ( $this->_post ( 'id' ) );
			$val = intval ( $this->_post ( 'val' ) );
			
			$Cates = D ( 'Cates' );
			$Cates->find ( $id );
			$Cates->pc_dispweight = $val;
			$Cates->save ();
			
			make_json_result ( $val );
		} catch ( Exception $e ) {
			make_json_error ( $e->getMessage () );
		}
	}
	
	/**
	 * 分类删除
	 *
	 * @access private
	 * @return mix
	 */
	public function remove() {
		//var_dump($this->_get( 'id' ));
		//exit();
		try {
			$id = intval ( $this->_get ( 'id' ) );
			$Cates = D ( 'Cates' );
			$Cates->find ( $id );
			$Cates->deleteCate ();
		
		} catch ( Exception $e ) {
			//var_dump($id);
			//make_json_error ( $e->getMessage () );
		}
	
		/* 获取分类列表 */
		$list = $Cates->getCateList ( 0, 0, false );
		// 模版赋值
		$this->assign ( 'cat_list', $list );
		$this->assign ( 'catelabel', L ( 'cate_list_label' ) );
		$this->assign ( 'cateop', L ( 'cate_op_label' ) );
		$this->assign ( 'action_link', array (
				'href' => '../../index'
		)

		);
		//make_json_result ( $this->fetch ( 'index' ), '', array () );
	}
	
}