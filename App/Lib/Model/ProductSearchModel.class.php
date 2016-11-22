<?php
/**
 * 产品搜索类
 *
 * @author jzy miaomin 
 * Jul 18, 2013 11:06:38 AM
 * 
 * $Id: ProductSearchModel.class.php 2240 2014-04-17 02:45:43Z zhangzhibin $
 */
class ProductSearchModel extends Model {
	public $DisplayFields = '';
	public $IsCount = true;
	public $TotalCount = 0;
	protected $DBF;
	protected $trueTableName = 'tdf_product';
	protected $fields = array (
			'p_id',
			'p_name',
			'p_creater',
			'p_cate_1',
			'p_cate_2',
			'p_cover',
			'p_price',
			'p_vprice',
			'p_tags',
			'p_intro',
			'p_author',
			'p_createdate',
			'p_lastupdate',
			'p_downs',
			'p_downs_disp',
			'p_views',
			'p_views_disp',
			'p_score',
			'p_comments',
			'p_zans',
			'p_photos',
			'p_dispweight',
			'p_slabel',
			'p_producttype',
			'p_formal',
            'p_lictype',
			'p_choice',
			'p_dvs_createtool',
			'p_dvs',
			'p_dvs_pfid',
			'p_score',
			'pc_type',
			'_pk' => 'p_id',
			'_autoinc' => true 
	);
	private $SearchMode = 'model';
	private $MultiTags = true;
	private $SearchInfo;
	private $CPM;
	private $IsFront = false;
	private $Product;
	private $P_ID;
	// ---------- 模型分段查询的范围值 ---------- //
	/**
	 * 不要轻易改动此值否则会造成不可预知的后果
	 */
	private $_resultLimitStep = 10000;
	/**
	 * 搜索结果大于10页则最后一页启用反查机制
	 */
	private $_reverseSearchLimit = 5000;
	private $_nowPage = 1;
	private $_nowType = 0;
	public $IsOrderByScore = false;
	
	/**
	 * 产品搜索类
	 *
	 * @param array $SearchInfo        	
	 * @param string $SearchModel        	
	 * @param boolean $IsFront        	
	 * @param boolean $CPM        	
	 * @param boolean $MultiTags        	
	 */
	public function __construct($SearchInfo, $SearchModel = 'model', $IsFront = true, $CPM = null, $MultiTags = false) {
		parent::__construct ();
		$this->DBF = new DBF ();
		$this->Product = $this->DBF->Product->_Table;
		$this->P_ID = $this->Product . '.' . $this->DBF->Product->ID;
		$this->SearchInfo = $SearchInfo;
		$this->SearchMode = $SearchModel;
		$this->CPM = new CategoryPickerModel ();
		$this->IsFront = $IsFront;
		$this->MultiTags = $MultiTags;
	}
	
	/**
	 * 获取模型库总数量
	 */
	private function _getAllProductNum() {
		$sql = "SELECT COUNT(p_id) AS total FROM tdf_product WHERE 1=1";
		$result = $this->query ( $sql );
		if ($result) {
			return $result [0] ['total'];
		} else {
			return 1;
		}
	}
	
	/**
	 * 获取模型分段数限量
	 */
	private function _getRollLimit() {
		$productNum = $this->_getAllProductNum ();
		if ($this->_resultLimitStep) {
			$rollLimit = ceil ( $productNum / $this->_resultLimitStep );
		} else {
			$rollLimit = 1;
		}
		
		return $rollLimit;
	}
	
	/**
	 * 我的收藏统计数
	 *
	 * @return int $result
	 */
	public function noTagsMyFavorCount() {
		$result = 0;
		if (array_key_exists ( 'favor', $this->SearchInfo )) {
			// ---------- 我的收藏 统计计数 ---------- //
			$sql = "SELECT COUNT(*) FROM tdf_user_favor WHERE u_id ='" . $this->SearchInfo ['favor'] . "'";
			$result = get_one ( $this->query ( $sql ) );
		}
		
		return $result;
	}
	
	/**
	 * 我的购买统计数
	 *
	 * @return int $result
	 */
	public function noTagsMyOwnerCount() {
		$result = 0;
		if (array_key_exists ( 'owner', $this->SearchInfo )) {
			// ---------- 我的购买 统计计数 ---------- //
			$sql = "SELECT COUNT(*) FROM tdf_user_own_product WHERE u_id ='" . $this->SearchInfo ['owner'] . "'";
			$result = get_one ( $this->query ( $sql ) );
		}
		
		return $result;
	}
	
	/**
	 * 无关键字普通查询统计数
	 *
	 * @return int $result
	 */
	public function noTagsNormalCount($type) {
		$result = 0;
		$sql = 'SELECT COUNT( DISTINCT tdf_product.p_id) FROM tdf_product';
		if (($this->SearchInfo ['isprint'] == 0) or ($this->SearchInfo ['isprint'] == 1) or ($this->SearchInfo ['istexture'] == 1) or ($this->SearchInfo ['ismaterials'] == 1) or ($this->SearchInfo ['isrigged'] == 1) or ($this->SearchInfo ['isanimation'] == 1) or ($this->SearchInfo ['isuvlayout'] == 1) or ($this->SearchInfo ['isrendered'] == 1) or ($this->SearchInfo ['isvr'] == 1) or ($this->SearchInfo ['isvr'] == 1) or ($this->SearchInfo ['isar'] == 1) or ($this->SearchInfo ['iscp'])) {
			$sql .= $this->getCountLJNoTagString ();
		}
		// ---------- 增加分类的类型筛选 ---------- //
		$sql .= $this->_getCateTypeLeftJoinStr ();
		$sql .= ' WHERE ';
		$sql .= ' 1=1 AND ';
		$sql .= $this->getFormat ();
		$sql .= $this->getTools ();
		$sql .= $this->getCategory ();
		$sql .= $this->getProductType ();
		$sql .= $this->getFront ();
		$sql .= $this->getCp ();
		$sql .= $this->getMainWhereString ();
		$sql .= $this->getAuthor ();
		$sql .= $this->getAudit ();
		$sql .= $this->getCreateDateRange ();
		$sql .= $this->getBeginEnd ();
		$sql .= $this->getLowHi();
		$sql .= $this->getProductWaterProof();
		$sql .= $this->getTitleLike();
		$sql .= $this->getNoPrinter ();
		$sql .= $this->getIsPrinter ();
		$sql .= $this->getIsFinishProduct ();
		$sql .= $this->getIsFinishProductEnabled ();
		$sql .= $this->getLicType ();

		if($type == 3){
			//PC端轻定制
			$sql .= " tdf_product.p_diy_cate_cid != '0'";
		}

		if (substr ( trim ( $sql ), - 3 ) == 'AND') {
			$sql = substr ( $sql, 0, strlen ( $sql ) - 4 );
		}
		// echo $sql;
		// exit;
		$result = get_one ( $this->query ( $sql ) );
		
		return $result;
	}
	
	/**
	 * 带关键字普通查询统计数
	 *
	 * @return int $result
	 */
	public function tagsNormalCount() {
		$result = 0;
		// ---------- 关键字搜索 ---------- //
		$sql = 'SELECT COUNT(tdf_product_tags_index.p_id) FROM tdf_product_tags_index';
		$sql .= $this->getCountLJString ();
		$sql .= ' WHERE ';
		$sql .= $this->getFormat ();
		$sql .= $this->getTools ();
		$sql .= $this->getCategory ();
		$sql .= $this->getProductType ();
		$sql .= $this->getFront ();
		$sql .= $this->getCp ();
		$sql .= $this->getMainWhereString ();
		$sql .= $this->getAuthor ();
		$sql .= $this->getAudit ();
		$sql .= $this->getCreateDateRange ();
		$sql .= $this->getBeginEnd ();
		$sql .= $this->getLowHi();
		$sql .= $this->getProductWaterProof();
		$sql .= $this->getTitleLike();
		$sql .= $this->getNoPrinter ();
		$sql .= $this->getIsPrinter ();
		$sql .= $this->getIsFinishProduct ();
		$sql .= $this->getIsFinishProductEnabled ();
		$sql .= $this->getLicType ();
		
		if (substr ( trim ( $sql ), - 3 ) == 'AND') {
			$sql = '(' . substr ( $sql, 0, strlen ( $sql ) - 4 ) . ')';
		}
		// echo $sql;
		// exit;
		$result = get_one ( $this->query ( $sql ) );
		
		return $result;
	}
	
