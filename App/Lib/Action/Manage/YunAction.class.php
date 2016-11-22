<?php
/**
 * 云端模块
 *
 * @author miaomin 
 * Sep 12, 2013 7:03:19 PM
 */
class YunAction extends CommonAction {
	
	/**
	 * 首页
	 */
	public function index() {
		$this->display ();
	}
	
	/**
	 * 移除云目录
	 */
	public function removefolder() {
		try {
			$folderId = intval ( $this->_get ( 'folderid' ) );
			if ($folderId) {
				$myinfo = $this->_session ( 'my_info' );
				
				$YF = new UserFoldersModel ();
				
				// 检查
				$YF->helper->verifyRemoveFolder ( $myinfo ['aid'], $folderId );
				
				$res = $YF->helper->removeFolder ( $myinfo ['aid'], $folderId );
				
				if ($res) {
					$this->redirect ( 'yun/listfolder/' );
				}
			}
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 添加云目录
	 */
	public function addfolder() {
		try {
			if ($this->isPost ()) {
				$myinfo = $this->_session ( 'my_info' );
				$folderName = $this->_post ( 'foldername' );
				
				$YF = new UserFoldersModel ();
				$YF->helper->verifyFolder ( $myinfo ['aid'], $folderName );
				$folderId = $YF->helper->createFolder ( $myinfo ['aid'], $folderName );
				
				// 计数器
				$UCAP = new UserCapModel ();
				$UCAP->changeCount ( $UCAP->F->FolderNum, $myinfo ['aid'], 1 );
			}
			$this->display ();
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 重命名云目录
	 */
	public function renamefolder() {
		try {
			if ($this->isPost ()) {
				$myinfo = $this->_session ( 'my_info' );
				$folderName = $this->_post ( 'foldername' );
				$folderId = intval ( $this->_post ( 'folderid' ) );
				
				$YF = new UserFoldersModel ();
				$YF->helper->verifyFolder ( $myinfo ['aid'], $folderName );
				$res = $YF->helper->renameFolder ( $myinfo ['aid'], $folderId, $folderName );
				
				if ($res) {
					$this->redirect ( 'yun/listfolder/' );
				}
			} else {
				$folderId = intval ( $this->_get ( 'folderid' ) );
				if ($folderId) {
					$YF = new UserFoldersModel ();
					
					$folderinfo = $YF->helper->getFolderInfo ( $folderId );
					if ($folderinfo) {
						$this->assign ( 'folderinfo', $folderinfo );
						$this->display ();
					}
				}
			}
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 列出云目录
	 */
	public function listfolder() {
		try {
			$myinfo = $this->_session ( 'my_info' );
			
			$YF = new UserFoldersModel ();
			$folderlist = $YF->helper->listFolder ( $myinfo ['aid'] );
			
			$this->assign ( 'folderlist', $folderlist );
			$this->display ();
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 初始化系统云目录
	 */
	public function initsys() {
		try {
			if ($this->isPost ()) {
				$uid = intval ( $this->_post ( 'userid' ) );
				
				$YF = new UserFoldersModel ();
				// 检查
				$YF->helper->verifyInitSysFolder ( $uid );
				// 初始化
				$YF->helper->initUserYunFolder ( $uid );
			}
			$this->display ();
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 上传云文件
	 */
	public function upload() {
		try {
			if ($this->isPost ()) {
				if ($_FILES ['yunfile']) {
					$md5filename = md5_file ( $_FILES ['yunfile'] [tmp_name] );
					
					// 保存文件
					import ( 'ORG.Net.UploadFile' );
					$upload = new UploadFile ();
					$upload->uploadReplace = true;
					// 单个文件最大容许10M
					$upload->maxSize = 10485760;
					$upload->allowExts = array (
							'stl',
							'zip' 
					);
					$upload->savePath = C ( 'YUN.FILE_SAVE_PATH' ) . getSavePathByID ( 1 ) . 'o/';
					$upload->saveRule = $md5filename . '';
					
					$FileInfo = $upload->uploadOne ( $_FILES ['yunfile'] );
					
					print_r ( $FileInfo );
				}
				exit ();
			}
			$this->display ();
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	
	/**
	 * 验证STL文件
	 */
	public function validstl() {
		$file = './Uploads/Yun/1/c4/1/o/974cf773675e34b1bcab26870f42545a.stl';
		$fs = filesize ( $file );
		var_dump ( $fs );
		$YF = new YunFilesModel ();
		$res = $YF->helper->validSTLFile ( $file );
		var_dump ( $res );
	}
	
	/**
	 * 搜索文件
	 */
	public function search() {
		@load ( '@.Paging' );
		@load ( "@.SearchParser" );
		if ($this->isPost ()) {
			$SP = new SearchParser ();
			if ($SP->parseSearchInfo ( true )) {
				$Url = $SP->getFormattedUrl ();
			}
			$this->redirect ( '/yun/search?' . substr ( $Url ['url'], 1 ) );
		} else {
			$SP = new SearchParser ();
			if ($SP->parseUrlInfo ( true )) {
				$myinfo = $this->_session ( 'my_info' );
				$SP->SearchInfo ['owner'] = $myinfo ['aid'];
				//
				// pr($SP->SearchInfo);
				$YSM = new YunSearchModel ( $SP->SearchInfo, 'model', true, false );
				$res = $YSM->getResult ( $SP->SearchInfo ['page'] );
				if ($YSM->TotalCount) {
					$this->assign ( 'filelist', $res );
					$this->assign ( 'totalcount', $YSM->TotalCount );
				}
			}
		}
		$this->display ();
	}
	
	/**
	 * TEST
	 */
	public function test() {
		echo 'test';
	}
	
	/**
	 * UNZIP
	 *
	 * @param unknown_type $file        	
	 */
	private function unzip($file) {
		$zip = zip_open ( $file );
		if (is_resource ( $zip )) {
			$tree = "";
			while ( ($zip_entry = zip_read ( $zip )) !== false ) {
				echo "Unpacking " . zip_entry_name ( $zip_entry ) . "\n";
				if (strpos ( zip_entry_name ( $zip_entry ), DIRECTORY_SEPARATOR ) !== false) {
					$last = strrpos ( zip_entry_name ( $zip_entry ), DIRECTORY_SEPARATOR );
					$dir = substr ( zip_entry_name ( $zip_entry ), 0, $last );
					$file = substr ( zip_entry_name ( $zip_entry ), strrpos ( zip_entry_name ( $zip_entry ), DIRECTORY_SEPARATOR ) + 1 );
					if (! is_dir ( $dir )) {
						@mkdir ( $dir, 0755, true ) or die ( "Unable to create $dir\n" );
					}
					if (strlen ( trim ( $file ) ) > 0) {
						$return = @file_put_contents ( $dir . "/" . $file, zip_entry_read ( $zip_entry, zip_entry_filesize ( $zip_entry ) ) );
						if ($return === false) {
							die ( "Unable to write file $dir/$file\n" );
						}
					}
				} else {
					file_put_contents ( $file, zip_entry_read ( $zip_entry, zip_entry_filesize ( $zip_entry ) ) );
				}
			}
		} else {
			echo "Unable to open zip file\n";
		}
	}
	
	/**
	 * 灌100万条记录
	 */
	public function bwdata() {
		$yf = new YunFilesModel ();
		
		for($j = 0; $j < 100; $j ++) {
			for($i = 0; $i < 1000; $i ++) {
				$hex = md5 ( microtime () );
				$sql .= "INSERT INTO tdf_yun_files_old (yf_md5_hex) VALUES ('$hex');";
			}
			
			$res = $yf->execute ( $sql );
			var_dump ( $res );
		}
	}
}
?>