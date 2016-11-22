<?php
/**
 * 下载类
 *
 * @author miaomin 
 * Jul 19, 2013 11:11:33 AM
 */
class DownloadAction extends CommonAction {
	
	/**
	 * 构造函数
	 */
	public function __construct() {
		
		//
		parent::__construct ();
		
		// 判断登录
		if (! $this->_isLogin ()) {
			
			//echo $_GET['id'];
			//var_dump( __SELF__);
			
			//exit;
			$this->_needLogin ();
		}
		
		// load ( "@.DBF" );
		$this->DBF = new DBF ();
	}
	
	/**
	 * 下载
	 */
	public function now() {
		try {
			if ($this->_get ()) {
				$fid = $this->_get ( 'fid' );
				$PF = D ( 'ProductFile' );
				// 无此PFID
				if (! $PF->find ( $fid )) {
					throw new Exception ( 'no_pfid' );
				}
				// 是否允许下载
				$UOP = D ( 'UserOwnProduct' );
				$condition ['u_id'] = $this->_session ( 'f_userid' );
				$condition ['p_id'] = $PF->p_id;
				if ($UOP->where ( $condition )->find ()) {
					// 允许下载
					// 开始下载
					$isLogDownload = true;
					$Log = session('log_download_' . $condition['p_id']);
					if(isset($Log)) {
						$LastTime = session('log_download_' . $condition['p_id']);
						if((time() - $LastTime) <= (10 * 60 * 60)) //十分钟内再次下载不记录次数
						{ $isLogDownload = false; }
					}
					session('log_download_' . $condition['p_id'], time()); //记录下载模型ID和时间

					if($isLogDownload){
						$LUDM = new LogUserDownloadModel();
						$LUDM->addLog($condition['p_id'], $condition['u_id']);
					}
					import ( "ORG.Net.Http" );
					
					$alioss=new AliossModel();	
					//------------------------------------------下载文件 start
					if(C('DOWNTYPE')){////读取默认下载模式设置 如果OSS优先
						$filename=$PF->pf_path.$PF->pf_filename;
						if($alioss->file_exist($filename)){ //如果oss端文件存在
							header("Location: " . $alioss->getFileUrl($filename));
						}else{
							if($PF->pf_filesize > 100000000){ //如果下载文件大于1G
								$downurl=WEBROOT_PATH.getfilepath($PF->pf_path) . $PF->pf_filename ;
								header("Location: " . $downurl);
							}else{
								http::download ( ".".getfilepath($PF->pf_path) . $PF->pf_filename );
							}
						}
					}else{
						if($PF->pf_filesize > 100000000){//如果下载文件大于1G
							$downurl=WEBROOT_PATH.getfilepath($PF->pf_path) . $PF->pf_filename ;
							header("Location: " . $downurl);
						}else{
							http::download ( ".".getfilepath($PF->pf_path) . $PF->pf_filename );
						}
					}
					//------------------------------------------下载文件 end
													
				} else {
					throw new Exception ( 'not_allow_down' );
				}
			}
		} catch ( Exception $e ) {
			$this->error ( $e->getMessage (), WEBROOT_PATH . '/index.php/models/detail/id/' . $PF->p_id );
		}
		// @formatter:off
		/* TP自带的下载范例其实利用X-SENDFILE效率更佳
		$file_dir = "./upload/productfile/"; 
		$file_name ="da9cfbb8e8b7a386.rar"; 
		import ( "ORG.Net.Http" ); 
		http::download ($file_dir . $file_name );
		*/
		// @formatter:on
	}
	