	/**
	 * 无关键字查询统计数
	 * @return int $result
	 */
	public function noTagsSearchCount($type) {
		$result = 0;
		if ($this->SearchInfo ['tags'] == '') {
			if (array_key_exists ( 'favor', $this->SearchInfo )) {
				$result = $this->noTagsMyFavorCount ();
			} elseif (array_key_exists ( 'owner', $this->SearchInfo )) {
				$result = $this->noTagsMyOwnerCount ();
			} else {
				$result = $this->noTagsNormalCount ($type);
			}
		}
		return $result;
	}
	
	/**
	 * 无关键字查询结果
	 *
	 * @return array $result
	 */
	public function noTagsSearchResult($type) {
		$result = array ();
		if ($this->SearchInfo ['tags'] == '') {
			if (array_key_exists ( 'favor', $this->SearchInfo )) {
				$result = $this->_getMyFavorResult ();
			} elseif (array_key_exists ( 'owner', $this->SearchInfo )) {
				$result = $this->_getMyModelsResult ();
			} else {
				$result = $this->_getNormalResult ($type);
			}
		}
		return $result;
	}
	
	/**
	 * 带关键字查询统计数
	 *
	 * @return int $result
	 */
	public function tagsSearchCount() {
		$result = 0;
		if ($this->SearchInfo ['tags']) {
            $result = $this->tagsNormalCount ();
		}
		return $result;
	}
	
	/**
	 * 带关键字查询结果
	 *
	 * @return array $result
	 */
	public function tagsSearchResult($type) {
		$result = array ();
		if ($this->SearchInfo ['tags']) {
			$result = $this->_getTagsResult ($type);
		}
		return $result;
	}
	
	/**
	 * 获取搜索统计数
	 *
	 * @return int $result
	 */
	public function getSearchCount($type) {
		$result = 0;
		if ($this->SearchInfo ['tags'] == '') {
			$result = $this->noTagsSearchCount ($type);
		} else {
            $result = $this->tagsSearchCount ();
		}
		return $result;
	}
	
	/**
	 * 获取搜索结果
	 *
	 * @return array $result
	 */
	public function getSearchResult($type) {
		//$result = array ();
		if ($this->SearchInfo ['tags'] == '') {
			$result = $this->noTagsSearchResult ($type);
		} else {
			$result = $this->tagsSearchResult ($type);
		}

		return $result;
	}
	
	/**
	 * 获取搜索结果
	 *
	 * @param int $Page        	
	 * @param int $type
	 *        	1 - DVS搜索; 0 - 网站搜索(默认); 2 - 手机端搜索; 3 - PC端轻定制
	 * @return Ambigous <mixed, multitype:>
	 */
	public function getResult($Page = 1, $type) {
        //var_dump($this->IsCount);
		if ($this->IsCount) {
			// ---------- 获取搜索统计数 ---------- //

			$this->TotalCount = $this->getSearchCount ($type);
		}
		if (($this->TotalCount) || (($this->SearchInfo ['tags'] == '') && ($this->SearchInfo ['creater'] == ''))) {
			// ---------- 获取搜索结果 ---------- //
            $this->_nowPage = $Page;
			$this->_nowType = $type;

			$Result = $this->getSearchResult ($type);
		}
		
		return $Result;
	}
	
	/**
	 * 获取文件分类信息
	 *
	 * @return multitype:unknown
	 */
	public function getFileOptionList() {
		$Result = array ();
		
		$Where = $this->getWhereSting ();
		
		$sql = "SELECT tdf_product.p_ctprime, COUNT(*) as cnt_num
FROM tdf_product";
		$sql .= $this->_getTagsLeftJoinStr ();
		if ($this->SearchInfo ['ismaterials'] or $this->SearchInfo ['istexture'] or $this->SearchInfo ['isrigged'] or $this->SearchInfo ['isanimation'] or $this->SearchInfo ['isuvlayout'] or $this->SearchInfo ['isrendered'] or $this->SearchInfo ['isvr'] or $this->SearchInfo ['isar'] or $this->SearchInfo ['isprint']) {
			$sql .= $this->_getProductModelLeftJoinStr ();
		}
		$sql .= $this->_getIsCPLeftJoinStr ();
		$sql .= " WHERE " . $Where . " AND tdf_product.p_ctprime > 1 GROUP BY tdf_product.p_ctprime";
		// echo $sql;
		$res = $this->query ( $sql );
		// print_r($res);
		// exit;
		
		$pct = D ( 'ProductCreateTool' );
		$allpct = $pct->where ( '1=1' )->select ();
		$pct_prime = array ();
		$prime_arr = array ();
		$add_res = array ();
		foreach ( $allpct as $key => $val ) {
			$pct_prime [$val ['pct_prime']] = $val;
		}
		foreach ( $res as $key => $val ) {
			if (is_array ( $pct_prime [$val ['p_ctprime']] )) {
				$val ['pf_createtool'] = $pct_prime [$val ['p_ctprime']] ['pct_id'];
				$val ['pct_name'] = $pct_prime [$val ['p_ctprime']] ['pct_name'];
				$val ['pct_ext'] = $pct_prime [$val ['p_ctprime']] ['pct_ext'];
				$val ['pct_id'] = $pct_prime [$val ['p_ctprime']] ['pct_id'];
				$Result [] = $val;
			} else {
				$prime_arr [] = $val;
			}
		}
		foreach ( $prime_arr as $key => $val ) {
			foreach ( $allpct as $k => $v ) {
				if ($val ['p_ctprime'] % $v ['pct_prime'] == 0) {
					$tmpRes ['p_ctprime'] = $v ['pct_prime'];
					$tmpRes ['cnt_num'] = $val ['cnt_num'];
					$tmpRes ['pf_createtool'] = $v ['pct_id'];
					$tmpRes ['pct_name'] = $v ['pct_name'];
					$tmpRes ['pct_ext'] = $v ['pct_ext'];
					$tmpRes ['pct_id'] = $v ['pct_id'];
					$add_res [] = $tmpRes;
				}
			}
		}
		foreach ( $add_res as $key => $val ) {
			$hasone = 0;
			foreach ( $Result as $k => $v ) {
				if ($val ['p_ctprime'] == $v ['p_ctprime']) {
					$hasone = 1;
					$Result [$k] ['cnt_num'] += $val ['cnt_num'];
				}
			}
			if (! $hasone) {
				$Result [] = $val;
			}
		}
		
		return $Result;
	}
	
	/**
	 * 获取模型分类分组信息
	 *
	 * @return unknown
	 */
	public function getCateGroupRes_1() {
		$Where = $this->IsFront ? $this->getFront () : '';
		
		$Where_1 .= $this->getTags ();
		$Where_1 .= $this->getisAssembly ();
		// $Where_1 .= $this->getCategory();
		$Where_1 .= $this->getFormat ();
		$Where_1 .= $this->getTools ();
		$Where_1 .= $this->getSubProductInfo ();
		$Where_1 .= $this->getCreateDateRange ();
		$Where_1 .= $this->getLastUpdateRange ();
		$Where_1 .= $this->getCp ();
		$Where_1 .= "tdf_product_cate.pc_type=0 ";
		
		if (empty ( $Where_1 )) {
			$Where = substr ( $Where, 0, strlen ( $Where ) - 4 );
		} else {
			$Where = $Where . $Where_1;
		}
		
		$this->join ( 'tdf_product_cate ON tdf_product_cate.pc_id = tdf_product.p_cate_1' );
		
		if ($this->SearchInfo ['iscp']) {
			
			$this->join ( 'tdf_product_use ON tdf_product_use.p_id = tdf_product.p_id' );
		}
		
		$this->join ( 'tdf_product_model ON tdf_product.p_id = tdf_product_model.p_id' );
		
		$Result = $this->field ( 'tdf_product_cate.pc_id,tdf_product_cate.pc_name,count(*) as cnt_num' )->where ( $Where )->group ( 'tdf_product.p_cate_1' )->select ();
		
		// echo ($this->getLastSql());
		
		return $Result;
	}
	
