<?php
require_once 'MemReader.php';
class MemReadProduct extends MemReader
{
	/**
	 * @access public
	 * @var array
	 * @example 查询条件的数组
	 */
	public $SearchInfo = null;
	
	/**
	 * @access public
	 * @var int
	 * @example 查询后会将查询记录的总行数写入该变量
	 */
	public $TotalCount = 0;
	/**
	 * @access private
	 * @var int
	 * @example
	 * 默认分页行数<br/>
	 * 当$this->SearchInfo不包含['count']时使用这个变量获得行数
	 */
	private $DefaultCount = 20;
	
	/**
	 * construct函数，将载入配置文件
	 * @access public
	 * @param array $SearchInfo 初始化查询条件
	 */
	public function __construct($SearchInfo)
	{
		parent::__construct(C('MEM_SERVER'));
		$this->SearchInfo = $SearchInfo;
	}
	
	/**
	 * 根据查询条件返回排序并分页的结果
	 * @access public
	 * @return boolean | array
	 * @example 根据SearchInfo获得查询结果<br/>
	 * 先根据SearchInfo序列化后的Key查询缓存<br/>
	 * 如果没有对应记录，则使用SearchInfo作为查询条件查询记录<br/>
	 * 查询完成后，对记录进行分页和排序后返回结果
	 */
	public function getResult()
	{
		$this->TotalCount = 0;
		$ResultIDArray = $this->searchBySearchInfo();
		//序列化的SI无法找到结果则执行完全查询
		if($ResultIDArray === false)
		{
			$ResultIDArray = $this->searchResult();
			//查询到结果后将根据SI序列化后缓存
			if($ResultIDArray !== false)
			{ $this->saveSearchInfoResult($ResultIDArray); }
		}
		if($ResultIDArray === false) { return false; }
		
		$OrderedIDArray = $this->OrderBy($ResultIDArray);
		$this->TotalCount = count($OrderedIDArray);
		return $this->Paging($ResultIDArray);
	}
	
	/**
	 * 根据SearchInfo检索结果
	 * @return boolean | array
	 */
	public function searchResult()
	{
		//存放零时结果集
		$TempIDArray = array();
		$ResultIDArray = array();
		if($this->checkValue($this->SearchInfo['category']))
		{ $TempIDArray[] = $this->analyzeCate($this->SearchInfo['category']); }
		if($this->checkValue($this->SearchInfo['tags']))
		{ $TempIDArray[] = $this->analyzeTags($this->SearchInfo['tags']); }
		//通用检索器
		$TempIDArray[] = $this->analyzeFilters($this->SearchInfo);
		foreach ($TempIDArray as $TempIDs)
		{
			//任何一个检索结果为空则返回false
			if($TempIDs === false) { return false; }
			if($TempIDs !== null)
			{
				//数组为空则跳出，反之进行交集
				if(count($ResultIDArray) == 0) { return array(); }
				else { $ResultIDArray = array_intersect($ResultIDArray, $TempIDs); }
			}
		}
		return $ResultIDArray;
	}
	
	/**
	 * 根据SearchInfo的序列化获得缓存结果
	 * @return boolean | array
	 */
	public function searchBySearchInfo()
	{
		$SI = $this->SearchInfo;
		//移除非必要条件并排序
		if(isset($SI['count'])) { unset($SI['count']); }
		if(isset($SI['page'])) { unset($SI['page']); }
		if(isset($SI['order'])) { unset($SI['order']); }
		ksort($SI);
		return $this->readMem('mem_SI_', trim(serialize($SI)));
	}
	
	private function analyzeCate($CateID)
	{
		//查询合并后的Cate
		$Cate = $this->readMem('mem_MergedCate_', $CateID);
		return array_unique($Cate);
	}
	
