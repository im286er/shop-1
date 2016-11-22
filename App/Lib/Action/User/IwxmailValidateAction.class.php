<?php
/**
 * 第三版手机版忘记密码模块
 * Created by PhpStorm.
 * User: guolixun
 * Date: 2016/10/20
 * Time: 10:39
 */
class IwxmailValidateAction extends CommonAction {
    public function __construct() {
        session_start ();
        parent::__construct ();
    }
    public function forgetPass() {
        if($this->isPost ()) {

            $UserInfo = $UM->getUserByEMail ( $Email );
            if ($UserInfo === false) {
                return $this->error ( '连接失败，请稍后再试' );
            }
            if ($UserInfo === null) {
                return $this->error ( '当前账户不存在' );
            }
            $this->sendResetPassMail ( $UserInfo ['u_id'], $UserInfo ['u_dispname'], $Email );
            return $this->success ( '重置密码的邮件已发送至您的邮箱', U ( 'User/index' ), 8 );
        }
        $this->assign ( 'EMail', $Email );
        $this->_renderPage();
    }

    /*
     * 验证身份：选择验证方式
     */
//    public function forgetPass_s2(){
//        if($this->isPost ()) {
//            $account    = I('account','0','string');
//            $UM = new UsersModel ();
//            /*  if (strtolower ( $this->_post ( 'verification' ) ) !== strtolower ( $this->_session ( 'php_captcha' ) )) {
//                  return $this->error ( '验证码错误！' );
//              }  */
//            if(is_valid_email($account)){//判断是否为mail accountType为1是email
//                $userInfo=$UM->getUserByName($account);
//                if(!$userInfo){
//                    return $this->error('邮件未注册！');
//                }
//                $userInfo['accountType']=1;
//            }elseif($account){//判断是否为手机号 accountType为2是手机号 elseif(is_numeric($account)){
//                $userInfo=$UM->getUserByMobno($account);
//                if(!$userInfo){
//                    return $this->error('手机号未注册！');
//                }
//                $userInfo['accountType']=2;
//            }else{//输入有误
//                return $this->error ( '输入的账户名有误！' );
//            }
//            $this->assign('userInfo',$userInfo);
//        }else{
//            $this->error('很抱歉，参数错误！');
//        }
//        $this->_renderPage();
//    }

