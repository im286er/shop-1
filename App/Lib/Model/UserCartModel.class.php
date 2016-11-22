<?php
class UserCartModel extends Model {
	
	// $_map
	protected $_map = array (
			'pid' => 'p_id',
			'uid' => 'u_id',
			'count' => 'uc_count',
			'producttype' => 'uc_producttype',
			'realobject' => 'uc_isreal',
			'bindids' => 'uc_bindids',
			'isbind' => 'uc_isbind',
			'masterid' => 'uc_masterid',
			'handleuc' => 'uc_handleuc' 
	);
	
	// $_auto
	protected $_auto = array (
			array (
					'uc_lastupdate',
					'get_now',
					3,
					'function' 
			),
			
			array (
					'uc_ctime',
					'time',
					3,
					'function' 
			) 
	);
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	/**
	 *
	 * @var DBF_UserCart
	 */
	public $F;
	
	/**
	 * UserCart
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->UserCart;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
	
	/**
	 * 获取购物车数量
	 *
	 * @author miaomin
	 * @param int $UID        	
	 * @return mixed
	 */
	public function getCartNum($UID) {
		$DBF_P = $this->DBF->Product;
		$P_ID = $DBF_P->_Table . '.' . $DBF_P->ID;
		$UC_PID = $this->F->_Table . '.' . $this->F->ProductID;
		return $this->join ( $DBF_P->_Table . " ON " . $P_ID . "=" . $UC_PID )->where ( $this->F->UserID . "='" . $UID . "'" )->count ();
	}
	
	/**
	 * 获取购物车数据
	 *
	 * @author miaomin
	 * @param int $UID        	
	 * @return mixed
	 */
	public function getProduct($UID) {
		$DBF_P = $this->DBF->Product;
		$P_ID = $DBF_P->_Table . '.' . $DBF_P->ID;
		$UC_PID = $this->F->_Table . '.' . $this->F->ProductID;
		return $this->join ( $DBF_P->_Table . " ON " . $P_ID . "=" . $UC_PID )->where ( $this->F->UserID . "='" . $UID . "'" )->order ( 'uc_producttype desc' )->select ();
	}
	
	/**
	 * 对捆绑ids做出有小到大的排序处理
	 *
	 * @param string $ids        	
	 * @return string
	 */
	private function _sortBindIds($ids) {
		$idsArr = explode ( ',', $ids );
		asort ( $idsArr );
		return arrayTransToStr ( $idsArr );
	}
	
	/**
	 * 重写为购物车增加一个ITEM
	 *
	 * @author miaomin
	 * @param AbstractCartItem $cim        	
	 * @param int $uid        	
	 * @return boolean
	 */
	public function addItem(AbstractCartItem $cim, $uid) {
		$cimArgs = $cim->getArgs ();
		
		// add uid
		$cimArgs ['uid'] = $uid;
		
		// add producttype
		$PM = new ProductModel ();
		$pmRes = $PM->find ( $cimArgs ['pid'] );
		$cimArgs ['producttype'] = $pmRes [$PM->F->ProductType];
		
		// 处理捆绑
		if ($cimArgs ['bindids'] != '') {
			$cimArgs ['isbind'] = 1;
			// 排序bindids
			$cimArgs ['bindids'] = $this->_sortBindIds ( $cimArgs ['bindids'] );
		}
		
		// 处理被捆绑
		if ($cimArgs ['masterid'] != '') {
			$cimArgs ['isbind'] = 2;
		}
		
		// set Args
		$cim->setArgs ( $cimArgs );
		// 排重检查
        $isAlreadyAdd=$cim->isAlreadyAdd ();
		if (! $isAlreadyAdd) {
			return $cim->add();
		}else{
            $UCM=new UserCartModel();
            if($UCM->where("uc_id=".$isAlreadyAdd[0]['uc_id']."")->setInc("uc_count")){
                return $isAlreadyAdd[0]['uc_id'];
            }
        }
		
		return false;
	}
	
