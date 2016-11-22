<?php
class SmsAction extends CommonAction{
    public function __construct(){
        parent::__construct();
        if (! $this->_isLogin ()) {
            $this->_needLogin ();
        }
    }
    public function index(){


        if($this->existmob()){
            redirect(WEBROOT_PATH);
        }
        
        if($this->_get('msg')=="errorvfy"){
            $this->assign("msg","验证码错误");
        }
        $this->assign('wxtitle','绑定手机');
        $this->display();
    }
    //获取验证码
    /*
     * @to string 手机号
     * 
     */
    public function sentcode($to){
        if(preg_match('/^1[3|4|5|8]{1}[0-9]{9}$/', $to) != 1){
            $msg['status'] = "error";
            $msg['info'] = "手机号不合法";
            $this->ajaxReturn($msg);           
        }
        elseif(M('users')->where("u_mob_no = '".$this->_post('to')."'")->count() != 0){
            $msg['status'] = "error";
            $msg['info'] = "手机号已存在";
            $this->ajaxReturn($msg);
        }
        $code = mt_rand(100000, 999999);        
        $SM = new LogSmsModel();
        $datas[0] = $code;
        //验证每日发送次数
        if($SM->vfyperiod(session('f_userid'),C('SMS_LIMIT'))){
            if(smssent($to, $datas,'15380')){
                $SM->addlog(session('f_userid'),get_now(),$code,$to);
                $msg['status'] = "success";
                $msg['info'] = "验证码已经发送到你的手机";
                
                //发送成功后，绑定账号
                $this->ajaxReturn($msg);
            }
            else{
                $msg['status'] = "error";
                $msg['info'] = "网络超时发送失败";
                $this->ajaxReturn($msg);
            } 
        }
        else{
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

        if(strlen($this->_post('pwd'))<6 || $this->_post('pwd')==null){
            redirect(__APP__."/sms/index/msg/error");
        }
        if($this->_get('msg') == "error"){
            $this->assign("tips","密码不符合规则");
        }

        $SM = new LogSmsModel();
        //判断验证码是否正确
        if($SM->getcode($this->_post('mobno'),$this->_post('code'))){
            //添加手机号字段到users
            $uid = session('f_userid');
            $data['u_mob_no'] = $this->_post('mobno');
            $U = D('users');
            $usalt = $U->getFieldByU_id($uid,'u_salt');
            $data['u_pass'] = md5(trim($this->_post('pwd')).$usalt);
            M('users')->where("u_id= $uid")->save($data);
            //跳转到设置初始密码页面
            //echo "验证码正确";
            redirect(session('smsbind_urlfrom'),0,'绑定成功');
            //redirect(__APP__."/sms/index");
        }else{
            //验证码无效,跳转到前一个页面
            //echo "验证码错误";
            redirect(__URL__."/index/msg/errorvfy");            
        }
    }
    /*
     * 设置初始密码
     */
    public function initpwd(){
        //判断是否为初始密码
        if(!$this->isdefaultpwd()){
            if(session('smsbind_urlfrom') == NULL){
                redirect(session('smsbind_urlfrom'));
            }
            else{
                redirect(WEBROOT_PATH);
            }
            
        }
                      
        if($this->_get('msg') == "error"){
            $this->assign("tips","密码不符合规则");
        }
        if($this->_post('pwd') != NULL){
            if(strlen($this->_post('pwd'))<6){                
                redirect(__APP__."/sms/initpwd/msg/error");                
            }
            $uid=session('f_userid');
            $U = D('users');
            $usalt = $U->getFieldByU_id($uid,'u_salt');
            $data['u_pass'] = md5(trim($this->_post('pwd')).$usalt);
            $U->where("u_id=$uid")->save($data);
            redirect(session('smsbind_urlfrom'),3,'绑定成功');
        }
        $this->display();
    }
    //判断users表是否存在手机号
    private function existmob(){
        if(M('users')->getFieldByU_id(session('f_userid'),'u_mob_no') != NULL){
            return true;
        }
        else{
            return false;
        }
    }
    //判断是否是默认密码
    private function isdefaultpwd(){
        $U = new UsersModel();
        $arr = $U->getByU_id(session('f_userid'));
        if($arr['u_pass'] == md5('3dcity2014'.$arr['u_salt'])){
            return true;
        }
        else{
            return false;
        }
    }
    //更改绑定手机
    public function changemob(){
        $ipaddress=$_SERVER["REMOTE_ADDR"];
        if($ipaddress=="140.207.154.14" || $ipaddress=="::1"){
            $showGame="<a href=''>GO</a>";
            $this->assign("showGame",$showGame);
        }

       $this->assign('wxtitle','更改手机绑定');
        if($this->_get('msg') == "errorpwd"){
            $this->assign("tips","密码错误");
        }
        elseif($this->_get("msg") == "errorvfy"){
            $this->assign("tips","验证码错误");
        }
        else{}
        if($this->isPost()){
            //判断验证码
            $SM = new LogSmsModel();
            if($SM->getcode($this->_post('mobno'),$this->_post('code'))){                
                $uid = session('f_userid');
                
                $U = new UsersModel();
                $arr = $U->getByU_id(session('f_userid'));
                //判断密码是否正确
                if($arr['u_pass'] != md5($this->_post('rpwd').$arr['u_salt'])){
                    redirect(__URL__."/changemob/msg/errorpwd");
                }
                else{
                    $data['u_mob_no'] = $this->_post('mobno');
                    $U->where("u_id = $uid")->save($data);
                    redirect(WEBROOT_PATH,'3','更改手机成功');//更改成功后的页面
                }
            
            }
            else{
                //echo "验证码错误";
                redirect(__URL__."/changemob/msg/errorvfy");
            }
        }
        $this->display();
               
    }
   
    
}