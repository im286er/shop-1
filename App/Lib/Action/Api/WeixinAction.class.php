<?php
/**
 * Weixin相关API
 *
 * @author zhangzhibin 
 * 2015-01-15
 */
class WeixinAction extends CommonAction {
    // TODO
    // 魔术方法
    public function __call($name, $arguments) {
        throw new Exception ( $this->RES_CODE_TYPE ['METHOD_ERR'] );
    }


    public function getwxrepayid(){
        $res = array ();        // 返回结果
        $args = func_get_args ();
        $args = $this->decodeArguments ( $args );
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
        if (! $logindata) {
            throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
        }
        if($args['datas']){
            $datas=json_decode(base64_decode($args['datas']),true);
        }
        $up_orderid=$datas['orderid'];
        $UPM=new UserPrepaidModel();
        $OrderInfo=$UPM->getPrepaidListByOrderid($up_orderid);

        /*$a['timeStamp']=intval($OrderInfo[0]['up_amount']);
        $res[]=$a;
        return $res;*/
        Vendor ( 'Wxpay.WxNative.WxPayPubHelper');
        $unifiedOrder = new UnifiedOrder_pub();
        $total_fee     =doubleval($OrderInfo[0]['up_amount'])*100;
        //var_dump($total_fee);
        $out_trade_no   =$OrderInfo[0]['up_orderid'];
        $show_name      ="首饰定制";
        $spbill_create_ip=$_SERVER["REMOTE_ADDR"];
        $unifiedOrder->setParameter("out_trade_no",$out_trade_no);//商户订单号
        $unifiedOrder->setParameter("body",$show_name);//商品描述
        $unifiedOrder->setParameter("total_fee",$total_fee);//总金额
        $unifiedOrder->setParameter("spbill_create_ip",$spbill_create_ip);//总金额
        $unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址
        $unifiedOrder->setParameter("trade_type","APP");//交易类型
        $unifiedOrderResult = $unifiedOrder->getResult();
        //var_dump($unifiedOrderResult);
        if($unifiedOrderResult){
            $unifiedOrderResult['timeStamp']=time();
            $unifiedOrderResult['packageValue']="Sign=WXPay";
        }
        $res[]=$unifiedOrderResult;
        return $res;
	}

}
?>