	/**
	 * 获取打印模型分类分组信息
	 *
	 * @return unknown
	 */
	public function getCateGroupRes_2() {
		$Where = $this->IsFront ? $this->getFront () : '';
		
		$Where .= $this->getTags ();
		$Where .= $this->getisAssembly ();
		// $Where .= $this->getCategory ();
		$Where .= $this->getFormat ();
		// $Where .= $this->getIsPrinter ();
		$Where .= $this->getCreateDateRange ();
		$Where .= $this->getLastUpdateRange ();
		$Where .= $this->getCp ();
		$Where .= "tdf_product_cate.pc_type=1 ";
		
		if (substr ( trim ( $Where ), - 3 ) == 'AND') {
			$Where = '(' . substr ( $Where, 0, strlen ( $Where ) - 4 ) . ')';
		}
		
		$this->join ( 'tdf_product_cate ON tdf_product_cate.pc_id = tdf_product.p_cate_2' );
		
		if ($this->SearchInfo ['iscp']) {
			$this->join ( 'tdf_product_use ON tdf_product_use.p_id = tdf_product.p_id' );
		}
		
		$this->join ( 'tdf_product_model ON tdf_product.p_id = tdf_product_model.p_id' );
		
		$Result = $this->field ( 'tdf_product_cate.pc_id,tdf_product_cate.pc_name,count(*) as cnt_num' )->where ( $Where )->group ( 'tdf_product.p_cate_2' )->select ();
		
		foreach ( $Result as $key => $val ) {
			if ($val ['pc_id'] == '') {
				array_splice ( $Result, $key, 1 );
			}
		}
		// echo $this->getLastSql();
		return $Result;
	}
	
	/**
	 * 其他检索结果
	 *
	 * @return array $Result
	 */
	private function _getNormalResult($type) {
		$Result = array ();
		if ($this->TotalCount && $this->SearchInfo ['count']) {
			$TotalPage = ceil ( $this->TotalCount / $this->SearchInfo ['count'] );
		} else {
			$TotalPage = 0;
		}
		
		if (($this->_nowPage == $TotalPage) && ($this->_nowPage >= $this->_reverseSearchLimit)) {
			$Result = $this->_getNormalReverseResult ( $this->_nowPage, $this->_nowType );
		} else {
			$Result = $this->_getNormalDirectionResult ($type);
		}
		
		return $Result;
	}
	
	/**
	 * 其他检索结果(倒查用于最后一页结果)
	 *
	 * @param int $Page        	
	 * @param int $type        	
	 * @return array $Result
	 */
	private function _getNormalReverseResult($Page, $type) {
		$Result = array ();
		
		if ($this->TotalCount && $this->SearchInfo ['count']) {
			$TotalPage = ceil ( $this->TotalCount / $this->SearchInfo ['count'] );
			$lastPageNum = $this->TotalCount % $this->SearchInfo ['count'];
			$lastPageNum = $lastPageNum ? $lastPageNum : $this->SearchInfo ['count'];
		} else {
			$TotalPage = 0;
			$lastPageNum = 0;
		}
		
		if (($Page == $TotalPage) && ($Page > $this->_reverseSearchLimit)) {
			// ---------- 从数据库底部反查 ---------- //
			
			// ---------- 保险因子避免死循环 ---------- //
			$stub = 0;
			// ---------- 分段计数器 ---------- //
			$roll = $this->_getRollLimit ();
			// ---------- 临时查询结果保存处 ---------- //
			$RollResult = array ();
			do {
				$stub += 1;
				$smallPage = 0;
				
				$Where = $this->getMainWhereString ();
				
				if (substr ( trim ( $Where ), - 3 ) == 'AND') {
					$Where = '(' . substr ( $Where, 0, strlen ( $Where ) - 4 ) . ')';
				}
				
				$SubTable = $this->getSubTableString ( $roll, $type );
				$LeftJoin = $this->getLeftJoinString ();
				$PageLimit = $this->getPageLimit ( $smallPage );
				
				if (strlen ( $Where ) > 0) {
					$sql = $SubTable . $LeftJoin . ' WHERE ' . $Where . $PageLimit;
				} else {
					$sql = $SubTable . $LeftJoin . $PageLimit;
				}
				
				$this->setOrderBy ();
				$this->setCount ();
				$this->page ( $Page );
				
				$Result = $this->query ( $sql );
				
				// 结果有可能无法满足当前显示页的数量，必须通过几次循环(拆表)获得数据
				$RollResult = array_merge ( $RollResult, $Result );
				
				$roll -= 1;
				
				$Result = $RollResult;
				
				if (count ( $Result ) > $lastPageNum) {
					$more = count ( $Result ) - $lastPageNum;
					for($k = 0; $k < $more; $k ++) {
						array_pop ( $Result );
					}
				}
				// }while ((count($Result) < $lastPageNum) and ($roll >= 0)
				// and ($stub < $roll));
			} while ( (count ( $Result ) < $lastPageNum) and ($roll >= 0) );
		}
		
		return $Result;
	}
	
	/**
	 * 其他检索结果(顺查)
	 */
	private function _getNormalDirectionResult($type) {
		$Result = array ();
		// DISPLAY
		if ($this->DisplayFields) {
			$sql = "SELECT " . $this->DisplayFields . " FROM tdf_product";
		} else {
			$sql = "SELECT tdf_product.p_id,tdf_product.p_producttype,tdf_product.p_price,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_views_disp,tdf_product.p_zans,tdf_product_model.pm_isprmodel,pm_isprready,tdf_product.p_diy_cate_cid FROM tdf_product";
		}
		$sql .= $this->getLeftJoinString ();
		$sql .= ' WHERE ';
		$sql .= $this->getFormat ();
		$sql .= $this->getTools ();
		$sql .= $this->getCategory ();
		$sql .= $this->getProductType ();
		$sql .= $this->getFront ();
		$sql .= $this->getCp ();
		$sql .= $this->getMainWhereString ();
		$sql .= $this->getAuthor ();
		$sql .= $this->getAudit ();
		$sql .= $this->getCreateDateRange ();
		$sql .= $this->getBeginEnd ();
		$sql .= $this->getLowHi();
		$sql .= $this->getProductWaterProof();
		$sql .= $this->getTitleLike();
		$sql .= $this->getNoPrinter ();
		$sql .= $this->getIsPrinter ();
		$sql .= $this->getIsFinishProduct ();
		$sql .= $this->getIsFinishProductEnabled ();
		$sql .= $this->getLicType ();

		//轻定制
		if($type == 3){
			$sql .= " tdf_product.p_diy_cate_cid != '0'";
		}

		if (substr ( trim ( $sql ), - 3 ) == 'AND') {
			$sql = '(' . substr ( $sql, 0, strlen ( $sql ) - 4 ) . ')';
		}

		/*
		 * 注意,这块$sql需重新赋值
		 * 排重,针对tdf_product_cate_index表中同p_id但不同pc_type
		 */
		$sql = $this->sqlUnique($sql);

		
		$sql .= $this->getOrderBy ();
		$sql .= $this->getPageLimit ();
		/*echo $sql;
		exit;*/
		$Result = $this->query ( $sql );
		// 排重
		//$Result = arrayUnique($Result);
		return $Result;
	}

	/**
	 * 排重
	 * 2016-09-09
	 * create by guolixun
	 * 针对tdf_product_cate_index表中同p_id但不同pc_type
	 * 在去重查询时注意结果集的别名，一定要对应getOrderBy()方法中字段排序的表明
	 * 否则sql语句会错误
	 */
	public function sqlUnique($sql){
		$Product = $this->DBF->Product;
		$aliasname = $Product->_Table;
		$sql = "select *,count(distinct p_id) from (". $sql.")  ".$aliasname." group by p_id";
		return $sql;
	}
	
	/**
	 * 我的购买检索结果
	 */
	private function _getMyModelsResult() {
		$Result = array ();
		
		if ($this->checkValue ( $this->SearchInfo ['owner'] )) {
			$sql = "SELECT tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_views_disp,tdf_product.p_zans FROM tdf_user_own_product LEFT JOIN tdf_product ON (tdf_product.p_id = tdf_user_own_product.p_id) WHERE tdf_user_own_product.u_id = '" . $this->SearchInfo ['owner'] . "' AND (tdf_user_own_product.uop_type = '1' OR tdf_user_own_product.uop_type = '3')";
			
			$sql .= $this->getOrderBy ();
			$sql .= $this->getPageLimit ();
			
			$Result = $this->query ( $sql );
		}
		
		return $Result;
	}
	
