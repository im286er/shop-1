<?php
/**
 * API Action通用类
 * 
 * 所有的API Action都将继承此类
 *
 * @author miaomin 
 * Oct 11, 2013 6:49:24 PM
 * 
 * $Id: CommonAction.class.php 952 2013-11-14 03:15:35Z miaomiao $
 */
class CommonAction extends Action {
	
	// 公钥
	public $_publicKey = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';


	// 代码示意
	protected $RES_CODE_TYPE = array (
				
			// 数据库连接失败
			'DB_CONNECTED_ERR' => 100,
			// 接口参数错误
			'PARAMETERS_ERR' => 101,
			// 当前选项不存在
			'ITEM_NOT_EXIST' => 102,
			// 签名错误
			'SIGN_ERR' => 110,
			// 需要更多接口参数
			'NEED_MORE_PARAMETERS' => 111,
			// 方法调用错误
			'METHOD_ERR' => 120,
			// 用户信息无法正常解析
			'USER_VISA_PARSE_ERR' => 121,
			// 用户信息错误
			'USER_INFO_ERR' => 122,
			// 命令参数错误
			'PARAMETER_METHOD_ERR' => 123,
			// 输出格式参数错误
			'PARAMETER_FORMAT_ERR' => 124,
			
			// 正常
			'OK' => 200,
			
			// 账户
			'USER_ACCOUNT_UNUSUAL' => 310,
			'USERNAME_NOT_EXIST' => 311,
			'PASSWORD_UNMATCH' => 312,
			'EMAIL_HAVE_REGISTERED' => 321,
			'USERNAME_HAVE_REGISTERED' => 322,
			'EMAIL_FORMAT_ERR' => 323,
			'USERNAME_UNMATCH' => 324,
			'PASSWORD_UNMATCH' => 325,
			'TWICE_PASSWORD_UNMATCH' => 326,
			
			// 没有检测到有效的文件
			'FILE_NOT_EXIST' => 400,
			// 文件上传失败
			'UPLOAD_FILE_FAILED' => 401,
			// 文件类型不被允许
			'UPLOAD_FILE_NOT_ALLOWED' => 402,
			// 压缩文件中没有包含有效的文件类型
			'UPLOAD_FILE_NOT_INCLUDE_ALLOWED_EXTENSION' => 403,
			// 压缩文件中没有包含有效的文件类型
			'UPLOAD_FILE_TOO_LARGE' => 404,
			// 写入块文件表失败
			'WRITE_BLOCK_TABLE_ERR' => 405,
			// 合并块文件失败
			'MERGE_BLOCK_FILE_ERR' => 406,
			// 写入云文件表失败
			'WRITE_YUN_TABLE_ERR' => 407,
			// 写入用户文件表失败
			'WRITE_UF_TABLE_ERR' => 408,
			// 清理块文件失败
			'CLEAR_BLOCK_FILE_ERR' => 409,
			// 写入打印模型表失败
			'WRITE_PM_TABLE_ERR' => 410,
			// 没有检测到有效的用户文件
			'USERFILE_NOT_EXIST' => 411,
			'PRINTERMODEL_NOT_EXIST' => 412,
			'YUNFILE_NOT_EXIST' => 413,
			'PRINTMATERIAL_NOT_EXIST' => 414,
			'MATERIAL_NOT_MATCH' => 415,
			'USERFOLDER_NOT_EXIST' => 416,
			
			// 添加购物车失败
			'WRITE_CART_TABLE_ERR' => 501,

			//获取用户地址
			'USERADDRESS_NOT_EXIST'=>601,
			'USERADDRESS_FORMAT_ERROR'=>602, //地址格式错误

			//验证码
            'VERIFY_ERR'=>701, //手机验证码错误
            'MOB_EXIST'=>702,//手机号码已经存在
            'MOB_ERR'=>703,//手机号码格式有误
            'VERIFY_ERR_LONGTIME'=>704,//网络超时
            'VERIFY_ERR_NUM'=>705,//你今天的发送次数已经达到最大限制
            'MOB_NOT_EXIST'=>706,//手机号码未注册

            //密码修改
            'RESET_PASS_ERR'=>801,//密码修改失败

            'USERORDER_NOT_EXIST'=>901,

            'ADDCART_FAIL'=>902,
            'APPCONF_ERROR'=>903,
            'DIYCID_FAIL'=>904,
            'DETAIL_ERROR'=>905,
            'USERDIY_NOT_EXIST'=>906,
            'APPWX_LOGIN_ERR'=>907,
            'TEL_ERR'=>908,
            'USERDIY_DEL_FAIL'=>909,

			//产品通过接口直接生成订单
			'PRODUCT_PREPAID_ERR'=>911,
     	    'PRODUCT_ID_DOES_NOT_EXIST'=>921,

			'ORDERID_NOT_EXIEXT'=>9211,

			'JSON_DATA_ERROR'=>9212,
		'PRODUCT_PRICE_ERR'=>9213,
		'AGENT_CODE_ERR'=>9214



	);
	