	/**
	 * 通用筛选器
	 * @param array $Filters SearchInfo
	 * @return boolean | null | array
	 */
	private function analyzeFilters($Filters)
	{
		$FilterArray = array(
				'istexture' => 'IsTexture',
				'ismaterials' => 'IsMaterials',
				'isanimation' => 'IsAnimation',
				'isrigged' => 'IsRigged',
				'isuvlayout' => 'IsUVLayout',
				'author' => 'Creater',
				'isar' => 'IsAR',
				'isvr' => 'IsVR',
				'audit' => 'SLabel',
				'ischoice' => 'IsChoice',
				'isformal' => 'IsFormal',
				'format' => 'CreateTool');
		$FilterResult = array();
		foreach ($FilterArray as $FilterKey=>$MemName)
		{
			//判断查询条件是是否包含筛选器
			if(array_key_exists($FilterKey, $Filters))
			{
				$Temp = $this->readMem('mem_' . $MemName . '_', $Filters[$FilterKey]);
				if($Temp === false) { return false; }
				$FilterResult[$MemName] = $Temp;
			}
		}
		//不包含任何筛选器返回null
		if(count($FilterResult) === 0) { return null; }
		foreach ($FilterResult as $Filter)
		{
			//数组为空返回空数组，反之进行交集
			if(!isset($Result)) { return array(); }
			$Result = array_intersect($Result , $Filter);
		}
		return $Result;
	}

	private function analyzeTags($TagsName)
	{
		//根据关键字获得ID
		$TagsID = $this->readMem('mem_Tags_', $TagsName);
		if($TagsID === false) { return array(); }
		if(count($TagsID)>0)
		{
			$TagsID = $TagsID[0];
			return $this->readMem('mem_TagsIndex_', $TagsID);
		}
		else { return array(); }
	}
	
	private function OrderBy($IDArray)
	{
		$OrderList = array(
				'lastupdate_desc' => array('p_lastupdate' => SORT_DESC), 
				'lastupdate_asc' => array('p_lastupdate' => SORT_ASC), 
				'createdate_desc' => array('p_createdate' => SORT_DESC), 
				'createdate_asc' => array('p_createdate' => SORT_ASC), 
				'view_desc' => array('p_view' => SORT_DESC), 
				'downloads_desc' => array('p_downs' => SORT_DESC), 
				'id_desc' => array('p_id' => SORT_DESC), 
				'id_asc' => array('p_id' => SORT_ASC), 
				'name_desc' => array('p_name' => SORT_DESC), 
				'name_asc' => array('p_name' => SORT_ASC));
		//排序规则存在则初始化,否则使用默认排序
		$OrderBy = array_key_exists($this->SearchInfo['order'], $OrderList) ? $OrderList[$this->SearchInfo['order']] : $OrderList['lastupdate_desc'];
		$OrderBy = array_merge(array('p_dispweight' => SORT_DESC), $OrderBy);
		
		$MemKey = array();
		//根据ID获得排序信息
		foreach ($IDArray as $ID)	{ $MemKey[] = 'mem_OB_Product_' . $ID; }
		$OrderString = $this->memC->get($MemKey);
		$OrderInfo = array();
		foreach($OrderString as $Order) { $OrderInfo[] = unserialize($Order); }
		//返回排序后的结果
		return array_column($this::sortByMultiCols($OrderInfo, $OrderBy), 'p_id');
	}
	
	private function Paging($IDArray)
	{
		$Page = $this->SearchInfo['page'];
		$Count = $this->SearchInfo['count'];
		$Page = $Page >= 0 ? $Page : 0;
		$Count = $Count ? $Count : $this->DefaultCount;
		$Page = $Page * $Count <= $this->TotalCount ? $Page : floor($this->TotalCount / $Count);
		return array_slice($IDArray, $Page * $Count, $Count);
	}
	
	private function saveSearchInfoResult($ResultData)
	{
		$SI = $this->SearchInfo;
		//移除非必要条件并排序
		if(isset($SI['count'])) { unset($SI['count']); }
		if(isset($SI['page'])) { unset($SI['page']); }
		if(isset($SI['order'])) { unset($SI['order']); }
		ksort($SI);
		require_once 'MemWriter.php';
		$MW = new MemWriter(C('MEM_SERVER'));
		return $MW->saveArray2Mem(array(trim(serialize($SI))=>$ResultData), 'mem_SI_');
	}
	
	private function checkValue($Value)
	{	return (isset($Value) && !empty($Value)) || ($Value === 0 || $Value === false || $Value === '0'); }
}

?>