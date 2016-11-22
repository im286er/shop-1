<?php
/**
 * Socket通讯方法类
 *
 * @author miaomin 
 * Jul 2, 2014 7:21:10 PM
 *
 * $Id$
 */
class Nsocket {
	
	// address
	private $_address = '';
	
	// port
	private $_port = '';
	
	// error_code
	private $_errorCode = '';
	
	// error_message
	private $_errorMessage = '';
	
	/**
	 * Socket通讯方法类
	 *
	 * @param string $address        	
	 * @param int $port        	
	 */
	public function __construct($address, $port = 0) {
		if (filter_var ( $address, FILTER_VALIDATE_IP )) {
			$this->_address = $address;
		} else {
			die ( 'Not validation socket address!' );
		}
		
		if (( int ) $port) {
			$this->_port = $port;
		}
	}
	
	/**
	 * Socket Create
	 *
	 * @return mixed
	 */
	private function _create() {
		$socket = socket_create ( AF_INET, SOCK_STREAM, 0 );
		if ($socket < 0) {
			$this->_errorCode = socket_last_error ();
			$this->_errorMessage = socket_strerror ( $this->_errorCode );
			return false;
		}
		return $socket;
	}
	
	/**
	 * Socket Connect
	 *
	 * @param resource $socket_handle        	
	 * @param string $address        	
	 * @param int $port        	
	 *
	 * @return boolean
	 */
	private function _connect($socket_handle, $address, $port = 0) {
		$result = @socket_connect ( $socket_handle, $address, $port );
		
		if ($result == false) {
			$this->_errorCode = socket_last_error ();
			$this->_errorMessage = socket_strerror ( $this->_errorCode );
			return false;
		}
		
		return $result;
	}
	
	/**
	 * Socket Close
	 *
	 * @param resource $socket_handle        	
	 */
	private function _close($socket_handle) {
		socket_close ( $socket_handle );
	}
	
	/**
	 * Socket send message
	 *
	 * @param string $message        	
	 * @param int $back
	 *        	是否返回消息,默认为0不返回
	 * @return mixed
	 */
	public function send($message, $back = 0) {
		$socket_handle = $this->_create ();
		if (! $socket_handle) {
			return false;
		}
		if (! $this->_connect ( $socket_handle, $this->_address, $this->_port )) {
			return false;
		}
		socket_write ( $socket_handle, $message, strlen ( $message ) );
		if ($back != 0) {
			$input = socket_read ( $socket_handle, 1024 );
			$this->_close ( $socket_handle );
			return $input;
		} else {
			$this->_close ( $socket_handle );
			return true;
		}
	}
	
	/**
	 * Get Socket communication error code
	 */
	public function getErrorCode() {
		return $this->_errorCode;
	}
	
	/**
	 * Get Socket communication error message
	 */
	public function getErrorMessage() {
		return $this->_errorMessage;
	}
}
?>