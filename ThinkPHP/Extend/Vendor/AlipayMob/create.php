<?php
include "./alipay.config.php";
include "./lib/alipay_notify.class.php";


$order_info_array = argSort(array(
    '_input_charset' => "utf-8",
    'body' => "3dcity首饰定制",
    'notify_url' => "http://www.3dcity.com",
    'out_trade_no' => "2012010111",
    'partner' => $alipay_config['partner'],
    'payment_type' => 1,
    'seller_id' => $alipay_config['seller_email'],
    'service' => $alipay_config['service'],
    'subjet' => "首饰",
    'total_fee' => "1",
    'showUrl' =>'m.alipay.com',//用户手机未装支付宝客户端时访问支付宝H5网页版
));
// 组合数据
$order_info = createLinkstring(paraFilter($order_info_array));
logResult($order_info);
// 生成签名
$sign = rsaSign($order_info, $alipay_config['private_key_path']);
logResult($sign);
// 对签名进行url编码
//$sign = urlencode($sign, 'UTF-8');

$pay_info = $order_info . "&sign=\"" . $sign . "\"&sign_type=\"RSA\"";
logResult($pay_info);

//echo $pay_info;
echo json_encode(array(
    'code' => 1,
    'data' => $pay_info,
));