	/**
	 * 重写为购物车移除一个ITEM
	 *
	 * @author miaomin
	 * @param AbstractCartItem $cim        	
	 * @param unknown_type $uid        	
	 * @return boolean
	 */
	public function removeItem(AbstractCartItem $cim, $uid) {
		$cimArgs = $cim->getArgs ();
		
		// add uid
		$cimArgs = $cim->getArgs ();
		$cimArgs ['uid'] = $uid;
		
		// add producttype
		$PM = new ProductModel ();
		$pmRes = $PM->find ( $cimArgs ['pid'] );
		$cimArgs ['producttype'] = $pmRes [$PM->F->ProductType];
		
		// set Args
		$cim->setArgs ( $cimArgs );
		
		return $cim->remove();
	}
	
	/**
	 * 清空购物车
	 *
	 * @author miaomin
	 * @param int $uid        	
	 *
	 */
	public function clearCart($uid) {
		$condition = array (
				$this->F->UserID => $uid 
		);
		$this->where ( $condition )->delete ();
		
		// 删除user_printmodel
		$UPMM = new UserPrintModelsModel ();
		$condition = array (
				$UPMM->F->UID => $uid,
				$UPMM->F->INCART => 0 
		);
		$UPMM->where ( $condition )->delete ();
	}
	
	/**
	 * 增加一个购物车产品(旧)
	 */
	public function addProduct($PID, $UID) {
		$PM = new ProductModel ();
		$Product = $PM->getProductByID ( $PID );
		if ($Product === false) {
			return false;
		}
		if ($Product === null) {
			return null;
		}
		$Exists = $this->isProductExists ( $PID, $UID );
		if ($Exists === false) {
			return false;
		}
		if ($Exists) {
			return true;
		} else {
			$this->{$this->F->ProductID} = $PID;
			$this->{$this->F->UserID} = $UID;
			$this->{$this->F->Count} = 1;
			// 这边留一个判断如果是购买打印件则直接使用ProductType如果购买数字模型则调整ProductType
			$this->{$this->F->ProductType} = $Product [$PM->F->ProductType];
			if ($this->add () === false) {
				return false;
			} else {
				return true;
			}
		}
	}
	
	public function addProduct_diy($PID, $UID) { // diy加入购物车
		$PM = new ProductModel ();
		$Product = $PM->getProductByID ( $PID );
		if ($Product === false) {
			return false;
		}
		if ($Product === null) {
			return null;
		}
		$Exists = $this->isProductExists ( $PID, $UID );
		if ($Exists === false) {
			return false;
		}
		if ($Exists) {
			return true;
		} else {
			$this->{$this->F->ProductID} = $PID;
			$this->{$this->F->UserID} = $UID;
			$this->{$this->F->Count} = 1;
			$this->{$this->F->ProductType} = $Product [$PM->F->ProductType];
			if ($this->add () === false) {
				return false;
			} else {
				return true;
			}
		}
	}
	public function deleteProduct($PID, $UID) {
		$Result = $this->where ( $this->F->UserID . "='" . $UID . "' AND " . $this->F->ProductID . "='" . $PID . "'" )->delete ();
		if (!$Result ){
			return false;
		}else{
			return true;
		}
	}
	
