<?php
/**
 * 客户端请求日志Model类
 *
 * @author miaomin 
 * Oct 8, 2013 4:46:11 PM
 */
class LogClientModel extends Model {
	
	/**
	 * 自动完成
	 *
	 * @var unknown_type
	 */
	protected $_auto = array (
			array (
					'log_createdate',
					'get_now',
					1,
					'function' 
			),
			array (
					'log_ip',
					'get_client_ip',
					1,
					'function'
			),
			array (
					'log_useragent',
					'get_client_agent',
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
	 * @var DBF_LogClient
	 */
	public $F;
	
	/**
	 * 客户端请求日志Model类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->LogClient;
		$this->trueTableName = $this->F->_Table;
		
		parent::__construct ();
	}
	
	/**
	 * 插入一条日志
	 *
	 * @param array $data        	
	 */
	public function addLog($data) {
		$this->create ( $data );
		return $this->add ();
	}
}
?>