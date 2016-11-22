AlipayOrderData *order = [[AlipayOrderData alloc] init];
order.partner = partner;
order.seller = seller;
order.tradeNO = self.orderData.result; //订单ID
order.productName = self.orderData.productTitle; //商品标题
order.productDescription = self.orderData.producDetail; //商品描述
order.amount = self.orderData.orderPrice; //商品价格
order.notifyURL =  @"http://www.3dcity.com"; //回调URL

文村2015-09-17 17:09:51 182        AlipayOrderData *order = [[AlipayOrderData alloc] init];
order.partner = partner;
order.seller = seller;
order.tradeNO = self.orderData.result; //订单ID
order.productName = self.orderData.productTitle; //商品标题
order.productDescription = self.orderData.producDetail; //商品描述
order.amount = self.orderData.orderPrice; //商品价格
order.notifyURL =  @"http://www.3dcity.com"; //回调URL

order.service = @"mobile.securitypay.pay";
order.paymentType = @"1";
order.inputCharset = @"utf-8";
order.itBPay = @"30m";
order.showUrl = @"m.alipay.com";

文村2015-09-17 17:10:00 183
//应用注册scheme,在AlixPayDemo-Info.plist定义URL types
NSString *appScheme = @"3dcityAlipay";

//将商品信息拼接成字符串
NSString *orderSpec = [order description];
NSLog(@"orderSpec = %@",orderSpec);

//        获取私钥并将商户信息签名,外部商户可以根据情况存放私钥和签名,只需要遵循RSA签名规范,并将签名字符串base64编码和UrlEncode
id<DataSigner> signer = CreateRSADataSigner(privateKey);
    NSString *signedString = [signer signString:orderSpec];
    文村2015-09-17 17:30:38 184 NSString *orderString = nil;
    if (signedString != nil) {
    orderString = [NSString stringWithFormat:@"%@&sign=\"%@\"&sign_type=\"%@\"",
    orderSpec, signedString, @"RSA"];