	// 错误提示
	protected $RES_MESSAGE = array (
			
			'100' => 'DB connected error',
			'101' => 'Parameters error',
			'102' => 'Current object not exist',
			'110' => 'Signature error',
			'111' => 'Need more parameters',
			'120' => 'Mehtod not found.',
			'121' => 'User visa parse failed',
			'122' => 'Userinfo error',
			'123' => 'Parameters method error',
			'124' => 'Parameters format error',
			
			'200' => 'OK',
			
			'310' => 'User Account unusual',
			'311' => 'Username not exist or password error',
			'3115'=> 'Username not exist',
			'3116'=> 'password error',
			'312' => 'User password error',
			'321' => 'User email have registered',
			'322' => 'Username have registered',
			'323' => 'User email format error',
			'324' => 'Username unmatch',
			'325' => 'User password unmatch',
			'326' => 'User twice password unmatch',
            '330' => '手机号已经注册',
			
			'400' => 'File not exist',
			'401' => 'Upload file failed',
			'402' => 'Upload file extension not allowed',
			'403' => 'Upload zipfile not include allowed extension',
			'404' => 'Upload file too large',
			'405' => 'Write block table failed',
			'406' => 'Merge block file failed',
			'407' => 'Write yun table failed',
			'408' => 'Write userfile table failed',
			'409' => 'Clear block files error',
			'410' => 'Write printermodel table failed',
			'411' => 'Userfile not exist',
			'412' => 'PrintModel not exist',
			'413' => 'Yunfile not exist',
			'414' => 'PrintMaterial not exist',
			'415' => 'PrintMaterial not match',
			'416' => 'Userfolder not exist',
			
			'501' => 'Write cart table failed', 
			
			'601' => 'USERADDRESS_NOT_EXIST',
			'602' => 'USERADDRESS_FORMAT_ERROR',

            '701' => '手机验证码错误',
            '702' => '手机号码存在',
            '703' => '手机号码格式有误',
            '704' => '网络超时',
            '705' => '发送数超过当日最低发送数量',
            '706' => '手机号码还未注册',
            '801' => '密码修改失败',

            '901' =>'用户订单不存在',
            '902' => '加入购物车失败',
            '903' => 'APP微信参数配置有误',
            '904'=>'DIY的CID错误',
            '905'=>'获取详情错误',
            '906'=>'用户DIY方案不存在',
            '907'=>'微信登录未成功',
            '908'=>'客服电话未获取',
            '909'=>'DIY方案删除有误',
			'911'=>'Product ID have been generated orders, please do not repeat!',
			'921'=>'pid does not exist',
			'9211'=>'orderid does not exist!',
			'9212'=>'Json Datas Error!',
			'9213'=>'Product_Price_Error!',
			'9214'=>'Agentid Error!'


	);
	
	// 请求来源类型
	protected $REQUEST_FROM_TYPE = array (
			'PAGE' => 1,
			'CLIENT' => 2,
			'RP360' => 3,
            'APP'   =>4,
			'UNKNOWN' => 9
	);
	
	/**
	 * API Action通用类
	 */
	function __construct() {
		//load ( "@.DBF" );
		if(SAVETOOSS_OPEN){ //动态修改文件或图片公共路径,默认为本地,如果SAVETOOSS_OPEN=true时,读取阿里OSS路径
			C('PUBPATH',OSSPATH);
		}
		parent::__construct ();
	}
	
	/**
	 * 检查API Key
	 *
	 * @param string $api_key        	
	 */
	function checkApiKey($api_key) {
		if ($api_key == '8888') {
			echo 'Yes! Apikey!';
		} else {
			echo 'No! Apikey!';
		}
	}
	
