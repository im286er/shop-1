<?php
/**
 * Created by PhpStorm.
 * User: guolixun
 * Date: 2016/9/13
 * Time: 16:58
 */
class OrderpageAction extends CommonAction {
    public function _initialize() {
        Vendor('AlipayPage.config');
        Vendor('AlipayPage.alipay_core');
        Vendor('AlipayPage.alipay_rsa');
        Vendor('AlipayPage.alipay_notify');
        Vendor('AlipayPage.alipay_submit');
    }

    function alipayPage($out_trade_no, $total_fee, $go_subject = "ignjewelry.com", $go_body = "ignjewelry.com 支付", $paym = "", $bankc = "") {
        /**************************请求参数**************************/
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $out_trade_no;
        //订单名称，必填
        $subject = $go_subject;
        //付款金额，必填
        $total_fee = $total_fee;
        //收银台页面上，商品展示的超链接，必填
        $show_url = '';
        //商品描述，可空
        $body = $go_body;
        /************************************************************/
        $alipay_config = alicofingsPage ();
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service"       => $alipay_config['service'],
            "partner"       => $alipay_config['partner'],
            "seller_id"  => $alipay_config['seller_id'],
            "payment_type"	=> $alipay_config['payment_type'],
            "notify_url"	=> $alipay_config['notify_url'],
            "return_url"	=> $alipay_config['return_url'],
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "total_fee"	=> $total_fee,
            "show_url"	=> $show_url,
            //"app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
            "body"	=> $body,
            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
            //如"参数名"	=> "参数值"   注：上一个参数末尾需要“,”逗号。

        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        echo $html_text;
    }


    /*
     * 功能：支付宝页面跳转同步通知页面 版本：3.3 日期：2012-07-23
     * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见vender下的alipay下的notify.php中的函数verifyReturn
     */
    public function returnurl() { //
        $aliapy_config = alicofingsPage ();
        $alipayNotify = new AlipayNotify ( $aliapy_config );
        $verify_result = $alipayNotify->verifyReturn ();

        $out_trade_no = $_GET ['out_trade_no']; // 获取订单号
        $trade_no = $_GET ['trade_no']; // 获取支付宝交易号
        $total_fee = $_GET ['total_fee']; // 获取总价格
        if ($verify_result) { // 验证成功
            $d = D ( 'User_prepaid' );
            $result = $d->where ( "up_orderid_new='" . $out_trade_no . "'" )->select (); // 查询对应order_id的记录
            $status = $result [0] ['up_status'];
            $up_amount = $result [0] ['up_amount'];
            $out_trade_no_orderid= $result [0] ['up_orderid'];
            if ($_GET ['trade_status'] == 'TRADE_FINISHED' || $_GET ['trade_status'] == 'TRADE_SUCCESS') {
                // -------------------------------------------更新支付状态和回单号、回单时间、
                // 并增加积分、日志、和已购买的模型^
                if ($status == 0 && $up_amount == $total_fee) {
                    $UM = new UserAccountModel ();
                    $UM->addLogAndRCoin ( $out_trade_no_orderid, $trade_no, $total_fee ); // 包含up_type为0,1,3 的操作判断
                }
                // -------------------------------------------更新支付状态和回单号、回单时间
                //日志
            }else{
                echo "trade_status=" . $_GET ['trade_status'];
                $this->assign ( 'msg', '充值失败!' );
            }

            echo "验证成功<br />";
            echo "trade_no=" . $trade_no;

            $pay_done_url = "/sales/payresult/mmode/1/otype/0/out_trade_no/".$out_trade_no_orderid;
            $this->redirect ( $pay_done_url );
        } else {
            $this->assign ( 'msg', '验证失败' );
            echo "False";
        }

        // $this->display();
    }

