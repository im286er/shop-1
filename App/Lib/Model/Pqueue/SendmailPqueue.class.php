<?php
/**
 * SendmailPqueue类
 *
 * @author miaomin 
 * Feb 21, 2014 3:18:53 PM
 *
 * $Id$
 */
require 'AbstractPqueue.class.php';
class SendmailPqueue extends AbstractPqueue {
	
	/**
	 * SendmailPqueue类
	 */
	public function __construct() {
		$this->setQueName ();
		$this->setJobName ();
	}
	
	/*
	 * setQueName
	 */
	protected function setQueName() {
		$this->queName = 'sendmail';
	}
	
	/*
	 * setJobName
	 */
	protected function setJobName() {
		$this->jobName = 'PHP_Sendmail';
	}
}
?>