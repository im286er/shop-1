<?php
/**
 * 搜索条件解析器
 * 
 * @author jzy
 * 
 */
// @formatter:off
class SearchParser
{
	/** 
	 * @var DBF
	 */
	private $DBF;
	public $SearchInfo;
	public $__map;
	/**
	 * value为表单中的空间name
	 * 
	 * @var unknown
	 */
	private $UrlInfoList = array('tags' => 'tags', 'cate' => 'category', 
								'cds' => 'createdate_s', 'cde' => 'createdate_e', 
								'lus' => 'lastupdate_s', 'lue' => 'lastupdate_e', 
								'format' => 'format', 'disp' => 'disp', 'thumb' => 'thumb',
								'count' => 'count' , 'page' => 'page' , 'creater' => 'creater',
								'tools'=>'tools','price'=>'price','isAssembly'=>'isAssembly',
								'used'=>'iscp','isorignal_new'=>'isorignal_new',
								'ispublish_new'=>'ispublish_new','isar_new'=>'isar_new',
								'isprint_new'=>'isprint_new','isadmin_new'=>'isadmin_new','catetype'=>'catetype',
								'begin_id'=>'begin_id','end_id'=>'end_id','type' => 'producttype','tlike' => 'producttitle',
	                            'lprice'=>'low_price','hprice'=>'hi_price','wp'=>'productwp','audit'=>'audit');
 	private $FilterList = array('te' => 'istexture', 'ma' => 'ismaterials', 'am' => 'isanimation',
 								'ri' => 'isrigged', 'uv' => 'isuvlayout', 're' => 'isrendered',
								'fo' => 'isformal', 'ch' => 'ischoice', 'ar' => 'isar','an'=>'noar',
 								'vr' => 'isvr','pu'=>'ispublish','pn'=>'nopublish','ve' => 'isverify', 
 								'rv' => 'isrvfy','fr' => 'isfree','or' => 'isorignal', 'cp'=>'iscp', 
 								'pr' => 'isprint','np'=>'noprint','ia'=>'isadmin','na'=>'noadmin',
 								'fp' => 'isfp','fa'=> 'isfpav');
 	private $OrderList = array(	'lad' => 'lastupdate_desc', 'laa' => 'lastupdate_asc','crd' => 'createdate_desc',
 								'cra' => 'createdate_asc','vid' => 'view_desc', 'dod' => 'downs_desc',
 								'low'=>'price_asc','high'=>'price_desc','idd' => 'id_desc', 'ida' => 'id_asc',
								'nad' => 'name_desc', 'naa' => 'name_asc','sd' => 'score_desc' ,
 								'sa'=>'score_asc','svid' => 'score_view_desc','dwa'=>'dispweight_asc','dwd'=>'dispweight_desc');
	function __construct()
	{
		$this->__map = isset($this->$__map) ? $this->$__map : array();
	}

	function parseSearchInfo($isFront = true)
	{
		$PVC = $this->getSearchInfoSelector($isFront);
		$PVC->verifyAll();
		if($PVC->Error) {	return false; }
		$this->SearchInfo = $this->formatSearchInfo($PVC->ResultArray);
		return true;
	}

	function parseUrlInfo($isFront = true,$isDVS=0)
	{
		$PVC = $this->getUrlInfoSelector($isFront, $isDVS);
		$PVC->verifyAll();
		if($PVC->Error) { return false; }
		$this->SearchInfo = $this->formatUrlInfo($PVC->ResultArray);
		return true;
	}

	
	function getFormattedUrl()
	{
		$UrlInfo = $this->getUrlInfo();
		//exit;
		$ResultArray = array();
		$ResultArray['url'] = '';
		

		foreach($UrlInfo as $Key => $Value)
		{
			if($Key=='page') { continue; }
			$ResultArray[$Key] = '&' . $Key . '=' . urlencode($Value);
			$ResultArray['url'] = isset($ResultArray['url']) ? $ResultArray['url'] . $ResultArray[$Key] : '?' . substr($ResultArray[$Key], 1);
		}
		return $ResultArray;
	}
	
