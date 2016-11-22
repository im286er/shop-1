<?php
/**
 * 用户粉丝表
 *
 * @author miaomin 
 * May 6, 2014 10:01:29 AM
 *
 * $Id$
 */
class UserFollowerModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_UserFollower
	 */
	public $F;
	
	/**
	 * 用户粉丝表
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->UserFollower;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 粉丝列表
	 */
	public function fansList(int $uid) {
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
		$sqlRes = $this->field ( $this->F->FollowerID )->where ( $condition )->order ( $this->F->ID . ' desc' )->select ();
		if (is_array ( $sqlRes )) {
			$returnRes = array ();
			foreach ( $sqlRes as $key => $val ) {
				$returnRes [] = $val [$this->F->FollowerID];
			}
		}
		return $returnRes;
	}
}