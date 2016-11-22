<?php
/**
 * 设计之都WebService接口类
 * 
 * 说明:
 * 1. 在调用WebService之前必须要设置WebServiceReceiver.xml中<wsdlsoap:address>的地址(该地址设计之都会提供)
 * 2. 保证WebServiceReceiver.xml文件的路径正确且可以被正常调用
 *
 * @author miaomin 
 * Aug 16, 2013 2:51:32 PM
 */
class SdcpWebService {
	// 专业平台注册号
	private $_sdcpCode = '20122511';
	// 专业平台注册名
	private $_sdcpName = '3DFactory';
	// 设计之都WebServiceReceiver.xml文件存放路径
	private $_wsdlFile = 'D:\\Zend\\WorkSpace\\3DF\\WebServiceReceiver.xml';
	private $_client;
	private $_encoding = 'utf-8';
	// 消息号
	private $_serviceIdArr = array (
			// 活动信息
			'eventinfo' => 'sjzd01',
			// 服务概要
			'serviceintro' => 'sjzd07',
			// 用户注册数
			'usercount' => 'sjzd08' 
	);
	
	/**
	 * 构造
	 */
	public function __construct() {
		// 需要将iXBus的服务描述WSDL文件WebServiceReceiver.xml下载到本地，修改<wsdlsoap:address>为实际服务地址。
		$this->_client = new SoapClient ( $this->_wsdlFile, array (
				'trace' => 1 
		) );
		
		// 设置字符编码
		$this->_client->soap_defencoding = $this->_encoding;
		$this->_client->xml_encoding = $this->_encoding;
	}
	
	/**
	 * 注册用户数接口
	 */
	public function sendRegUserCount() {
		$user_real_num = 0; // 真实注册用户数据
		$user_fake_num = 0; // 返还数据
		$user_gain_num = 350; // 增益值
		
		$Users = new UsersModel ();
		$user_real_num = $Users->count ();
		$user_fake_num = $user_real_num + $user_gain_num;
		// 严格按照上海设计之都企业服务总线接口规范2.4.8构成结果数组(Msgbody)
		$res_arr = array (
				$this->_serviceIdArr ['usercount'],
				$this->_sdcpCode,
				$this->_sdcpName,
				$user_fake_num 
		);
		
		$result = $this->submitWebService ( $this->_serviceIdArr ['usercount'], $res_arr );
		
		return $result;
		// var_dump ( $result );
		
		// var_dump ( $result->receiveMessageReturn->returnCode);
	}
	
	/**
	 * 服务概要接口
	 */
	// TODO
	public function sendServiceIntro() {
		$SM = new SettingModel ();
		$Setting = $SM->getAllSetting ();
		// 严格按照上海设计之都企业服务总线接口规范2.4.7构成结果数组(Msgbody)
		$res_arr = array (
				$this->_serviceIdArr ['serviceintro'],
				$this->_sdcpCode,
				// 账号ID
				$this->_sdcpCode,
				$this->_sdcpName,
				// 服务名称
				$Setting ['servicename'] ['value'],
				// 服务内容
				$Setting ['servicecontent'] ['value'],
				// 服务状态
				$Setting ['servicestatus'] ['value'],
				// 详细信息入口地址
				$Setting ['serviceurl'] ['value'] 
		);
		
		$result = $this->submitWebService ( $this->_serviceIdArr ['serviceintro'], $res_arr );
		
		return $result;
	}
	
	/**
	 * 活动信息接口
	 */
	// TODO
	public function sendEventInfo() {
		$SM = new SettingModel ();
		$Setting = $SM->getAllSetting ();
		// 严格按照上海设计之都企业服务总线接口规范2.4.1构成结果数组(Msgbody)
		$res_arr = array (
				$this->_serviceIdArr ['eventinfo'],
				$this->_sdcpCode,
				$this->_sdcpName,
				// 活动标题
				$Setting ['eventtitle'] ['value'],
				// 活动图片(base64格式)
				base64_encode ( file_get_contents ( $Setting ['eventimg'] ['value'] ) ),
				// 图片名称
				$Setting ['eventimgtitle'] ['value'],
				// 缩略图(base64格式)
				base64_encode ( file_get_contents ( $Setting ['eventthumb'] ['value'] ) ),
				// 缩略图名称
				$Setting ['eventthumbtitle'] ['value'],
				// 页面简介
				$Setting ['eventcolsinfo'] ['value'],
				// 活动介绍
				$Setting ['eventintro'] ['value'],
				// 更新时间(YYYY-MM-DD)
				$Setting ['eventdates'] ['value'],
				// 开始时间(YYYY-MM-DD)
				$Setting ['eventdates'] ['value'],
				// 结束时间(YYYY-MM-DD)
				$Setting ['eventdatee'] ['value'],
				// 区县
				$Setting ['eventdistrict'] ['value'],
				// 地点
				$Setting ['eventlocation'] ['value'] 
		);
		
		$result = $this->submitWebService ( $this->_serviceIdArr ['eventinfo'], $res_arr );
		
		return $result;
	}
	
	/**
	 * 生成返回结果
	 *
	 * @param array $arr        	
	 * @return string $res
	 */
	private function genReturnString($arr) {
		$res = '';
		$delimeters = '@!';
		if (is_array ( $arr ) && (count ( $arr ) > 0)) {
			foreach ( $arr as $key => $val ) {
				$res .= $val . $delimeters;
			}
			if (substr ( $res, - strlen ( $delimeters ) ) === $delimeters) {
				$res = substr ( $res, 0, - strlen ( $delimeters ) );
			}
			return $res;
		} else {
			return $res;
		}
	}
	
	/**
	 * 生成返回时间
	 */
	private function genReturnTime() {
		return date ( 'YmdHis' ) . substr ( microtime (), 2, 3 );
	}
	
	/**
	 * 负责向WebService提交数据
	 *
	 * @param string $serviceId        	
	 * @param array $data        	
	 * @return stdClass $result
	 */
	private function submitWebService($serviceId, $data) {
		try {
			$dwsr = array (
					'msgBody' => $this->genReturnString ( $data ),
					'msgSendTime' => $this->genReturnTime (),
					'msgToken' => '',
					'password' => '',
					'serviceId' => $serviceId,
					'sourceAppCode' => $this->_sdcpCode,
					'tempField' => '',
					'version' => '1.0' 
			);
			// 推荐使用__soapCall调用webservice函数
			$result = $this->_client->__soapCall ( "receiveMessage", array (
					array (
							'in0' => $dwsr 
					) 
			) );
		} catch ( SoapFault $soapFault ) {
			echo $soapFault;
			echo "request:<br/>" . htmlspecialchars ( $this->_client->__getLastRequest () ) . "<br/>";
			echo "response:<br/>" . htmlspecialchars ( $this->_client->__getLastResponse () ) . "<br/>";
		}
		
		return $result;
	}
}
?>