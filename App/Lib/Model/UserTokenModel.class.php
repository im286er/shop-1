<?php
/**
 * 用户登录口令Model类
 *
 * @author miaomin 
 * Dec 18, 2013 1:11:47 PM
 *
 * $Id$
 */
class UserTokenModel extends Model {
	
	/**
	 *
	 * @var DBF_UserToken
	 */
	public $F;
	
	/**
	 * 用户登录口令Model类
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->UserToken;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
}
?>