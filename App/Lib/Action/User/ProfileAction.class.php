<?php
/**
 * 用户个人资料页
 *
 * @author miaomin 
 * Mar 7, 2014 2:03:52 PM
 *
 * $Id$
 */
class ProfileAction extends CommonAction {
	
	/**
	 * 用户个人资料页
	 */
	public function __construct() {
		parent::__construct ();
		
		$this->DBF = new DBF ();
		$this->header = "square";
		
	}
	
	/**
	 * 个人主页
	 */
	public function u() {
		try {
			// @formatter:off
			/*
			$domain = $_GET ['_URL_'] [2];
			
			if (! $domain) {
				header("HTTP/1.0 404 Not Found");
				$this->display('Public:404');
				exit;
			}
			
			// 加入个人域名的判断
			$UP = new UserProfileModel ();
			$domainRes = $UP->getByu_domain ( $domain );
			if ($domainRes) {
				$uid = $domainRes ['u_id'];
			}
			
			// 没有结果两种可能
			if (! $uid) {
				$pattern = '/^[uU]{1}+\d+$/';
				$domainRes = preg_match ( $pattern, $domain );
				if ($domainRes) {
					// 如果是系统默认的匹配用户ID值
					$uid = ( int ) substr ( $domain, 1 );
				}
			}
			
			//
			if (! $uid) {
				header("HTTP/1.0 404 Not Found");
				$this->display('Public:404');
				exit;
				//throw new Exception ( '用户信息有误' );
			}
			*/
			// @formatter:on
			
			// 测试用
			$uid = I ( 'get.uid' );
			
			// 渲染页面
			$Users = new UsersModel ();
			$uRes = $Users->find ( $uid );
			$UP = $Users->getUserProfile ();
			$UA = $Users->getUserAcc ();
			if ((! $uRes) || (! $UP->u_id) || (! $UA->u_id)) {
				header ( "HTTP/1.0 404 Not Found" );
				$this->display ( 'Public:404' );
				
				// throw new Exception ( '用户信息有误' );
			}
			$birth_year_combo = get_dropdown_option ( genBirthYear (), $UP->u_bir_y );
			$birth_month_combo = get_dropdown_option ( genBirthMonth (), $UP->u_bir_m );
			$birth_day_combo = get_dropdown_option ( genBirthDay (), $UP->u_bir_d );
			// 处理省市联动
			$province_combo = get_dropdown_option ( genCHNProvince (), $UP->u_province_fid );
			$city_combo = get_dropdown_option ( genCHNCity ( $UP->u_province ), $UP->u_city_no );
			// 我的关注数和我的粉丝数
			$UR = new UserRelationModel ();
			$urRes = $UR->getList ( $uid );
			if ($urRes) {
				$urListArr = unserialize ( $urRes [$UR->F->CountList] );
			} else {
				$urListArr = $UR->getListCountStorage ()->getListCount ();
			}
			$this->assign ( 'urList', $urListArr );
			// 我的工作经历和教育经历
			$UED = new UserEducationModel ();
			$UWE = new UserWorkModel ();
			$workexpList = $UWE->getUserWork ( $uid, 0 );
			$eduexpList = $UED->getUserEdu ( $uid, 0 );
			$this->assign ( 'DBF_UWE', $UWE->F );
			$this->assign ( 'DBF_UED', $UED->F );
			$this->assign ( 'workexpList', $workexpList );
			$this->assign ( 'eduexpList', $eduexpList );
			// 同当前登录用户的好友关系和关注关系
			// 判断登录
			if ($this->_isLogin ()) {
				$sqlRes [0] ['u_id'] = $uid;
				$UR = new UserRelationModel ();
				$sqlRes = $UR->markUserRelation ( $sqlRes, $this->_loginUserObj->{$this->_loginUserObj->F->ID} );
				$this->assign ( 'loginRelation', $sqlRes );
			}
			// 我发布的商品
			// @load ( '@.Paging' );
			@load ( '@.SearchParser' );
			$SP = new SearchParser ();
			$SP->parseUrlInfo ( true );
			$SearchInfo = $SP->SearchInfo;
			$SearchInfo ['page'] = $this->_get ( 'page' );
			$SearchInfo ['count'] = 16;
			$SearchInfo ['creater'] = $uid;
			$PSM = new ProductSearchModel ( $SearchInfo, 'model', true );
			// print_r ( $PSM->getResult ( $SP->SearchInfo ['page'] ) );
			
			// 我发布的作品
			$SP2 = new SearchParser ();
			$SP2->parseUrlInfo ( true );
			$SearchInfo2 = $SP2->SearchInfo;
			$SearchInfo2 ['page'] = $this->_get ( 'page' );
			$SearchInfo2 ['producttype'] = 6;
			$SearchInfo2 ['count'] = 16;
			$SearchInfo2 ['creater'] = $uid;
			$PSM2 = new ProductSearchModel ( $SearchInfo2, 'model', true );
			// print_r ( $PSM2->getResult ( $SP2->SearchInfo ['page'] ) );
			
			// 产品评论
			$nowPage = $this->_get ( 'page' );
			(empty ( $nowPage )) ? $nowPage = 1 : $nowPage = $this->_get ( 'page' );
			$page_limit = 10;
			$start = ($nowPage - 1) * $page_limit;
			$UC = D ( 'UserComment' );
			$ProductComment = $UC->join ( "tdf_users ON tdf_users.u_id = tdf_user_comment.u_id" )->where ( "tdf_user_comment.uc_slabel = 1 and tdf_user_comment.uc_type = '2' and tdf_user_comment.uc_pid = '" . $uid . "'" )->order ( 'tdf_user_comment.uc_id DESC' )->limit ( $start, $page_limit )->select ();
			
			// 判断评论头像是否存在，不存在用默认图片替换 start
			foreach ( $ProductComment as $key => $value ) {
				if ($value ['u_avatar']) {
					$ProductComment [$key] ['u_avatar'] = WEBROOT_PATH . '/upload/avatar/' . $value ['u_avatar'];
				} else {
					$ProductComment [$key] ['u_avatar'] = WEBROOT_PATH . '/static/images/avatar/default.png';
				}
			}
			// 判断评论头像是否存在，不存在用默认图片替换 end
			// 评论分页
			$comment_count = $UC->join ( "tdf_users ON tdf_users.u_id = tdf_user_comment.u_id" )->where ( "tdf_user_comment.uc_slabel = 1 and tdf_user_comment.uc_type = '2' and tdf_user_comment.uc_pid = '" . $uid . "'" )->count ();
			$this->assign ( 'Paging', getPagingInfo2 ( $comment_count, $this->_get ( 'page' ), $page_limit, 4, __SELF__ ) );
			// miaomin added by 2013/7/15 end
			
			// miaomin edited@2014.4.28
			$ubdata = $Users->data ();
			$updata = $UP->data ();
			if ($updata ['u_domain']) {
				$updata ['dis_u_domain'] = $updata ['u_domain'];
			} else {
				$updata ['dis_u_domain'] = "u" . $updata ['u_id'];
			}
			
			
			$NFM=new UserNewProfModel();
			$updata['u_newprof']=$NFM->getProfByNewprof($updata['u_newprof']);
			
			if($updata['u_sex']==1){
				$updata['u_sex_name']="female";
			}else{
				$updata['u_sex_name']="male";
			}
			//var_dump($PSM->getResult ( $SP->SearchInfo ['page'] ));
			//var_dump($updata);
			$this->assign ( 'SearchResult', $PSM->getResult ( $SP->SearchInfo ['page'] ) );
			$this->assign ( 'SearchResult2', $PSM2->getResult ( $SP2->SearchInfo ['page'] ) );
			$this->assign ( 'DBF_P', $this->DBF->Product );
			$this->assign ( 'SearchResultCount', $PSM->TotalCount );
			$this->assign ( 'SearchResult2Count', $PSM2->TotalCount );
			$this->assign ( 'duserBasic', $ubdata );
			$this->assign ( 'duserPro', $updata );
			$this->assign ( 'userProf', explode ( '#', $UP->u_prof ) );
			$this->assign ( 'userAcc', $UA->u_vcoin_av );
			$this->assign ( 'userAcc_rmb', $UA->u_rcoin_av );
			$this->assign ( 'yearCombo', $birth_year_combo );
			$this->assign ( 'monthCombo', $birth_month_combo );
			$this->assign ( 'dayCombo', $birth_day_combo );
			$this->assign ( 'provinceCombo', $province_combo );
			$this->assign ( 'cityCombo', $city_combo );
			$this->assign ( 'ProductComment', $ProductComment );
			$this->assign ( 'commentRandom', time () );
			$this->assign ( 'header', $this->header );
				
			// 分页
			$this->assign ( 'PI', getPagingInfo ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SearchInfo ['count'], 4 ) );
			$Url = $SP->getFormattedUrl ();
			$BaseUrl = C ( 'REDIRECT_PRE_URL' ) . U ( 'profile/u/' . $domain ) . '/1';
			$this->assign ( 'BaseUrl', $BaseUrl );
			$paging = getPagingInfo2_yuan ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SearchInfo ['count'], 4, $BaseUrl );
			$this->assign ( 'Paging', $paging );
			$this->assign ( 'CommentPaging', getPagingInfo2 ( $comment_count, $this->_get ( 'page' ), $page_limit, 4, __SELF__ ) );
			$showtitle = $ubdata ['u_dispname'] . "的个人主页_ignjewelry.com";
			$this->assign ( 'showtitle', $showtitle );
			//$this->assign ( 'header', 'page-square' );
			$this->assign ( 'item_header', 1 );
			// 被访问次数统计
			$UP->u_visitnum += 1;
			$UP->save ();
			
