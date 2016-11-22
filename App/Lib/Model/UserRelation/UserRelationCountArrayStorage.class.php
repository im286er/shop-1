<?php
/**
 * 用户关系统计数组存储结构类
 *
 * @author miaomin 
 * May 8, 2014 3:49:34 PM
 *
 * $Id$
 */
import ( 'App.Model.UserRelation.AbstractUserRelationCountStorage' );

class UserRelationCountArrayStorage extends AbstractUserRelationCountStorage {
	
	/**
	 * 用户关系统计数组存储结构类
	 */
	public function __construct() {
		$this->_initListCount ();
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationCountStorage::_initListCount()
	 */
	protected function _initListCount() {
		$this->_listCount = array (
				'following' => 0,
				'follower' => 0,
				'friends' => 0 
		);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationCountStorage::getListCount()
	 */
	public function getListCount() {
		return $this->_listCount;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationCountStorage::setListCount()
	 */
	public function setListCount($listCount) {
		$this->_listCount = $listCount;
	}
	
	/**
	 * 关注数+1
	 */
	public function incFollowingCount() {
		$this->_listCount ['following'] += 1;
	}
	
	/**
	 * 粉丝数+1
	 */
	public function incFollowerCount() {
		$this->_listCount ['follower'] += 1;
	}
	
	/**
	 * 好友数+1
	 */
	public function incFriendsCount() {
		$this->_listCount ['friends'] += 1;
	}
	
	/**
	 * 关注数-1
	 */
	public function decFollowingCount() {
		if ($this->_listCount ['following'] > 0) {
			$this->_listCount ['following'] -= 1;
		}
	}
	
	/**
	 * 粉丝数-1
	 */
	public function decFollowerCount() {
		if ($this->_listCount ['follower'] > 0) {
			$this->_listCount ['follower'] -= 1;
		}
	}
	
	/**
	 * 好友数-1
	 */
	public function decFriendsCount() {
		if ($this->_listCount ['friends'] > 0) {
			$this->_listCount ['friends'] -= 1;
		}
	}
}

?>