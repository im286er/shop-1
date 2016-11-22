<?php
class MemReader
{
	/**
	 * @var Memcache
	 */
	protected $memC;
	/**
	 * @var MemLoaderModel
	 */
	protected  $MLM;
	
	protected $MenServer = null;
	
	public function __construct($Server = null)
	{
		//修改脚本时间限制和缓存限制
		ini_set('max_execution_time', '600');
		ini_set('memory_limit', '5000000000'); 
		//修改memcache配置方式
		ini_set('memcache.hash_function', 'crc32');
		ini_set('memcache.hash_strategy', 'consistent');
		$this->MenServer = $Server;
		$this->memC = new Memcache();
		$this->MLM = new MemLoaderModel();
		$this->addServer();
	}
	
	/**
	 * 
	 * @param string $PreFix 前缀
	 * @param string $IDKey 关键字
	 * @return boolean|array
	 */
	protected function readMem($PreFix, $IDKey)
	{
		$Result = '';
		//获取截断信息
		$ExceedID = $this->memC->get($PreFix . 'Cut');
		if($ExceedID !== false)
		{
			$ExceedID = unserialize($ExceedID);
			//如果有Cut信息则分段读取并合并
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
		if($Result === false)	{ return false; }
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
	
	private function addServer()
	{
		if(!isset($this->MenServer)) { return false; }
		if(is_array($this->MenServer))
		{
			foreach ($this->MenServer as $Server)
			{ $this->memC->addserver($Server['server']); }
		}
		else { $this->memC->addserver($this->MenServer); }
		return true;
	}
}
?>