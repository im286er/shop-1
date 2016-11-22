<?php

class MemLoadAction extends Action
{
	private $S1 = 'localhost';
	//private $S2 = 'M1';
	private $S3 = '192.168.53.101';
	/**
	 * @var Memcache
	 */
	public $memC;
	/**
	 * @var MemLoaderModel
	 */
	private $MLM;
	
	public function __construct()
	{
		ini_set('max_execution_time','600');
		ini_set('memory_limit','5000000000');
		$this->memC = new Memcache();
		$this->memC->addserver($this->S3);
		$this->MLM = new MemLoaderModel();
	}
	
	public function Test()
	{
		//echo "aaa";
		
	}
		
	public function Clear()
	{
		$this->memC->flush();
		print_r('清除完成<br/><br/><br/>');
	}
	
	public function Cate()
	{
		$t1 = microtime(1);
		$F = $this->MLM->DBF->Product;
		$Fields = array(
				'Cate1' => $F->Cate_1,
				'Cate2' => $F->Cate_2,
				'ID' => $F->ID);
		$ProductCate = $this->MLM->getData($F->_Table, $Fields);
		if($ProductCate === false || $ProductCate === null)
		{ echo ' ProductCate: ' . var_dump($ProductCate); return;}
		
		$CPM = new CategoryPickerModel();
		if(!$CPM->IsLoaded) { echo ' ProductCate: ' . var_dump($ProductCate); }
		
		$CategoryIDList = $CPM->getChildIDList(1);
		$MemCate = array(); $MemCate['NotExist'] = array();
		foreach($CategoryIDList as $CID) { $MemCate['Cate_' . $CID] = array(); }
		foreach($ProductCate as $PC)
		{
			if(in_array($PC[$F->Cate_1], $CategoryIDList))
			{ $MemCate['Cate_' . $PC[$F->Cate_1]][] = $PC[$F->ID]; }
			else { $MemCate['NotExist'][] = $PC[$F->Cate_1]; }
			
			if(in_array($PC[$F->Cate_2], $CategoryIDList))
			{ $MemCate['Cate_' . $PC[$F->Cate_2]][] = $PC[$F->ID]; }
			else { $MemCate['NotExist'][] = $PC[$F->Cate_2]; }
		}
		$MemCate['NotExist'] = array_unique($MemCate['NotExist']);
		foreach($CategoryIDList as $CID)
		{ $MemCate['Cate_' . $CID] = array_unique($MemCate['Cate_' . $CID]); }
		
		foreach($CategoryIDList as $CID)
		{
			$ChildIDList = $CPM->getChildIDList($CID);
			$MemCate['MergedCate_' . $CID] = array();
			foreach($ChildIDList as $ChildCID)
			{
				//var_dump($CID); var_dump($ChildCID);
				//print_r('<br/>');
				//print_r($MemCate[$ChildCID]);
				//print_r('<br/>');
				//print_r('<br/>');
				$MemCate['MergedCate_' . $CID] = array_merge($MemCate['MergedCate_' . $CID], $MemCate['Cate_' . $ChildCID]);
			}
		}
		$t2 = microtime(1);
		print_r('LoadCate: ' . round($t2 - $t1, 4));
		print_r('<br/>');print_r('<br/>');print_r('<br/>');
		ob_flush();
		flush();
		$t1 = microtime(1);
		foreach ($MemCate as $Key=>$MC)
		{
			print_r($Key . ' : ');
			$this->saveArray2Mem(array($Key=>$MC), 'mem_');
			print_r('<br/>');
			ob_flush();
			flush();
		}
		$t2 = microtime(1);
		print_r('SaveCate: ' . round($t2 - $t1, 4));
		//print_r($MemCate);
	}
	
	public function Product()
	{
		$F = $this->MLM->DBF->Product;
		$Fields = array(
				'Creater' => $F->Creater, 
				'SLabel' => $F->Slabel, 
				'IsChoice' => $F->IsChoice,
				'IsFormal' => $F->IsFormal,
				'ID' => $F->ID);
		$Product = $this->MLM->getData($F->_Table, $Fields);
		if($Product === false || $Product === null) { echo 'Product: ' . var_dump($Product); return; }
		$this->saveData2Mem($Product, $Fields, $F->ID);
	}
	
	public function ProductModel()
	{
		$F = $this->MLM->DBF->ProductModel;
		$Fields = array(
				'IsTexture' => $F->IsTexture, 
				'IsMaterials' => $F->IsMaterials, 
				'IsAnimation' => $F->IsAnimation, 
				'IsRigged' => $F->IsRigged, 
				'IsUVLayout' => $F->IsUVLayout, 
				'IsAR' => $F->IsAR,
				'IsVR' => $F->IsVR,
				'ID' => $F->ProductID);
		$ProductModel = $this->MLM->getData($F->_Table, $Fields);
		if($ProductModel === false || $ProductModel === null) { echo 'ProductModel: ' . var_dump($ProductModel); return;}
		$this->saveData2Mem($ProductModel, $Fields, $F->ProductID);
	}
	
