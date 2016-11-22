<?php
/**
 * 订单相关API
 *
 * @author miaomin 
 * Oct 15, 2013 10:56:46 AM
 *
 * $Id: OrdersAction.class.php 1169 2013-12-27 07:02:01Z miaomiao $
 */
class OrdersAction extends CommonAction {
	
	// TODO
	// 魔术方法
	public function __call($name, $arguments) {
		throw new Exception ( $this->RES_CODE_TYPE ['METHOD_ERR'] );
	}
	
	/**
	 * 订单相关API
	 */
	public function __construct() {
		parent::__construct ();
		
		import ( 'App.Model.Cart.AbstractCartModel' );
		import ( 'App.Model.Cart.AbstractCondimentDecorator' );
		import ( 'App.Model.Cart.CartItem' );
		import ( 'App.Model.Cart.MaterialsCondiment' );
		import ( 'App.Model.Cart.QuantityCondiment' );
		import ( 'App.Model.Cart.DensityCondiment' );
		import ( 'App.Model.Cart.UnitPriceCondiment' );
		import ( 'App.Model.Cart.StartPriceCondiment' );
		import ( 'App.Model.Cart.VolumeCondiment' );
		import ( 'App.Model.Cart.UsersCondiment' );
		import ( 'App.Model.Cart.CalcPriceCondiment' );
		import ( 'App.Model.CalcPrinterObject' );
	}
	
	/**
	 * 计算单个模型价格
	 *
	 * 从流程上来讲这个时候无法提交模型ID
	 * 只能提交模型的体积包围盒等信息
	 * 并且根据材质ID来获取价格
	 * 所以算价接口其实模型ID只是一个可有可无的数据
	 */
	public function checkprice() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		/* 接受计价参数 */
		
		// 模型信息
		// volume
		// 体积单位转换为立方厘米
		$volume = ( float ) $args ['cartitem_volume'] / 1000;
		// boundBox
		$boundboxL = ( float ) $args ['cartitem_minBoundBox_L'];
		$boundboxW = ( float ) $args ['cartitem_minBoundBox_W'];
		$boundboxH = ( float ) $args ['cartitem_minBoundBox_H'];
		$boundBox = ( float ) ($boundboxL * $boundboxW * $boundboxH);
		// ratio
		$ratio = round ( ($volume * 1000 / $boundBox), 2 );
		// surfaceArea
		$surfaceArea = ( float ) $args ['cartitem_surfaceArea'];
		// repairLevel
		$repairLevel = ( int ) $args ['cartitem_repairLevel'] ? $args ['cartitem_repairLevel'] : 1;
		// repairFee
		$repairFee = $repairLevel * 50;
		// postFee
		$postFee = 0;
		
		// 模型信息数组化
		$pmArr = array (
				'boundBox' => $boundBox,
				'volume' => $volume,
				'ratio' => $ratio,
				'surfaceArea' => $surfaceArea,
				'repairLevel' => $repairLevel,
				'repairFee' => $repairFee,
				'postFee' => $postFee 
		);
		
		// 材质信息
		$PMA = new PrinterMaterialModel ();
		$pmaRes = $PMA->find ( $args ['cartitem_material'] );
		// 如果没有正确取到材质信息抛出异常
		if (! $pmaRes) {
			throw new Exception ( 'PRINTMATERIAL_NOT_EXIST' );
		}
		
		// 计价模型初始化
		$CPO = new CalcPrinterObject ();
		$CPO->transMap ( $PMA );
		$CPO->transMap ( $pmArr );
		$paraArr = $CPO->getPara ();
		
		$res [] = UserCartModel::singleCalcProduct2 ( $CPO, 1, $args ['cartitem_calcdebug'] );
		
