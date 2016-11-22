<?php
/**
 * 用户关系列表数组存储结构类
 *
 * @author miaomin 
 * May 8, 2014 3:27:43 PM
 *
 * $Id$
 */
import ( 'App.Model.UserRelation.AbstractUserRelationListStorage' );
class UserRelationListArrayStorage extends AbstractUserRelationListStorage {
	
	/**
	 * 用户关系列表数组存储结构类
	 */
	public function __construct() {
		$this->_initList ();
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationListStorage::initList()
	 */
	protected function _initList() {
		$this->_list = array (
				'following' => array (),
				'follower' => array (),
				'friends' => array () 
		);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationListStorage::setList()
	 */
	public function setList($list) {
		$this->_list = $list;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationListStorage::getList()
	 */
	public function getList() {
		return $this->_list;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationListStorage::addFollowing()
	 */
	public function addFollowing(int $itemId) {
		array_unshift ( $this->_list ['following'], $itemId );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationListStorage::addFollower()
	 */
	public function addFollower(int $itemId) {
		array_unshift ( $this->_list ['follower'], $itemId );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationListStorage::addFriends()
	 */
	public function addFriends(int $itemId) {
		array_unshift ( $this->_list ['friends'], $itemId );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationListStorage::removeFollowing()
	 */
	public function removeFollowing(int $itemId) {
		if (in_array ( $itemId, $this->_list ['following'] )) {
			$listKey = array_search ( $itemId, $this->_list ['following'] );
			unset ( $this->_list ['following'] [$listKey] );
		}
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see AbstractUserRelationListStorage::removeFollower()
	 */
	public function removeFollower(int $itemId) {
		if (in_array ( $itemId, $this->_list ['follower'] )) {
			$listKey = array_search ( $itemId, $this->_list ['follower'] );
			unset ( $this->_list ['follower'] [$listKey] );
		}
	}
	
	/**
	 * 移除一个好友
	 */
	public function removeFriends(int $itemId) {
		if (in_array ( $itemId, $this->_list ['friends'] )) {
			$listKey = array_search ( $itemId, $this->_list ['friends'] );
			unset ( $this->_list ['friends'] [$listKey] );
		}
	}
}
?>