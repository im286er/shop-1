<?php
class ProductCreateToolIndexModel extends Model {
	/**
	 * @var DBF
	 */
	protected $DBF;
	/**
	 * @var DBF_ProductCreateToolIndex
	 */
	public $F;
	
	public $CreateToolIndex = null;
	
	function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->ProductCreateToolIndex;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
		$this->CreateToolIndex = $this->getCreateToolIndex();
	}
	
	public function getCreateToolIndex()
	{

		$CreateToolIndex = $this->select();
		if($CreateToolIndex === false) { return false; }
		$CTIArray = array();
		foreach ($CreateToolIndex as $CTI)
		{
			$PCT_ID = $CTI[$this->F->PCTID];
			$PCT_SubID = $CTI[$this->F->PCTSubID];
			if(!isset($CTIArray[$PCT_ID])) { $CTIArray[$PCT_ID] = array(); }
			if(!in_array($PCT_SubID, $CTIArray[$PCT_ID]))	{ $CTIArray[$PCT_ID][] = $PCT_SubID; }
		}
		return $CTIArray;
	}
	
	public function getCreateToolIndexJson()
	{
		if($this->CreateToolIndex === false) { return false; }
		$JsonStr = '';
		foreach ($this->CreateToolIndex as $PCT_ID => $PCT_SubIDList)
		{
			$StrSubID  = '';
			foreach ($PCT_SubIDList as $SubID) { $StrSubID .= $SubID . ','; }
			if(strlen($StrSubID) > 0) { $StrSubID = substr($StrSubID, 0, strlen($StrSubID) - 1); }
			$JsonStr .= '"' . $PCT_ID . '":[' . $StrSubID . '],';
		}
		if(strlen($JsonStr) > 0) { $JsonStr = substr($JsonStr, 0, strlen($JsonStr) - 1); }
		return '{' . $JsonStr . '}';
	}
	
	//old
	public function getExtraRenderer() {
		$res = array ();
		$res = $this->query ( "SELECT pcti.*,pct.pct_name FROM tdf_product_createtool_index pcti LEFT JOIN tdf_product_createtool pct ON (pct.pct_id = pcti.pct_subid)" );
		return $res;
	}
}
?>