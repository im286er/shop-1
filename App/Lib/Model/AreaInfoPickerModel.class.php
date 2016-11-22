<?php
class AreaInfoPickerModel extends PickerModel
{
	/**
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 * @var DBF_AreaInfo
	 */
	public $F;
	
	public $Picker;
	public $ChildIndex = array();
	
	public function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->AreaInfo;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
		$this->IDKey = $this->F->ID;
		$this->PartentKey = $this->F->ParentID;
		$this->DisplayKey = $this->F->Name;
		$this->Init();
	}
	
	public function getJsonAreaInfo()
	{
		$Map = array(
				$this->F->ID=>'id',
				$this->F->Name=>'name',
				$this->F->ParentID=>'pid'
		);
		return $this->getJsonItems($Map, $this->F->ID);
	}
	
	public function getarea($a,$b,$c,$sep=' '){
		$Result = '未知';
		if ($a){
			$Result = $this->getItemNameByID($a);
		}
		if ($b){
			$Result .=  $sep . $this->getItemNameByID($b);
		}
		if ($c){
			$Result .=  $sep . $this->getItemNameByID($c);
		}
		
		return $Result;
	}
	
	public function getAllAreaInfo(){
		return $this->Picker;
	}
	
	
	
	

}