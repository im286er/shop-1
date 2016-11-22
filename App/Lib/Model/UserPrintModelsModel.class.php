<?php
/**
 * UserPrintModels基本类
 *
 * @author miaomin 
 * Nov 5, 2014 1:25:35 PM
 *
 * $Id$
 */
class UserPrintModelsModel extends Model {
	
	/**
	 *
	 * @var DBF_UserPrintModel
	 */
	public $F;
	
	// Mapping
	protected $_map = array (
			'pid' => 'p_id',
			'uid' => 'u_id',
			'print_length' => 'upm_length',
			'print_width' => 'upm_width',
			'print_height' => 'upm_height',
			'print_volume' => 'upm_volume',
			'print_convex' => 'upm_convex'
	);
	
	/**
	 * 构造
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->UserPrintModel;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
}
?>