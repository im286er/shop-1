<?php
/**
 * 用户教育经历类
 *
 * @author miaomin 
 * Mar 7, 2014 1:38:13 PM
 *
 * $Id$
 */
class UserEducationModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_UserEdu
	 */
	public $F;
	
	/**
	 * 用户教育经历类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->UserEdu;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 根据用户ID获取教育经历
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
	public function getUserEdu(int $uid, $public = 1) {
		$res = NULL;
		if ($public == 1) {
			$con = array (
					$this->F->UID => $uid,
					$this->F->Status => array (
							'egt',
							0 
					) 
			);
		} elseif ($public == 0) {
			$con = array (
					$this->F->UID => $uid,
					$this->F->Status => array (
							'egt',
							0 
					),
					$this->F->IsPublic => '0' 
			);
		}
		
		$res = $this->where ( $con )->order ( $this->F->StartYear . ' DESC' )->select ();
		return $res;
	}
}