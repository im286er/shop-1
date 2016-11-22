<?php

class AccessAction extends Action
{

	public function index()
	{
		session_start ();
		if($this->isPost())
		{
			if(isset($_POST['accesscode']) && $_POST['accesscode'] == C('ACCESSCODE'))
			{
				$_SESSION['AccessCode'] = C('ACCESSCODE');
				redirect(WEBROOT_PATH . '/index.php');
			}
			else { $this->assign("Error",true); }
		}
		$this->display();
	}
}
?>