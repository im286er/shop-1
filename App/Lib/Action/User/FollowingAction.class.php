<?php
/**
 * 用户关注行为类
 *
 * @author miaomin 
 * May 6, 2014 10:06:34 AM
 *
 * $Id$
 */
class FollowingAction extends CommonAction {
	
	/**
	 * 用户关注行为类
	 */
	public function __construct() {
		parent::__construct ();
		$this->DBF = new DBF ();
	}
	
	/**
	 * 查看好友
	 */
	public function myfriends() {
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
		@load ( '@.Paging' );
		$uid = $this->_loginUserObj->{$this->_loginUserObj->F->ID};
		// 统计数量
		$UF = new UserFollowingModel ();
		$sql = "SELECT COUNT(UF.u_id) as Count
		FROM tdf_user_following UF
		WHERE UF.u_id = '{$uid}' AND UF.uf_isfriend = '1'";
		$countRes = $UF->query ( $sql );
		$totalCount = $countRes [0] ['Count'];
		// 获取信息
		$limit = 20;
		$page = intval ( $this->_get ( 'page' ) );
		$page = $page ? $page : 1;
		$startPage = ($page - 1) * $limit;
		$sql = "SELECT UF.u_id,UF.uf_touid,UF.uf_isfriend,U.u_dispname,U.u_avatar,UP.u_intro,UR.ur_list_num
		FROM tdf_user_following UF
		LEFT JOIN tdf_users U ON (U.u_id = UF.uf_touid)
		LEFT JOIN tdf_user_profile UP ON (UP.u_id = UF.uf_touid)
		LEFT JOIN tdf_user_relation UR ON (UR.u_id = UF.uf_touid)
		WHERE UF.u_id = '{$uid}' AND UF.uf_isfriend = '1' ORDER BY UF.uf_frienddate_ts DESC LIMIT {$startPage},{$limit}";
		$UF = new UserFollowingModel ();
		$sqlRes = $UF->query ( $sql );
		foreach ( $sqlRes as $key => $val ) {
			$sqlRes [$key] ['ur_list_num_arr'] = unserialize ( $sqlRes [$key] ['ur_list_num'] );
		}
		$this->assign ( 'totalCount', $totalCount );
		$this->assign ( 'followList', $sqlRes );
		// 分页
		$BaseUrl = C ( 'REDIRECT_PRE_URL' ) . U ( 'following/myfriends?', 1 );
		$this->assign ( 'BaseUrl', $BaseUrl );
		$paging = getPagingInfo2 ( $totalCount, $this->_get ( 'page' ), $limit, 4, $BaseUrl );
		$this->assign ( 'Paging', $paging );
		$this->_renderPage ();
	}
	
	/**
	 * 查看我的粉丝
	 */
	public function myfans() {
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
		@load ( '@.Paging' );
		$uid = $this->_loginUserObj->{$this->_loginUserObj->F->ID};
		// 统计数量
		$UF = new UserFollowerModel ();
		$sql = "SELECT COUNT(UF.u_id) as Count
		FROM tdf_user_follower UF
		WHERE UF.u_id = '{$uid}'";
		$countRes = $UF->query ( $sql );
		$totalCount = $countRes [0] ['Count'];
		// 获取信息
		$limit = 20;
		$page = intval ( $this->_get ( 'page' ) );
		$page = $page ? $page : 1;
		$startPage = ($page - 1) * $limit;
		$sql = "SELECT UFO.u_id,UFO.uf_isfriend,U.u_dispname,U.u_avatar,UP.u_intro,UR.ur_list_num FROM tdf_user_following UFO
		LEFT JOIN tdf_users U ON (U.u_id = UFO.u_id)
		LEFT JOIN tdf_user_profile UP ON (UP.u_id = UFO.u_id)
		LEFT JOIN tdf_user_relation UR ON (UR.u_id = UFO.u_id)
		LEFT JOIN tdf_user_follower UFOL ON (UFOL.ufr_fromuid = UFO.u_id)
		WHERE UFO.u_id IN (SELECT ufr_fromuid FROM (SELECT UF.ufr_fromuid
		FROM tdf_user_follower UF
		WHERE UF.u_id = '{$uid}' LIMIT 0,20) AS formuid) AND UFO.uf_touid = '{$uid}' AND UFOL.u_id = '{$uid}' ORDER BY UFOL.ufr_createdate_ts DESC";
		$UF = new UserFollowingModel ();
		$sqlRes = $UF->query ( $sql );
		foreach ( $sqlRes as $key => $val ) {
			$sqlRes [$key] ['ur_list_num_arr'] = unserialize ( $sqlRes [$key] ['ur_list_num'] );
		}
		$this->assign ( 'totalCount', $totalCount );
		$this->assign ( 'myfansList', $sqlRes );
		// 分页
		$BaseUrl = C ( 'REDIRECT_PRE_URL' ) . U ( 'following/myfans?', 1 );
		$this->assign ( 'BaseUrl', $BaseUrl );
		$paging = getPagingInfo2 ( $totalCount, $this->_get ( 'page' ), $limit, 4, $BaseUrl );
		$this->assign ( 'Paging', $paging );
		$this->assign('showtitle',"我的粉丝-3D城");
		$this->_renderPage ();
	}
	
