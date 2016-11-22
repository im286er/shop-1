<?php
class CashecouponAction extends CommonAction{
    
    public function __construct(){
        parent::__construct();
        
        //if (! $this->_isLogin ()) {
        //    $this->_needLogin ();
        //}
    }
    
    /*
     * 购买
     */
    //需提交name = amout表达数据
    public function buy(){
        $Cashecoupon = new CashecouponModel();
        $Cashecoupon->create( $this->_get() );//create是为了使$_auot,$_map生效；
        $Cashecoupon->ca_owner = $this->_session('f_userid');
        $Cashecoupon->ca_status = 2;
        $Cashecoupon->add();
        echo $Cashecoupon->getLastSql();
    }
    /*
     * 充值
     *需提交代金券码  $code
     */
    public function recharge(){
        if($this->isPost()){
            if( ! $this->_isLogin () ){
                $this->_needLogin();
            }
            //判断code输入错误次数
            if( $this->_session('vfyerror') == NULL){
                session(vfyerror,0);
            }
            if($this->_session('vfyerror') > 2){
                if( $this->_session('verify') != md5($this->_post('verify')) ){
                    $this->error('验证码错误');
                }
            } 
              
            $code = trim($this->_post('coupon'));//代金券码
            $Cashecoupon = M('cashecoupon');
            
            if( $Cashecoupon->getByCa_code($code) == ""){
                //失败记录到SESSION
                $no = $this->_session('vfyerror');
                $no++;
                session('vfyerror',$no);
                
                $this->error("代价券不存在","__APP__/cashecoupon/recharge");
            }
            if($Cashecoupon->ca_status == 3){
                $this->error("代价券已被充值","__APP__/cashecoupon/recharge");
            }
            elseif( $Cashecoupon->ca_status == 1 ){
                $this->error("代价券尚未被发放","__APP__/cashecoupon/recharge");
            }
            elseif( $Cashecoupon->ca_status == 2 ){
                //判断是否过期
                if($Cashecoupon->ca_expiredate < get_now() && $Cashecoupon->ca_expiredate != 0 ){
                    //$Cashecoupon->ca_status = 4;
                    //$Cashecoupon->where("ca_code = '{$code}'")->save();
                    $this->error("代价券已过期","__APP__/cashecoupon/recharge");
                }
                $amount = $Cashecoupon->ca_amount;
                
                //个人账户操作            
                $User_account = M('user_account');
                $condition['u_id'] = $this->_session('f_userid');
                $moe = $User_account->where($condition)->getField('u_rcoin_av');
                $moe += $amount;
                $User_account->u_rcoin_av = $moe;
                if( $User_account->where($condition)->save() ){
                    //代金券状态操作
                    $Cashecoupon->ca_status = 3;
                    $Cashecoupon->ca_usedate = get_now();
                    $Cashecoupon->where("ca_code = '{$code}'")->save();
                    //日志记录
                    $Logcashecoupon = M('log_cashecoupon');
                    $Logcashecoupon->cashcode = $code;
                    $Logcashecoupon->userid = $this->_session('f_userid');
                    $Logcashecoupon->usedate = get_now();
                    $Logcashecoupon->amount = $amount;
                    $Logcashecoupon->add();
            
                    $this->success("成功充值 $amount 元","__APP__/sales/ordermanage");
                    //$this->success("充值成功","__APP__/cashecoupon/recharge");
                }
                else{
                    $this->error("充值失败，请联系可客服");
                }
            
            }
            else{
                $this->error("错误参数:此情况永远不会出现","__APP__/cashecoupon/recharge");
            }
        }
        $this->assign('vfysts',$this->_session('vfyerror')>2?1:0);
        //dump($this->_session('vfyerror'));
        $this->assign('cacode',$this->_get('id'));
        $this->_renderPage();
        
    }

