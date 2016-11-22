<?php
/**
 * 微信sdk操作类
 *
 * @author miaomin 
 * Jan 29, 2015 7:25:12 PM
 *
 * $Id$
 */
class Wxsdk {
	
	// 公众平台应用还是开放平台应用
	private $_platform;
	
	// APPID
	private $_appid;
	
	// SECRET
	private $_secret;
	
	/**
	 * 微信sdk操作类
	 */
	public function __construct($platform = 'mp') {
		
		// 确定平台入口
		$this->_platform = $platform;
		
		if ($this->_platform == 'mp'){
			
			// 公众平台应用APPID和SECRET
			//$this->_appid = 'wxf71f9222d3bc3c2e';//3dcity
			$this->_appid = 'wx391ada2be4c02b13';
			
			//$this->_secret = '7338ad0158221be35c8572c562a56899';//3dcity
			$this->_secret = '878ec244159e8a48a4d744668047769e';
		}elseif ($this->_platform == 'open'){
			
			// 开放平台应用APPID和SECRET
			//$this->_appid = 'wx05261590a5873320';//3dcity
			$this->_appid = 'wx1cb3615051b4b08b';

			//$this->_secret = 'e300d95d02f6fd6d871d24776da6c4cb';//3dcity
			$this->_secret = 'a92542dd6a8f073ba50af892e96a0ce3';
		}else{
			
			// 出错
			throw new Exception ( '微信初始化操作失败' );
		}
		
		// 加载Ncurl基本库
		import ( 'Common.Ncurl', APP_PATH, '.php' );
	}
	
	/**
	 * 微信
	 *
	 * 根据access_token和openid拉取用户信息
	 *
	 * 返回用户信息:
	 *
	 * openid
	 * nickname
	 * sex
	 * province
	 * city
	 * country
	 * headimgurl
	 * unionid
	 *
	 *
	 * @author miaomin
	 * @param string $access_token        	
	 * @param string $openid        	
	 * @param string $lang        	
	 * @return array
	 */
	public function wx_getuserinfo($access_token, $openid, $lang = 'zh_CN') {
		$getUserBaseInfoURL = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=' . $lang;
		$wxRes = Ncurl::curlWX ( $getUserBaseInfoURL, '', false, false );
		
		return $wxRes;
	}
	
	/**
	 * 微信
	 *
	 * 通过code换取网页授权access_token
	 *
	 * @author miaomin
	 * @param string $code        	
	 * @return array
	 */
	public function wx_getaccesstoken($code) {
		$getAccessTokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->_appid . '&secret=' . $this->_secret . '&code=' . $code . '&grant_type=authorization_code';
		$wxRes = Ncurl::curlWX ( $getAccessTokenURL, '', false, false );
		
		return $wxRes;
	}
	
	/**
	 * 微信
	 *
	 * 解析微信返回结果
	 *
	 * @param
	 *        	$wxres
	 * @return array
	 */
	public function parseWxresBody($wxres) {
		return json_decode ( $wxres ['body'], true );
	}
	
	/**
	 * 微信
	 *
	 * 解析并判断微信返回结果
	 *
	 * @param array $wxres        	
	 * @return array
	 */
	public function handleParseWxresBody($wxres) {
		$wxResArr = $this->parseWxresBody ( $wxres );
		
		if ($wxResArr ['errcode']) {
			throw new Exception ( $wxResArr ['errmsg'] );
		}
		
		return $wxResArr;
	}
	
	/**
	 * 微信
	 *
	 * 生成微信认证页URI(供跳转用)
	 *
	 * @param string $redirect_uri        	
	 * @param string $apitype        	
	 * @param string $state        	
	 * @return string
	 */
	public function genWxAuthorizeUri($redirect_uri, $apitype = 'snsapi_base', $state = '123') {
		$getAuthorizeURL = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $this->_appid . "&redirect_uri=" . urlencode ( $redirect_uri ) . "&response_type=code&scope=" . $apitype . "&state=" . $state . "#wechat_redirect";
		
		return $getAuthorizeURL;
	}
	
	/**
	 * 微信
	 *
	 * 生成微信登录页URI(供网站登录用)
	 *
	 * @param string $redirect_uri        	
	 * @param string $apitype        	
	 * @param string $state        	
	 * @return string
	 */
	public function genWxLoginUri($redirect_uri, $apitype = 'snsapi_login', $state = '123') {
		$getLoginURL = "https://open.weixin.qq.com/connect/qrconnect?appid=" . $this->_appid . "&redirect_uri=" . urlencode ( $redirect_uri ) . "&response_type=code&scope=" . $apitype . "&state=" . $state . "#wechat_redirect";
		
		return $getLoginURL;
	}
}
?>