<?php

class AlipayMobModel extends Model {

    public function __construct() {
        Vendor('AlipayMob.config');
        Vendor('AlipayMob.alipay_core');
        Vendor('AlipayMob.alipay_rsa');
        $alipay_config=alicofingsMob();

        $this->partner = $alipay_config['partner'];
        $this->seller_email = $alipay_config['seller_email'];
        $this->service = $alipay_config['service'];
        $this->private_key_path = $alipay_config['private_key_path'];
        $this->public_key_path = $alipay_config['ali_public_key_path'];
        parent::__construct ();
    }

    public function createSign($orderid){
        $UPM=new UserPrepaidModel();
        if($UPM->updateOrderSuffix($orderid)){//更新订单号后缀加1
            $orderInfo=$UPM->getPrepaidListByOrderid($orderid);
        }
        //var_dump($orderInfo);
        //exit;
        $order_info_array = argSort(array(
            'partner' =>  '"'.$this->partner.'"',
            'seller_id' =>  '"'.$this->seller_email.'"',
            'out_trade_no' =>'"'.$orderInfo[0]['up_orderid_new'].'"',
            'subject'=>'"3dcity首饰定制"',
            'body'=>'"3dcity首饰定制"', //商品描述
            'total_fee' => '"'.$orderInfo[0]['up_amount'].'"',
            'notify_url' => '"http://140.207.154.14/user.php/Ordermob/notifyurl"',
            'service' =>  '"'.$this->service.'"',
            'payment_type' => '"1"',
            '_input_charset' => '"utf-8"',
            'it_b_pay'=>'"30m"',
            'show_url' =>'"m.alipay.com"'//用户手机未装支付宝客户端时访问支付宝H5网页版
        ));
       $order_info = createLinkstring(paraFilter($order_info_array));// 组合数据
       //logResult($order_info);
        //生成签名
//$order_info ='app_id=2016071201609127&biz_content={"timeout_express":"30m","seller_id":"","product_code":"QUICK_MSECURITY_PAY","total_amount":"0.01","subject":"1","body":"我是测试数据","out_trade_no":"J2MYHLG1EIK42MF"}&charset=utf-8&method=alipay.trade.app.pay&sign_type=RSA&timestamp=2016-10-09 13:34:56&version=1.0';
        $sign = rsaSign($order_info, $this->private_key_path);
// logResult($sign);
        $sign = urlencode($sign);// 对签名进行url编码
    //echo $sign;
        $pay_info = $order_info . "&sign=\"" . $sign . "\"&sign_type=\"RSA\"";
       // logResult($pay_info);
        //echo json_encode(array(
          //  'code' => 1,
        //    'result' => $pay_info,
       // ));
        return  $pay_info ;
    }

    //获取APP的扫码支付参数
    public function createQrCodeParam($orderid,$methodType=0){
        $UPM=new UserPrepaidModel();
        if($UPM->updateOrderSuffix($orderid)){//更新订单号后缀加1
            $orderInfo=$UPM->getPrepaidListByOrderid($orderid);
        }
        $nowdate=date ( "Y-m-d H:i:s", time () );
        //echo $nowdate;
        //exit;
        if(!$methodType){
            $method='alipay.trade.precreate';
        }else{
            $method='alipay.trade.app.pay';
        }
        $order_info_array = argSort(array(
            'method'    =>$method,
            'app_id'    =>'2016071201609127',
            'charset'   =>'utf-8',
            'sign_type' =>'RSA',
            'timestamp' =>$nowdate,
            'biz_content' =>  '{
                "out_trade_no": "'.$orderInfo[0]['up_orderid_new'].'",
                "total_amount":"'.$orderInfo[0]['up_amount'].'",
                "discount_amount":"0",
                "unDiscount_amount": "'.$orderInfo[0]['up_amount'].'",
                "subject": "当面付二维码支付"
            }',
            'notify_url' => '"http://140.207.154.14/user.php/Ordermob/notifyurl"',
        ));
        $order_info = createLinkstring(paraFilter($order_info_array));// 组合数据

