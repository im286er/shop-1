<?php
/**
 * 用户设置webgl
 * @author zhangzhibin 
 * 2014 04.02
 */
class WebglAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct ();
		
		// load ( "@.DBF" );
		$this->DBF = new DBF ();
		
		// 导入队列类
		import ( 'Common.Nqueue', APP_PATH, '.php' );
		// import ( '@.Model.Pqueue.SendmailPqueue' );
		import ( '@.Model.Pqueue.WebglPqueue' );
	}
	public function index() {
		try {
			$pid = I ( "pid", 0, 'intval' );
			if (! $this->_isLogin ()) {
				$this->_needLogin ();
			} // 判断登录
			if ($pid) {
				// --------------
				$TP = new ProductModel ();
				$productinfo = $TP->getProductInfoByID ( $pid );
				if ($productinfo ['p_creater'] !== $this->_session ( 'f_userid' )) {
					$this->error ( "抱歉，没有权限操作！", __APP__ );
				}
				// var_dump ( $productinfo );
				// --------------
				
				// miaomin add
				if (count ( $productinfo ['filelist'] ) > 0) {
					foreach ( $productinfo ['filelist'] as $key => $val ) {
						// 这里需要做一个开关
						// 切换LAO3D和自己的WEB3D
						// 如果是WEB3D的我们明确规定的格式才能转换FBX/OBJ/STL
						// 如果是LAO3D的都可以转
						if (ProductWebglModel::isAllowWebglConvert ( $val )) {
							$productinfo ['filelist'] [$key] ['pct_isconvert'] = 1;
						} else {
							$productinfo ['filelist'] [$key] ['pct_isconvert'] = 0;
						}
					}
				}
				
				$Users = new UsersModel ();
				$Users->find ( $this->_session ( 'f_userid' ) );
				$UP = $Users->getUserProfile ();
				$UA = $Users->getUserAcc ();
				
				$this->assign ( 'PID', $pid ? $pid : 0 );
				$this->assign ( 'userBasic', $Users->data () );
				$this->assign ( 'userPro', $UP->data () );
				$this->assign ( 'productinfo', $productinfo );
				//
				$this->assign ( 'WEBED_ENABLED', C ( 'WEB3D_ENABLED' ) );
				
				$this->_renderPage ();
			}
		} catch ( Exception $e ) {
			$this->error ( $e->getMessage (), '__APP__/index' );
		}
	}
	
	// ---------------------------------------------上传到lao3d，upload_webgl演示
	public function upload_webgl() {
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
		/**
		 * MIAOMIN ADDED@2014.7.29
		 *
		 * --- 3DCITY的WEBGL开始 ---
		 */
		if (C ( 'WEB3D_ENABLED' )) {
			// ---------- 清理数据测试用 ----------
			/**
			 * $M = new Model ();
			 * $M->execute ( 'DELETE FROM tdf_job_queue' );
			 * $M->execute ( 'DELETE FROM tdf_product_webgl' );
			 */
			
			// WEB3D
			// ---------- 获取模型文件ID并做验证 ----------
			// ---------- TP3.1.3后开始支持I方法 ----------
			$fid = I ( "post.filechoose", 0, "intval" );
			$PMF = new ProductFileModel ();
			if (! $PMF->isUploader ( $fid, $this->_session ( 'f_userid' ) )) {
				$this->error ( "抱歉，没有权限操作！", __APP__ );
			}
			$Fileinfo = $PMF->getFileByFileID ( $fid );
			if (! $Fileinfo) {
				$this->error ( '获取文件信息不完整', __APP__ );
			}
			// ---------- 获取PID及相关信息 ----------
			$pid = $Fileinfo [0] ['p_id'];
			$pf_path = getDropDotPath ( $Fileinfo [0] ['pf_path'] );
			$filepath = C ( 'WEB3D_ABSPATH_PRE' ) . $pf_path . $Fileinfo [0] ['pf_filename'];
			// MIAOMIN ADDED@2014.7.10
			// ---------- 采集转换Webgl必要的参数 ----------
			$args = array (
					'time' => time (),
					'pid' => $pid,
					'pfid' => $Fileinfo [0] ['pf_id'],
					'pmwid' => 0,
					'fbxpath' => $filepath 
			);
			/**
			 * ---------- 重要 ----------
			 *
			 * ---------- 在将任务加入队列之前需要先把数据保存入库 ----------
			 * ---------- 必须先入库再提交任务否则会有数据不同步的问题 ----------
			 */
			$PWGL = new ProductWebglModel ();
			$pwglCondition = array (
					$PWGL->F->PID => $pid,
					$PWGL->F->PFID => $Fileinfo [0] ['pf_id'],
					$PWGL->F->STAT => array (
							'neq',
							0 
					) 
			);
			$pwglSelectRes = $PWGL->where ( $pwglCondition )->find ();
			// ---------- 如果该文件已经转过WEBGL则不用再处理直接跳转 ----------
			if (! $pwglSelectRes) {
				// ---------- 如果该文件没有转过或者尚未转成功则开始转换 ----------
				$pwglCondition [$PWGL->F->STAT] = 0;
				$pwglSelectRes = $PWGL->where ( $pwglCondition )->find ();
				if ($pwglSelectRes) {
					$PWGL->delete ( $pwglCondition );
				}
				$pwglData = array (
						$PWGL->F->PID => $pid,
						$PWGL->F->PFID => $Fileinfo [0] ['pf_id'],
						$PWGL->F->CREATEDATE => get_now (),
						$PWGL->F->CDTIME => time (),
						$PWGL->F->LASTUPDATE => get_now (),
						$PWGL->F->LDTIME => time (),
						$PWGL->F->STAT => 0,
						$PWGL->F->UID => $this->_session ( 'f_userid' ),
						$PWGL->F->FROM => 1 
				);
				$pwglAddRes = $PWGL->add ( $pwglData );
				if ($pwglAddRes) {
					$args ['pmwid'] = $pwglAddRes;
					// ---------- 将任务加入队列 ----------
					$nq = new Nqueue ();
					$nqAddRes = $nq->addQueue ( new WebglPqueue (), $args );
					// ---------- 接收队列系统的返回值,正常情況下应该是一个32位的任务号 ----------
					if ($nqAddRes) {
						$JQ = new JobQueueModel ();
						$jobData = array (
								$JQ->F->JOBCODE => $nqAddRes,
								$JQ->F->REID => $pwglAddRes,
								$JQ->F->STAT => 0,
								$JQ->F->TYPE => 1 
						);
						$jqAddRes = $JQ->addJob ( $jobData );
						redirect ( __APP__ . "/webgl/index/pid/" . $pid );
					} else {
						// ---------- 添加队列失败 ----------
						$this->error ( '添加WEBGL队列失败', __APP__ );
					}
				} else {
					// echo $PWGL->getLastSql();
					// exit;
					// ---------- 添加WEBGL失败 ----------
					$this->error ( '添加WEBGL失败', __APP__ );
				}
			} else {
				// ---------- 不用再做转换但是需要将PRODUCT_MODEL中的当前WEBGL数据做更新 ----------
				$PMM = new ModelsModel ();
				$pmmSelectRes = $PMM->find ( $pid );
				if ($pmmSelectRes) {
					$pmmSelectRes [$PMM->F->IsAR] = 1;
					$pmmSelectRes [$PMM->F->WebPF] = $Fileinfo [0] ['pf_id'];
					$PMM->save ( $pmmSelectRes );
				}
				redirect ( __APP__ . "/webgl/index/pid/" . $pid );
			}
		} else {
			// LAO3D WEBGL
			$fid = I ( "post.filechoose", 0, "intval" );
			$PMF = new ProductFileModel ();
			$Fileinfo = $PMF->getFileByFileID ( $fid );
			if ($Fileinfo [0] ['pf_uploader'] !== $this->_session ( 'f_userid' )) {
				$this->error ( "抱歉，没有权限操作！", __APP__ );
			}
			$pid = $Fileinfo [0] ['p_id'];
			$PM = new ProductModel ();
			$ModelInfo = $PM->getProductByID ( $pid );
			$default_imgpath = BASE_PATH;
			$filepath = $default_imgpath . $Fileinfo [0] ['pf_path'] . $Fileinfo [0] ['pf_filename'];
			// $filepath="d:/website/city/upload/productfile/36/32/10724/b10beba1bab4707c.zip";
			$targetFile = iconv_code ( $filepath );
			$filepath = str_replace ( "/", "\\", $targetFile );
			Vendor ( 'Lao3d.Lao3dApiV1' );
			Vendor ( 'Lao3d.config' );
			$errret = '{"ret": 1, "msg": "上传失败"}';
			if ($targetFile) {
				$fileTypes = array (
						'zip',
						'rar' 
				); // File extensions
				$fileParts = pathinfo ( $_FILES ['obj'] ['name'] );
				if ($targetFile) {
					$modelName = $ModelInfo ['p_id'];
					$tags = 'Bitmap3D';
					$introduce = $ModelInfo ['p_intro'];
					$priviledge = BITMAP3D_ENTERPRISE_PRIVILEDGE;
					$apikey = BITMAP3D_ENTERPRISE_API;
					$libname = BITMAP3D_ENTERPRISE_LIBNAME;
					$sdk = new Lao3dApiV1 ( $apikey );
					$sdk->setServerName ( 'www.lao3d.com' );
					$params = array (
							'modelName' => $modelName,
							'libraryName' => $libname,
							'tags' => $tags,
							'priviledge' => $priviledge,
							'introduce' => $introduce 
					);
					
					$array_files = array (
							'obj' => ('@' . $targetFile),
							/*'icon' => '@c:\\095859.png',*/
					);
					
					$res = $sdk->apiUploadFile ( '/openApi/uploadModel', $params, $array_files );
					if (! is_array ( $res ) || ! isset ( $res ['ret'] )) {
						echo $errret;
					} else {
						// var_dump($res);
						// echo json_encode($res);
						// exit;
						$modelid = $res ['modelid'];
						if ($modelid) {
							$TPM = M ( "product_model" );
							$data ['pm_isar'] = 1;
							$data ['pm_arcode'] = $modelid;
							$TPM->where ( 'p_id=' . $pid . '' )->save ( $data );
							if ($TPM) {
								redirect ( __APP__ . "/webgl/index/pid/" . $pid );
								// $this->success("在线展示设置成功！",__APP__."/webgl/index/pid/".$pid);
							}
						}
					}
					// unlink($targetFile);
				} else {
					// echo $errret;
				}
			} else {
				echo $errret;
			}
		}
	}
	public function webedit() {
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		} // 判断登录
		$modelid = I ( 'post.modelid', 0, 'intval' );
		
		$this->assign ( 'modelid', $modelid );
		$this->_renderPage ();
	}
	public function webdel() {
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		} // 判断登录
		
		$productid = I ( 'post.productid', 0, 'intval' );
		$state = $this->webgl_del ( $productid );
		// miaomin added
		$PWEB = new ProductWebglModel ();
		$pwebCondition = array (
				$PWEB->F->PID => $productid 
		);
		$PWEB->where ( $pwebCondition )->delete ();
		$this->success ( "删除预览成功！", __APP__ . "/webgl/index/pid/" . $productid );
	}
	private function webgl_del($pid) {
		$TPM = new ModelsModel ();
		$TPM->where ( "p_id=" . $pid . "" )->setField ( "pm_isar", 0 );
	}
}