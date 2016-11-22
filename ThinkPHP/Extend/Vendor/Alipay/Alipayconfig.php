<?php
function alicofings(){
$alipay_config['partner']      = '2088421410566234';

//安全检验码，以数字和字母组成的32位字符
$alipay_config['key']          = 'f58gtuj74gd9n517tq2g5q3mqn24yubp';

//签约支付宝账号或卖家支付宝账户
$alipay_config['seller_email'] = 'miaomin@bitmap3d.com.cn';

//页面跳转同步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
//return_url的域名不能写成http://localhost/create_direct_pay_by_user_php_utf8/return_url.php ，否则会导致return_url执行无效
$alipay_config['return_url']   = 'http://www.ignjewelry.com/user.php/Order/returnurl';

//服务器异步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
$alipay_config['notify_url']   = 'http://www.ignjewelry.com/user.php/Order/notifyurl';

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


$alipay_config['ali_public_key_path'] = 'key/alipay_public_key.pem';




//签名方式 不需修改
$alipay_config['sign_type']    = strtoupper('MD5');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';
return $alipay_config;
}
?>