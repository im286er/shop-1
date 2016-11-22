<?php

class MemLoaderModel extends Model
{
	/**
	 * @var DBF
	 */
	public $DBF;
	public function __construct()
	{
		$this->DBF = new DBF();
		parent::__construct();
	}
	
	public function getData($ModelName, $Fields)
	{
		$M = M($ModelName,null);
		$StrFields = is_array($Fields) ? implode(',', $Fields) : $Fields;
		$result =  $M->field($StrFields)->select();
		return $result;
	}
}
?>