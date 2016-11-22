<?php
class Reginer {
	public $ErrorMessage = array (
			10 => '数据验证不通过',
			100 => '数据库连接失败',
			110 => '验证码错误',
			101 => '页面传值错误',
			311 => '用户不存在或密码错误',
			312 => '密码输入错误',
			321 => '邮箱已被注册',
			322 => '用户名已被注册',
			323 => '邮箱格式错误',
			324 => '用户名不符合规范',
			325 => '密码不符合规范',
			326 => '两次密码不同',
            327 => '手机号码格式错误',
            //328 => '手机号已经注册',
            329 => '手机验证码错误',
            330 => '手机号码已经存在'
	);
	public $ErrorCode = 0;
	public $ReqestFrom = 1;
	private $UserData = false;
	
	// 请求来源类型
	protected $REQUEST_FROM = array (
			'PAGE' => 1,
			'CLIENT' => 2,
			'RP360' => 3,
            'APP'=>4,
            'APPAND'=>5,
			'UNKNOWN' => 9
	);
	
	/**
	 * 用户注册
	 *
	 * @author miaomin
	 * @param array $RegInfo
	 *        	- email
	 *        	- nickname
	 *        	- pass(明文)
	 * @return mixed
	 */
	function Register(array $RegInfo) {
        //return $RegInfo['from'];
       // exit;
		// 数据验证
		$RegPost = $this->getRegisterPost ( $RegInfo );

        if (! $RegPost) {
			$this->ErrorCode = 10;
			return false;
		}
		/*// 校验码验证
		if (C ( 'ENABLE_REGISTER_VCODE' )) {
			if (strtolower ( $RegInfo ['verify_code'] ) != strtolower ( $this->_session ( 'php_captcha' ) )) {
				$this->ErrorCode = 110;
				return false;
			}
		}*/
		// 是否被注册
		$UM = new UsersModel ();
        if($RegInfo ['email']){
            if ($UM->isUserEMailExist ( $RegInfo ['email'] )) {
                $this->ErrorCode = 321;
                return false;
            }
        }
        if($RegInfo ['mobno']){
            if ($UM->isUserMobnoExist ( $RegInfo ['mobno'] )) {
                $this->ErrorCode = 702;
                return false;
            }
        }

        //-----------判断手机验证码是否正确
        $SM = new LogSmsModel();
        if(!$SM->verGetcode($RegInfo ['mobno'],$RegInfo ['verifycode'],1)){//如果验证不通过,返回false
            $this->ErrorCode = 701; //手机验证码错误
            return false;
        }
        //------------
		// 写表
		$UM = $this->buildRegisterData ( $RegInfo );
		$this->UserData = $UM->data ();
		$UM->startTrans ();
		$UID = $UM->add ();

        if (! $UID || ! $this->createRelevantTables ( $UID )) {
			$UM->rollback ();
			$this->ErrorCode = 100;
			return false;
		}
		$UM->commit ();
		$this->UserData [$UM->F->ID] = $UID;
        $regUserInfo['u_id']=$this->UserData['u_id'];
        $regUserInfo['u_avatar']=$this->UserData['u_avatar']?$this->UserData['u_avatar']:"";
        $regUserInfo['u_dispname']=$this->UserData['u_dispname']?$this->UserData['u_dispname']:"";
        $regUserInfo['u_status']=$this->UserData['u_status']?$this->UserData['u_dispname']:"";
        $regUserInfo['u_salt']=$this->UserData['u_salt'];
        $regUserInfo['u_mob_no']=$this->UserData['u_mob_no'];
		// 发送激活邮件
		//$MVA = new MailValidateAction ();
		//$sendRes = $MVA->sendRegisterMail ( $UID, $RegInfo ['email'] );
		return $regUserInfo;
		//return $this->UserData;
	}
	
	/*
	 * 用户名和密码验证(登录)
	 * @param Array $LoginInfo 用户信息
	 * @param Int  $islog 1为记录登录日志 0为不记录登录日志
	 */
	public function Login($LoginInfo) {
        $LoginPost = $this->getLoginPost ( $LoginInfo );
		$UM = new UsersModel ();
        $UserInfo = $UM->getUserByMobnoEmail( $LoginPost ['mobno'] );


		if ($UserInfo === false) {
			$this->ErrorCode = 100;
			return false;
		}
		if ($UserInfo === null) {
			$this->ErrorCode = 3116;
			return false;
		}

		if ($UserInfo [$UM->F->Pass] != $this->getSaltPass ( $LoginPost ['pass'], $UserInfo [$UM->F->Salt] )) {
			$this->ErrorCode = 3115;
			return false;
		}
        return $UserInfo;
	}
	
