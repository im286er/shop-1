<?php
/**
 * 用户类
 *
 * @author miaomin 
 * Jun 24, 2013 10:21:23 AM
 */
class UsersModel extends Model {
	protected $tableName = 'users';
	protected $_map = array (
			//'email' => 'u_email',
            'mobno'=>'u_mob_no',
			//'nickname' => 'u_dispname',
			'password' => 'u_pass' 
	);
	protected $fields = array (
			'u_id',
			'u_pass',
			'u_salt',
			'u_email',
			'u_avatar',
			'u_dispname',
			'u_realname',
			'u_type',
			'u_level',
			'u_title',
			'u_createdate',
			'u_lastlogin',
			'u_status',
			'u_permission',
			'u_lastip',
			'u_exp',
			'u_exp_av',
			'u_mob_pre',
			'u_mob_no',
			'u_mail_verify',
			'u_idd_verify',
			'u_del',
			'u_logout',
			'u_from',
			'u_lastlogintime',
			'u_identifier',
			'u_token',
			'u_timeout',
			'_pk' => 'u_id',
			'_autoinc' => TRUE 
	);
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	/**
	 *
	 * @var DBF_Users
	 */
	public $F;
	
	// @formatter:off
	/**
	 * 
	 *使用自动填充可能会覆盖表单提交项目。其目的是为了防止表单非法提交字段。使用Model类的Create方法创建数据对象的时候会自动进行表单数据处理。
	 *和自动验证一样，自动完成机制需要使用Create方法才能生效。并且，也可以在操作方法中动态的更改自动完成的规则。
	 * 
	 */
	protected $_auto = array (
			array (
					'u_title',
					'getUserNormalTitle',
					1,
					'callback'
			),
			array (
					'u_salt',
					'getUserSalt',
					1,
					'callback'
			),
			array (
					'u_createdate',
					'get_now',
					1,
					'function'
			)
	);
	/*
	protected $_auto = array (
			array (
					'u_pass',
					'genUserPass',
					3,	//新增或者更新
					'callback' 
			),
			array (
					'u_createdate',
					'get_now',
					1,	//新增
					'function' 
			)
	);
	*/
	// @formatter:on
	
	// 用户帮助类
	public $helper;
	
	// 用户ID混淆值
	private $_idChaos = 1397723794;
	
	/**
	 * 用户类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->Users;
		$this->trueTableName = $this->F->_Table;
		
		$this->fields = $this->F->getFields ();
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
		
		import ( "App.Helper.UserHelper" );
		$this->helper = new UserHelper ( $this );
	}
	
	/**
	 * 自动登录
	 *
	 * @author miaomin
	 * @param int $uid        	
	 * @param string $logintype        	
	 * @return boolean
	 */
	public function autoLogin($uid, $logintype = 'wx') {
		// 自动登录
		if ($uid) {
			
			$UM = new UsersModel ();
			$userinfo = $UM->getUserByID ( $uid );
			
			// 无法获取用户信息
			if (! $userinfo) {
				return false;
			}
			
			session ( 'f_userid', $UM->u_id );
			session ( 'f_nickname', $UM->u_dispname );
			session ( 'f_logindate', time () );
			session ( 'f_logintype', $logintype );
			
			$UM->u_lastlogin = get_now ();
			$UM->u_lastip = get_client_ip ();
			$UM->save ();
			
			$LULM = new LogUserLoginModel ();
			$LULM->addLog ( session ( 'f_userid' ) );
			
			return true;
		}
	}
	
	/**
	 * 用户名是否已经存在
	 * @param unknown_type $email        	
	 */
	public function isUserEMailExist($email) {
		$res = $this->getByU_EMAIL( $email );
		if (is_array ( $res )) {
			return true;
		} else {
			return false;
		}
	}
    /**
     * 手机号码是否已经存在
     * @param unknown_type $mobno
     */
    public function isUserMobnoExist($mobno) {
        //echo $mobno;
        $res = $this->getByu_mob_no( $mobno );
       // var_dump($this->getLastSql());
        if (is_array ( $res )) {
            return true;
        } else {
            return false;
        }
    }
	
	/**
	 * 根据混淆值计算出真正的ID值
	 *
	 * @param number $chaosId        	
	 * @return number
	 */
	public function decodeUIDChaos($chaosId) {
		return $chaosId - $this->getIdChaos ();
	}
	