    /*
     * 验证身份：选择验证方式
     */
    public function forgetPass_s3(){
        if($this->isPost ()) {
            $accountType = I('accountType','0','string');
            $UM = new UsersModel ();
            if($accountType==1){//发送验证邮件
                $userMail   = I('account','0','string');
                $userInfo = $UM->getUserByEMail ( $userMail );
                if($userInfo){
                    $this->sendResetPassMail($userInfo ['u_id'], $userInfo ['u_dispname'], $userMail);
                    return $this->success('重置密码的邮件已发送至您的邮箱', U('User/index'), 8);
                }else{
                    return $this->error('很抱歉，参数错误！');
                }
            }else{//手机验证
                $mobno=I('account','0','string');
                $userInfo = $UM->getUserByMobno($mobno);
                $LSM=new LogSmsModel();
                $mobnoCaptcha=I('mobnoCaptcha',0,'intval');
                if($LSM->verGetcode($mobno,$mobnoCaptcha,2)){//验证成功
                    $umvCode=$this->getResetPassByMobno($userInfo['u_id']);

                    if($umvCode){
                        redirect(__APP__."/mail_validate/userresetpass/?code=".$umvCode."&accounttype=".$accountType);
                    }else{
                        $this->error('很抱歉，参数错误.');
                    }
                }else{
                    $this->error('手机验证码错误！');
                }
            }
        }else{
            $this->error('很抱歉，参数错误！');
        }
        $this->_renderPage();
    }
    /*
     * 修改密码页面
     * by zhangzhibin 2015.05.21
     * 包含手机和邮箱的重置密码
     */
//    public function UserResetPass() {
//        $VCode      = I('code','0','string');
//        $accounttype=I('accounttype',0,'intval');
//        //var_dump($VCode);
//        //exit;
//        if (! isset ( $VCode ) || !isset($accounttype)) {
//            return $this->error( '很抱歉,验证无效', U ( 'User/index' ) );
//        }
//
//        $this->assign ( 'VCode', $VCode );
//        $UMVM = new UserMailVaildateModel ();
//        $ActiveInfo = $UMVM->getActiveInfo ( $VCode, 2 );
//        // var_dump($ActiveInfo);
//        //exit;
//        if ($ActiveInfo === false) {
//            return $this->error ( '连接失败，请稍后再试', U ( 'User/index' ) );
//        }
//        if ($ActiveInfo === null) {
//            // return $this->error ( '验证码过期或无效,请重新申请。', __APP__ . '/mail_validate/forgetpass' );
//        }
//        if (! $this->checkExpiredTime ( $ActiveInfo [$UMVM->F->CrteateTime], $ActiveInfo [$UMVM->F->Expired] )) {
//            $UMVM->where ( $UMVM->F->ID . "='" . $ActiveInfo [$UMVM->F->ID] . "'" )->delete ();
//            return $this->error ( '超出有效的验证时间', U ( 'User/index' ) );
//        }
//        $UID = $ActiveInfo [$UMVM->F->UserID];
//        $UM = new UsersModel ();
//        $UserInfo = $UM->find ( $UID );
//
//        if ($UserInfo === false) {
//            return $this->displayError ( '连接失败，请稍后再试' );
//        }
//        if ($UserInfo === null) {
//            return $this->error ( '当前用户不存在或已被删除', U ( 'User/index' ) );
//        }
//        $account=$accounttype==2?$UserInfo ['u_mob_no']:$UserInfo ['u_email'];
//        $this->assign ( "account", $account );
//
//        if ($this->isPost ()) {
//
//            $Pass = $_POST ['pass'];
//            $PassConfirm = $_POST ['pass_confirm'];
//            if ($Pass != $PassConfirm) {
//                return $this->error ( '两次密码不同' );
//            }
//            if (strlen ( $Pass ) < 6) {
//                return $this->error ( '密码长度需要超过6位' );
//            }
//
//            $UM = new UsersModel ();
//            $UserInfo = $UM->find ( $UID );
//            if ($UserInfo === false) {
//                return $this->error ( '连接失败，请稍后再试' );
//            }
//            if ($UserInfo === null) {
//                return $this->error ( '当前用户不存在或已被删除', U ( 'User/index' ) );
//            }
//
//            $UM->u_id = $UID;
//            $UM->u_pass = $this->getSaltPass ( $Pass, $UserInfo ['u_salt'] );
//
//            $UM->startTrans ();
//            if ($UM->save () === false) {
//                $UM->rollback ();
//                return $this->displayError ( '修改密码失败，请稍后再试', U ( 'User/index' ) );
//            }
//            if ($UMVM->where ( $UMVM->F->ID . "='" . $ActiveInfo [$UMVM->F->ID] . "'" )->delete () === false) {
//                $UM->rollback ();
//                return $this->displayError ( '修改密码失败，请稍后再试', U ( 'User/index' ) );
//            }
//            $UM->commit ();
//            return $this->success ( '修改成功', U ( 'User/index' ) );
//        }
//        $this->_renderPage();
//    }