	/**
	 * 我的收藏检索结果
	 */
	private function _getMyFavorResult() {
		$Result = array ();
		
		if ($this->checkValue ( $this->SearchInfo ['favor'] )) {
			$sql = "SELECT tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_views_disp,tdf_product.p_zans FROM tdf_user_favor LEFT JOIN tdf_product ON (tdf_product.p_id = tdf_user_favor.uf_id) WHERE tdf_user_favor.u_id = '" . $this->SearchInfo ['favor'] . "'";
			
			$sql .= $this->getOrderBy ();
			$sql .= $this->getPageLimit ();
			
			$Result = $this->query ( $sql );
		}
		
		return $Result;
	}
	
	/**
	 * 关键字表小于5W条时可使用此方法获取检索结果
	 */
	private function _getTagsResult($type) {
		$Result = array ();
		
		
		if ($this->checkValue ( $this->SearchInfo ['tags'] )) {
			if ($this->DisplayFields) {
				$sql = "SELECT " . $this->DisplayFields . " FROM tdf_product_tags_index";
			} else {
				$sql = 'SELECT tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_views_disp,tdf_product.p_zans,tdf_product.p_price,tdf_product.p_producttype,tdf_product.p_diy_cate_cid,tdf_product.p_mini FROM tdf_product_tags_index';
			}
			$sql .= $this->getCountLJString ();
			$sql .= ' WHERE ';
			$sql .= $this->getFormat ();
			$sql .= $this->getTools ();
			$sql .= $this->getCategory ();
			$sql .= $this->getProductType ();
			$sql .= $this->getFront ();
			$sql .= $this->getCp ();
			$sql .= $this->getMainWhereString ();
			$sql .= $this->getAuthor ();
			$sql .= $this->getAudit ();
			$sql .= $this->getCreateDateRange ();
			$sql .= $this->getBeginEnd ();
			$sql .= $this->getLowHi();
			$sql .= $this->getProductWaterProof();
			$sql .= $this->getTitleLike();
			$sql .= $this->getNoPrinter ();
			$sql .= $this->getIsPrinter ();
			$sql .= $this->getIsFinishProduct ();
			$sql .= $this->getIsFinishProductEnabled ();
			$sql .= $this->getLicType ();


			if (substr ( trim ( $sql ), - 3 ) == 'AND') {
				$sql = '(' . substr ( $sql, 0, strlen ( $sql ) - 4 ) . ')';
			}

			$sql .= $this->getOrderBy ();
			//区别手机端搜索
			if($type ==2){
				$sql .= $this->getPageLimit ();
			}
			$Result = $this->query ( $sql );
		}
		return $Result;
	}
	
	/**
	 * 获取GROUP BY分类信息的父祖节点
	 *
	 * @param array $groupByArr        	
	 * @return array $cateArrRes;
	 *        
	 */
	public function getGroupByArrParentCate($groupByArr) {
		$cateArrRes = array ();
		
		$Cates = D ( 'Cates' );
		$cpm = new CategoryPickerModel ();
		
		foreach ( $groupByArr as $key => $val ) {
			$cateArrRes [] = $cpm->getPartentList ( $val ['pc_id'] );
		}
		
		return $cateArrRes;
	}
	
	/**
	 * 获取搜索结果右侧栏分类信息的标准结构数组
	 *
	 * @param array $groupByArr        	
	 * @return array $cateArrRes
	 */
	public function getCateDispArr($groupByArr) {
		$cateArrRes = array ();
		
		$temp_res = $this->getGroupByArrParentCate ( $groupByArr );
		
		foreach ( $temp_res as $key => $val ) {
			$cate_item = array ();
			$cate_item ['pc_id'] = $val ['Child'] ['pc_id'];
			$cate_item ['pc_name'] = $val ['Child'] ['pc_name'];
			$cate_item ['cnt_num'] = 0;
			
			if (! in_array ( $cate_item, $cateArrRes )) {
				$cateArrRes [] = $cate_item;
			}
		}
		
		foreach ( $cateArrRes as $key => $val ) {
			if ((! $val ['pc_id']) || ($val ['pc_id'] == 1)) {
				unset ( $cateArrRes [$key] );
			}
		}
		
		return $cateArrRes;
	}
	
	/**
	 * 获取CG模型搜索结果右侧栏的分类信息
	 *
	 * @return Ambigous <multitype:unknown number , unknown>
	 */
	public function getCateGroupRes() {
		$cate_1_group = $this->getCateGroupRes_1 ();
		
		// 为分组分类添加父类ID节点
		$temp_res = $this->getGroupByArrParentCate ( $cate_1_group );
		foreach ( $temp_res as $key => $val ) {
			$cate_1_group [$key] ['fst_id'] = $val ['Child'] ['pc_id'];
		}
		
		$first_cls_res = $this->getCateDispArr ( $cate_1_group );
		
		foreach ( $cate_1_group as $key => $val ) {
			foreach ( $first_cls_res as $k => $v ) {
				if ($val ['fst_id'] == $v ['pc_id']) {
					// 数量统计
					$first_cls_res [$k] ['cnt_num'] += $val ['cnt_num'];
					// 子分类信息
					if ($val ['fst_id'] != $val ['pc_id']) {
						$first_cls_res [$k] ['Child'] [$val ['pc_id']] = $val;
					}
					// 当前是否选中
					if ($this->checkValue ( $this->SearchInfo ['category'] )) {
						if ($val ['pc_id'] == $this->SearchInfo ['category']) {
							$first_cls_res [$k] ['Selected'] = 1;
						}
					}
				}
			}
		}
		$first_cls_res = array_values ( $first_cls_res );
		
		return $first_cls_res;
	}
	
	/**
	 * 获取3D打印模型搜索结果右侧栏的分类信息
	 *
	 * @return Ambigous <multitype:unknown number , unknown>
	 */
	public function getCateGroupRes_print() {
		$cate_2_group = $this->getCateGroupRes_2 ();
		
		$cate_1_group = array ();
		foreach ( $cate_2_group as $key => $val ) {
			$inOne = false;
			foreach ( $cate_1_group as $k => $v ) {
				if ($val ['pc_id'] == $v ['pc_id']) {
					$inOne = true;
					$cate_1_group [$k] ['cnt_num'] += $val ['cnt_num'];
				}
			}
			if (! $inOne) {
				$cate_1_group [] = $val;
			}
		}
		// 为分组分类添加父类ID节点
		$temp_res = $this->getGroupByArrParentCate ( $cate_1_group );
		foreach ( $temp_res as $key => $val ) {
			$cate_1_group [$key] ['fst_id'] = $val ['Child'] ['pc_id'];
		}
		
		$first_cls_res = $this->getCateDispArr ( $cate_1_group );
		
		foreach ( $cate_1_group as $key => $val ) {
			foreach ( $first_cls_res as $k => $v ) {
				if ($val ['fst_id'] == $v ['pc_id']) {
					$first_cls_res [$k] ['cnt_num'] += $val ['cnt_num'];
					if ($val ['fst_id'] != $val ['pc_id']) {
						$first_cls_res [$k] ['Child'] [$val ['pc_id']] = $val;
					}
					if ($val ['pc_id'] == $this->SearchInfo ['category']) {
						$first_cls_res [$k] ['Selected'] = 1;
					}
				}
			}
		}
		$first_cls_res = array_values ( $first_cls_res );
		// dump($first_cls_res);
		return $first_cls_res;
	}
	private function getNextClassCate($arr, $cateId) {
		if ($arr ['pc_parentid'] == $cateId) {
			return $arr;
		} else {
			if (array_key_exists ( 'Child', $arr )) {
				return $this->getNextClassCate ( $arr ['Child'], $cateId );
			} else {
				return false;
			}
		}
	}
	// miaomin
	private function getMainWhereString() {
		// TAGS + 搜索工具
		$Where .= $this->getTags ();
		$Where .= $this->getisAssembly ();
		$Where .= $this->getStar ();
		$Where .= $this->getcatetype ();
		$Where .= $this->getSubProductInfo ();
		return $Where;
	}
	
