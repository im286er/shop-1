<?php
class WomendayAction extends CommonAction{
    public function __construct(){
        parent::__construct();
    }
    public function test(){
        echo "######";
    }
    //分享主页面
    public function sharetotimeline(){
        $user = M('users')->getFieldByU_id(session('f_userid'),'u_email');
        $key = 'guimijie';
        $salt = md5($user."guimijie");
        Vendor('wxshare.jssdk');
        $jssdk = new JSSDK("wxf71f9222d3bc3c2e", "7338ad0158221be35c8572c562a56899");
        $signPackage = $jssdk->GetSignPackage();
       // var_dump($signPackage);
        $this->assign('signPackage',$signPackage);
        //$this->assign('salt',$salt);
        $codeInfo=M("log_active_code")->field('code')->where('id=3')->find();
        $code=$codeInfo['code'];
        //var_dump(session_id());
        $data['code']=$code;
        $data['ip']=get_client_ip();
        $data['url']=__SELF__ ;
        $data['sessionid']=session_id();
        M('log_active')->add($data);
        $this->assign('code',$code);
        $this->display();
    }
    //分享到朋友圈,生成码并且跳转到aftershare页面
    public function sharetowx(){
        if (! $this->_isLogin ()) {
            $this->_needLogin ();
        }
        redirect('http://www.ignjewelry.com/user.php/womenday/sharetotimeline');
        /*$user = M('users')->getFieldByU_id(session('f_userid'),'u_email');
        $etid = M('coupon_type')->getFieldByEt_name('七夕','et_id');
        //if($s != md5($user."guimijie")){
        //    $this->redirect("sharetotimeline",array(),3,'error:invalid operation,3 secs reutrn to lastPage');
        //}
        //coupon_type没有闺蜜节字典字条记录，自动添加
        if($etid == NULL){
            $data['et_name'] = "七夕";
            $data['et_private'] = "2";
            $data['et_type'] = "2";
            $data['et_amount'] = "40";
            $data['et_createdate'] = get_now();
            $data['et_expiredate'] = "2015-05-31 23:59:59";
            M('coupon_type')->add($data);
            $etid = M('coupon_type')->getFieldByEt_name('七夕','et_id');
        }
        $Coupon = new CouponModel();
         
        $sql = "select count(*) num from tdf_coupon t1 left join tdf_coupon_type t2 on t2.et_id=t1.etId where t1.ec_owner='{$user}' and t2.et_id='{$etid}';";
        $count = $Coupon->query($sql);
        if($count[0]['num'] == 0){
            $Coupon->create($this->_get());
            $Coupon->etId = $etid;
            $Coupon->ec_owner = $user;
            $Coupon->ec_status = 1;
            $Coupon->add();
        }
        else{
            $this->redirect("aftershare",array('msg'=>'1'),0,'error:has owned a guimijie ecoupon,3 secs reutrn to lastPage');
        }
        $this->redirect("aftershare",array('msg'=>'0'),0,"success:U get one guimijie ecoupon,3 secs reutrn to mycoupon");*/
    }

    //分享后跳转到该页面
    public function aftershare($msg = ""){
        if (! $this->_isLogin ()) {
            $this->_needLogin ();
        }
        Vendor('wxshare.jssdk');
        $jssdk = new JSSDK("wxf71f9222d3bc3c2e", "7338ad0158221be35c8572c562a56899");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
         
        $user = M('users')->getFieldByU_id(session('f_userid'),'u_email');
        $etid = M('coupon_type')->getFieldByEt_name('闺蜜节','et_id');
         
        $Coupon = new CouponModel();
        $condition['ec_owner'] = $user;
        $condition['etId'] = $etid;
        $code = $Coupon->where($condition)->select();
        if($msg == 0){
            $this->assign('msg','0');
        }
        else{
            $this->assign('msg','1');
        }
        $this->assign('code',$code[0]['ec_code']);
        $this->display();
    }
    //分享给好友后，好友点击的页面
    public function sharetof(){
        /*if (! $this->_isLogin ()) {
            $this->_needLogin ();
        }*/
        Vendor('wxshare.jssdk');
        $jssdk = new JSSDK("wxf71f9222d3bc3c2e", "7338ad0158221be35c8572c562a56899");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
         
        $etid = M('coupon_type')->getFieldByEt_name('闺蜜节','et_id');
        $user = M('users')->getFieldByU_id(session('f_userid'),'u_email');
        $Coupon = new CouponModel();
    
        $sql = "select count(*) num from tdf_coupon t1 left join tdf_coupon_type t2 on t2.et_id=t1.etId where t1.ec_owner='{$user}' and t2.et_id='{$etid}';";
        $count = $Coupon->query($sql);
        if($count[0]['num'] == 0){
            $Coupon->create($this->_get());
            $Coupon->etId = $etid;
            $Coupon->ec_owner = $user;
            $Coupon->ec_status = 1;
            $Coupon->add();
            $this->assign('msg','0');
        }
        else{
            $this->assign('msg','1');
        }
        $condition['ec_owner'] = $user;
        $condition['etId'] = $etid;
        $code = $Coupon->where($condition)->select();

        $this->assign('code',$code[0]['ec_code']);
        $this->display();
    }
}
?>