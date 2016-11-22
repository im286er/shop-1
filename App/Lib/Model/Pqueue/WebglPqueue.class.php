<?php
require 'AbstractPqueue.class.php';
/**
 * WebglPqueue类
 *
 * @author miaomin
 *         Apr 23, 2014 11:38:39 AM
 *        
 *         $Id$
 */
class WebglPqueue extends AbstractPqueue {
	
	/**
	 * URL
	 */
	protected $url = 'http://192.168.20.166/php-resque/webgl/pqueue.php';
	
	/**
	 * CheckURL
	 */
	protected static $checkUrl = 'http://192.168.20.166/php-resque/webgl/check_status.php';
	
	/**
	 * WebglPqueue类
	 */
	public function __construct() {
		$this->setQueName ();
		$this->setJobName ();
	}
	
	/*
	 * setQueName
	 */
	protected function setQueName() {
		$this->queName = 'webgl';
	}
	
	/*
	 * setJobName
	 */
	protected function setJobName() {
		$this->jobName = 'PHP_Job';
	}
}
?>