	/**
	 * 排重
	 *
	 * @author jzy,miaomin(重写,重写部分已废弃)
	 *         2014.11.5
	 * @param int $PID        	
	 * @param int $UID        	
	 * @param AbstractCartItem $cim        	
	 * @return boolean NULL
	 */
	public function isProductExists($PID, $UID, AbstractCartItem $cim = NULL) {
		$productTypeArr = C ( 'PRODUCT.TYPE' );
		$productRealArr = C ( 'PRODUCT.ISREAL' );
		
		$PM = new ProductModel ();
		$pmRes = $PM->find ( $PID );
		
		if ($pmRes [$PM->F->ProductType] == $productTypeArr ['PRINTMODEL']) {
			
			if ($cim == null) {
				
				return true;
			}
			
			// 新排重逻辑
			$cimArgs = $cim->getArgs ();
			
			if ($cimArgs ['realobject'] == $productRealArr ['VIRTUAL']) {
				// 虚拟物品
				$condition = array (
						$this->F->ProductID => $PID,
						$this->F->UserID => $UID,
						$this->F->IsReal => $cimArgs ['realobject'] 
				);
				
				$Result = $this->where ( $condition )->select ();
			} else {
				// 实体物品
				$condition = array (
						$this->F->ProductID => $PID,
						$this->F->UserID => $UID,
						$this->F->IsReal => $cimArgs ['realobject'] 
				);
				
				$Result = $this->where ( $condition )->select ();
				
				if ($Result) {
					
					$UPMM = new UserPrintModelsModel ();
					
					$condition = array (
							$UPMM->F->PID => $PID,
							$UPMM->F->UID => $UID,
							$UPMM->F->INCART => 0,
							$UPMM->F->PMAID => $cimArgs ['pma_id'],
							$UPMM->F->PMDID => $cimArgs ['pmd_id'],
							$UPMM->F->LENGTH_PRINT => $cimArgs ['print_length'],
							$UPMM->F->WIDTH_PRINT => $cimArgs ['print_width'],
							$UPMM->F->HEIGHT_PRINT => $cimArgs ['print_height'] 
					);
					
					$Result = $UPMM->where ( $condition )->select ();
				}
			}
		} else {
			// 旧排重逻辑
			$Result = $this->where ( $this->F->UserID . "='" . $UID . "' AND " . $this->F->ProductID . "='" . $PID . "'" )->select ();
		}
		
		if ($Result) {
			return true;
		}
		if ($Result === false) {
			return false;
		}
		if ($Result === null) {
			return null;
		}
		return false;
	}
	public function updateCartCount($pid, $count) { // 更新购物车中的数量
		$data ['uc_count'] = $count;
		return $this->where ( $this->F->ProductID . "='" . $pid . "'" )->setField ( $data );
	}
	
	/**
	 * 更新购物车数量
	 *
	 * @author miaomin
	 *        
	 * @param int $ucid        	
	 * @param int $count        	
	 */
	public function updateItemCount($ucid, $count) {
		$condition = array (
				$this->F->ID => $ucid
		);
		
		$data = array (
				$this->F->Count => $count 
		);
		$this->where ( $condition )->save ( $data );
	}
	
