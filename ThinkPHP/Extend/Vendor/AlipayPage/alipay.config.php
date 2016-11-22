<?php
/* *
 * 配置文件
 * 版本：3.5
 * 日期：2016-06-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['partner']		= '2088421410566234';

//收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
$alipay_config['seller_id']	= $alipay_config['partner'];

//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
//$alipay_config['private_key']	= 'MIICXQIBAAKBgQDiXfJuXdocJXIWI4fcwYVJnec7o+W2SKJXbqBDJ+YddymbtbYdPOa9maBJfgkvVeq5IkYMTCp7QRJWOZM6j1+lsD5YL5+VDOf4Whob0+u1LrDqg5K5qLjDticRt4rPz7KTMWRiLFBPPZXsWCdXWgU1kkag7TUBlPN9FxFJdHXsHwIDAQABAoGAClkpIAqCUg8ULBbruxfNtBPX4k4XzkF7ymtwQCGuz2IHMOsZrDSAh+JiBXoRiWxwEo6SjTVeK8xJvk9bf63WPXLPBMxXly2f7b0k3osP0sjfwWSJD0TXGsHObcQEv5o2bBzEEOzGLULWc9xdwVm0SN/i73xHWEFx+7hOD9m3hQECQQD5ezP38nHqISwC0f7qB3Bc77OQpsN8akhplCv4XfVFUUGP6W9kgw0KXhcct7gXADm61p6Acc5qh3goq9WueJDhAkEA6EghiawnIitWrIGPazo97In+L+R1f4ATUUHuFRdadVumzj1IJ+vYgCUlT84mqAyf4ruZOJGYRIxjwMk9yiUc/wJARNJe6LRiBmN2P6TsfGTm80xQrcPDQ6wSI8XzR0WsJp4exvNvfjvEuipwl8R6rSWrx0XzXZ2Hgs2yMQ4OGV47IQJBAMj1FqbgHJ8wNXVbRbQ0GDzbieDvW0Qqjwve6Vav9J/R4jdNx25aWd1LxcMMjF8ZRP5I5R+OvtiXSPoSYPXRNAsCQQCzGKu1U5S87J8zJjtKsTeqgjM4B6I2D9swcdmMTIfsAFmuPNSW40wMfUNjJHSer/pqU7FV6E/n0sXP+6ZEJ4Qq';
$alipay_config['private_key'] = 'MIICXQIBAAKBgQDiXfJuXdocJXIWI4fcwYVJnec7o+W2SKJXbqBDJ+YddymbtbYdPOa9maBJfgkvVeq5IkYMTCp7QRJWOZM6j1+lsD5YL5+VDOf4Whob0+u1LrDqg5K5qLjDticRt4rPz7KTMWRiLFBPPZXsWCdXWgU1kkag7TUBlPN9FxFJdHXsHwIDAQABAoGAClkpIAqCUg8ULBbruxfNtBPX4k4XzkF7ymtwQCGuz2IHMOsZrDSAh+JiBXoRiWxwEo6SjTVeK8xJvk9bf63WPXLPBMxXly2f7b0k3osP0sjfwWSJD0TXGsHObcQEv5o2bBzEEOzGLULWc9xdwVm0SN/i73xHWEFx+7hOD9m3hQECQQD5ezP38nHqISwC0f7qB3Bc77OQpsN8akhplCv4XfVFUUGP6W9kgw0KXhcct7gXADm61p6Acc5qh3goq9WueJDhAkEA6EghiawnIitWrIGPazo97In+L+R1f4ATUUHuFRdadVumzj1IJ+vYgCUlT84mqAyf4ruZOJGYRIxjwMk9yiUc/wJARNJe6LRiBmN2P6TsfGTm80xQrcPDQ6wSI8XzR0WsJp4exvNvfjvEuipwl8R6rSWrx0XzXZ2Hgs2yMQ4OGV47IQJBAMj1FqbgHJ8wNXVbRbQ0GDzbieDvW0Qqjwve6Vav9J/R4jdNx25aWd1LxcMMjF8ZRP5I5R+OvtiXSPoSYPXRNAsCQQCzGKu1U5S87J8zJjtKsTeqgjM4B6I2D9swcdmMTIfsAFmuPNSW40wMfUNjJHSer/pqU7FV6E/n0sXP+6ZEJ4Qq';

//支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
//$alipay_config['alipay_public_key']= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQChTGjqCjbdvXpfCsvgFVoCkzUPv8j/G7C8/v5V quH3l/D9ZLfUieBIvsT3A3lRjIJpIWdFoXtEzW/qPOLJTSN+nly4CAyauhAY6bAzjjXH+1tfOxLC KDR3ciIYl70DURoPORRFKYxjdwx23/E/4GLo2EKIu89HCKHS2N83viUCvwIDAQAB';
  $aplipay_config['alipay_public_key']='fMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';
// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['notify_url'] = "http://www.ignjewelry.com/user.php/Orderpage/returnurl";

// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['return_url'] = "http://www.ignjewelry.com/user.php/Orderpage/notifyurl";

//签名方式
$alipay_config['sign_type']    = strtoupper('RSA');

//字符编码格式 目前支持utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';

// 支付类型 ，无需修改
$alipay_config['payment_type'] = "1";
		
// 产品类型，无需修改
$alipay_config['service'] = "alipay.wap.create.direct.pay.by.user";

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

?>