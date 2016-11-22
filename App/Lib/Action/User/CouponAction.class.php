<?php
class CouponAction extends CommonAction{
    public function __construct(){
        parent::__construct();
        
        if(!$this->_isLogin()){
            $this->_needlogin();
        }
    }
    public function mycoupon(){
        $User = M('users');
        $User->getByU_id($this->_session('f_userid'));
        
        $Coupon = new CouponModel();
        if($User->u_email){
            $con['ec_owner']    = $User->u_email;
        }else{
            $con['ec_owner']    = $User->u_mob_no;
        }
        //$con['ec_owner']    = $User->u_email;
        $con['et_private']  = 2;
        $arr = $Coupon->relation(true)->where($con)->select();
        $this->assign("list",$arr);

        //$condition['ec_status'] = 1;
        //$condition['et_private'] = 1;
        //$ar = $Coupon->relation(true)->where($condition)->select();  
        $sql = "select t1.*,t2.* from tdf_coupon t1 left join tdf_coupon_type t2 on t2.et_id = t1.etId where t2.et_private = 1 and t1.ec_status = 1";
        $ar = $Coupon->query($sql);     
        $this->assign('commoncoupon',$ar);
        
        $this->assign("getnow",get_now()); 
        
        $this->assign('num1',count($arr));
        $this->assign('num2',count($ar));
        $this->assign('num',count($arr)+count($ar));
        $this->_renderPage();
        
        
       
    }
    
    /**
     * 优惠券是否属于活动范围
     * 
     * miaomin added@2014.8.4
     * 
     * param string $code
     */
    public function isCouponBelongSP($code){
        $Coupon = new CouponModel();
        if( $Coupon->relation(true)->getByEc_code($code)){
            $todayStr = date('Y-m-d');
            $SPPropM = new SPPropModel();
            $condition = array(
                $SPPropM->F->SPITEMID => 9,
                $SPPropM->F->SPPVAL => $Coupon->etId,
                'tdf_spmain.spm_begin' => array(
                    'elt',
                    $todayStr
                ),
                'tdf_spmain.spm_end' => array(
                    'egt',
                    $todayStr
                )
            );
            $res = $SPPropM->join(" tdf_spmain ON tdf_spprop.spm_id = tdf_spmain.spm_id")->where($condition)->find();
            if ($res){
                return $res;
            }
        }
        return false;
    }
    
    public function isPrepiadPidBelongSPProduct($spid){
        $SPProductM = new SPProductModel();
        $condition = array(
          $SPProductM->F->SPID => $spid  
        );
        $SPPRes = $SPProductM->where($condition)->select();
        if($SPPRes){
            foreach($SPPRes as $key=>$val){
                $res[] = $val[$SPProductM->F->PID];
            }
            
            return $res;
        }
        return false;
    }