	/**
	 * 返回用户ID混淆值
	 *
	 * @return number
	 */
	public function getIdChaos() {
		return $this->_idChaos;
	}
	
	/**
	 * 返回注册用户默认的名称等级
	 *
	 * @return Ambigous <mixed, void, multitype:, string|array, string>
	 */
	public function getUserNormalTitle() {
		return L ( 'normal_user' );
	}
	
	/**
	 * 获取用户信息
	 *
	 * @param unknown_type $UserID        	
	 * @return Ambigous <mixed, boolean, NULL, multitype:, unknown, string>
	 */
	public function getUserByID($UserID) {
		return $this->find ( $UserID );
	}
	
	/**
	 * 返回一个盐值
	 *
	 * @return string
	 */
	public function getUserSalt() {
		return generate_password ( 5 );
	}
	
	/**
	 * 获取用户作品数量
	 *
	 * @param int $uid        	
	 * @return int
	 */
	public function getUserWorksNum($uid) {
		$sql = "SELECT COUNT(tdf_product.p_id) FROM tdf_product LEFT JOIN tdf_product_model ON (tdf_product_model.p_id = tdf_product.p_id) WHERE 1=1 AND tdf_product.p_producttype='1' AND tdf_product.p_slabel!='2' AND (tdf_product.p_creater='" . $uid . "')";
		return get_one ( $this->query ( $sql ) );
	}
	
