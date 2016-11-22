<?php
class MemWriter
{
	/**
	 * @var Memcache
	 */
	protected $memC;
	
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
		$this->addServer();
	}

	/**
	 * 将检索结果格式化为Key=>Value对应格式
	 * @param Array $SourceArray 源数组
	 * @param string $SourceKey 索引项
	 * @param string $IDKey 对应ID
	 * @return array
	 */
	public function formatterArray($SourceArray, $SourceKey, $IDKey)
	{
		$ResultArray = array();
		foreach($SourceArray as $Item)
		{
			if($Item[$SourceKey])
			{
				if(!isset($ResultArray[$Item[$SourceKey]]))
				{ $ResultArray[$Item[$SourceKey]] = array(); }
				$ResultArray[$Item[$SourceKey]][] = $Item[$IDKey];
			}
		}
		return $ResultArray;
	}

	/**
	 * 将检索结果保存到memcache中
	 * @param array $SourceArray 源数组
	 * @param string $PreFix 前缀
	 * @param int $MaxLength 最大截取长度
	 * @return boolean
	 */
	public function saveArray2Mem($SourceArray, $PreFix, $MaxLength = 1000000)
	{
		$ExceedID = array();
		foreach($SourceArray as $ID => $IDArray)
		{
			$IDArrayFromatted = is_array($IDArray) ? implode(',', array_unique($IDArray)) : $IDArray;
			if(strlen($IDArrayFromatted) >= $MaxLength)
			{
				$ExceedID[$ID] = ceil(strlen($IDArrayFromatted) / $MaxLength);
				for($i = 0; $i < $ExceedID[$ID]; $i++)
				{
					$ArrayCut = substr($IDArrayFromatted, $i * $MaxLength, $MaxLength);
					if(!$this->memC->set($PreFix . $ID . '_' . $i, $ArrayCut)) { return false; }
				}
			}
			else { if(!$this->memC->set($PreFix . $ID, $IDArrayFromatted)) { return false; } }
		}
		if(!$this->memC->set($PreFix . 'Cut', serialize($ExceedID))) { return false; }
		return true;
	}

	private function conventColumn2Key($SourceArray, $Key, $ValueKey)
	{
		$ResultArray = array();
		foreach($SourceArray as $Item)
		{ if($Item[$Key]) { $ResultArray[$Item[$Key]] = $Item[$ValueKey]; } }
		return $ResultArray;
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