	// TODO
	private function getSubTableString($roll = 0, $type = 0) {
		$res = '';
		$field = 'tdf_product.p_id,tdf_product.p_name,tdf_product.p_price,tdf_product.p_vprice,tdf_product.u_id,tdf_product.u_realname,tdf_product.u_dispname,tdf_product.p_createdate,tdf_product.p_lastupdate,tdf_product.p_downs_disp,tdf_product.p_views_disp,tdf_product.p_cover,tdf_product.p_formal,tdf_product.p_slabel,tdf_product.p_choice,tdf_product.p_lictype,tdf_product.p_cate_1,tdf_product.p_cate_2,tdf_product.p_dvs_pfid,tdf_product.p_score,tdf_product.p_zans,tdf_product_model.pm_isprint';
		$field2 = 'tdf_product.p_id,tdf_product.p_name,tdf_product.p_price,tdf_product.p_vprice,tdf_users.u_id,tdf_users.u_realname,tdf_users.u_dispname,tdf_product.p_createdate,tdf_product.p_lastupdate,tdf_product.p_downs_disp,tdf_product.p_views_disp,tdf_product.p_cover,tdf_product.p_formal,tdf_product.p_choice,tdf_product.p_slabel,tdf_product.p_lictype,tdf_product.p_cate_1,tdf_product.p_cate_2,tdf_product.p_dvs_pfid,tdf_product.p_score,tdf_product.p_zans';
		$res .= 'SELECT ' . $field . ' FROM';
		$res .= ' (SELECT ';
		$table = ' FROM tdf_product ';
		$table .= $this->getCreaterNameString ();
		$alias = ' AS tdf_product';
		$Where = ' WHERE ';
		$Where .= ' 1=1 AND ';
		$Where .= $this->getFormat ();
		$Where .= $this->getTools ();
		$Where .= $this->getCategory ();
		$Where .= $this->getProductType ();
		$Where .= $this->getFront ();
		$Where .= $this->getCp ();
		$Where .= $this->getAuthor ();
		$Where .= $this->getAudit ();
		$Where .= $this->getFavor ();
		$Where .= $this->getOwner ();
		$Where .= $this->getCreateDateRange ();
		$Where .= $this->getBeginEnd ();
		$Where .= $this->getLowHi();
		$Where .= $this->getProductWaterProof();
		$Where .= $this->getTitleLike();
		$Where .= $this->getNoPrinter ();
		$Where .= $this->getIsPrinter ();
		$Where .= $this->getIsFinishProduct ();
		$Where .= $this->getIsFinishProductEnabled ();
		$Where .= $this->getLicType ();
		$this->setOrderBy ();
		if (! empty ( $Where ) and substr ( $Where, - 4 ) == 'AND ') {
			$Where = substr ( $Where, 0, strlen ( $Where ) - 4 );
		}
		$Where .= $this->getOrderBy ();
		$Where .= $this->getLimit ( $roll );
		$Where .= ')';
		// echo $Where."<br>";
		
		$res .= $field2 . $table . $Where . $alias;
		
		return $res;
	}
	
	/**
	 * SUB SQL的LEFT JOIN部分
	 *
	 * @return string $res
	 */
	private function getCreaterNameString() {
		$res = '';
		
		$res .= $this->_getModelCreaterLeftJoinStr ();
		$res .= $this->_getIsCPLeftJoinStr ();
		$res .= $this->_getOwnerLeftJoinStr ();
		$res .= $this->_getFavorLeftJoinStr ();
		
		return $res;
	}
	
	/**
	 * 为模型作者关联用户表生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getModelCreaterLeftJoinStr() {
		$res = '';
		$res .= ' LEFT JOIN tdf_users ON (tdf_users.u_id = tdf_product.p_creater)';
		return $res;
	}
	
	/**
	 * 为$SearchInfo['owner']生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getOwnerLeftJoinStr() {
		$res = '';
		if ($this->SearchInfo ['owner']) {
			if ($this->checkValue ( $this->SearchInfo ['owner'] )) {
				$res .= ' LEFT JOIN tdf_user_own_product ON (tdf_user_own_product.p_id = tdf_product.p_id)';
			}
		}
		return $res;
	}
	
	/**
	 * 为$SearchInfo['favor']生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getFavorLeftJoinStr() {
		$res = '';
		if ($this->SearchInfo ['favor']) {
			if ($this->checkValue ( $this->SearchInfo ['favor'] )) {
				$res .= ' LEFT JOIN tdf_user_favor ON (tdf_user_favor.uf_id = tdf_product.p_id)';
			}
		}
		return $res;
	}
	
	/**
	 * 为$SearchInfo['tags']生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getTagsLeftJoinStr() {
		$res = '';
		if ($this->SearchInfo ['tags']) {
			$res .= " LEFT JOIN tdf_product_tags_index ON (tdf_product_tags_index.p_id = tdf_product.p_id)";
		}
		return $res;
	}
	
	/**
	 * 为$SearchInfo['iscp']生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getIsCPLeftJoinStr() {
		$res = '';
		if ($this->SearchInfo ['iscp']) {
			$res .= ' LEFT JOIN tdf_product_use ON (tdf_product_use.p_id = tdf_product.p_id)';
		}
		return $res;
	}
	
	/**
	 * 为$SearchInfo['category']生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getCateLeftJoinStr(){
		$res = '';
		if ($this->SearchInfo ['category']) {
			$res .= ' LEFT JOIN tdf_product_cate_index ON (tdf_product_cate_index.p_id = tdf_product.p_id)';
		}
		return $res;
	}
	
	/**
	 * 为模型关键字表匹配的模型ID关联模型表生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getTagsProductLeftJoinStr() {
		$res = '';
		$res .= ' LEFT JOIN tdf_product ON (tdf_product.p_id = tdf_product_tags_index.p_id)';
		return $res;
	}
	
	/**
	 * 为模型关键字表匹配的模型ID关联模型扩展表生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getTagsProductModelLeftJoinStr() {
		$res = '';
		$res .= ' LEFT JOIN tdf_product_model ON (tdf_product_model.p_id = tdf_product_tags_index.p_id)';
		return $res;
	}
	
	/**
	 * 为模型关联模型扩展表生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getProductModelLeftJoinStr() {
		$res = '';
		$res .= ' LEFT JOIN tdf_product_model ON (tdf_product_model.p_id = tdf_product.p_id)';
		return $res;
	}
	
	/**
	 * 为模型关联模型扩展表生成LeftJoin语句
	 *
	 * @return string $res
	 */
	private function _getProductNoneDiyLeftJoinStr() {
		$res = '';
		$res .= ' LEFT JOIN tdf_info_producttype ON (tdf_info_producttype.ipt_id = tdf_product.p_maintype)';
		$res .= ' LEFT JOIN tdf_users ON (tdf_users.u_id = tdf_product.p_creater)';
		$res .= ' LEFT JOIN tdf_product_waterproof ON (tdf_product_waterproof.pwp_id = tdf_product.p_wpid)';
		return $res;
	}
	
	/**
	 * 为$SearchInfo['catetype']生成LeftJoin语句
	 *
	 * @return string
	 */
	private function _getCateTypeLeftJoinStr() {
		$res = '';
		if (! is_null ( $this->SearchInfo ['catetype'] )) {
			if ($this->SearchInfo ['catetype'] == 1) {
				$res .= ' LEFT JOIN tdf_product_cate ON (tdf_product_cate.pc_id = tdf_product.p_cate_2)';
			} else {
				$res .= ' LEFT JOIN tdf_product_cate ON (tdf_product_cate.pc_id = tdf_product.p_cate_1)';
			}
		}
		return $res;
	}
	
	/**
	 * 整个SQL的LEFT JOIN部分
	 *
	 * @return string
	 */
	private function getLeftJoinString() {
		$res = '';
		if ($this->SearchInfo ['tags'] == '') {
			
			if ($this->SearchMode == 'model') {
				$res .= $this->_getProductModelLeftJoinStr ();
			}
			
			if ($this->SearchMode == 'nonediy') {
				$res .= $this->_getProductNoneDiyLeftJoinStr ();
			}
			
			if ($this->SearchInfo['category']){
				$res .= $this->_getCateLeftJoinStr();
			}
			
			$res .= $this->_getCateTypeLeftJoinStr ();
		} else {
			$res .= $this->_getTagsLeftJoinStr ();
			
			if ($this->SearchMode == 'model') {
				$res .= $this->_getProductModelLeftJoinStr ();
			}
			
			if ($this->SearchMode == 'nonediy') {
				$res .= $this->_getProductNoneDiyLeftJoinStr ();
			}
		}
		return $res;
	}
	
	/**
	 * LJ
	 *
	 * @return string
	 */
	private function getCountLJString() {
		$res = '';
		$res .= $this->_getTagsProductLeftJoinStr ();
		if ($this->SearchMode == 'model') {
			$res .= $this->_getProductModelLeftJoinStr ();
		} elseif ($this->SearchMode == 'nonediy') {
			$res .= $this->_getProductNoneDiyLeftJoinStr ();
		}
		return $res;
	}
	
