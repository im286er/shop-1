<?php
/**
 * 用户关系表
 *
 * @author miaomin 
 * May 6, 2014 9:51:15 AM
 *
 * $Id$
 */
class UserRelationModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_UserRelation
	 */
	public $F;
	
	/**
	 *
	 * @var AbstractUserRelationListStorage
	 */
	protected $listStorage;
	
	/**
	 *
	 * @var AbstractUserRelationCountStorage
	 */
	protected $listCountStorage;
	
	/**
	 * 用户关系表
	 */
	public function __construct() {
		import ( 'App.Model.UserRelation.UserRelationCountArrayStorage' );
		import ( 'App.Model.UserRelation.UserRelationListArrayStorage' );
		
		$this->DBF = new DBF ();
		$this->F = $this->DBF->UserRelation;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		$this->listStorage = new UserRelationListArrayStorage ();
		$this->listCountStorage = new UserRelationCountArrayStorage ();
		
		parent::__construct ();
	}
	
	/**
	 * 设置关系列表存储结构
	 */
	public function setListStorage(AbstractUserRelationListStorage $storage) {
		$this->listStorage = $storage;
	}
	
	/**
	 * 设置关系统计存储结构
	 */
	public function setListCountStorage(AbstractUserRelationCountStorage $storage) {
		$this->listCountStorage = $storage;
	}
	
	/**
	 * 获取关系统计存储结构
	 */
	public function getListCountStorage() {
		return $this->listCountStorage;
	}
	
	/**
	 * 执行创建命令
	 */
	private function _create(int $uid, array $listArr, array $listCountArr) {
		$this->create ();
		$data = array (
				$this->F->UID => $uid,
				$this->F->List => serialize ( $listArr ),
				$this->F->CountList => serialize ( $listCountArr ),
				$this->F->Ver => 1,
				$this->F->LastUpdate => get_now (),
				$this->F->LastUpdateTS => time () 
		);
		$insertRes = $this->add ( $data );
		return $insertRes;
	}
	
	/**
	 * 执行更新命令
	 */
	private function _update(int $uid, $listArr, $listCountArr) {
		$this->create ();
		$data = array (
				$this->F->UID => $uid,
				$this->F->List => serialize ( $listArr ),
				$this->F->CountList => serialize ( $listCountArr ),
				$this->F->Ver => 1,
				$this->F->LastUpdate => get_now (),
				$this->F->LastUpdateTS => time () 
		);
		$updateRes = $this->save ( $data );
		return $updateRes;
	}
	
	/**
	 * 获取用户关系
	 *
	 * @param int $uid        	
	 * @return mixed
	 */
	public function getList(int $uid) {
		$listRes = $this->find ( $uid );
		return $listRes;
	}
	
	/**
	 * 加粉丝
	 *
	 * @param int $uid        	
	 * @param int $followerUID        	
	 */
	public function follower(int $uid, int $followerUID) {
		$listRes = $this->getList ( $uid );
		if ($listRes === null) {
			// 没记录
			$this->listStorage->addFollower ( $followerUID );
			$this->listCountStorage->incFollowerCount ();
			return $this->_create ( $uid, $this->listStorage->getList (), $this->listCountStorage->getListCount () );
		} else {
			// 有记录
			$listArr = unserialize ( $listRes [$this->F->List] );
			$listCountArr = unserialize ( $listRes [$this->F->CountList] );
			
			$this->listStorage->setList ( $listArr );
			$this->listStorage->addFollower ( $followerUID );
			
			$this->listCountStorage->setListCount ( $listCountArr );
			$this->listCountStorage->incFollowerCount ();
			
			// 如果互相关注了则成为好友
			$UFollowing = new UserFollowingModel ();
			$isEachFollowing = $UFollowing->isFollowing ( $uid, $followerUID );
			if (is_array ( $isEachFollowing )) {
				//
				$this->listStorage->addFriends ( $followerUID );
				$this->listCountStorage->incFriendsCount ();
			}
			
			return $this->_update ( $uid, $this->listStorage->getList (), $this->listCountStorage->getListCount () );
		}
	}
	
	/**
	 * 加关注
	 *
	 * @param int $uid        	
	 * @param int $followingUID        	
	 */
	public function following(int $uid, int $followingUID) {
		$listRes = $this->getList ( $uid );
		if ($listRes === null) {
			// 没记录
			$this->listStorage->addFollowing ( $followingUID );
			$this->listCountStorage->incFollowingCount ();
			
			$insertRes = $this->_create ( $uid, $this->listStorage->getList (), $this->listCountStorage->getListCount () );
		} else {
			// 有记录
			$listArr = unserialize ( $listRes [$this->F->List] );
			$listCountArr = unserialize ( $listRes [$this->F->CountList] );
			
			$this->listStorage->setList ( $listArr );
			$this->listStorage->addFollowing ( $followingUID );
			
			$this->listCountStorage->setListCount ( $listCountArr );
			$this->listCountStorage->incFollowingCount ();
			
			// 如果互相关注了则成为好友
			$UFollowing = new UserFollowingModel ();
			$isEachFollowing = $UFollowing->isFollowing ( $followingUID, $uid );
			if (is_array ( $isEachFollowing )) {
				//
				$this->listStorage->addFriends ( $followingUID );
				$this->listCountStorage->incFriendsCount ();
			}
			$insertRes = $this->_update ( $uid, $this->listStorage->getList (), $this->listCountStorage->getListCount () );
		}
		
		return $insertRes;
	}
	
	/**
	 * 移除关注
	 *
	 * @param int $uid        	
	 * @param int $followingUID        	
	 */
	public function unfollowing(int $uid, int $followingUID) {
		//
		$listRes = $this->getList ( $uid );
		if (is_array ( $listRes )) {
			$listArr = unserialize ( $listRes [$this->F->List] );
			$listCountArr = unserialize ( $listRes [$this->F->CountList] );
			
			$this->listStorage->setList ( $listArr );
			$this->listStorage->removeFollowing ( $followingUID );
			
			$this->listCountStorage->setListCount ( $listCountArr );
			$this->listCountStorage->decFollowingCount ();
			
			// 如果为好友则需要解除好友关系
			$UFollowing = new UserFollowingModel ();
			$isFriends = $UFollowing->isFriends ( $uid, $followingUID );
			if (is_array ( $isFriends )) {
				$this->listStorage->removeFriends ( $followingUID );
				$this->listCountStorage->decFriendsCount ();
			}
			
			return $this->_update ( $uid, $this->listStorage->getList (), $this->listCountStorage->getListCount () );
		}
		return false;
	}
	
	/**
	 * 移除粉丝
	 *
	 * @param int $uid        	
	 * @param int $followerUID        	
	 * @param int $isFriends        	
	 */
	public function unfollower(int $uid, int $followerUID) {
		//
		$listRes = $this->getList ( $uid );
		if (is_array ( $listRes )) {
			$listArr = unserialize ( $listRes [$this->F->List] );
			$listCountArr = unserialize ( $listRes [$this->F->CountList] );
			
			$this->listStorage->setList ( $listArr );
			$this->listStorage->removeFollower ( $followerUID );
			
			$this->listCountStorage->setListCount ( $listCountArr );
			$this->listCountStorage->decFollowerCount ();
			
			// 如果为好友则需要解除好友关系
			$UFollowing = new UserFollowingModel ();
			$isFriends = $UFollowing->isFriends ( $uid, $followerUID );
			if (is_array ( $isFriends )) {
				$this->listStorage->removeFriends ( $followerUID );
				$this->listCountStorage->decFriendsCount ();
			}
			return $this->_update ( $uid, $this->listStorage->getList (), $this->listCountStorage->getListCount () );
		}
		
		return false;
	}
	
	/**
	 * 获取好友圈列表
	 *
	 * @param int $uid        	
	 * @return mixed
	 */
	public function friendsList(int $uid) {
		$listRes = $this->getList ( $uid );
		if (is_array ( $listRes )) {
			$listArr = unserialize ( $listRes [$this->F->List] );
			$listCountArr = unserialize ( $listRes [$this->F->CountList] );
		}
		return $listArr ['friends'];
	}
	
	/**
	 * 标记一个用户列表中所有的用户同某一个用户的好友关系以及关注关系
	 *
	 * @param array $userlist        	
	 * @param int $uid        	
	 */
	public function markUserRelation($userlist, $uid) {
		// 需要判断用户列表同登录用户的关系
		// 结果的ISFRIEND标明结果用户同当前登录用户的好友关系
		$returnRes = false;
		$userRelation = $this->getList ( $uid );
		// 有可能没有记录
		if ($userRelation) {
			$loginRList = unserialize ( $userRelation [$this->F->List] );
			$loginFriendsList = $loginRList ['friends'];
			$loginFollowingList = $loginRList ['following'];
			//
			foreach ( $userlist as $key => $val ) {
				// 好友关系
				if ($userlist [$key] ['u_id'] == $uid) {
					// 就是当前登录用户自己
					$userlist [$key] ['uf_isfriend'] = - 1;
				} elseif (in_array ( $userlist [$key] ['u_id'], $loginFriendsList )) {
					$userlist [$key] ['uf_isfriend'] = 1;
				} else {
					$userlist [$key] ['uf_isfriend'] = 0;
				}
				// 关注关系
				if ($userlist [$key] ['u_id'] == $uid) {
					// 就是当前登录用户自己
					$userlist [$key] ['uf_isfollowing'] = - 1;
				} elseif (in_array ( $userlist [$key] ['u_id'], $loginFollowingList )) {
					$userlist [$key] ['uf_isfollowing'] = 1;
				} else {
					$userlist [$key] ['uf_isfollowing'] = 0;
				}
			}
			$returnRes = $userlist;
		}else{
			foreach ( $userlist as $key => $val ) {
				$userlist [$key] ['uf_isfriend'] = 0;
				$userlist [$key] ['uf_isfollowing'] = 0;
			}
			$returnRes = $userlist;
		}
		return $returnRes;
	}
}