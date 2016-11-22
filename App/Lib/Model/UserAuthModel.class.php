<?php
/**
 * 帮助文档表
 *
 */
class UserAuthModel extends Model {
	protected $tableName = 'user_auth';
	protected $fields = array (
			'id',
			'u_id',
			'AuthType',
			'content',
			'NickName',
		'Headimgurl',
			'OpenId',
			'Access_Token',
			'CreateTime',
			'DelSign',
			'_pk' => 'id',
			'_autoinc' => TRUE 
	);
	
	/**
	 * 根据openid获取auth信息
	 *
	 * <----高能注意: 这里的OPENID可能是UNIONID---->
	 * 
	 * @author miaomin
	 * @param string $openid        	
	 * @param int $type
	 *        	1:QQ
	 *        	2:微信(默认)
	 * @return mixed
	 */
	public function getOpenId($openid, $type = 2) {
		$condition = array (
				'OpenId' => $openid,
				'AuthType' => $type 
		);
		$authRes = $this->where ( $condition )->find ();
		return $authRes;
	}
	
	/**
	 * 微信绑定用户并登陆
	 *
	 * <----高能注意: 这里的OPENID可能是UNIONID---->
	 * 
	 * @author miaomin
	 * @param string $openid        	
	 * @param array $userinfo        	
	 * @return mixed
	 */
	public function bindByWXOpenId($openid, $userinfo) {
		// 新建用户
		$result = $this->CreateWXUser ( $openid, $userinfo );
		return $result;
}
	
	/**
	 * 微信绑定创建用户
	 *
	 * <----高能注意: 这里的OPENID可能是UNIONID---->
	 * 
	 * @author miaomin
	 * @param string $openid        	
	 * @param string $userinfo        	
	 * @return mixed
	 */
	public function CreateWXUser($openid, $userinfo) {
		$reArr = array ();
		
		//
		$tempUsername = 'wx_' . substr ( md5 ( $openid . microtime () ), 1, 16 );
		if ($userinfo ['bindtype'] == 'userinfo') {
			$tempDispname = $userinfo ['nickname'];
		} else {
			$tempDispname = $tempUsername;

		}
		
		// 用户基本表
		$Users = new UsersModel ();
		$Users->startTrans ();
		$Users->create ();
		$Users->u_salt = $Users->getUserSalt ();
		$Users->u_pass = md5 ( '3dcity2014' . $Users->u_salt );
		$Users->u_email = $tempUsername . "@3dcity.com";
		$Users->u_from = $userinfo ['from'];
        $Users->u_mail_verify = 1;
		$Users->u_dispname = $tempDispname;
		$Users->u_title = '普通用户';
		$Users->u_createdate = get_now ();
		$uid = $Users->add ();
		$reArr [] = $uid;
		
		// 用户Profile表
		$UP = D ( 'UserProfile' );
		$UP->u_id = $uid;
		$reArr [] = $UP->add ();
		
		// 用户Account表
		$UA = D ( 'UserAccount' );
		$UA->u_id = $uid;
		$reArr [] = $UA->add ();
		
		// 用户Auth表
		$UAM = D ( 'UserAuth' );
		$UAM->u_id = $uid;
		$UAM->AuthType = $userinfo ['authtype'];
		$UAM->NickName = $tempDispname;
		$UAM->Headimgurl = $userinfo ['headimgurl'];
		$UAM->OpenId = $openid;
		$reArr [] = $UAM->add ();
		
		// 验证事务
		if (in_array ( false, $reArr )) {
			$Users->rollback ();
			$result = false;
			throw new Exception ( 'MySQL I/0 Error.' );
		} else {
			$result = $uid;
			$Users->commit ();
		}
		
		return $result;
	}
	public function getByOpenId($openid) { // 根据openid获得查询
		$UA = M ( 'user_auth' );
		$uainfo = $UA->where ( "OpenId='" . $openid . "'" )->find ();
		return $uainfo;
	}
	public function getByAccess_Token($token) { // 根据token获得查询
		$UA = M ( 'user_auth' );
		$uainfo = $UA->where ( "Access_Token='" . $token . "'" )->find ();
		return $uainfo;
	}
	public function getByUid($uid) { // 根据uid获得查询
		$UA = M ( 'user_auth' );
		$uainfo = $UA->where ( "u_id=" . $uid . "" )->find ();
		return $uainfo;
	}
	public function bindbyOpenId($token, $qquser) { // 通过openid检测用户是否绑定,
		$UA = M ( 'user_auth' );
		$uainfo = $this->getByOpenId ( $token ['openid'] );
		if ($uainfo) { // 如果记录存在
			if ($token ['access_token'] == $uainfo ['Access_Token']) {
				$result = $uainfo ['u_id'];
			}
		} else {
			$result = $this->CreateUser ( $token, $qquser ); // 新建用户
		}
		return $result;
	}
	public function CreateUser($token, $qquser) { // 创建用户：用户表、auth绑定表
		$reArr = array (); // 保存数据操作的结果集
		$Users = new UsersModel ();
		$Users->startTrans ();
		$Users->create ();
		$Users->u_salt = $Users->getUserSalt ();
		$Users->u_pass = md5 ( '3dcity2014' . $Users->u_salt );
		$Users->u_email = $token ['openid'] . "@3dcity.com";
		$Users->u_from = 5; // 注册来源：5为QQ登录
		$Users->u_mail_verify = 1; // 用户mail验证1为验证通过
		$Users->u_dispname = $qquser ['nickname'];
		$Users->u_title = '普通用户';
		$Users->u_createdate = get_now ();
		$uid = $Users->add ();
		$reArr [] = $uid;
		$UP = D ( 'UserProfile' );
		$UP->u_id = $uid;
		$reArr [] = $UP->add ();
		
		$UA = D ( 'UserAccount' );
		$UA->u_id = $uid;
		$reArr [] = $UA->add ();
		
		$UAM = D ( 'UserAuth' );
		$UAM->u_id = $uid;
		$UAM->AuthType = 1;
		$UAM->NickName = $qquser ['nickname'];
		$UAM->Headimgurl = $qquser ['headimgurl'];
		$UAM->OpenId = $token ['openid'];
		$UAM->Access_Token = $token ['access_token'];
		$UAM->AuthType = 1;
		$reArr [] = $UAM->add ();
		
		if (in_array ( false, $reArr )) {
			$Users->rollback ();
			$result = 0;
			throw new Exception ( 'MySQL I/0 Error.' );
		} else {
			$Users->commit ();
			// 任务系统
			$HJ = new HookJobsModel ();
			$HJ->run ( $uid, __METHOD__ );
			$result = $Users->u_id;
			// $this->success ( L ( 'send_active_mail' ), '__DOC__/index.php' );
		}
		return $result;
	}
	public function bindbyNewUid($yuid, $nuid) { // 通过openid检测用户是否绑定,
		$UA = M ( 'user_auth' );
		$data ['u_id'] = $nuid;
		$result = $UA->where ( "u_id=" . $yuid . "" )->save ( $data );
		return $result;
	}

	public function updateByWXOpenId($openid, $wxResArr) { // 通过openid更新微信用户昵称和头像
		$UA = M ( 'user_auth' );
		$data ['NickName'] = $wxResArr['nickname'];
		$data ['Headimgurl'] = $wxResArr['headimgurl'];
		$result = $UA->where ( "OpenId='" . $openid . "'" )->save ( $data );
		return $result;
	}
}
?>