	/**
	 * 计数部分整个SQL的LEFT JOIN部分
	 *
	 * @return string
	 */
	private function getCountLJNoTagString() {
		// echo $this->SearchMode;
		$res = '';
		if ($this->SearchInfo ['iscp']) {
			$res .= $this->_getIsCPLeftJoinStr ();
		} else {
			if ($this->SearchMode == 'model') {
				$res .= $this->_getProductModelLeftJoinStr ();
			} elseif ($this->SearchMode == 'nonediy') {
				//
			}
		}
		
		if ($this->SearchInfo ['category']) {
			$res .= $this->_getCateLeftJoinStr ();
		}
		
		return $res;
	}
	
	/**
	 * 主查询SQL拼接 - LIMIT部分
	 *
	 * @param int $Page        	
	 * @return string
	 */
	private function getPageLimit($Page = 0) {
		$res = '';
		$res = ' LIMIT ';
		if ($this->checkValue ( $this->SearchInfo ['page'] )) {
			$Page = $this->SearchInfo ['page'];
		}
		if ($Page < 1) {
			$Page = 1;
		}
		$start = ($Page - 1) * $this->SearchInfo ['count'];
		//$start = $start - 1;
		if($start <'0'){
			$start = 0;
		}
		$limit = $this->SearchInfo ['count'];
		$res .= $start . ',' . $limit;
		return $res;
		
	}
	private function getWhereSting() {
		$Where = $this->getTags ();
		$Where .= $this->getisAssembly ();
		$Where .= $this->IsFront ? $this->getFront () : $this->getAudit ();
		$Where .= $this->getCp ();
		$Where .= $this->getCategory () . $this->getFormat () . $this->getSubProductInfo () . $this->getTools ();
		$Where .= $this->getCreateDateRange () . $this->getLastUpdateRange () . $this->getAuthor ();
		if (! empty ( $Where )) {
			$Where = substr ( $Where, 0, strlen ( $Where ) - 4 );
		}
		return $Where;
	}
	private function getFileList($PIDWhere) {
		$PFM = new ProductFileModel ();
		$ProductFile = $this->DBF->ProductFile->_Table;
		$PF_PID = $ProductFile . '.' . $this->DBF->ProductFile->ProductID;
		$PF_CreateTool = $ProductFile . '.' . $this->DBF->ProductFile->CreateTool;
		
		$ProductCreateTool = $this->DBF->ProductCreateTool->_Table;
		$PCT_ID = $ProductCreateTool . '.' . $this->DBF->ProductCreateTool->ID;
		
		$PFM->join ( $ProductCreateTool . ' ON ' . $PF_CreateTool . ' = ' . $PCT_ID );
		return $PFM->where ( $PIDWhere )->select ();
	}
	private function getCreateDateRange() {
		$StartDate = $this->SearchInfo ['createdate_s'];
		$EndDate = $this->SearchInfo ['createdate_e'];
		if (! $EndDate) {
			$EndDate = $StartDate;
		}
		$P_CreateDate = $this->Product . '.' . $this->DBF->Product->CreateDate;
		return $this->getDateRange ( $P_CreateDate, $StartDate, $EndDate );
	}
	private function getBeginEnd() {
		$BeginID = $this->SearchInfo ['begin_id'] ? $this->SearchInfo ['begin_id'] : 0;
		$EndID = $this->SearchInfo ['end_id'] ? $this->SearchInfo ['end_id'] : 0;
		if ($EndID) {
			$result = " " . $this->Product . '.' . $this->DBF->Product->ID . ">" . $BeginID . " and " . $this->Product . '.' . $this->DBF->Product->ID . "<" . $EndID . " AND ";
		} else {
			$result = "";
		}
		return $result;
	}
	private function getTitleLike() {
	    $TitleKey = $this->SearchInfo ['producttitle'] ? $this->SearchInfo ['producttitle'] : '';
	    if ($TitleKey) {
	        $result = " " . $this->Product . '.' . $this->DBF->Product->Name . " LIKE '%" . $TitleKey . "%'" . " AND ";
	    } else {
	        $result = "";
	    }
	    return $result;
	}
	private function getLowHi() {
	    $LowPrice = $this->SearchInfo ['low_price'] ? $this->SearchInfo ['low_price'] : 0;
	    $HiPrice = $this->SearchInfo ['hi_price'] ? $this->SearchInfo ['hi_price'] : 0;
	    if ($HiPrice) {
	        $result = " " . $this->Product . '.' . $this->DBF->Product->Price . ">" . $LowPrice . " and " . $this->Product . '.' . $this->DBF->Product->Price . "<" . $HiPrice . " AND ";
	    } else {
	        $result = "";
	    }
	    return $result;
	}
	private function getProductWaterProof() {
	    $ProductWP = $this->SearchInfo ['productwp'] ? $this->SearchInfo ['productwp'] : 0;
	    if ($ProductWP) {
	        $result = " " . $this->Product . '.' . $this->DBF->Product->WaterProofId . "='" . $ProductWP . "' AND ";
	    } else {
	        $result = "";
	    }
	    return $result;
	}
	private function getLastUpdateRange() {
		$StartDate = $this->SearchInfo ['lastupdate_s'];
		$EndDate = $this->SearchInfo ['lastupdate_e'];
		$P_LastupDate = $this->Product . '.' . $this->DBF->Product->LastupDate;
		return $this->getDateRange ( $P_LastupDate, $StartDate, $EndDate );
	}
	
	/**
	 * 生成排序语句
	 *
	 * @return string
	 */
	private function getOrderBy() {
		$Product = $this->DBF->Product;
		$default_order = $Product->_Table . '.' . $Product->ID . ' DESC';
		$OrderList = array (
				'lastupdate_desc' => $Product->_Table . '.' . $Product->LastupDate . ' DESC',
				'lastupdate_asc' => $Product->_Table . '.' . $Product->LastupDate . ' ASC',
				'createdate_desc' => $Product->_Table . '.' . $Product->ID . ' DESC',
				'createdate_asc' => $Product->_Table . '.' . $Product->ID . ' ASC',
				'view_desc' => $Product->_Table . '.' . $Product->Views . ' DESC',
				'downs_desc' => $Product->_Table . '.' . $Product->Downs . ' DESC',
				'id_desc' => $Product->_Table . '.' . $Product->ID . ' DESC',
				'id_asc' => $Product->_Table . '.' . $Product->ID . ' ASC',
				'name_desc' => $Product->_Table . '.' . $Product->Name . ' DESC',
				'name_asc' => $Product->_Table . '.' . $Product->Name . ' ASC',
				'price_desc' => $Product->_Table . '.' . $Product->Price . ' DESC',
				'price_asc' => $Product->_Table . '.' . $Product->Price . ' ASC',
				'score_desc' => $Product->_Table . '.' . $Product->Score . ' DESC',
				'score_asc' => $Product->_Table . '.' . $Product->Score . ' ASC',
				'dispweight_desc' => $Product->_Table . '.' . $Product->Dispweight . ' DESC',
				'dispweight_asc' => $Product->_Table . '.' . $Product->Dispweight . ' ASC',
				'score_view_desc' => $Product->_Table . '.' . $Product->Views 
		);
		if ($this->checkValue ( $this->SearchInfo ['order'] )) {
			if (array_key_exists ( $this->SearchInfo ['order'], $OrderList )) {
				return ' ORDER BY ' . $OrderList [$this->SearchInfo ['order']] . ',' . $default_order;
			}
		} else {
			return ' ORDER BY ' . $default_order;
		}
		return '';
	}
	
	/**
	 * 拼接模型分段查询的范围值SQL部分
	 *
	 * @param int $roll        	
	 * @return string
	 */
	private function getLimit($roll = 0) {
		$step = $this->_resultLimitStep;
		$start = $roll * $step;
		return ' Limit ' . $start . ',' . $step;
	}
	
