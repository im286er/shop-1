<?php
/**
 * 产品模型控制器
 *
 * @author jzy,miaomin 
 * Apr 17, 2014 11:22:48 AM
 *
 * $Id$
 */
class ProductModelAction extends CommonAction {
	// 发布模型后的跳转页面
	private $UrlHome = '/user.php';
	private $UrlUpload = 'user/product_model/upload';
	private $UrlUploadFile = 'user/product_model/uploadfile';
	private $UrlUploadPhoto = 'user/product_model/uploadphoto';
	private $UrlAdd = 'user/product_model/add';
	private $UrlEdit = 'user/product_model/edit/pid/';
	private $UrlDeleteFile = 'user/product_model/deletefile';
	private $UrlDeletePhoto = 'user/product_model/deletephoto';
	function __construct() {
		parent::__construct ();
		$this->UrlHome = WEBROOT_PATH . $this->UrlHome;
		$this->UrlUpload = U ( $this->UrlUpload );
		$this->UrlUploadFile = U ( $this->UrlUploadFile );
		$this->UrlUploadPhoto = U ( $this->UrlUploadPhoto );
		$this->UrlAdd = U ( $this->UrlAdd );
		$this->UrlEdit = U ( $this->UrlEdit );
		$this->UrlDeleteFile = U ( $this->UrlDeleteFile );
		$this->UrlDeletePhoto = U ( $this->UrlDeletePhoto );
		$this->assign ( 'U_Home', $this->UrlHome );
		$this->assign ( 'U_Upload', $this->UrlUpload );
		$this->assign ( 'U_UploadFile', $this->UrlUploadFile );
		$this->assign ( 'U_UploadPhoto', $this->UrlUploadPhoto );
		$this->assign ( 'U_Add', $this->UrlAdd );
		$this->assign ( 'U_Edit', $this->UrlEdit );
		$this->assign ( 'U_DeleteFile', $this->UrlDeleteFile );
		$this->assign ( 'U_DeletePhoto', $this->UrlDeletePhoto );
		$this->assign ( 'showtitle', "上传模型-3D城" );
	}
	
	/**
	 * 产品上传
	 *
	 * @return void boolean
	 */
	public function upload() {
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
			return false;
		}
		$uptype = I ( "uptype", 0, "intval" ); // 获取上传的类型，默认为0（cg模型上传） 1为3d打印模型上传
		
