<?php
/**
 * 第三方认证回调
 * @author zhangzhibin 
 * 2014-08-05
 */
class AuthAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * QQ登录授权后的回调地址
	 */
	public function authreg() {
		
		// echo "<a href='" . __APP__ . "/auth-wx_launch'>WX Launch</a>";
		echo "<a href='" . WX_CALLBACK_DOMAIN . "/index/wx-jewelrylist.html'>WX Jewelry</a>";
	}
	
	/**
	 * 微信
	 *
	 * 入口访问URL
	 */
	public function wx_launch() {

        
		// 加载Wxsdk操作类
		import ( 'Common.Wxsdk', APP_PATH, '.php' );
		
		// 获取referer url
		// $referer_url = $_SERVER ['HTTP_REFERER'];
		
		// 获取跳转url
		$jump_url = $_GET ['jump_uri'];
		
		if ($jump_url) {
			$redirect_uri = WX_CALLBACK_DOMAIN . "/index/auth-wx_oauth?from_uri=" . $jump_url;
		} else {
			$redirect_uri = WX_CALLBACK_DOMAIN . "/index/auth-wx_oauth";
		}
		
		//
		$WX = new Wxsdk ();
		$wxAuthUrl = $WX->genWxAuthorizeUri ( $redirect_uri, 'snsapi_userinfo' );
		
		redirect ( $wxAuthUrl );
	}
	
	/**
	 * 微信
	 *
	 * 网页授权获取用户基本信息
	 *
	 * @author miaomin
	 */
	public function wx_oauth() {

		// 加载Wxsdk操作类
		import ( 'Common.Wxsdk', APP_PATH, '.php' );
		
		// 当前的绑定类型,userinfo或者base
		// <----- 高能注意:目前必须要拿userinfo ----->
		$currentBindType = 'userinfo';
		
		// 微信网页授权获取用户基本信息
		try {
			
			// 出错异常处理
			if (! ($_GET ['code']) || ! ($_GET ['state'])) {
				throw new Exception ( '无法从微信开放平台获取正确的数据,请稍后再试' );
			}
			
			if ($_GET ['entry']) {
				$WX = new Wxsdk ( $_GET ['entry'] );
			} else {
				$WX = new Wxsdk ();
			}
			
			// 获取access_token
			$wxRes = $WX->wx_getaccesstoken ( $_GET ['code'] );
			$wxResArr = $WX->handleParseWxresBody ( $wxRes );

            // 如果是userinfo绑定模式从微信拉取用户信息
			if ($currentBindType == 'userinfo') {
				$wxRes = $WX->wx_getuserinfo ( $wxResArr ['access_token'], $wxResArr ['openid'] );
				$wxResArr = $WX->handleParseWxresBody ( $wxRes );
				session_start();
				$_SEESION['nickname'] = $wxResArr['nickname'];
				$_SEESION['headimgurl'] = $wxResArr['headimgurl'];
			}

           /* //远程发送数据到xfx---start-------
            header("Content-Type:text/html; charset=utf-8");
            $openid=$wxResArr['openid'];
            $avatar=base64_encode($wxResArr['headimgurl']);
            $dispname=$wxResArr['nickname'];
            $ch = curl_init("http://140.207.154.14/xfx/index.php/index/getdatas/openid/".$openid."/avatar/".$avatar."/dispname/".$dispname."") ;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
            curl_exec($ch) ;
            //远程发送数据到xfx---end-------*/


			// 需要在本地DB更新相关数据
			$UAM = new UserAuthModel ();
			$authRes = $UAM->getOpenId ( $wxResArr ['unionid'], 2 );
			if ($authRes === null) {
				// 查询结果为空,补齐绑定数据
				$wxResArr ['bindtype'] = $currentBindType;
				$wxResArr ['from'] = 6;
				$wxResArr ['authtype'] = 2;
				$uid = $UAM->bindByWXOpenId ( $wxResArr ['unionid'], $wxResArr );
			} else {
				$uid = $authRes ['u_id'];// 已绑定
				$UAM->updateByWXOpenId($wxResArr ['unionid'], $wxResArr);
			}



			// 自动登录
			if ($uid) {
				$UM = new UsersModel ();
				$autologinRes = $UM->autoLogin ( $uid, 'wx' );
				
				if (! $autologinRes) {
					throw new Exception ( '绑定失败,请稍后再试' );
				}
				
				// 跳转
				($_GET ['from_uri']) ? $jump_url = $_GET ['from_uri'] : $jump_url = __APP__ . '/index';
				
				redirect ( $jump_url );
				
				/**
				 * miaomin edited@2015.8.27
				 * 
				 * 关闭手机绑定功能
				 */
				//判断微信授权后是否绑定手机和设置了默认密码				
				/********************************************
				if(M('users')->getFieldByU_id(session('f_userid'),'u_mob_no') != NULL){
				    $U = new UsersModel();
				    $arr = $U->getByU_id(session('f_userid'));
				    if($arr['u_pass'] == md5('3dcity2014'.$arr['u_salt'])){
				        session('smsbind_urlfrom',$jump_url);
				        redirect('/user.php/sms/initpwd');				    
				    }
				    else{
				        redirect ( $jump_url );
				    }
				    
				}
				else{
				    session('smsbind_urlfrom',$jump_url);
				    redirect('/user.php/sms/index');
				}
				**************************************************/
			}
		}catch ( Exception $e ) {
			$this->error ( $e->getMessage (), '__APP__/index' );
		}
	}
	
	/*
	 * 测试QQ登录
	 */
	public function qqlogin() {
		$currenturl = pub_encode_pass ( urlencode ( get_url () ), "3dcity", "encode" );
		$this->assign ( "currenturl", $currenturl );
		$this->display ();
	}
	
	// 登录地址
	public function login_qq($type = null) {
		empty ( $type ) && $this->error ( '参数错误' );
		// 加载ThinkOauth类并实例化一个对象
		import ( "ORG.ThinkSDK.ThinkOauth" );
		$sns = ThinkOauth::getInstance ( $type );
		$url = $_GET ['url'];
		$callback = "http://testqq.3dcity.com/city/index/auth-callback-type-qq-url-" . $url;
		
		// $sns->getCallback($url);
		// 跳转到授权页面
		redirect ( $sns->getRequestCodeURL ( $callback ) );
	}
	
	// 授权回调地址
	public function callback($type = null, $code = null) {
		$url = $_GET ['url'];
		
		// $tempurl==pub_encode_pass($url,"3dcity","decode");
		$callback = "http://testqq.3dcity.com/city/index/auth-callback-type-qq-url-" . $url;
		(empty ( $type ) || empty ( $code )) && $this->error ( '参数错误' );
		
		// 加载ThinkOauth类并实例化一个对象
		import ( "ORG.ThinkSDK.ThinkOauth" );
		$sns = ThinkOauth::getInstance ( $type );
		
		// 腾讯微博需传递的额外参数
		$extend = null;
		if ($type == 'tencent') {
			$extend = array (
					'openid' => $this->_get ( 'openid' ),
					'openkey' => $this->_get ( 'openkey' ) 
			);
		}
		
		// 请妥善保管这里获取到的Token信息，方便以后API调用
		// 调用方法，实例化SDK对象的时候直接作为构造函数的第二个参数传入
		// 如： $qq = ThinkOauth::getInstance('qq', $token);
		$token = $sns->getAccessToken ( $code, $extend, $callback );
		$qq = ThinkOauth::getInstance ( 'qq', $token ); // 实例化腾讯QQ开放平台对象 $token
		                                                // 参数为授权成功后获取到的 $token
		$qquserinfo = $qq->call ( 'user/get_user_info' ); // 调用接口，获取QQ用户信息(昵称)
		$UA = new UserAuthModel ();
		$uid = $UA->bindbyOpenId ( $token, $qquserinfo );
		
		$UM = new UsersModel ();
		$userinfo = $UM->getUserByID ( $uid );
		
		if ($userinfo) {
			session ( 'f_userid', $UM->u_id );
			session ( 'f_nickname', $UM->u_dispname );
			session ( 'f_logindate', time () );
			session ( 'f_logintype', "qq" );
			$UM->u_lastlogin = get_now ();
			$UM->u_lastip = get_client_ip ();
			$UM->save ();
			$LULM = new LogUserLoginModel ();
			$LULM->addLog ( session ( 'f_userid' ) );
			// 任务系统
			$HJ = new HookJobsModel ();
			$HJ->run ( $uid, "LoginAction::index" );
			$reurl = pub_encode_pass ( $url, "3dcity", "decode" );
			redirect ( urldecode ( $reurl ) );
		} else {
			$this->error ( "很抱歉,登录失败..." );
		}
	}
	private function login_web($userinfo) {
		$Users = new UsersModel ();
		session ( 'f_userid', $userinfo ['u_id'] );
		session ( 'f_nickname', $userinfo ['u_id'] );
		session ( 'f_logindate', time () );
		$Users->u_lastlogin = get_now ();
		$Users->u_lastip = get_client_ip ();
		$Users->save ();
		$LULM = new LogUserLoginModel ();
		$LULM->addLog ( session ( 'f_userid' ) );
		// 任务系统
		$HJ = new HookJobsModel ();
		$HJ->run ( $userinfo ['u_id'], __METHOD__ );
		return $userinfo ['u_id'];
	}
}