	/**
	 * 查看我的关注者
	 */
	public function myfollow() {
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
		@load ( '@.Paging' );
		$uid = $this->_loginUserObj->{$this->_loginUserObj->F->ID};
		// 统计数量
		$UF = new UserFollowingModel ();
		$sql = "SELECT COUNT(UF.u_id) as Count
				FROM tdf_user_following UF
				WHERE UF.u_id = '{$uid}'";
		$countRes = $UF->query ( $sql );
		$totalCount = $countRes [0] ['Count'];
		// 获取信息
		$limit = 20;
		$page = intval ( $this->_get ( 'page' ) );
		$page = $page ? $page : 1;
		$startPage = ($page - 1) * $limit;
		$sql = "SELECT UF.u_id,UF.uf_touid,UF.uf_isfriend,U.u_dispname,U.u_avatar,UP.u_intro,UR.ur_list_num
				FROM tdf_user_following UF 
				LEFT JOIN tdf_users U ON (U.u_id = UF.uf_touid) 
				LEFT JOIN tdf_user_profile UP ON (UP.u_id = UF.uf_touid) 
				LEFT JOIN tdf_user_relation UR ON (UR.u_id = UF.uf_touid)
				WHERE UF.u_id = '{$uid}' ORDER BY UF.uf_createdate_ts DESC LIMIT {$startPage},{$limit}";
		$UF = new UserFollowingModel ();
		$sqlRes = $UF->query ( $sql );
		foreach ( $sqlRes as $key => $val ) {
			$sqlRes [$key] ['ur_list_num_arr'] = unserialize ( $sqlRes [$key] ['ur_list_num'] );
		}
		$this->assign ( 'totalCount', $totalCount );
		$this->assign ( 'followList', $sqlRes );
		// 分页
		$BaseUrl = C ( 'REDIRECT_PRE_URL' ) . U ( 'following/myfollow?', 1 );
		$this->assign ( 'BaseUrl', $BaseUrl );
		$paging = getPagingInfo2 ( $totalCount, $this->_get ( 'page' ), $limit, 4, $BaseUrl );
		$this->assign ( 'Paging', $paging );
		$this->assign('showtitle',"我的关注-3D城");
		$this->_renderPage ();
	}
	
