<?php
/**
 * 第三版手机版订单结算模块
 * Created by PhpStorm.
 * User: guolixun
 * Date: 2016/10/19
 * Time: 9:18
 */
class IwxuserAction extends CommonAction {

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
        $this->assign ( 'wxtitle', "最酷炫的个性化定制平台" );
    }
    /*
     * 优惠券
     */
    public function mycoupon($handle="",$attr="",$extra = ""){
        $this->assign('wxtitle',"我的优惠券");
        $User = M('users');
        $User->getByU_id($this->_session('f_userid'));
        $me = $User->u_email;
        $Coupon = new CouponModel();
        import('ORG.Util.Page');
        if($extra == NULL){
            $sql = "select t1.*,t2.* from tdf_coupon t1 left join tdf_coupon_type t2 on t1.etId = t2.et_id where t2.et_private = 2 and t1.ec_owner = '{$me}'";
            if($handle != NULL){
                $nowtime = get_now();
                $sql .= " and t1.$handle='{$attr}' and (t2.et_expiredate >'{$nowtime}' or t2.et_expiredate = 0)";
                $this->assign("btnstyle","可用优惠券");
            }
            else{
                $this->assign("btnstyle","所有优惠券");
            }
            $count = count($Coupon->query($sql.";"));
            $Page = new Page($count,10);
            $sql .= " limit $Page->firstRow,$Page->listRows";
            $arr = $Coupon->query($sql.";");
            $show = $Page->showwx();

            $this->assign("num",$count);
            $this->assign("list",$arr);
            $this->assign('page',$show);
            $this->assign('nowtime',$nowtime);
            //echo $Coupon->getLastSql();
            $this->_renderPage();
        }
        else{
            import('ORG.Util.Date');
            $date = new Date(get_now());
            $extratime = $date->dateAdd(30,"d");
            $extratime->format("%Y-%m-%d %H:%M:%S");
            $nowtime = get_now();
            $sql = "select t1.*,t2.* from tdf_coupon t1 left join tdf_coupon_type t2 on t1.etId = t2.et_id where t1.ec_status = 1 and t1.ec_owner = '{$me}' and t2.et_private = '2'";
            $sql .= "and t2.et_expiredate < '{$extratime}' and t2.et_expiredate > '{$nowtime}'";
            $count = count($Coupon->query($sql.";"));
            $Page = new Page($count,10);
            $show =$Page->showwx();
            $sql2 = "select t1.*,t2.* from tdf_coupon t1 left join tdf_coupon_type t2 on t1.etId = t2.et_id where t1.ec_status = 1 and t1.ec_owner = '{$me}' and t2.et_private = '2' and t2.et_expiredate < '{$extratime}' and t2.et_expiredate > '{$nowtime}' limit $Page->firstRow,$Page->listRows";
            $arr = $Coupon->query($sql2.";");
            $show =$Page->showwx();
            $this->assign("num",$count);
            $this->assign("list",$arr);
            $this->assign('page',$show);
            $this->assign("btnstyle","即将过期");
            //echo $me;
            $this->_renderPage();
        }
    }
    public function address($status = "") {
        $this->assign ( 'wxtitle', "地址管理" );
        $UAddress = M ( "user_address" );
        $AIPM = new AreaInfoPickerModel ();

        $arr = $UAddress->where ( "u_id = " . session ( 'f_userid' ) . " AND ua_isremove = 0")->select ();
        foreach ( $arr as $key => $list ) {
            $addarr [$key] ['name'] = $list ['ua_addressee'];
            $addarr [$key] ['mailaddress'] = $AIPM->getarea ( $list ['ua_province'], $list ['ua_city'], $list ['ua_region'] ) . $list ['ua_address'];
            $addarr [$key] ['phone'] = $list ['ua_mobile'];
            $addarr [$key] ['isdefault'] = $list ['ua_isdefault'];
            $addarr [$key] ['uaid'] = $list ['ua_id'];
        }
        $this->assign ( 'status_', $status );
        $this->assign ( 'list', $addarr );
        $this->assign ( 'addrcount', count ( $addarr ) );
        $this->_renderPage ();
    }
    public function deladdress($uaid) {
        $Address = M ( "user_address" );
        $condition ['ua_id'] = $uaid;
        if ($Address->where ( $condition )->delete ()) {
            redirect ( WEBROOT_URL . "/user.php/iwxuser/address/status/yes" );
        } else {
            redirect ( WEBROOT_URL . "/user.php/iwxuser/address/status/no" );
        }
    }
    public function defaultaddr($uaid) {
        $Address = M ( "user_address" );
        $Address->where ( "u_id = '{$this->_session('f_userid')}' and ua_isdefault = '1'" )->save ( array (
            'ua_isdefault' => '0'
        ) );

        $condition ['ua_id'] = $uaid;
        $data ['ua_isdefault'] = 1;
        if ($Address->where ( $condition )->save ( $data )) {
            redirect ( WEBROOT_URL . "/user.php/iwxuser/address/status/yes" );
        } else {
            redirect ( WEBROOT_URL . "/user.php/iwxuser/address/status/no" );
        }
    }
    /*
     * 地址新增
     */
    public function addressadd() {
        $AIPM = new AreaInfoPickerModel ();
        $oid=I("orderid","0",string);
//        //$oid=pub_encode_pass ( $orderid, $_SESSION ['f_userid'], "decode" );
//        if($this->isPost()){
//            $Post = $_POST;
////            dump($Post);
////            exit;
//            $this->assign ( 'Post', $Post );
//            $AddressPost = $this->getAddressPost ();
//            if (! $AddressPost) {return $this->displayError ( '必填信息没有输入完整' );}
//            $AddressPost ['id'] = $AddressID;
//            $UAM = $this->bulidAddressData ( $AddressPost );
//            $UAM->startTrans ();
//            $Result = $AddressID ? $UAM->save () : $UAM->add ();
//            if ($Result === false) {
//                //echo "aaa";
//                $UAM->rollback ();
//                return $this->displayError ( '连接失败，请稍后再试' );
//            }
//
//            if (! $AddressID || isset( $AddressPost ['defaultaddress'] )) {
//                if ($UAM->setDefaultAddress ( $_SESSION ['f_userid'], $AddressID ) === false) {
//                    $UAM->rollback ();
//                    return $this->displayError ( '连接失败，请稍后再试' );
//                }
//            }
//            $UAM->commit ();
//            redirect ( WEBROOT_URL . "/user.php/iwxuser/address" );// 跳转
//        }
        $UC = A ( "User/Cart" ); // 调用user分组下的sales模块
        $this->assign ( 'HtmlCtrl', $UC->getHtmlCtrl ( $AIPM ) );
        $this->assign ( 'wxtitle', "增加新地址" );
        $this->assign ( 'oid', $oid );
        $this->assign ('type' ,$_GET['type']);
        $this->_renderPage ();
    }


    private function mapAddressPost(DBF_UserAddress $F, $Data) {
        $_map = array (
            $F->ID => 'id',
            $F->Addressee => 'addressee',
            $F->Province => 'province',
            $F->City => 'city',
            $F->Region => 'region',
            $F->Address => 'address',
            $F->ZipCode => 'zipcode',
            $F->PhonePre => 'phonepre',
            $F->Mobile => 'mobile',
            $F->Phone => 'phone',
            $F->PhoneExt => 'phoneext',
            $F->IsDefault => 'defaultaddress'
        );
        $Result = array ();
        foreach ( $_map as $Key => $Map ) {
            $Result [$Map] = $Data [$Key];
        }
        return $Result;
    }

    /*
     * 地址编辑
    */
    public function addressedit(){
        $AddressID = I ( "id", '0', intval );
        $UAM = new UserAddressModel ();
        $AddressInfo=$UAM->getAddressByID($AddressID);
        //var_dump($this->mapAddressPost ( $UAM->F, $AddressInfo ));
        if ($AddressID) {
            $this->assign ( 'Post', $this->mapAddressPost ( $UAM->F, $AddressInfo ) );
        }

        //exit;
        if ($this->isPost ()) {
            $Post = $_POST;
            $this->assign ( 'Post', $Post );
            $AddressPost = $this->getAddressPost ();
            if (! $AddressPost) {return $this->displayError ( '必填信息没有输入完整' );}
            $AddressPost ['id'] = $AddressID;
            $UAM = $this->bulidAddressData ( $AddressPost );
            $UAM->startTrans ();
            $Result = $AddressID ? $UAM->save () : $UAM->add ();
            if ($Result === false) {
                //echo "aaa";
                $UAM->rollback ();
                return $this->displayError ( '连接失败，请稍后再试' );
            }

            if (! $AddressID || isset( $AddressPost ['defaultaddress'] )) {
                if ($UAM->setDefaultAddress ( $_SESSION ['f_userid'], $AddressID ) === false) {
                    $UAM->rollback ();
                    return $this->displayError ( '连接失败，请稍后再试' );
                }
            }
            $UAM->commit ();
            redirect ( WEBROOT_URL . "/user.php/iwxuser/address" );// 跳转
        }

        $AIPM = new AreaInfoPickerModel ();
        $UC = A ( "User/Cart" ); // 调用user分组下的sales模块

        $this->assign ( 'HtmlCtrl', $UC->getHtmlCtrl ( $AIPM ) );
        $this->assign ( 'wxtitle', "地址修改" );
        $this->display ();
    }

    //验证post传递过来的参数
    private function getAddressPost() {
        $PVC = new PVC2 ();
        $PVC->setModePost ()->setStrictMode ( false );
        $PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'province' );
        $PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'city' );
        //$PVC->isInt ()->Between ( 1, null )->validateMust ()->add ( 'region' );
        $PVC->isString ()->validateMust ()->add ( 'address' );
        //$PVC->isString ()->validateMust ()->add ( 'zipcode' );
        $PVC->isString ()->validateNotNull ()->add ( 'mobile' );
        //$PVC->isString ()->validateNotNull ()->add ( 'phonepre' );
        //$PVC->isString ()->validateNotNull ()->add ( 'phone' );
        //$PVC->isString ()->validateNotNull ()->add ( 'phoneext' );
        $PVC->isString ()->validateExists ()->add ( 'defaultaddress' );
        if (! $PVC->verifyAll ()) {
            return false;
        }
        if (! isset ( $PVC->ResultArray ['mobile'] ) && ! isset ( $PVC->ResultArray ['phone'] )) {
            return false;
        }
        return $PVC->ResultArray;
    }

    private function bulidAddressData($Post) {
        $UAM = new UserAddressModel ();
        $UAM->create ();
        if ($Post ['id']) {
            $UAM->{$UAM->F->ID} = $Post ['id'];
        } else {
            $UAM->{$UAM->F->UserID} = $_SESSION ['f_userid'];
        }
        if (! $Post ['defaultaddress']) {
            $UAM->{$UAM->F->IsDefault} = 0;
        }

        return $UAM;
    }


    /*
     * 订单详情
     */
    public function orderdetail() {
        $mmode=1;
        R ( 'User/Sales/orderdetail',array(1));

        //dump($this->get('orderdetail'));
    }

    /*
     * 我的微订单
     */
    public function ordermanage() {
        $orderchecked = I ( 'orderchecked', 0, 'intval' ); // 进行中的订单
        if ($orderchecked == 1) {
            $condition = "up_uid=" . $_SESSION ['f_userid'] . " and up_status=1 and up_checked=0";
        } else {
            $MonthSelet = I ( 'searchmonth', 0, 'intval' ); // 查询的订单周期
            $MonthOptions = array (
                0 => '三个月内的订单',
                1 => '三个月前的订单'
            );
            $this->assign ( "MonthOptions", $MonthOptions ); // select所有选项
            $this->assign ( "MonthSelect", $MonthSelet ); // select当前选中项

            $StatusSelect = I ( 'searchstatus', 0, 'intval' );
            $StatusOptions = array (
                0 => '所有订单',
                1 => '未支付',
                2 => '已支付',
                3 => '关闭的订单'
            );
            $this->assign ( "StatusOptions", $StatusOptions ); // select所有选项
            $this->assign ( "StatusSelect", $StatusSelect ); // select当前选中项
            $strnowtime = strtotime ( "-90 days" );

            $condition = "up_uid=" . $_SESSION ['f_userid'] . " and ";
            $condition .= ($MonthSelet == 0) ? "unix_timestamp(up_dealdate) > $strnowtime" : "unix_timestamp(up_dealdate) < $strnowtime";
            $condition .= $this->getconditionByStatus ( $StatusSelect ); // 附加订单状态条件
        }

        $this->assign ( "orderchecked", $orderchecked );
        $SM = M ( "user_prepaid" );
        import ( 'ORG.Util.Page' ); // 导入分页类
        $count = $SM->where ( $condition . ' and delsign="0"' )->count (); // 查询满足要求的总记录数
        $Page = new Page ( $count, 10 ); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->showwx(); // 分页显示输出

        $sql = "select TUP.up_express,TUP.up_id,TUP.up_orderid,TUP.up_dealdate,TUP.up_type,TUP.up_amount,TUP.up_efee,TUP.up_status,TUP.up_addressee,TUPD.up_product_info,TUP.up_amount_account,TUP.up_amount_total,TUP.up_done_status from tdf_user_prepaid as TUP ";
        $sql .= "Left Join tdf_user_prepaid_detail as TUPD ON TUPD.up_id=TUP.up_id ";
        $sql .= "where " . $condition . " and delsign=0 order by TUP.ctime desc limit " . $Page->firstRow . "," . $Page->listRows . "";

        $orderlist = $SM->query ( $sql );
        foreach ( $orderlist as $key => $value ) {
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
                        if ($k1 !== 'p_belongpid' & $k1 !== 'p_id' & $k1 !== 'p_cover' & $k1 !== 'p_name' & $k1 !== 'p_cate_3' & $k1 !== 'p_diy_id' & $k1 !== 'uc_producttype' & $k1 !== 'p_cate_4' & $k1 !== 'uc_isbind') {
                            unset ( $v [$k1] );
                        } // 消除产品其他数组元素
                    }
                    $productinfo [$k] = $v;
                    // miaomin added@2014/12/24
                    // 判断后设置链接的模块名
                    if ($v ['uc_producttype'] == 5) {
                        $productinfo [$k] ['linkedmodel'] = 'product';
                    } else {
                        $productinfo [$k] ['linkedmodel'] = 'models';
                    }
                }
                $orderlist [$key] ['en_up_orderid'] = $this->encode_pass ( $value ['up_orderid'], $_SESSION ['f_userid'], "encode" );
                $orderlist [$key] ['productinfo'] = $productinfo;
            }
            unset ( $orderlist [$key] ['up_product_info'] );
        }
        $UA = new UserAccountModel ();
        $UA_info = $UA->getUserAccountByUid ( $_SESSION ['f_userid'] );
        $user_account = $UA_info ['u_rcoin_av'];
        $this->assign ( 'user_account', $user_account );
        $this->assign ( 'wxtitle', "订单管理" );
        $this->assign ( 'page', $show ); // 赋值分页输出
        $this->assign ( "orderlist", $orderlist ); // select当前选中项
        $this->assign ( 'SearchResultCount', $count );
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

    /*
     * 微信订单提交
     */
    public function sendbill() {
        $orderid = I ( "orderid", '0', string );
        $out_trade_no = pub_encode_pass ( $orderid, $_SESSION ['f_userid'], "decode" );
        $UPM = new UserPrepaidModel ();
        $up_info = $UPM->getPrepaidListByOrderid ( $out_trade_no );
        $up_mount = $up_info [0] ['up_amount'];

        /**
         * miaomin added@2015.8.26 start
         */
        // $out_trade_no=$this->update_orderid($out_trade_no);
        /**
         * miaomin added@2015.8.26 end
         */

        Vendor ( 'Wxpay.WxPayPubHelper.WxPayPubHelper' );
        // 使用jsapi接口
        $jsApi = new JsApi_pub ();
        $up_mount = $up_mount * 100;
        // =========步骤1：网页授权获取用户openid============
        // 通过code获得openid
        if (! isset ( $_GET ['code'] )) {
            // 触发微信返回code码
            $url = $jsApi->createOauthUrlForCode ( WxPayConf_pub::JS_API_CALL_URL . "?orderid=" . $orderid );
            Header ( "Location: $url" );
        } else {
            // 获取code码，以获取openid
            $code = $_GET ['code'];
            $jsApi->setCode ( $code );
            $openid = $jsApi->getOpenId ();
        }

        /*
         * $code = $_GET['code']; $jsApi->setCode($code); $openid =
         * $jsApi->getOpenId();
         */
        // =========步骤2：使用统一支付接口，获取prepay_id============
        // 使用统一支付接口
        $unifiedOrder = new UnifiedOrder_pub ();

        // 设置统一支付接口参数
        // 设置必填参数
        // appid已填,商户无需重复填写
        // mch_id已填,商户无需重复填写
        // noncestr已填,商户无需重复填写
        // spbill_create_ip已填,商户无需重复填写
        // sign已填,商户无需重复填写
        $unifiedOrder->setParameter ( "openid", "$openid" ); // 商品描述
        $unifiedOrder->setParameter ( "body", "定制首饰" ); // 商品描述
        // 自定义订单号，此处仅作举例
        // $timeStamp = time();
        // $out_trade_no =
        // WxPayConf_pub::APPID."$timeStamp";
        $unifiedOrder->setParameter ( "out_trade_no", $out_trade_no ); // 商户订单号
        $unifiedOrder->setParameter ( "total_fee", $up_mount ); // 总金额
        $unifiedOrder->setParameter ( "notify_url", WxPayConf_pub::NOTIFY_URL ); // 通知地址
        $unifiedOrder->setParameter ( "trade_type", "JSAPI" ); // 交易类型
        // 非必填参数，商户可根据实际情况选填
        // $unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        // $unifiedOrder->setParameter("device_info","XXXX");//设备号
        // $unifiedOrder->setParameter("attach","XXXX");//附加数据
        // $unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        // $unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        // $unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        // $unifiedOrder->setParameter("openid","XXXX");//用户标识
        // $unifiedOrder->setParameter("product_id","XXXX");//商品ID

        $prepay_id = $unifiedOrder->getPrepayId ();
        // =========步骤3：使用jsapi调起支付============
        $jsApi->setPrepayId ( $prepay_id );

        $jsApiParameters = $jsApi->getParameters ();
        $this->assign ( "out_trade_no", $out_trade_no );
        $this->assign ( 'jsApiParameters', $jsApiParameters );
        // echo $jsApiParameters;
        $this->display ();
    }

    //我的方案列表
    public function diylist(){
        //$mmode=I('mmode',0,intval);
        R ( 'User/Iwxmydiy/jewelrylist');

        // $this->display();
    }
    /*
    //分享到朋友圈,生成码并且跳转到aftershare页面
    public function sharetowx($s){
        $user = M('users')->getFieldByU_id(session('f_userid'),'u_email');
        $etid = M('coupon_type')->getFieldByEt_name('闺蜜节','et_id');
        if($s != md5($user."guimijie")){
            $this->redirect("sharetotimeline",array(),3,'error:invalid operation,3 secs reutrn to lastPage');
        }
        //coupon_type没有闺蜜节字典字条记录，自动添加
        if($etid == NULL){
            $data['et_name'] = "闺蜜节";
            $data['et_private'] = "2";
            $data['et_type'] = "2";
            $data['et_amount'] = "40";
            $data['et_createdate'] = get_now();
            $data['et_expiredate'] = "2015-05-31 23:59:59";
            M('coupon_type')->add($data);
            $etid = M('coupon_type')->getFieldByEt_name('闺蜜节','et_id');
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
        $this->redirect("aftershare",array('msg'=>'0'),0,"success:U get one guimijie ecoupon,3 secs reutrn to mycoupon");
    }
    //分享主页面
    public function sharetotimeline(){
        $user = M('users')->getFieldByU_id(session('f_userid'),'u_email');
        $key = 'guimijie';
        $salt = md5($user."guimijie");
        Vendor('wxshare.jssdk');
        $jssdk = new JSSDK("wxf71f9222d3bc3c2e", "7338ad0158221be35c8572c562a56899");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $this->assign('salt',$salt);
        $this->display();
    }
    //分享后跳转到该页面
    public function aftershare($msg = ""){
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
    */

    /*
     * 更新订单号
     * @param $orderid 订单号
     * @return 新的订单号
     */
    private function update_orderid($orderid){
        $UPM=new UserPrepaidModel();
        if($orderid){
            $orderid_new=$UPM->updateOrderSuffix($orderid);
        }
        return $orderid_new;
    }

}
?>