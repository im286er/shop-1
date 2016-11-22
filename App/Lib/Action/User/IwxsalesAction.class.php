<?php
/**
 * Created by PhpStorm.
 * User: guolixun
 * Date: 2016/10/13
 * Time: 14:24
 */
class IwxsalesAction extends CommonAction {
    /**
     * 构造函数
     */
    public function __construct() {
        //
        parent::__construct ();

        // 判断登录
        if (! $this->_isLogin ()) {
            $this->_needLogin ();
        }
    }
    public function index() {
        $this->_renderPage ();
    }
    public function sum() {
        $Sa = D ( 'UserAccount' );
        $UserAccountArr = $Sa->find ( $_SESSION ['f_userid'] );
        $u_rcoin_av_show = $UserAccountArr ['u_rcoin_av']; // 显示可用
        $this->assign ( 'showrcoin', $u_rcoin_av_show );
        if (IS_POST) {
            import ( 'ORG.Util.Page' ); // 导入分页类
            $begintime = $_POST ['begintime'];
            $endtime = $_POST ['endtime'];
            $sql_begintime = $begintime . " 00:00:00";
            $sql_endtime = $endtime . " 23:59:59";
            $UPM = new UserPrepaidModel ();

            $UPM->where ( 'up_uid="' . $_SESSION ['f_userid'] . '" and up_status=1 and up_dealdate >"' . $sql_begintime . '" and up_dealdate <"' . $sql_endtime . '"' )->select ();
            $count = $UPM->count ();
            $Page = new Page ( $count, 16 ); // 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig ( 'theme', '%first% %prePage% %upPage% %linkPage% %downPage% %nextPage% %end% <span class=title>%totalRow% %header%</span> <span>%nowPage%/%totalPage% 页</span>' );
            $show = $Page->show (); // 分页显示输出

            $list = $UPM->where ( 'up_uid="' . $_SESSION ['f_userid'] . '" and up_status=1 and up_dealdate >"' . $sql_begintime . '" and up_dealdate <"' . $sql_endtime . '"' )->order ( 'ctime desc' )->limit ( $Page->firstRow . "," . $Page->listRows )->select ();

            if ($list) {
                $showtables = "<div class='balance-tab'>";
                $showtables .= "<ul><li class='title'>";
                // $showtables.="<span class='f100'>ID</span>";
                $showtables .= "<span class='f200'>类型</span>";
                $showtables .= "<span class='f200'>时间</span>";
                $showtables .= "<span class='f100'>金额</span>";
                $showtables .= "<span class='f100'>状态</span>";
                $showtables .= "<span class='f80'>操作</span>";
                $showtables .= "</li>";

                foreach ( $list as $result ) {
                    $orderid = $this->encode_pass ( $result ['up_orderid'], $_SESSION ['f_userid'], 'encode' );
                    $showtables .= "<li>";
                    if ($result ['up_type'] == 1) {
                        $temp_pay_name = "购买模型 <a href=__APP__/user/sales/orderdetail/orderid/" . $orderid . ">详情</a>";
                    } elseif ($result ['up_type'] == 0) {
                        $temp_pay_name = "充值";
                    } elseif ($result ['up_type'] == 3) {
                        $temp_pay_name = "兑换积分";
                    } elseif ($result ['up_type'] == 4) {
                        $temp_pay_name = "账户余额支付";
                    }
                    // $temp_pay_name = ($result['up_type']==1) ? "购买模型 <a
                    // href='__APP__/mymodels/index'>详情</a>":"充值" ;
                    // $showtables.="<span class='f100'>".$result['up_id']."
                    // </span>";
                    $showtables .= "<span class='f200'>" . $temp_pay_name . "</span>";
                    $showtables .= "<span class='f200'>" . $result ['up_dealdate'] . "</span>";
                    $showtables .= "<span class='f100'>" . $result ['up_amount'] . "</span>";
                    $showtables .= "<span class='f100'>" . replace_int_vars ( $result ['up_status'] ) . "</span>";
                    $showtables .= "<span class='f80'><a href=__APP__/user/sales/orderdetail/orderid/" . $orderid . " target='_blank'>查看</a></span>";

                    $showtables .= "</li>";
                }
            } else {
                $showtables .= "<li>Sorry，无记录！</li>";
            }
            $showtables .= "</ul></div>";
            /*
             * <div class="balance-tab"> <ul> <li class="title"> <span
             * class="f1">类型</span> <span class="f2">时间</span> <span
             * class="f3">金额</span> <span class="f4">描述</span> </li> <li> <span
             * class="f1">消费</span> <span class="f2">2013-02-01 16:36:48</span>
             * <span class="f3">￥85</span> <span class="f4"><a href=""#>购买
             * 3D打印产品 [迷失的驯鹿]</a></span> </li> </ul> </div>
             */
        } else {
            $showtables = "";
            $begintime = date ( "Y-m-d", time () - 1 * 3600 * 24 );
            $endtime = date ( "Y-m-d", time () );
        }

        $this->assign ( 'showtitle', "财务管理-账户余额" );
        $this->assign ( 'begintime', $begintime );
        $this->assign ( 'endtime', $endtime );
        $this->assign ( 'showtables', $showtables );
        $this->assign ( 'page', $show ); // 赋值分页输出

        $this->_renderPage ();
    }
    public function topup() {
        $this->_renderPage ();
    }

