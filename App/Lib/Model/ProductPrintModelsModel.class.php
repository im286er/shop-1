<?php
/**
 * ProductPrintModels基本类
 *
 * @author miaomin 
 * Oct 20, 2014 3:43:34 PM
 *
 * $Id$
 */
class ProductPrintModelsModel extends Model {
	
	/**
	 *
	 * @var DBF_ProductPrintModel
	 */
	public $F;
	
	// Mapping
	protected $_map = array (
			'volume' => 'ppr_volume',
			'length' => 'ppr_length',
			'width' => 'ppr_width',
			'height' => 'ppr_height',
			'surface' => 'ppr_surface',
			'repairlv' => 'ppr_repairlv',
			'convex' => 'ppr_convex',
			'vfyreason' => 'ppr_vfy_reason',
			'printready' => 'ppr_verify' 
	);
	
	// Mapping2
	protected $_mapCartItem = array (
			'print_length' => 'ppr_length',
			'print_width' => 'ppr_width',
			'print_height' => 'ppr_height',
			'print_volume' => 'ppr_volume',
			'print_surface' => 'ppr_surface',
			'print_repairlv' => 'ppr_repairlv',
			'print_convex' => 'ppr_convex' 
	);
	
	// 计算价格用(见后台公式)
	protected $_calcPara = array (
			// 锐打计算价格
			'rpprice' => NULL,
			// 设计费
			'designfee' => NULL 
	);
	
	/**
	 * 构造
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->ProductPrintModel;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
	
	/**
	 * 获取计价参数
	 *
	 * @return array
	 */
	public function getCalcPara() {
		return $this->_calcPara;
	}
	
	/**
	 * 计算体积
	 *
	 * @param float $length        	
	 * @param float $width        	
	 * @param float $height        	
	 */
	public function getVolume($length, $width, $height) {
		return round ( $length * $width * $height, 4 );
	}
	
	/**
	 * 获取映射数组
	 */
	public function getMapCartItem() {
		return $this->_mapCartItem;
	}
	
	/**
	 * 通过RP360接口获取打印材料精度
	 *
	 * @return mixed
	 */
	public function getMaterialsPrecision() {
		try {
			
			import ( 'Common.Ncurl', APP_PATH, '.php' );
			
			$method = 'models.getmaterialsprecision';
			$format = 'json';
			$debug = 0;
			$userinfoArr = array (
					'user' => RP360_API_USER,
					'pass' => RP360_API_PASS 
			);
			
			$NC = new Ncurl ( RP360_API_URL );
			$curlRes = $NC->curlPost2 ( $method, $format, $userinfoArr, array (), 1, $debug );
			
			$res = json_decode ( $curlRes, 1 );
			
			if (is_array ( $res ) && $res [0] ['code'] == '200') {
				
				array_shift ( $res );
				
				return $res;
			}
			
			return false;
		} catch ( Exception $e ) {
			echo $e->getMessage ();
			return false;
		}
	}
	
	/**
	 * 通过RP360接口获取打印材料
	 *
	 * 添加一个过滤器
	 *
	 * @return mixed
	 */
	public function getMaterials() {
		try {
			
			import ( 'Common.Ncurl', APP_PATH, '.php' );
			
			$method = 'models.getmaterialslevel';
			$format = 'json';
			$debug = 0;
			$userinfoArr = array (
					'user' => RP360_API_USER,
					'pass' => RP360_API_PASS 
			);
			
			$NC = new Ncurl ( RP360_API_URL );
			$curlRes = $NC->curlPost2 ( $method, $format, $userinfoArr, array (), 1, $debug );
			
			$res = json_decode ( $curlRes, 1 );
			
			if (is_array ( $res ) && $res [0] ['code'] == '200') {
				
				array_shift ( $res );
				
				// filter pma_id
				$filterArr = array ();
				$PPMFM = new ProductPMMaterialFilterModel ();
				$ppmfmRes = $PPMFM->where ( '1=1' )->select ();
				if ($ppmfmRes) {
					foreach ( $ppmfmRes as $key => $val ) {
						$filterArr [] = $val [$PPMFM->F->PMAID];
					}
					
					foreach ( $res as $key => $val ) {
						foreach ( $val ['Child'] as $k => $v ) {
							foreach ( $filterArr as $f => $p ) {
								if ($v ['pma_id'] == $p) {
									unset ( $res [$key] ['Child'] [$p] );
								}
							}
						}
					}
					
					foreach ( $res as $key => $val ) {
						if (count ( $val ['Child'] ) == 0) {
							unset ( $res [$key] );
						}
					}
					
					// print_r ( $res );
				}
				
				return $res;
			}
			
			return false;
		} catch ( Exception $e ) {
			echo $e->getMessage ();
			return false;
		}
	}
	
