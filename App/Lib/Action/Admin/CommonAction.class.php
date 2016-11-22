<?php
class CommonAction extends Action {
	public $DBF;
	function __construct() {
		parent::__construct ();
		$this->DBF = new DBF ();
		if (isset ( $_POST ["sessionid"] )) {
			session_id ( $_POST ['sessionid'] );
			session_start ();
			return true;
		}
		if (!isset ( $_SESSION ['userid'] ) && !isset ($_SESSION ['my_info'] )) {
			redirect ( __APP__ . '/login' );
		}
	}

	
	
}
?>