	//获取url新方法，改为参数/参数值得形式
	function getFormattedUrl_new()
	{
		$UrlInfo = $this->getUrlInfo();
		
		//exit;
		$ResultArray = array();
		$ResultArray['url'] = '';
		foreach($UrlInfo as $Key => $Value)
		{
			if($Key=='page') { continue; }
			$ResultArray[$Key] = '-' . $Key . '-' . urlencode($Value);
			$ResultArray['url'] = isset($ResultArray['url']) ? $ResultArray['url'] . $ResultArray[$Key] : '?' . substr($ResultArray[$Key], 1);
		}
		
		return $ResultArray;
	}
	
	function getUrlInfo()
	{
		$UrlInfoList = array_flip($this->UrlInfoList);
		$ResultArray = array();
		foreach($UrlInfoList as $Index => $Key)
		{ if(isset($this->SearchInfo[$Index])) { $ResultArray[$Key] = $this->SearchInfo[$Index]; } }
		
		$FilterList = $this->FilterList;
		foreach($FilterList as $Filter){
		
			// bug修订 
			// miaomin@2014.3.7
			// filter不应该是截取字符操作
			if(isset($this->SearchInfo[$Filter])) {
				// 错误代码 
				// $ResultArray['filter'] .= substr($Filter, 2, 2); }
				$FilterListFlip = array_flip($this->FilterList);
				$ResultArray['filter'] .= $FilterListFlip[$Filter];}
		}
		$OrderList = array_flip($this->OrderList);
		
		if(isset($this->SearchInfo['order']))
		{
			if(array_key_exists($this->SearchInfo['order'], $OrderList))
			{ $ResultArray['order'] = $OrderList[$this->SearchInfo['order']]; }
		}
		if(isset($this->SearchInfo['tools'])){
			if(array_key_exists($this->SearchInfo['tools'], $OrderList))
			{ $ResultArray['tools'] = $OrderList[$this->SearchInfo['tools']]; }
		}
		if(isset($this->SearchInfo['price'])){
			if(array_key_exists($this->SearchInfo['price'], $OrderList))
			{ $ResultArray['price'] = $OrderList[$this->SearchInfo['price']]; }
		}
		return $ResultArray;
	}

	function getHtmlCtrls()
	{
		return array(
			'PI' => array(
				'isorignal'=> '原创',
				'isformal' => '正式', 
				'ischoice' => '推荐', 
				'isar' => '包含AR', 
				'isvr' => '包含VR'), 
			'MI' => array(
				'istexture' => '贴图', 
				'ismaterials' => '材质', 
				'isanimation' => '动画', 
				'isrigged' => '绑定', 
				'isuvlayout' => 'UV布局', 
				'isrendered' => '已渲染',
				'isprint' => '3D打印'), 
			'Cate' => array(
				'List' => $this->getProductCategory($this->SearchInfo['category']), 
				'Selected' => $this->SearchInfo['category']), 
			'Format' => array(
				'List'=> $this->getProductCreateTool($this->SearchInfo['format']),
				'Selected' => $this->SearchInfo['format']),
			'Creater' => array(
				'List'=> $this->geProductCreater($this->SearchInfo['creater']),
				'Selected' => $this->SearchInfo['creater']),
			'OB' => array(
				'List' => array(
					'score_desc' => '积分最高',
					'lastupdate_desc' => '最近更新', 
					'lastupdate_asc' => '最早更新', 
					'createdate_desc' => '最近发布', 
					'createdate_asc' => '最早发布', 
					'view_desc' => '最多浏览', 
					'downloads_desc' => '最多下载', 
					'id_desc' => '按ID逆序', 
					'id_asc' => '按ID正序', 
					'name_desc' => '按名称逆序', 
					'name_asc' => '按名称正序'), 
				'Selected' => $this->SearchInfo['order']),
				'Count' => array(
				'20' => '20', 
				'50' => '50', 
				'100' => '100', 
				'200' => '200'
				),
			'Tools'=>	array(
				'List'=> $this->getProductCreateTool($this->SearchInfo['tools']),
				'Selected' => $this->SearchInfo['tools']
			),
			'Price'=>	array(
				'1'=>'99',
				'2'=>'499',
				'3'=>'999',
				'4'=>'1799',
				'5'=>'1800'
			),
			'ispublish_new'=>array(
				'List'=>$this->getProductIspublish($this->SearchInfo['ispublish_new']),
				'Selected' => $this->SearchInfo['ispublish_new']
			),
			'isar_new'=>array(
				'List'=>$this->getProductIsar($this->SearchInfo['isar_new']),
				'Selected' => $this->SearchInfo['isar_new']
			),
			'isprint_new'=>array(
				'List'=>$this->getProductIsprint($this->SearchInfo['isprint_new']),
				'Selected' => $this->SearchInfo['isprint_new']
			),
			'isadmin_new'=>array(
				'List'=>$this->getProductIsadmin($this->SearchInfo['isadmin_new']),
				'Selected' => $this->SearchInfo['isadmin_new']
			),
		);
	}