	/**
	 * 获取购物车列表信息
	 */
	public function getCartItemList($u_id=0) {
		$res = array ();
		import ( 'App.Model.CartItem.AbstractCartItem' );
		import ( 'App.Model.CartItem.FactoryCartItemModel' );
		import ( 'App.Model.CartItem.CartItemRealPrintModel' );
		import ( 'App.Model.CartItem.CartItemVirtualPrintModel' );
		import ( 'App.Model.CartItem.CartItemNoneDiyModel' );
		import ( 'App.Model.CartItem.CartItemDiyModel' );
		
		$producttypeArr = C ( 'PRODUCT.TYPE' );
		$UID = $u_id?$u_id:session ( 'f_userid' );
		$UCM = new UserCartModel ();
		$UDM = new UserDiyModel();

		$ProductList = $UCM->getProduct ( $UID );
		
		$TotalPrice = 0;
		$RenderCartItemList = array ();
		
		// 先做删选如果PID不存在则必须从购物车清空该商品
		foreach ( $ProductList as $key => $val ) {
			if (! $val [$UCM->F->ProductID]) {
				unset ( $ProductList [$key] );
				$ProductList = array_values ( $ProductList );
			}
		}
		
		// 组织捆绑结构
		foreach ( $ProductList as $key => $Product ) {
			// 捆绑销售类商品
			if ($Product ['uc_isbind'] == 1) {
				foreach ( $ProductList as $k => $v ) {
					if (($v ['uc_isbind'] == 2) && ($v ['uc_masterid'] == $Product ['p_id']) && ($v ['uc_handleuc'] == $Product ['uc_id'])) {
						$ProductList [$key] ['binditems'] [] = $v;
						unset ( $ProductList [$k] );
					}
				}
			}
			

		}

		$ProductList = array_values ( $ProductList );
		
		// 渲染购物车显示效果
		foreach ( $ProductList as $key => $Product ) {
		    //echo 'PTYPE: ' . $Product ['p_producttype'];
		    //echo '<br><br>';
			if ($Product ['p_producttype'] == 2) {
				$isexpress = 1;
				$ProductList [$key] ['modellink'] = "shop";
				$TotalPrice += $Product [$this->DBF->Product->Price];
			} elseif ($Product ['p_producttype'] == 4) {
				// DIY
				// 处理活动优惠
				$isexpress = 1;
				$ProductList [$key] ['modellink'] = "diy";
				$CIF = FactoryCartItemModel::init($Product);
				//var_dump($CIF);
				$cifArgs = $CIF->getArgs();
				$cifArgs ['uid'] = $Product ['u_id'];
				$CIF->setArgs($cifArgs);
				$ProductList [$key] ['cartitem'] = $CIF->renderIndex();
				$ProductList [$key] ['p_spprice'] = $CIF->calcprice();
				$TotalPrice += $CIF->amount();
				// $TotalPrice += $Product [$this->DBF->Product->Price];
			/******增加DIY商品的显示详情**************start*/
				$userDiyInfo=$UDM->getUserDiyInfoById($Product['p_diy_id']);
				//var_dump($userDiyInfo);
				//exit;
				$Product['diy_unit_info']=$userDiyInfo['diy_unit_info'];//加入diy的值信息到$Product中用于下面的getUserCartDiyByProduct方法
				$ProductList [$key] ['diy_unit_info']=$userDiyInfo['diy_unit_info'];
				if($Product['p_cate_4']==1){
					$ProductList [$key]['p_description']="简笔画";
				}else{
					$ProductList [$key]['p_description']=$UCM->getUserCartDiyByProduct($Product);
				}
			/******增加DIY商品的显示详情**************end*/
			} elseif ($Product ['p_producttype'] == $producttypeArr ['NDIY']) {
				// 非DIY类商品
			    // 处理活动优惠
				$ProductList [$key] ['modellink'] = "product";
				$CIF = FactoryCartItemModel::init ( $Product );
				//var_dump($CIF);
				$cifArgs = $CIF->getArgs ();
				$cifArgs ['uid'] = $Product ['u_id'];
				$CIF->setArgs ( $cifArgs );
				$ProductList [$key] ['cartitem'] = $CIF->renderIndex (';');
				$ProductList [$key] ['p_spprice'] = $CIF->calcprice();
				$TotalPrice += $CIF->amount ();
			} elseif ($Product ['p_producttype'] == $producttypeArr ['PRINTMODEL']) {
				// 3D打印模型实物商品
				$ProductList [$key] ['modellink'] = "models";
				$CIF = FactoryCartItemModel::init ( $Product );
				$cifArgs = $CIF->getArgs ();
				$cifArgs ['uid'] = $Product ['u_id'];
				$CIF->setArgs ( $cifArgs );
				$ProductList [$key] ['cartitem'] = $CIF->renderIndex ();
				$TotalPrice += $CIF->amount ();
			} else {
				$ProductList [$key] ['modellink'] = "models";
				$TotalPrice += $Product [$this->DBF->Product->Price];
			}
		}
		
		// 处理捆绑商品
		foreach ( $ProductList as $key => $Product ) {
			if (key_exists ( 'binditems', $Product )) {
				foreach ( $Product ['binditems'] as $k => $BindProduct ) {
					if ($BindProduct ['p_producttype'] == 4) {
						// DIY
						$isexpress = 1;
						$ProductList [$key] ['binditems'] [$k] ['modellink'] = "diy";
						$TotalPrice += $BindProduct [$this->DBF->Product->Price];
						$ProductList [$key] ['p_price'] += $BindProduct [$this->DBF->Product->Price];
					} elseif ($BindProduct ['p_producttype'] == $producttypeArr ['NDIY']) {
						// 非DIY类商品
						$ProductList [$key] ['binditems'] [$k] ['modellink'] = "product";
						$CIF = FactoryCartItemModel::init ( $BindProduct );
						$cifArgs = $CIF->getArgs ();
						$cifArgs ['uid'] = $Product ['u_id'];
						$CIF->setArgs ( $cifArgs );
						$ProductList [$key] ['binditems'] [$k] ['cartitem'] = $CIF->renderIndex ( '/' );
						$TotalPrice += $CIF->amount ();
						$ProductList [$key] ['p_price'] += $BindProduct['p_price'];
					}
				}
			}

		}
		
		$res ['list'] = $ProductList;
		$res ['isexpress'] = $isexpress;
		$res ['totalprice'] = $TotalPrice;
		return $res;
	}


