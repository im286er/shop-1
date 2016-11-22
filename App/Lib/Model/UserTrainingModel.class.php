<?php
/**
 * 用户培训经历类
 *
 * @author miaomin 
 * Mar 10, 2014 4:54:16 PM
 *
 * $Id$
 */
class UserTrainingModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_UserTrain
	 */
	public $F;
	
	/**
	 * 用户培训经历类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->UserTrain;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 根据用户ID获取培训经历
	 *
	 * 返回结果：
	 * NULL表示没有结果
	 * False表示查询错误
	 * 二维数组表示查询结果
	 *
	 * @param int $uid        	
	 * @return multitype
	 */
	public function getUserTrain(int $uid) {
		$res = NULL;
		$con = array (
				$this->F->UID => $uid 
		);
		$res = $this->where ( $con )->select ();
		return $res;
	}
}