	/**
	 * 判断管理员对某一个操作是否有权限。 根据当前对应的ua_code，然后再和用户里的u_permission做匹配，以此来决定是否可以继续执行。
	 *
	 * @param string $priv_str
	 *        	操作对应的priv_str 返回的类型
	 * @return true/false
	 */
	public function admin_priv($priv_str) {
		if ($this->u_permission == 'all') {
			return true;
		}
		if (strpos ( '|' . $this->u_permission . '|', '|' . $priv_str . '|' ) === false) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * 获取注册用户列表
	 *
	 *
	 * @param array $req        	
	 * @return array
	 */
	public function getUserList($req) {
		import ( 'ORG.Util.Page' );
		$res = array (
				'arr' => array (),
				'page' 
		);
		$count = $this->where ( '1=1' )->count ();
		$Page = new Page ( $count, 20 );
		$show = $Page->show ();
		$list = $this->where ( '1=1' )->order ( 'u_id' )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		$res ['arr'] = $list;
		$res ['page'] = $show;
		return $res;
	}
    //根据email获取用户信息
	public function getUserByName($UserEmail) {
		$DBF_U = new DBF_Users ();
		$User = $this->where ( $DBF_U->EMail . "='" . $UserEmail . "'" )->select ();
		if (! $User) {
			return $User;
		}
		return $User [0];
	}
    //根据手机号或mail获取用户信息
    public function getUserByMobnoEmail($mobno) {
       if(reg_mail($mobno)){
           $result=$this->getUserByEMail($mobno);
       }else{
           $result=$this->getUserByMobno($mobno);
       }
        return $result;
    }

    //根据手机号获取用户信息
    public function getUserByMobno($mobno) {
        $DBF_U = new DBF_Users ();
        $User = $this->where ( $DBF_U->MobNo . "='" . $mobno . "'" )->select ();
        if (! $User) {
            return $User;
        }
        return $User [0];
    }



	public function getUsersCombo($where = '1=1', $location = 0, $IsAddRoot = true) {
		$re = '';
		$num = 0;
		
		$num = $this->where ( $where )->count ();
		if ($num) {
			$infobit_ = $this->where ( $where )->select ();
			$re = $IsAddRoot ? "<option value='0'>" . '请选择' . "</option>" : '';
			foreach ( $infobit_ as $key => $val ) {
				if ($val ['u_id'] == $location) {
					$re .= "<option value='" . $val ['u_id'] . "' selected>" . $val ['u_realname'] . " (" . $val ['u_dispname'] . ")</option>";
				} else {
					$re .= "<option value='" . $val ['u_id'] . "'>" . $val ['u_realname'] . " (" . $val ['u_dispname'] . ")</option>";
				}
			}
		} else {
			$re = $IsAddRoot ? "<option value='0'>" . L ( 'root_cate' ) . "</option>" : '';
		}
		return $re;
	}
	
	/**
	 * 获取用户详情对象
	 */
	public function getUserProfile() {
		$UP = D ( 'UserProfile' );
		$res = $UP->find ( $this->u_id );
		// debug 老旧帐号可能会出现无法找到UserProfile数据的问题
		// miaomin edited@2014.4.18
		if (! $res) {
			// insert
			$UP->u_id = $this->u_id;
			$addRes = $UP->add ();
			
			if ($addRes) {
				$UP->find ( $this->u_id );
				return $UP;
			} else {
				return false;
			}
		} else {
			return $UP;
		}
	}
	
	/**
	 * 获取用户积分、账户余额信息 zhangzhibin
	 */
	public function getUserAcc() {
		$UA = D ( 'UserAccount' );
		$UA->find ( $this->u_id );
		return $UA;
	}
	public function getUserByUid($Uid) { // 根据 u_id 查询 0621 by zhangzhibin
		$DBF_U = new DBF_Users ();
		$User = $this->where ( "u_id=" . $Uid . "" )->select ();
		if (! $User) {
			return $User;
		}
		return $User [0];
	}
	public function getUserByEMail($EMail) {
		$UserInfo = $this->where ( 'u_email' . "='" . $EMail . "'" )->select ();
		
		if ($UserInfo === false) {
			return false;
		}
		if ($UserInfo === null) {
			return null;
		}
		if (count ( $UserInfo ) == 0) {
			return null;
		}
		return $UserInfo [0];
	}
	public function getUsersByIDList($IDList) {
		load ( '@.WhereBuilder' );
		$WB = new WhereBuilder ();
		$Where = $WB->addIn ( $this->F->ID, $IDList )->getWhere ();
		$Users = $this->where ( $Where )->select ();
		$Users = array_column ( $Users, null, $this->F->ID );
		return $Users;
	}
	
	/*
	 *
	 */
	public function getUsersProfByIDList($IDList) {
		$WhereIn = implode ( ',', $IDList );
		$sql = "select TU.u_id,TU.u_avatar,TUP.u_firstname,TUP.u_position from tdf_users as TU ";
		$sql .= "Left Join tdf_user_profile as TUP On TUP.u_id=TU.u_id ";
		$sql .= "where TU.u_id in (" . $WhereIn . ") ";
		$Users = M ()->query ( $sql );
		return $Users;
	}
	public function getUserByNickName($NickName) {
		/*
		 * load ( '@.WhereBuilder' ); $WB = new WhereBuilder (); $Where =
		 * $WB->addIn ( $this->F->DispName, $NickName )->getWhere ();
		 */
		$condition = array (
				$this->F->DispName => $NickName 
		);
		return $this->where ( $condition )->find ();
	}
	public function updateUserLastLogin($UserID) {
		$UM = new UsersModel ();
		$UM->{$UM->F->ID} = $UserID;
		$UM->{$UM->F->LastLogin} = get_now ();
		$UM->{$UM->F->LastLoginTime} = time ();
		$UM->{$UM->F->LastIP} = $_SERVER ["REMOTE_ADDR"];
		return $UM->save ();
	}

    public function updateUserPass($mobno,$pass) {
        $this->{$this->F->MobNo} = $mobno;
        //var_dump($this->F->Salt);
       // exit;
        $this->{$this->F->Pass} = md5($pass.$this->{$this->F->Salt});
        $this->{$this->F->LastLoginTime} = time ();
        return $this->save ();
    }

	public function getUserInfo($email, $pass) {
		$userinfo = $this->getUserByEMail ( $email );
		if (! $userinfo) {
			return $userinfo;
		}
		$upassword = md5 ( $pass.$userinfo ['u_salt'] );
		if ($upassword === $userinfo ['u_pass']) {
			$result = $this->where ( "u_id = '" . $userinfo ['u_id'] . "'" )->field ( 'u_id,u_email,u_avatar,u_dispname,u_createdate,u_lastlogin,u_lastip,u_type,u_status' )->select ();
			$temail = urlencode ( $result [0] ['u_email'] );
			$temail = pub_encode_pass ( $temail, "10000", "encode" );
			$result [0] ['verifymailurl'] = WEBROOT_URL . "/user.php/register/getemailverify/email/" . $temail;
			return $result;
		} else {
			return false;
		}
	}

    public function getUserInfoByMobno($mobno, $pass) {
        if(reg_mail($mobno)){
            $userinfo = $this->getUserByEMail( $mobno );
        }else{
            $userinfo = $this->getUserByMobno ( $mobno );
        }
        if (! $userinfo) {
            return $userinfo;
        }
        $upassword = md5 ( $pass . $userinfo ['u_salt'] );
        if ($upassword === $userinfo ['u_pass']) {
            $result = $this->where ( "u_id = '" . $userinfo ['u_id'] . "'" )->field ( 'u_id,u_avatar,u_dispname,u_status,u_salt,u_mob_no,u_type' )->select ();
            //$temail = urlencode ( $result [0] ['u_email'] );
            //$temail = pub_encode_pass ( $temail, "10000", "encode" );
            //$result [0] ['verifymailurl'] = WEBROOT_URL . "/user.php/register/getemailverify/email/" . $temail;
            return $result;
        } else {

            return false;
        }
    }

	/**
	 * 获取用户
	 */
	public function getUserProByID($uid) {
		$UP = D ( 'UserProfile' );
		$res = $UP->find ( $uid );
		// debug 老旧帐号可能会出现无法找到UserProfile数据的问题
		// miaomin edited@2014.4.18
		if (! $res) {
			// insert
			$UP->u_id = $uid;
			$addRes = $UP->add ();
			
			if ($addRes) {
				$UP->find ( $uid );
				return $UP;
			} else {
				return false;
			}
		} else {
			return $UP;
		}
	}
	
	/**
	 * 获取设计师或者极客数量
	 */
	public function getTotalDesignerNum() {
		$condition = array (
				$this->F->Group => array (
						'in',
						array (
								'1',
								'3' 
						) 
				) 
		);
		
		return $this->where ( $condition )->count ();
	}

    /*
     * 获取用户显示名称
     */
    public function getShowUserName($userArr){
        if($userArr['u_dispname']){
            $userArr['showusername']=$userArr['u_dispname'];
        }elseif($userArr['u_email']) {
            $userArr['showusername'] = $userArr['u_email'];
        }else{
            $userArr['showusername'] =substr( $userArr['u_mob_no'],0,3)."****".substr( $userArr['u_mob_no'],-4);
        }
        return $userArr;
    }

    /*
     * 根据传入参数获得用户信息(参数可能是mail，可能是手机号)
     * 返回数组，其中arr['type']为account的类型：0为手机，1为mail
     */
    public function getUserInfoByAccount($account){
        if($this->is_valid_email($account,false)){
            echo "mail";
            var_dump($account);
        }elseif(is_numeric($account)){
            var_dump($account);
        }else{
            echo "格式错误";
        }
    }


//是否有管理员生成的指派订单
    public function haveNewOrder($uid){
        $condition="up_source=1 and up_uid=".$uid." ";
        $prepaidInfo=M('user_prepaid')->order('ctime')->limit(1)->where($condition)->find();
        if($prepaidInfo){
            $result=pub_encode_pass( $prepaidInfo['up_orderid'], $_SESSION ['f_userid'], "encode" );
        }else{
            $result=0;
        }
        return $result;
    }

	/**
	 * @param $agentid
	 *根据agentid获取用户信息   (agentid为salt+u_id)
	 */
	public function getUserInfoByAgentId($agentid){
		$u_id  =  substr($agentid,5,strlen($agentid));
		$userInfo=$this->getUserByUid($u_id);
		if($u_id==$userInfo['u_id']){
			$result=$userInfo;
		}else{
			$result=0;
		}
		return $result;
	}
	/**
	 * @param $agentid
	 *根据agentid获取用户信息   (agentid为salt+u_id)
	 */
	public function getThirdInfo($agentid){
		$u_id   =  substr($agentid,5,strlen($agentid));
		$salt   =  substr($agentid,0,5);
		$result = $this->where("u_id=".$u_id." and u_salt='".$salt."' and u_type=1")->find();
		if($result){
			$res=$result;
		}else{
			$res=0;
		}
		return $res;
	}




}
?>