    /**
     * 获取购物车数据
     *
     * @author miaomin
     * @param int $UID
     * @return mixed
     */
    public function getProductByUcid($UID,$ucidString) {
        $DBF_P = $this->DBF->Product;
        $P_ID = $DBF_P->_Table . '.' . $DBF_P->ID;
        $UC_PID = $this->F->_Table . '.' . $this->F->ProductID;
        $result=$this->join ( $DBF_P->_Table . " ON " . $P_ID . "=" . $UC_PID )->where ( $this->F->UserID . "='" . $UID . "' and uc_id in (".$ucidString.")" )->order ( 'uc_producttype desc' )->select ();
        return $result;
    }

    /**
     * 将购物车提交到订单需要处理的商品数据作为一个函数来处理
     * 目的是为了压缩CartAction.class.php中pay函数的代码量
     * 
     * @author miaomin@2015.10.28
     * @param array $productlist 从购物车中获取的商品列表数据 getProduct($uid)
     * @return mixed
     */
    public function processPay2OrderProducts($ProductList){
        $tempcount = 0;   //单个商品购买单位

        foreach ( $ProductList as $k => $Product ) {
			$tempcount = I("numb_".$Product ['p_id'],1,'intval' ); // 数量统计
            $this->updateCartCount ( $Product ['p_id'], $tempcount ); // 购物车更新数量

            $CIF = FactoryCartItemModel::init ( $Product );  // 价格小计
            $cifArgs = $CIF->getArgs ();
            $cifArgs ['uid'] = $Product ['u_id'];
            $CIF->setArgs ( $cifArgs );

            $ProductList [$k] ['p_price'] = $CIF->calcprice();
            $ProductList [$k] ['p_count'] = $tempcount; // 数量同步
			$ProductList [$k] ['uc_count'] = $tempcount;
            if ($Product ['p_producttype'] == 4) { // 如果是DIY件，需保存DIY的快照参数(增加tdf_user_diy中的diy_unit_info和cover、price字段)
                $udinfo = M ( 'user_diy' )->where ( 'id=' . $ProductList [$k] ['p_diy_id'] )->find ();
                $ProductList [$k] ['diy_unit_info'] = $udinfo ['diy_unit_info'];
                $ProductList [$k] ['cover'] = $udinfo ['cover'];
                $ProductList [$k] ['price'] = $udinfo ['price'];
            }
        }
        return $ProductList;
    }