	/**
	 * 设置排序信息
	 */
	private function setOrderBy() {
		$Product = $this->DBF->Product;
		$default_order = $Product->_Table . '.' . $Product->IsFormal . ' DESC';
		$OrderList = array (
				'lastupdate_desc' => $Product->Table . '.' . $Product->LastupDate . ' DESC',
				'lastupdate_asc' => $Product->Table . '.' . $Product->LastupDate . ' ASC',
				'createdate_desc' => $Product->Table . '.' . $Product->CreateDate . ' DESC',
				'createdate_asc' => $Product->Table . '.' . $Product->CreateDate . ' ASC',
				'view_desc' => $Product->Table . '.' . $Product->Views_disp . ' DESC',
				'downs_desc' => $Product->Table . '.' . $Product->Downs_disp . ' DESC',
				'id_desc' => $Product->Table . '.' . $Product->ID . ' DESC',
				'id_asc' => $Product->Table . '.' . $Product->ID . ' ASC',
				'name_desc' => $Product->Table . '.' . $Product->Name . ' DESC',
				'name_asc' => $Product->Table . '.' . $Product->Name . ' ASC',
				'price_desc' => $Product->Table . '.' . $Product->Price . ' DESC',
				'price_asc' => $Product->Table . '.' . $Product->Price . ' ASC',
				'score_desc' => $Product->Table . '.' . $Product->Score . ' DESC',
				'score_asc' => $Product->Table . '.' . $Product->Score . ' ASC',
				'dispweight_desc' => $Product->Table . '.' . $Product->Dispweight . ' DESC',
				'dispweight_asc' => $Product->Table . '.' . $Product->Dispweight . ' ASC'
		);
		if ($this->checkValue ( $this->SearchInfo ['order'] )) {
			if (array_key_exists ( $this->SearchInfo ['order'], $OrderList )) {
				$this->order ( $default_order, $OrderList [$this->SearchInfo ['order']] );
			}
		} else {
			$this->order ( $default_order );
		}
	}
	
	/**
	 * 设置分页数
	 */
	private function setCount() {
		$Count = $this->checkValue ( $this->SearchInfo ['count'] ) ? $this->SearchInfo ['count'] : 50;
		$this->limit ( $Count );
	}
	
	// -----------------------------------------------------------以下全部都是SQL
	// WHERE条件生成函数--//
	
	/**
	 * 整个SQL的筛选判定部分
	 *
	 * @return string
	 */
	private function getSubProductInfo() {
		$ProductModel = $this->DBF->ProductModel->_Table;
		$ProductUse = $this->DBF->ProductUse->_Table;
		$FilterList = array (
				'istexture' => $ProductModel . '.' . $this->DBF->ProductModel->IsTexture,
				'ismaterials' => $ProductModel . '.' . $this->DBF->ProductModel->IsMaterials,
				'isanimation' => $ProductModel . '.' . $this->DBF->ProductModel->IsAnimation,
				'isrigged' => $ProductModel . '.' . $this->DBF->ProductModel->IsRigged,
				'isuvlayout' => $ProductModel . '.' . $this->DBF->ProductModel->IsUVLayout,
				'isrendered' => $ProductModel . '.' . $this->DBF->ProductModel->IsRendered,
				'isformal' => $this->Product . '.' . $this->DBF->Product->IsFormalLicType,
				'ischoice' => $this->Product . '.' . $this->DBF->Product->IsChoice,
				'ispublish' => $this->Product . '.' . $this->DBF->Product->Slabel,
				'isfree' => $this->Product . '.' . $this->DBF->Product->Price,
				'isar' => $ProductModel . '.' . $this->DBF->ProductModel->IsAR,
				'isvr' => $ProductModel . '.' . $this->DBF->ProductModel->IsVR,
				'nopublish' => $this->Product . '.' . $this->DBF->Product->Slabel,
				'isadmin' => $this->Product . '.' . $this->DBF->Product->IsFormal,
				'noadmin' => $this->Product . '.' . $this->DBF->Product->IsFormal 
		);
		$Where = '';
		foreach ( $FilterList as $Key => $Value ) {
			if ($this->checkValue ( $this->SearchInfo [$Key] ))
				if ($Key == 'isfree') {
					$Where .= $Value . "='0' AND ";
				} elseif ($Key == 'nopublish') {
					$Where .= $Value . "='0' AND ";
				} elseif ($Key == 'noadmin') {
					$Where .= $Value . "='0' AND ";
				} else {
					$Where .= $Value . "='1' AND ";
				}
		}
		if (! empty ( $Where )) {
			$Where = '(' . substr ( $Where, 0, strlen ( $Where ) - 4 ) . ') AND ';
		}
		return $Where;
	}
	
	/**
	 * 模型创建日期筛选WHERE语句
	 *
	 * @param unknown_type $Column        	
	 * @param unknown_type $StartDate        	
	 * @param unknown_type $EndDate        	
	 * @return string
	 */
	private function getDateRange($Column, $StartDate, $EndDate) {
		$Where = '';
		if ($this->checkValue ( $StartDate ) || $this->checkValue ( $EndDate )) {
			if ($this->checkValue ( $StartDate )) {
				$Where .= $Column . ">='" . $StartDate . "' AND ";
			}
			if ($this->checkValue ( $EndDate )) {
				$Where .= $Column . "<date_add('" . $EndDate . "', INTERVAL 1 DAY) AND ";
			}
		}
		if (! empty ( $Where )) {
			$Where = '(' . substr ( $Where, 0, strlen ( $Where ) - 4 ) . ') AND ';
		}
		return $Where;
	}
	
	/**
	 * 模型创作工具筛选WHERE语句
	 *
	 * @return string
	 */
	private function getTools() {
		$Where = '';
		if ($this->SearchInfo ['tools']) {
			$Where = "tdf_product.p_dvs_createtool=" . $this->SearchInfo ['tools'] . " AND ";
		}
		return $Where;
	}
	
	/**
	 * 模型不可打印筛选WHERE语句
	 *
	 * @return string
	 */
	private function getNoPrinter() {
		$Where = '';
		if ($this->SearchInfo ['noprint']) {
			$Where = "tdf_product.p_cate_1>0" . " AND ";
		}
		return $Where;
	}
	
	/**
	 * 模型可打印筛选WHERE语句
	 *
	 * @return string
	 */
	private function getIsPrinter() {
		$Where = '';
		if ($this->SearchInfo ['isprint']) {
			$Where = "tdf_product.p_cate_2>0" . " AND ";
		}
		return $Where;
	}
	
	/**
	 * 3D打印成品模型筛选WHERE语句
	 *
	 * @return string
	 */
	private function getIsFinishProduct() {
		$Where = '';
		if ($this->SearchInfo ['isfp']) {
			$Where = "tdf_product_model.pm_isprmodel=1" . " AND ";
		}
		return $Where;
	}
	
	/**
	 * 3D打印成品模型通过审核筛选WHERE语句
	 *
	 * @return string
	 */
	private function getIsFinishProductEnabled() {
		$Where = '';
		if ($this->SearchInfo ['isfpav']) {
			$Where = "tdf_product_model.pm_isprready=1" . " AND ";
		}
		return $Where;
	}
	
	/**
	 * 模型授权类型筛选WHERE语句
	 *
	 * @return string
	 */
	private function getLicType() {
		$Where = '';
		if ($this->SearchInfo ['isorignal']) {
			$Where = "tdf_product.p_lictype=" . $this->SearchInfo ['isorignal'] . " AND ";
		}
		return $Where;
	}
	
	/**
	 * 模型星级筛选WHERE语句
	 *
	 * @return string
	 */
	private function getStar() {
		$Where = '';
		if ($this->SearchInfo ['star']) {
			$Where = "tdf_product.p_score=" . $this->SearchInfo ['star'] . " AND ";
		}
		return $Where;
	}
	
	/**
	 * 分类的类型筛选WHERE语句
	 *
	 * @return string
	 */
	private function getcatetype() {
		$Where = '';
		if (! is_null ( $this->SearchInfo ['catetype'] )) {
			$Where = "tdf_product_cate.pc_type=" . $this->SearchInfo ['catetype'] . " AND ";
		}
		return $Where;
	}
	
	/**
	 * 可装配条件筛选WHERE语句
	 *
	 * @return string
	 */
	private function getisAssembly() {
		$Where = '';
		if ($this->SearchInfo ['isAssembly']) {
			$Where = "tdf_product_model.pm_ismorenode=" . $this->SearchInfo ['isAssembly'] . " AND ";
		}
		return $Where;
	}
	
	/**
	 * 模型作者筛选WHERE语句
	 *
	 * @return string
	 */
	private function getAuthor() {
		$Where = '';
		if ($this->checkValue ( $this->SearchInfo ['creater'] )) {
			$P_Author = $this->Product . '.' . $this->DBF->Product->Creater;
			$Where = "(" . $P_Author . "='" . $this->SearchInfo ['creater'] . "') AND ";
		}
		return $Where;
	}
	