			$this->_renderPage ();
		} catch ( Exception $e ) {
		}
	}
	
	/**
	 * FTP字符串
	 */
	public function fftp() {
		$remote = './upload/productfile/0/f4/12544/55222cfa0e1f8451.zip';
		$dirname = pathinfo ( $remote, PATHINFO_DIRNAME );
		// var_dump ( $dirname );
		
		$res = $this->ck_dirname ( $dirname );
		// var_dump ( $res );
		foreach ( $res as $v ) {
			// var_dump ( $v );
		}
	}
	
	/**
	 * 检测目录名
	 *
	 * @param string $url
	 *        	目录
	 * @return 由 / 分开的返回数组
	 */
	private function ck_dirname($url) {
		$url = str_replace ( '', '/', $url );
		$urls = explode ( '/', $url );
		return $urls;
	}
	
	/**
	 * CURL Python
	 */
	public function py() {
		$host = 'tcp://192.168.20.151:8001';
		$res = $this->_sendSocketMsg ( '192.168.20.151', '8001', '123456|123', 1 );
		var_dump ( $res );
		/*
		 * $fp = stream_socket_client ( $host, $errno, $errstr, 3,
		 * STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT ); if (! $fp) {
		 * echo $errno; } else { fwrite ( $fp, '123|12345' ); while ( ! feof (
		 * $fp ) ) { echo fgets ( $fp ); } echo 'yes!!!'; fclose ( $fp ); }
		 */
	}
	
	/**
	 * socket收发数据
	 *
	 * @host(string) socket服务器IP
	 * @post(int) 端口
	 * @str(string) 要发送的数据
	 * @back 1|0 socket端是否有数据返回
	 * 返回true|false|服务端数据
	 */
	private function _sendSocketMsg($host, $port, $str, $back = 0) {
		$socket = socket_create ( AF_INET, SOCK_STREAM, 0 );
		if ($socket < 0)
			return false;
		$result = @socket_connect ( $socket, $host, $port );
		if ($result == false)
			return false;
		socket_write ( $socket, $str, strlen ( $str ) );
		
		if ($back != 0) {
			$input = socket_read ( $socket, 1024 );
			socket_close ( $socket );
			return $input;
		} else {
			socket_close ( $socket );
			return true;
		}
	}
	
	/**
	 * 教育经历
	 *
	 * @access public
	 * @return null
	 */
	public function edu() {
		$UserEdu = new UserEducationModel ();
		$res = $UserEdu->getUserEdu ( 99 );
		vd ( $res );
	}
	
	/**
	 * 培训经历
	 *
	 * @access public
	 * @return null
	 */
	public function train() {
		$UserTrain = new UserTrainingModel ();
		$res = $UserTrain->getUserTrain ( 99 );
		vd ( $res );
	}
	
	/**
	 * 点赞用户列表
	 *
	 * @access public
	 * @return null
	 */
	public function zan() {
		$UserZan = new UserZanModel ();
		$res = $UserZan->getZanUserList ( 1001 );
		vd ( $res );
	}
	
	/**
	 * 用户点赞列表
	 *
	 * @access public
	 * @return null
	 */
	public function myzan() {
		$UserZan = new UserZanModel ();
		$res = $UserZan->getZanPList ( 1 );
		vd ( $res );
	}
	
	/**
	 * 加奖项
	 *
	 * @access public
	 * @return mixed
	 */
	public function addaward() {
		$tags = array (
				'2013年度编辑选择奖',
				'2013年度大众选择奖' 
		);
		$pid = 22;
		$PAM = new ProductAwardModel ();
		$res = $PAM->addAwardArray ( $pid, $tags );
		vd ( $res );
	}
	
	/**
	 * 点赞
	 *
	 * @access public
	 * @return null
	 */
	public function addzan() {
		$uId = 100;
		$pId = 5;
		
		$UserZan = new UserZanModel ();
		$res = $UserZan->addZan ( $uId, $pId );
		vd ( $res );
	}
	
	/**
	 * 添加一个标签
	 *
	 * @access public
	 * @return null
	 */
	public function addtag() {
		$tag = '影视动画';
		$UserTags = new UserTagsModel ();
		$res = $UserTags->addTag ( $tag );
		vd ( $res );
	}
	
	/**
	 * 添加用户标签
	 */
	public function addusertags() {
		$tags = array (
				'影视动画',
				'虚拟现实',
				'建筑设计' 
		);
		$uid = 99;
		$UserTags = new UserTagsModel ();
		$res = $UserTags->addUserTagsArray ( $uid, $tags, 1 );
		vd ( $res );
	}
	
	/**
	 * 添加多个标签
	 *
	 * @access public
	 * @return null
	 */
	public function addtags() {
		$tags = array (
				'影视动画',
				'原画设计',
				'虚拟现实',
				'建筑设计' 
		);
		$UserTags = new UserTagsModel ();
		$res = $UserTags->addTagsArray ( $tags );
		vd ( $res );
	}
	
	/**
	 * 获取标签
	 *
	 * @access public
	 * @return null
	 */
	public function gettags() {
		$UserTags = new UserTagsModel ();
		$res = $UserTags->getTages ( 10 );
		vd ( $res );
	}
	
	/**
	 * 站内信
	 *
	 * @access public
	 * @return null
	 */
	public function letter() {
		$SI = array (
				'uid' => 25,
				'count' => 5,
				'page' => 1 
		);
		$LUM = new LitterUserModel ();
		$res = $LUM->getInBox ();
		vd ( $res );
	}
}