<?php
/**
 * 用户关注表
 *
 * @author miaomin 
 * May 6, 2014 9:58:02 AM
 *
 * $Id$
 */
class UserFollowingModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_UserFollowing
	 */
	public $F;
	
	/**
	 * 用户关注表
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->UserFollowing;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 关注者列表
	 */
	public function followingList(int $uid) {
		// 返回结果
		// false - 表示查询错误
		// null - 表示结果返回为空
		// array - 查询结果
		$returnRes = false;
		
		// 查询条件
		$condition = array (
				$this->F->UID => $uid 
		);
		
		// 查询结果
		$sqlRes = $this->field ( $this->F->FollowingUID )->where ( $condition )->order ( $this->F->ID . ' desc' )->select ();
		
		if (is_array ( $sqlRes )) {
			$returnRes = array ();
			foreach ( $sqlRes as $key => $val ) {
				$returnRes [] = $val [$this->F->FollowingUID];
			}
		}
		return $returnRes;
	}
	
	/**
	 * 是否添加过这位关注者
	 *
	 * @param int $uid        	
	 * @param int $followingUID        	
	 * @return mixed
	 */
	public function isFollowing(int $uid, int $followingUID) {
		// 返回结果:
		// false - 表示查询错误
		// null - 表示结果返回为空
		// array - 查询结果
		$returnRes = false;
		
		// 查询条件
		$condition = array (
				$this->F->UID => $uid,
				$this->F->FollowingUID => $followingUID 
		);
		
		// 查询结果
		$returnRes = $this->where ( $condition )->select ();
		return $returnRes;
	}
	
	/**
	 * 是否是好友
	 *
	 * @param int $uid        	
	 * @param int $followingUID        	
	 * @return mixed
	 */
	public function isFriends(int $uid, int $friendsUID) {
		// 返回结果:
		// false - 表示查询错误
		// null - 表示结果返回为空
		// array - 查询结果
		$returnRes = false;
		
		// 查询条件
		$condition = array (
				$this->F->UID => $uid,
				$this->F->FollowingUID => $friendsUID,
				$this->F->IsFriend => 1 
		);
		
		// 查询结果
		$returnRes = $this->where ( $condition )->select ();
		return $returnRes;
	}
	
	/**
	 * 移除一位关注者
	 *
	 * @param int $uid        	
	 * @param int $followingUID        	
	 * @return mixed
	 */
	public function removeOne(int $uid, int $followingUID) {
		// 返回结果
		$returnRes = false;
		
		// 时间
		$nowFMT = get_now ();
		$nowTS = time ();
		
		// 开启一个事务
		$this->startTrans ();
		
		// 更新关系表
		// 用户主动取消关注
		$URelation = new UserRelationModel ();
		$followingRelRes = $URelation->unfollowing ( $uid, $followingUID );
		// 用户被动掉粉丝
		$URelation = new UserRelationModel ();
		$followerRelRes = $URelation->unfollower ( $followingUID, $uid );
		
		// 如果为好友则需要解除好友关系
		$isFriends = $this->isFriends ( $uid, $followingUID );
		if (is_array ( $isFriends )) {
			// 更新关注者好友信息
			$this->create ();
			$condition = array (
					$this->F->UID => $followingUID,
					$this->F->FollowingUID => $uid 
			);
			$data = array (
					$this->F->IsFriend => 0,
					$this->F->FriendDate => $nowFMT,
					$this->F->FriendDateTS => $nowTS 
			);
			$followingDelRes = ($this->where ( $condition )->save ( $data ) && $followingDelRes);
		}
		
		// 删除粉丝表
		$UFollower = new UserFollowerModel ();
		$condition = array (
				$UFollower->F->UID => $followingUID,
				$UFollower->F->FollowerID => $uid 
		);
		$followerDelRes = $UFollower->where ( $condition )->delete ();
		
		// 最后删除关注表
		$condition = array (
				$this->F->UID => $uid,
				$this->F->FollowingUID => $followingUID 
		);
		$followingDelRes = $this->where ( $condition )->delete ();
		
		// 提交事务或回滚
		if ($followingDelRes && $followerDelRes && $followingRelRes && $followerRelRes) {
			$this->commit ();
			$returnRes = true;
		} else {
			$this->rollback ();
		}
		
		return $returnRes;
	}
	
	/**
	 * 添加一位关注者
	 *
	 * @param int $uid        	
	 * @param int $followingUID        	
	 * @return mixed
	 */
	public function addOne(int $uid, int $followingUID) {
		// 返回结果
		$returnRes = false;
		
		// 时间
		$nowFMT = get_now ();
		$nowTS = time ();
		
		// 开启一个事务
		$this->startTrans ();
		
		// 如果互相关注了则成为好友
		$isEachFollowing = $this->isFollowing ( $followingUID, $uid );
		if (is_array ( $isEachFollowing )) {
			// 插入关注表
			$this->create ();
			$data = array (
					$this->F->UID => $uid,
					$this->F->FollowingUID => $followingUID,
					$this->F->CreateDate => $nowFMT,
					$this->F->CreateDateTS => $nowTS,
					$this->F->IsFriend => 1,
					$this->F->FriendDate => $nowFMT,
					$this->F->FriendDateTS => $nowTS 
			);
			$followingAddRes = $this->add ( $data );
			// 更新关注者好友信息
			$this->create ();
			$condition = array (
					$this->F->UID => $followingUID,
					$this->F->FollowingUID => $uid 
			);
			$data = array (
					$this->F->IsFriend => 1,
					$this->F->FriendDate => $nowFMT,
					$this->F->FriendDateTS => $nowTS 
			);
			$followingAddRes = ($this->where ( $condition )->save ( $data ) && $followingAddRes);
		} else {
			// 插入关注表
			$this->create ();
			$data = array (
					$this->F->UID => $uid,
					$this->F->FollowingUID => $followingUID,
					$this->F->CreateDate => $nowFMT,
					$this->F->CreateDateTS => $nowTS 
			);
			$followingAddRes = $this->add ( $data );
		}
		
		// 插入粉丝表
		$UFollower = new UserFollowerModel ();
		$UFollower->create ();
		$data = array (
				$UFollower->F->UID => $followingUID,
				$UFollower->F->FollowerID => $uid,
				$UFollower->F->CreateDate => $nowFMT,
				$UFollower->F->CreateDateTS => $nowTS 
		);
		$followerAddRes = $UFollower->add ( $data );
		
		// 插入关系表
		// 用户主动关注
		$URelation = new UserRelationModel ();
		$followingRelRes = $URelation->following ( $uid, $followingUID );
		// 用户被动增加粉丝
		$URelation = new UserRelationModel ();
		$followerRelRes = $URelation->follower ( $followingUID, $uid );
		
		// 提交事务或回滚
		if ($followingAddRes && $followerAddRes && $followingRelRes && $followerRelRes) {
			$this->commit ();
			$returnRes = true;
		} else {
			$this->rollback ();
		}
		
		return $returnRes;
	}
}