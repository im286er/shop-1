<?php
/*
*
*   时间：2014-11-18
*	用户订单快递信息
*	By zhangzhibin
*/
class UserPrepaidExpressModel extends Model{
	//Protected $autoCheckFields = false;//关闭字段信息自动检测
	
	
	/*根据订单up_id返回HTML格式的快递详情
	 * @param int $up_id 订单的处理单号(up_id) 
	 */
	public function getExpressHtml($up_id){
		$express=$this->getExpressByUpid($up_id);
		$com=$express['express_com'];
		$nu=$express['express_number'];
		$result=$this->getExpressApi($com,$nu);
		return $result;
	}
	
	/*根据订单up_id返回快递公司和单号
	 * @param int $up_id 订单的处理单号(up_id)
	 */
	public function getExpressByUpid($up_id){
		$result=M("user_prepaid_express")->where("up_id=".$up_id)->find();
		$express_com = L ('express_com');
		if($result){
			$result['express_com_name']=$express_com[$result['express_com']];
		}
		
		return $result;
	}

	
	
	/*  @param string $com 查询的快递公司代码
	 *  @param string $nu 快递单号
	 */
	public function getExpressApi($com,$nu,$backType=0){//输出快递物流跟踪信息
		Vendor ( 'Express.Snoopy');
		$AppKey='eedcd565bf5b507e';//请将XXXXXX替换成您在http://kuaidi100.com/app/reg.html申请到的KEY
		//$url ='http://api.kuaidi100.com/api?id='.$AppKey.'&com='.$com.'&nu='.$nu.'&show=2&muti=1&order=asc'; //直接查询接口的url
		$url= 'http://www.kuaidi100.com/applyurl?key='.$AppKey.'&com='.$com.'&nu='.$nu;//生成完整的请求URL
		//请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
		$powered = '查询数据由<a href="http://kuaidi100.com" target="_blank">快递100sss</a>网站提供 ';
		
		//优先使用curl模式发送数据
		if (function_exists('curl_init') == 1){
			$curl = curl_init();
			curl_setopt ($curl, CURLOPT_URL, $url);
			curl_setopt ($curl, CURLOPT_HEADER,0);
			curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt ($curl, CURLOPT_TIMEOUT,5);
			$get_content = curl_exec($curl);
			curl_close ($curl);
		}else{
			Vendor ( 'Express.Snoopy');
			$snoopy = new snoopy();
			$snoopy->referer = 'http://www.google.com/';//伪装来源
			$snoopy->fetch($url);
			$get_content = $snoopy->results;
		}
		if($backType==1){//返回类型为1时,直接返回蚊子内容
			$result=$get_content . '' . $powered;
		}else{
			$result='<iframe src="'.$get_content.'" width="580" height="254" scrolling="no" class="wuliu_detail">' . $powered."</iframe>";

		}

		return $result;
	}



	/**获取顺丰快递的返回数据
	 *  @param string $com 查询的快递公司代码
	 *  @param string $nu 快递单号
	 */
	public function getExpressStatus($com,$nu){
		Vendor ( 'Express.Snoopy');
		$typeCom = $com;//快递公司
		$typeNu = $nu;  //快递单号
//echo $typeCom.'<br/>' ;
//echo $typeNu ;
		$AppKey='eedcd565bf5b507e';//请将XXXXXX替换成您在http://kuaidi100.com/app/reg.html申请到的KEY
		$url ='http://api.kuaidi100.com/api?id='.$AppKey.'&com='.$typeCom.'&nu='.$typeNu.'&show=2&muti=1&order=asc';
//请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
		echo $url;
		$powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ';

//优先使用curl模式发送数据
		if (function_exists('curl_init') == 1){
			$curl = curl_init();
			curl_setopt ($curl, CURLOPT_URL, $url);
			curl_setopt ($curl, CURLOPT_HEADER,0);
			curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt ($curl, CURLOPT_TIMEOUT,5);
			$get_content = curl_exec($curl);
			curl_close ($curl);
		}else{
			Vendor ( 'Express.Snoopy');
			$snoopy = new snoopy();
			$snoopy->referer = 'http://www.google.com/';//伪装来源
			$snoopy->fetch($url);
			$get_content = $snoopy->results;
		}
		print_r($get_content . '<br/>' . $powered);

		return $get_content;
	}

	/*根据订单up_id返回快递单号 只返回单号
	 * @param int $up_id 订单的处理单号(up_id)
	 */
	public function getExpressNumberByUpid($up_id){
		$result=M("user_prepaid_express")->where("up_id=".$up_id)->find();
		if($result){
			$re=$result['express_number'];
		}else{
			$re = "Express_number Error!";
		}
		return $re;
	}




}
?>