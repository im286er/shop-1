<?php
/**
 * 活动主表基本类
 * 
 * @author miaomin
 * Jul 15, 2015 3:04:49 PM
 *
 */
class SPMainModel extends Model {
    
    protected $_map = array (
        'sp_name' => 'spm_title',
        'sp_maintype' => 'spm_type',
        'begindate' => 'spm_begin',
        'enddate' => 'spm_end',
        'sp_pids' => 'spm_pids'
    );
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_SPMain
	 */
	public $F;
	
	/**
	 * 活动主表基本类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->SPMain;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
}