		$uptype = 1;
		$PID = $this->getProductID ();
		$UID = session ( 'f_userid' );
		$PFM = new ProductFileModel ();
		$PCTM = new ProductCreateToolModel ( $uptype );
		if ($this->isPost ()) {
			$FileData = $this->getPostFileInfo ( $PCTM, $UID, $PID );
			// var_dump($FileData);
			if ($FileData !== false) {
				if ($FileData === null) {
					echo '{"isSuccess":false, "Message":"数据库连接失败"}';
					return;
				}
				if ($FileData == array ()) {
					echo '{"isSuccess":false, "Message":"没有提交有效数据"}';
					return;
				}
				$ProductPrime = 1;
				$PrimeList = array ();
				$MainFile = 0;
				$MainFileExt = '';
				foreach ( $FileData as $ID => $File ) {
					// $Prime的值为创作工具的ID
					$Prime = isset ( $File ['CT'] ) && $File ['CT'] ? $File ['CT'] : 1;
					// 现改为质数值
					$Prime = $PCTM->CreateTool [$File ['CT']] [$PCTM->F->Prime];
					if (! in_array ( $Prime, $PrimeList )) {
						$PrimeList [] = $Prime;
					}
					if (isset ( $File ['MainFile'] ) && $File ['MainFile']) {
						$MainFile = $File [$PFM->F->ID];
						$MainFileExt = $PCTM->CreateTool [$File ['CT']] [$PCTM->F->Ext];
					}
				}
				// debug miaomin edited@2014.4.17
				// 不是ID相乘而是要根据ID获取对应的质数值相乘
				foreach ( $PrimeList as $Prime ) {
					$ProductPrime *= $Prime;
				}
				$FileKey = array_keys ( $FileData );
				if (! $MainFile) {
					$MainFile = $FileKey [0];
				}
				
				$PFM->startTrans ();
				$PM = new ProductModel ();
				if (! $PID) {
					$PM = $this->buildNewProductModel ( $FileData [0] [$PFM->F->OriginalName], $UID );
					$PM->{$PM->F->Ctprime} = $ProductPrime;
					$PM->{$PM->F->MainFile} = $MainFile;
					$PM->{$PM->F->MainFile_disp} = $MainFileExt;
					$PID = $PM->add ();
					if (! $PID) {
						$PFM->rollback ();
						echo '{"isSuccess":false, "Message":"数据库连接失败"}';
						return;
					}
					
					// PPM
					$PPMM = new ProductPrintModelsModel ();
					$PPMM->{$PPMM->F->PID} = $PID;
					$PPMM->add ();
					
					$PMM = new ModelsModel ();
					$PMM->{$PMM->F->ProductID} = $PID;
					$PMM->{$PMM->F->IsPrintModel} = 1;
					if (! $PMM->add ()) {
						$PFM->rollback ();
						echo '{"isSuccess":false, "Message":"数据库连接失败"}';
						return;
					}
					$ProductPrime = false;
				}
				if ($ProductPrime) {
					$PM->{$PM->F->ID} = $PID;
					$PM->{$PM->F->Ctprime} = $ProductPrime;
					$PM->{$PM->F->MainFile} = $MainFile;
					$PM->{$PM->F->MainFile_disp} = $MainFileExt;
					if ($PM->save () === false) {
						$PFM->rollback ();
						echo '{"isSuccess":false, "Message":"数据库连接失败"}';
						return;
					}
				}
				if ($this->moveFileToProductDir ( $FileData, $PID )) {
					$DirPath = C ( 'UPLOAD_PAHT.PRODUCT_WEB' ) . getSavePathByID ( $PID );
					foreach ( $FileData as $ID => $File ) {
						$PFM = $this->setFileDataModel ( $PFM, $File, $PID, $DirPath );
						if ($PFM->save () === false) {
							$PFM->rollback ();
							echo '{"isSuccess":false, "Message":"数据库连接失败"}';
							return;
						}
					}
					$PFM->commit ();
					
					// -----------------------------工具Pid上传文件到OSS start
					$OSSM = new AliossModel ();
					$res = $OSSM->upfileOssByPidSingle ( $PID );
					
					// -----------------------------工具Pid上传文件到OSS end
					
					$this->deleteTempFile ( $FileData );
				} else {
					echo '{"isSuccess":false , "Message":"文件操作错误"}';
					return;
				}
				echo '{"isSuccess":true, "PID":' . $PID . '}';
				return;
			} else {
				echo '{"isSuccess":false, "Message":"提交数据异常"}';
				return;
			}
		} else {
			// 准备工作
			$MainFile = 0;
			if ($PID) {
				$PM = new ProductModel ();
				$Product = $PM->getProductByID ( $PID );
				if ($Product === false) {
					return $this->error ( '连接失败', $this->UrlHome );
				}
				if ($Product === null) {
					return $this->error ( '当前项不存在或已被删除', $this->UrlHome );
				}
				if ($Product [$PM->F->Creater] != $UID) {
					return $this->error ( '非本人发布不能编辑', $this->UrlHome );
				} // !
				$JsonFile = $PFM->getFileByProductjSON ( $PID );
				if ($JsonFile === false) {
					return $this->error ( '连接失败', $this->UrlHome );
				}
				$MainFile = $Product [$PM->F->MainFile];
				
				$this->assign ( 'JsonFile', $JsonFile );
			}
			// 获取创作工具数据
			
			$JsonPCT = $PCTM->getCreateToolJson ( 1 );
			
			// 获取创作工具的所属渲染器关系
			$JsonPCTI = $PCTM->CTIModel->getCreateToolIndexJson ();
			// var_dump($JsonPCTI);
			// $JsonPCTI='{"2":[16,17,18],"3":[18],"1":[18],"7":[21]}';
			
			if ($JsonPCT === false || $JsonPCTI === false) {
				return $this->error ( '连接失败', $this->UrlHome );
			}
			
			$JsonTempFile = $PFM->getTempFileJsonByUser ( $UID );
			
			if ($JsonTempFile === false) {
				return $this->error ( '连接失败' . $PFM->getLastSql (), $this->UrlHome );
			}
			// pr($JsonPCT);
			$this->assign ( 'SessionID', session_id () );
			$this->assign ( 'PID', $PID ? $PID : 0 );
			$this->assign ( 'MainFile', $MainFile ? $MainFile : 0 );
			$this->assign ( 'JsonPCT', $JsonPCT );
			$this->assign ( 'JsonPCTI', $JsonPCTI );
			$this->assign ( 'JsonTempFile', $JsonTempFile );
			$this->_renderPage ();
		}
	}
	public function uploadweb() {
		$this->_renderPage ();
	}
	public function uploadfile() {
		if (! $_FILES ['Filedata']) {
			echo '{"isSuccess":false, "Message":"没有检测到有效的文件"}';
			return false;
		}
		$UID = $_SESSION ['f_userid'];
		$PID = $this->getProductID ();
		$FileInfo = $this->saveFile ( $UID, $PID );
		// var_dump($FileInfo);
		// exit;
		if (! $FileInfo) {
			echo '{"isSuccess":false, "Message":"文件上传失败"}';
			return false;
		}
		$FM = $this->buildFileDateModel ( $FileInfo, $UID, $PID );
		$CreateDate = get_now ();
		$FM->startTrans ();
		$FileID = $FM->add ();
		if ($FileID) {
			$FM->commit ();
			echo '{"isSuccess":true,"CreateDate":"' . $CreateDate . '","FileID":"' . $FileID . '"}';
		} else {
			$FM->rollback ();
			echo '{"isSuccess":false, "Message":"数据库连接失败"}';
		}
	}
	public function uploadphoto() {
		$UID = session ( 'f_userid' );
		$PID = $this->getProductID ();
		if (! $PID) {
			echo '{"isSuccess":false, "Message":"验证错误"}';
			return false;
		}
		// miaomin added@2014.8.26
		// 获取模型数据
		$PM = new ProductModel ();
		$Product = $PM->getProductInfoByID ( $PID );
		if ($Product [$PM->F->Creater] !== $UID) {
			echo '{"isSuccess":false, "Message":"非本人发布不能编辑"}';
			return false;
		} // !
		if (! $_FILES ['Photodata']) {
			echo '{"isSuccess":false, "Message":"无效的文件"}';
			return false;
		}
		// echo "aaaaabbbbb";
		// var_dump($_FILES ['Photodata']);
		// exit;
		$PhotoInfo = $this->savePhoto ( $PID );
		
		if (! $PhotoInfo) {
			echo '{"isSuccess":false, "Message":"上传失败"}';
			return false;
		}
		// miaomin added@2014.8.26
		// 自动添加默认图片
		$CoverID = $Product [$PM->F->Cover_ID] ? $Product [$PM->F->Cover_ID] : 0;
		
		$PPM = $this->bulidNewPhotoDataModel ( $PhotoInfo, $PID );
		$PPM->startTrans ();
		$PhotoID = $PPM->add ();
		if ($PhotoID) {
			// 自动添加默认图片
			if ($CoverID == 0) {
				$CoverID = $PhotoID;
				$this->setProductCover ( $Product, $CoverID );
			}
			$PPM->commit ();
			$PhotoPath = preg_replace ( '|^./|', '/', $PhotoInfo ['savepath'], 1 ) . basename ( $PhotoInfo ['savename'] );
			echo '{"isSuccess":true,"PhotoID":"' . $PhotoID . '","PhotoPath":"' . $PhotoPath . '"}';
		} else {
			$PPM->rollback ();
			echo '{"isSuccess":false, "Message":"数据库连接失败"}';
		}
	}
	public function edit() {
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
			return false;
		}
		$UID = session ( 'f_userid' );
		$PID = $this->getProductID ();
		if (! $PID) {
			$this->error ( '打开方式错误', $this->UrlHome );
		}
		$PM = new ProductModel ();
		$Product = $PM->getProductInfoByID ( $PID );
		if ($Product [$PM->F->Creater] !== $UID) {
			return $this->error ( '非本人发布不能编辑', $this->UrlHome );
		} // !
		
		$PPM = new ProductPhotoModel ();
		if ($this->isPost ()) {
			$PostInfo = $this->getEditPost ();
			
			if (! $PostInfo) {
				$this->error ( '有必要的项没有填写', $this->UrlEdit . '/' . $PID );
			}
			
			$Result = true;
			$CoverID = $Product [$PM->F->Cover_ID] ? $Product [$PM->F->Cover_ID] : 0;
			if ($Product [$PM->F->Cate_1] == 0) {
				if ($PostInfo ['tags']) {
					$PostInfo ['tags'] [] = $PostInfo ['name'];
				} else {
					$PostInfo ['tags'] = array (
							$PostInfo ['name'] 
					);
				}
			}
			
			$PM = $this->bulidProductDataModel ( $PostInfo, $PID );
			$PM->startTrans ();
			// var_dump($PostInfo);
			// exit;
			foreach ( $PostInfo ['photoinfo'] as $Photo ) {
				$PPM = $this->buildPhotoDataModel ( $Photo );
				if ($PPM->save () === false) {
					$Result = false;
					break;
				}
				if ($Photo ['IsCover']) {
					$CoverID = $Photo ['PID'];
				}
			}
			if ($CoverID == 0) {
				$CoverID = $PostInfo ['photoinfo'] [0] ['PID'];
			}
			$PMM = $this->buildModelDataModel ( $PostInfo, $PID );
			$Result = $Result !== false ? $PM->save () : false;
			$Result = $Result !== false ? $PMM->save () : false;
			if ($Result === false) {
				$PM->rollback ();
				$this->error ( '保存失败', $this->UrlEdit . '/' . $PID );
			}
			
			// 保存TAG
			if (! $this->saveTags ( $PID, $PostInfo ['tags'] )) {
				 $PM->rollback (); 
				 $this->error ( '保存失败', $this->UrlEdit . '/' . $PID ); 
			}
			
			$PM->commit ();
			$SetCover = $this->setProductCover ( $Product, $CoverID );
			$SetCover = $SetCover === false ? '<br/>封面设置失败' : '';
			
			// 材料管理
			// ProductSupportMaterials
			$PSM = new ProductPMMaterialModel ();
			$condition = array (
					$PSM->F->PID => $PID 
			);
			$psmRes = $PSM->where ( $condition )->select ();
			
			$itemArr = array ();
			foreach ( $_POST as $key => $val ) {
				if (substr ( $key, 0, 5 ) == 'item_') {
					$itemArr [] = $val;
				}
			}
			
			$savedate = array (
					$PSM->F->ENABLED => 0 
			);
			$PSM->where ( $condition )->save ( $savedate );
			
			if (count ( $itemArr ) > 0) {
				
				$map [$PSM->F->PID] = $PID;
				$map [$PSM->F->MATERIALID] = array (
						'in',
						$itemArr 
				);
				
				$savedate = array (
						$PSM->F->ENABLED => 1 
				);
				
				$PSM->where ( $map )->save ( $savedate );
				
				echo $PSM->getLastSql ();
			}
			
			// 跳转赋值
			$this->_assignLoginInfo ();
			
			if ($Product [$PM->F->Cate_1] == 0) {
				// ---------------任务系统：上传模型成功增加2积分
				$HJ = new HookJobsModel ();
				$jf_result = $HJ->run ( $this->_session ( 'f_userid' ), __METHOD__ );
				if ($jf_result) {
					$showmessage = ", 积分增加" . $jf_result [0] ['val'] . " ";
				}
				// ----------------
				// return $this->success ( '保存完成, 并增加积分' . $jf_result [0]
				// ['val'], $this->UrlHome );
				$nexturl = WEBROOT_PATH . "/user.php/webgl/index/pid/" . $PID;
				// return $this->success ( '保存完成, 并增加积分' . $jf_result [0]
				// ['val'],$nexturl);
				// redirect ( $nexturl );
			} else {
				return $this->success ( '保存完成.' );
			}
		} else {
			$JsonPhoto = $PPM->getPhotosJsonByPID ( $PID );
			if ($JsonPhoto === false) {
				echo '{"isSuccess":false, "Message":"数据库连接失败"}';
				return;
			}
			
			//
			// 打印模型
			// ProductPrintModels
			$PRODUCTTYPE = C ( 'PRODUCT.TYPE' );
			if (($Product ['p_producttype'] == $PRODUCTTYPE ['PRINTMODEL']) && ($Product ['pm_isprready'] == 1)) {
				$PRM = new ProductPrintModelsModel ();
				$prmRes = $PRM->getByp_id ( $PID );
				
				// 打印材料信息
				$materialsArr = $PRM->getMaterials ();
				
				// ProductSupportMaterials
				$PSM = new ProductPMMaterialModel ();
				$condition = array (
						$PSM->F->PID => $PID 
				);
				$psmRes = $PSM->where ( $condition )->select ();
				
				// print_r($psmRes);
				
				if ((count ( $psmRes ) == 1) && ($psmRes [0] [$PSM->F->MATERIALID] == 0)) {
					$supportAllMaterials = 1;
				} else {
					$supportAllMaterials = 0;
				}
				
				if ((! $supportAllMaterials) && ($psmRes)) {
					foreach ( $materialsArr as $key => $val ) {
						$ChildCount = count ( $val ['Child'] );
						$addCount = 0;
						foreach ( $val ['Child'] as $k => $v ) {
							foreach ( $psmRes as $kp => $vp ) {
								if (($v ['pma_id'] == $vp ['pma_id'])) {
									$addCount += 1;
									$materialsArr [$key] ['Child'] [$k] ['checked'] = 1;
									
									if ($vp ['ppm_enabled'] == 1) {
										$materialsArr [$key] ['Child'] [$k] ['enchecked'] = 1;
									} else {
										$materialsArr [$key] ['Child'] [$k] ['enchecked'] = 0;
									}
								}
							}
							if ($addCount == $ChildCount) {
								$materialsArr [$key] ['checked'] = 1;
							} elseif ($addCount == 0) {
								$materialsArr [$key] ['checked'] = 0;
							} else {
								$materialsArr [$key] ['checked'] = 1;
							}
						}
					}
				}
				
				$this->assign ( 'allmaterials', $supportAllMaterials );
				$this->assign ( 'supportMaterials', $materialsArr );
				$this->assign ( 'PrintModels', $prmRes );
			}
			
			$Product [$PM->F->Tags] = preg_replace ( '/ /', ',', $Product [$PM->F->Tags] );
			$this->assign ( 'JsonPhoto', $JsonPhoto );
			$this->assign ( 'Product', $Product );
			$this->assign ( 'DBF_P', $PM->F );
			$this->assign ( 'DBF_PF', $this->DBF->ProductFile );
			$this->assign ( 'DBF_PM', $this->DBF->ProductModel );
			$this->assign ( 'HtmlCtrl', $this->getEditHtmlCtrl () );
		}
		
		$this->assign ( 'SessionID', session_id () );
		$this->assign ( 'PID', $PID ? $PID : 0 );
		$this->_renderPage ();
	}
	public function deletefile() {
		$FileIDArray = $this->getDeleteFileID ();
		$UID = session ( 'f_userid' );
		if ($FileIDArray !== false) {
			$FileArray = $this->checkFileIDArray ( $FileIDArray, $UID );
			if ($FileArray === array ()) {
				echo '{"isSuccess":false, "Message":"没有删除文件的权限"}';
				return;
			}
			$PFM = new ProductFileModel ();
			$PFM->startTrans ();
			if ($PFM->deleteFileByID ( array_keys ( $FileArray ) )) {
				$PFM->commit ();
				echo '{"isSuccess":true}';
			} else {
				$PFM->rollback ();
				echo '{"isSuccess":false, "Message":"删除失败"}';
			}
		} else {
			echo '{"isSuccess":false, "Message":"没有指定要删除的文件"}';
		}
	}
	public function deletephoto() {
		$UID = session ( 'f_userid' );
		
		$PhotoID = $this->getDeletePhotoID (); // !
		                                       // var_dump($PhotoID);
		                                       // exit;
		if (! $PhotoID) {
			echo '{"isSuccess":false, "Message":"没有删除权限"}';
			return;
		}
		$PPM = new ProductPhotoModel ();
		$Photo = $PPM->find ( $PhotoID );
		if (! $Photo) {
			echo '{"isSuccess":false, "Message":"连接失败"}';
			return;
		}
		$PM = new ProductModel ();
		$Product = $PM->getProductByID ( $Photo [$PPM->F->ProductID] );
		if (! $Product) {
			echo '{"isSuccess":false, "Message":"连接失败"}';
			return;
		}
		
		/*
		 * if($Product[$PM->F->Creater] != $UID) { echo '{"isSuccess":false,
		 * "Message":"没有删除权限"}'; return; }
		 */
		
		// exit;
		if ($PPM->deletePhoto ( $PhotoID )) {
			echo '{"isSuccess":true}';
		} else {
			echo '{"isSuccess":false, "Message":"删除失败"}';
		}
	}
	private function getProductID($Mode = 'get') {
		$PVC = new PVC2 ( $Mode );
		$PVC->isInt ()->validateMust ()->Between ( 1, null )->add ( 'pid' );
		if ($PVC->verifyAll ()) {
			return $PVC->ResultArray ['pid'];
		} else {
			return false;
		}
	}
	private function getEditPost() {
		if (! isset ( $_POST ['lictype'] )) {
			return false;
		}
		$PVC = new PVC2 ();
		$PVC->setModePost ();
		$LicType = $_POST ['lictype'];
		$ShareBy = $_POST ['shareby'];
		// var_dump($LicType);
		// exit;
		$PVC->isString ()->validateMust ()->add ( 'name' );
		$PVC->isInt ()->validateMust ()->add ( 'lictype' );
		$PVC->isInt ()->validateMust ()->add ( 'shareby' );
		if ($ShareBy == 3) {
			$PVC->isInt ()->Between ( 0 )->validateMust ()->add ( 'vprice' );
		} elseif ($ShareBy == 1) {
			$PVC->isInt ()->Between ( 0 )->validateMust ()->add ( 'price' );
		}
		if ($ShareBy == 1) {
			// $PVC->isInt ()->validateMust ()->add ( 'shareby1' );
		} else {
			$PVC->isInt ()->validateMust ()->add ( 'sharebyother' );
		}
		$PVC->isInt ()->Between ( 0 )->validateMust ()->add ( 'designfee' );
		// var_dump($ShareBy);
		$PVC->isInt ()->validateMust ()->add ( 'cate1' );
		$PVC->isInt ()->validateNotNull ()->add ( 'cate2' );
		$PVC->isIntBool ()->validateMust ()->add ( 'istexture' );
		$PVC->isIntBool ()->validateMust ()->add ( 'ismaterials' );
		$PVC->isJsonArray ()->validateMust ()->add ( 'photoinfo' );
		$PVC->isInt ()->validateMust ()->add ( 'status' );
		$PVC->isIntBool ()->validateNotNull ()->add ( 'isprint' );
		if ($LicType == 1) 		// 原创
		{
			$PVC->isInt ()->Between ( 1, 3 )->validateMust ()->add ( 'shareby' );
			$PVC->isInt ()->Between ( 1, 8 )->validateMust ()->add ( 'geometry' );
			$PVC->isInt ()->validateMust ()->add ( 'mesh' );
			$PVC->isInt ()->validateMust ()->add ( 'vertices' );
			$PVC->isIntBool ()->validateMust ()->add ( 'isanimation' );
			$PVC->isIntBool ()->validateMust ()->add ( 'isrigged' );
			$PVC->isIntBool ()->validateMust ()->add ( 'isuvlayout' );
			$PVC->isInt ()->Between ( 1, 5 )->validateMust ()->add ( 'unwrappeduvs' );
			$PVC->isString ()->validateMust ()->add ( 'intro' );
			$PVC->Split ( ',' )->validateMust ()->add ( 'tags' );
		} else {
			$PVC->isInt ()->Between ( 0, 3 )->validateMust ()->add ( 'shareby' );
			$PVC->isInt ()->validateNotNull ()->add ( 'geometry' );
			$PVC->isInt ()->validateNotNull ()->add ( 'mesh' );
			$PVC->isInt ()->validateNotNull ()->add ( 'vertices' );
			$PVC->isIntBool ()->validateNotNull ()->add ( 'isanimation' );
			$PVC->isIntBool ()->validateNotNull ()->add ( 'isrigged' );
			$PVC->isIntBool ()->validateNotNull ()->add ( 'isuvlayout' );
			$PVC->isInt ()->validateNotNull ()->add ( 'unwrappeduvs' );
			$PVC->isString ()->validateNotNull ()->add ( 'intro' );
			$PVC->Split ( ',' )->validateNotNull ()->add ( 'tags' );
		}
		
		if (! $PVC->verifyAll ()) {
			print_r ( $PVC->Error );
			return false;
		}
		return $PVC->ResultArray;
	}
	
	/**
	 *
	 * @param ProductCreateToolModel $PCTModel        	
	 * @return int:
	 */
	private function getPostFileInfo($PCTModel, $UID, $PID) {
		$PVC = new PVC2 ();
		$PVC->isJsonArray ()->validateMust ()->add ( 'FileData' );
		if (! $PVC->verifyAll ()) {
			return false;
		}
		$FileData = $PVC->ResultArray ['FileData'];
		$FileIDArray = array ();
		foreach ( $FileData as $File ) {
			$File ['CT'] = $File ['CT'] || $File ['CT'] == 'null' ? $File ['CT'] : 0;
			$File ['SCT'] = $File ['SCT'] || $File ['SCT'] == 'null' ? $File ['SCT'] : 0;
			if (! $PCTModel->isRootTool ( $File ['CT'] )) {
				return false;
			}
			if ($File ['SCT'] && ! $PCTModel->isSubTool ( $File ['CT'], $File ['SCT'] )) {
				return false;
			}
			$FileIDArray [] = $File ['FileID'];
		}
		$FileIDArray = $this->checkFileIDArray ( $FileIDArray, $UID, $PID );
		return $this->checkFileListInIDArray ( $FileIDArray, $FileData, 'FileID' );
	}
	private function getDeleteFileID() {
		$PVC = new PVC2 ( 'post' );
		$PVC->isJsonArray ()->validateMust ()->add ( 'fileid' );
		if ($PVC->verifyAll ()) {
			return $PVC->ResultArray ['fileid'];
		} else {
			return false;
		}
	}
	private function getDeletePhotoID() {
		$PVC = new PVC2 ( 'post' );
		$PVC->isJsonArray ()->validateMust ()->add ( 'photoid' );
		if ($PVC->verifyAll ()) {
			return $PVC->ResultArray ['photoid'];
		} else {
			return false;
		}
	}
	private function getEditHtmlCtrl() {
		$HtmlCtrl = array ();
		$HtmlCtrl ['shareby'] = array (
				0 => '- 请选择 -',
				// 2 => '无偿分享',
				3 => '模型分享',
				1 => '出售模型' 
		);
		
		$HtmlCtrl ['geometry'] = array (
				0 => '- 请选择 -',
				1 => 'Polygonal',
				2 => 'NURBS',
				3 => 'Subdivision',
				4 => 'Polygonal Quads/Tris',
				5 => 'Polygonal Quads only',
				6 => 'Polygonal Tris only',
				7 => 'Polygonal Ngons used',
				8 => 'Unknown' 
		);
		$HtmlCtrl ['unwrappeduvs'] = array (
				0 => '- 请选择 -',
				1 => 'Unknown',
				2 => 'Yes, non-overlapping',
				3 => 'Yes, overlapping',
				4 => 'Mixed',
				5 => 'No' 
		);
		
		$PPermitM = new ProductPermitModel ();
		
		$Permit1 = $PPermitM->getPermitByType ( 1 );
		
		$PermitOther = array ();
		
		foreach ( $Permit1 as $Permit ) {
			$PermitOther [$Permit [$PPermitM->F->ID]] = $Permit [$PPermitM->F->Name];
		}
		$HtmlCtrl ['permitother'] = $PermitOther;
		
		$CPM = new CategoryPickerModel ();
		$Cate = $CPM->getOptionArray ( 1, array (
				1147,
				1249,
				1250,
				1251,
				1252,
				1253,
				1254,
				1255,
				1256,
				1257 
		) );
		$Cate2 = $CPM->getOptionArray ( 1, array (
				1035,
				1036,
				1037,
				1052,
				1077,
				1078,
				1105,
				1144,
				1147,
				1148,
				1149,
				1229,
				1016,
				1014,
				1002,
				1003,
				1005,
				1011 
		) );
		
		$HtmlCtrl ['cate1'] = $Cate;
		$HtmlCtrl ['cate2'] = $Cate2;
		return $HtmlCtrl;
	}
	private function setProductCover($Product, $CoverID) {
		$DBF_P = $this->DBF->Product;
		if ($Product [$DBF_P->Cover_ID] == $CoverID) {
			return null;
		}
		$PPM = new ProductPhotoModel ();
		$Photo = $PPM->find ( $CoverID );
		if (! $Photo) {
			return false;
		}
		$PM = new ProductModel ();
		$PM->{$PM->F->ID} = $Product [$PM->F->ID];
		$PM->{$PM->F->Cover} = $Photo [$PPM->F->Path] . 'o/' . $Photo [$PPM->F->FileName];
		$PM->{$PM->F->Cover_ID} = $CoverID;
		return $PM->save ();
	}
	private function checkFileIDArray($IDArray, $UID, $PID) {
		$PFM = new ProductFileModel ();
		$FileList = $PFM->getFileByIDArray ( $IDArray, $UID );
		if (! $FileList) {
			return null;
		}
		$IDArray = array ();
		foreach ( $FileList as $File ) {
			if ($File [$PFM->F->Uploader] != $UID) {
				continue;
			} // !
			if ($PID && ($File [$PFM->F->ProductID] != 0 && $File [$PFM->F->ProductID] != $PID)) {
				continue;
			}
			$IDArray [$File [$PFM->F->ID]] = $File;
		}
		return $IDArray;
	}
	private function checkFileListInIDArray($IDArray, $List, $Key) {
		$ResultList = array ();
		foreach ( $List as $Item ) {
			if (array_key_exists ( $Item [$Key], $IDArray )) {
				$ResultList [] = array_merge ( $Item, $IDArray [$Item [$Key]] );
			}
		}
		return $ResultList;
	}
	private function saveFile($UID, $PID) {
		// echo session_id() .'|'. $UID.'|'.$PID;
		$MD5File16Name = getMD5File16 ( $_FILES ['Filedata'] ['tmp_name'] );
		$SavePath = $PID ? C ( 'UPLOAD_PAHT.PRODUCT' ) : C ( 'UPLOAD_PAHT.PRODUCT_TEMP' );
		$SubDir = $PID ? getSavePathByID ( $PID ) : getSavePathByID ( $UID );
		import ( 'ORG.Net.UploadFile' );
		$upload = new UploadFile ();
		$upload->uploadReplace = true;
		$upload->maxSize = 1024 * 1024 * 2048;
		$upload->allowExts = array (
				'rar',
				'zip',
				'7z' 
		);
		$upload->savePath = $SavePath;
		$upload->saveRule = $MD5File16Name . '';
		$upload->autoSub = false;
		$upload->subType = 'custom';
		$upload->subDir = $SubDir;
		$FileInfo = $upload->uploadOne ( $_FILES ['Filedata'], $upload->savePath . $upload->subDir );
		
		return $FileInfo ? $FileInfo [0] : false;
	}
	private function savePhoto($PID) {
		$MD5File16Name = getMD5File16 ( $_FILES ['Photodata'] ['tmp_name'] );
		
		$SavePath = C ( 'UPLOAD_PAHT.PRODUCT_PHOTO' );
		$SubDir = getSavePathByID ( $PID );
		
		import ( 'ORG.Net.UploadFile' );
		$upload = new UploadFile ();
		$upload->uploadReplace = true;
		$upload->maxSize = 1024 * 1024 * 2048;
		
		$upload->allowExts = array (
				'jpg',
				'jpeg',
				'png' 
		);
		$upload->savePath = $SavePath . $SubDir . 'o/';
		$upload->saveRule = $MD5File16Name . '';
		$upload->thumb = true;
		$upload->thumbPath = $SavePath . $SubDir . 's/';
		$upload->thumbPrefix = '100_100_,300_300_,600_600_,800_800_,1200_1200_,64_64_,220_220_';
		$upload->thumbMaxWidth = '100,300,600,800,1200,64,220';
		$upload->thumbMaxHeight = '100,300,600,800,1200,64,220';
		$upload->thumbRemoveOrigin = false;
		$PhotoInfo = $upload->uploadOne ( $_FILES ['Photodata'] );
		
		return $PhotoInfo ? $PhotoInfo [0] : false;
	}
	private function saveTags($PID, $Tags) {
		$PTM = new ProductTagsModel ();
		$OldTags = $PTM->getTagsByProduct ( $PID );
		
		if ($OldTags === false) {
			return false;
		}
		$OldTags = $OldTags ? $OldTags : array ();
		$ExistTags = array ();
		foreach ( $OldTags as $OldTag ) {
			if (in_array ( $OldTag [$PTM->F->Name], $Tags )) {
				$ExistTags [] = $OldTag [$PTM->F->Name];
			}
		}
		if (! array_diff ( $Tags, $OldTags )) {
			return true;
		}
		if ($PTM->changTagsCount ( $ExistTags, - 1 ) === false) {
			return false;
		}
		$TagsIDArray = $PTM->addTagsArray ( $Tags );
		if ($TagsIDArray === false) {
			return false;
		}
		$PTIM = new ProductTagsIndexModel ();
		if ($PTIM->where ( $PTIM->F->ProductID . "='" . $PID . "'" )->delete () === false) {
			return false;
		}
		foreach ( $TagsIDArray as $TagID ) {
			$PTIM->{$PTIM->F->ProductID} = $PID;
			$PTIM->{$PTIM->F->TagsID} = $TagID;
			if ($PTIM->add () === false) {
				return false;
			}
		}
		return true;
	}
	private function moveFileToProductDir($FileData, $PID) {
		$F = new DBF_ProductFile ();
		$DirPath = BASE_PATH . C ( 'UPLOAD_PAHT.PRODUCT_WEB' ) . getSavePathByID ( $PID );
		foreach ( $FileData as $ID => $File ) {
			if ($File [$F->ProductID] == 0) {
				$source = BASE_PATH . $File [$F->Path] . $File [$F->FileName];
				$destination = $DirPath . $File [$F->FileName];
				if (! file_exists ( $source ) || $source == $destination) {
					continue;
				}
				if (file_exists ( $DirPath ) || mkdir ( $DirPath, 0777, true )) {
					if (! copy ( $source, $destination )) {
						return false;
					}
				} else {
					return false;
				}
			}
		}
		return true;
	}
	private function buildFileDateModel($FileInfo, $UID, $PID) {
		$PFM = new ProductFileModel ();
		$PFM->{$PFM->F->FileName} = basename ( $FileInfo ['savename'] );
		$PFM->{$PFM->F->OriginalName} = $FileInfo ['name'];
		$PFM->{$PFM->F->FileSize} = $FileInfo ['filesize'];
		$PFM->{$PFM->F->FileSize_disp} = $FileInfo ['filesize_disp'];
		$PFM->{$PFM->F->Uploader} = $UID;
		$PFM->{$PFM->F->CreateDate} = get_now ();
		$PFM->{$PFM->F->CreateTime} = time ();
		$PFM->{$PFM->F->LastUpdate} = get_now ();
		$PFM->{$PFM->F->LastUpdateTime} = time ();
		$PFM->{$PFM->F->Path} = preg_replace ( '|^./|', '/', $FileInfo ['savepath'], 1 );
		$PFM->{$PFM->F->Ext} = $FileInfo ['extension'];
		if ($PID) {
			$PFM->{$PFM->F->ProductID} = $PID;
		}
		return $PFM;
	}
	private function bulidNewPhotoDataModel($PhotoInfo, $PID) {
		$PPM = new ProductPhotoModel ();
		$PPM->{$PPM->F->OriginalName} = basename ( $PhotoInfo ['name'] );
		$PPM->{$PPM->F->FileName} = $PhotoInfo ['savename'];
		$PPM->{$PPM->F->Path} = preg_replace ( '|/o/|', '/', preg_replace ( '|^./|', '/', $PhotoInfo ['savepath'], 1 ) );
		$PPM->{$PPM->F->CreateDate} = get_now ();
		$PPM->{$PPM->F->Title} = $PhotoInfo ['name'];
		$PPM->{$PPM->F->Remark} = $PhotoInfo ['name'];
		$PPM->{$PPM->F->ProductID} = $PID;
		return $PPM;
	}
	private function buildNewProductModel($Name, $UserID) {
		$PM = new ProductModel ();
		$NameArray = split ( '[.]', $Name );
		unset ( $NameArray [count ( $NameArray ) - 1] );
		$PM->{$PM->F->Name} = implode ( '.', $NameArray );
		$PM->{$PM->F->Creater} = $UserID;
		$PM->{$PM->F->CreateDate} = get_now ();
		$PM->{$PM->F->CreateTime} = time ();
		$PM->{$PM->F->LastUpdate} = get_now ();
		$PM->{$PM->F->LastUpdateTime} = time ();
		$PM->{$PM->F->ProductType} = 3;
		return $PM;
	}
	private function bulidProductDataModel($ProductInfo, $PID) {
		$PM = new ProductModel ();
		$PM->{$PM->F->ID} = $PID;
		$PM->{$PM->F->Name} = $ProductInfo ['name'];
		$PM->{$PM->F->LastUpdate} = get_now ();
		$PM->{$PM->F->LastUpdateTime} = time ();
		$PM->{$PM->F->Cate_1} = $ProductInfo ['cate1'];
		$PM->{$PM->F->Cate_2} = $ProductInfo ['cate2'];
		$PM->{$PM->F->DesignPrice} = $ProductInfo ['designfee'];
		$PM->{$PM->F->Price} = $ProductInfo ['price'];
		$PM->{$PM->F->VPrice} = $ProductInfo ['vprice'];
		$PM->{$PM->F->Intro} = $ProductInfo ['intro'];
		$PM->{$PM->F->Slabel} = $ProductInfo ['status'] == 1 ? 1 : 0;
		$PM->{$PM->F->LicType} = $ProductInfo ['lictype'];
		$PM->{$PM->F->DownloadLimit} = $ProductInfo ['shareby'];
		$PM->{$PM->F->License} = $ProductInfo ['sharebyother'] ? $ProductInfo ['sharebyother'] : $ProductInfo ['shareby1'];
		$TagsStr = '';
		foreach ( $ProductInfo ['tags'] as $Tags ) {
			$TagsStr .= $Tags . ' ';
		}
		if (strlen ( $TagsStr ) > 0) {
			$TagsStr = substr ( $TagsStr, 0, strlen ( $TagsStr ) - 1 );
		}
		$PM->{$PM->F->Tags} = $TagsStr;
		return $PM;
	}
	private function buildModelDataModel($ProductInfo, $PID) {
		$MM = new ModelsModel ();
		$MM->{$MM->F->ProductID} = $PID;
		$MM->{$MM->F->Geometry} = $ProductInfo ['geometry'];
		$MM->{$MM->F->Mesh} = $ProductInfo ['mesh'];
		$MM->{$MM->F->Vertices} = $ProductInfo ['vertices'];
		$MM->{$MM->F->IsTexture} = $ProductInfo ['istexture'];
		$MM->{$MM->F->IsMaterials} = $ProductInfo ['ismaterials'];
		$MM->{$MM->F->IsAnimation} = $ProductInfo ['isanimation'];
		$MM->{$MM->F->IsRigged} = $ProductInfo ['isrigged'];
		$MM->{$MM->F->IsUVLayout} = $ProductInfo ['isuvlayout'];
		$MM->{$MM->F->UnWrappedUVs} = $ProductInfo ['unwrappeduvs'];
		$MM->{$MM->F->IsPrint} = $ProductInfo ['isprint'];
		return $MM;
	}
	private function buildPhotoDataModel($PhotoInfo) {
		$PPM = new ProductPhotoModel ();
		$PPM->{$PPM->F->ID} = $PhotoInfo ['PID'];
		$PPM->{$PPM->F->Title} = $PhotoInfo ['Title'];
		$PPM->{$PPM->F->DispWeight} = $PhotoInfo ['Weight'];
		return $PPM;
	}
	private function setFileDataModel($PFM, $File, $PID, $DirPath) {
		$PFM->{$PFM->F->ID} = $File [$PFM->F->ID];
		$PFM->{$PFM->F->CreateTool} = $File ['CT'];
		$PFM->{$PFM->F->CTVersion} = $File ['CTV'];
		$PFM->{$PFM->F->SubCreateTool} = $File ['SCT'];
		$PFM->{$PFM->F->SubCTVersion} = $File ['SCTV'];
		if ($File [$PFM->F->ProductID] == 0) {
			$PFM->{$PFM->F->ProductID} = $PID;
			$PFM->{$PFM->F->Path} = $DirPath;
		}
		return $PFM;
	}
	private function deleteTempFile($FileData) {
		$F = new DBF_ProductFile ();
		foreach ( $FileData as $ID => $File ) {
			if ($File [$F->ProductID] == 0) {
				$source = BASE_PATH . $File [$F->Path] . $File [$F->FileName];
				@unlink ( $source );
			}
		}
		return true;
	}
	public function testimg() {
		import ( 'ORG.Net.ImageCheck' );
		$imagea = new ImageCheck ();
		$t = $imagea->imagezoom ( "./upload/1.jpg", "./upload/2.jpg", 100, 100, "#FFFFFF" );
	}
	public function testuposs() {
		// -----------------------------工具Pid上传文件到OSS start
		$OSSM = new AliossModel ();
		$res = $OSSM->upfileOssByPidSingle ( 21007 );
		// -----------------------------工具Pid上传文件到OSS end
	}
}
?>