        $sign = rsaSign($order_info, $this->private_key_path);
        $sign = urlencode($sign);// 对签名进行url编码
        $pay_info = $order_info . "&sign=\"" . $sign . "\"&sign_type=RSA";
        return  $pay_info ;
    }


    //获取APP的扫码支付参数
    public function createQrCodeParamByApp($orderid){
        $UPM=new UserPrepaidModel();
        if($UPM->updateOrderSuffix($orderid)){//更新订单号后缀加1
            $orderInfo=$UPM->getPrepaidListByOrderid($orderid);
        }
        $nowdate    = date("Y-m-d H:i:s");
        //$nowdate    ='2016-08-25:20:26:31';
        $method     = 'alipay.trade.app.pay';
        $order_info_array = argSort(array(
            'method'=>$method,
            'app_id'=>'2016071201609127',
            'charset'=>'utf-8',
            'sign_type'=>'RSA',
            'timestamp'=>$nowdate,
            'version' =>'1.0',
            'notify_url' => 'http://140.207.154.14/user.php/Ordermob/notifyurl',
            'biz_content' =>  '{"timeout_express":"30m","product_code":"QUICK_MSECURITY_PAY","total_amount":"'.$orderInfo[0]['up_amount'].'","subject":"支付宝","body":"支付宝客户端","out_trade_no":"'.$orderInfo[0]['up_orderid_new'].'"}'
        ));
//        print_r($order_info_array) ;
//        echo "<br>";

       // 'biz_content' =>  '{"timeout_express":"30m","product_code":"QUICK_MSECURITY_PAY","total_amount":"'.$orderInfo[0]['up_amount'].'","subject":"支付宝","body":"支付宝客户端","out_trade_no":"'.$orderInfo[0]['up_orderid_new'].'"}'
        //echo $order_info_array['biz_content'];
/*$order_info = createLinkstring(paraFilter($order_info_array));// 组合数据*/
        $order_info = createLinkstring($order_info_array);// 组合数据*
        /*$order_info ='app_id=2016071201609127&biz_content={
            "timeout_express":"30m",
            "seller_id":"",
            "product_code":"QUICK_MSECURITY_PAY",
            "total_amount":"0.01",
            "subject":"1",
            "body":"我是测试数据",
            "out_trade_no":"J2MYHLG1EIK42MF"}
            &charset=utf-8&method=alipay.trade.app.pay&sign_type=RSA&timestamp=2016-10-09 13:34:56&version=1.0';
        */
//        echo $order_info;
//        echo "<br>+++++";
//        $temp_info="app_id=2016071201609127&biz_content={\"body\":\"支付宝客户端\",\"total_amount\":\"0.01\",\"subject\":\"支付宝\",\"out_trade_no\":\"OZ02IW8GV5G9KA0\",\"timeout_express\":\"30m\",\"product_code\":\"QUICK_MSECURITY_PAY\"}&charset=utf-8&method=alipay.trade.app.pay&notify_url=http://140.207.154.14/user.php/Ordermob/notifyurl&sign_type=RSA&timestamp=2016-10-10 10:53:20&version=1.0";
//        echo $temp_info;
//        echo "<br>";
//        $temp_sign=rsaSign($temp_info, $this->private_key_path);
//        echo $temp_sign;

//        exit;
        $sign = rsaSign($order_info, $this->private_key_path);

//$sign = urlencode($sign);// 对签名进行url编码
        $pay_info   = $order_info;
//        echo $pay_info;
//        echo "<br>------";
        $pay_info   = $pay_info . "&sign=" . $sign ;
        $pay_info_arr=explode('&',$pay_info);
//        ksort($pay_info_arr);

        foreach($pay_info_arr as $key => $value){
            $value_arr=explode('=',$value);
            $result_info.="$value_arr[0]=".urlencode($value_arr[1])."&";
        }
        //$result_info   = $result_info . "sign=" . $sign ;

        $result_info=substr($result_info, 0, -1);
//        echo $result_info;
//        exit;
        //var_dump($result_info);
        //exit;
        //$pay_info   = urlencode($pay_info);
        //echo $pay_info;
        return  $result_info ;
    }

}

?>
