<?php
/**
 * 用户相关API
 *
 * @author miaomin 
 * Oct 15, 2013 11:11:56 AM
 *
 * $Id: UsersAction.class.php 1148 2013-12-20 07:32:44Z miaomiao $
 */
class UsersAction extends CommonAction {
	
	// TODO
	// 魔术方法
	public function __call($name, $arguments) {
		throw new Exception ( $this->RES_CODE_TYPE ['METHOD_ERR'] );
	}

	/**
	 * 获取用户信息
	 */
	public function getuserinfo() {
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		/*
		 * pr ( $args ); exit ();
		 */
		// 处理用户名和密码信息
		$visa = base64_decode ( $args ['visa'] );
		if ($visa) {
			$logindata = explode ( ' ', $visa );
			if ((is_array ( $logindata )) && (count ( $logindata ) == 2)) {
				$Users = new UsersModel ();
				$res = $Users->getUserInfo ( $logindata [0], $logindata [1] );
				return $res;
			}
		}
		return false;
	}
	
	/**
	 * 注册
	 */
	public function register() {
		// 返回结果
		$res = array ();
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
        //var_dump($args);
		// 解析用户信息
        $regdata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $regdata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		}
        // 处理手机号和密码、手机验证码信息
		$reginfo ['mobno'] = $regdata [0];
		$reginfo ['pass'] = $regdata [1];
        $datas=json_decode(base64_decode($args['datas']),true);
        $reginfo ['verifycode']=$datas['verifycode'];
        $reginfo ['apptype']   =$datas['apptype'];

		//import ( "App.Action.User.MailValidateAction" );
        load ( '@.Reginer' );
		$reginer = new Reginer ();
        if (! $args ['userfrom']) {
			$reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
		} else {
			if (array_key_exists ( $args ['userfrom'], $this->REQUEST_FROM_TYPE )) {
				$reginer->ReqestFrom = $this->REQUEST_FROM_TYPE [$args ['userfrom']];
			} else {
				$reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['UNKNOWN'];
			}
		}

        if($reginfo ['apptype']){//注册来源。5为安卓
            $reginfo ['from'] = 5; //安卓为5
        }else{
            $reginfo ['from'] = 4;
        }

        // 开始注册
		$regRes = $reginer->Register ( $reginfo );
       // print_r($reginfo );

