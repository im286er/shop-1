<?php
class BaseAction extends Action
{
	//公用用户信息，根据需要自己添加
	protected $UID;
	protected $UName;
	protected $UEMail;
	
	protected $UrlHome = '/index.php';
	protected $UrlLogin = '/user.php/login';
	
	protected $SessionKey = 'sessionid';


	public function __construct()
	{

		echo header("Content-Type:text/html; charset=utf-8");
		parent::__construct();
		$this->initInclude();
		$this->initSessionID();
		$this->initUrl();
		$this->initUserInfo();

		//$this->assign('pubtext',L('pubtext'));//赋值公共的文字，包括header和footer
		
	}
	
	//加载公用方法
	private function initInclude()
	{
		load('@.PVC2');
		load('@.WhereBiuilder');
		load('@.DBF');
		load('@.Paging');
	}
	
	//初始化SessionID(Flash上传问题)
	private function initSessionID()
	{
		if (isset($_POST[$this->SessionKey]))
		{ session_id($_POST[$this->SessionKey]); }
		session_start ();
	}
	
	//用来初始化公用URL，最上层变量定义可以忽略
	//子类改写Url后需要重新执行该函数
	private function initUrl()
	{
		$this->UrlHome = U($this->UrlHome);
		$this->UrlLogin = U($this->UrlLogin);
		$Url = array(
				'Home' => $this->UrlHome,
				'Login' => $this->UrlLogin
		);
		$this->assign('Url', $Url);
	}
	
	//初始化用户信息
	//根据需要可以增加其他用户信息
	private function initUserInfo()
	{
		if($this->isLogin())
		{
			$this->UID = session('f_userid');
			$this->UName = session('f_nickname');
			$this->UEMail = session('f_email');
		}
		$UserInfo = array(
				'ID' => $this->UID,
				'Name' => $this->UName,
				'EMail' => $this->UEMail
		);
		$this->assign('UserInfo', $UserInfo);
	}

	/**
	 * 判断登录
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function isLogin() {
		if (isset ( $_SESSION ['f_userid'] ) && isset ( $_SESSION ['f_logindate'] )) {
			return true;
		}
		return false;
	}
	
	/**
	 * 渲染页面
	 * @access protected
	 * @return null
	 */
	protected function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
	{
		if($this->isLogin())
		{
			$this->assign('isLogin', 1);
			$this->assign('UserInfo', $this->_session());
		}
		parent::display($templateFile, $charset, $contentType, $content, $prefix);
	}

	protected function displayError($Error, $Key = 'ErrInfo')
	{
		$this->assign($Key, $Error);
		$this->display();
	}
	
	protected function jumpToLogin() {
		if($this->_get('reqtype') == 'ajax')
		{
			$result['isSuccess'] = false;
			$result['Reason'] = '0001'; // 需要登录
			$result['fromUrl'] = $_SERVER['HTTP_REFERER'];
			echo json_encode($result);
			exit();
		}
		else
		{
			// TODO // 跳转路径以后都可以整理入配置文件 //!
			redirect(WEBROOT_PATH . '/user.php/login/?from_url=' . __SELF__);
		}
	}
}
?>