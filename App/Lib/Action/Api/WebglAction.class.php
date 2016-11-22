<?php
/**
 * Webgl相关API
 *
 * @author miaomin 
 * Apr 23, 2014 2:29:20 PM
 *
 * $Id$
 */
class WebglAction extends CommonAction {
	
	// TODO
	// 魔术方法
	public function __call($name, $arguments) {
		throw new Exception ( $this->RES_CODE_TYPE ['METHOD_ERR'] );
	}
	
	/**
	 * 内部WEBGL文件修改后的保存处理
	 *
	 * @author miaomin
	 * @param int $pmwId        	
	 * @param array $webglArr        	
	 * @return mixed
	 */
	private function processWebgl($pmwID, $webglArr) {
		// ---------- 返回结果 ----------
		$returnRes = false;
		// ---------- 保存路径 ----------
		$SavePath = $webglArr ['webglpath'];
		$SaveFilename = $webglArr ['webglfilename'];
		$SaveData = $webglArr ['webgldata'];
		// ---------- 文件处理 ----------
		if (! is_dir ( $SavePath )) {
			// ---------- 检查目录是否编码后的 ----------
			if (is_dir ( base64_decode ( $SavePath ) )) {
				$SavePath = base64_decode ( $SavePath );
			} else {
				// ---------- 尝试创建目录 ----------
				if (! mkdir ( $SavePath, 0777, true )) {
					return false;
				}
			}
		} else {
			if (! is_writeable ( $SavePath )) {
				return false;
			}
		}
		$SaveFile = $SavePath . $SaveFilename;
		$writeRes = file_put_contents ( $SaveFile, $SaveData );
		// ---------- 数据表操作 ----------
		if ($pmwID) {
			$PMW = new ProductWebglModel ();
			$pmwSelectRes = $PMW->find ( $pmwID );
			if ($pmwSelectRes) {
				// ---------- 操作PRODUCT_MODEL_WEBGL表设置STAT=2 ----------
				$PM = new ModelsModel ();
				$pmSelectRes = $PM->find ( $pmwSelectRes [$PMW->F->PID] );
				if ($pmSelectRes) {
					$pmwSelectRes [$PMW->F->STAT] = 2;
					$pmwSelectRes [$PMW->F->LASTUPDATEFILE] = $SaveFile;
					$pmwSelectRes [$PMW->F->LASTUPDATE] = get_now ();
					$pmwSelectRes [$PMW->F->LDTIME] = time ();
					$returnRes = $pmwSaveRes = $PMW->save ( $pmwSelectRes );
				}
			}
		}
		return $pmwSaveRes;
	}
	