	/**
	 * 查看关注者
	 */
	public function followlist() {
		try {
			// 是否是一个有效ID
			$followingData = $this->_validFollowingData ( $_GET );
			if (! $followingData) {
				throw new Exception ( 'Parameter format error.' );
			}
			$uid = $followingData ['u'];
			// 用户
			$U = new UsersModel ();
			$userinfo = $U->getUserByUid ( $uid );
			$this->assign ( 'UPROFILE', $userinfo );
			//
			@load ( '@.Paging' );
			// 统计数量
			$UF = new UserFollowingModel ();
			$sql = "SELECT COUNT(UF.u_id) as Count
			FROM tdf_user_following UF
			WHERE UF.u_id = '{$uid}'";
			$countRes = $UF->query ( $sql );
			$totalCount = $countRes [0] ['Count'];
			// 获取信息
			$limit = 20;
			$page = intval ( $this->_get ( 'page' ) );
			$page = $page ? $page : 1;
			$startPage = ($page - 1) * $limit;
			$sql = "SELECT UF.uf_touid AS u_id,UF.uf_isfriend,U.u_dispname,U.u_avatar,UP.u_intro,UR.ur_list_num
			FROM tdf_user_following UF
			LEFT JOIN tdf_users U ON (U.u_id = UF.uf_touid)
			LEFT JOIN tdf_user_profile UP ON (UP.u_id = UF.uf_touid)
			LEFT JOIN tdf_user_relation UR ON (UR.u_id = UF.uf_touid)
			WHERE UF.u_id = '{$uid}' ORDER BY UF.uf_createdate_ts DESC LIMIT {$startPage},{$limit}";
			$UF = new UserFollowingModel ();
			$sqlRes = $UF->query ( $sql );
			foreach ( $sqlRes as $key => $val ) {
				$sqlRes [$key] ['ur_list_num_arr'] = unserialize ( $sqlRes [$key] ['ur_list_num'] );
			}
			// pr($sqlRes);
			// exit;
			// 判断登录
			if ($this->_isLogin ()) {
				// 标明结果用户同当前登录用户的好友关系和关注关系
				$UR = new UserRelationModel ();
				$sqlRes = $UR->markUserRelation ( $sqlRes, $this->_loginUserObj->{$this->_loginUserObj->F->ID} );
			}
			$this->assign ( 'totalCount', $totalCount );
			$this->assign ( 'followList', $sqlRes );
			// 分页
			$BaseUrl = C ( 'REDIRECT_PRE_URL' ) . U ( 'following/followlist?', 1 );
			$this->assign ( 'BaseUrl', $BaseUrl );
			$paging = getPagingInfo2 ( $totalCount, $this->_get ( 'page' ), $limit, 4, $BaseUrl );
			$this->assign ( 'Paging', $paging );
			$this->_renderPage ();
		} catch ( Exception $e ) {
			$this->error ( $e->getMessage (), '__APP__' );
		}
	}
	
	/**
	 * 查看粉丝
	 */
	public function fanslist() {
		try {
			// 是否是一个有效ID
			$followingData = $this->_validFollowingData ( $_GET );
			if (! $followingData) {
				throw new Exception ( 'Parameter format error.' );
			}
			$uid = $followingData ['u'];
			// 用户
			$U = new UsersModel ();
			$userinfo = $U->getUserByUid ( $uid );
			$this->assign ( 'UPROFILE', $userinfo );
			//
			@load ( '@.Paging' );
			// 统计数量
			$UF = new UserFollowerModel ();
			$sql = "SELECT COUNT(UF.u_id) as Count
			FROM tdf_user_follower UF
			WHERE UF.u_id = '{$uid}'";
			$countRes = $UF->query ( $sql );
			$totalCount = $countRes [0] ['Count'];
			// 获取信息
			$limit = 20;
			$page = intval ( $this->_get ( 'page' ) );
			$page = $page ? $page : 1;
			$startPage = ($page - 1) * $limit;
			// 如果是登录状态需要获取同登录用户的好友关系和关注关系
			$sql = "SELECT UFO.u_id,UFO.uf_isfriend,U.u_dispname,U.u_avatar,UP.u_intro,UR.ur_list_num,UR.ur_list FROM tdf_user_following UFO
			LEFT JOIN tdf_users U ON (U.u_id = UFO.u_id)
			LEFT JOIN tdf_user_profile UP ON (UP.u_id = UFO.u_id)
			LEFT JOIN tdf_user_relation UR ON (UR.u_id = UFO.u_id)
			LEFT JOIN tdf_user_follower UFOL ON (UFOL.ufr_fromuid = UFO.u_id)
			WHERE UFO.u_id IN (SELECT ufr_fromuid FROM (SELECT UF.ufr_fromuid
			FROM tdf_user_follower UF
			WHERE UF.u_id = '{$uid}' LIMIT 0,20) AS formuid) AND UFO.uf_touid = '{$uid}' AND UFOL.u_id = '{$uid}' ORDER BY UFOL.ufr_createdate_ts DESC";
			$UF = new UserFollowingModel ();
			$sqlRes = $UF->query ( $sql );
			foreach ( $sqlRes as $key => $val ) {
				$sqlRes [$key] ['ur_list_num_arr'] = unserialize ( $sqlRes [$key] ['ur_list_num'] );
			}
			// 判断登录
			if ($this->_isLogin ()) {
				// 标明结果用户同当前登录用户的好友关系和关注关系
				$UR = new UserRelationModel ();
				$sqlRes = $UR->markUserRelation ( $sqlRes, $this->_loginUserObj->{$this->_loginUserObj->F->ID} );
			}
			$this->assign ( 'totalCount', $totalCount );
			$this->assign ( 'myfansList', $sqlRes );
			// 分页
			$BaseUrl = C ( 'REDIRECT_PRE_URL' ) . U ( 'following/fanslist?', 1 );
			$this->assign ( 'BaseUrl', $BaseUrl );
			$paging = getPagingInfo2 ( $totalCount, $this->_get ( 'page' ), $limit, 4, $BaseUrl );
			$this->assign ( 'Paging', $paging );
			$this->_renderPage ();
		} catch ( Exception $e ) {
			$this->error ( $e->getMessage (), '__APP__' );
		}
	}
	
