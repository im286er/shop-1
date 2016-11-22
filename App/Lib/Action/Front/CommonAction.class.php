<?php
class CommonAction extends Action {
	public $DBF;
	
	/**
	 * 构造函数
	 *
	 * @return boolean
	 */
	function __construct() {
		parent::__construct ();
		// exit;
		$this->DBF = new DBF ();
		if (isset ( $_POST ["sessionid"] )) {
			session_id ( $_POST ['sessionid'] );
		}
		session_start ();
		
		
		$this->_isToken (); // 通过token判断是否登录
		
		if (C ( 'ENABLE_ACCESSCODE' ) && ! (isset ( $_SESSION ['AccessCode'] ) && $_SESSION ['AccessCode'] == C ( 'ACCESSCODE' ))) {
			redirect ( WEBROOT_PATH . '/index.php/access' );
		}
		
		$this->assign ( 'pubtext', L ( 'pubtext' ) ); // 赋值公共的文字，包括header和footer
		                                              
		// 当前url地址
		$enurl = pub_encode_pass ( get_url (), "url", $type = "encode" );
		$this->assign ( 'enurl', $enurl );
		
		// header上的分类信息 begin
		/*
		 * $CM = new CatesModel (); $main_cate = $CM->getCateList ( 0, 0, false,
		 * 0, true, 'c.pc_type=0 ' ); $main_cateinfo = $this->unlimitedForLayer
		 * ( $main_cate ); $this->assign ( 'main_cateinfo', $main_cateinfo );
		 */
		
		$CatePicker = new CategoryPickerModel ();
		$cateList = $CatePicker->getChildList ( '1263' );
		// var_dump($cateList);
		$this->assign ( 'cateList', $cateList );

        $TDK['tdk_title']='IGNITE-IGNITE官网:3D打印首饰,DIY方案,商务礼品定制|珠宝首饰|个性化定制购物体验平台';
        $TDK['tdk_keywords']='3D打印,首饰定制,商务礼品定制,DIY方案,个性化,礼物,设计师,金属打印,创意,3Dprinting';
        $TDK['tdk_description']='IGNITE是国内领先3D打印首饰个性化定制平台,基于3D图形技术,用户创造、分享的全新购物体验.配有优质设计师资源,您的购买的每一件产品根据要求量身定制,独一无二.';
		$this->assign ( 'TDK', $TDK );

		$CMP = M ();
		$sql_p = "select pc_id,pc_name,pc_count from tdf_product_cate where pc_type=1";
		$print_cate = $CMP->query ( $sql_p );
		$this->assign ( 'print_cate', $print_cate );
		// header上的分类信息 end
		
		// 浏览器判断
		$agentBrowser = get_user_browser();
	}
	function unlimitedForLayer($cate, $name = 'child', $pid = 1) {
		$arr = array ();
		foreach ( $cate as $key => $v ) {
			if ($v ['pc_parentid'] == $pid) {
				$v [$name] = $this->unlimitedForLayer ( $cate, $name, $v ['pc_id'] );
				$arr [] = $v;
			}
		}
		return $arr;
	}
	