    /**
     * 购物车中显示DIY的配置说明(目前只用于APP)
     * @param $v array 购物车产品数组
     */
    public function getUserCartDiyByProduct($v){
		$DCM=new DiyCateModel();
	    $cateInfo=$DCM->getDiyCateByCid($v['p_cate_4']);
		if($cateInfo['cate_type']==6) {//如果cate_typ为6是新的模板(react)解析
			$product_type = "";
			$conf_json = $cateInfo['conf_json'];
			$conf_array = json_decode($conf_json, true);
			$proj_array = json_decode($v['diy_unit_info'], true);
			foreach ($conf_array['groups'] as $key => $value) {
				if ($proj_array[$key]['visible']) {
					foreach ($value['paras'] as $subk => $subv) {
						$product_type .= $value['paras'][$subk]['label'] . ":" . $proj_array[$key][$subk] . "  ";
					}
				}
			}
		}elseif($cateInfo['cate_type']==5){//如果cate_typ为5是新的简笔画模板(react)解析
			$product_type=$v['p_intro'];
		}else{
	        $sql = "select TPM.pma_id,TPM.pma_name as TPM_name,TPMP.pma_name as TPMP_name,TPM.pma_unitprice,TPM.pma_density,TPM.pma_startprice,TPM.pma_diy_formula_s,TPM.pma_diy_formula_b from tdf_printer_material as TPM ";
	        $sql .= "Left Join tdf_printer_material as TPMP ON TPMP.pma_id=TPM.pma_parentid ";
	        $sql .= "where TPM.pma_type=1 order by TPM.pma_weight ASC ";
	        $mcate = M ( "printer_material" )->query ( $sql ); // 打印材料数组，必须
	        // ----------------------------------打印材料数组V
	        $DNM=new DiyNecklaceModel();//项链表模型
	        $DDM=new DiyDiamondModel();//宝石表模型
	        $DPM=new DiyPendantModel();//吊坠表模型
	        $product_type="";
	        foreach ( $mcate as $keyM => $valueM ){
	            $materialArr [$valueM ['pma_id']] = $valueM ['TPM_name'];
	        }//材料数组
				$udinfo = unserialize ( $v ['diy_unit_info'] );
                $DU = M ( 'diy_unit' )->where ( 'cid=' . $v ['p_cate_4'] . ' and ishidden=0' )->order ( 'sort' )->select (); // 选择tdf_diy_unit
	            // $product_type="";
                foreach ( $DU as $keyN => $valueN ) {
                    if($valueN ['unit_name'] == "Textvalue"){//输入的主体字符
                        $product_type.=$valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']]."; " ;
                    }elseif($valueN ['unit_name'] == "Material") {
                        $product_type.=$valueN ['unit_showname'].":".$materialArr [$udinfo [$valueN ['id']]]."; "; //属性加材质
                    }elseif($valueN ['unit_name'] == "Chaintype"){
                        $product_type.= $valueN ['unit_showname'] . ":" .$DNM->getNecklaceExplainByID($udinfo [$valueN ['id']])."; "; //属性加材质
                    }elseif($valueN ['unit_name'] == "Gendertype"){
                        $product_type.= $valueN ['unit_showname'] . ":" .$DNM->getSelectValue($udinfo [$valueN ['id']],$valueN ['id'])."; ";
                    }else{
                        if($valueN ['fieldtype'] == "DIAMOND") {
                            $product_type .= $DDM->getDimondValue($udinfo [$valueN ['id']], $valueN['unit_showname']);
                        }elseif($valueN ['fieldtype'] == "PENDANT"){
                            $product_type.= $valueN ['unit_showname'] . ":" .$DPM->getPendantValue($udinfo [$valueN ['id']],$valueN ['id'])."; ";
                        }else{
                            $product_type .= $valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']] . "; ";
                        }
                    }
                }
		}
        return $product_type;
    }



