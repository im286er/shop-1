<?php
class PickerModel extends Model
{
	/**
	 * @var DBF
	 */
	protected $DBF;
	
	public $F;
	
	public $IsLoaded = false;
	
	protected $IDKey;
	protected $PartentKey;
	protected $DisplayKey;
	protected $Picker;
	protected $OrderCol;
	protected $ChildIndex = array();
	
	public function __construct() { parent::__construct(); }
	
	public function Init()
	{
		//if($this->ReadPickerFromMem()) { $this->IsLoaded = true; return true; }
		$this->Picker = $this->order($this->OrderCol)->select();
		if($this->Picker === false) { $this->IsLoaded = false; return false; }
		
		$this->Picker = array_column($this->Picker, null, $this->IDKey);
		foreach($this->Picker as $Item)
		{
			if(!isset($this->ChildIndex[$Item[$this->PartentKey]]))
			{ $this->ChildIndex[$Item[$this->PartentKey]] = array(); }
			$this->ChildIndex[$Item[$this->PartentKey]][] = $Item[$this->IDKey];
		}
		//$this->WritePockerToMem();
		$this->IsLoaded = true; return true;
	}
	
	public function getItemByID($ItemID) { return $this->Picker[$ItemID]; }
	
	public function getItemNameByID($ItemID)
	{
		$Item = $this->Picker[$ItemID];
		if(!$Item) { return $Item; }
		return $Item[$this->DisplayKey];
	}
	
	public function getPartentByID($ItemID)
	{
		if($ItemID == 0) { return false; }
		$Item = $this->getItemByID($ItemID);
		if(!$Item) { return null; }
		return $this->getItemByID($Item[$this->PartentKey]);
	}
	
	public function getChildList($PID, $Level = 99)
	{
		$Result = array();
		$Result = $this->Picker[$PID]; 
		
		if($Result === false && $PID !== 0) { return false; }
		if(isset($this->ChildIndex[$PID]))
		{
			if($Level > 0 && $PID !== 0)
			{
				$Result['Child'] = array();
				foreach($this->ChildIndex[$PID] as $ChildID)
				{ $Result['Child'][$ChildID] = $this->getChildList($ChildID, $Level - 1); }
			}
			else if($Level > 0) {
				foreach($this->ChildIndex[$PID] as $ChildID)
				{ $Result[] = $this->getChildList($ChildID, $Level - 1); }
			}
		}
		return $Result;
	}
	
	public function getChildIDList($PID, $IncludePartent = true)
	{
		$Result = array();
		if($IncludePartent) { $Result[] = $PID; }
		$Item = $this->Picker[$PID];
		if(!$Item) {return false;}
	
		if(isset($this->ChildIndex[$PID]))
		{
			foreach($this->ChildIndex[$PID] as $ChildID)
			{ $Result = array_merge($Result, $this->getChildIDList($ChildID)); }
		}
		return $Result;
	}
	
	public function getPartentList($CID, $Level = 99)
	{
		if($CID == 0) { return false; }
		$Item = $this->getItemByID($CID);
		if(!$Item) { return null; }
	
		$Result = $Item;
		do
		{
			$PartentItem = $this->getItemByID($Result[$this->PartentKey]);
			$PartentItem['Child'] = $Result;
			$Result = $PartentItem;
			$Level--;
		} while($Result[$this->PartentKey] != 0 && $Level>0);
		return $Result;
	}
	
	public function getWhereORByIDList($IDList)
	{
		$Where = "`" . $this->F->_Table . "`.`" . $this->IDKey . "` = '";
		$Where .= implode("' OR " . $Where, $IDList);
		return $Where . "'";
	}
	
	public function getWhereINByIDList($IDList)
	{
		$Where = "`" . $this->F->_Table . "`.`" . $this->IDKey . "` IN('";
		$Where .= implode("','", $IDList);
		return $Where . "')";
	}
	
	public function getJsonItems($Map, $IDKey)
	{
		$Items = $this->Picker;
		$JsonStr = '';
		foreach ($Items as $Item)
		{
			$JsonStr .= '"' . $Item[$IDKey] . '":{';
			foreach ($Map as $Key=>$Disp)
			{ $JsonStr .= '"' . $Disp . '":"' . $Item[$Key] . '",'; }
			if(strlen($JsonStr) > 0) { $JsonStr = substr($JsonStr, 0, strlen($JsonStr) - 1); }
			$JsonStr .= '},';
		}
		if(strlen($JsonStr) > 0) { $JsonStr = substr($JsonStr, 0, strlen($JsonStr) - 1); }
		return '{' . $JsonStr . '}';
	}
	
	public function getJsonChildIndex() { return json_encode($this->ChildIndex); }
	
	public function getOptionArray($RootID = 1, $Level = 99)
	{
		$Items = $this->getChildList($RootID, $Level);
		return $this->callOptionArray($Items, array(), 0);
	}
	
	public function hasChind($ItemID) { return isset($this->ChildIndex[$ItemID]); }
	
	private function callOptionArray($Items, $Result, $Level)
	{
		$Span = '';
		for ($i=0; $i<$Level; $i++)	{ $Span .= '--'; }
		$Level++;
		foreach ($Items['Child'] as $Item)
		{
			$Result[$Item[$this->IDKey]] = $Span . $Item[$this->DisplayKey];
			$Result = $this->callOptionArray($Item, $Result, $Level);
		}
		return $Result;
	}
}
?>