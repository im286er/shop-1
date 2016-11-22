<?php 
ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);

require_once "WxPay.Api.php";
require_once "WxPay.JsApiPay.php";

session_start();

//①、获取用户openid
$tools = new JsApiPay();
$openId = $tools->GetOpenid();

//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("微信支付");
$input->SetAttach("微信支付");
$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
$input->SetTotal_fee($_SESSION['heji_sum'] * 100);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("微信支付");
$input->SetNotify_url("http://string2.xicp.net/fangeshi/wx_pay/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);

$jsApiParameters = $tools->GetJsApiParameters($order);

?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>微信支付</title>

    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				var str = res.err_code+res.err_desc+res.err_msg;
				if(str.substr(str.length-2) == 'ok'){
					var orders_id = document.getElementById("orders_id").value;	//订单ID
					window.location.href = "http://www.gobistone.cn/";
				}
				//else{
					//alert(res.err_code+res.err_desc+res.err_msg);
				//}
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>

</head>
<body>
    <br/>
    <input type="hidden" name="orders_id" id="orders_id" value="<?php echo $_SESSION['orders_id']; ?>" />
    <div align="center" style="margin-bottom: 10px;">实付金额</div>
    <div align="center" style=" font-size:50px; margin-bottom: 15px; font-weight:600;"><span style="font-size:24px;">￥</span> <?php echo $_SESSION['heji_sum']; ?> <span style="font-size:20px;">元</span></div>
	<div align="center" style=" border-top: 1px solid #EDEDED; border-bottom: 1px solid #EDEDED;">
	<div style="line-height: 40px;"><span style=" float:left; color:#666;">收款方：</span><span style=" float:right;">梵戈石生态圈</span></div>
	<div style="clear: both;"></div>
	<div style="line-height: 40px;"><span style=" float:left; color:#666;">商&nbsp;&nbsp;&nbsp;&nbsp;品：</span><span style=" float:right;">梵戈石生态圈</span></div>
	<div style="clear: both;"></div>
	</div>
	<div align="center" style="margin-top: 20px;">
		<button style="width:95%; height:45px; border-radius: 5px;background:#43C01A; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
	</div>
</body>
</html>