    //由diy的信息获得diy详情 不加镶钻类
    public function getDiyInfoByDataArr($dataArr,$pid){//
        foreach ( $dataArr['material'] as $keyM => $valueM ){
            $materialArr [$valueM ['pma_id']] = $valueM ['TPM_name'];
        }//材料数组
        $i=0;
        //var_dump($dataArr);
        $product_info[$i]="ID:".$pid;
        foreach ( $dataArr['diy_unit'] as $keyN => $valueN ) {
            $i++;
            if($valueN ['unit_name'] == "textValue"){//输入的主体字符
                if(!$valueN ['ishidden']){$product_info[$i]=$valueN ['unit_showname'] . ":" . $valueN['fieldvalue']."" ;}
            }elseif($valueN ['unit_name'] == "thickness" || $valueN ['unit_name'] == "height"){
                if(!$valueN ['ishidden']){$product_info[$i]=$valueN ['unit_showname'] . ":" . $valueN['fieldvalue']."mm " ;}
            }elseif($valueN ['unit_name'] == "Material") {
                if(!$valueN ['ishidden']){
                    $product_info[$i]= $valueN ['unit_showname'] . ":" . $materialArr[$valueN ['fieldvalue']] . "";
                } //属性加材质
            }elseif($valueN ['unit_name'] == "size"){
                if(!$valueN ['ishidden']){
                    $product_info[$i]= $valueN ['unit_showname'] . ":" . $valueN ['fieldvalue_show'] . "";
                }
             }else{
                if(!$valueN ['ishidden']){
                    $product_info[$i]= $valueN ['unit_showname'] . ":" . $valueN ['fieldvalue'] . "";
                }
            }

        }
        return $product_info;
    }

  /*  //由diy的信息获得diy详情
    public function getDiyInfoByDataArr($dataArr){
        //var_dump($dataArr);
        foreach ( $dataArr['material'] as $keyM => $valueM ){
            $materialArr [$valueM ['pma_id']] = $valueM ['TPM_name'];
        }//材料数组
        $i=0;
        foreach ( $dataArr['diy_unit'] as $keyN => $valueN ) {
            if($valueN ['unit_name'] == "textValue"){//输入的主体字符
                if(!$valueN ['ishidden']){$product_info[$i]=$valueN ['unit_showname'] . ":" . $valueN['fieldvalue']."; " ;}
            }elseif($valueN ['unit_name'] == "Material") {
                if(!$valueN ['ishidden']){
                    $product_info[$i]= $valueN ['unit_showname'] . ":" . $materialArr[$valueN ['fieldvalue']] . "; ";
                } //属性加材质
            }elseif($valueN ['unit_name'] == "size"){
                if(!$valueN ['ishidden']){
                    $product_info[$i]= $valueN ['unit_showname'] . ":" . $valueN ['fieldvalue_show'] . "; ";
                }
                //}elseif($valueN ['unit_name'] == "Chaintype"){
                //    $product_info[$i]= $valueN ['unit_showname'] . ":" .$DNM->getNecklaceExplainByID($udinfo [$valueN ['id']])."; "; //属性加材质
                // }elseif($valueN ['unit_name'] == "Gendertype"){
                //   $product_info[$i]= $valueN ['unit_showname'] . ":" .$DNM->getSelectValue($udinfo [$valueN ['id']],$valueN ['id'])."; ";
            }else{
                //  if($valueN ['fieldtype'] == "DIAMOND") {
                //    $product_info[$i]= $DDM->getDimondValue($udinfo [$valueN ['id']], $valueN['unit_showname']);
                //}elseif($valueN ['fieldtype'] == "PENDANT"){
                //   $product_info[$i]= $valueN ['unit_showname'] . ":" .$DPM->getPendantValue($udinfo [$valueN ['id']],$valueN ['id'])."; ";
                //}else{
                if(!$valueN ['ishidden']){
                    $product_info[$i]= $valueN ['unit_showname'] . ":" . $valueN ['fieldvalue'] . "; ";
                }
                //}
            }
            $i++;
        }
        return $product_info;
    }*/


    /*/**
     * 获取购物车数据
     *
     * @author miaomin
     * @param int $UID
     * @return mixed

    public function getProductByPidUid($UID,$PID) {

       $UserCartInfo=$this->where("u_id=".$UID." and p_id=".$PID."")->find();
        var_dump($UserCartInfo);
    }*/