		return $res;
	}
	
	/**
	 * 获取回报单
	 */
	public function getcallback() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 获取回报单
		$UNPC = new UnionPrepaidCallbackModel ();
		// TODO
		$res [] = $UNPC->getCallback ( $args );
		
		return $res;
	}
	
	/**
	 * 设置厂商打印能力
	 */
	public function setcapacity() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 设置打印能力
		$UPC = new UnionPrinterCapacityModel ();
		// TODO
		$res [] = $UPC->updateCapacity ( $args );
		
		return $res;
	}
	
	/**
	 * 回复回报单
	 */
	public function replycallback() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 获取回报单
		$UNPC = new UnionPrepaidCallbackModel ();
		// TODO
		$res [] = $UNPC->replyCallback ( $args );
		
		return $res;
	}
	
	/**
	 * 发送回报单
	 */
	public function sendcallback() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 上传文件
		// 预载入
		load ( '@.YunUploader' );
		load ( '@.FileChecker' );
		$YU = new YunUploader ();
		$FC = new FileChecker ();
		
		$path = getSavePathByID ( $args ['orderid'] );
		$FileInfo = $YU->uploadCallbackAttach ( $path, $args ['orderid'], $_FILES ['filename1'] );
		
		// 上传失败
		if (! $FileInfo) {
			throw new Exception ( $this->RES_CODE_TYPE ['UPLOAD_FILE_FAILED'] );
		}
		
		// 发送回报单
		$UNPC = new UnionPrepaidCallbackModel ();
		// TODO
		$res [] = $UNPC->addCallback ( $args, $FileInfo );
		
		return $res;
	}
	
	/**
	 * 设置订单详情状态
	 */
	public function setorderdetail() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 上传文件
		// 预载入
		load ( '@.YunUploader' );
		load ( '@.FileChecker' );
		$YU = new YunUploader ();
		$FC = new FileChecker ();
		
		$path = getSavePathByID ( $args ['detailid'] );
		$FileInfo = $YU->uploadDetailAttach ( $path, $args ['detailid'], $_FILES ['filename1'] );
		
		// 上传失败
		if (! $FileInfo) {
			throw new Exception ( $this->RES_CODE_TYPE ['UPLOAD_FILE_FAILED'] );
		}
		
		// 设置订单详情状态
		$UNPD = new UnionPrepaidDetailModel ();
		// TODO
		$res [] = $UNPD->setOrderDetail ( $args, $FileInfo );
		
		return $res;
	}
	
	/**
	 * 设置订单状态
	 */
	public function setorder() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 设置订单状态
		$UNP = new UnionPrepaidModel ();
		// TODO
		$res [] = $UNP->setOrders ( $args );
		
		return $res;
	}
	
	/**
	 * 获取订单详情列表
	 */
	public function getorderdetail() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 获取订单详情列表
		$UNPD = new UnionPrepaidDetailModel ();
		$UNPD->setFilterCondition ( $args );
		$res = $UNPD->getOrderDetails ();
		
		return $res;
	}
	
	/**
	 * 获取订单列表
	 */
	public function getorders() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 获取订单列表
		$UNP = new UnionPrepaidModel ();
		$UNP->setFilterCondition ( $args );
		$res = $UNP->getOrders ();
		
		return $res;
	}
	
	/**
	 * 生成订单列表
	 */
	public function createorders() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		// 获取订单列表
		$UNP = new UnionPrepaidModel ();
		$UNP->create ( $args );
		$res [] = $UNP->add ();
		
		return $res;
	}
	
	/**
	 * 单个模型加入购物车
	 *
	 * lastcheck: 2013/11/5
	 */
	public function addcart() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		} else {
			$Users = new UsersModel ();
			$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
			if (! $userinfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
			}
			$Users->find ( $userinfo [0] ['u_id'] );
		}
		
		$UFId = ( int ) $args ['ufid'];
		$pmaId = ( int ) $args ['pmaid'];
		
		// 获取数据
		$UF = new UserFilesModel ();
		$ufRes = $UF->find ( $UFId );
		
		if ((! is_array ( $ufRes )) || ($ufRes ['u_id'] != $userinfo [0] ['u_id'])) {
			throw new Exception ( $this->RES_CODE_TYPE ['USERFILE_NOT_EXIST'] );
		}
		
		$PF = new PrinterModelModel ();
		$pfRes = $PF->find ( $ufRes ['yf_id'] );
		if (! is_array ( $pfRes ) || ($pfRes ['pm_status'] == 0)) {
			throw new Exception ( $this->RES_CODE_TYPE ['PRINTERMODEL_NOT_EXIST'] );
		}
		
		$YF = new YunFilesModel ();
		$yfRes = $YF->find ( $ufRes ['yf_id'] );
		if (! is_array ( $yfRes )) {
			throw new Exception ( $this->RES_CODE_TYPE ['YUNFILE_NOT_EXIST'] );
		}
		
		// 可打印材料
		$PMM = new PrinterModelMaterialModel ();
		$pmmList = $PMM->getMaterialByYunFileID ( $ufRes ['yf_id'] );
		
		// pr($pmmList);
		// exit;
		
		// 获取打印材料数据
		$PM = new PrinterMaterialModel ();
		$pmRes = $PM->find ( $pmaId );
		if (! is_array ( $pmRes )) {
			throw new Exception ( $this->RES_CODE_TYPE ['PRINTMATERIAL_NOT_EXIST'] );
		}
		
		$pmaIdEnable = false;
		foreach ( $pmmList as $key => $val ) {
			if ($pmRes ['pma_id'] == $val ['pma_id']) {
				$pmaIdEnable = true;
			}
		}
		
		if (! $pmaIdEnable) {
			throw new Exception ( $this->RES_CODE_TYPE ['MATERIAL_NOT_MATCH'] );
		}
		
		$itemsaver = array ();
		$itemsaver ['itemUFId'] = $UFId;
		$itemsaver ['itemId'] = $ufRes ['yf_id'];
		$itemsaver ['itemPMAId'] = $pmaId;
		$itemsaver ['itemCount'] = 1;
		$itemsaver ['itemType'] = 1;
		$itemsaver ['itemUserId'] = $userinfo [0] ['u_id'];
		$UC = new UserCartModel ();
		if (! $UC->addProduct ( $itemsaver, $userinfo [0] ['u_id'] )) {
			throw new Exception ( $this->RES_CODE_TYPE ['WRITE_CART_TABLE_ERR'] );
		}
		
		// 返回结果
		// $res [] = $itemsaver;
		$res [] = true;
		return $res;
	}
	
	/**
	 * 获取购物车数量
	 */
	public function getcartnum() {
		// 返回结果
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
		if (! $logindata) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
		}
		
		// 处理用户名和密码信息
		$logininfo ['email'] = $logindata [0];
		$logininfo ['pass'] = $logindata [1];
		$logininfo ['from'] = $this->REQUEST_FROM_TYPE ['CLIENT'];
		
		// 用户名密码验证
		load ( '@.Reginer' );
		$reginer = new Reginer ();
		$reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['CLIENT'];
		$loginRes = $reginer->Login ( $logininfo );
		if (! $loginRes) {
			throw new Exception ( $reginer->ErrorCode );
		}
		
		// 验证成功
		$Users = new UsersModel ();
		$userRes = $Users->getUserInfo ( $logindata [0], $logindata [1] );
		
		if (! $userRes) {
			throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
		}
		
		// 获取购物车数量
		$UCM = new UserCartModel ();
		$ucmRes = $UCM->getPrinterModelCount ( $userRes [0] ['u_id'] );
		
		$res[] = $ucmRes;
		return $res; 
	}
	
	// TODO
	// 修改购物车
}

?>