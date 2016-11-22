<?php
/**
 * 用户关系列表存储结构抽象类
 *
 * @author miaomin 
 * May 8, 2014 3:10:49 PM
 *
 * $Id$
 */
abstract class AbstractUserRelationListStorage {
	
	/**
	 * list
	 *
	 * @var unknown_type
	 */
	protected $_list;
	
	/**
	 * 获取List
	 */
	abstract public function getList();
	
	/**
	 * 初始化List
	 */
	abstract protected function _initList();
	
	/**
	 * 设置List
	 */
	abstract public function setList($list);
	
	/**
	 * 增加一个关注
	 */
	abstract public function addFollowing(int $itemId);
	
	/**
	 * 增加一个粉丝
	 */
	abstract public function addFollower(int $itemId);
	
	/**
	 * 增加一个好友
	 */
	abstract public function addFriends(int $itemId);
	
	/**
	 * 移除一个关注
	 */
	abstract public function removeFollowing(int $itemId);
	
	/**
	 * 移除一个粉丝
	 */
	abstract public function removeFollower(int $itemId);
	
	/**
	 * 移除一个好友
	 */
	abstract public function removeFriends(int $itemId);
}
?>