<?php

class WeixinpayMobModel extends Model {

    public function __construct() {
        Vendor ( 'Wxpay.WxPayPubHelper.WxPayPubHelper');
        parent::__construct ();
    }

    /*
     * 微信
     */
    public function wxQrCode($orderid){
        $UPM=new UserPrepaidModel();
        //$orderInfo=$UPM->getPrepaidListByOrderid($orderid);
        if($UPM->updateOrderSuffix($orderid)){//更新订单号后缀加1
            $orderInfo=$UPM->getPrepaidListByOrderid($orderid);
            $out_trade_no=$orderInfo[0]['up_orderid_new'];
        }
        $unifiedOrder = new UnifiedOrder_pub();
        $up_amount=$orderInfo[0]['up_amount'];
        $total_fee=$up_amount*100;
        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $show_name="IGNITE 定制饰品";
        $unifiedOrder->setParameter("body",$show_name);//商品描述
        //自定义订单号，此处仅作举例
        $timeStamp = time();
        //$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
        $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
        $unifiedOrder->setParameter("total_fee",$total_fee);//总金额
        $unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址
        $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
        //var_dump($unifiedOrder);
        //exit;
        //获取统一支付接口结果
        $unifiedOrderResult = $unifiedOrder->getResult();
        //商户根据实际情况设置相应的处理流程
        if ($unifiedOrderResult["return_code"] == "FAIL")
        {
            //商户自行增加处理流程
            echo "通信出错：".$unifiedOrderResult['return_msg']."<br>";
        }
        elseif($unifiedOrderResult["result_code"] == "FAIL")
        {
            //商户自行增加处理流程
            echo "错误代码：".$unifiedOrderResult['err_code']."<br>";
            echo "错误代码描述：".$unifiedOrderResult['err_code_des']."<br>";
        }
        elseif($unifiedOrderResult["code_url"] != NULL)
        {
            //从统一支付接口获取到code_url
            $code_url = $unifiedOrderResult["code_url"];
            //商户自行增加处理流程
            //......
        }
        return $code_url;
        //var_dump($unifiedOrderResult);

    }



}

?>
