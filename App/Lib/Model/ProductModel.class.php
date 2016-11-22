<?php
class ProductModel extends Model {
	protected $_map = array (
			'product_name' => 'p_name',
			'cate_1' => 'p_cate_1',
			'cate_2' => 'p_cate_2',
			'is_formal' => 'p_formal',
			'is_choice' => 'p_choice',
			'price' => 'p_price',
			'author' => 'p_author',
			'mini' => 'p_mini',
			'desc' => 'p_intro',
			'tags' => 'p_tags',
			'downloadlimit' => 'p_downloadlimit',
			'source' => 'p_source',
			'p_dvs_pfid' => 'p_dvs_pfid',
			'producttype' => 'p_producttype',
			'product_maintype' => 'p_maintype',
			'product_author' => 'p_creater',
			'product_relation' => 'p_relation',
			'p_diy_cate_cid' => 'p_diy_cate_cid',
	        'unitname' => 'p_unitname',
	        'product_waterproof' => 'p_wpid',
	        'product_onsaleintro' => 'p_onsaleintro',
            'p_key'=>'p_key'

	);
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	/**
	 *
	 * @var DBF_Product
	 */
	public $F;
	function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->Product;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		parent::__construct ();
	}
	
	// old
	public function getModelsInfo() {
		$Models = D ( 'Models' );
		$Models->find ( $this->p_id );
		return $Models;
	}
	public function getProductList($req) {
		$M = M ();
		$result = get_filter ();
		
		if ($result === false) {
			$filter = array ();
			$filter ['keyword'] = empty ( $req ['keyword'] ) ? '' : trim ( $req ['keyword'] );
			if ($req ['is_ajax'] == 1) {
				$filter ['keyword'] = json_str_iconv ( $filter ['keyword'] );
			}
			$filter ['cat_id'] = empty ( $req ['cat_id'] ) ? 0 : intval ( $req ['cat_id'] );
			$filter ['sort_by'] = empty ( $req ['sort_by'] ) ? 'a.p_id' : trim ( $req ['sort_by'] );
			$filter ['sort_order'] = empty ( $req ['sort_order'] ) ? 'DESC' : trim ( $req ['sort_order'] );
			
			$where = '';
			/* 关键字查询 */
			if (! empty ( $filter ['keyword'] )) {
				$where = " AND a.p_name LIKE '%" . mysql_like_quote ( $filter ['keyword'] ) . "%'";
			}
			
			if ($filter ['cat_id']) {
				$sql = "SELECT pc_id FROM tdf_product_cate WHERE pc_parentid = '{$filter ['cat_id']}'";
				$subCates = $M->query ( $sql );
				if (count ( $subCates )) {
					$whereIn = '(';
					foreach ( $subCates as $key => $val ) {
						$whereIn .= $val ['pc_id'] . ',';
					}
					$whereIn = substr ( $whereIn, 0, - 1 );
					$whereIn .= ')';
					$where .= " AND (a.p_cate_1= '{$filter ['cat_id']}' OR a.p_cate_1 IN " . $whereIn . ") ";
				} else {
					$where .= " AND a.p_cate_1=" . $filter ['cat_id'];
				}
			}
			
			/* 产品内容总数 */
			$sql = "SELECT COUNT(*) FROM tdf_product AS a " . "LEFT JOIN tdf_product_cate AS ac ON ac.pc_id = a.p_cate_1 " . " WHERE 1 " . $where;
			$filter ['record_count'] = get_one ( $M->query ( $sql ) );
			$filter = page_and_size ( $filter );
			
			/* 获取产品内容数据 */
			$sql = "SELECT a.*, ac.pc_name FROM tdf_product AS a " . "LEFT JOIN tdf_product_cate AS ac ON ac.pc_id = a.p_cate_1 " . " WHERE 1 " . $where . " ORDER by " . $filter ['sort_by'] . " " . $filter ['sort_order'];
			if ($filter ['start'] == 0) {
				$sql .= ' LIMIT ' . $filter ['page_size'];
			} else {
				$sql .= ' LIMIT ' . $filter ['start'] . ', ' . $filter ['page_size'];
			}
			
			$filter ['keyword'] = stripslashes ( $filter ['keyword'] );
			set_filter ( $filter, $sql );
		} else {
			$sql = $result ['sql'];
			$filter = $result ['filter'];
		}
		$arr = array ();
		$res = $M->query ( $sql );
		// print_r($res);
		/* 获取产品分类数据 */
		$Cates = D ( 'Cates' );
		$cate_list = $Cates->select ();
		$cate_pk_list = trans_pk_to_key ( $cate_list, 'pc_id' );
		
		for($i = 0; $i < count ( $res ); $i ++) {
			$res [$i] ['p_price'] = number_format ( $res [$i] ['p_price'] );
			$res [$i] ['pc_name_2'] = $cate_pk_list [$res [$i] ['p_cate_2']] ['pc_name'];
			$arr [] = $res [$i];
		}
		return array (
				'arr' => $arr,
				'filter' => $filter,
				'page_count' => $filter ['page_count'],
				'record_count' => $filter ['record_count'] 
		);
	}
	
	// Zerock @2013/03/04
	public function getProductByID($PID) {
		$Product = $this->where ( "p_id='" . $PID . "'" )->select ();
		return $Product !== false ? $Product ? $Product [0] : null : false;
	}
	
	/**
	 * 获取非DIY类商品信息
	 *
	 * @param int $PID        	
	 */
	public function getNoneDiyProductInfoByID($PID) {
		$sql = "SELECT P.*,PT.ipt_name,U.u_avatar,U.u_dispname,U.u_realname FROM tdf_product P LEFT JOIN tdf_info_producttype PT ON (PT.ipt_id = P.p_maintype) LEFT JOIN tdf_users U ON (U.u_id = P.p_creater) WHERE P.p_id = '{$PID}'";
        $res = $this->query ( $sql );
		return $res [0];
	}
	
	/**
	 * 获取非DIY类商品信息
	 *
	 * @param array $pidArr        	
	 */
	public function getNoneDiyProductInfoByIDArr($PIDARR, $fields = null) {
		$idWhere = '';
		$fieldsStr = '';
		
		foreach ( $PIDARR as $key => $val ) {
			$idWhere .= 'P.p_id="' . $val . '" OR ';
		}
		
		if (substr ( $idWhere, - 4 ) == ' OR ') {
			$idWhere = substr ( $idWhere, 0, - 4 );
		}
		
		if (is_array ( $fields )) {
			foreach ( $fields as $key => $val ) {
				$fieldsStr .= 'P.' . $val . ',';
			}
			
			if (substr ( $fieldsStr, - 1 ) == ',') {
				$fieldsStr = substr ( $fieldsStr, 0, - 1 );
			}
		} else {
			$fieldsStr = 'P.*';
		}
		
		$sql = "SELECT " . $fieldsStr . ",PT.ipt_name,U.u_avatar,U.u_dispname,U.u_realname FROM tdf_product P LEFT JOIN tdf_info_producttype PT ON (PT.ipt_id = P.p_maintype) LEFT JOIN tdf_users U ON (U.u_id = P.p_creater) WHERE " . $idWhere;
		$res = $this->query ( $sql );
		
		return $res;
	}
	public function getProductInfoByID($PID) {
		$DBF = new DBF ();
		$Product = $DBF->Product->_Table;
		$P_ID = $Product . '.' . $DBF->Product->ID;
		
		$ProductModel = $DBF->ProductModel->_Table;
		$PM_PID = $ProductModel . '.' . $DBF->ProductModel->ProductID;
		
		$ProductFile = $DBF->ProductFile->_Table;
		$PF_PID = $ProductFile . '.' . $DBF->ProductFile->ProductID;
		$PF_CreateTool = $ProductFile . '.' . $DBF->ProductFile->CreateTool;
		
		$this->join ( $ProductModel . ' ON ' . $P_ID . ' = ' . $PM_PID );
		$Result = $this->where ( $P_ID . "='" . $PID . "'" )->select ();
		if ($Result) {
			$Result = $Result [0];
			// getFileList
			$PIDWhere = '';
			$ProductFile = $DBF->ProductFile->_Table;
			$PCF_PID = $ProductFile . '.' . $DBF->ProductFile->ProductID;
			$PIDWhere .= $PCF_PID . "='" . $PID . "'";
			$FileList = $this->getFileList ( $PIDWhere );
			$Result ['filelist'] = $FileList;
		}
		return $Result;
	}
	
	/*
	 * $cid类别ID $p_lictype 收费还是免费
	 */
	public function getSaleModelsNum($cid, $p_lictype = 0) { // 根据分cateid返回模型的数量
		$where = "";
		if ($p_lictype == 1) { // 是否收费
			$where .= "p_lictype=1 and p_price>0 and p_slabel=1 ";
			$pc_name = "商店";
		} else {
			$where .= "p_lictype<>1 and p_price=0 and p_slabel=1 ";
		}
		if ($cid > 1) {
			$TPC = new CatesModel ();
			$catearr = $TPC->getCategoryByCID ( $cid );
			$pc_name = $catearr ['pc_name'];
			if ($catearr ['pc_parentid'] == 1) {
				$subCate = $TPC->getCateList ( $cid, $cid, false );
				$where .= "and (";
				foreach ( $subCate as $key => $value ) {
					$where .= "p_cate_1=" . $value ['pc_id'] . " or ";
				}
				$where = substr ( $where, 0, strlen ( $where ) - 3 );
				$where .= " ) ";
			} else {
				$where .= "and p_cate_1=" . $cid . " ";
			}
		}
		if ($cid == 1) {
			$pc_name = "全部";
		}
		$result ['pc_name'] = $pc_name;
		$result ['nums'] = $this->where ( $where )->count ( 'p_id' );
		return $result;
	}
	private function getFileList($PIDWhere) {
		$DBF = new DBF ();
		$PFM = new ProductFileModel ();
		$ProductFile = $DBF->ProductFile->_Table;
		$PF_PID = $ProductFile . '.' . $DBF->ProductFile->ProductID;
		$PF_CreateTool = $ProductFile . '.' . $DBF->ProductFile->CreateTool;
		
		$ProductCreateTool = $DBF->ProductCreateTool->_Table;
		$PCT_ID = $ProductCreateTool . '.' . $DBF->ProductCreateTool->ID;
		
		$PFM->join ( $ProductCreateTool . ' ON ' . $PF_CreateTool . ' = ' . $PCT_ID );
		$Result = $PFM->where ( $PIDWhere )->select ();
		return $Result;
	}
	
	/**
	 * 获得用于DVS的模型信息 老版本，只能下载fbx
	 */
	public function getDVSProductInfoByID($PID) {
		$DBF = new DBF ();
		$Product = $DBF->Product->_Table;
		$P_ID = $Product . '.' . $DBF->Product->ID;
		$ProductModel = $DBF->ProductModel->_Table;
		$PM_PID = $ProductModel . '.' . $DBF->ProductModel->ProductID;
		$ProductFile = $DBF->ProductFile->_Table;
		$PF_PID = $ProductFile . '.' . $DBF->ProductFile->ProductID;
		$PF_CreateTool = $ProductFile . '.' . $DBF->ProductFile->CreateTool;
		$this->join ( $ProductModel . ' ON ' . $P_ID . ' = ' . $PM_PID );
		$Result = $this->where ( $P_ID . "='" . $PID . "'" )->select ();
		if ($Result) {
			$Result = $Result [0];
			$PIDWhere = '';
			$ProductFile = $DBF->ProductFile->_Table;
			$PCF_PID = $ProductFile . '.' . $DBF->ProductFile->ProductID;
			$PIDWhere = "pct_id=" . $Result ['p_dvs_pfid'] . "";
			$FileList = $this->getfileByPfid ( $Result ['p_dvs_pfid'] );
			// var_dump($FileList);
			if (! $FileList) { // 如果无记录，证明没有关联dvs文件
				$Result ['pct_ext'] = "未关联文件";
			}
			if ($FileList) {
				$Result = array_merge ( $FileList, $Result );
			}
		}
		// var_dump($Result);
		if ($Result ['p_lictype'] == 1) {
			$UP = new UserDealsModel ();
			$Result ['isbuy'] = $UP->getIsbuyByUidPid ( $_SESSION ['f_userid'], $PID );
		} else {
			$UP = new UserDealsVcoinModel ();
			$Result ['isbuy'] = $UP->getIsbuyByUidPid ( $_SESSION ['f_userid'], $PID );
		}
		$MC = new CatesModel ();
		$catearr = $MC->getCategoryByCID ( $Result ['p_cate_1'] );
		$parentcatearr = $MC->getCategoryByCID ( $catearr ['pc_parentid'] );
		$Result ['cateName'] = $catearr ['pc_name'];
		$Result ['parentcateName'] = $parentcatearr ['pc_name'];
		$Result ['p_name'] = getname ( $Result ['p_name'] );
		return $Result;
	}
	
	/**
	 * 获得用于DVS的模型信息 新版本beta
	 */
	public function getDVSProductInfoByID_beta($PID) {
		$DBF = new DBF ();
		$Product = $DBF->Product->_Table;
		$P_ID = $Product . '.' . $DBF->Product->ID;
		$ProductModel = $DBF->ProductModel->_Table;
		$PM_PID = $ProductModel . '.' . $DBF->ProductModel->ProductID;
		$ProductFile = $DBF->ProductFile->_Table;
		$PF_PID = $ProductFile . '.' . $DBF->ProductFile->ProductID;
		$PF_CreateTool = $ProductFile . '.' . $DBF->ProductFile->CreateTool;
		$this->join ( $ProductModel . ' ON ' . $P_ID . ' = ' . $PM_PID );
		$Result = $this->where ( $P_ID . "='" . $PID . "'" )->select ();
		/*
		 * if($Result) {	$Result = $Result[0]; $PIDWhere = ''; $ProductFile =
		 * $DBF->ProductFile->_Table; $PCF_PID = $ProductFile . '.' .
		 * $DBF->ProductFile->ProductID;
		 * $PIDWhere="pct_id=".$Result['p_dvs_pfid'].""; $FileList =
		 * $this->getfileByPfid($Result['p_dvs_pfid']); //var_dump($FileList);
		 * if(!$FileList){//如果无记录，证明没有关联dvs文件 $Result['pct_ext']="未关联文件"; }
		 * if($FileList){$Result = array_merge($FileList,$Result);} }
		 */
		// <--------获得文件列表
		if ($Result) {
			$Result = $Result [0];
			// getFileList
			$PIDWhere = '';
			$ProductFile = $DBF->ProductFile->_Table;
			$PCF_PID = $ProductFile . '.' . $DBF->ProductFile->ProductID;
			$PIDWhere .= $PCF_PID . "='" . $PID . "'";
			$FileList = $this->getFileList ( $PIDWhere );
			$Result ['filelist'] = $FileList;
		}
		// --------获得文件列表 >
		
		// 是否允许直接下载
		$UOPM = new UserOwnProductModel ();
		if ($UOPM->IsUserBuyProduct ( $_SESSION ['f_userid'], $PID )) {
			$Result ['isbuy'] = 1;
		} else {
			$Result ['isbuy'] = 0;
		}
		
		$MC = new CatesModel ();
		$catearr = $MC->getCategoryByCID ( $Result ['p_cate_1'] );
		$parentcatearr = $MC->getCategoryByCID ( $catearr ['pc_parentid'] );
		
		$Result ['cateName'] = $catearr ['pc_name'];
		$Result ['parentcateName'] = $parentcatearr ['pc_name'];
		$Result ['parentid'] = $parentcatearr ['pc_parentid'];
		$Result ['p_name'] = getname ( $Result ['p_name'] );
		
		return $Result;
	}
	public function getfileByPfid($pfid) { // 根据pfid得到file信息
		$PF = M ();
		$sql = "select TPF.pf_id,TPF.pf_filesize,TPF.pf_filesize_disp,TPF.pf_path,TPF.pf_ext,TPF.pf_downloads,TPC.pct_name,TPC.pct_ext,TPF.pf_filename ";
		$sql .= "from tdf_product_file as TPF Left Join tdf_product_createtool as TPC On TPC.pct_id=TPF.pf_createtool where TPF.pf_id=" . $pfid;
		$result = $PF->query ( $sql );
		return $result [0];
	}
	public function getProductsByIDList($IDList) {
		load ( '@.WhereBuilder' );
		$WB = new WhereBuilder ();
		$Where = $WB->addIn ( $this->F->ID, $IDList )->getWhere ();
		$Products = $this->where ( $Where )->select ();
		$Products = array_column ( $Products, null, $this->F->ID );
		return $Products;
	}
	public function getPrinterIndex($cateid, $pnum = 8) { // 获取所有的打印机,按照p_dispweight排列
		$Products = $this->field ( 'p_id,p_name,p_creater,p_cover,p_cover_id,p_slabel,p_price' )->where ( "p_cate_3=" . $cateid . " and p_slabel=1" )->limit ( $pnum )->order ( p_dispweight )->select ();
		return $Products;
		// var_dump($Products);
	}
	public function getPrinterIndexChoice() { // 获取推荐的打印机,即显示在商店幻灯片上的打印机
		$Products = $this->field ( 'p_id,p_name,p_creater,p_intro,p_author,p_cover,p_cover_id,p_slabel' )->where ( 'p_cate_3>0 and p_slabel=1 and p_choice=1' )->limit ( 3 )->order ( p_dispweight )->select ();
		var_dump ( $Products );
	}
	
	// 根据PID获得商品的详细信息
	public function getShoppingInfoByID($PID) {
		$DBF = new DBF ();
		$Product = $DBF->Product->_Table;
		$P_ID = $Product . '.' . $DBF->Product->ID;
		$Result = $this->where ( $P_ID . "='" . $PID . "'" )->select ();
		if ($Result) {
			$PPM = new ProductPhotoModel ();
			$Result ['photolist'] = $PPM->getPhotosByPID ( $PID );
		}
		return $Result;
	}
	
	// zhangzhibin @2014/10/08
	public function getProductByDiyid($id) {
		$Product = $this->where ( "p_diy_id='" . $id . "'" )->select ();
		return $Product !== false ? $Product ? $Product [0] : null : false;
	}


    // zhangzhibin @2016/02/24
    public function getUdidByPid($pid) {
        $Product = $this->where ( "p_id='" . $pid . "'" )->select ();
        $Product = $Product ? $Product [0] : null;
        return $Product['p_diy_id'];
    }

	// zhangzhibin @2016/02/24
	public function getPidByUdid($udid) {
		$Product = $this->where ( "p_diy_id=" . $udid . "" )->select ();
		$Product = $Product ? $Product [0] : null;
		return $Product['p_id'];
	}

    //根据订单的商品ID数组返回商品名称字串
    public function getProductNameByArr($pidArr){
      //  var_dump($pidArr);
         foreach($pidArr as $key => $value){
            $pidArr=explode(',',$value);
            $pid=$pidArr[0];
            $productInfo=$this->getProductByID($pid);
            if($productInfo['p_producttype']==4){
                $sql = "select TDC.cate_name from tdf_user_diy as TUD ";
                $sql .= "Left Join tdf_diy_cate as TDC On TDC.cid=TUD.cid ";
                $sql .= "where TUD.id=" . $productInfo['p_diy_id'] . " ";
                $diyCateInfo = M()->query($sql);
                $result=$result.$diyCateInfo[0]['cate_name'].",";
            }else{
                $result=$result.$productInfo['p_name'].",";
            }
        }
        $result= substr($result,0,strlen($result)-1);
        return $result;
    }
	
	/**
	 * 获取所属商品列表
	 *
	 * @param int $pid        	
	 */
	public function getBelongProductList($pid) {
		$res = '';
		$condition = array (
				$this->F->BelongPid => $pid 
		);
		$res = $this->where ( $condition )->select ();
		return $res;
	}
	
	/**
	 * 获取上架的商品列表
	 *
	 * @param int $pid        	
	 */
	public function getBelongAvProductList($pid) {
		$res = '';
		$condition = array (
				$this->F->BelongPid => $pid,
				$this->F->Slabel => 1 
		);
		$res = $this->where ( $condition )->select ();
		return $res;
	}
	
	// zhangzhibin @2014/12/16 通过p_diy_cate_cid获取product记录
	public function getProductByDiyCateCid($id) {
		$Product = $this->where ( "p_diy_cate_cid='" . $id . "'" )->select ();
		return $Product !== false ? $Product ? $Product [0] : null : false;
	}
	
	/**
	 * 获取可以显示在前台的商品数量
	 *
	 * @param int $pid        	
	 */
	public function getTotalProductNum() {
		$condition = array (
				$this->F->ProductType => array (
						'in',
						array (
								'5',
								'7' 
						) 
				),
				$this->F->BelongPid => 0,
				$this->F->Slabel => 1 
		);
		
		return $this->where ( $condition )->count ();
	}
	
	/**
	 * 获取关联商品信息
	 *
	 * @author miaomin
	 * @param int $pid        	
	 */
	public function getRelationProduct($pid) {
		
		// 返回结果
		$res = array ();
		
		// 商品信息
		$pmRes = $this->getNoneDiyProductInfoByID ( $pid );
		
		// 关联商品
		if ($pmRes [$this->F->Relation]) {
			$idArr = explode ( ',', $pmRes [$this->F->Relation] );
			
			$fieldsArr = array (
					$this->F->ID,
					$this->F->Name,
					$this->F->Price,
					$this->F->Cover,
			);
			
			$relationPmRes = $this->getNoneDiyProductInfoByIDArr ( $idArr, $fieldsArr );
			
			foreach ( $relationPmRes as $key => $val ) {
				if ($key == 0) {
					$relationPmRes [$key] ['p_default'] = 1;
				} else {
					$relationPmRes [$key] ['p_default'] = 0;
				}
			}
			
			return $relationPmRes;
		}
		
		return $res;
	}

    //根据diy_cate_id获得diy产品的封面图(cover)
    public function getProductCover($diy_cate_id){
        $productInfo=$this->getProductByDiyCateCid($diy_cate_id);
        return $productInfo['p_cover'];
    }

    /**
     * 点赞
     */
    public function zanAdd($uid,$pid,$ip){
        $UZAN = new UserZanModel ();
        if($UZAN->addZanNew($uid,$pid,$ip)){
            $result = $this->productAddZans($pid);
        }else{
            $result=0;
        }
        return $result;
    }

    public function productAddZans($pid){
        $PM=new ProductModel();
        if($PM->find($pid)){
            $result=$PM->where("p_id=".$pid."")->setInc("p_zans");
        }else{
            $result=0;
        }
        return $result;
    }

    public function getPidByPkey($pKey){
        $productInfo= $this->where("p_key='".$pKey."'")->find();
        $result=$productInfo?$productInfo['p_id']:0;
        return $result;
    }

    public function getProductinfoByPkey($pKey){
        $productInfo= $this->where("p_key='".$pKey."'")->find();
        $result=$productInfo?$productInfo:'';
        return $result;
    }

	/**
	 * @param $pid
	 * @return string
	 */
	public function getProductinfoByPid($pkey){
		$productInfo= $this->where("p_key='".$pkey."'")->find();
		$result=$productInfo?$productInfo:'';
		return $result;
	}
	/**
	 * 由diy_id生成订单,并从tdf_user_cart中删除对应的pid记录
	 */
	public function productToOrder($datas){
		$productArr=explode(",",$datas['detail']);
		$TUD    = new UserDiyModel();
		foreach($productArr as $key => $value){
			$productInfo                = explode(":",$value);
			$prodAllInfo                = $this->where("p_diy_id='".$productInfo[0]."'")->find();
			$product[$key]              = $prodAllInfo;
			$product[$key]['count']     = $productInfo[1];
			$product[$key]['pidCount']  = $prodAllInfo['p_id'].",".$productInfo[1];
			$up_productid[$key]         = $prodAllInfo['p_id'].",".$productInfo[1];
			if($productInfo[2] == $prodAllInfo['p_price']){//价格判断
				$product[$key]['price'] = $productInfo[2] ;
				$up_amount      = $up_amount + $productInfo[2] * $productInfo[1];
				$up_amount_res  = 1;
				$diy_info       = $TUD->getUserDiyInfoById($productInfo[0]);
				$product[$key]['diy_unit_info']     = serialize($diy_info) ;//加入一个购物车产品类型 4为diy
				$product[$key]['uc_producttype']    = 4 ;//加入一个购物车产品类型 4为diy
			}else{
				$up_amount_res  = 0;
			}
		}
		$datas['up_amount']     = $up_amount;   //总价
		$datas['up_productid']  = $up_productid;
		$datas['product']       = $product;
		if(!$up_amount_res){//价格判断不对
			$upresult   = -1;
		}else{
			$UCM        = new UserPrepaidModel();
			$upresult   = $UCM->addOrderAPI($datas);//生成订单记录
		}
		return $upresult;
	}



}
?>