	private function formatSearchInfo($Array)
	{
		$SearchInfoList = array_merge($this->UrlInfoList,$this->FilterList);
		array_merge($this->UrlInfoList, $this->FilterList);
		$ResultArray = array();
		foreach($SearchInfoList as $Key)
		{
			$MappedKey = isset($this->__map[$Key]) ? $this->__map[$Key] : $Key;
			if(isset($Array[$MappedKey]) && $Array[$MappedKey]) { $ResultArray[$Key] = $Array[$MappedKey]; }
		}
		$FilterList = $this->FilterList;
		foreach($FilterList as $Key) { if(isset($ResultArray[$Key])) { $ResultArray[$Key] = 1; } }
		$ResultArray['order'] = 'score_desc';
		if(isset($Array['order']))
		{
			$OrderList = $this->OrderList;
			if(in_array($Array['order'], $OrderList)) { $ResultArray['order'] = $Array['order']; }
		}
		
		/*if(isset($Array['star'])){
			 $ResultArray['star'] = $Array['star']; 
		}*/
		return $ResultArray;
	}

	//TODO
	//filter的取值必须两位一取
	private function formatUrlInfo($Array)
	{
		$UrlInfoList = $this->UrlInfoList;
		$ResultArray = array();
		foreach($UrlInfoList as $Key => $Index) { $ResultArray[$Index] = $Array[$Key]; }
		if(isset($Array['filter']))
		{
			//miaomiao debug@2013.6.20
			$FilterArray = str_split($Array['filter'], 2);
			$FilterList = $this->FilterList;
			foreach ($FilterList as $Key => $Filter){
				if (in_array($Key, $FilterArray)) { $ResultArray[$Filter] = 1; }
			}
		}
		$ResultArray['order'] = 'score_desc';
		if(isset($Array['order']))
		{
			$OrderList = $this->OrderList;
			if(array_key_exists($Array['order'], $OrderList))
			{ $ResultArray['order'] = $OrderList[$Array['order']]; }
		}
		return $ResultArray;
	}
	
	private function getProductCategory($SelectedID = 0)
	{ $CM = new CatesModel(); return $CM->getCateList(0, $SelectedID, true); }
	
	private function getProductCreateTool($SelectedID = 0)
	{ $PCT = new ProductCreateToolModel(); return $PCT->getCreateToolCombo(1, $SelectedID, false); }
		
	private function geProductCreater($SelectedID = 0)
	{ $UM = new UsersModel(); return $UM->getUsersCombo('u_type=2',$SelectedID, false); }
	