		if (! $regRes) {
			throw new Exception ($reginer->ErrorCode);
		} else {

            $res[] = $regRes;
		}
		// 返回结果
		return $res;
	}
	
	/**
	 * 获取客户端更新信息
	 */
	public function getversion() {
		// 返回结果
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		
		// if (! $logindata) {
		// throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		// }
		// 处理用户名和密码信息
		$logininfo ['email'] = $logindata [0];
		$logininfo ['pass'] = $logindata [1];
		$logininfo ['from'] = $this->REQUEST_FROM_TYPE ['CLIENT'];
		
		// 用户名密码验证
		// load ( '@.Reginer' );
		// $reginer = new Reginer ();
		// $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['CLIENT'];
		
		// $loginRes = $reginer->Login ( $logininfo );
		
		// if (! $loginRes) {
		// throw new Exception ( $reginer->ErrorCode );
		// }
		
		// 验证成功
		// $Users = new UsersModel ();
		// $userRes = $Users->getUserInfo ( $logindata [0], $logindata [1] );
		// var_dump($logindata [1] );
		
		// if (! $userRes) { //去掉验证用户名和密码
		// throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
		// }
		
		// 获取客户端版本更新信息
		$client_version = $args ['client_version'];
		
		$CVM = new ClientVersionModel ();
		$res [] = $CVM->needUpdate ( $client_version );
		// var_dump($res);
		// exit;
		return $res;
	}
	
	/**
	 * 请求登录口令
	 */
	public function gettoken() {
		// 返回结果
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		// exit;
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		}
		
		// 处理用户名和密码信息
		$logininfo ['email'] = $logindata [0];
		$logininfo ['pass'] = $logindata [1];
		$logininfo ['from'] = $this->REQUEST_FROM_TYPE ['CLIENT'];
		
		// 用户名密码验证
		load ( '@.Reginer' );
		$reginer = new Reginer ();
		$reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['CLIENT'];
		$loginRes = $reginer->Login ( $logininfo );
		if (! $loginRes) {
			throw new Exception ( $reginer->ErrorCode );
		}
		
		// 验证成功
		$res[] = 'It\'s nice!';
		
		return $res; 
	}
	
	/**
	 * 生成TOKEN
	 *
	 * @param int $UserID        	
	 * @return string
	 */
	private function _createToken($UserID) {
		$Salt = generate_password ( 5 );
		$md5Token = md5 ( $UserID . $Salt . time () );
		$base64Token = base64_encode ( $md5Token );
		return $base64Token;
	}
	
	/**
	 * 登录
	 */
	public function login() {
		// 返回结果
		$res = array ();
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		}
		// 处理用户名和密码信息
		$logininfo ['mobno'] = $logindata [0];
		$logininfo ['pass'] = $logindata [1]; // 原密码，未加md5
		$logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
            $logininfo ['apptype']   =$datas['apptype'];
        }
		// 登录
		load ( '@.Reginer' );
		$reginer = new Reginer ();
		$reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
		$loginRes = $reginer->Login ( $logininfo );
		if (! $loginRes) {
			throw new Exception ( $reginer->ErrorCode );
		}

		// 登录成功
		$Users = new UsersModel ();
        $res = $Users->getUserInfoByMobno ( $logindata [0], $logindata [1] );
       // var_dump($res);
        if($res){
            if($res[0]['u_avatar']){
                $res[0]['u_avatar']=WEBROOT_URL.'/upload/avatar/'.$res[0]['u_avatar'];
            }
        }
        //var_dump($res);
		// 记录登录日志
        $loginType=$logininfo['apptype']?5:4;
		$LULM = new LogUserLoginModel ();
		$LULM->addLog ( $res [0] ['u_id'],$loginType );

		if (! $res) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
		}
		return $res;
	}


    /*
     * 重置密码
     */
    public function resetpass(){
        $res = array ();//返回结果
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        //var_dump($logindata);
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $resetInfo ['mobno'] = $logindata [0];
        $resetInfo ['pass'] = $logindata [1]; // 原密码，未加md5
        $datas=json_decode(base64_decode($args['datas']),true);
        $resetInfo ['verifycode']=$datas['verifycode'];

        $UM = new UsersModel ();
        if($resetInfo ['mobno']){
            if (!$UM->isUserMobnoExist ( $resetInfo ['mobno'] )) { //如果用户手机号不存在
                throw new Exception ( $this->RES_CODE_TYPE ['MOB_NOT_EXIST'] );
                return false;
            }
        }

        //-----------判断手机验证码是否正确
        $SM = new LogSmsModel();
        if(!$SM->verGetcode($resetInfo ['mobno'],$resetInfo ['verifycode'],2)){//如果验证不通过,返回false
            throw new Exception ( $this->RES_CODE_TYPE ['VERIFY_ERR'] );
            return false;
        }
        //修改密码
        $result= $UM->updateUserPass($resetInfo ['mobno'],$resetInfo ['pass']);
        if (! $result) {
            throw new Exception ( $this->RES_CODE_TYPE ['RESET_PASS_ERR'] );
        }
        $result_arr['t']=$result;
        $res[]=$result_arr;
        return $res;
    }

	/**
	 * 找回密码
	 */
	public function findpwd() {
		// 返回结果
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		}
		
		// 判断邮件地址是否存在
		$Users = new UsersModel ();
		if (! $Users->isUserEMailExist ( $logindata [0] )) {
			throw new Exception ( $this->RES_CODE_TYPE ['USERNAME_NOT_EXIST'] );
		}
		
		// 是否可以根据Email获取用户信息
		$userinfo = array ();
		$userinfo [] = $Users->getUserByEMail ( $logindata [0] );
		// if (! $userinfo) {
		// throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
		// }
		
		// 发送邮件
		import ( "App.Action.User.MailValidateAction" );
		$mv = new MailValidateAction ();
		$res [] = $mv->sendResetPassMail ( $userinfo [0] ['u_id'], $userinfo [0] ['u_dispname'], $logindata [0] );
		
		return $res;
	}

    /*
     * 发送手机验证码接口
     */
    public function mobsendcode(){
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        $datas=json_decode(base64_decode($args['datas']),true);
        $codetype=$datas['codetype'];
        //echo $codetype;
        if (! $logindata) {
          throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }

        $sendResult=$this->sentcode($logindata[0],$codetype);

        if($sendResult['status']==702){
                throw new Exception ( $this->RES_CODE_TYPE ['MOB_EXIST'] );
            }elseif($sendResult['status']==703) {
                throw new Exception ($this->RES_CODE_TYPE ['MOB_ERR']);
            }elseif($sendResult['status']==704) {
                throw new Exception ($this->RES_CODE_TYPE ['VERIFY_ERR_LONGTIME']);
            }elseif($sendResult['status']==705) {
                throw new Exception ($this->RES_CODE_TYPE ['VERIFY_ERR_NUM']);
            }elseif($sendResult['status']==706) {
                 throw new Exception ($this->RES_CODE_TYPE ['MOB_NOT_EXIST']);
            }elseif($sendResult['status']==200){
                $tmp['status']=1;
                $res[]=$tmp;
            }
        return $res;
    }

   /* /*
    * 验证手机验证码 是否正确
    */
   /* public function verifymobcode(){
        // 返回结果
        $res = array();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
//      $res[]=$this->sentcode($logindata [0]);
        return $res;
    }*/

    /**
     * 是否注册 通过手机号判断手机号码是否注册
     */
    public function isregistermobile() {
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
       if (! $logindata) {
        	throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 判断手机号码是否存在
        $Users = new UsersModel ();
        $uinfoArr=$Users->isUserMobnoExist ( $logindata [0] );
        //var_dump($uinfoArr);
        if (!$uinfo=$Users->isUserMobnoExist ( $logindata [0] )) {
            $t['status']=0;
            $res [] = $t;
            return $res;
        }else{
            $t['status']=1;
            $res [] = $t;
        }
        /*// 是否可以根据Email获取用户信息
        $userinfo = array ();
        $userinfo [] = $Users->isUserMobnoExist ( $logindata [0] );
        if (! $userinfo) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
        }*/
        return $res;
    }

	/**
	 * 是否注册 通过email判断
	 */
	public function isregister() {
		// 返回结果
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		//$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		//if (! $logindata) {
		//	throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		//}

		// 判断邮件地址是否存在
		$Users = new UsersModel ();
		if (! $Users->isUserEMailExist ( $logindata [0] )) {
			$res [] = 0;
			return $res;
		}

		// 是否可以根据Email获取用户信息
		$userinfo = array ();
		$userinfo [] = $Users->getUserByEMail ( $logindata [0] );
		if (! $userinfo) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
		}
		
		$res [] = $userinfo [0] ['u_id'];
		
		return $res;
	}
	
	/**
	 * TEST
	 */
	public function test() {
		// 返回结果
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 写入文件
		$filename = 'D:\isutf.txt';
		$fp = fopen ( $filename, 'a' );
		fwrite ( $fp, date ( "F j, Y, g:i a" ) . ': [Args] ' );
		fwrite ( $fp, serialize ( $args ) );
		// 换行不要用单引号！！！
		fwrite ( $fp, "\r\n" );
		fclose ( $fp );
		
		return $res;
	}
	
	/**
	 * 获取3dcity的URL
	 */
	public function getcityurl() {
		$res = array ();
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 完成自动登录
		// 生成口令
		$UA = new UsersAction ();
		$token = $UA->getOneToken ( $userinfo [0] ['u_id'] );
		$UT = new UserTokenModel ();
		$utRes = $UT->find ( $userinfo [0] ['u_id'] );
		if ($utRes) {
			// 更新
			$UT->ut_token = $token;
			$UT->ut_expire = time () + 10;
			$UT->ut_lastupdate = get_now ();
			$UT->ut_ipaddress = get_client_ip ();
			$tokenRes = $UT->save ();
		} else {
			// 生成
			$UT = new UserTokenModel ();
			$UT->create ();
			$UT->u_id = $userinfo [0] ['u_id'];
			$UT->ut_token = $token;
			$UT->ut_expire = time () + 10;
			$UT->ut_lastupdate = get_now ();
			$UT->ut_ipaddress = get_client_ip ();
			$tokenRes = $UT->add ();
		}
		
		// 返回3dcity的地址URL
		
		if ($tokenRes) {
			$urlRes ['order_url'] = WEBROOT_URL . '/index/models-index-thumb-2-count-30-filter-pr-uid-' . $userinfo [0] ['u_id'] . '-token-' . $token;
		}
		$res [] = $urlRes ['order_url'];
		return $res;
	}
	
	/**
	 * 拿一个TOKEN
	 */
	public function getOneToken($UserID) {
		return $this->_createToken ( $UserID );
	}

    /*APP获取手机验证码
     * @to string 手机号
     */
        public function sentcode($to,$codetype=1){

            if ($codetype == 1) {//注册时
                if (preg_match('/^1[3|4|5|6|7|8|9]{1}[0-9]{9}$/', $to) != 1) {
                    $msg['status'] = 703;
                    return $msg;
                } elseif (M('users')->where("u_mob_no = '" . $to . "'")->count() != 0) {
                    $msg['status'] = 702;
                    return $msg;
                }
            } elseif ($codetype == 2) {//找回密码时
                if (preg_match('/^1[3|4|5|6|8|9]{1}[0-9]{9}$/', $to) != 1) {
                    $msg['status'] = 703;
                    return $msg;
                } elseif (M('users')->where("u_mob_no = '" . $to . "'")->count() == 0) {
                    $msg['status'] = 706; //手机号码未注册
                    return $msg;
                }
            }

        $SM = new LogSmsModel();
        $code=$SM->getMobieCaptchaCode($to,1);//获得验证码
        $datas[0] = $code;

        //验证每日发送次数
        //exit;
        if($SM->vfyperiodByMobno($to,C('SMS_LIMIT'))){
            if(smssent($to, $datas,'102938')){
                $SM->addlog(0,get_now(),$code,$to,$codetype);
                $msg['status'] = 200;
            }else{
                $msg['status'] = 705;
            }
        }else{
            $msg['status'] = 705;
        }

        return $msg;
    }

    /*
     * 头像上传接口 By zhangzhibin
     *
     */
    public function uploadavartar(){
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (!$logindata) {throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );}
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1]; // 原密码，未加md5
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (!$loginRes) {throw new Exception ( $reginer->ErrorCode );}
        $AM=new AvartarModel();
        $uploadResult=$AM->uploadAvartar($loginRes['u_id'],$_FILES ['filename']);
        if($uploadResult){
            $res[]=$uploadResult;
        }else{
            throw new Exception ( 401 );
        }
        return $res;
    }

    /*
     * 获得省、市、地区
     */
    public function getarea() {
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $AM = new AreaInfoPickerModel ();
        $areainfo = $AM->getAllAreaInfo ();
        $res = $areainfo;
        return $res;
    }

    /*
	 * 新增地址 by zhangzhibin 2014-05-29
	 */
    public function adduseraddress() {
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $UM = new UsersModel ();
        $userinfo = $UM->getUserByMobnoEmail($logininfo ['mobno'] );
        $uid = $userinfo ['u_id'];
        $AddressInfo = json_decode(base64_decode($args ['datas']),true);
        // ------------------------进行pvc 数据验证 begin


        $AddressInfo = $this->getAddressArray ( $AddressInfo );
        if (! $AddressInfo) {
            throw new Exception ( $this->RES_CODE_TYPE ['USERADDRESS_FORMAT_ERROR'] );
            exit ();
        }
        // ------------------------进行pvc 数据验证 end
        $UA = new UserAddressModel ();
        $adddata ['u_id'] = $uid;
        $adddata ['ua_addressee'] = $AddressInfo ['ua_addressee'];
        $adddata ['ua_province'] = $AddressInfo ['ua_province'];
        $adddata ['ua_city'] = $AddressInfo ['ua_city'];
        $adddata ['ua_region'] = $AddressInfo ['ua_region'];
        $adddata ['ua_address'] = $AddressInfo ['ua_address'];
        $adddata ['ua_zipcode'] = $AddressInfo ['ua_zipcode'];
        $adddata ['ua_mobile'] = $AddressInfo ['ua_mobile'];
        $Address['ua_id'] = $UA->data ( $adddata )->add ();
        if ($Address['ua_id']) {
            if ($AddressInfo ['isdefault']) {
                $UA->setDefaultAddress ( $uid, $Address['ua_id'] );
            }
            $res [] = $Address;
        }
        return $res;
    }

    /*
	 * 获取地址 by zhangzhibin 2014-05-29
	 */
    public function getuseraddress() {
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        //var_dump($loginRes);
        // 获取address
        $UM = new UsersModel ();
        $UserInfo = $UM->getUserByMobnoEmail($logininfo ['mobno'] );

        $UID = $UserInfo ['u_id'];
        $UA = new UserAddressModel ();
        $UserAddress = $UA->getAddressAreaByUserID ( $UID );
        if (! $UserAddress) {
            throw new Exception ( $this->RES_CODE_TYPE ['USERADDRESS_NOT_EXIST'] );
        }
        $res = $UserAddress;
        return $res;
    }

    /*
	 * 更新地址 by zhangzhibin 2014-05-29
	 */
    public function updateuseraddress() {
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $UM = new UsersModel ();
        $userinfo = $UM->getUserByMobnoEmail($logininfo ['mobno']);
        $uid = $userinfo ['u_id'];
        $AddressInfo = json_decode ( base64_decode( $args ['datas']), true );

        // ------------------------进行pvc 数据验证 begin
        // $AddressInfo = $this->getAddressArray($AddressInfo);
        if (!$AddressInfo) {
            $res [] = 0;
            return $res;
            exit ();
        }
        // ------------------------进行pvc 数据验证 end
        $UA = new UserAddressModel ();
        $UAID = intval ( $AddressInfo ['ua_id'] );
        $savedata ['ua_addressee'] = $AddressInfo ['ua_addressee'];
        $savedata ['ua_province'] = $AddressInfo ['ua_province'];
        $savedata ['ua_city'] = $AddressInfo ['ua_city'];
        $savedata ['ua_region'] = $AddressInfo ['ua_region'];
        $savedata ['ua_address'] = $AddressInfo ['ua_address'];
        $savedata ['ua_mobile'] = $AddressInfo ['ua_mobile'];
        $savedata ['ua_lastupdate'] = get_now ();
        $up_result['ua_id'] = $UA->where ( "ua_id=" . $UAID . "" )->save ( $savedata );
        if ($up_result){
            if($AddressInfo ['isdefault']) {
                $UA->setDefaultAddress ( $uid, $UAID  );
            }
            $result['result']=1;
        }else{
            $result['result']=1;
        }
        //$result['result']=$AddressInfo ['isdefault'];
        //$result['isdefault']=$AddressInfo ['isdefault'];
        $res [] = $result;
        return $res;
    }

    /*
	 * 删除收货地址
	 */
    public function deladdress() {
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        $datas=json_decode(base64_decode($args ['datas']),true);
        $uaid = $datas ['ua_id'];
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $UM = new UsersModel ();
        $userinfo = $UM->getUserByMobnoEmail($logininfo ['mobno'] );
        $uid = $userinfo ['u_id'];
        $UA = new UserAddressModel ();
        if ($UA->where ( "ua_id=" . $uaid . " and u_id=" . $uid . "" )->delete ()) {
            $result['result']=1;
            $res [] = $result;
        }else{
            $result['result']=0;
            $res [] = $result;
        }
        return $res;
    }

    private function getAddressArray($AddressArray) { // 地址信息验证
        $PVC = new PVC2 ( "array" );
        $PVC->SourceArray = $AddressArray;
        $PVC->setModeArray ()->setStrictMode ( false );
        if ($AddressArray ['ua_id']) {
            $PVC->isInt ()->Between ( 1, null )->add ( 'ua_id' );
        }
        if ($AddressArray ['isdefault']){
            $PVC->isInt ()->Between ( 0, 1 )->add ( 'isdefault' );
        }
        $PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'ua_province' );
        $PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'ua_city' );
        $PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'ua_region' );
        $PVC->isString ()->validateMust ()->add ( 'ua_addressee' );
        $PVC->isString ()->validateMust ()->add ( 'ua_address' );
        $PVC->isString ()->validateNotNull ()->add ( 'ua_mobile' );

        if (! $PVC->verifyAll ()) {
            return false;
        }
        if (! isset ( $PVC->ResultArray ['ua_mobile'] ) ) {
            return false;
        }

        return $PVC->ResultArray;
    }



    /*保存diy控件对应值*/
    public function savediyvalue(){
        $res = array();        // 返回结果
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );// 解析用户信息
        $loginRes=$this->apiLogin($args);//判断visa和登录
        $DatasInfo = json_decode(base64_decode($args ['datas']),true);
        $UDM=new UserDiyModel();
        $DatasInfo['u_id']=$loginRes['u_id'];

       // var_dump($DatasInfo);
        $UDM=new UserDiyModel();
        $saveResult['result']=$UDM->saveDiy($DatasInfo);

        if(!$saveResult) {
            throw new Exception ( $this->RES_CODE_TYPE ['banner_error'] );
        }
        $res[]= $saveResult;
        return $res;
    }

    //获取用户购物车
    public function getusercart(){
        $res = array();        // 返回结果
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );// 解析用户信息
        $loginRes=$this->apiLogin($args);//判断visa和登录
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
         }
        $UCM=new UserCartModel();
        $UCInfo=$UCM->getCartItemList($loginRes['u_id']);
        $UDM=new UserDiyModel();
        foreach($UCInfo['list'] as $key=>$value){
            $UCResult[$key]['uc_id']=intval($value['uc_id']);
            $UCResult[$key]['p_id']=$value['p_id'];
            $UCResult[$key]['p_cover']=WEBROOT_URL.$value['p_cover'];
            $UCResult[$key]['p_price']=doubleval($value['p_price']);
            $UCResult[$key]['p_name']=$value['p_name'];
            $UCResult[$key]['uc_count']=intval($value['uc_count']);
            $UCResult[$key]['diy_id']=$value['p_producttype']==4?1:0;
            if( $UCResult[$key]['diy_id']==1){
                $userDiyInfo=$UDM->getUserDiyInfoById($value['p_diy_id']);
                $value['diy_unit_info']=$userDiyInfo['diy_unit_info'];
                $UCResult[$key]['p_description']=$UCM->getUserCartDiyByProduct($value);
            }else{
                $UCResult[$key]['p_description']='';
            }
        }
        $res=$UCResult;
        return $res;
    }

    //从购物车中删除
    public function delusercart(){
        $res = array();        // 返回结果
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );// 解析用户信息
        $loginRes=$this->apiLogin($args);//判断visa和登录

        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
        }
        $UCM=new UserCartModel();
        $pidArr=explode(",",$datas['pid']);
        if(is_array($pidArr)){
            foreach($pidArr as $key => $value){
                $result=$UCM->deleteProduct($value,$loginRes['u_id']);
               // $re=$result?1:0;
              //  $returnresult.=$re.",";
            }
        }
     //   $returnresult=substr ( $returnresult, 0, - strlen ( "," ) );
        $return['result']=1;
        $res[]=$return;
        return $res;
    }


    /**更新购物车--点击结算按钮
     * @return array
     * @throws Exception
     */
    public function updateusercart(){
        $res = array();        // 返回结果
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );// 解析用户信息
        $loginRes=$this->apiLogin($args);//判断visa和登录
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
        }
       // $ucinfo=$datas['ucinfo'];//购物车信息，array

        $UCM=new UserCartModel();
        foreach($datas as $key => $value){
            $UCM->updateItemCount($key,$value);
        }
        $return['result']=1;
        $res[]=$return;
        return $res;
    }


    //生成订单
    public function buildorder(){
        $res        = array();        // 返回结果
        $args       = func_get_args ();
        $args       = $this->decodeArguments ( $args );// 解析用户信息
        $loginRes   = $this->apiLogin($args);//判断visa和登录
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
        }
        $ucidString = $datas['uc_id'];//购物车id，string
        $uaId       = $datas['ua_id'];//产品id
        $uid        = $loginRes['u_id'];
        $UCM        = new UserPrepaidModel();
        $addResult  = $UCM->appPrepaidadd($uid,$ucidString,$uaId);
        $return     = $addResult?$addResult:0;
        $res[]      = $return;
        return $res;
    }






    public function apiLogin($args){
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if(!$logindata){
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1]; // 原密码，未加md5
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        return $loginRes;
    }


    /*
    * 获取订单列表 by zhangzhibin 2014-07-21
    */
    public function getuserorder() {
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        //var_dump($loginRes);
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $UPM=new UserPrepaidModel();
        $UserOrder = $UPM->appGetUserOrder($loginRes['u_id']);
        //exit;
        //var_dump($UserOrder);

        if (! $UserOrder) {
            throw new Exception ( $this->RES_CODE_TYPE ['USERORDER_NOT_EXIST'] );
        }
        $res = $UserOrder;
        return $res;
    }


    /*
   * 获取订单详情 by zhangzhibin 2014-07-21
   */
    public function getorderdetail() {
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
            $upid=$datas['upid'];
        }
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $UPM=new UserPrepaidModel();
        $orderDetail = $UPM->appGetOrderDetail($upid);
        //var_dump($orderDetail);
        if (! $orderDetail) {
            throw new Exception ( $this->RES_CODE_TYPE ['USERORDER_NOT_EXIST'] );
        }
        $res[] = $orderDetail;
        return $res;
    }

  /*
  * 加入购物车 by zhangzhibin 2014-07-21
  */
    public function addusercart() {
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
            $pid=$datas['pid'];
        }
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $UCM=new UserCartModel();
        // 需要用户登录的判断
        import ( 'App.Model.CartItem.AbstractCartItem' );
        import ( 'App.Model.CartItem.FactoryCartItemModel' );
        import ( 'App.Model.CartItem.CartItemRealPrintModel' );
        import ( 'App.Model.CartItem.CartItemVirtualPrintModel' );
        import ( 'App.Model.CartItem.CartItemNoneDiyModel' );

        $_GET['pid']=$pid;
        $_GET['isreal']=1;

        // 处理主商品
        $CIF = FactoryCartItemModel::init ( $_GET );
        $CIF->transMap ( $_GET );
        $UCM = new UserCartModel ();
        $addMainItemRes = $UCM->addItem ( $CIF,  $loginRes['u_id']);

        if (! $addMainItemRes) {
            throw new Exception ( $this->RES_CODE_TYPE ['ADDCART_FAIL'] );
        }
        //$result['ucid']=$addMainItemRes;
        $result['result']=$addMainItemRes?1:0;
        $res[] = $result;
        return $res;
    }


  /*
  * 再次购买 by zhangzhibin 2015-08-03
  */
    public function readdusercart() {
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
            $orderUpid=$datas['orderUpid'];
        }
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $UPM=new UserPrepaidModel();
        $orderInfo=$UPM->getProductListByUpid($orderUpid);
        import ( 'App.Model.CartItem.AbstractCartItem' );
        import ( 'App.Model.CartItem.FactoryCartItemModel' );
        import ( 'App.Model.CartItem.CartItemRealPrintModel' );
        import ( 'App.Model.CartItem.CartItemVirtualPrintModel' );
        import ( 'App.Model.CartItem.CartItemNoneDiyModel' );
        $UCM = new UserCartModel ();
        foreach($orderInfo as $key => $value){
            $_GET['pid']=$value['p_id'];
            $_GET['isreal']=1;
            // 处理主商品
            $CIF = FactoryCartItemModel::init ( $_GET );
            $CIF->transMap ( $_GET );
            $addMainItemRes = $UCM->addItem ( $CIF,  $loginRes['u_id']);
        }
        if (! $addMainItemRes) {
            throw new Exception ( $this->RES_CODE_TYPE ['ADDCART_FAIL'] );
        }
        $result['result']=1;
        $res[] = $result;
        return $res;
    }

    /**
     * 获取用户DIY方案列表
     */
    public function userdiy() {
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $UID = $loginRes ['u_id'];
        $UDM = new UserDiyModel();
        $UserDiyList = $UDM->getUserDiyList($UID );
        if (!$UserDiyList) {
            throw new Exception ( $this->RES_CODE_TYPE ['USERDIY_NOT_EXIST'] );
        }else{
            foreach($UserDiyList as $key =>$value){
                $UserDiyListRes[$key]['userdiyid'] =$value['id'];
                $UserDiyListRes[$key]['title']  =$value['title'];
                $UserDiyListRes[$key]['cover']  =WEBROOT_URL.$value['cover'];
                $UserDiyListRes[$key]['ctime']  =$value['ctime'];
                $UserDiyListRes[$key]['price']  =$value['price'];
                $UserDiyListRes[$key]['cid']  =$value['cid'];
            }
        }
        $res = $UserDiyListRes;
        return $res;
    }

    /**
     * 删除用户DIY方案
     */
    public function userdiydel() {
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
            $udid=$datas['udid'];
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (! $loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $UID = $loginRes ['u_id'];
        $UDM=new UserDiyModel();
        $UserDiyDel['result'] = $UDM->delUserDiy($udid,$UID);
        if (!$UserDiyDel) {
            throw new Exception ( $this->RES_CODE_TYPE ['USERDIY_DEL_FAIL'] );
        }
        $res[] = $UserDiyDel;
        return $res;
    }



    /**
     * app支付宝签名生成
     */
    public function alipaymob() {
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
            $orderid=$datas['tradeNO'];
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (!$loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        //echo $orderid;
        $AMM=new AlipayMobModel();
        $SignResult['result']=$AMM->createSign($orderid);
        $res[] = $SignResult;
        return $res;
    }

    /*
     * APP获取支付宝二维码
     */
    public function alipayqrcode(){
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
            $orderid=$datas['tradeNO'];
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (!$loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $AMM            = new AlipayMobModel();
        $SignResult['result']=$AMM->createQrCodeParam($orderid);
        $res[] = $SignResult;
        return $res;
    }

    /*
    * APP获取支付宝支付信息,调起app支付
    */
    public function alipayapp(){
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
            $orderid=$datas['tradeNO'];
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (!$loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $AMM            = new AlipayMobModel();
        $SignResult['result']=$AMM->createQrCodeParamByApp($orderid);
        $res[] = $SignResult;
        return $res;
    }


    /*
     * APP 支付宝返回结果验签接口
     */
    public function alipayqrcode_singcheck(){

    }

    /*
     * APP 获取微信支付二维码
     */
    public function weixinqrcode(){
        // 返回结果
        $res = array ();
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
            $orderid=$datas['tradeNO'];
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load ( '@.Reginer' );
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login ( $logininfo );
        if (!$loginRes) {
            throw new Exception ( $reginer->ErrorCode );
        }
        $WMM=new WeixinpayMobModel();
        $CodeUrlResult['result']=$WMM->wxQrCode($orderid);
        $res[] = $CodeUrlResult;
        return $res;
    }





}
?>