    /*
     * 订单详情页面
     * 第三版手机端默认mmode=2
     */
    public function orderdetail($mmode = 2) {

        $this->assign ( 'U_Pay', U ( "/cart/pay" ) );
        $orderid = I ( 'orderid', "0", strval );


        $ordertype=I('ordertype',0,intval);
        if($ordertype){
            $orderid = $this->encode_pass ( $orderid, '1', "decode" );
            $orderid_ = $this->encode_pass ( $orderid, $_SESSION ['f_userid']);;
            if($ordertype==1){//第一次扫描
                $updateStatus=$this->updateUserPrepaidUid($orderid,$_SESSION ['f_userid']);//更新订单用户ID为当前微信登录的用户
                if(!$updateStatus){
                    redirect ( WEBROOT_URL );
                }
            }else{
                $updateStatus=$this->updateUserPrepaidUid($orderid,$_SESSION ['f_userid'],1);//更新订单用户ID为当前微信登录的用户
            }
        }else{
            $orderid_ = $orderid;
            $orderid = $this->encode_pass ( $orderid, $_SESSION ['f_userid'], "decode" );
        }
        $this->assign('orderid_',$orderid_);
        //获得发票信息

        $Paybill = M('user_paybill');
        $paybillarr = $Paybill->getByUp_orderid($orderid);
        $this->assign('paybillarr',$paybillarr);
        // echo $orderid;
        // exit;
        $TP = new ProductModel ();
        $UP = M ();
        $sql = "select TUP.up_amount_account,TUP.up_amount_coupon,TUP.up_orderid,TUP.up_paytype,TP.payname,TUP.up_amount,TUP.up_status,TUP.up_dealdate,TUP.up_efee,TUP.up_amount_account,TUP.up_amount_total,TUP.up_order_bz,";
        $sql .= "TUP.up_productid,TUP.up_uid,TUP.up_type,TPD.up_product_info,TUP.up_express,TUP.up_address,TUP.up_addressee,TUP.up_mobile,TUP.up_id,TUP.up_done_status,TUP.up_expressname,TUP.up_expressid ";
        $sql .= "from tdf_user_prepaid as TUP ";
        $sql .= "Left Join tdf_paytype as TP On TP.pt_id=TUP.up_paytype ";
        $sql .= "Left Join tdf_user_prepaid_detail as TPD On TUP.up_id=TPD.up_id ";
        $sql .= "where TUP.up_orderid=" . $orderid . "";
        $OD = $UP->query ( $sql );
        $OD = $OD [0];
        //dump($OD);
        $OD ['up_orderid_en'] = $this->encode_pass ( $OD ['up_orderid'], $_SESSION ['f_userid'] );
        $temp_product_info = unserialize ( $OD ['up_product_info'] );

        // 组织捆绑结构
        foreach ( $temp_product_info as $key => $Product ) {
            // 捆绑销售类商品
            if ($Product ['uc_isbind'] == 1) {
                foreach ( $temp_product_info as $k => $v ) {
                    if (($v ['uc_isbind'] == 2) && ($v ['uc_masterid'] == $Product ['p_id']) && ($v ['uc_handleuc'] == $Product ['uc_id'])) {
                        $temp_product_info [$key] ['binditems'] [] = $v;
                        unset ( $temp_product_info [$k] );
                    }
                }
            }
        }
        $temp_product_info = array_values ( $temp_product_info );

        // 处理主商品
        foreach ( $temp_product_info as $key => $value ) {
            unset ( $temp_product_info [$key] ['p_intro'] );
            unset ( $temp_product_info [$key] ['p_author'] );
            foreach ( $temp_product_info as $k => $v ) {
                foreach ( $v as $k1 => $v1 ) {
                    if ($k1 !== 'p_propid_spec' & $k1 !== 'p_belongpid' & $k1 !== 'p_id' & $k1 !== 'p_cover' & $k1 !== 'p_name' & $k1 !== 'p_cate_3' & $k1 !== 'p_producttype' & $k1 !== 'p_price' & $k1 !== 'cartitem' & $k1 !== 'uc_isreal' & $k1 !== 'uc_count' & $k1 !== 'binditems') {
                        unset ( $v [$k1] );
                    } // 消除产品其他数组元素
                }
                $temp_product_info [$k] = $v;
                // 判断后设置链接的模块名
                if ($v ['p_producttype'] == 5) {
                    $temp_product_info [$k] ['linkedmodel'] = 'product';
                } else {
                    $temp_product_info [$k] ['linkedmodel'] = 'models';
                }
                $temp_product_info [$k] ['propspec'] = ProductPropValModel::parseCombinePropVals ( $temp_product_info [$k] ['p_propid_spec'], '<br>' );
            }
        }

        // 处理捆绑商品
        foreach ( $temp_product_info as $key => $Product ) {
            if (key_exists ( 'binditems', $Product )) {
                foreach ( $Product ['binditems'] as $k => $BindProduct ) {
                    foreach ( $BindProduct as $k1 => $v1 ) {
                        if ($k1 !== 'p_propid_spec' & $k1 !== 'p_belongpid' & $k1 !== 'p_id' & $k1 !== 'p_cover' & $k1 !== 'p_name' & $k1 !== 'p_cate_3' & $k1 !== 'p_producttype' & $k1 !== 'p_price' & $k1 !== 'cartitem' & $k1 !== 'uc_isreal' & $k1 !== 'uc_count' & $k1 !== 'binditems') {
                            unset ( $BindProduct [$k1] );
                        }
                    }
                    $temp_product_info [$key] ['binditems'] [$k] = $BindProduct;
                    // 判断后设置链接的模块名
                    if ($BindProduct ['p_producttype'] == 5) {
                        $temp_product_info [$key] ['binditems'] [$k] ['linkedmodel'] = 'product';
                    } else {
                        $temp_product_info [$key] ['binditems'] [$k] ['linkedmodel'] = 'models';
                    }
                    $temp_product_info [$key] ['binditems'] [$k] ['propspec'] = ProductPropValModel::parseCombinePropVals ( $BindProduct ['p_propid_spec'], '/' );
                    $temp_product_info [$key] ['p_price'] += $BindProduct['p_price'];
                }
            }
        }

        $OD ['up_product_infoarr'] = $temp_product_info;
        $OD ['up_status_text'] = replace_int_vars ( $OD ['up_status'] );
        if ($OD ['up_uid'] !== $_SESSION ['f_userid']) {
            $this->error ( "参数错误！" );
        }

//		if ($OD ['up_type'] == 1 || $OD ['up_type'] == 4) {
//			$product = unserialize ( $OD ['up_productid'] );
//			$OD ['detail'] = $OD ['up_product_infoarr'];
//			$OD ['distype'] = 1;
//		} elseif ($OD ['up_type'] == 3) { // 充值兑换为积分
//			$detail_value = $OD ['up_amount'] * 20;
//			$OD ['detail'] = "充值兑换 " . $detail_value . " 积分.";
//			$OD ['distype'] = 0;
//		} elseif ($OD ['up_type'] == 0) { // 向账户充值
//			$OD ['detail'] = "账户充值 " . $OD ['up_amount'] . " 元现金";
        $OD ['distype'] = 1;
//		}

        $order_do_status = L ( "up_done_status" );
        $OD ['up_done_status_text'] = $order_do_status [$OD ['up_done_status']];

        // 输出订单过程状态 start-------------------------
        $UPM = new UserPrepaidModel ();
        $PrepaidStatus = $UPM->PrepaidStatus ( $OD ['up_status'], $OD ['up_id'] );

        /*################################# 新增参数 2016/10/13##1-提交订单 2-等待付款 3-订单制作中 4-已发货#########*/
        $this->assign('prepaidstatus',$PrepaidStatus);
        /*################################# 新增参数 2016/10/13##################################################*/

        $PrepaidStatusHtml = $UPM->getPrepaidStatusHtml ( $PrepaidStatus );
        // var_dump($PrepaidStatusHtml);
        $PrepaidProcess = $UPM->getPrepaidProcess ( $OD ['up_status'], $OD ['up_id'], $OD ['up_dealdate'],$mmode); // 订单跟踪过程

        $PrepaidExpress = $UPM->getPrepaidExpress ( $OD ['up_id'] );
        $this->assign ( 'PrepaidExpress', $PrepaidExpress );
        // var_dump($OD);
        $address_status = 0;
        if ($OD ['up_address']) {
            $address_status = 1;
        }

        $UA = new UserAddressModel ();
        $UserAddress = $UA->getAddressByUserID ( $OD ['up_uid'] );
        // var_dump($UserAddress);
        $this->assign ( "PrepaidStatus", $PrepaidStatus );
        $this->assign ( "AddressStatus", $address_status );
        $this->assign ( "UserAddress", $UserAddress );
        // 输出订单过程状态 end---------------------------
        $this->assign ( 'PrepaidStatusHtml', $PrepaidStatusHtml );
        $this->assign ( 'PrepaidProcess', $PrepaidProcess );
        $PR = M('product');
        foreach($OD['detail'] as $k=>$a){
            $PR->getByP_id($a['p_id']);
            $OD['detail'][$k]['p_diy_id'] = $PR->p_diy_id;
        }
        //dump($OD);
        $this->assign ( 'orderdetail', $OD );

        $this->_renderPage ();
    }