	/**
	 * 通过公式替换获取最终价格
	 *
	 * @param array $calcFactory        	
	 */
	public function getlocalPrice(array $calcFactory) {
		$res = 0;
		
		$PMFM = new ProductPMFormulaModel ();
		$pmfmRes = $PMFM->getCurrent ();
		
		if ($pmfmRes) {
			$currentFormula = $pmfmRes [$PMFM->F->FORMULA];
			$condition = replace_string_vars ( $currentFormula, $calcFactory );
			$res = eval ( "return $condition;" );
		}
		
		return $res;
	}
	
	/**
	 * 通过RP360接口获取成品模型打印价格
	 *
	 * @param array $calcFactory;
	 *        	-- pma_id 打印材料ID
	 *        	-- pmd_id 打印材料精度ID
	 * @return mixed
	 */
	public function getRPPrice(array $calcFactory) {
		try {
			
			import ( 'Common.Ncurl', APP_PATH, '.php' );
			
			$method = 'orders.checkprice';
			$format = 'json';
			$debug = 0;
			$userinfoArr = array (
					'user' => RP360_API_USER,
					'pass' => RP360_API_PASS 
			);
			
			/* 计价参数 */
			// 材质ID
			$cartitem_material = ( int ) $calcFactory ['pma_id'];
			
			// 模型体积
			$cartitem_volume = ( float ) $this->{$this->F->VOLUME};
			
			// 最小包围盒长
			$cartitem_minBoundBox_L = ( float ) $this->{$this->F->LENGTH};
			
			// 最小包围盒宽
			$cartitem_minBoundBox_W = ( float ) $this->{$this->F->WIDTH};
			
			// 最小包围盒高
			$cartitem_minBoundBox_H = ( float ) $this->{$this->F->HEIGHT};
			
			// 模型表面积
			$cartitem_surfaceArea = ( float ) $this->{$this->F->SURFACE};
			
			// 模型待修理级别
			$cartitem_repairLevel = ( int ) $this->{$this->F->REPAIRLV};
			
			// 凸包
			$cartitem_convex = ( float ) $this->{$this->F->CONVEX};
			
			// Debug
			$cartitem_calcdebug = 0;
			
			// COPYNUM
			$cartitem_num = 1;
			
			// 打印精度
			$cartitem_precisionId = ( int ) $calcFactory ['pmd_id'];
			
			// Curl
			$curlPost = array (
					'cartitem_material' => $cartitem_material,
					'cartitem_volume' => $cartitem_volume,
					'cartitem_minBoundBox_L' => $cartitem_minBoundBox_L,
					'cartitem_minBoundBox_W' => $cartitem_minBoundBox_W,
					'cartitem_minBoundBox_H' => $cartitem_minBoundBox_H,
					'cartitem_surfaceArea' => $cartitem_surfaceArea,
					'cartitem_repairLevel' => $cartitem_repairLevel,
					'cartitem_convex' => $cartitem_convex,
					'cartitem_calcdebug' => $cartitem_calcdebug,
					'cartitem_num' => $cartitem_num,
					'cartitem_precisionId' => $cartitem_precisionId 
			);
			
			// print_r($curlPost);
			
			$NC = new Ncurl ( RP360_API_URL );
			$curlRes = $NC->curlPost2 ( $method, $format, $userinfoArr, $curlPost, 1, $debug );
			
			$curlRes = json_decode ( $curlRes, 1 );
			
			if ($curlRes [0] ['code'] == 200) {
				return $curlRes [1];
			} else {
				return 0;
			}
		} catch ( Exception $e ) {
			
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 通过RP360接口获取成品模型打印价格
	 *
	 * @param array $calcFactory;        	
	 * @return mixed
	 */
	public function calcPrice(array $calcFactory) {
		// 获取RP360报价
		$rpPrice = $this->getRPPrice ( $calcFactory );
		
		// 获取设计费用
		$designFee = $calcFactory ['print_designfee'];
		
		// 获取本地报价
		$calcFactory = $this->getCalcPara ();
		
		$calcFactory ['rpprice'] = $rpPrice;
		$calcFactory ['designfee'] = $designFee;
		
		$localPrice = $this->getlocalPrice ( $calcFactory );
		
		$localPrice = round ( $localPrice, 2 );
		
		return $localPrice;
	}
	
	/**
	 * 导入打印成品模型数据
	 *
	 * @param array $prmDataArr        	
	 */
	public function importByXls(array $prmDataArr) {
		
		// PRODUCTTYPE
		$productTypeArr = C ( 'PRODUCT.TYPE' );
		
		// ProductModel
		$PMM = new ModelsModel ();
		
		// Product
		$PM = new ProductModel ();
		
		foreach ( $prmDataArr as $key => $val ) {
			
			$PID = ( int ) $val ['p_id'];
			
			if (is_int ( $PID ) && $PID > 0) {
				
				$pmmRes = $PMM->find ( $PID );
				
				// 模型数据获取
				if ($pmmRes) {
					// 操作
					
					$PMM->startTrans ();
					
					// Update Product表
					$pmRes = $PM->find ( $PID );
					if ($pmRes [$PM->F->ProductType] != $productTypeArr ['PRINTMODEL']) {
						$condition = array (
								$PM->F->ID => $PID 
						);
						
						$savedata = array (
								$PM->F->ProductType => $productTypeArr ['PRINTMODEL'] 
						);
						
						$PM->where ( $condition )->save ( $savedata );
					}
					
					// Insert or Update ProductPrintModel表
					$condition = array (
							$this->F->PID => $PID 
					);
					$prmFindRes = $this->where ( $condition )->find ();
					
					// Create Data
					$this->create ( $val );
					$this->{$this->F->PID} = $PID;
					$this->{$this->F->LASTUPDATE} = get_now ();
					$this->{$this->F->VERIFY} = 1;
					$this->{$this->F->VFYDATE} = get_now ();
					$this->{$this->F->VFYUID} = $_SESSION ['userid'];
					
					if ($prmFindRes) {
						$prmAddRes = $this->where ( $condition )->save ();
					} else {
						$prmAddRes = $this->add ();
					}
					
					// Insert or Update ProductPMMaterial表
					$PRMM = new ProductPMMaterialModel ();
					$condition = array (
							$PRMM->F->PID => $PID 
					);
					$prmmDelRes = $PRMM->where ( $condition )->delete ();
					
					if ($val ['materials'] == 0) {
						
						$PRMM->create ();
						$PRMM->{$PRMM->F->PID} = $PID;
						$PRMM->{$PRMM->F->MATERIALID} = 0;
						$prmmAddRes = $PRMM->add ();
					} else {
						
						$materialsArr = explode ( '#', $val ['materials'] );
						foreach ( $materialsArr as $k => $v ) {
							
							$PRMM->create ();
							$PRMM->{$PRMM->F->PID} = $PID;
							$PRMM->{$PRMM->F->MATERIALID} = $v;
							$prmmAddRes = $PRMM->add ();
						}
					}
					
					// 将isprintready设置1
					if ($pmmRes [$PMM->F->IsPrintReady] == 0) {
						
						$condition = array (
								$PMM->F->ProductID => $pmmRes [$PMM->F->ProductID] 
						);
						
						$savedata = array (
								$PMM->F->IsPrintReady => 1 
						);
						
						$pmmUpdateRes = $PMM->where ( $condition )->save ( $savedata );
					} else {
						$pmmUpdateRes = true;
					}
					
					// 将isprintmodel设置1
					if ($pmmRes [$PMM->F->IsPrintModel] == 0) {
						
						$condition = array (
								$PMM->F->ProductID => $pmmRes [$PMM->F->ProductID] 
						);
						
						$savedata = array (
								$PMM->F->IsPrintModel => 1 
						);
						
						$pmmUpdateRes = $PMM->where ( $condition )->save ( $savedata );
					} else {
						$pmmUpdateRes = true;
					}
				}
			}
			
			// Commit
			// var_dump($prmmAddRes);
			// var_dump($prmmAddRes);
			// var_dump($pmmUpdateRes);
			
			if ($prmAddRes && $prmmAddRes && $pmmUpdateRes) {
				$PMM->commit ();
				$processRes = true;
			} else {
				$PMM->rollback ();
				$processRes = false;
			}
		}
		
		echo $processRes ? '处理完毕！' : '处理异常！';
	}
}
?>