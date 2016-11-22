<?php
class CashecouponAction extends CommonAction{
    
    public function __construct(){
        parent::__construct();
    }
    /*
     * 后台管理优惠券首页
     */
    public function index(){
        $this->display();
    }
    /*
     * 后台查看代金券
     */
    public function showlists($orderby = 'ca_status',$desc=''){        
        //$map['ca_status'] = array('neq',3);
        $admin = $this->_session('authId');
        $Cashecoupon = new CashecouponModel();
        //$count = count($Cashecoupon->where("ca_operator = $admin and ca_status != 3")->select());
        $count = count($Cashecoupon->where("ca_status != 3")->select());
        import("ORG.Util.Page");
        $Page = new Page($count,15);
        $show = $Page->show();
        $arr = $Cashecoupon->order($orderby." ".$desc)->limit($Page->firstRow.','.$Page->listRows)->where("ca_status != 3")->select();
        $this->assign('list',$arr);
        $this->assign('page',$show);
        $this->display();
        
        //var_dump($arr[0]['ca_code']);
    }
    /*
     * 后台生成代金券
     */
    public function generate(){
        if($this->isPost()){
            $num = intval ( $this->_post ( 'no' ) );
            if( ($num) && $num <=200 ){
                $this->createCashEcoupon($num);
                $this->success("添加成功","__URL__/showlists");
            }
            else{
                $this->error("数量不能大于200");
            }
          
        }
        else{
            $this->display();
        }
        
    }
    /*功能：
     * 生成代金券
     */
    public function createCashEcoupon($no = 0){
        $Cashecoupon = new CashecouponModel();
        for($i=0;$i<$no;$i++){
            $Cashecoupon->create($this->_post());
            $Cashecoupon->ca_operator = $this->_session('authId');
            $Cashecoupon->ca_status =1;
            if( $this->_post('expiredate') < get_now() && $this->_post('expiredate') != 0){
                $this->error("不能生成已过期的优惠券");
            }
            $Cashecoupon->add();
        }
    }
    /*
     * 查询代金券状态
     */
    public function cadetails(){
        if($this->isPost()){
            $field = $this->_post('xx');
            $code = $this->_post('code');
            $Cashecoupon = M('cashecoupon');
            //$arr = $Cashecoupon->where("{$field} = '{$code}' and ca_operator = '{$this->_session('authId')}'")->select();
            $arr = $Cashecoupon->where("{$field} = '{$code}'")->select();
            //echo $Cashecoupon->getLastSql();
            //echo $this->_post('xx');
            $this->assign('list',$arr);
        }
        $this->display();
    }

    /*
     * 删除单个
     */
    public function del($id){
        $Cashecoupon = M('cashecoupon');
        $condition['ca_id'] = $id;
        $Cashecoupon->getByCa_id($id);
        if($Cashecoupon->ca_status != 3){
            $Cashecoupon->where($condition)->delete();
            $this->success('删除成功','__URL__/showlists');
        }
        else{
            $this->error("操作失败");
        }

    }
    /*
     * 批量删除
     */
    public function droplist(){
        $arr = $this->_post('n');
        $Cashecoupon = M('cashecoupon');
        foreach ($arr as $id){
            $Cashecoupon->getByCa_id($id);
            if( $Cashecoupon->ca_status != 3){
                $Cashecoupon->where("ca_id='{$id}'")->delete();
                $this->success("删除成功 ".$Cashecoupon->ca_code);
            }
            else{
                $this->error("不能删除 ".$Cashecoupon->ca_code);
            }
    
        }
    }
    /*
     * 批量发送优惠券
     */
    
