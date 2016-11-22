<?php
//@formatter:off
class DBFTestCreater
{
	private $_dispPrefix = false;
	public function getFields()
	{
		$Define = array('_pk', '_autoinc');
		$ClassVars = get_class_vars(get_class($this));
		$Result = array();
		foreach($ClassVars as $Key => $Val)
		{
			if($Key[0] != '_') { $Result[] = $Val; }
			else { if(in_array($Key, $Define)) { $Result[$Key] = $Val; } }
		}
		return $Result;
	}
	
	public function getMappedFields()
	{
		$ClassVars = get_class_vars(get_class($this));
		$Result = array();
		foreach($ClassVars as $Key => $Val)
		{ if($Key[0] != '_') { $Result[strtolower($Key)] = $Val; } }
		return $Result;
	}
	
	public function dispPrefix($isDisp = false)
	{
		if($this->_dispPrefix == $isDisp ) { return; }
		$this->_dispPrefix = $isDisp;
		
		$Define = array('_pk');
		$ClassVars = get_class_vars(get_class($this));
		$Result = array();
		foreach($ClassVars as $Key => $Val)
		{
			if($Key[0] == '_' && !in_array($Key, $Define)) { continue; }
			if(!$isDisp)
			{ $Val = preg_replace('/$' . $ClassVars['_Table'] . '[.]/', '', $Val); }
			else { $Val = $ClassVars['_Table'] . '.' . $Val; }
			
			$this->{$Key} = $Val;
		}
	}
}

class DBF_Users1 extends DBFTestCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_users';
	public $ID = 'u_id';
	public $RealName = 'u_dispname';
	public $FirstName = 'u_firstname';
	public $SecondName = 'u_secondname';
	public $Pass = 'u_pass';
	public $EMail = 'u_email';
	public $Avatar = 'u_avatar';
	public $Url = 'u_url';
	public $DispName = 'u_dispname';
	public $Type = 'u_type';
	public $Title = 'u_title';
	public $CreateDate = 'u_createdate';
	public $LastLogin = 'u_lastlogin';
	public $Status = 'u_status';
	public $Permission = 'u_permission';
	public $LastIP = 'u_lastip';
	public $Vcoin = 'u_vcoin';
	public $Vcoin_av = 'u_vcoin_av';
	public $Rcoin = 'u_rcoin';
	public $Rcoin_av = 'u_rcoin_av';
	public $_pk = 'u_id';
	public $_autoinc = true;
}

class DBF_Users2 extends DBFTestCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_users';
	public $ID = 'u_id';
	//public $Name = 'u_name';
	public $RealName = 'u_dispname';
	public $FirstName = 'u_firstname';
	public $SecondName = 'u_secondname';
	public $Pass = 'u_pass';
	public $EMail = 'u_email';
	public $Avatar = 'u_avatar';
	public $Url = 'u_url';
	public $DispName = 'u_dispname';
	public $Type = 'u_type';
	public $Title = 'u_title';
	public $CreateDate = 'u_createdate';
	public $LastLogin = 'u_lastlogin';
	public $Status = 'u_status';
	public $Permission = 'u_permission';
	public $LastIP = 'u_lastip';
	public $Vcoin = 'u_vcoin';
	public $Vcoin_av = 'u_vcoin_av';
	public $Rcoin = 'u_rcoin';
	public $Rcoin_av = 'u_rcoin_av';
	public $_pk = 'u_id';
	public $_autoinc = true;
}

class DBFTest
{
	/**
	 * @var DBF_Users1
	 */
	public $Users1;
	
	/**
	 * @var DBF_Users2
	 */
	public $Users2;
	
	function __construct()
	{
		$this->Users1 = new DBF_Users1;
		$this->Users2 = new DBF_Users2;
	}       
}
//@formatter: on
?>