	/**
	 * 内部截图文件处理
	 *
	 * @author miaomin
	 * @param int $PID        	
	 * @param string $captureBase64        	
	 * @param string $imgsuffix        	
	 * @return mixed
	 */
	private function processCapture($PID, $captureBase64, $imgsuffix = 'png') {
		// ---------- 截图文件数据还原 ----------
		// ---------- 重要 ----------
		// ---------- BASE64编码在HTTP传输过程中会自动将加号替换成空格必须再进行反处理 ----------
		$imgData = base64_decode ( str_replace ( ' ', '+', str_replace ( 'data:image/png;base64,', '', $captureBase64 ) ) );
		$MD5File16Name = substr ( md5 ( $imgData ), 8, 16 );
		$imgDotSuffix = '.' . $imgsuffix;
		// ---------- 截图文件保存路径设定 ----------
		$SavePath = C ( 'UPLOAD_PAHT.PRODUCT_PHOTO' );
		$SubDir = getSavePathByID ( $PID );
		$target_path = $SavePath . $SubDir;
		$original_path = $target_path . 'o/';
		$thumbPath = $target_path . 's/';
		if (! file_exists ( $original_path )) {
			mkdir ( $original_path, 0777, true );
		}
		// ---------- 截图文件保存 ----------
		$output_file = $original_path . $MD5File16Name . $imgDotSuffix;
		$writeRes = file_put_contents ( $output_file, $imgData );
		/**
		 * $ifp = fopen ( $output_file, "wb" );
		 * $writeRes = fwrite ( $ifp,$imgData );
		 * fclose ( $ifp );
		 */
		// ---------- 截图文件生成缩略图 ----------
		if ($writeRes) {
			import ( 'ORG.Net.ImageCheck' );
			$imagea = new ImageCheck ();
			$imageSizeArr = getimagesize ( $output_file );
			if (($imageSizeArr [0] < C ( 'WEBGL.CAPTURE_NORMAL_WIDTH' )) || ($imageSizeArr [1] < C ( 'WEBGL.CAPTURE_NORMAL_HEIGHT' ))) {
				$imagea->imagezoom ( $output_file, $output_file, C ( 'WEBGL.CAPTURE_NORMAL_WIDTH' ), C ( 'WEBGL.CAPTURE_NORMAL_HEIGHT' ), "#FFFFFF" );
			}
			// ---------- 生成缩略图保存路径 ----------
			if (! is_dir ( $thumbPath )) {
				// ---------- 检查目录是否编码后的 ----------
				if (is_dir ( base64_decode ( $thumbPath ) )) {
					$thumbPath = base64_decode ( $thumbPath );
				} else {
					// ---------- 尝试创建目录 ----------
					if (! mkdir ( $thumbPath, 0777, true )) {
						return false;
					}
				}
			} else {
				if (! is_writeable ( $thumbPath )) {
					return false;
				}
			}
			// ---------- 生成图像缩略图 ----------
			$thumbWidth = C ( 'WEBGL.CAPTURE_THUMB_WIDTH' );
			import ( 'ORG.Util.Image' );
			for($i = 0, $len = count ( $thumbWidth ); $i < $len; $i ++) {
				$prefix = $thumbWidth [$i] . '_' . $thumbWidth [$i] . '_';
				$thumbname = $prefix . $MD5File16Name . $imgDotSuffix;
				Image::thumb2 ( $output_file, $thumbPath . $thumbname, '', $thumbWidth [$i], $thumbWidth [$i], true );
			}
			// ---------- 数据表操作 ----------
			$PPM = new ProductPhotoModel ();
			$PPM->{$PPM->F->OriginalName} = 'Capture' . $imgDotSuffix;
			$PPM->{$PPM->F->FileName} = $MD5File16Name . $imgDotSuffix;
			$PPM->{$PPM->F->Path} = preg_replace ( '|/o/|', '/', preg_replace ( '|^./|', '/', $original_path, 1 ) );
			$PPM->{$PPM->F->CreateDate} = get_now ();
			$PPM->{$PPM->F->Title} = 'Capture' . $imgDotSuffix;
			$PPM->{$PPM->F->Remark} = 'Capture' . $imgDotSuffix;
			$PPM->{$PPM->F->ProductID} = $PID;
			$res [] = $PPM->add ();
		}
		return $output_file;
	}
	
	/**
	 * 内部贴图文件处理
	 *
	 * @author miaomin
	 * @param int $PID        	
	 * @param string $textureBase64        	
	 * @param string $texturePath        	
	 * @param string $imgsuffix
	 *        	只接受'png'或者'jpeg'
	 * @return mixed
	 */
	private function processTexture($PID, $textureBase64, $texturePath, $imgsuffix = 'png') {
		// ---------- 贴图文件数据还原 ----------
		// ---------- 重要 ----------
		// ---------- BASE64编码在HTTP传输过程中会自动将加号替换成空格必须再进行反处理 ----------
		$imgData = base64_decode ( str_replace ( ' ', '+', str_replace ( 'data:image/' . $imgsuffix . ';base64,', '', $textureBase64 ) ) );
		$MD5File16Name = substr ( md5 ( $imgData ), 8, 16 );
		$imgDotSuffix = '.' . $imgsuffix;
		// ---------- 贴图文件保存路径设定 ----------
		$SavePath = $texturePath;
		if (! file_exists ( $SavePath )) {
			mkdir ( $SavePath, 0777, true );
		}
		// ---------- 贴图文件保存 ----------
		$output_file = $SavePath . $MD5File16Name . $imgDotSuffix;
		$writeRes = file_put_contents ( $output_file, $imgData );
		
		return $output_file;
	}
	