	/**
	 * 首页
	 */
	public function index() {

		try {
			
			if ($this->isGet ()) {
				
				$pid = $this->_get ( 'id' );
				$curract  = I('get.curract',0,'intval');
				
				$Product = D ( 'Product' );
				// 无此PID
				
				if (! $Product->find ( $pid )) {
					$this->error ( L ( 'no_pid' ), WEBROOT_PATH . '/index.php' );
				}
				
				// 如果没下载开始走下载流程积分扣起来...
				// 简要流程:
				// 1. 查user_own_product有记录则被允许直接输出下载
				// 2. 没有记录则需要查该物品以何种货币结算
				// 3. 如果以真实货币结算去购物车以积分结算则开始扣除积分
				// 4.
				// 购物车不多说了扣积分需要完成四表操作分别是user_account/user_own_product/user_downloads/product
				$UOP = D ( 'UserOwnProduct' );
				$condition ['u_id'] = $this->_session ( 'f_userid' );
				$condition ['p_id'] = $Product->p_id;
				if ($UOP->where ( $condition )->find ()) {
					// 直接允许下载
					// throw new Exception ( '... ...' );
					if ($this->_get ( 'reqtype' ) == 'ajax') {
						$result ['isSuccess'] = true;
						echo json_encode ( $result );
						exit ();
					} else {
						
						redirect ( WEBROOT_PATH . '/index.php/models-detail-id-' .$pid );
					}
				}
		
				$UA = D ( 'UserAccount' );
				$ua_arr = $UA->find ( $this->_session ( 'f_userid' ) );
				// 积分大于0
				//
				// 开始
				$ua_op_res = true;
				$UOP->startTrans ();
				if ($Product->p_vprice) {
					// 判断用户积分是否足以扣除
			
					if ($UA->u_vcoin_av < $Product->p_vprice) {
						
						//$this->dispatchJump('很抱歉，您的积分不足！<a href=>如何获取积分</a>');
						//$this->redirect(WEBROOT_PATH . '/index.php/models/detail/id/' . $pid,'',20,'很抱歉，您的积分不足！<a href=>如何获取积分</a>');
						$this->error ( '很抱歉，您的积分不足！     <br> <br> <a href=__DOC__/index/help-index-id-94.html target=_blank>如何获取积分</a>', 0);
						
						//throw new Exception ( 'no_enough_vcoin' );
						//rdirect ( WEBROOT_PATH . '/index.php/models/detail/id/' . $pid.'/curract/'.$curract );
					}
					
					// 表user_account
					$ua_op_res = $UA->changeVCoin ( $ua_arr, - $Product->p_vprice, - $Product->p_vprice );
					//表user_deals_vcoin
					$ua_op_userdeal_res=$UA->add_UserDealsVcoin($Product->p_id,$this->_session ( 'f_userid' ));
					//记录现在扣除积分日志
					$VL=new LogVTransModel();
					$vl_res=$VL->addLog($ua_arr, - $Product->p_vprice, 0, 4, $Product->p_id);
				}
				
				// 表user_own_product
				$uop_op_res = $UOP->addRecord ( $UA->u_id, $Product->p_id, $Product->p_creater, 3 );
				//dump($uop_op_res);
			
				// 表user_downloads
				$UDL = D ( 'UserDownloads' );
				$udl_op_res = $UDL->addRecord ( $UA, $Product );
				//dump($udl_op_res);
				// 表product
				// 下载次数+1
				$Product->p_downs += 1;
				$p_op_res = $Product->save ();
				// dump($p_op_res);
				// exit;
				if ($ua_op_res && $uop_op_res && $udl_op_res && $p_op_res) {
					$UOP->commit ();
				} else {
					$UOP->rollback ();
				}
				//echo "aaa";
				//exit;
				if ($this->_get ( 'reqtype' ) == 'ajax') {
					$result ['isSuccess'] = true;
					echo json_encode ( $result );
				} else {
					redirect ( WEBROOT_PATH . '/index/models-detail-id-' . $pid.'-curract-'.$curract );
				}
			}
	
			// @formatter:off
			/*
			$file_dir = "./upload/productfile/";
			$file_name = "da9cfbb8e8b7a386.rar";
			
			import ( "ORG.Net.Http" );
			http::download ( $file_dir . $file_name );
			*/
			/*
			header ( "Content-type:application/octete-stream" );
			header ( "Accept-Ranges:bytes" );
			header ( "Accept-Length:" . filesize ( $file_dir . $file_name ) );
			header ( "Content-Disposition:attachment;filename=" . $file_name );
			
			$file = fopen ( $file_dir . $file_name, "r" );
			$line = 10;
			$pos = - 2;
			$t = "";
			$data = "";
			// echo fread ( $file, filesize ( $file_dir . $file_name ) );
			// //这种方法要警惕
			while ( $line > 0 ) {
				while ( $t != "\n" ) {
					fseek ( $file, $pos, SEEK_END );
					$t = fgetc ( $file );
					$pos --;
				}
				$t = "";
				$data .= fgets ( $file );
				$line --;
			}
			fclose ( $file );
			echo $data;
			// exit ();
			*/
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
}