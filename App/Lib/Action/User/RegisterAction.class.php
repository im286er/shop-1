<?php
class RegisterAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		//
		parent::__construct ();
	}
	
	/**
	 * 注册
	 *
	 * @access public
	 * @return mixed
	 */

    public function index2(){
        $data=$this->_post();
        print_r($data);
        exit;

    }

    public function index(){
        $this->assign ( "currenturl", pub_encode_pass ( urlencode ( get_url () ), "3dcity", "encode" ) ); // 赋值当前url到模板
        $this->assign('type',$_GET['type']);
        try {
            // $switch = false;
            if ($this->isPost ()) {
                $da['mobno']=$_POST['mobno'];
                $da['mobnoCaptcha']=$_POST['mobnoCaptcha'];
                $da['password']=$_POST['password'];
                $da['cfm_password']=$_POST['cfm_password'];
                $da['from_url']=$_POST['from_url'];
                $res=$this->_validRegister($da);
                if($res){
                    // TODO
                    // 增加事务处理
                    // 新增用户
                    $Users = D ( 'Users' );
                    $Users->startTrans ();
                    $reArr = array (); // 保存数据操作的结果集
                    $Users->create ();
                    // ----------------------------------------rp360 zhangzhibin
                    // 2014-07-25
                    // $rp360_user=$Users->data();//赋值用户信息
                    // var_dump($rp360_user);
                    // ----------------------------------------rp360 zhangzhibin
                    // 2014-07-25
                    $Users->u_pass = md5 ( $Users->u_pass . $Users->u_salt );
                    $uid = $Users->add ();
                    //  var_dump($Users);

                    // dump($uid);
                    $reArr [] = $uid;

                    $UP = D ( 'UserProfile' );
                    $UP->u_id = $uid;
                    $reArr [] = $UP->add ();
                    // dump($reArr);
                    $lastSql = $UP->getLastSql ();
                    // dump($lastSql);

                    $UA = D ( 'UserAccount' );
                    $UA->u_id = $uid;
                    $reArr [] = $UA->add ();

                    $lastSql = $UA->getLastSql ();

                    // -------------------向rp360发送注册请求,暂时不用
                    // $apicurl=new ApicurlModel();
                    // $apicurlresult=$apicurl->register($rp360_user);
                    // -------------------向rp360发送注册请求
                    // exit;
                    // 处理邀请码
                    // @formatter:off
                    /*
                    $invite = D ( 'InviteCode' );
                    $invite->getByic_code ( $this->_post ( 'invitecode' ));
                    $invite->ic_active = 1;
                    $invite->ic_activedate = get_now ();
                    $reArr[] = $invite->save ();
                    */
                    // @formatter:on

                    // 插入激活码
                    $UserAct = D ( 'UserActive' );
                    $ac_code = genactivecode ( $uid );
                    $UserAct->u_id = $uid;
                    $UserAct->uc_code = $ac_code;
                    $UserAct->uc_createdate = get_now ();
                    $reArr[] = $UserAct->add ();


                    // dump($reArr);
                    // $lastSql = $UserAct->getLastSql ();
                    // dump($lastSql);

                    if (in_array ( false, $reArr )) {
                        $Users->rollback ();
                        $data['msg']="注册失败";
                        $this->ajaxReturn($data,JSON);
                        throw new Exception ( 'MySQL I/0 Error.' );
                    } else {
                        $Users->commit ();

                        // 发送验证邮件
                        // @formatter:off
                        /*if($this->_post ( 'email' )){
                            vendor ( 'ELetter.Letter' );
                            $cfg = C ( 'USER_ACTIVE' );
                            $args ['active_url'] = $cfg ['URL'] . $ac_code;
                            Letter::sendActiveMail ( $this->_post ( 'email' ), $args ); //发送email,上传后需要打开
                        }*/
                        session ( 'f_userid', $uid);
                        session ( 'f_logindate', time ());

                        // 任务系统  积分
                        //$HJ = new HookJobsModel ();
                        //$HJ->run ( $uid, __METHOD__ );
                        //$this->redirect('regsuccess');
                        $data['status']=2;
                        $data['msg']="注册成功";
                        $this->ajaxReturn($data,JSON);
                        //echo "已发送激活邮件。";
                        //$this->success ( L ( 'send_active_mail' ), '__DOC__/index.php' );


                    }
                }else{
                    $data['status']=1;
                    $data['msg']="注册失败";
                    $this->ajaxReturn($data,JSON);
                }

            } else {
                $this->assign ( 'showtitle', "用户注册-Ignite" );
                $this->_renderPage ();
            }
        } catch ( Exception $e ) {

            // echo $e->getMessage ();
            $this->error ( $e->getMessage (), '__APP__/register' );
        }
    }

	
	/**
	 * 验证注册信息
	 *
	 * @param array $req        	
	 * @throws Exception
	 * @return boolean
	 */
	protected function _validRegister(array $req) {
		// TODO
		// 能否写一个既能用于AJAX又能用于表单的验证
		// vd ( $req );
		$PVC = new PVC2 ();
		$PVC->setStrictMode ( true )->setModeArray ()->SourceArray = $req;
		// 用户协议验证
		// $PVC->validateMust ()->Eq ( 'on' )->Error ( '没有勾选用户条款' )->add (
		// 'agree' );
		// 邮件验证
		//$PVC->isEMail ()->Error ( '邮件地址不符合注册条件' )->add ( 'email' );
		// 昵称验证
		//$PVC->Length ( 1, 20 )->Error ( '昵称不符合注册条件' )->add ( 'nickname' );

       $Users = D ( 'Users' );

        //手机是否已被注册
        $res=$Users->getByu_mob_no($req['mobno']);
        if (is_array ( $res )) {
            $data['status']=1;
            $data['msg']='验证码输入有误';
            return false;
            throw new Exception ( '手机号已被注册！' );
        }
        // 密码验证
		$PVC->Length ( 6, null )->Error ( '密码不符合注册条件' )->add ( 'password' );
		//$PVC->Confirm ( 'cfm_password' )->Error ( '两次输入的密码不符合注册条件' )->add ( 'password' );
//		if (! $PVC->verifyAll ()) {
//            $data['status']=1;
//            $data['msg']='验证码输入有误';
//            return false;
//			throw new Exception ( $PVC->Error );
//		}
        // 邮件是否已被注册
        if($req ['email']){
            $res = $Users->getByu_email ( $req ['email'] );
            if (is_array ( $res )) {
                $data['status']=1;
                $data['msg']='验证码输入有误';
                return false;
                throw new Exception ( '邮箱已被注册' );
            }
        }
        //手机验证码
        if($req['mobnoCaptcha']){
            $LSM=new LogSmsModel();
            if(!$LSM->getcode($req['mobno'],$req['mobnoCaptcha'])){
                $data['status']=1;
                $data['msg']='验证码输入有误';
                return false;
                throw new Exception ( '验证码输入有误错误' );
            }
        }
		// 昵称是否已被注册
		//$res = $Users->getByu_dispname ( $req ['nickname'] );
		// if (is_array ( $res )) {
		// throw new Exception ( 'u_dispname_already_register' );
		// }
		/*// 昵称是否含有屏蔽字
		$NK = D ( 'NoKeywords' );
		$nkCount = $NK->where ( "nk_words='" . $req ['nickname'] . "'" )->count ( "nk_id" );
		if ($nkCount) {
			throw new Exception ( L ( '昵称含有违禁词汇' ) );
		}*/

		/*
			if (strtolower ( $v_verify ) !== strtolower ( $this->_session ( 'php_captcha' ) )) {
		throw new Exception ( 'no_verify' );
		}
		*/
		// @formatter:on	
        return true;
	}
	private function buildNewUMVData($UMVM, $UserID, $Type = 1) {
		$UMVM->{$UMVM->F->UserID} = $UserID;
		$UMVM->{$UMVM->F->Code} = $this->createVCode ( $UserID );
		$UMVM->{$UMVM->F->Type} = $Type;
		$UMVM->{$UMVM->F->CrteateTime} = time ();
		return $UMVM;
	}
	
	// 获取邮箱激活邮件
	public function getemailverify() {
		if ($this->isPost ()) {
			
			// var_dump( $this->_session ( 'php_captcha' ));
			// exit;
			if (strtolower ( $this->_post ( 'verification' ) ) !== strtolower ( $this->_session ( 'php_captcha' ) )) {
				$this->error ( "验证码错误，请重新输入！" );
				// throw new Exception ( L ( 'verification_error' ) );
			}
			$u_email = I ( "post.email", "0", "string" );
			$UA = M ();
			$sql = "select TUA.uc_code from tdf_user_active as TUA Left Join tdf_users as TU On TUA.u_id=TU.u_id where TU.u_email='" . $u_email . "'";
			$uaresult = $UA->query ( $sql );
			$ac_code = $uaresult [0] ['uc_code'];
			vendor ( 'ELetter.Letter' );
			$cfg = C ( 'USER_ACTIVE' );
			$args ['active_url'] = $cfg ['URL'] . $ac_code;
			$args ['active_mail'] = $u_email;
			
			Letter::sendActiveMail ( $u_email, $args );
			$this->success ( L ( 'send_active_mail' ), '__APP__/login' );
			// exit;
		}
		$email = I ( 'get.email', 0, 'string' );
		$email = pub_encode_pass ( $email, "10000", "decode" );
		$email = str_replace ( "%40", "@", $email );
		$this->assign ( "email", $email );
		$this->display ();
	}
	
	//注册成功后的页面
	public function regsuccess(){
		// 浏览器判断
		if (is_weixin ()) {
			$mobileAgent = 1;
		} elseif (is_mobile ()) {
			$mobileAgent = 2;
		} else {
			$mobileAgent = 3;
		}
		$this->assign("header",1);
		$this->assign ( 'mobileAgent', $mobileAgent );
		$this->assign('message','注册成功,您可以开始首饰专属定制.');
		$this->display();
	}

    //手机号查重   判断users表是否存在手机号
    public function checkmobno(){
        if(M('users')->where("u_mob_no = '".$this->_post('mobno')."'")->count() != 0){
            $rvalue=false;
        }else{
            $rvalue=true;
        }
        $this->ajaxReturn($rvalue);
    }

    //邮箱查重   判断users表是否存在手机号
    public function checkemail(){
        $UM=new UsersModel();
        if($UM->getUserByEMail($this->_post('email'))){
            $rvalue=false;
        }else{
            $rvalue=true;
        }
        /*if(M('users')->where("u_mob_no = '".$this->_post('mobno')."'")->count() != 0){
            $rvalue=false;
        }else{
            $rvalue=true;
        }*/
        $this->ajaxReturn($rvalue);
    }


    function testCode(){
       // echo "abc";
        exit;
        $SM = new LogSmsModel();
        $smMobno='18621118091';
        $code=25645;
       // $to=$smMobno;
        $code=$SM->getMobieCaptchaCode($smMobno,1);
      //  $tes2=$SM->addlog(0,get_now(),$code,$to,1);
       // var_dump($code);
        echo $code;
        $datas[0]=$code;
        echo "<br<br<br>";
        $back_msg=smssent($smMobno, $datas,'102938');
        var_dump($back_msg);
    }

    /*注册时获取验证码
     * @to string 手机号
     */
    public function sentcode(){
      //  var_dump($_POST);
        $to=$this->_post('phone');
        if(preg_match('/^1[3|4|5|6|7|8|9]{1}[0-9]{9}$/', $to) != 1){
            $msg['status'] = "error";
            $msg['info'] = "手机号不合法";
            $this->ajaxReturn($msg);
        }elseif(M('users')->where("u_mob_no = '$to'")->count() != 0){
            $msg['status'] = "error";
            $msg['info'] = "手机号已注册";
            $this->ajaxReturn($msg);
        }elseif(strtolower($_POST['verification'])!=strtolower ( $this->_session ( 'php_captcha' ) )){
           $msg['status'] = "error";
            $msg['info'] = "图形验证码有误";
            $this->ajaxReturn($msg);
        }


        //$code = mt_rand(100000, 999999);

        $SM = new LogSmsModel();
        $code=$SM->getMobieCaptchaCode($this->_post('phone'),1);//获得验证码
        $datas[0] = $code;
        //验证每日发送次数
        if($SM->vfyperiodByMobno($to,C('SMS_LIMIT'))){
            if(smssent($to, $datas,'102938')){

                $SM->addlog(0,get_now(),$code,$to,1);
                $msg['status'] = "success";
                $msg['info'] = "验证码已发送到手机";
                //发送成功后，绑定账号
                $this->ajaxReturn($msg);
            }
            else{
                $msg['status'] = "error";
                $msg['info'] = "网络超时发送失败";
                $this->ajaxReturn($msg);
            }
        }else{
            $msg['status'] = "error";
            $msg['info'] = "你今天的发送次数已经达到最大限制";
            $this->ajaxReturn($msg);
        }

    }
    /*
     * 下一步验证
     */
    public function bindmob(){
        if($this->existmob()){
            redirect(WEBROOT_PATH);
        }

        $SM = new LogSmsModel();
        //判断验证码是否正确
        if($SM->getcode($this->_post('mobno'),$this->_post('code'))){
            //添加手机号字段到users
            $uid = session('f_userid');
            $data['u_mob_no'] = $this->_post('mobno');
            M('users')->where("u_id= $uid")->save($data);
            //跳转到设置初始密码页面
            //echo "验证码正确";
            redirect(__APP__."/sms/initpwd");
        }else{
            //验证码无效,跳转到前一个页面
            //echo "验证码错误";
            redirect(__URL__."/index/msg/errorvfy");
        }
    }

    /*注册时获取验证码
        * @to string 手机号
        */
    public function findpassSentcode($to){
        if(preg_match('/^1[0-9]{10}$/', $to) != 1){
            $msg['status'] = "error";
            $msg['info'] = "手机号不合法";
            $this->ajaxReturn($msg);
        }elseif(M('users')->where("u_mob_no = '".$this->_post('to')."'")->count() == 0){
            $msg['status'] = "error";
            $msg['info'] = "未注册的手机号";
            $this->ajaxReturn($msg);
        }
        //$code = mt_rand(100000, 999999);

        $SM = new LogSmsModel();
        $code=$SM->getMobieCaptchaCode($this->_post('to'));//获得验证码
        $datas[0] = $code;
        //验证每日发送次数
        if($SM->vfyperiodByMobno($to,C('SMS_LIMIT'))){
            if(smssent($to, $datas,'102938')){

                $SM->addlog(0,get_now(),$code,$to,2);//增加发送日志,最后的参数2为找回密码的验证码
                $msg['status'] = "success";
                $msg['info'] = "验证码已发送到手机,请填写到上方的输入框中,即可进行修改密码操作.";
                //发送成功后，绑定账号
                $this->ajaxReturn($msg);
            }else{

                $msg['status'] = "error";
                $msg['info'] = "网络超时发送失败";
                $this->ajaxReturn($msg);
            }
        }else{
            $msg['status'] = "error";
            $msg['info'] = "你今天的发送次数已经达到最大限制";
            $this->ajaxReturn($msg);
        }

    }
	
	
	
}
?>