    /*
     * APP单产品订单，更新订单的up_uid和agent_userid（代理商的userId）
     */
    public function updateUserPrepaidUid($oid,$uid,$secondStatus=0){
        $UPM=new UserPrepaidModel();
        $orderInfo=$UPM->getPrepaidListByOrderid($oid);
        $data['up_uid']=$uid;
        if(!$orderInfo[0]['up_agent_userid']){
            if(!$secondStatus){
                $data['up_agent_userid']=$orderInfo[0]['up_uid'];
            }
            $data['scan_sign']      =1;
            $result=$UPM->where("up_orderid='" .$oid."'")->setField ($data);
        }else{
            $result=$UPM->where("up_orderid='" .$oid."'")->setField ($data);
            if($uid==$orderInfo[0]['up_uid']){
                $result=1;
            }else{
                $result=0;
            }
        }
        return $result;
    }

    /**
     * 支付页面
     *
     * @access private
     * @return null
     */
    public function payment() {
        $ptype = I ( "ptype", 0, "int" );
        $this->assign ( 'ptype', $ptype );
        if ($ptype == 1) {
            $up_type = 3;
        } else {
            $up_type = 0;
        }
        $this->assign ( 'up_type', $up_type );
        $this->assign ( 'username', $_SESSION ['f_nickname'] );
        $this->assign ( 'u_id', $_SESSION ['f_userid'] );
        $temp_orderid = $this->get_umorderid ();
        $oid = $this->encode_pass ( $temp_orderid, $_SESSION ['f_userid'] ); // 加密orderid
        $this->assign ( 'oid', $oid );
        // <<----------------------------------------支付方式
        $PT = new PayTypeModel ();
        $paytype_arr = $PT->get_paytype ();
        $this->assign ( 'pt_arr', $paytype_arr );
        // ------------------------------------------------->>
        $this->_renderPage ();
    }