    /*
     * function testsave(){ $UM = new UserAccountModel();
     * $out_trade_no="138683052809258109"; $trade_no="ceshi123456789";
     * $total_fee=168; $UM->addLogAndRCoin($out_trade_no,$trade_no,$total_fee);
     * }
     */
    public function getpayresult(){
        $total_fee		= $_POST ['payprice'];
        $out_trade_no 	= $_POST ['oid'];
        $payresult 		= $_POST ['payquery'];
        $UID			= $_POST['uid'];


        // miaomin edited@2014.11.12
        // 扣款金额从数据库中获取
        //$UID = session ( 'f_userid' );
        $UPM = new UserPrepaidModel ();

        $condition = array (
            $UPM->F->OrderID => $out_trade_no,
            $UPM->F->UserID => $UID
        );
        //$condition="up_orderid='".$out_trade_no."' and up_uid=".$UID."";
        $upmRes = $UPM->where( $condition )->find();
        if($upmRes){
            $db_total_fee = $upmRes [$UPM->F->Amount] + $upmRes [$UPM->F->Efee];
            if($db_total_fee == $total_fee) {
                //$UPM->updateAccountPaytypeByOrderid($out_trade_no);	//设置支付方式为余额支付
                $UM = new UserAccountModel ();
                $result = $UM->addLogAndRcoin_order ( $out_trade_no, $total_fee );
                echo $result;
            }
        }
        echo false;
    }

    /*
     * 功能：支付宝服务器异步通知
     * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
     * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
     */
    public function notifyurl() {
        $aliapy_config = alicofingsPage ();
        $alipayNotify = new AlipayNotify ( $aliapy_config );
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) { // 验证成功
            $out_trade_no = $_POST ['out_trade_no']; // 获取订单号
            $trade_no = $_POST ['trade_no']; // 获取支付宝交易号
            $total_fee = $_POST ['total_fee']; // 获取总价格
            $d = D ( 'User_prepaid' );
            $result = $d->where ( "up_orderid_new='" . $out_trade_no . "'" )->select (); // 查询对应order_id的记录
            $status = $result [0] ['up_status'];
            $up_amount = $result [0] ['up_amount'];
            $out_trade_no_orderid= $result [0] ['up_orderid'];
            logResult ( "<br>" . date ( 'Y-m-d H:i:s', NOW_TIME ) . "WEB手机支付执行异步通知，" .$resultSQL. $up_amount . "start-----<br>" );
            if ($_POST ['trade_status'] == 'TRADE_FINISHED' || $_POST ['trade_status'] == 'TRADE_SUCCESS') {
                // -------------------------------------------更新支付状态和回单号、回单时间、
                // 并增加积分、日志^
                if ($status == 0 && $up_amount == $total_fee) {
                    $UM = new UserAccountModel ();
                    $UM_result = $UM->addLogAndRCoin ( $out_trade_no_orderid, $trade_no, $total_fee );
                    if ($UM_result) {
                        logResult ( "<br>" . date ( 'Y-m-d H:i:s', NOW_TIME ) . "WEB手机支付执行异步通知，" . $out_trade_no_orderid . "成功<br>" );
                    }
                }
                // -------------------------------------------更新支付状态和回单号、回单时间
                // 、日志v
            }

            // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            // 验证失败
            echo "fail";

            // 调试用，写文本函数记录程序运行情况是否正常
            logResult ( "<br>" . date ( 'Y-m-d H:i:s', NOW_TIME ) . "WEB手机支付执行异步通知,未成功<br>" );
        }
    }

    public function paySuccessMail(){
        $out_trade_no='141767007223212727';
        $trade_no='111222333';
        $total_fee=197;
        $UAM=new UserAccountModel();
        $UAM->addLogAndRCoin($out_trade_no, $trade_no, $total_fee);
    }


    function alipay_close($out_order_no) {

        $alipay_config = alicofingsPage ();
        //支付宝交易号与商户订单号不能同时为空
        //$trade_no = $_POST['WIDtrade_no'];			//支付宝交易号
        //$out_order_no = $_POST['WIDout_order_no'];	//商户订单号
        $trade_no="";
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" 		=> "close_trade",
            "partner" 		=> trim($alipay_config['partner']),
            "trade_no"		=> $trade_no,
            "out_order_no"	=> $out_order_no,
            "_input_charset"=> trim(strtolower($alipay_config['input_charset']))
        );
        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($parameter);
        //解析XML
        //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
        $doc = new DOMDocument();
        $doc->loadXML($html_text);

        //请在这里加上商户的业务逻辑程序代码
        //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
        //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

        //解析XML
        if( ! empty($doc->getElementsByTagName( "alipay" )->item(0)->nodeValue) ) {
            $alipay = $doc->getElementsByTagName( "alipay" )->item(0)->nodeValue;
            echo $alipay;
        }
        //exit;
    }

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

    /*
     * APP单个产品生成订单
     */
    public function apporder(){
        echo "abc";
    }




}

?>