    public function sendmail(){
        $to = $this->_post('mailadr');
        if($to == ""){
            $this->error("email不能为空");
        }
        
        $Cashecoupon = M('cashecoupon');
        $lists = array();
        $errorlists = array();
        $arr = $this->_post('n');
        foreach ($arr as $id){
            $Cashecoupon->getByCa_id($id);
            if( $Cashecoupon->ca_status == 1){
                $data['ca_status'] = 2;
                $data['ca_sentwho'] = 1;
                $data['ca_email'] = $to;
                $Cashecoupon->where("ca_id = '{$id}'")->save($data);
                $lists[$Cashecoupon->ca_code] = $Cashecoupon->ca_amount;
            }
            else{
                $errorlists[] = $Cashecoupon->ca_code;
            }
    
        }
        //邮箱部分
        $content = "";
        foreach($lists as $key=>$amount){
            $this->mailecoupon( $to,$key,$amount);
        }
        
        $this->success( "发送完成".count($lists)."个，失败".count($errorlists)."个。" );
    }
    /*
     * 邮箱功能模块
     */
    private function mailecoupon($emailadr,$key,$amount){
        $to = $emailadr;
        $toname = $to;
        $title = "曼恒蔚图发来的代金券";
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

<tr><th style='font-weight:normal;font-size:14px;color:#999;'>&nbsp;&nbsp;&nbsp;&nbsp;曼恒蔚图(<a style='color:#EA572B;text-decoration:none;' href='http://www.3dcity.com'>www.3dcity.com</a>) 赠给您一张3DCity的代金券，欢迎到&nbsp;<a style='color:#EA572B;text-decoration:none;' href='http://www.3dcity.com'>3dcity.com</a>&nbsp;使用以下代金券 </th></tr>
<tr><th style='height:300px;'><img src='http://www.3dcity.com/doge/imgs/emailCoupon.jpg' width='450'/></th></tr>

<!--代金券面值(暂时由100代替） START-->
<tr><th><h2 style='color:#FFF;font-size:35px;font-weight:normal;text-shadow: 2px 2px 10px #999;margin-top:-190px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b style='font-size:20px;font-weight:normal;'>￥</b>$amount</h2></th></tr>
<!--代金券面值 END-->

<tr><th style='font-size:14px;text-align:center;font-weight:normal;font-size:14px;color:#999;'>代金券编码</th></tr>

<!--代金券编码 START-->
<tr><th span style='display:block;border:1px solid #999;font-size:14px;text-align:center;font-weight:normal;font-size:14px;height:45px;line-height:45px;color:#999;'>$key</th></tr>
<!--代金券编码 END-->
<tr><th><a style='display:block;background: #CCC; border-color: #de4a4a;background-color: #EA572B;color: #fff!important;width:100px;margin:15px auto 0;text-decoration:none;text-align:center;width:150px;height:50px;line-height:50px;' href='http://www.3dcity.com/user.php/cashecoupon/recharge/id/$key'>点击充值</a></th></tr>
<tr><th><br></th></tr>
<tr><th></th></tr>
<tr><th></th></tr>
<tr><th style='font-size:14px;'>如果以上按钮无法打开，请将以下链接复制到浏览器地址栏中打开：</th></tr>

<!--链接地址 START-->
<tr><th><a href='' style='color:#EA572B;text-decoration:none;font-size:14px;' target='_blank'>http://www.3dcity.com/user.php/cashecoupon/recharge/id/$key</a></th></tr>
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
        
        
        think_send_mail($to,$toname,$title,$content);
    }

    /*
     * 单个激活
     */
    public function marksent($id){
        $Cashecoupon = M('cashecoupon');
        $Cashecoupon->getByCa_id($id);
        if( $Cashecoupon->ca_status == 1){
             $data['ca_status'] = 2;
             $data['ca_sentwho'] = 2;
             $Cashecoupon->where("ca_id='{$id}'")->save($data);
             //echo $Cashecoupon->getLastSql();
             $this->success("成功");
        }
        else{
            $this->error("失败");
        }
        
    }
    /*
     * 批量激活
     */
    public function activelist(){
        $arr = $this->_post('n');
        $Cashecoupon = M('cashecoupon');
        foreach ($arr as $id){
            $Cashecoupon->getByCa_id($id);
            if( $Cashecoupon->ca_status == 1){
                $data['ca_status'] = 2;
                $data['ca_sentwho'] = 2;
                $Cashecoupon->where("ca_id='{$id}'")->save($data);
                $this->success("激活成功 ".$Cashecoupon->ca_code);
            }
            else{
                $this->error("不能激活  ".$Cashecoupon->ca_code);
            }
        
        }
    }
    /*
    * 验证是否为站内邮箱
    */
    public function vfymail($amail){
        $User = M('users');
        if( $User->getByU_email($amail) != NULL){
            return true;
        }
        else{
            echo "xxxxxx";
            return false;
        }
    
    }
    /*
     * 批量站内激活
     * 
     */
    public function cityactivelist(){
        $amail = $this->_post('mailadr');
        if( !$this->vfymail($amail) ){
            $this->error("站内不存在该邮箱账户 $amail");
        }
        $arr = $this->_post('n');
        $Cashecoupon = M('cashecoupon');
        foreach ($arr as $id){
            $Cashecoupon->getByCa_id($id);
            if( $Cashecoupon->ca_status == 1){
                $data['ca_status'] = 2;
                $data['ca_sentwho'] = 3;
                $data['ca_email'] = $this->_post('mailadr');
                $Cashecoupon->where("ca_id='{$id}'")->save($data);
                $this->success("激活成功 ".$Cashecoupon->ca_code);
            }
            else{
                $this->error("不能激活  ".$Cashecoupon->ca_code);
            }
        
        }
    }
    public function showorderlists($bythis){
        
    }

    
    /*
     * 单个站内激活
     *
    public function citymarksent($id){
        $Cashecoupon = M('cashecoupon');
        $Cashecoupon->getByCa_id($id);
        if( $Cashecoupon->ca_status == 1){
            $data['ca_status'] = 2;
            $data['ca_sentwho'] = 3;
            $data['ca_email'] = $this->_post('citymail');
            $Cashecoupon->where("ca_id='{$id}'")->save($data);
            //echo $Cashecoupon->getLastSql();
            $this->success("成功");
        }
        else{
            $this->error("失败");
        }
    
    }
     
    /*内建：
     * 生成码
     *
    private function getCode($num=8){
        $data = substr(microtime(),2,6);
        //echo $data."\n";
        $data .=generate_password(26);
        //echo $data."\n";
        $data = md5($data);
        //echo $data."\n";
        $data = substr($data, mt_rand(0,strlen($data)-$num), $num);
        $data = strtoupper($data);
        return $data;
    }*/
    
} 
?>