    public function resendRegisterMail() {
        $Email = $_SESSION ['f_useremail'];
        if ($this->isPost ()) {
            $Email = $_POST ['email'];
            $UM = new UsersModel ();
            $UserInfo = $UM->getUserByEMail ( $Email );
            if ($UserInfo === false) {
                return $this->displayError ( '连接失败，请稍后再试' );
            }
            if ($UserInfo === null) {
                return $this->displayError ( '当前用户不存在' );
            }
            if ($UserInfo [$UM->F->MailVerify]) {
                return $this->success ( '当前账户已被激活', U ( 'User/index' ) );
            }

            $this->sendRegisterMail ( $UserInfo [$UM->F->ID], $Email );
            return $this->success ( '用户验证的邮件已发送至您的邮箱', U ( 'User/index' ) );
        }
        $this->assign ( 'EMail', $Email );
        $this->display ();
    }
    public function RegisterActivate() {
        // ! 加个Error
        $VCode = $_GET ['code'];
        if (! isset ( $VCode )) {
            return $this->error ( '无效的验证码', U ( 'User/index' ) );
        }
        $UMVM = new UserMailVaildateModel ();
        $ActiveInfo = $UMVM->getActiveInfo ( $VCode, 1 );
        if ($ActiveInfo === false) {
            return $this->error ( '连接失败，请稍后再试', U ( 'User/index' ) );
        }
        if ($ActiveInfo === null) {
            return $this->error ( '无效的验证码', U ( 'User/index' ) );
        }
        if (! $this->checkExpiredTime ( $ActiveInfo [$UMVM->F->CrteateTime], $ActiveInfo [$UMVM->F->Expired] )) {
            return $this->error ( '超出有效的验证时间', U ( 'User/index' ) );
        }

        $UM = new UsersModel ();
        $UM->{$UM->F->ID} = $ActiveInfo [$UMVM->F->UserID];
        $UM->{$UM->F->MailVerify} = 1;
        $UM->startTrans ();
        if ($UM->save () === false) {
            $UM->rollback ();
            return $this->error ( '激活失败，请稍后再试', U ( 'User/index' ) );
        }
        $UM->commit ();
        $UMVM->where ( $UMVM->F->ID . "='" . $ActiveInfo [$UMVM->F->ID] . "'" )->delete ();
        $this->success ( '激活成功', U ( 'User/index' ) );
    }
    public function sendRegisterMail($UserID, $MailTo) {
        if (! $UserID) {
            exit ( 'Error!' );
        }
        $UMVM = new UserMailVaildateModel ();
        $UMVM->startTrans ();
        if ($UMVM->deleteActiveInfo ( $UserID, 1 ) === false) {
            return false;
        }
        $UMVM = $this->buildNewUMVData ( $UMVM, $UserID, 1 );
        $MailData = $UMVM->data ();
        $UMV_ID = $UMVM->add ();

        $UserAct = D ( 'UserActive' );
        $UserAct->u_id = $UserID;
        $UserAct->uc_code = $MailData['umv_code'];
        $UserAct->uc_createdate = get_now ();
        $uactRes = $UserAct->add ();

        if (($UMV_ID === false) || ($uactRes === false)) {
            $UMVM->rollback ();
            return false;
        }
        $UMVM->commit ();

        vendor ( 'ELetter.Letter' );
        $cfg = C ( 'USER_ACTIVE' );
        $args ['active_url'] = $cfg ['URL'] . $MailData [$UMVM->F->Code];
        return Letter::sendActiveMail ( $MailTo, $args );
    }
    public function sendResetPassMail($UserID, $UserName, $MailTo) {
        if (! $UserID) {
            exit ( 'Error!' );
        }
        $UMVM = new UserMailVaildateModel ();
        $UMVM->startTrans ();
        if ($UMVM->deleteActiveInfo ( $UserID, 2 ) === false) {
            return false;
        }

        $UMVM = $this->buildNewUMVData ( $UMVM, $UserID, 2 );
        $MailData = $UMVM->data ();
        $UMV_ID = $UMVM->add ();
        if ($UMV_ID === false) {
            $UMVM->rollback ();
            return false;
        }
        $UMVM->commit ();

        vendor ( 'ELetter.Letter' );
        $cfg = C ( 'USER_RESET' );
        $args ['reset_url'] = $cfg ['URL'] . $MailData [$UMVM->F->Code];
        $args ['username'] = $UserName;

        return Letter::sendResetMail ( $MailTo, $args );
    }
    private function createVCode($UserID) {
        $Salt = generate_password ( 5 );
        return md5 ( $UserID . $Salt . time () );
    }
    private function buildNewUMVData($UMVM, $UserID, $Type = 1) {
        $UMVM->{$UMVM->F->UserID} = $UserID;
        $UMVM->{$UMVM->F->Code} = $this->createVCode ( $UserID );
        $UMVM->{$UMVM->F->Type} = $Type;
        $UMVM->{$UMVM->F->CrteateTime} = time ();
        return $UMVM;
    }
    private function checkExpiredTime($CreateTime, $Expired) {
        return (time () <= $CreateTime + $Expired) ? true : false;
    }
    protected function displayError($Error, $Key = 'ErrInfo') {
        $this->assign ( $Key, $Error );
        $this->display ();
    }
    private function getSaltPass($Pass, $Salt) {
        return md5 ( $Pass . $Salt );
    }