	/**
	 * 判断登录
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function _isLogin() {
		// print_r($_COOKIE);
		// exit;
		if ($this->_session ( 'f_userid' ) && $this->_session ( 'f_logindate' )) {
			return true;
		} elseif (isset ( $_COOKIE ['b_m0'] ) && (strlen ( $_COOKIE ['b_m0'] ) > 0)) {
			$cookieVal = $_COOKIE ['b_m0'];
			$cookieArr = explode ( '|', $cookieVal );
			if (count ( $cookieArr ) == 3) {
				$encodeIdentifier = $cookieArr [0];
				$chaosUID = $cookieArr [1];
				$loginToken = $cookieArr [2];
				// 查询用户永久登录信息
				$Users = new UsersModel ();
				$condition ['u_id'] = $Users->decodeUIDChaos ( $chaosUID );
				$res = $Users->find ( $condition ['u_id'] );
				if ($res) {
					$now = time ();
					// 两个判断一个过期时间一个token值
					if (($Users->u_token == $loginToken) && ($now < $Users->u_timeout)) {
						// 重新写入cookie
						// 判断用户是否生成了identifier
						if (! $Users->u_identifier) {
							$identifier = $Users->helper->genIdentifier ();
							$Users->u_identifier = $identifier;
						} else {
							$identifier = $Users->u_identifier;
						}
						// 生成一个Token值和Timeout值
						$loginToken = $Users->helper->genLoginToken ();
						$Users->u_token = $loginToken;
						// 设置Cookie
						$cookieVal = $Users->helper->encodeIdentifier ( $identifier ) . '|' . $Users->helper->genUIDChaos () . '|' . $loginToken;
						cookie ( 'b_m0', null );
						cookie ( 'b_m0', $cookieVal, ($Users->u_timeout - $now) );
						// 设置session
						session ( 'f_userid', $Users->u_id );
						session ( 'f_nickname', $Users->u_dispname );
						session ( 'f_logindate', $now );
						// 更新登录信息
						$Users->u_lastlogin = get_now ();
						$Users->u_lastip = get_client_ip ();
						$Users->save ();
						$LULM = new LogUserLoginModel ();
						$LULM->addLog ( session ( 'f_userid' ) );
						// 任务系统
						$HJ = new HookJobsModel ();
						$HJ->run ( session ( 'f_userid' ), __METHOD__ );
						return true;
					}
				}
			}
		} else {
			return false;
		}
		return false;
	}
	
	/**
	 * 渲染页面前需要获得登录用户的基本信息
	 *
	 * @access protected
	 * @return null
	 */
	protected function _assignLoginInfo() {
		if ($this->_isLogin ()) {
			$Users = new UsersModel ();
			$Users->find ( $_SESSION ['f_userid'] );
			$UA = $Users->getUserAcc ();
			
			// ASSIGN
			$this->assign ( 'isLogin', 1 );
			$this->assign ( 'userAcc', $UA->u_vcoin_av );
			$this->assign ( 'userAcc_rmb', $UA->u_rcoin_av . " " . L ( 'ACCOUNT_UNIT' ) );

            /*zhangzhibin added @2015.05.19			 */
            $userBasic=$Users->getShowUserName($Users->data());

			/* miaomin added @2014.3.26 */
			$this->assign ( 'userBasic', $userBasic);
			$this->assign ( 'u', $this->_session () );
			
			// 购物车信息
			$UCM = new UserCartModel ();
			$ucRes = $UCM->getCartItemList ();
			//dump($ucRes);
			/*
			foreach ( $ucRes ['list'] as $key => $val ) {
				if ($val ['cartitem'] ['propspec']) {
					$propSpecArr = explode ( '<br>', $val ['cartitem'] ['propspec'] );
					$ucRes ['list'] [$key] ['cartitem'] ['propspec'] = $propSpecArr [0];
				}
			}
			*/
			$this->assign ( 'cartlist', $ucRes ['list'] );
			
			// 购物车数量
			// $cartNum = $UCM->getCartNum ( $this->_session ( 'f_userid' ) );
			$this->assign ( 'cartnum', count ( $ucRes ['list'] ) );
			
			// 未支付订单数量
			$UPM = new UserPrepaidModel ();
			$condition = array (
					$UPM->F->UserID => $_SESSION ['f_userid'],
					$UPM->F->Status => 0,
					$UPM->F->Delsign => 0 
			);
			$userordernum = $UPM->getPrepaidListNumByCondition ( $condition );
			$this->assign ( 'unpayordernum', $userordernum );
			
			// 未使用优惠券数量
			$UCPM = new CouponModel ();
			$unUseCouponNum = $UCPM->getUnuseNum ( $_SESSION ['f_userid'] );
			$this->assign ( 'unusecouponnum', $unUseCouponNum );
			
			// 专属定制
			$UCUM = new UserCustomizeModel ();
			$ucumRes = $UCUM->getCustomizeList ( 'tdf_users.u_id = "' . $_SESSION ['f_userid'] . '"' );
			$this->assign ( 'customizenum', count ( $ucumRes ) );
			
			// 登录判断
			$this->assign ( 'isLogin', 1 );
		}
	}
	
