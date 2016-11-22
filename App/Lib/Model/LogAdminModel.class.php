<?php
/**
 * 后台操作日志Model类
 * @author zhangzhibin 
 * 
 *$Id: LogAdminModel.class.php 636 2013-10-19 07:07:25Z zhangzhibin $
 */
class LogAdminModel extends Model {
	
	/**
	 * 自动完成
	 *
	 * @var unknown_type
	 */
	protected $_auto = array (
			array (
					'logip',
					'get_client_ip',
					1,
					'function'
			)
	);
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 * @var DBF_LogAdmin
	 */
	public $F;
	
	/**
	 * 客户端请求日志Model类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->LogAdmin;
		$this->trueTableName = $this->F->_Table;
		parent::__construct ();
	}
	
	/**
	 * 插入一条日志
	 *
	 * @param array $data    
	 * $data 数组:
	 */
	public function addLog($data) {
		$this->create ( $data );
		return $this->add ();
	}
}
?>