 /*
 * 产品直接生成订单
 */
	public function procuct_cart_order($pid){
		$UPM = new UserPrepaidModel ();
		$userPrepaidInfo=$UPM->getPrepaidInfoByPid($pid);
		$TPM=new ProductModel();
		$Product=$TPM->getProductByID($pid);
		$ProductList [0]=$Product;
		$up_express = 1;  //是否需要快递
		$tempcount = 1;  //产品数量为1
		$TotalPrice=$Product['p_price']*$tempcount;
		$pid_array [] = $Product [$this->DBF->Product->ID] . "," . $tempcount; // product的id数组
		if ($Product ['p_producttype'] == 4) { // 如果是DIY件，需保存DIY的快照参数(增加tdf_user_diy中的diy_unit_info和cover、price字段)
			$udinfo = M ( 'user_diy' )->where ( 'id=' . $ProductList [0] ['p_diy_id'] )->find ();
			$ProductList [0] ['diy_unit_info'] = $udinfo ['diy_unit_info'];
			$ProductList [0] ['cover'] = $udinfo ['cover'];
			$ProductList [0] ['price'] = $udinfo ['price'];
			$ProductList [0] ['uc_producttype'] = 4;
			$ProductList [0] ['uc_count'] = 1; //数量为1
			$ProductList [0] ['uc_id'] = 1;//模拟购物车ID为1
			$ProductList [0] ['uc_isreal'] = 0;
			$ProductList [0] ['uc_lastupdate'] = time();
			$ProductList [0] ['uc_ctime'] = 0;
			$ProductList [0] ['uc_isbind'] = 0;
			$ProductList [0] ['uc_bindids'] = 0;
			$ProductList [0] ['uc_masterid'] = 0;
			$ProductList [0] ['uc_handleuc'] = 0;
			$ProductList [0] ['p_count'] = 1;
			$uid    = $udinfo ['u_id'];
		}
		$up_product_info = serialize ( $ProductList ); // 存储到订单商品快照中的商品信息
		$up_amount_save = $TotalPrice;
		$IP = get_client_ip ();
		$up_type = 4;


		if($userPrepaidInfo){//如果已经有订单
			$p_data['up_amount']    = $udinfo ['price'];
			$p_data['up_dealdate']  = get_now ();
			$upid=M("user_prepaid")->where("up_orderid='".$userPrepaidInfo['up_orderid']."'")->setField($p_data);
		}else{
			$temp_orderid = $this->get_umorderid ();
			$oid = $temp_orderid; // 加密orderid
		/*	$TPM=new ProductModel();
			$Product=$TPM->getProductByID($pid);*/
			//$product_cart=$UCM->getProductByPidUid($pid,$uid);
			$UPD = new UserPrepaidDetailModel ();
			$UPM->startTrans (); // 在d模型中启动事务
			$upid = $UPM->addRecord ( $uid, $up_amount_save, $IP, 0, $oid, 0, serialize ( $pid_array ), $up_type, $up_express );
			$upd_id = $UPD->addRecord ( $upid, $up_product_info );
			$pidUpdate=$UPM->where("up_id=".$upid."")->setField('p_id',$pid);

			if ($upid && $upd_id && $pidUpdate) {
				$UPM->commit (); // 提交事务
				$res=1;
			}else{
				$UPM->rollback (); // 事务回滚
				$res=0;
			}
		}


		return $res;
	}


	public function get_umorderid() {// 产生orderid
		$tempid = time () . $this->generate_rand ( 8 );
		return $tempid;
	}
    public function generate_rand($l) { // 产生随机数$l为多少位
        $c = "0123456789";
        // $c= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $rand=0;
        srand ( ( double ) microtime () * 1000000 );
        for($i = 0; $i < $l; $i ++) {
            $rand .= $c [rand () % strlen ( $c )];
        }
        return $rand;
    }

	/**
	 *根据ucidString删除购物车信息
	 * zhangzhibin 20160902
	 */
    public function deleteProductByUcidString($ucidString){
	    $condition="uc_id in (".$ucidString.")";
	    $result=$this->where ( $condition )->delete ();
	    return $result;
    }

}
?>