	/**
	 * 我的收藏筛选WHERE语句
	 *
	 * @return string
	 */
	private function getFavor() {
		$Where = '';
		if ($this->checkValue ( $this->SearchInfo ['favor'] )) {
			$Where = "tdf_user_favor.u_id ='" . $this->SearchInfo ['favor'] . "' AND ";
		}
		return $Where;
	}
	
	/**
	 * 我的模型筛选WHERE语句
	 *
	 * @return string
	 */
	private function getOwner() {
		$Where = '';
		if ($this->checkValue ( $this->SearchInfo ['owner'] )) {
			$Where = "tdf_user_own_product.u_id ='" . $this->SearchInfo ['owner'] . "' AND ";
		}
		return $Where;
	}
	
	/**
	 * 关键字筛选WHERE语句
	 *
	 * @return string
	 */
	private function getTags() {
		$Where = '';
		if ($this->checkValue ( $this->SearchInfo ['tags'] )) {
			$ProductTags = $this->DBF->ProductTags->_Table;
			$PT_TID = $ProductTags . '.' . $this->DBF->ProductTags->ID;
			$PT_TagsName = $ProductTags . '.' . $this->DBF->ProductTags->Name;
			
			$ProductTagsIndex = $this->DBF->ProductTagsIndex->_Table;
			$PTI_PID = $ProductTagsIndex . '.' . $this->DBF->ProductTagsIndex->ProductID;
			$PTI_TID = $ProductTagsIndex . '.' . $this->DBF->ProductTagsIndex->TagsID;
			
			$this->join ( $ProductTagsIndex . ' ON ' . $this->P_ID . ' = ' . $PTI_PID );
			
			$Tags = preg_split ( '/[\s,]/', $this->SearchInfo ['tags'] );
			$Where .= $PTI_TID . " = (SELECT " . $PT_TID . " FROM " . $ProductTags . " WHERE ";
			foreach ( $Tags as $KeyWord ) {
				$Where .= 'binary ' . $PT_TagsName . " = '" . $KeyWord . "' OR ";
				if (! $this->MultiTags) {
					break;
				}
			}
			if (! empty ( $Where )) {
				$Where = substr ( $Where, 0, strlen ( $Where ) - 4 ) . ') AND ';
			}
		}
		return $Where;
	}
	
	/**
	 * PRODUCTTYPE筛选WHERE语句
	 */
	private function getProductType() {
		$productTypeArr = C ( 'PRODUCT.TYPE' );
       // var_dump($productTypeArr);
		$Where = '';
		$producttype = $this->SearchInfo ['producttype'];
		
		if ($this->checkValue ( $this->SearchInfo ['producttype'] )) {
			$Where = $this->DBF->Product->_Table . '.' . $this->DBF->Product->ProductType . "='" . $producttype . "' AND " . $this->DBF->Product->_Table . '.' . $this->DBF->Product->BelongPid . "='0' AND ";
		}else{
			$Where = "(" . $this->DBF->Product->_Table . '.' . $this->DBF->Product->ProductType . "='" . $productTypeArr['NDIY'] . "' OR " . $this->DBF->Product->_Table . '.' . $this->DBF->Product->ProductType . "='" . $productTypeArr['ODIY'] . "') AND " . $this->DBF->Product->_Table . '.' . $this->DBF->Product->BelongPid . "='0' AND ";
		}
		
		return $Where;
	}
	
	/**
	 * 分类筛选WHERE语句
	 *
	 * @return string
	 */
	private function getCategory() {
		$Where = '';
		$CateID = $this->SearchInfo ['category'];
		if ($this->checkValue ( $this->SearchInfo ['category'] )) {
			
			/*
			$P_Category_1 = $this->Product . '.' . $this->DBF->Product->Cate_1;
			$P_Category_2 = $this->Product . '.' . $this->DBF->Product->Cate_2;
			
			if ($this->SearchInfo ['getpartentcate'] === false) {
				$Where .= '(' . $P_Category_1 . "='" . $CateID . "' OR " . $P_Category_2 . "='" . $CateID . "') AND ";
			} else {
				$CateList = $this->CPM->getChildIDList ( $CateID );
				if ($CateList) {
					$PCID = $this->DBF->ProductCategory->ID;
					foreach ( $CateList as $Cate ) {
						$Where .= $P_Category_1 . "='" . $Cate . "' OR " . $P_Category_2 . "='" . $Cate . "' OR ";
					}
					if (! empty ( $Where )) {
						$Where = '(' . substr ( $Where, 0, strlen ( $Where ) - 4 ) . ') AND ';
					}
					
					// echo $Where;
				}
			}
			*/
			
			$CateList = $this->CPM->getChildIDList ( $CateID );
			if ($CateList) {
				foreach ( $CateList as $Cate ) {
					$Where .= "tdf_product_cate_index.pc_id = '" . $Cate . "' OR ";
				}
				if (! empty ( $Where )) {
					$Where = '(' . substr ( $Where, 0, strlen ( $Where ) - 4 ) . ' AND tdf_product_cate_index.pc_type != \'0\') AND ';
				}
			}
			
			// echo $Where;
		}
		return $Where;
	}
	
	/**
	 * 文件类型WHERE语句
	 *
	 * @return string
	 */
	private function getFormat() {
		$Where = '';
		if ($this->checkValue ( $this->SearchInfo ['format'] )) {
			
			$ProductFile = $this->DBF->ProductFile->Table;
			$PF_PID = $ProductFile . '.' . $this->DBF->ProductFile->Product_ID;
			$PF_CreateTool = $ProductFile . '.' . $this->DBF->ProductFile->CreateTool;
			
			$PCT = new ProductCreateToolModel ();
			$PCT->find ( $this->SearchInfo ['format'] );
			
			$Where = "((tdf_product.p_ctprime > 0) AND (tdf_product.p_ctprime = '" . $PCT->pct_prime . "' OR tdf_product.p_ctprime % " . $PCT->pct_prime . " = 0)) AND ";
		}
		return $Where;
	}
	
	/**
	 * ISCP筛选WHERE语句
	 *
	 * @return string
	 */
	private function getCp() {
		if ($this->SearchInfo ['iscp']) {
			return $this->DBF->ProductUse->_Table . '.' . $this->DBF->ProductUse->PUID . "='" . $this->SearchInfo ['iscp'] . "' AND ";
		}
	}
	
	/**
	 * 前台显示筛选条件WHERE语句
	 *
	 * @return string
	 */
	private function getFront() {
		if ($this->IsFront) {
			return $this->Product . '.' . $this->DBF->Product->Slabel . "='1' AND ";
		} else {
			if (($this->SearchInfo ['creater'] > 0) || ($this->SearchInfo ['favor'] > 0)) {
				return $this->Product . '.' . $this->DBF->Product->Slabel . "!='2' AND ";
			} else {
				return '';
			}
		}
	}
	
	/**
	 * 上下架筛选条件WHERE语句
	 *
	 * @return string
	 */
	private function getAudit() {
		$Where = '';
		if ($this->checkValue ( $this->SearchInfo ['audit'] )) {
			if (is_array ( $this->SearchInfo ['audit'] )) {
				foreach ( $this->SearchInfo ['audit'] as $Status ) {
					$Where .= $this->Product . '.' . $this->DBF->Product->Slabel . "='" . $this->SearchInfo ['audit'] . "' OR ";
				}
				if (! empty ( $Where )) {
					$Where = substr ( $Where, 0, strlen ( $Where ) - 4 );
				}
				$Where = '(' . $Where . ') AND';
			} else {
			    // miaomin added@2015/7/8
			    if ($this->SearchInfo['audit'] == '2'){
			        // 未发布
			        $Where = $this->Product . '.' . $this->DBF->Product->Slabel . "='0' AND ";
			    }elseif ($this->SearchInfo['audit'] === 'all'){
			        // 不限
			        $Where = '';
			    }else{
			        // 已发布
                    $Where = $this->Product . '.' . $this->DBF->Product->Slabel . "='" . $this->SearchInfo ['audit'] . "' AND ";
			    }
			}
		}
		return $Where;
	}
	
	/**
	 * CheckValue
	 *
	 * @param unknown_type $Value        	
	 * @return boolean
	 */
	private function checkValue($Value) {
		return (isset ( $Value ) && ! empty ( $Value )) || ($Value === 0 || $Value === false || $Value === '0');
	}
}