	/**
	 * 请求参数解码
	 *
	 * @param array $args        	
	 * @return array
	 */
	protected function decodeArguments($args) {
		$res = array ();
		foreach ( $args as $key => $val ) {
			$val_arr = explode ( '#', $val );
			$res [$val_arr [0]] = $val_arr [1];
		}
		return $res;
	}
	
	/**
	 * 请求参数加码
	 *
	 * @param array $args        	
	 * @return array
	 */
	protected function encodeArguments($args) {
		$res = array ();
		foreach ( $args as $key => $val ) {
			if (! is_array ( $val )) {
				$res [] = $key . '#' . $val;
			}
		}
		return $res;
	}
	
	/**
	 * 记录日志
	 *
	 * @param array $data        	
	 * @return int
	 */
	protected function addLog($data) {
		$log = LogFactoryModel::init ( 'client' );
		$res = $log->addLog ( $data );
		return $res;
	}
	
	/**
	 * 分析请求中的用户信息
	 *
	 * @param string $base64Str        	
	 * @return Ambigous <mixed, string, boolean, NULL, unknown>|boolean
	 */
	protected function parseRequestUserHandle($base64Str) {
		$base64Str=trim($base64Str);
        $base64Str_new=pub_encode_pass($base64Str,"O4rDRqwshSBojonvTt4mar21Yv1Ehmqm","decode"); //解密
        //$base64Str_new=$base64Str;
		if ($base64Str_new) {
			$visa = base64_decode ( $base64Str_new );
			if ($visa) {
				$logindata = explode ( ' ', $visa );
				if ((is_array ( $logindata )) && (count ( $logindata ) == 2)) {
					
					if ($this->validRequestUserMobnoVisa ( $logindata )) {
                        return $logindata;
					}
					
				}
			}
		}
		return false;
	}



	/**
	 * 分析请求中的用户信息
	 *
	 * @param string $base64Str
	 * @return Ambigous <mixed, string, boolean, NULL, unknown>|boolean
	 */
	protected function parseRequestUserHandle_nopubencode($base64Str) {
		$base64Str=trim($base64Str);
		//$base64Str_new=pub_encode_pass($base64Str,"O4rDRqwshSBojonvTt4mar21Yv1Ehmqm","decode"); //解密
		$base64Str_new=$base64Str;
		if ($base64Str_new) {
			$visa = base64_decode ( $base64Str_new );
			if ($visa) {
				$logindata = explode ( ' ', $visa );
				if ((is_array ( $logindata )) && (count ( $logindata ) == 2)) {

					if ($this->validRequestUserMobnoVisa ( $logindata )) {
						return $logindata;
					}

				}
			}
		}
		return false;
	}
	
	/**
	 * 校验请求中的用户信息的格式是否正确
	 *
	 * @param array $visadata        	
	 * @return bool
	 */
	protected function validRequestUserVisa($visadata) {
		//load ( '@.PVC2' );
		$PVC = new PVC2 ();
		$PVC->setStrictMode ( true )->setModeArray ()->SourceArray = $visadata;
		$PVC->isString ()->isEMail ()->validateMust ()->Error ( $this->RES_CODE_TYPE ['EMAIL_FORMAT_ERR'] )->add ( '0' );
		$PVC->isString ()->isMD5 ()->validateMust ()->Error ( $this->RES_CODE_TYPE ['PASSWORD_UNMATCH'] )->add ( '1' );
		
		return $PVC->verifyAll () ? true : false;
	}
    /**
     * 校验请求中的用户信息的格式是否正确（手机号码和md5密码）
     *
     * @param array $visadata
     * @return bool
     */
    protected function validRequestUserMobnoVisa($visadata) {
        //load ( '@.PVC2' );
        $PVC = new PVC2 ();
        $PVC->setStrictMode ( true )->setModeArray ()->SourceArray = $visadata;
        $PVC->isString ()->validateMust ()->Error ( $this->RES_CODE_TYPE ['EMAIL_FORMAT_ERR'] )->add ( '0' );
        //$PVC->isString ()->isMD5 ()->validateMust ()->Error ( $this->RES_CODE_TYPE ['PASSWORD_UNMATCH'] )->add ( '1' );
        return $PVC->verifyAll () ? true : false;
    }


}
?>