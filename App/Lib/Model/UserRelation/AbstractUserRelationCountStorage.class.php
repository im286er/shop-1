<?php
/**
 * 用户关系统计存储结构抽象类
 *
 * @author miaomin 
 * May 8, 2014 3:10:49 PM
 *
 * $Id$
 */
abstract class AbstractUserRelationCountStorage {
	
	/**
	 * listCount
	 *
	 * @var unknown_type
	 */
	protected $_listCount;
	
	/**
	 * 获取ListCount
	 */
	abstract public function getListCount();
	
	/**
	 * 初始化ListCount
	 */
	abstract protected function _initListCount();
	
	/**
	 * 设置ListCount
	 */
	abstract public function setListCount($listCount);
	
	/**
	 * 关注数+1
	 */
	abstract public function incFollowingCount();
	
	/**
	 * 粉丝数+1
	 */
	abstract public function incFollowerCount();
	
	/**
	 * 好友数+1
	 */
	abstract public function incFriendsCount();
	
	/**
	 * 关注数-1
	 */
	abstract public function decFollowingCount();
	
	/**
	 * 粉丝数-1
	 */
	abstract public function decFollowerCount();
	
	/**
	 * 好友数-1
	 */
	abstract public function decFriendsCount();
}
?>