    /*
     * 个人代金券展示
     * 
     */
    public function showca(){
        //$smarty = new Smarty();
        $Cashecoupon = M('cashecoupon');
        $User = M('users');
        $u_id = $this->_session('f_userid');
        $User->getByU_id($u_id);
        
        $arr = $Cashecoupon->where("ca_email = '{$User->u_email}'")->select();
        $num = count($arr);
        
        $this->assign('list',$arr);
        $this->assign('num1',$num);
        
        
        
        $this->_renderPage();

    }
    public function verify(){
        import('ORG.Util.Image');
        Image::buildImageVerify(4,1,png,30,36,verify); 
    }
    /*
     * 预览AJAX
     */
    public function caview(){
        $code = str_replace(array("/r","/n"), "", $this->_post('id'));
        $Cashecoupon = M('cashecoupon');
        if( $Cashecoupon->getByCa_code($code) == "" ){
            //$this->error("优惠券不存在");
            $data['info'] = "0";
            $data['content'] = "优惠券不存在";
            $this->ajaxReturn($data);
        }
        elseif($Cashecoupon->ca_status == 3){
            //$this->error("代价券已被充值");
            $data['info'] = "3";
            $data['content'] = "优惠券已被充值";
            $this->ajaxReturn($data);
        }
        elseif($Cashecoupon->ca_expiredate < get_now() && $Cashecoupon->ca_expiredate != 0 ){
            //$this->error("代价券已过期");
            $data['info'] = "4";
            $data['content'] = "代价券已过期";
            $this->ajaxReturn($data);
        }
        elseif( $Cashecoupon->ca_status == 1 ){
            //$this->error("未发放");
            $data['info'] = "1";
            $data['content'] = "未发放";
            $this->ajaxReturn($data);
        }
        else{
            $data['info'] = "2";
            $data['content'] = "代金券的金额为￥";
            $data['content'] .= $Cashecoupon->ca_amount;
            $data['content'] .= ", 你确定要充值到目前登录账号吗？";
            $this->ajaxReturn($data);
        }
        

    }
    public function test(){
        if($this->isPost()){
            $r = str_replace(array("/n","/r"), "", $this->_post('n1'));
            dump(trim($r));
        }
        $this->display();
    }
    /*$code $to
     * 赠送代金券
     */
    public function giveca(){
        if($this->isPost()){
            $User = M('users');
            $condition['u_email'] = $this->_post('emailaddr');
            $arr = $User->where($condition)->select();
            
            if($arr != NULL){
                $code = $this->_post('cou_num');
                $Cashecoupon = M('cashecoupon');
                $Cashecoupon->getByCa_code($code);
                if($Cashecoupon->ca_status == 2){
                    if($Cashecoupon->ca_expiredate < get_now() && $Cashecoupon->expiredate != 0){
                        $this->error("代金券过期");
                    }
                    else{
                        $to = $this->_post('emailaddr');
                        $toname = $to;
                        $nickname = $this->_session('f_nickname');
                        $title = "你的好友".$this->_session('nickname');
                        $title .= "赠送给你一张代金券";
                        $amount = $Cashecoupon->ca_amount;
                        $content = "
                            <!doctype html>
<html>
<head>
<meta charset='utf-8'>
<title>代金券</title>
</head>

<body>
<!--Email 插入代码(下文中注解为变量位置) START-->
<table style='width:460px;padding:20px 10px;margin:0 auto; font-family:Microsoft yahei, Tahoma, Verdana, Arial;border:1px solid #CECECE;padding:40px;box-shadow: 5px 5px 45px #999;text-align:left;font-weight:normal;'>

<!--接受者用户名(暂时由Doge代替) START-->
<tr><th style='font-weight:normal;font-size:14px;color:#999;'>尊敬的$to</th></tr>
<!--接受者用户名 END-->

<tr><th style='font-weight:normal;font-size:14px;color:#999;'>&nbsp;&nbsp;&nbsp;&nbsp;$nickname 赠给您一张3DCity的代金券，欢迎到&nbsp;<a style='color:#EA572B;text-decoration:none;' href='http://www.3dcity.com'>3dcity.com</a>&nbsp;使用以下代金券 </th></tr>
<tr><th style='height:300px;'><img src='http://www.3dcity.com/doge/imgs/emailCoupon.jpg' width='450'/></th></tr>

<!--代金券面值(暂时由100代替） START-->
<tr><th><h2 style='color:#FFF;font-size:35px;font-weight:normal;text-shadow: 2px 2px 10px #999;margin-top:-190px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b style='font-size:20px;font-weight:normal;'>￥</b>$amount</h2></th></tr>
<!--代金券面值 END-->

<tr><th style='font-size:14px;text-align:center;font-weight:normal;font-size:14px;color:#999;'>代金券编码</th></tr>

<!--代金券编码 START-->
<tr><th span style='display:block;border:1px solid #999;font-size:14px;text-align:center;font-weight:normal;font-size:14px;height:45px;line-height:45px;color:#999;'>$code</th></tr>
<!--代金券编码 END-->
<tr><th><a style='display:block;background: #CCC; border-color: #de4a4a;background-color: #EA572B;color: #fff!important;width:100px;margin:15px auto 0;text-decoration:none;text-align:center;width:150px;height:50px;line-height:50px;' href='http://www.3dcity.com/user.php/cashecoupon/recharge/id/$code'>点击充值</a></th></tr>
<tr><th><br></th></tr>
<tr><th></th></tr>
<tr><th></th></tr>
<tr><th style='font-size:14px;'>如果以上按钮无法打开，请将以下链接复制到浏览器地址栏中打开：</th></tr>

<!--链接地址 START-->
<tr><th><a href='' style='color:#EA572B;text-decoration:none;font-size:14px;' target='_blank'>http://www.3dcity.com/user.php/cashecoupon/recharge/id/$code</a></th></tr>
<!--链接地址 END-->
</table>
<table>
<tr><th></th></tr>
<tr><th></th></tr>

</table>
<!--Email 插入代码 END-->

</body>
</html>
                            ";
                        if( think_send_mail($to,$toname,$title,$content)){
                            $this->success("赠送成功");
                        }
                        
                    }
                    
                }
                else{
                    $this->error('该代金券状态不允许赠送');
                }
                
            }
            else{$this->error('用户邮箱错误');}
        }
        else{
            $this->error('非法操作');
        }
    }

    
}