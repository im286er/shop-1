<?php
/**
 * User分组使用的CommonAction类
 *
 * @author miaomin 
 * Jul 2, 2013 6:15:11 PM
 */
class CommonAction extends Action {

	/**
	 * 登录用户信息
	 *
	 * @var UsersModel
	 */
	protected $_loginUserObj;
	
	/**
	 * 构造函數
	 */
	public function __construct() {
		parent::__construct ();
		$this->DBF = new DBF ();
		/**
		 * miaomin added@2014.7.7
		 *
		 * swfupload需要正常在Firefox下工作必须做如下设定
		 */
		if (isset ( $_POST ["sessionid"] )) {
			session_id ( $_POST ['sessionid'] );
		}
		session_start ();
		
		if (C ( 'ENABLE_ACCESSCODE' ) && ! (isset ( $_SESSION ['AccessCode'] ) && $_SESSION ['AccessCode'] == C ( 'ACCESSCODE' ))) {
			redirect ( WEBROOT_PATH . '/index.php/access' );
		}
		$this->assign ( 'pubtext', L ( 'pubtext' ) ); // 赋值公共的文字，包括header和footer
		                                              
		// header上的分类信息 begin
		$CM = new CatesModel ();
		$main_cate = $CM->getCateList ( 0, 0, false, 0, true, 'c.pc_type=0 ' );
		$main_cateinfo = $this->unlimitedForLayer ( $main_cate );
		$this->assign ( 'main_cateinfo', $main_cateinfo );
		
		$CMP = M ();
		$sql_p = "select pc_id,pc_name,pc_count from tdf_product_cate where pc_type=1";
		$print_cate = $CMP->query ( $sql_p );
		$this->assign ( 'print_cate', $print_cate );
		$indexCode=pub_encode_pass(WEBROOT_URL,'url');
		$this->assign('index_code',$indexCode);
		// header上的分类信息 end
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
	 * 初始化登录用户信息
	 */
	protected function _initLoginUserObj() {
		$this->_loginUserObj = new UsersModel ();
		$res = $this->_loginUserObj->find ( $this->_session ( 'f_userid' ) );
		if ($res) {
			return true;
		}
		
		return false;
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
		if ($this->_session ( 'f_userid' ) && $this->_session ( 'f_logindate' ) && $this->_initLoginUserObj ()) {
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
						// 初始化登录用户对象
						$this->_initLoginUserObj ();
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
		echo header("Content-Type:text/html; charset=utf-8");
		if ($this->_isLogin ()) {
			$Users = new UsersModel ();
			$Users->find ( $_SESSION ['f_userid'] );
			$UA = $Users->getUserAcc ();
			$UP = $Users->getUserProfile();

			// ASSIGN
			$this->assign ( 'isLogin', 1 );
			$this->assign ( 'userAcc', $UA->u_vcoin_av );
			$this->assign ( 'userAcc_rmb', $UA->u_rcoin_av . " " . L ( 'ACCOUNT_UNIT' ) );

			/* miaomin added @2015.8.26 */
			$userBasic=$Users->getShowUserName($Users->data());

			/* miaomin edited @2015.8.26 */
			$this->assign ( 'userBasic', $userBasic );
			$this->assign ( 'userPro', $UP->data());

			$this->assign ( 'u', $this->_session () );
			/* miaomin added @2014.12.24 */
			
			// 购物车信息
			$UCM = new UserCartModel ();
			$ucRes = $UCM->getCartItemList ();
			$this->assign ( 'isexpress', $ucRes ['isexpress'] );
			$this->assign ( 'ProductList', $ucRes ['list'] );
			//dump($ucRes ['list'] );

			$this->assign ( 'TotalPrice', $ucRes ['totalprice'] );
			$this->assign ( 'cartlist', $ucRes ['list'] );
			$this->assign ( 'cartnum', count ( $ucRes ['list'] ) );
			
			// 未支付订单数量
			$UPM = new UserPrepaidModel ();
			/*
			$condition = array (
					$UPM->F->UserID => $_SESSION ['f_userid'],
					$UPM->F->Status => 0,
					$UPM->F->Delsign => 0 
			);
			$userordernum = $UPM->getPrepaidListNumByCondition ( $condition );
			*/
			$MonthSelet = 0;
			$strnowtime = strtotime ( "-90 days" );
			$condition = "up_uid=" . $_SESSION ['f_userid'] . " and ";
			$condition .= ($MonthSelet == 0) ? "unix_timestamp(up_dealdate) > $strnowtime" : "unix_timestamp(up_dealdate) < $strnowtime";
			$condition .= ' and up_status=0'; // 附加订单状态条件
			$userordernum = $UPM->where ( $condition . ' and delsign="0"' )->count (); // 查询满足要求的总记录数
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
        $this->assign ( 'showtitle', 'IGNITE官网:3D打印首饰,DIY方案,商务礼品定制|珠宝首饰|个性化定制购物体验平台' );
        $this->assign ( 'keywords', '3D打印,首饰定制,商务礼品定制,DIY方案,个性化,礼物,设计师,金属打印,创意,3Dprinting' );
        $this->assign ( 'description', 'IGNITE 国内领先3D打印首饰个性化定制平台,基于3D图形技术,用户创造、分享的全新购物体验.配有优质设计师资源,您的购买的每一件产品根据要求量身定制,独一无二.' );

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
		session('mobileAgent',$mobileAgent);
		$this->assign ( 'mobileAgent', $mobileAgent );
		$this->assign ( 'DBF_P', $this->DBF->Product );

		$this->display ( $templateFile, $charset, $contentType );
	}
	
	/**
	 * 跳转至登录界面
	 *
	 * @access protected
	 * @return null
	 */
	protected function _needLogin() {

		// TODO
		// 跳转路径以后都可以整理入配置文件
		if ($this->_get ( 'reqtype' ) == 'ajax') {
			$result ['isSuccess'] = false;
			$result ['Reason'] = '0001'; // 需要登录
			echo json_encode ( $result );
			exit ();
		} else {

			// 判断是否是微信浏览器
			if (is_weixin ()) {
				// 微信登录
				$launchUrl = WX_CALLBACK_DOMAIN . '/index/auth-wx_launch?jump_uri=' . __SELF__;
				redirect ( $launchUrl );
			} elseif (is_mobile ()) {
				// 手机登录
				$JUMP_URL = 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
				//判断手机版版本
				preg_match('/type\/iwx/',$JUMP_URL,$match);
				if(!empty($match)){
					redirect ( __APP__ . '/login/?mmode=1&type=iwx&from_url=' . $JUMP_URL );
				}else{
					redirect ( __APP__ . '/login/?mmode=1&from_url=' . $JUMP_URL );
				}
			} else {
				// PC端\PAD端登录
				$JUMP_URL = 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
				redirect ( __APP__ . '/login/?from_url=' . $JUMP_URL );
			}
		}
	}
	protected function displayError($Error, $Key = 'ErrInfo') {
		$this->assign ( $Key, $Error );
		$this->display ();
	}
}
?>