	/**
	 * 用户注册资料验证
	 *
	 * @param unknown_type $RegInfo        	
	 * @return multitype: boolean
	 */
	private function getRegisterPost($RegInfo) {
		$PVC = new PVC2 ();
		$PVC->setStrictMode ( false )->setModeArray ()->SourceArray = $RegInfo;
       // var_dump();
		if ($RegInfo ['from'] == $this->REQUEST_FROM ['CLIENT']) {
			// 客户端注册(现在应该已经废弃)
            $PVC->isString ()->isEMail ()->validateMust ()->Error ( '323' )->add ( 'email' );
			$PVC->isString ()->Length ( 32, null )->validateMust ()->Error ( '325' )->add ( 'pass' );
		} elseif ($RegInfo ['from'] == $this->REQUEST_FROM ['RP360']) {
            // RP360注册
            $PVC->isString ()->isEMail ()->validateMust ()->Error ( '323' )->add ( 'email' );
            $PVC->isString()->Length(6, null)->validateMust()->Error('325')->add('pass');
        }elseif($RegInfo ['from'] == $this->REQUEST_FROM ['APP'] || $RegInfo ['from'] == $this->REQUEST_FROM ['APPAND']){
            $PVC->isNum()->Length(11,11)->validateMust()->Error('327')->add('mobno');
            $PVC->isNum()->Length(6,6)->validateMust()->Error('327')->add('verifycode');
        }else{
			// 正常注册
            $PVC->isString ()->isEMail ()->validateMust ()->Error ( '323' )->add ( 'email' );
			$PVC->isString ()->Length ( 6, null )->validateMust ()->Error ( '325' )->add ( 'pass' );
			$PVC->isString ()->Confirm ( 'pass' )->validateMust ()->Error ( '326' )->add ( 'pass_confirm' );
		}
		// $PVC->isString()->validateMust()->Error('324')->add('nickname');
		/*if (C ( 'ENABLE_REGISTER_VCODE' )) {//页面验证码
			$PVC->isString ()->validateMust ()->Error ( '110' )->add ( 'verify_code' );
		}*/
        if ($PVC->verifyAll ()) {
			return $PVC->ResultArray;
		} else {
			$Err = array_values ( $PVC->Error );
			$this->ErrorCode = $Err [0];
			return false;
		}

	}
	private function getLoginPost($LoginInfo) {
		$PVC = new PVC2 ();
		$PVC->setStrictMode ( true )->setModeArray ()->SourceArray = $LoginInfo;
		if ($LoginInfo ['from'] == 2) {
            $PVC->isString ()->isEMail ()->validateMust ()->add ( 'email' );
            // $PVC->isString()->Length(32,
            // null)->validateMust()->Error('325')->add('pass');
            $PVC->isString()->validateMust()->Error('325')->add('pass');
        }elseif($LoginInfo ['from'] == 4){//手机APP登录
            $PVC->isString ()->validateMust ()->Error ( '327' )->add ( 'mobno' );
            $PVC->isString()->validateMust()->Error('325')->add('pass');
        }else{
            $PVC->isString ()->isEMail ()->validateMust ()->add ( 'email' );
            $PVC->isString ()->Length ( 6, null )->validateMust ()->Error ( '325' )->add ( 'pass' );
		}
		/*if (C ( 'ENABLE_REGISTER_VCODE' )) {
			$PVC->isString ()->validateMust ()->add ( 'verify_code' );
		}*/
		return $PVC->verifyAll () ? $PVC->ResultArray : false;
	}

	private function getSaltPass($Pass, $Salt) { // 全部用md5($Pass . $Salt)
		return md5 ( $Pass . $Salt );
	}
	private function buildRegisterData($RegPost) {
		$UM = new UsersModel ();
		$UM->create ( $RegPost );
		$UM->{$UM->F->Pass} = $this->getSaltPass ( $RegPost ['pass'], $UM->{$UM->F->Salt} );
		$UM->u_dispname = $RegPost ['nickname'];
		$UM->{$UM->F->LastLogin} = get_now ();
		$UM->{$UM->F->LastLoginTime} = time ();
		$UM->{$UM->F->LastIP} = $_SERVER ["REMOTE_ADDR"];
		$UM->u_from = $RegPost ['from'];
		return $UM;
	}
	private function createRelevantTables($UserID) {
		$UMP = new UserProfileModel ();
		$UMP->u_id = $UserID;
		if ($UMP->add () === false) {
			return false;
		}
		
		$UAP = new UserAccountModel ();
		$UAP->u_id = $UserID;
		if ($UAP->add () === false) {
			return false;
		}
		
		return true;
	}
	public function getLastError() {
		return $this->ErrorMessage [$this->ErrorCode];
	}
	public function getLastUserData() {
		return $this->UserData;
	}


}
?>