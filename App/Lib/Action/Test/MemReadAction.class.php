<?php

class MemReadAction extends Action
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
		$this->memC->addserver($this->S1);
		$this->MLM = new MemLoaderModel();
	}
	
	public function Test3()
	{
		$a1 = array(3,4,5,6,43,2,3);
		$a2 = array(3,4,5);
		$a3 = array(4,4,5,6);
		
		var_dump(array_intersect($a1, $a2));
		print_r('<br/>');
		var_dump(array_intersect($a2, $a3));
		print_r('<br/>');print_r('<br/>');
		
		$aa = $a1;
		
		$aa[] = 23;
		$aa[] = 25;
		var_dump($a1);
		print_r('<br/>');
		var_dump($aa);
	}
	
	public function Test2()
	{
		load("@.MemReadProduct");
		$MRP = new MemReadProduct();
		$stime = microtime(1);
		$SearchInfo = array(
				'tags' => '床', 
				'category' => 1, 
				'istexture' => 1, 
				'isuvlayout' => 1, 
				'page' => 20
		);
		$MRP->SearchInfo = $SearchInfo;
		$Result = $MRP->getResult();
		$etime = microtime(1);
		var_dump($MRP->TotalCount);
		print_r('<br/>'); print_r('<br/>');
		var_dump(count($Result));
		print_r('<br/>');
		print_r('STime: ' . $stime);
		print_r('<br/>');
		print_r('ETime: ' . $etime);
		print_r('<br/>');
		print_r('Use Time: ' . round ( ($etime - $stime), 4 ));
		print_r('<br/>');print_r('<br/>');
		
		$stime = microtime(1);
		$MRP->searchBySearchInfo();
		$etime = microtime(1);
		print_r('searchBySearchInfo Use Time: ' . round ( ($etime - $stime), 4 ));
		print_r('<br/>');
		$stime = microtime(1);
		$MRP->searchResult();
		$etime = microtime(1);
		print_r('searchResult Use Time: ' . round ( ($etime - $stime), 4 ));
	}
	
	public function Test()
	{
		$stime = microtime(1);
		$Cate = $this->analyzeCate(1001);
		//$Creater = $this->analyzeCreater(11);
		$Filters = $this->Filters(array('istexture'));
		//$Tags = $this->analyzeTags('桌子');
		
		$result = array_intersect($Cate,$Filters);
		$result2 = $this->OrderBy($result, 
								array('p_dispweight' => SORT_DESC, 'p_createdate' => SORT_DESC));
		$etime2 = microtime(1);
		$result3 = $this->OrderBy($result, 
								array('p_dispweight' => SORT_DESC, 'p_lastupdate' => SORT_DESC));
		$result4 = $this->OrderBy($result, 
								array('p_dispweight' => SORT_DESC, 'p_downs' => SORT_DESC));
		$result5 = $this->OrderBy($result, 
								array('p_dispweight' => SORT_DESC, 'p_view' => SORT_DESC));
		//$result = array_intersect( $Tags);
		$etime = microtime(1);
		//var_dump($result);
		//var_dump($Temp = $this->readMem('mem_IsTexture_', 1));
		var_dump(count($result));
		print_r('<br/>');
		var_dump(count($result2));
		print_r('<br/>');
		
		/*
		for($i = 0; $i < count($result2); $i++)
		{
			print_r($result2[$i]['p_id']);
			print_r(' : ');
			print_r($result3[$i]['p_id']);
			print_r(' : ');
			print_r($result4[$i]['p_id']);
			print_r(' : ');
			print_r($result5[$i]['p_id']);
			print_r('<br/>');
		}
		*/
		print_r('<br/>');
		print_r('STime: ' . $stime);
		print_r('<br/>');
		print_r('ETime: ' . $etime);
		print_r('<br/>');
		print_r('ETime2: ' . $etime2);
		print_r('<br/>');
		print_r('Use Time: ' . round ( ($etime - $stime), 4 ));
		print_r('<br/>');
		print_r('Use Time: ' . round ( ($etime2 - $stime), 4 ));
	}
	
	public function analyzeCate($CateID)
	{
		$Cate1 = $this->readMem('mem_Cate1_', $CateID);
		$Cate2 = $this->readMem('mem_Cate2_', $CateID);
		if(!$Cate1 || !$Cate2) { return false; }
		$Cate = array_unique(array_merge($Cate1, $Cate2));
		return $Cate;
	}
	
	public function analyzeCreater($CreaterID)
	{ return $this->readMem('mem_Creater_', $CreaterID); }
	
	public function Filters($Filters)
	{
		$FilterArray = array(
				'IsTexture', 
				'IsMaterials', 
				'IsAnimation', 
				'IsRigged', 
				'IsUVLayout');
		$FilterResult = array();
		foreach ($FilterArray as $FilterName)
		{
			$LowerName = strtolower($FilterName);
			if(in_array($LowerName, $Filters))
			{
				$Temp = $this->readMem('mem_' . $FilterName . '_', 1);
				if(!$Temp) { return null; }
				$FilterResult[$FilterName] = $Temp;
			}
		}
		foreach ($FilterResult as $Filter)
		{
			if(!isset($Result)) { $Result = $Filter; continue; }
			$Result = array_intersect($Result , $Filter);
		}
		return $Result;
	}
	
	public function analyzeTags($TagsName)
	{
		$TagsID = $this->readMem('mem_Tags_', $TagsName);
		if($TagsID === false) { return false; }
		if(count($TagsID)>0)
		{
			$TagsID = $TagsID[0];
			return $this->readMem('mem_TagsIndex_', $TagsID);
		}
		else { return array(); }
	}
	
	public function OrderBy($IDArray, $OrderBy)
	{
		$MemKey = array();
		foreach ($IDArray as $ID)
		{ $MemKey[] = 'mem_OB_Product_' . $ID; }
		$OrderString = $this->memC->get($MemKey);
		$OrderInfo = array();
		
		foreach($OrderString as $Order)
		{	$OrderInfo[] = unserialize($Order);	}
		
		return $this::sortByMultiCols($OrderInfo, $OrderBy);
	}
	
	private function readMem($PreFix, $IDKey)
	{
		$Result = '';
		$ExceedID = $this->memC->get($PreFix . 'Cut');
		if($ExceedID !== false) {
			$ExceedID = unserialize($ExceedID);
			if(key_exists($IDKey, $ExceedID))
			{
				for($i = 0; $i < $ExceedID[$IDKey]; $i++)
				{
					$Temp = $this->memC->get($PreFix . $IDKey . '_' . $i);
					if(!$Temp) { return false; }
					$Result .= $Temp;
				}
				return explode(',', $Result);
			}
		}
		$Result = $this->memC->get($PreFix . $IDKey);
		if($Result === false) { return false; }
		return explode(',', $Result);
	}
	
	static function sortByMultiCols($rowset, $args)
	{
		$sortArray = array();
		$sortRule = '';
		foreach ($args as $sortField => $sortDir)
		{
			foreach ($rowset as $offset => $row)
			{ $sortArray[$sortField][$offset] = $row[$sortField]; }
			$sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
		}
		if (empty($sortArray) || empty($sortRule)) { return $rowset; }
		eval('array_multisort(' . $sortRule . '$rowset);');
		return $rowset;
	}
}
?>