	public function Tags()
	{
		$F = $this->MLM->DBF->ProductTags;
		$Fields = array('TagsName' => $F->Name, 'TagsID' => $F->ID);
		$Tags = $this->MLM->getData($F->_Table, $Fields);
		if($Tags === false || $Tags === null) { echo 'Tags: ' . var_dump($Tags); return;}
		
		$Result = $this->conventColumn2Key($Tags, $F->Name, $F->ID);
		if($this->saveArray2Mem($Result, 'mem_Tags_'))
		{ print_r('Tags写入完成<br/>'); }
		else{ print_r('Tags写入失败<br/>'); }
	}
	
	public function TagsIndex()
	{
		$F = $this->MLM->DBF->ProductTagsIndex;
		$Fields = array('ProductID' => $F->ProductID, 'TagsIndex' => $F->TagsID);
		$TagsIndex = $this->MLM->getData($F->_Table, $Fields);
		if($TagsIndex === false || $TagsIndex === null) { echo 'Tags: ' . var_dump($TagsIndex); return;}
		$this->saveData2Mem($TagsIndex, $Fields, $F->ProductID);
	}
	
	public function OrderBy()
	{
		$F = $this->MLM->DBF->Product;
		$Fields = array(
				'ProductID' => $F->ID, 
				'OB_CreateDate' => $F->CreateDate, 
				'OB_LastUpdate' => $F->LastUpdate,
				'OB_View' => $F->Views,
				'OB_Downs' => $F->Downs,
				'OB_Dispweight' => $F->Dispweight
		);
		$ProductOrder = $this->MLM->getData($F->_Table, $Fields);
		print_r(count($ProductOrder));
		if($ProductOrder === false || $ProductOrder === null) { echo 'ProductOrder: ' . var_dump($ProductOrder); return;}
		foreach($ProductOrder as $OrderInfo)
		{
			$OrderInfo[$F->CreateDate] = strtotime($OrderInfo[$F->CreateDate]);
			$OrderInfo[$F->LastUpdate] = strtotime($OrderInfo[$F->LastUpdate]);
			if(!$this->memC->set('mem_OB_Product_' . $OrderInfo[$F->ID], serialize($OrderInfo)))
			{ echo '失败'; return; }
		}
		echo '成功!';
	}
	
	public function File()
	{
		$F = $this->MLM->DBF->ProductFile;
		$Fields = array('CreateTool' => $F->CreateTool, 'ID' => $F->ID);
		$File = $this->MLM->getData($F->_Table, $Fields);
		if($File === false || $File === null) { echo 'File: ' . var_dump($File); }
		$this->saveData2Mem($File, $Fields, $F->ID);
	}
	
	private function saveData2Mem($Data, $Fields, $ID)
	{
		foreach($Fields as $Name => $Key)
		{
			if($Key == $ID) { continue ; }
			$ResutlArray = $this->formatterArray($Data, $Key, $ID);
			if($this->saveArray2Mem($ResutlArray, 'mem_' . $Name . '_'))
			{ print_r('mem_' . $Name . '写入完成<br/>'); }
			else { print_r('mem_' . $Name . '写入失败<br/>'); }
		}
	}
	
	private function formatterArray($SourceArray, $SourceKey, $IDKey)
	{
		$ResultArray = array();
		foreach ($SourceArray as $Item)
		{
			if($Item[$SourceKey])
			{
				if(!isset($ResultArray[$Item[$SourceKey]])) { $ResultArray[$Item[$SourceKey]] = array(); }
				$ResultArray[$Item[$SourceKey]][] = $Item[$IDKey];
			}
		}
		return $ResultArray;
	}
	
	private function saveArray2Mem($SourceArray, $PreFix, $MaxLength = 1000000)
	{
		$ExceedID = array();
		foreach ($SourceArray as $ID => $IDArray)
		{
			$IDArrayFromatted = is_array($IDArray) ? implode(',', array_unique($IDArray)) : $IDArray;
			if(strlen($IDArrayFromatted) >= $MaxLength)
			{
				$ExceedID[$ID] = ceil(strlen($IDArrayFromatted) / $MaxLength);
				for($i = 0; $i < $ExceedID[$ID]; $i++)
				{
					$ArrayCut = substr($IDArrayFromatted, $i * $MaxLength, $MaxLength);
					if(!$this->memC->set($PreFix . $ID . '_' . $i, $ArrayCut))
					{ var_dump($PreFix . $ID . '_' . $i); var_dump($ArrayCut); return false; }
					var_dump($PreFix . $ID . '_' . $i);
				}
			}
			else
			{
				if(!$this->memC->set($PreFix . $ID, $IDArrayFromatted))
				{ var_dump($PreFix . $ID); var_dump($IDArrayFromatted); return false; }
			}
		}
		print_r(serialize($ExceedID));
		if(!$this->memC->set($PreFix . 'Cut', serialize($ExceedID)))
		{ var_dump($PreFix . 'Cut'); var_dump(serialize($ExceedID)); return false; }
		return true;
	}
	
	private function conventColumn2Key($SourceArray, $Key, $ValueKey)
	{
		$ResultArray = array();
		foreach ($SourceArray as $Item)
		{ if($Item[$Key]) { $ResultArray[$Item[$Key]] = $Item[$ValueKey]; } }
		return $ResultArray;
	}
}
?>