	/**
	 * 保存WEBGL前端的数据修改
	 *
	 * @author miaomin
	 * @return mixed
	 */
	public function savewebgl() {
		// ---------- 返回结果 ----------
		$res = array ();
		// ---------- 处理提交的参数 ----------
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		// ---------- 参数设定 ----------
		$pmwid = $args ['pmwid'];
		$webglpath = $args ['webglpath'];
		$webglfilename = $args ['webglfilename'];
		$webgldata = $args ['webgldata'];
		// ---------- 修改后文件处理 ----------
		if (($webgldata) && ($webglpath)) {
			$webglArr = array (
					'webglpath' => $webglpath,
					'webglfilename' => $webglfilename,
					'webgldata' => $webgldata 
			);
			$processRes = $this->processWebgl ( $pmwid, $webglArr );
		}
		// ---------- 日志处理 ----------
		$result = 'Date: ' . get_now () . ' | PMWID: ' . $pmwid . ' || WEBGLPATH: ' . $webglpath. ' || FILENAME: ' . $webglfilename . ' || DATA: ' . $webgldata . ' || Output: ' . $processRes . '|| End!';
		$logContent = $result . "\r\n\r\n";
		$fp = fopen ( 'saveWebgl.txt', 'a' );
		fwrite ( $fp, $logContent );
		fclose ( $fp );
		// ---------- 返回结果处理 ----------
		$res [] = $processRes;
		return $res;
	}
	
	/**
	 * 保存WEBGL前端的截图
	 *
	 * @author miaomin
	 * @return mixed
	 */
	public function savecapture() {
	
		// ---------- 返回结果 ----------
		$res = array ();
		// ---------- 处理提交的参数 ----------
		
		$args = func_get_args ();
		
		$args = $this->decodeArguments ( $args );
		// ---------- 参数设定 ----------
		$pid = $args ['pid'];
		$captureBase64 = $args ['capturedata'];
		
		
		// ---------- 截图文件处理 ----------
		if ($captureBase64) {
			$processRes = $this->processCapture ( $pid, $captureBase64 );
		}
		
//		echo $captureBase64;
		
		// ---------- 日志处理 ----------
		$result = 'Date: ' . get_now () . ' | PID: ' . $pid . ' || Output: ' . $processRes . '|| End!';
		$logContent = $result . "\r\n\r\n";
		$fp = fopen ( 'saveCaptureLog.txt', 'a' );
		fwrite ( $fp, $logContent );
		fclose ( $fp );
		// ---------- 返回结果处理 ----------
		$res [] = $processRes;
		return $res;
	}
	
	/**
	 * Returns the url query as associative array
	 *
	 * @param    string    query
	 * @return    array    params
	 */
	private function convertUrlQuery($query)
	{
		$queryParts = explode('&', $query);
		 
		$params = array();
		foreach ($queryParts as $param)
		{
			$item = explode('=', $param);
			$params[$item[0]] = $item[1];
		}
		 
		return $params;
	}
	