    /*
     * ajax使用优惠券
     */
    public function pay(){
        if($this->isPost()){
            $code = trim($this->_post('code'));
            $total = $this->getprice($this->_post('orderid'));
            $Coupon = new CouponModel();
            if( $Coupon->relation(true)->getByEc_code($code)){
                if($Coupon->et_private == 1){
                    if($Coupon->ec_status != 1){
                        die("该码不在启用状态");
                    }
                    else{
                        /**
                         * miaomin edited@2015.10.16
                         * 优惠券定义的字典数据不应该在使用中发生变化
                         * 使用次数字段必须复制到优惠券表中去
                         * 这里的判断也应该是优惠券表而不是字典定义表
                         */
                        // if($Coupon->et_usecount<1){
                        if($Coupon->ec_uc<1){
                            //公开优惠券用完后，关闭可使用状态
                            $da1['ec_status'] = 0;
                            $Coupon->where("ec_code = '{$code}'")->save($da1);//如果执行到这里，是否会出现错误??
                            die('优惠券已被抢购使用完毕');
                        }
                    }
                    if($Coupon->et_expiredate < get_now() && $Coupon->et_expiredate != 0){
                        die('优惠券已经过期');
                    }
                    else{
                        //判断是否余额支付过
                        if( $this->isaccounted($this->_post('orderid')) ){
                            $data['status'] = "failed";
                            $data['info'] = "被余额支付过";
                            $this->ajaxReturn($data,'JSON');
                            die('被余额支付过');
                        }
                        //判断订单是否使用过优惠券
                        if( $this->islogged($this->_post('orderid')) ){
                            $data['status'] = "failed";
                            $data['info'] = "只能使用一个优惠券码";
                            $this->ajaxReturn($data,'JSON');
                            die('只能使用一个优惠券码');
                        }
                        //判断订单金额是否符合
                        if($total<$Coupon->et_limitamount){
                            $data['status'] = "failed";
                            $data['info'] = "订单金额超过优惠券的限制金额";
                            $this->ajaxReturn($data,'JSON');
                            die('订单金额超过优惠券的限制金额');
                        }
                        //更新公开券使用次数
                        /**
                         * miaomin edited@2015.10.16
                         * 优惠券定义的字典数据不应该在使用中发生变化
                         * 使用次数字段必须复制到优惠券表中去
                         * 这里的判断也应该是优惠券表而不是字典定义表
                         */
                        // $CT = M('coupon_type');
                        $ctm = new CouponModel();
                        $da['ec_uc'] = $Coupon->ec_uc-1;
                        $ctm->where("ec_id = '{$Coupon->ec_id}'")->save($da);
                        //$Coupon->relation(true)->where("ec_code = '{$code}'")->save($da);
                        
                        $data['status'] = "success";
                        $data['details'] = $Coupon->et_type;
                        $data['amount'] = $this->savemoe($total,$Coupon->et_type,$Coupon->et_type==1?$Coupon->et_percent:$Coupon->et_amount,$Coupon->et_mamount);
                        //更新订单记录
                        if( $this->updateorder($this->_post('orderid'), $data['amount']) ){
                            //添加记录
                            $this->logcoupon($code,$data['amount'],$this->_post('orderid'));
                        }
                        $this->ajaxReturn($data,'JSON');
                    }
            
                }
                elseif($Coupon->et_private == 2){
                    // miaomin edited@2015.10.21
                    // 重要的BUG:专属优惠码居然没有对绑定账号做判定
                    if ($Coupon->ec_owner){
                        $UM = new UsersModel();
                        $umRes = $UM->getUserByMobnoEmail($Coupon->ec_owner);
                        if ($umRes){
                            if ($umRes['u_id'] != $_SESSION['f_userid']){
                                $data['status'] = "failed";
                                $data['info'] = "该码没有绑定相关账号";
                                $this->ajaxReturn($data,'JSON');
                                die("该码没有绑定相关账号");
                            }
                        }
                    }else{
                        $data['status'] = "failed";
                        $data['info'] = "该码没有绑定相关账号";
                        $this->ajaxReturn($data,'JSON');
                        die("该码没有绑定相关账号");
                    }
                    if($Coupon->ec_status != 1){
                        die("该码不在启用状态");
                    }
                    if($Coupon->et_expiredate < get_now() && $Coupon->et_expiredate != 0){
                        die('优惠券已经过期');
                    }
                    else{
                        //判断是否余额支付过
                        if( $this->isaccounted($this->_post('orderid')) ){
                            $data['status'] = "failed";
                            $data['info'] = "被余额支付过";
                            $this->ajaxReturn($data,'JSON');
                            die('被余额支付过');
                        }
                        //判断订单是否使用过优惠券
                        if( $this->islogged($this->_post('orderid')) ){
                            $data['status'] = "failed";
                            $data['info'] = "只能使用一个优惠券码";
                            $this->ajaxReturn($data,'JSON');
                            die('只能使用一个优惠券码');
                        }
                        //判断订单金额是否符合
                        if($total<$Coupon->et_limitamount){
                            $data['status'] = "failed";
                            $data['info'] = "订单金额超过优惠券的限制金额";
                            $this->ajaxReturn($data,'JSON');
                            die('订单金额超过优惠券的限制金额');
                        }
                        //更新私有券的状态
                        $da2['ec_status'] = 2;
                        $Coupon->where("ec_code = '{$code}'")->save($da2);
                        
                        $data['status'] = "success";
                        $data['details'] = $Coupon->et_type;
                        $data['amount'] = $this->savemoe($total,$Coupon->et_type,$Coupon->et_type==1?$Coupon->et_percent:$Coupon->et_amount,$Coupon->et_mamount);
                        //更新订单记录
                        if( $this->updateorder($this->_post('orderid'), $data['amount']) ){
                            //添加记录
                            $this->logcoupon($code,$data['amount'],$this->_post('orderid'));
                        }
                        $this->ajaxReturn($data,'JSON');
                    }
                }
                else{
                    die('error:invaild');
                }
            }
            else{
                die('not a coupon code');
            }
        }
        else{
            echo "#######";
        }
        
        
    }
    /*
     * ajax查询
     */
    public function check(){
        if( $this->_session('errnum') == NULL){
            session('errnum',0);
        }
        if($this->_session('errnum') > 2){
            if( $this->_session('verify') != md5($this->_post('verify')) ){
                $data['status'] = "failed";
                $data['info'] = "验证码错误";
                $data['errnum'] = session('errnum');
                $this->ajaxReturn($data,'JSON');
                die("验证码错误");
            }
        }
        if($this->isPost()){
            $code = trim($this->_post('code'));
            $total = $this->getprice($this->_post('orderid'));
            $Coupon = new CouponModel();
            if( $Coupon->relation(true)->getByEc_code($code)){
                if($Coupon->et_private == 1){
                    // 公开优惠码
                    if($Coupon->ec_status != 1){
                        $data['status'] = "failed";
                        $data['info'] = "该码不在启用状态";
                        $this->ajaxReturn($data,'JSON');
                        die("该码不在启用状态");
                    }
                    else{
                        /**
                         * miaomin edited@2015.10.16
                         * 优惠券定义的字典数据不应该在使用中发生变化
                         * 使用次数字段必须复制到优惠券表中去
                         * 这里的判断也应该是优惠券表而不是字典定义表
                         */
                        // if($Coupon->et_usecount<1){
                        if($Coupon->ec_uc<1){
                            $da1['ec_status'] = 0;
                            $Coupon->where("ec_code = '{$code}'")->save($da1);
                            $data['status'] = "failed";
                            $data['info'] = "优惠券已被抢购使用完毕";
                            $this->ajaxReturn($data,'JSON');
                            die('优惠券已被抢购使用完毕');
                        }
                    }
                    if($Coupon->et_expiredate < get_now() && $Coupon->et_expiredate != 0){
                        $data['status'] = "failed";
                        $data['info'] = "优惠码已经过期";
                        $this->ajaxReturn($data,'JSON');
                        die('优惠券已经过期');
                    }
                    else{
                        //判断是否余额支付过
                        if( $this->isaccounted($this->_post('orderid')) ){
                            $data['status'] = "failed";
                            $data['info'] = "被余额支付过";
                            $this->ajaxReturn($data,'JSON');
                            die('被余额支付过');
                        }
                        //判断订单是否使用过优惠券
                        if( $this->islogged($this->_post('orderid')) ){
                            $data['status'] = "failed";
                            $data['info'] = "只能使用一个优惠券码";
                            $this->ajaxReturn($data,'JSON');
                            die('只能使用一个优惠券码');
                        }
                        //判断订单金额是否符合
                        if($total<$Coupon->et_limitamount){
                            $data['status'] = "failed";
                            $data['info'] = "订单金额超过优惠券的限制金额";
                            $this->ajaxReturn($data,'JSON');
                            die('订单金额超过优惠券的限制金额');
                        }
                        $data['status'] = "success";
                        $data['info'] = "优惠码使用成功";
                        $data['details'] = $Coupon->et_type;
                        $data['amount'] = $this->savemoe($total,$Coupon->et_type,$Coupon->et_type==1?$Coupon->et_percent:$Coupon->et_amount,$Coupon->et_mamount);
                        $this->ajaxReturn($data,'JSON');
                    }
    
                }
                elseif($Coupon->et_private == 2){
                    // 专属优惠码
                    // miaomin edited@2015.10.21
                    // 重要的BUG:专属优惠码居然没有对绑定账号做判定
                    if ($Coupon->ec_owner){
                        $UM = new UsersModel();
                        $umRes = $UM->getUserByMobnoEmail($Coupon->ec_owner);
                        if ($umRes){
                            if ($umRes['u_id'] != $_SESSION['f_userid']){
                                $data['status'] = "failed";
                                $data['info'] = "该码没有绑定相关账号";
                                $this->ajaxReturn($data,'JSON');
                                die("该码没有绑定相关账号");
                            }
                        }
                    }else{
                        $data['status'] = "failed";
                        $data['info'] = "该码没有绑定相关账号";
                        $this->ajaxReturn($data,'JSON');
                        die("该码没有绑定相关账号");
                    }
                    
                    if($Coupon->ec_status != 1){
                        $data['status'] = "failed";
                        $data['info'] = "该码不在启用状态";
                        $this->ajaxReturn($data,'JSON');
                        die("该码不在启用状态");
                    }
                    if($Coupon->et_expiredate < get_now() && $Coupon->et_expiredate != 0){
                        $data['status'] = "failed";
                        $data['info'] = "优惠券已经过期";
                        $this->ajaxReturn($data,'JSON');
                        die('优惠券已经过期');
                    }
                    else{
                        //判断是否余额支付过
                        if( $this->isaccounted($this->_post('orderid')) ){
                            $data['status'] = "failed";
                            $data['info'] = "被余额支付过";
                            $this->ajaxReturn($data,'JSON');
                            die('被余额支付过');
                        }
                        //判断订单是否使用过优惠券
                        if( $this->islogged($this->_post('orderid')) ){
                            $data['status'] = "failed";
                            $data['info'] = "只能使用一个优惠券码";
                            $this->ajaxReturn($data,'JSON');
                            die('只能使用一个优惠券码');
                        }
                        //判断订单金额是否符合
                        if($total<$Coupon->et_limitamount){
                            $data['status'] = "failed";
                            $data['info'] = "订单金额超过优惠券的限制金额";
                            $this->ajaxReturn($data,'JSON');
                            die('订单金额超过优惠券的限制金额');
                        }
                        $data['status'] = "success";
                        $data['details'] = $Coupon->et_type;
                        $data['amount'] = $this->savemoe($total,$Coupon->et_type,$Coupon->et_type==1?$Coupon->et_percent:$Coupon->et_amount,$Coupon->et_mamount);
                        $this->ajaxReturn($data,'JSON');
                    }
                }
                else{
                    $data['status'] = "failed";
                    $data['info'] = "优惠券种类不合法";
                    $this->ajaxReturn($data,'JSON');
                    die('优惠券种类不合法');
                }
            }
            else{
                session('errnum',$this->_session('errnum')+1);
                $data['errnum'] = session('errnum');
                $data['status'] = "failed";
                $data['info'] = "优惠码不存在";
                $this->ajaxReturn($data,'JSON');
            }
        }
        else{
            $data['status'] = "failed";
            $data['info'] = "请求错误";
            $this->ajaxReturn($data,'JSON');
        }
    }
    /*
     * 计算优惠多少钱
     * 
     * miaomin added@2015.7.31
     * miaomin edited@2016.6.13 //增加活动规则188抵500
     * param int $total 总金额
     * param int $type 折扣还是立减还是活动抵扣
     * param int $num 优惠金额
     * param int $pnum 活动金额
     */
    private function savemoe($total,$type,$num,$pnum=0){
        $moe = 0;
        $minpay = 0.01;
        if($type == 1){
            // 折扣
            $moe = $total*(100-$num)/100;
            // 如果是全额折扣为了完成整个支付流程会留0.1元
            if ($moe == $total){
                $moe = $moe - $minpay;
                return $moe;
            }
            return round($moe);
        }
        elseif($type == 2){
            // 立减
            // 如果是全额折扣为了完成整个支付流程会留0.1元
            if ($num >= $total){
                $moe = $total - $minpay;
                return $moe;
            }
            $moe = $num;
            return round($moe);
        }elseif($type == 3){
//            echo 'total:' . $total;
//            echo 'num:' . $num;
//            echo 'pnum' . $pnum;
            // 活动抵扣例如订单金额不足500元实际支付188元，高于500元直接支付差额+188元
            if ($total >= $num){    // 订单金额大等于500，节约金额为500-188
                $moe = $num - $pnum;
            }elseif (($total < $num) && ($total > $pnum)){  //订单金额小于500大于188，节约金额为订单金额-188
                $moe = $total - $pnum;
            }else{  // 订单金额小等于188,不享受优惠按实际金额支付
                $moe = 0;
            }
//            echo 'moe:' . $moe;
            return round($moe);
        }
    }
    /*
     * 验证码
     */
    Public function verify(){
        import("ORG.Util.Image");
        Image::buildImageVerify();
    }
    /*
     * miaomin edited@2015.7.31
     * 
     * 查询享受折扣的订单金额
     * 
     * 原先的享受折扣的订单金额是订单总金额
     * 由于加入了活动可能有部分优惠码只能对某一款商品进行优惠
     * 而不是整个订单的金额
     */
    public function getprice($id){
        $Userprepaid = M('user_prepaid');
        $arr = $Userprepaid->getByUp_orderid($id);
        
        //获取优惠码
        $code = trim($this->_post('code'));
        if ($code){
            $couponSPRes = $this->isCouponBelongSP($code);
            // print_r($couponSPRes);
            if ($couponSPRes){
                $spProdutList = $this->isPrepiadPidBelongSPProduct($couponSPRes['spm_id']);
                // print_r($spProdutList);
                if ($spProdutList){
                    $totalRes = 0;
                    $UPDM = new UserPrepaidDetailModel();
                    $updmRes = $UPDM->getPrepaidDetailByUpid($arr['up_id']);
                    $odPList = unserialize($updmRes['up_product_info']);
                    // print_r($odPList);
                    foreach ($odPList as $key=>$val){
                        //非DIY
                        if ($val['p_producttype'] == 5){
                            if (in_array($val['p_id'], $spProdutList) || in_array($val['p_belongpid'], $spProdutList)){
                                $totalRes += $val['p_price'] * $val['p_count'];
                            }   
                        }elseif ($val['p_producttype'] == 4){
                            //DIY
                            //根据P_DIY_ID去TDF_USER_DIY找到P_DIY_CATE_CID再根据P_DIY_CATE_CID找BELONGPID
                            import ( 'App.Model.CartItem.AbstractCartItem' );
                            import ( 'App.Model.CartItem.CartItemDiyModel' );
                            $belongPID = CartItemDiyModel::getBelongPid($val['p_id']);
                            if($belongPID){
                                if (in_array($belongPID, $spProdutList)){
                                    $totalRes += $val['p_price'] * $val['p_count'];
                                }   
                            }
                        }
                    }
                    return $totalRes;
                }
            }
        }
        if($arr){
            return $arr['up_amount'];
        }
        else{
            die("error");
        }
    }
    /*
     * 更新Userprepaid表
     */
    private function updateorder($orderid,$moe){
        $Userprepaid = M('user_prepaid');
        $data['up_amount'] = $Userprepaid->up_amount - $moe;    //实际支付
        $data['up_amount_coupon'] = $moe;   //优惠金额
        $data['up_amount_total'] = $data['up_amount']+$data['up_amount_coupon'];    //初始金额
        $data['up_amount_total'] += $Userprepaid->up_amount_account;
        if( $Userprepaid->where("up_orderid = '{$orderid}'")->save($data) ){
            return true;
        }
        else{
            return false;
        }
    }
    /*
     * 记录该优惠券使用情况
     */
    private function logcoupon($code,$amount,$orderid){
        $Log = M('log_coupon');
        $data['log_eccode'] = $code;
        $data['log_ecamount'] = $amount;
        $data['log_orderid'] = $orderid;
        $data['log_usedate'] = get_now();
        $Log->add($data);
    }
    /*
     * 判断记录表中是否有该订单优惠券
     */
    public function islogged($orderid){
        $Log = M('log_coupon');
        $condition = array(
          'log_orderid' => $orderid,
          'log_eccode' => array('neq','5CFC86C351')
        );
        // if( $Log->getByLog_orderid($orderid) != NULL ){
        if ($Log->where($condition)->select() != NULL){
            return true;
        }
        else{
            return false;
        }
    } 
    /*
     * 判断是否余额支付过
     */
    private function isaccounted($orderid){
        $Account = M('user_prepaid');
        $Account->getByUp_orderid($orderid);
        if( $Account->up_amount_account != 0 ){
            return true;
        }
        else{
            return false;
        }
    }
    /*
     * 生成并且领取优惠券,9折,无订单金额限制;
     */
    private function generate(){
        
        $User = M('users');
        $User->getByU_id(session('f_userid'));
        if( $this->hasowned( $User->u_email)){
            $Coupon = new CouponModel();
            $Coupon->create($this->_get());
            $Coupon->ec_percent = 10;
            $Coupon->ec_type = 1;
            $Coupon->ec_private = 2;
            $Coupon->ec_status = 1;
            $Coupon->ec_owner = $User->u_email;
            $Coupon->ec_expiredate = "2015-03-15 00:00:00";
            $Coupon->ec_limit = 0;
            $Coupon->ec_name = "valentinesday";
            $Coupon->add();
            echo $Coupon->ec_code;
            echo "成功领取";
        }
        else{
            echo "已经领取过";
        }
        $Coupon = M('coupon');
        $condition['ec_owner'] = $User->u_email;
        $condition['ec_name'] = "valentinesday";
        echo $Coupon->where($condition)->getField('ec_code');
    }
    /*
     * 检测是否领取过valentinesday
     */
    private function hasowned($user){
        $Coupon = M('coupon');
        $condition['ec_name'] = "valentinesday";
        $condition['ec_owner'] = $user;
        $arr = $Coupon->where($condition)->count();
        if($arr > 0){
            return false;
        }
        else{
            return true;
        }
    }
    public function xx(){
        $val = 5.12345;
        echo round($val);
    }

    
    
    
    
    
    
    
    
}