    public function payresult(){ 	// 获得显示支付结果
        session_start ();
        $ordertype = $_GET ['otype'];
        $mmode=$_GET['mmode'];
        //echo $ordertype;
        if ($ordertype) {
            $out_trade_no = $this->encode_pass ( $_GET ['out_trade_no'], $_SESSION ['f_userid'], 'decode' ); // 解密orderid
        } else {

            $out_trade_no = $_GET ['out_trade_no'];
        }
        //echo $out_trade_no;
        $en_out_trade_no=$out_trade_no;
        $en_out_trade_no = $this->encode_pass ( $out_trade_no, $_SESSION ['f_userid'], 'encode' );
        // echo $out_trade_no;
        $CM = M ();
        $sql = "select tu.u_dispname,tup.up_amount,tup.up_status,tup.up_dealdate,tup.up_type,TPT.payname,tu.u_email,tup.up_efee ";
        $sql .= "from tdf_user_prepaid as tup left join tdf_users as tu On tu.u_id=tup.up_uid ";
        $sql .= "left join tdf_paytype as TPT On TPT.pt_id=tup.up_paytype ";
        $sql .= "where tup.up_orderid_new='" . $out_trade_no . "'";
        $order_array = $CM->query ( $sql );
        //echo $sql;
        if ($order_array [0] ['up_type'] == 1 || $order_array [0] ['up_type'] == 4) {
            // $showinfo="<p>订单号:".$out_trade_no."</p>";
            if ($order_array [0] ['up_status'] == 1) {
                if($mmode==1){
                    $buyurl = " <a href=__DOC__/user.php/wxuser/orderdetail/orderid/" . $en_out_trade_no . "><font color=blue>查看</font></a>";

                }else{
                    $buyurl = " <a href=__DOC__/user.php/sales/orderdetail/orderid/" . $en_out_trade_no . "><font color=blue>查看</font></a>";
                }
            }
            $showinfo .= "<p><br>类 型：在线购买   " . $buyurl . "</p>";
            $showinfo .= "<p><br>金 额：¥ " . ($order_array [0] ['up_amount'] + $order_array [0] ['up_efee']) . "</p>";
            $showinfo .= "<p><br>状 态：" . replace_int_vars ( $order_array [0] ['up_status'] ) . "</p>";
            $showinfo .= "<p><br>方 式：" . $order_array [0] ['payname'] . "</p>";
            $showinfo .= "<p><br><font color=#cccccc>" . $order_array [0] ['up_dealdate'] . "</font></p>";
        } elseif ($order_array [0] ['up_type'] == 0) { // 账户充值
            $showinfo .= "<p><br>充值账户：  " . $order_array [0] ['u_email'] . "</p>";
            $showinfo .= "<p><br>金 额：¥ " . $order_array [0] ['up_amount'] . "</p>";
            $showinfo .= "<p><br>状 态：" . replace_int_vars ( $order_array [0] ['up_status'] ) . "</p>";
            $showinfo .= "<p><br>方 式：" . $order_array [0] ['payname'] . "</p>";
            $showinfo .= "<p><br><font color=#cccccc>" . $order_array [0] ['up_dealdate'] . "</font></p>";
        } elseif ($order_array [0] ['up_type'] == 3) { // 积分充值
            $temp_jf = $order_array [0] ['up_amount'] * 4;
            $showinfo .= "<p><br>账户：  " . $order_array [0] ['u_email'] . "</p>";
            $showinfo .= "<p><br>金 额：¥ " . $order_array [0] ['up_amount'] . "</p>";
            $showinfo .= "<p><br>积分：" . $temp_jf . "</p>";
            $showinfo .= "<p><br>状 态：" . replace_int_vars ( $order_array [0] ['up_status'] ) . "</p>";
            $showinfo .= "<p><br>方 式：" . $order_array [0] ['payname'] . "</p>";
            $showinfo .= "<p><br><font color=#cccccc>" . $order_array [0] ['up_dealdate'] . "</font></p>";
        }
        /*******新增参数*********/
        //猜你喜欢
        $count=D('product')->where('p_choice=0')->count();
        $limitStart = rand(1,$count);
        if($limitStart>=($count-4)){
            $limitStart = 1;
        }
        $info=D('product')->where('p_choice=0')->limit($limitStart,4)->select();
        $this->assign('info',$info);
        /*******新增参数*********/
        $this->assign ( 'showhtml', $showinfo );
        $this->assign('mmode',$mmode);
        $this->_renderPage ();
    }


