<?php
/**
 * 用户工作经历类
 *
 * @author miaomin 
 * Mar 10, 2014 5:03:48 PM
 *
 * $Id$
 */
class UserWorkModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_UserWork
	 */
	public $F;
	
	/**
	 * 用户工作经历类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->UserWork;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		parent::__construct ();
	}
	
	/**
	 * 根据用户ID获取工作经历
	 *
	 * 返回结果：
	 * NULL表示没有结果
	 * False表示查询错误
	 * 二维数组表示查询结果
	 *
	 * @param int $uid        	
	 * @param int $public        	
	 * @return multitype
	 */
	public function getUserWork(int $uid, $public = 1) {
		$res = NULL;
		if ($public == 1) {
			$con = array (
					$this->F->UID => $uid,
					$this->F->Status => '0' 
			);
		} elseif ($public == 0) {
			$con = array (
					$this->F->UID => $uid,
					$this->F->Status => '0',
					$this->F->IsPublic => '0' 
			);
		}
		
		$res = $this->where ( $con )->order ( $this->F->StartYear . ' DESC' )->select ();
		return $res;
	}
}