	private function getUrlQuery($array_query)
	{
		$tmp = array();
		foreach($array_query as $k=>$param)
		{
			$tmp[] = $k.'='.$param;
		}
		$params = implode('&',$tmp);
		return $params;
	}
	
	
	/**
	 * 保存WEBGL的模型
	 *
	 * @author zhangzhibin
	 * @return mixed
	 */
	public function savemodel() {
		// ---------- 返回结果 ----------
		$res = array ();
		// ---------- 处理提交的参数 ----------
			
	
		$args = func_get_args ();
		//var_dump($args);
		$args = $this->decodeArguments ( $args );
		// ---------- 参数设定 ----------
		$pid = $args ['pid'];
		$idArr['pid']=$pid;
		$modelBase64 = $args ['modeldata'];
		// ---------- 模型文件处理 ----------
		if ($modelBase64) {
			$processRes = $this->processModel ( $idArr,$modelBase64 );
			if($processRes){
				$output=$processRes['pf_path'].$processRes['pf_filename'];
			}else{
				$output='数据库未更新,记录已存在！';
			}		
		}	
		// ---------- 日志处理 ----------
		$result = 'Date: ' . get_now () . ' | PID: ' . $pid . ' || Output: ' . $output . '|| End!';
		$logContent = $result . "\r\n\r\n";
		$fp = fopen ( '.\logs\diy\saveModelLog.txt', 'a' );
		fwrite ( $fp, $logContent );
		fclose ( $fp );
		// ---------- 返回结果处理 ----------
		$res [] = $processRes;
		return $res;
	}
	
	
	/**
	 * DIYWEBGL文件保存处理
	 *
	 * @author zhangzhibin
	 * @param array $idarr
	 * @param string $modelBase64 模型数据
	 * @param string $modelsuffix 保存文件的后缀名，默认'stl'
	 * @return mixed
	 */
	private function processModel($idArr, $modelData, $modelsuffix = 'stl') {
		$PID=$idArr['pid'];
		//$UID=$idArr['uid'];
		$MD5File16Name = substr ( md5 ( $modelData ), 8, 16 )."_".$PID; //文件名
		$modelDotSuffix = '.' . $modelsuffix; //后缀名
		// ---------- 模型文件保存路径设定 ----------
		$SavePath = C ( 'UPLOAD_PAHT.PRODUCT' );
		$SubDir = getSavePathByID ( $PID );
		$target_path = $SavePath . $SubDir;
		if (!file_exists ( $target_path )) {
			mkdir ( $target_path, 0777, true );
		}
		$output_file = $target_path . $MD5File16Name . $modelDotSuffix;
		$writeRes = file_put_contents ( $output_file, $modelData );
		$output_file_size=filesize($output_file);//文件大小
		/**
		 * $ifp = fopen ( $output_file, "wb" );
		 * $writeRes = fwrite ( $ifp,$imgData );
		 * fclose ( $ifp );
		*/
		if ($writeRes) {
			
			// ---------- 数据表操作  保存模型文件ID----------
			$PFM = new ProductFileModel();
			$pf_filename=$MD5File16Name.$modelDotSuffix;	//文件名
			//$existfile=$PFM->getFileByFilename($pf_filename);
			$existfile=$PFM->getFileByProduct($PID);
			$PFM->{$PFM->F->FileName} 		= $pf_filename;
			$PFM->{$PFM->F->OriginalName}	= $pf_filename;
			$PFM->{$PFM->F->FileSize}		= $output_file_size;
			$PFM->{$PFM->F->FileSize_disp} 	= $output_file_size;
			$PFM->{$PFM->F->ProductID} 		= $PID;
			$PFM->{$PFM->F->Uploader} 		= $UID;
			$PFM->{$PFM->F->CreateDate} 	= get_now ();
			$PFM->{$PFM->F->CreateTime} 	= time ();
			$PFM->{$PFM->F->LastUpdate} 	= get_now ();
			$PFM->{$PFM->F->LastUpdateTime} = time ();
			$PFM->{$PFM->F->Path}			= preg_replace ( '|^./|', '/', $target_path, 1 );
			$PFM->{$PFM->F->Ext}			= $modelsuffix;
			if(!$existfile){
				$res = $PFM->add();
			}else{
				$res = $PFM->where('p_id='.$PID)->save();
			}
									
			if($res){
				$result['pf_path']="模型成功生成,保存路径:".$res['pf_path'].$res['pf_filename'];
			}else{
				$result['pf_path']="模型已生成,保存路径:".$output_file;
			}
		}
		return $result;
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
	
	/**
	 * 保存WEBGL前端的贴图
	 *
	 * @author miaomin
	 * @return mixed
	 */
	public function savetexture() {
		
		// ---------- 返回结果 ----------
		$res = array ();
		// ---------- 处理提交的参数 ----------
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		// ---------- 参数设定 ----------
		$pid = $args ['pid'];
		$textureBase64 = $args ['texturedata'];
		$texturePath = $args ['texturepath'];
		$textureType = $args ['texturetype'];
		// ---------- 只接受PNG或者JPEG ----------
		if (! in_array ( $textureType, C ( 'WEBGL.TEXTURE_ALLOW_TYPE' ) )) {
			$processRes = 0;
		}else{
			// ---------- 贴图文件处理 ----------
			if ($textureBase64 && $texturePath && $textureType) {
				$processRes = $this->processTexture ( $pid, $textureBase64, $texturePath, $textureType );
			} else {
				$processRess = 0;
			}
		}
		// ---------- 日志处理 ----------
		$result = 'Date: ' . get_now () . ' | PID: ' . $pid . ' || Output: ' . $processRes . '|| End!';
		$logContent = $result . "\r\n\r\n";
		$fp = fopen ( 'saveTextureLog.txt', 'a' );
		fwrite ( $fp, $logContent );
		fclose ( $fp );
		// ---------- 返回结果处理 ----------
		$res [] = $processRes;
		return $res;
	}
	
	/**
	 * ---------- PYTHON WEBGL转换结束后的调取接口 ----------
	 *
	 * @author miaomin
	 * @return mixed
	 */
	public function pycall() {
		// ---------- 返回结果 ----------
		$res = array ();
		// ---------- 处理提交的参数 ----------
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		// ---------- 参数设定 ----------
		$pyArgsArr = json_decode ( $args ['pyargs'], true );
		// ---------- 日志处理 ----------
		$result = 'JobId:: ' . $pyArgsArr ['jobid'] . ' | ID#' . $pyArgsArr ['id'] . ' convert wegbl end. | Status: ' . ( string ) $pyArgsArr ['status'] . '  | Result File: ' . $pyArgsArr ['webfile'] . ' | Time: ' . get_now () . ' From ' . get_client_ip ();
		$res [] = $result;
		$logContent = $result . "\r\n\r\n";
		$fp = fopen ( 'pycallLog.txt', 'a' );
		fwrite ( $fp, $logContent );
		fclose ( $fp );
		// ---------- 任务表处理 ----------
		// ---------- 考虑到同步问题全部改为INSERT而不用UPDATE ----------
		if ($pyArgsArr ['jobid']) {
			// ---------- 反馈1表示转换成功STAT=2否则STAT=3表示失败 ----------
			$pyResult = $pyArgsArr ['status'] == 1 ? 2 : 3;
			$JQ = new JobQueueModel ();
			$jobData = array (
					$JQ->F->JOBCODE => $pyArgsArr ['jobid'],
					$JQ->F->STAT => $pyResult,
					$JQ->F->TYPE => 1 
			);
			$jqAddRes = $JQ->addJob ( $jobData );
		}
		// ---------- WEBGL表处理 ----------
		if ($pyArgsArr ['pmwid']) {
			$PMW = new ProductWebglModel ();
			$pmwSelectRes = $PMW->find ( $pyArgsArr ['pmwid'] );
			if ($pmwSelectRes) {
				// ---------- 如果转换成功操作PRODUCT_MODEL表设置IS_AR=1 ----------
				$PM = new ModelsModel ();
				$pmSelectRes = $PM->find ( $pmwSelectRes [$PMW->F->PID] );
				if (($pmSelectRes) && ($pyResult == 2)) {
					$pmSelectRes [$PM->F->IsAR] = 1;
					$pmSelectRes [$PM->F->WebPF] = $pmwSelectRes [$PMW->F->PFID];
					$PM->save ( $pmSelectRes );
				}
				// ---------- 如果转换成功操作PRODUCT_MODEL_WEBGL表设置STAT=1 ----------
				if ($pyResult == 2) {
					$pmwSelectRes [$PMW->F->STAT] = 1;
					$pmwSelectRes [$PMW->F->ORIGINALFILE] = $pyArgsArr ['webfile'];
					$pmwSelectRes [$PMW->F->LASTUPDATE] = get_now ();
					$pmwSelectRes [$PMW->F->LDTIME] = time ();
					$pmwSaveRes = $PMW->save ( $pmwSelectRes );
				}
			}
		}
		return $res;
	}
	
	/**
	 * ---------- fbx格式转wegbl格式 API ----------
	 * ---------- 这个接口等待Webgl队列的调用 ----------
	 *
	 * @author miaomin
	 * @return mixed
	 */
	public function fbx2webgl() {
		// ---------- 返回结果 ----------
		$res = array ();
		// ---------- 处理提交的参数 ----------
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		// ---------- 解析用户信息(暂时关闭) ----------
		//@formatter:off
		/*
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
		*/
		// @formatter:on
		// ---------- 参数设定 ----------
		$pid = $args ['pid'];
		$fbxPath = $args ['fbxpath'];
		$jobid = $args ['jobid'];
		$pfid = $args ['pfid'];
		$pmwid = $args ['pmwid'];
		// ---------- 调用PYTHON服务 ----------
		if ($pid && $fbxPath && $pfid && $pmwid) {
			// ---------- Call Python Service ----------
			// ---------- Call Python Service ----------
			// ---------- Call Python Service ----------
			
			// ---------- 收到Python方面的响应 ----------
			// ---------- 做日志处理和后续的库表处理 ----------
			// ---------- 任务表处理 ----------
			// ---------- 考虑到同步问题全部改为INSERT而不用UPDATE ----------
			if ($jobid) {
				$JQ = new JobQueueModel ();
				$jobData = array (
						$JQ->F->JOBCODE => $jobid,
						$JQ->F->REID => $pmwid,
						$JQ->F->STAT => 1,
						$JQ->F->TYPE => 1 
				);
				$jqAddRes = $JQ->addJob ( $jobData );
			}
			// ---------- 这个接口调试成功后可以直接放到队列的JOB中去避免多一次通讯 ----------
			// ---------- 提交的文件路径要记得是正斜线 ----------
			import ( 'Common.Nsocket', APP_PATH, '.php' );
			$pyArgs = array ();
			$pyArgs ['id'] = $pid;
			$pyArgs ['path'] = $fbxPath;
			$pyArgs ['jobid'] = $jobid;
			$pyArgs ['pfid'] = $pfid;
			$pyArgs ['pmwid'] = $pmwid;
			$jsonArgs = json_encode ( $pyArgs );
			// ---------- PYTHON通讯 ----------
			$NS = new Nsocket ( C ( 'PY_WEB3D_SERVER' ), C ( 'PY_WEB3D_PORT' ) );
			$socketRes = $NS->send ( $jsonArgs, 1 );
			if (! $socketRes) {
				$res [] = $NS->getErrorMessage ();
			} else {
				$res [] = $socketRes;
			}
			// ---------- 日志处理 ----------
			$logTxt = 'JobId:: ' . $jobid . ' | ID#' . $pid . ' start wegbl encoding... | Result: ' . $socketRes . ' | Time: ' . get_now () . ' From ' . get_client_ip ();
			$logContent = $logTxt . "\r\n\r\n";
			$fp = fopen ( 'webglLog.txt', 'a' );
			fwrite ( $fp, $logContent );
			fclose ( $fp );
		} else {
			throw new Exception ( $this->RES_CODE_TYPE ['PARAMETER_METHOD_ERR'] );
		}
		
		return $res;
	}
	
	
	/*
	 * 获取diy信息
	 */
	public function diyinfo(){
		// ---------- 处理提交的参数 ----------
		$args = func_get_args ();
		$args = $this->decodeArguments ( $args );
		$res = array ();
		$up_id=$args['up_id'];
		$p_id=$args['p_id'];
		
		$udinfo=$this->getUdinfoByUpid($up_id,$p_id);
		$cid=I('cid',0,'intval');//DIY种类(tdf_diy_cate中的cid)
		if(!$cid){$cid=$udinfo['p_cate_4']; }//如果没有得到cid的参数，需要从tdf_product表中的 p_cate_4中获得
		
		if(!$cid){$this->error("参数错误",'diy-jewelrylist');}
		$DC=M('diy_cate')->where("cid=".$cid)->find();//diy产品类型
		$DU=M('diy_unit')->where('cid='.$cid)->order('fieldgroup,sort')->select();//选择tdf_diy_unit
		
		$sql="select TPM.pma_id,TPM.pma_name as TPM_name,TPMP.pma_name as TPMP_name,TPM.pma_unitprice,TPM.pma_density,TPM.pma_startprice,TPM.pma_diy_formula_s,TPM.pma_diy_formula_b from tdf_printer_material as TPM ";
		$sql.="Left Join tdf_printer_material as TPMP ON TPMP.pma_id=TPM.pma_parentid ";
		$sql.="where TPM.pma_type=1 order by TPM.pma_weight ASC ";
		$mcate=M("printer_material")->query($sql);//打印材料数组，必须
		$dataArr['diy_cate']=$DC;
		
		foreach($DU as $key =>$value){
			if($key!==count($DU)){
				$DUArray[$key]=$value;
				$DUArray[$key]['next_fieldgroup']=$DU[$key+1]['fieldgroup'];//拼接处理next_fieldgroup
			}
			$DUArray[$key]['fieldvalue']=$udinfo[$value['unit_name']];
			if($value['fieldtype']=="SELECT"){//如果是SELECT,构造selectarr
				$DUArray[$key]['unit_value_arr']=$this->getUnitValueArr($value['unit_value']);
			}
		}
		//var_dump($DUArray);
		$dataArr['diy_unit']=$DUArray;
		$dataArr['material']=$mcate;
		
		
		$result['pid']		=$p_id;
		$result['mcate']	=$mcate;
		$result['udinfo']	=$udinfo;
		$result['dataArr']	=$dataArr;
		
		return $result;
	}
	
	
	
	private function getUdinfoByUpid($upid,$pid){
		$UPD=M("user_prepaid_detail")->field("up_product_info")->where("up_id=".$upid)->find();
		$upd_arr=unserialize($UPD['up_product_info']);
		//var_dump($upd_arr);
		foreach($upd_arr as $key => $value){
			if($value['p_id']==$pid){
				$productArr=$value;/*获得product的详细信息数组*/
			}
		}
		$udinfo=$this->get_udinfo($productArr);
		return $udinfo;
	}
	private function get_udinfo($udinfo){//根据udinfo数组来返回整个表单数据
		$diy_unit_info=unserialize($udinfo['diy_unit_info']);
		$UD=M("diy_unit")->where("cid=".$udinfo['p_cate_4']." and ishidden=0")->order("sort")->select();
		foreach($UD as $key =>$value){
			//echo "ID:".$value['id'];
			$udinfo[$value['unit_name']]=$diy_unit_info[$value['id']];
		}
		return $udinfo;
	}
	
	private function getUnitValueArr($unit_value){//根据传入的$unit_value值返回select的值数组
		$sizeArr=explode(";",$unit_value);//尺寸大小默认数组
		foreach($sizeArr as $rkey =>$rvalue){
			$tempsize=explode("=",$rvalue);
			$size[$rkey]['key']=$tempsize[0];
			$size[$rkey]['value']=$tempsize[1];
		}
		return $size;
	}
	
}
?>