    public function checkscore() {
        $checktype = I ( "ctype", 0, "int" );
        import ( 'ORG.Util.Page' ); // 导入分页类
        if ($checktype == 0) {
            $UJC = M ( "user_jobs" );
            $conditon = "u_id=" . $_SESSION ["f_userid"] . "";
            $count = $UJC->where ( $conditon )->count (); // 查询满足要求的总记录数
            $Page = new Page ( $count, 12 ); // 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig ( 'theme', '%first% %prePage% %upPage% %linkPage% %downPage% %nextPage% %end% <span class=title>%totalRow% %header%</span> <span>%nowPage%/%totalPage% 页</span>' );
            $show = $Page->show (); // 分页显示输出

            $UJ = M ();
            $sql = "select TUJ.uj_award,TUJ.uj_date,TJ.j_dis,TU.u_email from tdf_user_jobs as TUJ ";
            $sql .= "Left Join tdf_jobs as TJ On TUJ.j_id=TJ.j_id ";
            $sql .= "Left Join tdf_users as TU On TUJ.u_id=TU.u_id ";
            $sql .= "where TUJ.u_id=" . $_SESSION ["f_userid"] . " ";
            $sql .= "order by TUJ.uj_timestamp desc ";
            $sql .= "limit " . $Page->firstRow . "," . $Page->listRows . "";
            $jflist = $UJ->query ( $sql );
        } elseif ($checktype == 1) {
            $UJC = M ( "user_deals_vcoin" );
            $conditon = "ud_buyer=" . $_SESSION ["f_userid"] . "";
            $count = $UJC->where ( $conditon )->count (); // 查询满足要求的总记录数
            $Page = new Page ( $count, 12 ); // 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig ( 'theme', '%first% %prePage% %upPage% %linkPage% %downPage% %nextPage% %end% <span class=title>%totalRow% %header%</span> <span>%nowPage%/%totalPage% 页</span>' );
            $show = $Page->show (); // 分页显示输出

            $UJ = M ();
            $sql = "select TD.p_name,TUDV.ud_pprice,TUDV.ud_dealdate,TUDV.ud_pid from tdf_user_deals_vcoin as TUDV ";
            $sql .= "Left Join tdf_product as TD On TUDV.ud_pid=TD.p_id ";
            $sql .= "Left Join tdf_users as TU On TUDV.ud_buyer=TU.u_id ";
            $sql .= "where TUDV.ud_buyer=" . $_SESSION ["f_userid"] . " ";
            $sql .= "order by TUDV.ud_dealdate desc ";
            $sql .= "limit " . $Page->firstRow . "," . $Page->listRows . "";
            $jflist = $UJ->query ( $sql );
        } elseif ($checktype == 3) { // 充值获取积分
            $UPC = M ( "user_prepaid" );
            $conditon = "up_uid=" . $_SESSION ["f_userid"] . " and up_type=3 and up_status=1";
            $count = $UPC->where ( $conditon )->count (); // 查询满足要求的总记录数
            $Page = new Page ( $count, 12 ); // 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig ( 'theme', '%first% %prePage% %upPage% %linkPage% %downPage% %nextPage% %end% <span class=title>%totalRow% %header%</span> <span>%nowPage%/%totalPage% 页</span>' );
            $show = $Page->show (); // 分页显示输出

            $UP = M ();
            $sql = "select TUP.up_dealdate,TUP.up_orderid,TU.u_email,TUP.up_paytype,TUP.up_amount,TP.payname,TUP.up_status from tdf_user_prepaid as TUP ";
            $sql .= "Left Join tdf_users as TU On TU.u_id=TUP.up_uid ";
            $sql .= "Left Join tdf_paytype as TP On TP.pt_id=TUP.up_paytype ";
            $sql .= "where TUP.up_uid=" . $_SESSION ["f_userid"] . " and TUP.up_type=3 and TUP.up_status=1 ";
            $sql .= "order by TUP.up_dealdate ";
            $sql .= "limit " . $Page->firstRow . "," . $Page->listRows . "";
            $jflist = $UP->query ( $sql );
            // var_dump($sql);
            // var_dump($jflist);
        }
        $this->assign ( 'showtitle', "积分查询-3D城" );
        $this->assign ( "checktype", $checktype );
        $this->assign ( "jflist", $jflist );
        $this->assign ( 'page', $show ); // 赋值分页输出
        $this->_renderPage ();
    }
    public function pay() { // 提交订单
        if ($this->_post ()) {
            // --------------临时使用支付宝提示页面 begin
            // echo "请向支付宝账户 wuchengwu5@163.com 进行充值,充值完成后请联系
            // .wuchengwu@bitmap.com.cn";
            // $this->display();
            // exit;
            // --------------临时使用支付宝提示页面 end

            $up_uid = I ( "up_uid", 0, "int" );
            $up_amount = I ( "up_amount", 0, "float" );

            $up_amount_save = $up_amount;
            $up_orderid_post = I ( "up_orderid", "", "string" );
            $up_paytype = I ( "payt", 0, "int" );
            $up_type = I ( "up_type", 0, "int" );
            $up_orderid = $this->encode_pass ( $up_orderid_post, $up_uid, 'decode' ); // 解密orderid

            // <<---------------------------------------------------获得支付方式
            $PT = new PayTypeModel ();
            $paytype_arr = $PT->get_paytypeByPtid ( $up_paytype );
            $paym = $paytype_arr [0] ['paymethodcode'];
            $dbank = $paytype_arr [0] ['bankcode'];
            // ---------------------------------------------------获得支付方式-->>

            $IP = get_client_ip ();
            $UPM = new UserPrepaidModel ();
            $UPM_info = $UPM->getPrepaidListByOrderid ( $up_orderid );
            if (! $UPM_info) {
                if ($UPM->addRecord ( $up_uid, $up_amount_save, $IP, 0, $up_orderid, $up_paytype, "0", $up_type )) {
                    $alipay_order = new OrderAction ();
                    $alipay_order->alipayto ( $up_orderid, $up_amount, "ignjewelry.com", "ignjewelry.com 支付", $paym, $dbank );
                }
            } else {
                echo header ( "Content-Type:text/html; charset=utf-8" );
                echo "订单已经生成，请不要重复提交！";
            }
        }
    }
    public function get_umorderid() 	// 产生orderid
    {
        $tempid = time () . $this->generate_rand ( 8 );
        return $tempid;
    }
    public function generate_rand($l) { // 产生随机数$l为多少位
        $c = "0123456789";
        // $c= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        srand ( ( double ) microtime () * 1000000 );
        for($i = 0; $i < $l; $i ++) {
            $rand .= $c [rand () % strlen ( $c )];
        }
        return $rand;
    }

