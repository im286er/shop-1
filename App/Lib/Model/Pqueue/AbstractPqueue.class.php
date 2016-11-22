<?php
/**
 * Pqueue抽象类
 *
 * @author miaomin 
 * Feb 21, 2014 3:09:01 PM
 *
 * $Id$
 */
import ( 'Common.Ncurl', APP_PATH, '.php' );
abstract class AbstractPqueue {
	protected $url;
	protected static $checkUrl;
	protected $queName;
	protected $jobName;
	protected $args;
	abstract protected function setQueName();
	abstract protected function setJobName();
	
	/**
	 * 设置Queue地址
	 *
	 * @param string $url        	
	 */
	public function setQueUrl($Url) {
		if (filter_var ( $Url, FILTER_VALIDATE_URL )) {
			$this->url = $Url;
		} else {
			die ( 'Not validation url!' );
		}
	}
	
	/**
	 * setArgs
	 *
	 * @param array $args        	
	 */
	public function setArgs(array $args) {
		$this->args = $args;
	}
	
	/**
	 * add
	 */
	public function add($return = 1) {
		$postArray = array (
				'que' => $this->queName,
				'job' => $this->jobName,
				'args' => $this->args 
		);
		$nc = new Ncurl ( $this->url );
		return $nc->curlPost ( $postArray, $return );
	}
	
	/**
	 * stat
	 *
	 * @param string $jobId        	
	 */
	static public function stat($jobId) {
		$postArray = array (
				'jobId' => $jobId 
		);
		$nc = new Ncurl ( self::$checkUrl );
		return $nc->curlPost ( $postArray, 1 );
	}
}
?>