	/**
	 * 渲染页面
	 *
	 * edited by miaomin @2014.12.9
	 *
	 * 添加了3个参数可以指定渲染的模板
	 *
	 * @access protected
	 *        
	 * @param string $templateFile
	 *        	指定要调用的模板文件,默认为空 由系统自动定位模板文件.
	 * @param string $charset
	 *        	输出编码
	 * @param string $contentType
	 *        	输出类型
	 *        	
	 * @return null
	 */
	protected function _renderPage($templateFile = '', $charset = '', $contentType = '') {

        $this->_assignLoginInfo ();
		
		// 设计师数量以及产品总数
		$UM = new UsersModel ();
		$this->assign ( 'totalDesignerNum', $UM->getTotalDesignerNum () );
		
		$PM = new ProductModel ();
		$this->assign ( 'totalProductNum', $PM->getTotalProductNum () );
		
		// 浏览器判断
		if (is_weixin ()) {
			$mobileAgent = 1;
		} elseif (is_mobile ()) {
			$mobileAgent = 2;
		} else {
			$mobileAgent = 3;
		}


		$this->assign ( 'mobileAgent', $mobileAgent );
		
		$this->display ( $templateFile = '', $charset = '', $contentType = '' );
	}
	
	/**
	 * 跳转至登录界面
	 *
	 * @access protected
	 * @param string $loginUrl        	
	 * @return null
	 */
	protected function _needLogin($loginUrl = '') {
		if ($this->_get ( 'reqtype' ) == 'ajax') {
			$result ['isSuccess'] = false;
			$result ['Reason'] = '0001'; // 需要登录
			$result ['fromUrl'] = $_SERVER ['HTTP_REFERER'];
			echo json_encode ( $result );
			exit ();
		} else {
			// TODO
			// 跳转路径以后都可以整理入配置文件
			// 判断是否是微信浏览器
			if (is_weixin ()) {
				// 微信登录
				$launchUrl = WX_CALLBACK_DOMAIN . '/index/auth-wx_launch?jump_uri=' . __SELF__;
				redirect ( $launchUrl );
			} elseif (is_mobile ()) {
				// 手机登录
				redirect ( WEBROOT_PATH . '/user.php/login/?mmode=1&from_url=' . __SELF__ );
			} else {
				// PC端\PAD端登录
				redirect ( WEBROOT_PATH . '/user.php/login/?from_url=' . __SELF__ );
			}
		}
	}
	
	/**
	 * 判断口令
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function _isToken() {
		if (isset ( $_GET ['uid'] ) && isset ( $_GET ['token'] )) {
			// 开始验证
			$uid = ( int ) $_GET ['uid'];
			$token = ( string ) $_GET ['token'];
			
			$UT = new UserTokenModel ();
			$utRes = $UT->where ( "u_id='" . $uid . "' AND ut_token='" . $token . "'" )->find ();
			
			if (($utRes) && (time () <= $utRes ['ut_expire'])) {
				
				// 注销所有的SESSION
				session ( 'f_userid', null );
				session ( 'f_useremail', null );
				session ( 'f_avatar', null );
				session ( 'f_nickname', null );
				session ( 'f_logindate', null );
				// 登录成功写入SESSION
				$User = new UsersModel ();
				$UserData = $User->getUserByID ( $uid );
				session ( 'f_userid', $UserData ['u_id'] );
				// session ( 'f_useremail', $UserData ['u_email'] );
				// session ( 'f_avatar', $UserData ['u_avatar'] );
				session ( 'f_nickname', $UserData ['u_dispname'] );
				session ( 'f_logindate', time () );
				// var_dump($_SESSION);
				// exit;
				// 删除TOKEN
				$UT->delete ( $utRes ['u_id'] );
				
				return true;
			}
		}
		return false;
	}
}
?>