    // ---------------------------------------加密、解密函数--------------------------start----
    // *********by zhangzhibin***************
    // *********20130709 ***************
    public function encode_pass($tex, $key, $type = "encode") {
        $chrArr = array (
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9'
        );
        if ($type == "decode") {
            if (strlen ( $tex ) < 14)
                return false;
            $verity_str = substr ( $tex, 0, 8 );
            $tex = substr ( $tex, 8 );
            if ($verity_str != substr ( md5 ( $tex ), 0, 8 )) {
                // 完整性验证失败
                return false;
            }
        }
        $key_b = $type == "decode" ? substr ( $tex, 0, 6 ) : $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62];
        $rand_key = $key_b . $key;
        $rand_key = md5 ( $rand_key );
        $tex = $type == "decode" ? base64_decode ( substr ( $tex, 6 ) ) : $tex;
        $texlen = strlen ( $tex );
        $reslutstr = "";
        for($i = 0; $i < $texlen; $i ++) {
            $reslutstr .= $tex {$i} ^ $rand_key {$i % 32};
        }
        if ($type != "decode") {
            $reslutstr = trim ( $key_b . base64_encode ( $reslutstr ), "==" );
            $reslutstr = substr ( md5 ( $reslutstr ), 0, 8 ) . $reslutstr;
        }
        return $reslutstr;
    }
    // $psa=encode_pass("phpcode","taintainxousad");
    // echo $psa;
    // echo encode_pass($psa,"taintainxousad",'decode');
    // ---------------------------------------加密、解密函数--------------------------end----
    public function orderdel() {
        $orderid = I ( 'orderid', '0', 'string' );
        $orderid = $this->encode_pass ( $orderid, $_SESSION ['f_userid'], "decode" );
        $UP = new UserPrepaidModel ();
        $up_status = $UP->delAmountByOrderid ( $orderid );
        redirect ( WEBROOT_URL . "/user.php/sales/ordermanage" );
    }

    /*
     * 用户中心订单管理 zhang
     */
    public function ordermanage() { // 订单管理
        /*$orderchecked = I ( 'orderchecked', 0, 'intval' ); // 进行中的订单
        if ($orderchecked == 1) {
            $condition = "up_uid=" . $_SESSION ['f_userid'] . " and up_status=1 and up_checked=0";
        } else {
            $MonthSelet = I ( 'searchmonth', 0, 'intval' ); // 查询的订单周期
            $MonthOptions = array(
                    0 => '三个月内的订单',
                    1 => '三个月前的订单'
            );
            $this->assign ( "MonthOptions", $MonthOptions ); // select所有选项
            $this->assign ( "MonthSelect", $MonthSelet ); // select当前选中项

            $StatusSelect = I ( 'searchstatus', 0, 'intval' );
            $StatusOptions = array (
                    0 => '所有订单',
                    1 => '未支付的订单',
                    2 => '已支付的订单',
                    3 => '关闭的订单'
            );
            $this->assign ( "StatusOptions", $StatusOptions ); // select所有选项
            $this->assign ( "StatusSelect", $StatusSelect ); // select当前选中项
            $strnowtime = strtotime ( "-90 days" );

            $condition = "TUP.up_uid=" . $_SESSION ['f_userid'] . " and ";
            $condition .= ($MonthSelet == 0) ? "unix_timestamp(TUP.up_dealdate) > $strnowtime" : "unix_timestamp(TUP.up_dealdate) < $strnowtime";
            $condition .= $this->getconditionByStatus ( $StatusSelect ); // 附加订单状态条件
        }*/
        $orderchecked=I ( 'orderchecked', 0, 'intval' );//筛选类型 0所有1待付款2待发货3待收货
        $currentPage=I('p',1,'intval');
        switch($orderchecked){
            case 0:
                $condition = "TUP.up_uid=" . $_SESSION ['f_userid'] . " ";
                break;
            case 1:
                $condition = "TUP.up_uid=" . $_SESSION ['f_userid'] . " and TUP.up_status=0 ";
                break;
            case 2://待发货
                $condition = "TUP.up_uid=" . $_SESSION ['f_userid'] . " and TUP.up_status=1 ";
                $conditionProcess="and up_process<6 ";
                break;
            case 3: //待收货
                $condition = "TUP.up_uid=" . $_SESSION ['f_userid'] . " and TUP.up_status=1 ";
                $conditionProcess="and up_process=6";
                break;
            /*  case 4: //待评价(送货流程已完成)
                  $condition = "TUP.up_uid=" . $_SESSION ['f_userid'] . " and TUP.up_status=1 ";
                  $conditionProcess="and up_process>6";
                  break;*/
            default:
                $condition = "TUP.up_uid=" . $_SESSION ['f_userid'] . " ";
        }
        $this->assign ( "orderchecked", $orderchecked );
        $SM = M ( "user_prepaid" );
        import ( 'ORG.Util.Page' ); // 导入分页类
        $sqlCount="select TUP.up_id from tdf_user_prepaid as TUP ";
        $sqlCount .="Left Join tdf_user_prepaid_process as TUPP ON TUPP.up_id=TUP.up_id ";
        $sqlCount .="where " . $condition . " and TUP.delsign=0 ";
        $sqlCount .="group by TUP.up_id ";
        $sqlCount ="select count(*) as c from (".$sqlCount.") as t where 1=1 ".$conditionProcess;
        $countArr=$SM->query($sqlCount);
        $count=$countArr[0]['c']; //分页的数据记录总数
        $pageSize = 5;
        $totalPage=ceil($count/$pageSize);
        $startPage = ($currentPage-1)*$pageSize;
        $this->assign('totalPage',$totalPage);
        $Page = new Page ( $count, 5 ); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show (); // 分页显示输出

        /*$sql = "select TUP.up_express,TUP.up_id,TUP.up_orderid,TUP.up_dealdate,TUP.up_type,TUP.up_amount,TUP.up_efee,TUP.up_status,TUP.up_addressee,TUPD.up_product_info,TUP.up_amount_account,TUP.up_amount_coupon,TUP.up_amount_total from tdf_user_prepaid as TUP ";
        $sql .= "Left Join tdf_user_prepaid_detail as TUPD ON TUPD.up_id=TUP.up_id ";
        $sql .= "where " . $condition . " and delsign=0 order by TUP.ctime desc limit " . $Page->firstRow . "," . $Page->listRows . "";*/

        $sql = "select TUP.up_express,TUP.up_id,TUP.up_orderid,TUP.up_dealdate,TUP.up_type,TUP.up_amount,TUP.up_efee,TUP.up_status,TUP.up_addressee,TUPD.up_product_info,TUP.up_amount_account,TUP.up_amount_coupon,TUP.up_amount_total,max(TUPP.done_process) as up_process from tdf_user_prepaid as TUP ";
        $sql .="Left Join tdf_user_prepaid_detail as TUPD ON TUPD.up_id=TUP.up_id ";
        $sql .="Left Join tdf_user_prepaid_process as TUPP ON TUPP.up_id=TUP.up_id ";
        $sql .="where " . $condition . " and TUP.delsign=0 ";
        $sql .="group by TUP.up_id ";
//        $sql .="order by TUP.ctime desc limit " . $Page->firstRow . "," . $Page->listRows . "";
        $sql .="order by TUP.ctime desc limit ".$startPage.",".$pageSize."";
        $sql="select * from (".$sql.") as t where 1=1 ".$conditionProcess;
        $orderlist = $SM->query ( $sql );
        $processSimple=L('processSimple');//读取默认的用户orderlist进程说明
        $TDCM   = new DiyCateModel();
        $UDM    = new UserDiyModel();
        $UCM    = new UserCartModel();
        //dump($orderlist);
        foreach ( $orderlist as $key => $value ) {
            $orderlist [$key]['showprocess']="";
            if($value['up_status']==1){
                $orderlist [$key]['showprocess']=$processSimple[intval($value['up_process'])];
            }

            if ($value ['up_product_info']) {
                $productinfo = unserialize ( $value ['up_product_info'] );
                foreach ( $productinfo as $k => $v ) {
                    // 不显示捆绑商品
                    if ($v ['uc_isbind'] == 2) {
                        unset ( $productinfo [$k] );
                    }
                }
                $productinfo = array_values ( $productinfo );
                foreach ( $productinfo as $k => $v ) {
                    foreach ( $v as $k1 => $v1 ) {
                        if ($k1 !== 'p_belongpid' & $k1 !== 'p_id' & $k1 !== 'p_cover' & $k1 !== 'p_name' & $k1 !== 'p_cate_3' & $k1 !== 'p_diy_id' & $k1 !== 'uc_producttype' & $k1 !== 'p_cate_4' & $k1 !== 'uc_isbind' &$k1 !== 'p_price') {
                            unset ( $v [$k1] );
                        } // 消除产品其他数组元素
                    }

                    $productinfo [$k] = $v;
                    // miaomin added@2014/12/24// 判断后设置链接的模块名
                    if ($v ['uc_producttype'] == 5) {
                        $productinfo [$k] ['linkedmodel'] = 'product';
                    }elseif($v ['uc_producttype'] == 4){
                        $productinfo [$k] ['linkedmodel'] = 'diy';
                        $diyCateInfo=$TDCM->getDiyCateByCid($productinfo [$k]['p_cate_4']);
                        $productinfo [$k] ['isdiamond'] =$diyCateInfo['isdiamond'];

                        /*————————————————————————————————————————增加DIY商品的显示详情————————————————————————————————————————start*/
                        $userDiyInfo    = $UDM->getUserDiyInfoById($productinfo[$k]['p_diy_id']);
                        //var_dump($userDiyInfo);
                        //exit;
                        $Product['diy_unit_info']=$userDiyInfo['diy_unit_info'];//加入diy的值信息到$Product中用于下面的getUserCartDiyByProduct方法
                        $productinfo [$k] ['diy_unit_info']=$userDiyInfo['diy_unit_info'];
                        if($Product['p_cate_4']==1){
                            $productinfo [$k]['p_description']="简笔画";
                        }else{
                            $productinfo [$k]['p_description']=$UCM->getUserCartDiyByProduct($Product);
                        }
                        /*————————————————————————————————————————增加DIY商品的显示详情————————————————————————————————————————end*/
                    } else {
                        $productinfo [$k] ['linkedmodel'] = 'models';
                    }

                    /*————————————————————————————————————————增加订单列表中商品的详细信息glx————————————————————————————————————————start*/
                    $p_propid_spec_desc = $this->getProductByUpid($value['up_id']);
                    foreach ($p_propid_spec_desc as $v){
                        $productinfo[$k]['p_propid_spec_desc'] = $v['p_propid_spec_desc'];
                    }
                    /*————————————————————————————————————————增加订单列表中商品的详细信息glx————————————————————————————————————————end*/

                }
                $orderlist [$key] ['en_up_orderid'] = $this->encode_pass ( $value ['up_orderid'], $_SESSION ['f_userid'], "encode" );


                $orderlist [$key] ['productinfo'] = $productinfo;
            }
            unset ( $orderlist [$key] ['up_product_info'] );
        }
//		echo "<pre/>";
//		print_r($orderlist);
//		exit;
        $UA = new UserAccountModel ();
        $UA_info = $UA->getUserAccountByUid ( $_SESSION ['f_userid'] );
        $user_account = $UA_info ['u_rcoin_av'];
        $this->assign ( 'user_account', $user_account );
        $this->assign ( 'showtitle', "IGNITE-我的订单" );
        $this->assign ( 'page', $show ); // 赋值分页输出
        $this->assign ( "orderlist", $orderlist ); // select当前选中项
        $this->_renderPage ();
    }

    function getconditionByStatus($Status) { // 返回订单状态查询条件
        switch ($Status) {
            case 1 :
                $co = " and up_status=0";
                break;
            case 2 :
                $co = " and up_status=1";
                break;
            case 3 :
                $co = " and up_status=2";
                break;
            default :
                $co = "";
                break;
        }
        return $co;
    }
    public function payagain() { // 保存的订单重新发起支付
        $orderid = I ( "orderid", "0", "string" );
        if ($orderid == '0') {
            $this->error ( "Sorry! 参数错误! ", U ( 'User/sales/ordermanage' ) );
        }
        $OI = new UserPrepaidModel ();
        $prepaidinfo = $OI->getPrepaidListByOrderid ( $orderid );
        $prepaidinfo = $prepaidinfo [0];
        $up_paytype = $prepaidinfo ['up_paytype'];
        $up_orderid = $prepaidinfo ['up_orderid'];
        $up_amount = $prepaidinfo ['up_amount'] + $prepaidinfo ['up_efee'];
        $up_type = $prepaidinfo ['up_type'] == 0 ? "3Dcity在线支付" : "3Dcity在线支付";
        // <<---------------------------------------------------获得支付方式
        $PT = new PayTypeModel ();
        $paytype_arr = $PT->get_paytypeByPtid ( $up_paytype );
        $paytype_arr = $paytype_arr [0];
        $paym = $paytype_arr ['paymethodcode'];
        $dbank = $paytype_arr ['bankcode'];
        // ---------------------------------------------------获得支付方式-->>
        $alipay_order = new OrderAction ();
        $alipay_order->alipayto ( $up_orderid, $up_amount, "ignjewelry.com", $up_type, $paym, $dbank );
        // echo "OK";
    }


    private function getProductByUpid($up_id) {
        $ProductArr = $this->getProductArr ( $up_id );
        $DPM = new DiyPrepaidModel ();
        $UCM=new UserCartModel();
        foreach ( $ProductArr as $key => $Product ) {
            switch ($Product ['uc_producttype']) {
                case 1 :
                    $pid = $Product ['p_id'];
                    $product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
                    $product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
                    $product [$key] ['p_name'] = $Product ['p_name']; // 名称
                    $product [$key] ['p_price'] = $Product ['p_price']; // 单价
                    $product [$key] ['p_count'] = $Product ['p_count']; // 数量
                    $product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
                    $product [$key] ['cover'] = $Product ['p_cover']; // 截图
                    $product [$key] ['p_id'] = $Product ['p_id']; // 对应tdf_product产品p_id
                    $product [$key] ['cid'] = $Product ['p_cate_3']; // 对应tdf_product产品p_id
                    $product [$key] ['p_propid_spec_desc'] = ''; // 对应tdf_product产品p_id
                    break;
                case 2 :
                    $pid = $Product ['p_id'];
                    $product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
                    $product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
                    $product [$key] ['p_name'] = $Product ['p_name']; // 名称
                    $product [$key] ['p_price'] = $Product ['p_price']; // 单价
                    $product [$key] ['p_count'] = $Product ['p_count']; // 数量
                    $product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
                    $product [$key] ['cover'] = $Product ['p_cover']; // 截图
                    $product [$key] ['p_id'] = $Product ['p_id']; // 对应tdf_product产品p_id
                    $product [$key] ['cid'] = $Product ['p_cate_3']; // 对应tdf_product产品p_id
                    $product [$key] ['p_propid_spec_desc'] = ''; // 对应tdf_product产品p_id
                    break;
                case 4 : // DIY产品
                    $pid = $Product ['p_id'];
                    $upid = $up_id;
                    $udinfo [$key] = $DPM->getUdinfoByUpid ( $upid, $pid );

                    $product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
                    $product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
                    $product [$key] ['p_name']      = $udinfo [$key]['Textvalue']; // 名称
                    $product [$key] ['p_price']     = $udinfo [$key]['price']; // 单价
                    $product [$key] ['p_count']     = $udinfo [$key]['p_count']; // 数量
                    $product [$key] ['totle_price'] = $udinfo [$key]['p_count'] * $product [$key]['p_price']; // 小计价格
                    $product [$key] ['cover']       = $udinfo [$key]['p_cover']; // 截图
                    $product [$key] ['p_diy_id']    = $Product ['p_diy_id']; // 对应tdf_product产品p_id
                    $product [$key] ['p_id']        = $udinfo [$key]['p_id']; // 对应tdf_product产品p_id
                    $product [$key] ['cid']         = $udinfo [$key]['p_cate_4']; // 对应tdf_product产品p_id
                    $value['diy_unit_info']         = $udinfo [$key]['diy_unit_info'];
                    $value['p_cate_4']              = $udinfo [$key]['p_cate_4'];
                    $value['p_intro']               = $Product ['p_intro'];
                    $product [$key] ['p_propid_spec_desc'] = $UCM->getUserCartDiyByProduct($value); // 产品详情
                    break;
                case 5 :
                    $pid = $Product ['p_id'];
                    $product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
                    $product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
                    $product [$key] ['p_name'] = $Product ['p_name']; // 名称
                    $product [$key] ['p_price'] = $Product ['p_price']; // 单价
                    $product [$key] ['p_count'] = $Product ['p_count']; // 数量
                    $product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
                    $product [$key] ['cover'] = $Product ['p_cover']; // 截图
                    $product [$key] ['p_id'] = $Product ['p_id']; // 对应tdf_product产品p_id
                    $product [$key] ['cid'] = $Product ['p_cate_3']; // 对应tdf_product产品p_id
                    $product [$key] ['p_propid_spec_desc'] =ProductPropValModel::parseCombinePropVals ( $Product ['p_propid_spec'], ' -- '); // 对应tdf_product产品p_id
                    break;
                default :
            }
        }
        return $product;
    }
    private function getProductArr($up_id) { // 根据订单的upid返回订单产品数组
        $PPD = new UserPrepaidDetailModel ();
        $ProductInfo = $PPD->getPrepaidDetailByUpid ( $up_id );
        $ProductArr = unserialize ( $ProductInfo ['up_product_info'] );
        return $ProductArr;
    }

}
