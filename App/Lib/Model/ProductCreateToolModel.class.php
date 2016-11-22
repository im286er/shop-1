<?php
class ProductCreateToolModel extends Model {
	/**
	 * @var DBF
	 */
	protected $DBF;
	/**
	 * @var DBF_ProductCreateTool
	 */
	public $F;
	
	public $CreateTool = null;
	public $CTIModel = null;
	public $uptype =0;
	
	function __construct($uptype=0)
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->ProductCreateTool;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
		$this->uptype=$uptype;
		
		$where=$this->uptype?"pct_isprinter=1":"1=1";//判断是否为3d打印的创作工具
		$this->CreateTool = $this->where($where)->select();
		$this->CreateTool = $this->conventPKtoArrayKey($this->CreateTool, $this->F->ID);
		$this->CTIModel = new ProductCreateToolIndexModel();
	}
	
	/**
	 * 获取创作工具数据
	 * 
	 * @return boolean|string
	 */
	public function getCreateToolJson($uptype=0)
	{
	//var_dump($uptype);
		//var_dump($this->uptype);
	//var_dump($this->CreateTool);
		if($this->CreateTool === false) { return false; }
		$JsonStr = '';
		foreach ($this->CreateTool as $CT)
		{
			$CT_ID = $CT[$this->F->ID];
			if($CT[$this->F->Ext]){
				$CT_Name = $CT[$this->F->Name]." (".$CT[$this->F->Ext].")";
			}else{
				$CT_Name = $CT[$this->F->Name];	
			}
			$CT_HasSubType = $CT[$this->F->HasSubType] ? 'true' : 'false';
			$CT_CateID = $CT[$this->F->PCateID];
			$JsonStr .= '"' . $CT_ID . '":{"id":' . $CT_ID . ',"name":"' . $CT_Name .
						 '","hasSubType":' . $CT_HasSubType . ',"CateID":' . $CT_CateID . '},';
		}
		if(strlen($JsonStr) > 0) { $JsonStr = substr($JsonStr, 0, strlen($JsonStr) - 1); }
		return '{' . $JsonStr . '}';
	}
	
	public function getPrime($CreateToolID)
	{
		if($this->CreateTool === false) { return false; }
		$Prime = $this->CreateTool[$CreateToolID][$this->F->Prime];
		return $Prime ? $Prime : 1;
	}
	
	public function isRootTool($ToolID)
	{
		if($this->CreateTool[$ToolID] === false) { return null; }
		$Tool = $this->CreateTool[$ToolID];
		if(!$Tool) { return false; }
		return $Tool[$this->F->PCateID] != 0 ? true : false;
	}
	
	public function isSubTool($PartentID, $ChildID)
	{
		$Index = $this->CTIModel->CreateToolIndex;
		if($Index === false) { return null; }
		return in_array($ChildID, $Index[$PartentID]);
	}
	
	public function getCreateToolCombo($pc_id = 1, $location = 0, $IsAddRoot = true) {
		$re = '';
		$num = 0;
		
		$where = 'pc_id=' . $pc_id;
		$num = $this->where ( $where )->count ();
		if ($num) {
			$infobit_ = $this->where ( $where )->select ();
			$re = $IsAddRoot ? "<option value='0'>" . L ( 'root_cate' ) . "</option>" : '';
			foreach ( $infobit_ as $key => $val ) {
				//if ($val ['pct_prime'] == $location) {
				if ($val ['pct_id'] == $location) {
					//$re .= "<option value='" . $val ['pct_prime'] . "' selected>" . $val ['pct_name'] . " (" . $val ['pct_ext'] . ")</option>";
					$re .= "<option value='" . $val ['pct_id'] . "' selected>" . $val ['pct_name'] . " (" . $val ['pct_ext'] . ")</option>";
				} else {
					//$re .= "<option value='" . $val ['pct_prime'] . "'>" . $val ['pct_name'] . " (" . $val ['pct_ext'] . ")</option>";
					$re .= "<option value='" . $val ['pct_id'] . "'>" . $val ['pct_name'] . " (" . $val ['pct_ext'] . ")</option>";
				}
			}
		} else {
			$re = $IsAddRoot ? "<option value='0'>" . L ( 'root_cate' ) . "</option>" : '';
		}
		return $re;
	}
	
	public function getExtraRenderer() { return $this->where('pct_hassubtype=0')->select (); }
	
	private function conventPKtoArrayKey($Array, $PK)
	{
		$Result = array();
		foreach($Array as $Key => $Val)
		{
			if(!isset($Result[$Val[$PK]]))
			{ $Result[$Val[$PK]] = array(); }
			$Result[$Val[$PK]] = $Val;
		}
		return $Result;
	}
	
	//old
	public function getMapper() {
		$res = array ();
		$list = $this->where ( '1=1' )->select ();
		foreach ( $list as $key => $val ) {	$res [$val ['pct_id']] = $val; }
		return $res;
	}
}
?>