    /*
     * 获得重置密码的的umv_code
     * by zhangzhibin 2015.05.21
     */
    public function getResetPassByMobno($UserID) {
        if (! $UserID) { exit ( 'Error!' ); }

        $UMVM = new UserMailVaildateModel ();
        $UMVM->startTrans ();
        if ($UMVM->deleteActiveInfo ( $UserID, 2 ) === false) {
            return false;
        }

        $UMVM = $this->buildNewUMVData ( $UMVM, $UserID, 2 );
        $MailData = $UMVM->data ();
        $UMV_ID = $UMVM->add ();
        if ($UMV_ID === false) {
            $UMVM->rollback ();
            return false;
        }
        $result=$UMVM->commit ();
        if($result){
            return $MailData [$UMVM->F->Code];
        }else{
            return false;
        }
    }


    /*
     * 第三版手机版
     * 找回密码合一处理
     *  Creater:glx-2016/10/20
     */
    public function findPass(){
        if($this->isPost ()) {
            /*接受找回密码的数据
             * account:手机号
             * verification：图形验证码
             * phoneCode：手机验证码
             * password：密码
             * pass_confirm：确认密码
             */
            $account    = I('account','0','string');
            $verifycation = I('verifycation','0','intval');
            $phoneCode = I('phoneCode','0','intval');
            $password = I('password','0','string');
            $pass_confirm = I('pass_confirm','0','string');

            /*---------------------------验证账号的正确性----------------------------------*/
            $UM = new UsersModel ();
            if(is_valid_email($account)){
                //判断是否为mail accountType为1是email
                $userInfo=$UM->getUserByName($account);
                if(!$userInfo){
                    return $this->error('邮件未注册！');
                }
                $userInfo['accountType']=1;
            }elseif($account){
                //判断是否为手机号 accountType为2是手机号 elseif(is_numeric($account)){
                $userInfo=$UM->getUserByMobno($account);
                if(!$userInfo){
                    $data['status']=1;
                    $data['msg']='手机号未注册';
                    return $this->ajaxReturn($data,JSON);
                }
                $userInfo['accountType']=2;
                /*---------------------------判断验证码Start----------------------------------*/
                $verifyRes = $this->testVerify($this->_post('verification'));

                if($verifyRes){
                /*---------------------------判断手机验证码Start----------------------------------*/
                    $phoneCodeRes = $this->testPhoneCode($account,$phoneCode);
                    if($phoneCodeRes){

                        $res = $this->UserResetPass($phoneCodeRes,$password,$pass_confirm);
                        return $this->ajaxReturn($res,JSON);

                    }else{
                        //return $this->error ( '验证码错误！' );
                        $data['status']=5;
                        $data['msg']='验证码错误';
                        return $this->ajaxRETurn($data,JSON);
                    }

                /*---------------------------判断手机验证码End----------------------------------*/

                }else{
                    $data['status']=2;
                    $data['msg']='图形验证码错误';
                    return $this->ajaxReturn($data,JSON);
                    //return $this->error ( '图形验证码错误！' );
                }


                /*---------------------------判断验证码End------------------------------------*/


            }else{
                //输入有误
                //return $this->error ( '输入的账户名有误！' );
                $data['status']=3;
                $data['msg']='输入账户名有误';
                return $this->ajaxRETurn($data,JSON);
            }

        }else{
            $data['status']=4;
            $data['msg']='参数错误';
            return $this->ajaxRETurn($data,JSON);
            //$this->error('很抱歉，参数错误！');
        }
    }

    /*
     * 判断图形验证码正确性
     * Creater:glx-2016/10/20
     */
    private function testVerify($verify){
        if (strtolower ( $verify ) !== strtolower ( $this->_session ( 'php_captcha' ) )) {
            return false;
        }
        return true;
    }

    /*
     * 判断手机验证码正确性
     * Creater:glx-2016/10/20
     */
    private function testPhoneCode($accountType,$account,$phoneCode){
        $UM = new UsersModel ();
        $userInfo = $UM->getUserByMobno($account);
        $LSM=new LogSmsModel();
        if($LSM->verGetcode($account,$phoneCode,2)){//验证成功
            $umvCode=$this->getResetPassByMobno($userInfo['u_id']);
            if($umvCode){
                $arr=array(
                    'code'=>$umvCode,
                    'accounttype'=>$accountType
                );
                return $arr;
                //redirect(__APP__."/mail_validate/userresetpass/?code=".$umvCode."&accounttype=".$accountType);
            }else{
                return false;
                //$this->error('很抱歉，参数错误.');
            }
        }else{
            return false;
            //$this->error('手机验证码错误！');
        }
    }