	private function getSearchInfoSelector($isFront = true)
	{		
		$PVC = new PVC2(); 
		$PVC->setModePost()->validateNotNull();
		$PVC_DefVal = array('count' => 20);
		$PVC_Str = array('tags', 'order', 'isar','noar','isadmin','noadmin','isvr', 'istexture', 'ismaterials', 'isanimation', 'isrigged', 'isuvlayout', 'isrendered', 'isfree', 'isformal', 'isorignal','iscp','isprint','noprint','isorignal_new','ispublish_new','isar_new','isprint_new','isadmin_new','catetype','begin_id','end_id','isfp','isfpav','low_price','hi_price','productwp','audit','producttitle');
		$PVC_Int = array('category', 'format', 'count', 'creater','isAssembly', 'star', 'type');
		$PVC_Date = array('createdate_s', 'createdate_e', 'lastupdate_s', 'lastupdate_e');
		if($isFront){
			$PVC_Int[] = 'disp'; $PVC_Int[] = 'thumb'; 
		}else{
			$PVC_Str[] = 'isorignal'; $PVC_Str[] = 'isformal'; $PVC_Str[] = 'ischoice';	$PVC_Str[] = 'ispublish';$PVC_Str[] = 'nopublish';}
		
		foreach ($PVC_Str as $Key) { $PVC->isString()->add($this->getMappedKey($Key)); }
		foreach ($PVC_Int as $Key) {
			if(array_key_exists($Key, $PVC_DefVal)) { $PVC->DefVal($PVC_DefVal[$Key]); }
			$PVC->isInt()->add($this->getMappedKey($Key));
		}
		foreach ($PVC_Date as $Key) { $PVC->isDate()->add($this->getMappedKey($Key)); }
		return $PVC;
	}

	private function getUrlInfoSelector($isFront = true, $isDVS=0)
	{
		$PVC = new PVC2(); $PVC->setModeGet()->validateNotNull();
		$default_page_count = $isDVS ? 18 : 20;
		$PVC_DefVal = array('count' => $default_page_count, 'page' => 1, 'thumb'=>1, 'disp'=>1, 'star'=>0);
		$PVC_Str 	= array('tags','filter', 'order', 'price','isorignal_new','ispublish_new','isar_new','isprint_new','isadmin_new','catetype','begin_id','end_id','lprice','hprice','wp','audit','tlike');
		$PVC_Int 	= array('cate', 'format', 'count', 'creater', 'page','tools','isAssembly','star','used','type');
		$PVC_Date 	= array('cds', 'cde','lus','lue');
		if($isFront) { $PVC_Int[] = 'disp'; $PVC_Int[] = 'thumb'; }
		else { foreach ($PVC_Date as $Key) { $PVC->isDate()->add($this->getMappedKey($Key)); } }
		foreach ($PVC_Str as $Key) { $PVC->isString()->add($this->getMappedKey($Key)); }
		foreach ($PVC_Int as $Key) { 
			if(array_key_exists($Key, $PVC_DefVal)) { $PVC->DefVal($PVC_DefVal[$Key]); }
			$PVC->isInt()->add($this->getMappedKey($Key)); 
		}
		return $PVC;
	}
	
	private function getMappedKey($Key)
	{ return isset($this->__map[$Key]) ? $this->__map[$Key] : $Key; }
	
	
	
	
	private function getProductIspublish($currentkey){
		$list=array(
			'0'=>'所有',
			'1'=>'发布',
			'2'=>'未发布',
		);
		return $this->getSelectOption($list, $currentkey);
	}
	
	private function getProductIsar($currentkey){
		$list=array(
				'0'=>'所有',
				'1'=>'有',
				'2'=>'无',
		);
		return $this->getSelectOption($list, $currentkey);
	}
	
	private function getProductIsprint($currentkey){
		$list=array(
				'0'=>'所有',
				'1'=>'有',
				'2'=>'无',
		);
		return $this->getSelectOption($list, $currentkey);
	}
	
	private function getProductIsadmin($currentkey){
		$list=array(
				'0'=>'所有',
				'1'=>'后台发布',
				'2'=>'前台发布',
		);
		return $this->getSelectOption($list, $currentkey);
	}
	
	private function getSelectOption($listarray,$currentkey){
		$result="";
		foreach($listarray as $k =>$v){
			if($k==$currentkey){
				$result.="<option value=".$k." selected>".$v."</option>";
			}else{
				$result.="<option value=".$k.">".$v."</option>";
			}
		}
		return $result;
	}
	
}
?>