	/**
	 * 移除关注
	 */
	public function remove() {
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
		try {
			// 数据验证
			/*
			 * 1 是否是一个有效ID 2自己不能移除自己 3 尚未关注过
			 */
			
			// 是否是一个有效ID
			$followingData = $this->_validFollowingData ( $_GET );
			if (! $followingData) {
				throw new Exception ( 'Parameter format error.' );
			}
			
			// uid - 用户ID
			// followingUID - 待移除关注者ID
			$uid = $this->_loginUserObj->{$this->_loginUserObj->F->ID};
			$followingUID = $followingData ['u'];
			
			// 自己不能移除自己
			if ($uid == $followingUID) {
				throw new Exception ( 'You cann\'t unfollow youself.' );
			}
			
			// 尚未关注过
			$UF = new UserFollowingModel ();
			$followingRes = $UF->isFollowing ( $uid, $followingUID );
			if ($followingRes === false) {
				throw new Exception ( 'Query execute error.' );
			} elseif ($followingRes === null) {
				throw new Exception ( 'Already unfollow.' );
			} else {
				// 移除关注
				$addRes = $UF->removeOne ( $uid, $followingUID );
				redirect ( $_SERVER ["HTTP_REFERER"] );
			}
		} catch ( Exception $e ) {
			// 异常处理
			// echo $e->getMessage ();
			$this->error ( $e->getMessage (), '__APP__' );
		}
	}
	
	/**
	 * 添加关注
	 */
	public function add() {
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
		try {
			// 数据验证
			/*
			 * 1 是否是一个有效ID 2自己不能加自己 3 是否已经关注过
			 */
			
			// 是否是一个有效ID
			$followingData = $this->_validFollowingData ( $_GET );
			if (! $followingData) {
				throw new Exception ( 'Parameter format error.' );
			}
			
			// uid - 用户ID
			// followingUID - 关注者ID
			$uid = $this->_loginUserObj->{$this->_loginUserObj->F->ID};
			$followingUID = $followingData ['u'];
			
			// 自己不能加自己
			if ($uid == $followingUID) {
				throw new Exception ( 'You cann\'t following youself.' );
			}
			
			// 是否已经关注过
			$UF = new UserFollowingModel ();
			$followingRes = $UF->isFollowing ( $uid, $followingUID );
			if ($followingRes === false) {
				throw new Exception ( 'Query execute error.' );
			} elseif (is_array ( $followingRes )) {
				throw new Exception ( 'Already following.' );
			} else {
				// 加关注
				$addRes = $UF->addOne ( $uid, $followingUID );
				redirect ( $_SERVER ["HTTP_REFERER"] );
			}
		} catch ( Exception $e ) {
			// 异常处理
			// echo $e->getMessage ();
			$this->error ( $e->getMessage (), '__APP__' );
		}
	}
	
	/**
	 * 查看关注
	 */
	public function view() {
		try {
			// 数据验证
			
			// 是否是一个有效ID
			$followingData = $this->_validFollowingData ( $_GET );
			if (! $followingData) {
				throw new Exception ( 'Parameter format error.' );
			}
			$uid = $followingData ['u'];
			$URelation = new UserRelationModel ();
			$relationList = $URelation->getList ( $uid );
			$this->show ( vd ( $relationList ) );
		} catch ( Exception $e ) {
		}
	}
	
	/**
	 * 添加关注的数据验证
	 *
	 * @param array $followingData        	
	 * @return multitype: boolean
	 */
	private function _validFollowingData($followingData) {
		$PVC = new PVC2 ();
		$PVC->setStrictMode ( true )->setModeArray ()->SourceArray = $followingData;
		$PVC->isInt ()->validateMust ()->add ( 'u' );
		if ($PVC->verifyAll ()) {
			return $PVC->ResultArray;
		} else {
			return false;
		}
	}
}