    /*
     * 包含手机和邮箱的重置密码
     * Creater:glx-2016/10/20
     */
    private function UserResetPass($arr,$Pass,$PassConfirm) {
            $VCode      = $arr['code'];
            $accounttype=$arr['accounttype'];
            if (! isset ( $VCode ) || !isset($accounttype)) {
                $data['status']=7;
                $data['msg'] = '很抱歉,验证无效';
                return $data;
                //return $this->error( '很抱歉,验证无效', U ( 'User/index' ) );
            }
            $UMVM = new UserMailVaildateModel ();
            $ActiveInfo = $UMVM->getActiveInfo ( $VCode, 2 );
            // var_dump($ActiveInfo);
            //exit;
            if ($ActiveInfo === false) {
                $data['status']=7;
                $data['msg'] = '连接失败，请稍后再试';
                return $data;
                //return $this->error ( '连接失败，请稍后再试', U ( 'User/index' ) );
            }
            if ($ActiveInfo === null) {
                $data['status']=7;
                $data['msg'] = '验证码过期或无效,请重新申请';
                return $data;
                // return $this->error ( '验证码过期或无效,请重新申请。', __APP__ . '/mail_validate/forgetpass' );
            }
            if (! $this->checkExpiredTime ( $ActiveInfo [$UMVM->F->CrteateTime], $ActiveInfo [$UMVM->F->Expired] )) {
                $UMVM->where ( $UMVM->F->ID . "='" . $ActiveInfo [$UMVM->F->ID] . "'" )->delete ();
                $data['status']=7;
                $data['msg'] = '超出有效的验证时间';
                return $data;
                //return $this->error ( '超出有效的验证时间', U ( 'User/index' ) );
            }
            $UID = $ActiveInfo [$UMVM->F->UserID];
            $UM = new UsersModel ();
            $UserInfo = $UM->find ( $UID );
            if ($UserInfo === false) {
                $data['status']=7;
                $data['msg'] = '连接失败，请稍后再试';
                return $data;
                //return $this->displayError ( '连接失败，请稍后再试' );
            }
            if ($UserInfo === null) {
                $data['status']=7;
                $data['msg'] = '当前用户不存在或已被删除';
                return $data;
                //return $this->error ( '当前用户不存在或已被删除', U ( 'User/index' ) );
            }
            $account=$accounttype==2?$UserInfo ['u_mob_no']:$UserInfo ['u_email'];

            if (strlen ( $Pass ) < 6) {
                $data['status']=7;
                $data['msg'] = '密码长度需要超过6位';
                return $data;
                //return $this->error ( '密码长度需要超过6位' );
            }

            $UM = new UsersModel ();
            $UserInfo = $UM->find ( $UID );
            if ($UserInfo === false) {
                $data['status']=7;
                $data['msg'] = '连接失败，请稍后再试';
                return $data;
                //return $this->error ( '连接失败，请稍后再试' );
            }
            if ($UserInfo === null) {
                $data['status']=7;
                $data['msg'] = '当前用户不存在或已被删除';
                return $data;
                //return $this->error ( '当前用户不存在或已被删除', U ( 'User/index' ) );
            }

            $UM->u_id = $UID;
            $UM->u_pass = $this->getSaltPass ( $Pass, $UserInfo ['u_salt'] );

            $UM->startTrans ();
            if ($UM->save () === false) {
                $UM->rollback ();
                $data['status']=7;
                $data['msg'] = '修改密码失败，请稍后再试';
                return $data;
                //return $this->displayError ( '修改密码失败，请稍后再试', U ( 'User/index' ) );
            }
            if ($UMVM->where ( $UMVM->F->ID . "='" . $ActiveInfo [$UMVM->F->ID] . "'" )->delete () === false) {
                $UM->rollback ();
                $data['status']=7;
                $data['msg'] = '修改密码失败，请稍后再试';
                return $data;
                //return $this->displayError ( '修改密码失败，请稍后再试', U ( 'User/index' ) );
            }
            $UM->commit ();
            //return $this->success ( '修改成功', U ( 'User/index' ) );
            $data['status']=8;
            $data['msg'] = '修改成功';
            return $data;

    }
}
?>