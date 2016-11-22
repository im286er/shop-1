<?php
class LoginAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		//
		parent::__construct ();
	}
	
	/**
	 * 设计之都服务平台单点登录测试
	 *
	 * @author miaomin
	 * @throws Exception
	 */
	public function sso() {
		load ( "@.DBF" );
	
		// print_r ( $_REQUEST );
	
		// 配置信息
		$CFG_REMOTE_HOST = "http://sdsp.creativecity.sh.cn/";
		$CFG_LOCAL_HOST = "http://www.3dcity.com/";
		// $CFG_LOCAL_CHECKLOGIN = "http://127.0.0.1/checklogin.html";
		$CFG_LOCAL_CHECKLOGIN = array (
				0 => "http://www.3dcity.com/checklogin.html",
				1 => "http://www.3dcity.com/checklogin.html",
				2 => "http://www.3dcity.com/checklogin2.html"
		);
	
		// CURL调用地址
		$parameters .= '?token=' . urlencode ( $_REQUEST ['token'] ) . '&sessionid=' . $_REQUEST ['sessionid'] . '&userid=' . $_REQUEST ['userid'];
		$verify_url = $CFG_REMOTE_HOST . "ccs/verify_token" . $parameters;
	
		// 验证并登陆成功后跳转的URL
		/*
		 * $login_succ_url = $CFG_LOCAL_HOST; $login_succ_url2 = $CFG_LOCAL_HOST
		* . "index.php/models/add";
		*/
		$login_succ_url = array (
				0 => $CFG_LOCAL_HOST,
				1 => $CFG_LOCAL_HOST,
				2 => $CFG_LOCAL_HOST . "index.php/models/add"
		);
	
		// 设计之都服务平台登陆URL
		$remote_login_url = $CFG_REMOTE_HOST . "ccs/login.jsp";
	
		// CURL
		$ch = curl_init ();
		curl_setopt_array ( $ch, array (
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $verify_url
		) );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
	
		// var_dump ( $response );
		// var_dump ( $_REQUEST );
		// die ();
		// $response = 'ERROR';
		if ($response === 'OK') {
			// 验证成功
			// 如果前缀要修改一定要记得注册方法里面相应的验证也要改
			$local_username = 'sdcp_' . $_REQUEST ['userid'];
				
			$Users = D ( 'Users' );
			$res = $Users->getUserByName ( $local_username );
				
			if (is_array ( $res ) && $res ['u_source'] == 1) {
	
				// 该用户已存在
				$condition ['u_name'] = $local_username;
				$condition ['u_source'] = 1;
				// 更新登录信息
				$data ['u_lastlogin'] = get_now ();
				$data ['u_lastip'] = get_client_ip ();
				$Users->where ( $condition )->save ( $data );
	
				session ( 'f_userid', $res ['u_id'] );
				session ( 'f_username', $res ['u_name'] );
				session ( 'f_nicename', $res ['u_dispname'] );
				session ( 'f_realname', $res ['u_realname'] );
				session ( 'f_usertitle', $res ['u_title'] );
				session ( 'f_logindate', time () );
				// 登录成功
				$this->success ( L ( 'login_success' ), $login_succ_url [$_REQUEST ['skipType']] );
			} else {
				// 该用户不存在
				// 注册
				try {
					if ($Users->create ()) {
						// 新增用户
						$Users->u_name = $local_username;
						$Users->u_realname = $local_username;
						$Users->u_pass = md5 ( $local_username );
						$Users->u_title = L ( 'sdcp_user' );
						$Users->u_createdate = get_now ();
						$Users->u_dispname = $Users->u_name;
						$Users->u_status = 1;
						$Users->u_source = 1;
						$Users->u_email = '';
						$uid = $Users->add ();
						// 向设计之都提交注册用户数
						load ( "@.SdcpWebService" );
						$ws = new SdcpWebService ();
						$ws->sendRegUserCount ();
						// 注册成功并登陆
						// 更新登录信息
						$res = $Users->getUserByName ( $local_username );
						if (is_array ( $res ) && $res ['u_source'] == 1) {
							$condition ['u_name'] = $local_username;
							$condition ['u_source'] = 1;
								
							$data ['u_lastlogin'] = get_now ();
							$data ['u_lastip'] = get_client_ip ();
							$Users->where ( $condition )->save ( $data );
								
							session ( 'f_userid', $res ['u_id'] );
							session ( 'f_username', $res ['u_name'] );
							session ( 'f_nicename', $res ['u_dispname'] );
							session ( 'f_realname', $res ['u_realname'] );
							session ( 'f_usertitle', $res ['u_title'] );
							session ( 'f_logindate', time () );
								
							$this->success ( L ( 'login_success' ), $login_succ_url [$_REQUEST ['skipType']] );
						} else {
							throw new Exception ( 'error_tips' );
						}
					} else {
						throw new Exception ( 'error_tips' );
					}
				} catch ( Exception $e ) {
					$this->error ( L ( $e->getMessage () ), '__APP__/users/register' );
				}
			}
		} else {
			// 验证失败
			$targetUrl = urlencode ( $CFG_LOCAL_CHECKLOGIN [$_REQUEST ['skipType']] );
			$localLogin = urlencode ( $CFG_LOCAL_HOST . 'index.php/login/index' );
			$remote_login_url .= '?target=' . $targetUrl . '&localLogin=' . $localLogin;
			$this->error ( L ( 'error_tips' ), $remote_login_url );
		}
	}
	
	/**
	 * 登录
	 */
	public function index() {
		$this->assign("currenturl",pub_encode_pass(urlencode(get_url()),"3dcity","encode")); //赋值当前url到模板
		try {
			// 判断有登录出错的SESSION吗？如果有则需要显示验证码
			if ($this->isPost ()) {
				if ($this->_post ( 'email' ) == "请输入电子邮箱地址") {
					$this->error ( "请输入邮箱" );
				}
				if (strtolower ( $this->_post ( 'verification' ) ) !== strtolower ( $this->_session ( 'php_captcha' ) )) {
					return $this->error ( '验证码错误！' );
				}
				
				// 引用路径
				$from_url = $this->_post ( 'from_url' );
				// 登录成功后的跳转路径
				($from_url) ? $jump_url = $from_url : $jump_url = __APP__;
				// 用户名密码判断
				$condition ['u_email'] = $this->_post ( 'email' );
				$Users = new UsersModel ();
				$res = $Users->where ( $condition )->find ();
				if ($res) {
					$uid = $Users->u_id;
					
					$md5pass = md5 ( $this->_post ( 'password' ) . $Users->u_salt );
					//$md5pass_md5 = md5 ( md5($this->_post ( 'password' )) . $Users->u_salt );
					if ($Users->u_pass === $md5pass){//新增密码验证规则，需统一为md5(md5(password).盐值)
						// 用户名密码验证成功
						
						
						if($Users->u_type==3){ //如果u_type=3禁止登录跳转到404页面
							header("HTTP/1.0 404 Not Found");
							$this->display('Public:404');
							exit();
						}
						
						if ($Users->u_mail_verify == 0) {
							$temail = urlencode ( $this->_post ( 'email' ) );
							$temail = pub_encode_pass ( $temail, "10000", "encode" );
							$this->redirect ( "/login/verifymail/email/" . $temail );
							exit ();
						}
						// 如果用户勾选记住密码选项
						if (isset ( $_POST ['rememberme'] ) && ($_POST ['rememberme'] == 1)) {
							// 判断用户是否生成了identifier
							if (! $Users->u_identifier) {
								$identifier = $Users->helper->genIdentifier ();
								$Users->u_identifier = $identifier;
							} else {
								$identifier = $Users->u_identifier;
							}
							// 生成一个Token值和Timeout值
							$loginToken = $Users->helper->genLoginToken ();
							$loginTimeout = genWeekTimeout ();
							$Users->u_token = $loginToken;
							$Users->u_timeout = $loginTimeout;
							//设置Cookie
							$cookieVal = $Users->helper->encodeIdentifier ( $identifier ) . '|' . $Users->helper->genUIDChaos () . '|' . $loginToken;
							cookie ( 'b_m0', $cookieVal, genWeekTime () );
						}
						
						
						
						session ( 'f_userid', $Users->u_id );
						session ( 'f_nickname', $Users->u_dispname );
						session ( 'f_logindate', time () );
						// 更新登录信息
							//-------------------------更新新的验证方式  start
							//if($Users->u_pass === $md5pass){ //如果密码为旧的验证密码
							//	$Users->u_pass = $md5pass_md5; //更新密码为新的验证密码
							//}
							//-------------------------更新新的验证方式  end
						$Users->u_lastlogin = get_now ();
						$Users->u_lastip = get_client_ip ();
						$Users->save ();
						$LULM = new LogUserLoginModel ();
						$LULM->addLog ( session ( 'f_userid' ));
						// 任务系统
						$HJ = new HookJobsModel ();
						$HJ->run ( $uid, __METHOD__ );
						redirect ( $jump_url );
					}
				}
				// 用户名不存在或者密码错误
				$this->error ( L ( 'username_not_exist_or_password_error' ) );
				if (strtolower ( $this->_post ( 'verification' ) ) !== strtolower ( $this->_session ( 'php_captcha' ) )) {
					throw new Exception ( L ( 'verification_error' ) );
				}
				// throw new Exception ( L (
				// 'username_not_exist_or_password_error' ) );
				// $this->ajaxReturn('','用户名或密码不能为空！',0);
			} else {
				$from_url = urldecode ( $this->_get ( 'from_url' ) );
				$this->assign ( 'from_url', $from_url );
				$this->assign ( 'random', time () );
				$this->_renderPage ();
			}
		} catch ( Exception $e ) {
			// 异常处理
			$this->error ( $e->getMessage (), __APP__ . '/login' );
		}
	}
	public function verifymail() {
		$email = I ( 'get.email', '0', 'string' );
		$showemail = pub_encode_pass ( $email, "10000", "decode" );
		$showemail = str_replace ( "%40", "@", $showemail );
		
		// urlencode($email);
		$this->assign ( "email", $email );
		$this->assign ( "showemail", $showemail );
		$this->display ();
	}
	public function checkemail() { // 验证用户邮箱是否存在
		$Checkemail = I ( "get.email", "0", "intval" );
		;
		$TU = new UsersModel ();
		$userinfo = $TU->getUserByEMail ( $Checkemail );
		if ($userinfo) {
			echo "aa";
		} else {
			echo $Checkemail;
		}
	}
}
?>