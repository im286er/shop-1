<?php
/**
 * 模型相关API
 *
 * @author miaomin 
 * Oct 9, 2013 4:22:48 PM
 * 
 * $Id: ModelsAction.class.php 1230 2014-02-20 02:22:58Z miaomiao $
 */
class ModelsAction extends CommonAction {
	
	// TODO
	// 魔术方法
	public function __call($name, $arguments) {
		throw new Exception ( $this->RES_CODE_TYPE ['METHOD_ERR'] );
	}
	
	/**
	 * 获取3DCP成本库模型
	 */
	public function getgoods() {
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		$SearchInfo = array (
				'count' => $args ['count'] ? $args ['count'] : NULL,
				'page' => $args ['page'] ? $args ['page'] : NULL,
				'iscp' => $args ['iscp'] ? $args ['iscp'] : NULL,
				'order' => $args ['order'] ? $args ['order'] : NULL,
				'category' => $args ['category'] ? $args ['category'] : NULL 
		);
		// pr($SearchInfo);
		$PSM = new ProductSearchModel ( $SearchInfo, 'model', true );
		$goodsRes [] = $PSM->getResult ( $SearchInfo ['page'] );
		$goodsRes [] = $PSM->TotalCount;
		// 返回
		$res = $goodsRes;
		// pr($res);
		// exit;
		return $res;
	}
	
	/**
	 * 获取RP360精品库模型
	 */
	public function getmodelbyid() {
		$res = array ();
		
		// 获取参数
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		// 解析用户信息
		if ($args ['visa']) {
			$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
			if (! $logindata) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
			} else {
				$Users = new UsersModel ();
				$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
				if (! $userinfo) {
					throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
				}
			}
		}
		
		// 模型id
		$idList = json_decode ( $args ['idlist'] );
		
		// 模型基本信息
		$PM = new ProductModel ();
		$whereIn = db_create_in ( $idList, $PM->F->_Table . '.' . $PM->F->ID );
		$pmFindQuery = "SELECT tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_price,tdf_product.p_tags,tdf_product.p_intro,tdf_product.p_createdate,tdf_product.p_lastupdate,tdf_product_model.pm_geometry,tdf_product_model.pm_mash,tdf_product_model.pm_vertices,tdf_product_model.pm_geometry FROM tdf_product LEFT JOIN tdf_product_model ON (tdf_product_model.p_id = tdf_product.p_id) WHERE " . $whereIn;
		$pmFindRes = $PM->query ( $pmFindQuery );
		if ($pmFindRes) {
			foreach ( $pmFindRes as $key => $val ) {
				$res ['modelinfo'] [$val [$PM->F->ID]] [] = $val;
			}
		}
		
		// 模型图片
		$PP = new ProductPhotoModel ();
		$whereIn = db_create_in ( $idList, $PP->F->ProductID );
		$ppFindQuery = 'SELECT pp_id,pp_filename,pp_path,p_id FROM tdf_product_photo WHERE ' . $whereIn;
		$ppFindRes = $PP->query ( $ppFindQuery );
		if ($ppFindRes) {
			foreach ( $ppFindRes as $key => $val ) {
				$res ['photolist'] [$val [$PP->F->ProductID]] [] = $val;
			}
		}
		
		// 模型文件
		$PPF = new ProductFileModel ();
		$whereIn = db_create_in ( $idList, $PPF->F->ProductID );
		$ppfFindQuery = 'SELECT pf_id,pf_filename,pf_filesize,pf_filesize_disp, pf_path,pf_ext,p_id FROM tdf_product_file WHERE ' . $whereIn;
		$ppfFindRes = $PPF->query ( $ppfFindQuery );
		if ($ppfFindRes) {
			foreach ( $ppfFindRes as $key => $val ) {
				$res ['filelist'] [$val [$PPF->F->ProductID]] [] = $val;
			}
		}
		
