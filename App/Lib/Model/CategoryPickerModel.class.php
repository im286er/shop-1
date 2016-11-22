<?php

class CategoryPickerModel extends Model
{
	// @formatter:off
	/**
	 * @var DBF
	 */
	protected $DBF;
	/**
	 * @var DBF_ProductCategory
	 */
	public $F;
	
	public $IsLoaded = false;
	
	private $Category;
	private $ChildIndex = array();

	public function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->ProductCategory;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
		$this->InitCategory();
	}
	
	public function InitCategory()
	{
		//if($this->ReadCateFromMem()) { $this->IsLoaded = true; return true; }
		$this->Category = $this->order("`pc_parentid` ASC, `pc_dispweight` ASC, `pc_id` ASC")->select();
		if($this->Category === false) { $this->IsLoaded = false; return false; }
		
		$this->Category = $this->conventPKtoArrayKey($this->Category, 'pc_id');
		foreach($this->Category as $CID => $Cate)
		{
			foreach($this->Category as $PartentCID => $PartentCate)
			{
				if($CID == $PartentCate[$this->DBF->ProductCategory->ParentID])
				{
					if(!isset($this->ChildIndex[$CID]))
					{ $this->ChildIndex[$CID] = array(); }
					$this->ChildIndex[$CID][] = $PartentCate[$this->DBF->ProductCategory->ID];
				}
			}
		}
		//$this->WriteCateToMem();
		$this->IsLoaded = true; return true;
	}
	
	private function ReadCateFromMem()
	{
		$MemC = new Memcache();
		$Category = $MemC->get('mem_Category');
		if(!$Category) { return false; }
		$Category_ChildIndex = $MemC->get('mem_Category_ChildIndex');
		if(!$Category_ChildIndex) { return false; }
		$this->Category = unserialize($Category);
		$this->ChildIndex = unserialize($Category_ChildIndex);
		return true;
	}
	
	private function WriteCateToMem()
	{
		$MemC = new Memcache();
		if(!$MemC->set('mem_Category', serialize($this->Category)))
		{ return false; }
		if(!$MemC->set('mem_Category_ChildIndex', serialize($this->ChildIndex)))
		{ return false; }
		return true;
	}
	
	public function getCategoryByID($CID)	{ return $this->Category[$CID]; }
	
	public function getPartentByID($CID)
	{
		if($CID == 0) { return false; }
		$Cate = $this->getCategoryByID($CID);
		if(!$Cate) { return null; }
		return $this->getCategoryByID($Cate[$this->DBF->ProductCategory->ID]);
	}
	
	public function getChildList($PID, $Level = 99)
	{
		$Result = $this->Category[$PID];
		if(!$Result) { return false; }
		if(isset($this->ChildIndex[$PID]))
		{
			if($Level > 0)
			{
				$Result['Child'] = array();
				foreach($this->ChildIndex[$PID] as $ChildID)
				{ $Result['Child'][$ChildID] = $this->getChildList($ChildID, $Level - 1); }
			}
		}
		return $Result;
	}
	
	public function getChildIDList($PID, $IncludePartent = true)
	{
		$Result = array();
		if($IncludePartent) { $Result[] = $PID; }
		$Cate = $this->Category[$PID];
		if(!$Cate) {return false;}
	
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
		$Cate = $this->getCategoryByID($CID);
		if(!$Cate) { return null; }
		
		$Result = $Cate;
		do
		{
			$PartentCate = $this->getCategoryByID($Result[$this->DBF->ProductCategory->ParentID]);
			$PartentCate['Child'] = $Result;
			$Result = $PartentCate;
			$Level--;
		} while($Result[$this->DBF->ProductCategory->ParentID] != 0 && $Level>0);
		return $Result;
	}

	public function getWhereORByIDList($IDList)
	{
		$Where = "`" . $this->DBF->ProductCategory->Table . "`.`" .
				 $this->DBF->ProductCategory->ID . "` = '";
		$Where .= implode("' OR " . $Where, $IDList);
		return $Where . "'";
	}
	
	public function getWhereINByIDList($IDList)
	{
		$Where = "`" . $this->DBF->ProductCategory->Table . "`.`" .
				$this->DBF->ProductCategory->ID . "` IN('";
		$Where .= implode("','", $IDList);
		return $Where . "')";
	}
	
	public function getOptionArray($Type=1, $ExcludeID = null)
	{
		$Cate = $this->getChildList($Type);
		{  $Cate = $this->callRemoveItemsByID($Cate, $ExcludeID); }
		return $this->callOptionArray($Cate, array(), 0);
	}
	
	private function callRemoveItemsByID($Category, $ExcludeID)
	{
		if(!is_array($ExcludeID))
		{
			if(isset($Category[$ExcludeID]))
			{ unset($Category[$ExcludeID]); }
		}
		else
		{
			foreach($ExcludeID as $ID) 
			{
				if(isset($Category[$ID]))
				{ unset($Category[$ID]); } 
			}
		}
		if(isset($Category['Child']))
		{ $Category['Child'] = $this->callRemoveItemsByID($Category['Child'], $ExcludeID); }
		return $Category;
	}
	
	private function callOptionArray($Category, $Result, $Level)
	{
		$Span = ''; 
		for ($i=0; $i<$Level; $i++)	{ $Span .= '--'; }
		$Level++;
		foreach ($Category['Child'] as $Cate)
		{
			$Result[$Cate[$this->F->ID]] = $Span . $Cate[$this->F->Name];
			$Result = $this->callOptionArray($Cate, $Result, $Level);
		}
		return $Result;
	}
	// @formatter:on
	
	// @formatter:off
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
	
}