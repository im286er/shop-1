<?php
/**
 * 任务队列Model类
 *
 * @author miaomin 
 * Jul 3, 2014 5:58:09 PM
 *
 * $Id$
 */
class JobQueueModel extends Model {
	
	/**
	 *
	 * @var DBF_JobQueue
	 */
	public $F;
	
	/**
	 * 任务队列Model类
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->JobQueue;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 更新任务
	 *
	 * @param array $jobData        	
	 *
	 * @return mixed
	 */
	public function setStat($jobData) {
		$this->create ( $jobData );
		$this->{$this->F->PROCESSDATE} = get_now ();
		$this->{$this->F->PDTIME} = time ();
		return $this->save ();
	}
	
	/**
	 * 获取任务
	 *
	 * @param string $jobid        	
	 *
	 * @return mixed
	 */
	public function getJob($jobid) {
		return $this->getByjq_code ( $jobid );
	}
	
	/**
	 * 新增任务
	 *
	 * @param array $jobData        	
	 *
	 * @return mixed
	 */
	public function addJob($jobData) {
		$data = array (
				$this->F->CREATEDATE => get_now (),
				$this->F->CDTIME => time (),
				$this->F->IP => get_client_ip () 
		);
		$data = array_merge ( $data, $jobData );
		$this->create ( $data );
		return $this->add ();
	}
}
?>