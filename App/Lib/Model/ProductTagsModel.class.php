<?php
class ProductTagsModel extends Model {
	/**
	 * @var DBF
	 */
	protected $DBF;
	/**
	 * @var DBF_ProductTags
	 */
	public $F;

	protected $_auto = array (array('pt_count', '1'));
	
	function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->ProductTags;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}
	
	
	public function addTag($tag) {
		$res = $this->getBypt_name ( $tag );
		if (is_array ( $res )) { // 有返回结果
			$this->where ( 'pt_id=' . $this->pt_id )->setInc ( 'pt_count', 1 );
			return $this->pt_id;
		} else {
			$this->create ();
			$this->pt_name = $tag;
			return $this->add ();
		}
	}
	
	public function addTagsArray($TagsArray)
	{
		$INStr = "'" . implode("','", $TagsArray) . "'";
		$DBTags = $this->where($this->F->Name . ' IN (' . $INStr . ')')->select();
		if($DBTags === false) { return false; }
		$DBTags = $this->conventPKtoArrayKey($DBTags, $this->F->Name);
		$Result = array();
		foreach ($TagsArray as $Tags)
		{
			if(trim($Tags) == '') { continue; }
			if(array_key_exists($Tags, $DBTags))
			{
				$this->where('pt_id=' . $DBTags[$Tags][$this->F->ID])->setInc ($this->F->Count, 1);
				$Result[$Tags] = $DBTags[$Tags][$this->F->ID];
			}
			else
			{
				$this->{$this->F->Name} = $Tags;
				$this->{$this->F->Count} = 1;
				$TID = $this->add();
				if(!$TID) { return false; }
				$Result[$Tags] = $TID;
			}
		}
		return $Result;
	}
	
	public function changTagsCount($TagsArray, $Inc)
	{
		if(!$TagsArray) { return true; }
		$INStr = "'" . implode("','", $TagsArray) . "'";
		$DBTags = $this->where($this->F->Name . ' IN (' . $INStr . ')')->select();
		if($DBTags === false) { return false; }
		$TagsID = array();
		foreach ($DBTags as $Tag) { $TagsID[] = $Tag[$this->F->ID]; }
		$this->where($this->F->ID . ' IN (' . implode(',', $TagsID) . ')');
		return $this->setInc($this->F->Count, $Inc);
	}
	
	public function getTages($Top = 100)
	{
		$DBF = new DBF();
		return $this->limit($Top)->order($DBF->ProductTags->Count.' DESC')->select();
	}
	
	public function getTagsByProduct($PID)
	{
		$PTI = $this->DBF->ProductTagsIndex;
		$PTI_PID = $PTI->_Table . '.' . $PTI->ProductID;
		$PTI_TID = $PTI->_Table . '.' . $PTI->TagsID;
		$PT_ID = $this->F->_Table . '.' . $this->F->ID;
		return $this->join($PTI->_Table . " ON " . $PTI_TID . '=' . $PT_ID)
				->where($PTI_PID . "='" . $PID . "'")->select();
	}
	
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

/*
 * 获得热点tags
 */
    public function getHotTags(){
       $result=M('product_tags')->field("pt_id,pt_name")->where("pt_ishot=1")->select();
        return $result;
    }
}
?>