		// 返回
		return $res;
	}
	
	/**
	 * 获取3DCP成本库模型分类
	 */
	public function getgoodscate() {
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		$SearchInfo = array (
				'count' => $args ['count'] ? $args ['count'] : NULL,
				'page' => $args ['page'] ? $args ['page'] : NULL,
				'iscp' => $args ['iscp'] ? $args ['iscp'] : NULL,
				'order' => $args ['order'] ? $args ['order'] : NULL,
				'category' => $args ['category'] ? $args ['category'] : NULL 
		);
		
		$PSM = new ProductSearchModel ( $SearchInfo, 'model', true );
		$CateGroupRes = $PSM->getCateGroupRes ();
		
		// 返回
		$res = $CateGroupRes;
		return $res;
	}
	
	/**
	 * 文件是否存在
	 *
	 * 判断云里是否有这个文件判断用户文件表中是否有这个文件
	 * 如果都有直接返回结果
	 *
	 * 如果云里有用户文件表里没有
	 * 直接操作用户文件表
	 *
	 * 如果云里没有
	 * 上传开始
	 *
	 * @return unknown
	 */
	public function isfileexist() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		$md5 = $args ['md5'];
		$sha1 = $args ['sha1'];
		
		// 解析用户信息
		if ($args ['visa']) {
			$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
			if (! $logindata) {
				throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
			} else {
				$Users = new UsersModel ();
				$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
				if (! $userinfo) {
					throw new Exception ( $this->RES_CODE_TYPE ['USER_INFO_ERR'] );
				}
			}
		}
		
		$YFM = new YunFilesModel ();
		$YunFile = $YFM->helper->validYunFileExist ( $md5, $sha1 );
		
		if (! $YunFile) {
			// 云里没有该文件
			$res [] = 0;
			return $res;
		}
		
		if ($userinfo) {
			$UF = new UserFilesModel ();
			$ufRes = $UF->getUserfiles ( $userinfo [0] ['u_id'], $YunFile ['yf_id'] );
			if (! $ufRes) {
				// 云里有但用户没有该文件
				$YunFile ['f_stat'] = - 1;
				$YunFile ['uf_id'] = 0;
				// $res [] = - 1;
				$res [] = $YunFile;
				return $res;
			}
			
			// 在云里并且和用户关联
			$YunFile ['f_stat'] = 1;
			$YunFile ['uf_id'] = $ufRes [0] ['uf_id'];
		} else {
			// 没有用户信息直接返回YUN文件信息
			$YunFile ['f_stat'] = - 2;
			$YunFile ['uf_id'] = 0;
		}
		
		$res [] = $YunFile;
		return $res;
	}
	
	/**
	 * 发送邮件API
	 */
	public function sendmail() {
		import ( "App.Action.User.MailValidateAction" );
		
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		$userId = $args ['uid'];
		$userMail = $args ['mail'];
		
		if ($userId && $userMail) {
			$MVA = new MailValidateAction ();
			$res [] = $MVA->sendRegisterMail ( $userId, $userMail );
		} else {
			throw new Exception ( $this->RES_CODE_TYPE ['PARAMETER_METHOD_ERR'] );
		}
		
		return $res;
	}
	
	/**
	 * 块上传检查
	 *
	 * 在块上传前会先将主文件的MD5和SHA1信息提交到服务端
	 * 服务端会返回当前主文件已经上传的块文件列表信息
	 *
	 * @return array
	 */
	public function checkblock() {
		$res = array ();
		
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		
		$md5 = $args ['md5'];
		$sha1 = $args ['sha1'];
		
		if ($md5 && $sha1) {
			$BFM = new BlockFilesModel ();
			$blist = $BFM->getBlockList ( $md5, $sha1 );
			$res [] = $blist ? $blist : 0;
		} else {
			throw new Exception ( $this->RES_CODE_TYPE ['PARAMETER_METHOD_ERR'] );
		}
		
		return $res;
	}
	
	/**
	 * 分析BLOCKPOSITION
	 *
	 * @param unknown_type $blockpos        	
	 */
	private function parseBlockPosition($blockpos) {
		if ($blockpos) {
			return explode ( '-', $blockpos );
		}
		return false;
	}
	
	/**
	 * 校验渲染图文件上传的参数有效性
	 *
	 * @param unknown_type $args        	
	 */
	private function validExistFileUploadParameters($args) {
		$res = false;
		
		if ($args ['yfid'] && $args ['filename'] && isset ( $args ['folderid'] )) {
			$res = true;
		}
		
		return $res;
	}
	
	/**
	 * 校验渲染图文件上传的参数有效性
	 *
	 * @param unknown_type $args        	
	 */
	private function validRenderUploadParameters($args) {
		$res = false;
		
		if ($args ['yfid']) {
			$res = true;
		}
		
		return $res;
	}
	
	/**
	 * 校验块文件上传的参数有效性
	 *
	 * @param unknown_type $args        	
	 */
	private function validBlockUploadParameters($args) {
		$res = false;
		
		// 必须有$args['md5']、$args['sha1']、$args['targetext']
		$res1 = false;
		if ($args ['md5'] && $args ['sha1'] && $args ['targetext']) {
			$res1 = true;
		}
		
		// $args['blockpos'] 的格式必须是'1-8'这样格式
		$res2 = false;
		$res3 = false;
		// debug
		// 没有考虑十位数或者百位数带有0的数字
		// $pattern = '/^[1-9]*-[1-9]*$/';
		$pattern = '/^[1-9]{1}[0-9]*-[1-9]{1}[0-9]*$/';
		
		$res2 = preg_match ( $pattern, $args ['blockpos'] );
		if ($res2) {
			$blockposarr = $this->parseBlockPosition ( $args ['blockpos'] );
			if ($blockposarr [0] <= $blockposarr [1]) {
				$res3 = true;
			}
		}
		
		if ($res1 && $res2 && $res3) {
			$res = true;
		}
		
		return $res;
	}
	
	/**
	 * 上传单个文件
	 *
	 * @return 二维数组 / boolean
	 */
	public function upfile() {
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
		}
		
		// TODO
		// 断点续传的考虑
		$CFG_UPTYPE = C ( 'CLIENT_UPLOAD_TYPE' );
		if ($args ['uptype'] == $CFG_UPTYPE ['BLOCK']) {
			
			// 检查参数
			if (! $this->validBlockUploadParameters ( $args )) {
				throw new Exception ( $this->RES_CODE_TYPE ['PARAMETERS_ERR'] );
			}
			
			// 最后一个文件的标志
			$lastBlock = 0;
			// 返回结果
			$res = array ();
			
			// 处理块文件位置
			$blockpos = $args ['blockpos'];
			$blockposarr = $this->parseBlockPosition ( $blockpos );
			if ($blockposarr [0] == $blockposarr [1]) {
				$lastBlock = 1;
			}
			
			// 预载入
			load ( '@.YunUploader' );
			load ( '@.FileChecker' );
			$YU = new YunUploader ();
			$FC = new FileChecker ();
			$BFM = new BlockFilesModel ();
			$Yun = new YunFilesModel ();
			$UFM = new UserFilesModel ();
			$PM = new PrinterModelModel ();
			
			// 上传块文件
			$path = getSavePathByMd5 ( $args ['md5'] );
			$FileInfo = $YU->uploadBlock ( $path, $_FILES ['filename1'] );
			
			// 上传失败
			if (! $FileInfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['UPLOAD_FILE_FAILED'] );
			}
			
			// 准备写入BLOCKFILE
			$data = array ();
			// $data ['bf_fullname'] = iconv('GB2312', 'UTF-8', $FileInfo
			// ['name']);
			// $data ['bf_name'] = $FC->getFileName ( iconv('GB2312', 'UTF-8',
			// $FileInfo ['name']) );
			// $data ['bf_ext'] = $FC->getFileExt ( $FileInfo ['name'] );
			$data ['bf_fullname'] = $args ['filename'];
			$data ['bf_name'] = $FC->getFileName ( $args ['filename'] );
			$data ['bf_ext'] = $FC->getFileExt ( $args ['filename'] );
			$data ['bf_full_md5'] = $args ['md5'];
			$data ['bf_full_sha1'] = $args ['sha1'];
			$data ['bf_order'] = $blockposarr [0];
			$data ['bf_path'] = $FileInfo ['savepath'] . $FileInfo ['savename'];
			$data ['bf_size'] = $FileInfo ['size'];
			$data ['bf_md5'] = $FileInfo ['hash'];
			$data ['bf_sha1'] = $FC->getFileName ( $FileInfo ['savename'] );
			
			// 写入BLOCKFILE之前一定要保证唯一性
			// $BFileID = $BFM->isBlockFileExist ( $data ['bf_md5'], $data
			// ['bf_sha1'] );
			$BFileID = $BFM->isBlockFileExistStrict ( $data ['bf_md5'], $data ['bf_sha1'], $data ['bf_full_md5'], $data ['bf_full_sha1'] );
			if (! $BFileID) {
				$BFM->create ( $data );
				$BFileID = $BFM->add ();
			}
			
			// 写入BLOCKFILE失败
			if (! $BFileID) {
				throw new Exception ( $this->RES_CODE_TYPE ['WRITE_BLOCK_TABLE_ERR'] );
			}
			
			$res = $BFM->select ( $BFileID );
			
			// 判断是否是最后一个块文件
			if ($lastBlock) {
				
				// 合并文件前先判断一下这个文件有了吗
				$YunFile = $Yun->helper->validYunFileExist ( $args ['md5'], $args ['sha1'] );
				$YFtargetID = $YunFile ['yf_id'];
				if (! $YFtargetID) {
					// 如果没有开始合并文件
					$meg_res = $BFM->mergeBlockList ( $args ['md5'], $args ['sha1'], $args ['targetext'], $blockposarr [1] );
					
					// 合并文件失败
					if (! $meg_res) {
						throw new Exception ( $this->RES_CODE_TYPE ['MERGE_BLOCK_FILE_ERR'] );
					}
					
					$meg_res ['name'] = $args ['targetname'];
					$YFtarget = $YU->buildYunFileData ( $meg_res );
					$YFtargetID = $YFtarget->add ();
					
					if (! $YFtargetID) {
						throw new Exception ( $this->RES_CODE_TYPE ['WRITE_YUN_TABLE_ERR'] );
					}
					
					$YunFile = $Yun->find ( $YFtargetID );
				}
				
				// 插入USERFILE
				$Folder = isset ( $args ['folder'] ) ? $args ['folder'] : 0;
				$UFID = $UFM->helper->createUserFile ( $userinfo [0] ['u_id'], $Folder, $YFtargetID, $args ['targetname'] );
				if (! $UFID) {
					throw new Exception ( $this->RES_CODE_TYPE ['WRITE_UF_TABLE_ERR'] );
				}
				
				// 插入PRINTMODEL前先判断是否已经有这个文件了
				// 如果已经有了更新文件
				$pmRes = $PM->find ( $YFtargetID );
				if (! $pmRes) {
					$boundbox = explode ( '*', $args ['boundbox'] );
					$PM->create ( $YunFile );
					// $PM->yf_id = $YFtargetID;
					$PM->pm_status = 2;
					$PM->pm_needverify = 1;
					$PM->pm_volume = ( float ) $args ['volume'] ? ( float ) $args ['volume'] : 0;
					$PM->pm_surface = ( float ) $args ['surface'] ? ( float ) $args ['surface'] : 0;
					$PM->pm_length = ( float ) $boundbox [0] ? ( float ) $boundbox [0] : 0;
					$PM->pm_width = ( float ) $boundbox [1] ? ( float ) $boundbox [1] : 0;
					$PM->pm_height = ( float ) $boundbox [2] ? ( float ) $boundbox [2] : 0;
					$pmRes = $PM->add ();
					if (! $pmRes) {
						throw new Exception ( $this->RES_CODE_TYPE ['WRITE_PM_TABLE_ERR'] );
					}
				} else {
					// 更新
					if ((! $args ['volume']) || (! $args ['surface'])) {
						throw new Exception ( $this->RES_CODE_TYPE ['WRITE_PM_TABLE_ERR'] );
					}
					$PM->pm_status = 2;
					$PM->pm_needverify = 1;
					$PM->pm_volume = ( float ) $args ['volume'] ? ( float ) $args ['volume'] : 0;
					$PM->pm_surface = ( float ) $args ['surface'] ? ( float ) $args ['surface'] : 0;
					$PM->pm_length = ( float ) $boundbox [0] ? ( float ) $boundbox [0] : 0;
					$PM->pm_width = ( float ) $boundbox [1] ? ( float ) $boundbox [1] : 0;
					$PM->pm_height = ( float ) $boundbox [2] ? ( float ) $boundbox [2] : 0;
					$pmRes = $PM->save ();
					if (! $pmRes) {
						throw new Exception ( $this->RES_CODE_TYPE ['WRITE_PM_TABLE_ERR'] );
					}
				}
				
				// 插入PRINTMODELMATERIALS(可后期通过专门的接口更新)
				
				// 清理Block信息
				if (! $BFM->clearBlocklist ( $args ['md5'], $args ['sha1'] )) {
					throw new Exception ( $this->RES_CODE_TYPE ['CLEAR_BLOCK_FILE_ERR'] );
				}
				
				$res = $Yun->select ( $YFtargetID );
				// 加入UF信息
				foreach ( $res as $key => $val ) {
					$res [$key] ['uf_id'] = $UFID;
					$res [$key] ['f_stat'] = 1;
				}
			}
			// 返回结果
			return $res;
		} elseif ($args ['uptype'] == $CFG_UPTYPE ['FILE']) {
			load ( '@.YunUploader' );
			$YU = new YunUploader ();
			$YunFile = $YU->uploadOne ( $_FILES ['filename1'] );
			$FileID = $YunFile ['yf_id'];
			
			if ($FileID) {
				$logindata = $this->parseRequestUserHandle ( $args ['visa'] );
				if ($logindata) {
					$Users = new UsersModel ();
					$userinfo = $Users->getUserInfo ( $logindata [0], $logindata [1] );
				}
				
				if ($userinfo) {
					$Folder = isset ( $args ['folder'] ) ? $args ['folder'] : 0;
					$UFM = new UserFilesModel ();
					if ($UFM->helper->createUserFile ( $userinfo [0] ['u_id'], $Folder, $FileID, $_FILES ['filename1'] ['name'] )) {
						$Yun = new YunFilesModel ();
						return $Yun->select ( $FileID );
					}
				}
			}
		} elseif ($args ['uptype'] == $CFG_UPTYPE ['RENDER']) {
			// 检查参数
			if (! $this->validRenderUploadParameters ( $args )) {
				throw new Exception ( $this->RES_CODE_TYPE ['PARAMETERS_ERR'] );
			}
			
			// 预载入
			load ( '@.YunUploader' );
			load ( '@.FileChecker' );
			$YU = new YunUploader ();
			$FC = new FileChecker ();
			$Yun = new YunFilesModel ();
			$PM = new PrinterModelModel ();
			
			// 上传文件
			// $path = getSavePathByMd5 ( $args ['md5'] );
			$path = getSavePathByID ( $args ['yfid'] );
			$FileInfo = $YU->uploadRender ( $path, $args ['yfid'], $_FILES ['filename1'] );
			
			// 上传失败
			if (! $FileInfo) {
				throw new Exception ( $this->RES_CODE_TYPE ['UPLOAD_FILE_FAILED'] );
			}
			
			// 写入PrinterModel
			$pmRes = $PM->find ( $args ['yfid'] );
			if (! $pmRes) {
				throw new Exception ( $this->RES_CODE_TYPE ['USERFILE_NOT_EXIST'] );
			}
			$pmCover = $FileInfo ['savepath'] . $FileInfo ['savename'];
			if (substr ( $pmCover, 0, 1 ) === '.') {
				$pmCover = substr ( $pmCover, 1 );
			}
			$PM->{$PM->F->Cover} = $pmCover;
			$pmRes = $PM->save ();
			// @formatter:off
			/*
			if (! $pmRes) {
				throw new Exception ( $this->RES_CODE_TYPE ['WRITE_UF_TABLE_ERR'] );
			}
			*/
			// @formatter:on
			$res [] = true;
			return $res;
		} elseif ($args ['uptype'] == $CFG_UPTYPE ['EXISTFILE']) {
			// 检查参数
			
			if (! $this->validExistFileUploadParameters ( $args )) {
				throw new Exception ( $this->RES_CODE_TYPE ['PARAMETERS_ERR'] );
			}
			$folderId = ( int ) $args ['folderid'];
			$yfId = ( int ) $args ['yfid'];
			$fileName = ( string ) $args ['filename'];
			
			// 预载入
			$Yun = new YunFilesModel ();
			$UFO = new UserFoldersModel ();
			$UF = new UserFilesModel ();
			
			//
			$yfRes = $Yun->find ( $yfId );
			if (! $yfRes) {
				throw new Exception ( $this->RES_CODE_TYPE ['YUNFILE_NOT_EXIST'] );
			}
			
			if ($folderId != 0) {
				$ufoRes = $UFO->find ( $folderId );
				if (! $ufoRes) {
					throw new Exception ( $this->RES_CODE_TYPE ['USERFOLDER_NOT_EXIST'] );
				}
			}
			
			$ufRes = $UF->getUserfiles ( $userinfo [0] ['u_id'], $yfId );
			if ($ufRes) {
				throw new Exception ( $this->RES_CODE_TYPE ['PARAMETERS_ERR'] );
			}
			
			// 写入UserModel
			$UFID = $UF->helper->createUserFile ( $userinfo [0] ['u_id'], $folderId, $yfId, $fileName );
			if (! $UFID) {
				throw new Exception ( $this->RES_CODE_TYPE ['WRITE_UF_TABLE_ERR'] );
			}
			
			$res [] = $yfRes;
			// 加入UF信息
			$res [0] ['uf_id'] = $UFID;
			$res [0] ['f_stat'] = - 1;
			return $res;
		}
		return false;
	}
	
	/**
	 * 设定可打印材质
	 *
	 * @return boolean
	 */
	public function setmaterials() {
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
		}
		
		// 接受数据
		$md5 = ( string ) $args ['md5'];
		$sha1 = ( string ) $args ['sha1'];
		$pmaIdArr = explode ( '@', $args ['pmaid'] );
		
		$YFM = new YunFilesModel ();
		$YunFile = $YFM->helper->validYunFileExist ( $md5, $sha1 );
		$FileID = $YunFile ['yf_id'];
		
		if (! $FileID) {
			$this->RES_CODE_TYPE ['FILE_NOT_EXIST'];
		}
		
		// 全部删除
		$PMM = new PrinterModelMaterialModel ();
		$PMM->where ( "yf_id='" . $FileID . "'" )->delete ();
		foreach ( $pmaIdArr as $key => $val ) {
			$PMM->create ();
			$PMM->yf_id = $FileID;
			$PMM->pma_id = $val;
			$PMM->add ();
		}
		
		$res [] = true;
		return $res;
	}
	
	/**
	 * 获取材质最大可打印尺寸
	 *
	 * @return mixed
	 */
	public function getmaterials() {
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
		
		// 获取材质最大可打印尺寸
		$PMM = new PrinterMaterialModel ();
		$PrintMaterialList = $PMM->getMaxsize ();
		
		$res = $PrintMaterialList;
		
		return $res;
	}
	
	/**
	 * 下载
	 *
	 * @return unknown
	 */
	public function downfile() {
	}
	
	/**
	 * 计算价格
	 *
	 * @return unknown
	 */
	public function checkprice() {
		
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
		$res[] = 'Checkprice';
		
		return $res;
	}
	
	/**
	 * 获取插件信息
	 *
	 * @return unknown
	 */
	public function getpluginlist() {
		$res = array ();
		
		$CP = new ClientPluginModel ();
		$res = $CP->where ( "cp_status='" . "1'" )->select ();
		
		return $res;
	}
}
?>