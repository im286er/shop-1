<?php

class ProductPhotoModel extends Model
{
	/**
	 * @var DBF
	 */
	protected $DBF;
	/**
	 * @var DBF_ProductPhoto
	 */
	public $F;

	public function __construct()
	{
		parent::__construct();
		$this->DBF = new DBF();
		$this->F = $this->DBF->ProductPhoto;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		$this->_map = $this->F->getMappedFields();
		parent::__construct();
		$PCT = D ( 'ProductCreateTool' );
		$this->ct_name_mapper = $PCT->getMapper ();
	}

	/**
   * 根据产品ID获取相片
   * @param int $PID 产品ID
   * @return array
   */
	public function getPhotosByPID($PID)
	{
		return $this->where($this->F->ProductID . "='" . $PID . "'")->
		order($this->F->DispWeight . ' DESC')->select();
	}
	
	public function getPhotosJsonByPID($PID)
	{
		$PhotoList = $this->getPhotosByPID($PID);
		if($PhotoList === false) { return false; }
		$JsonStr = '';
		foreach ($PhotoList as $Photo)
		{
			$PhotoID = $Photo[$this->F->ID];
			$PhotoPath = $Photo[$this->F->Path] . 'o/' . $Photo[$this->F->FileName];
			$Title = $Photo[$this->F->Title];
			$isCover = 'false';
			$JsonStr .= '{"PhotoID":' . $PhotoID . ',"PhotoPath":"' . $PhotoPath .
						 '","Title":"' . $Title . '"},';
		}
		if(strlen($JsonStr) > 0) { $JsonStr = substr($JsonStr, 0, strlen($JsonStr) - 1); }
		return '[' . $JsonStr . ']';
	}
	
	//old
	/**
   * 插入一条相片数据
   * @param string $OriginalName 原文件名
   * @param string $FileName 保存的文件名
   * @param string $Path 保存路径
   * @param int $PID 产品ID
   * @return false/插入数据ID
   */
	public function insertPhoto($OriginalName, $FileName, $Path, $PID)
	{
		// @formatter:off
		$PhotoData = array(
			$this->DBF->ProductPhoto->OriginalName => $OriginalName,
			$this->DBF->ProductPhoto->FileName => $FileName,
			$this->DBF->ProductPhoto->Path => $Path,
			$this->DBF->ProductPhoto->CreateDate => get_now(),
			$this->DBF->ProductPhoto->Title => substr($OriginalName,0 ,50),
			$this->DBF->ProductPhoto->ProductID => $PID);
		$this->startTrans();
		$Result = $this->add($PhotoData);
		$Result !== false ? $this->commit() : $this->rollback();
		return $Result;
		// @formatter:on
	}

	/**
   * 根据指定的ID更新一条相片数据
   * @param int $PhotoID 相片ID
   * @param string $Title 相片标题
   * @param string $Remark 相片备注
   * @param int $DispWeight 显示优先级
   * @param boolean $UseTrans 是否使用事务
   * @return false/受影响行数
   */
	public function updatePhoto($PhotoID, $Title, $Remark, $DispWeight, $UseTrans = true)
	{
		// @formatter:off
		$PhotoData = array(
			$this->DBF->ProductPhoto->Title => $Title, 
			$this->DBF->ProductPhoto->Remark => $Remark, 
			$this->DBF->ProductPhoto->DispWeight => $DispWeight);
		if($UseTrans) { $this->startTrans(); }
		$Result = $this->where($this->DBF->ProductPhoto->ID . "='" . $PhotoID . "'")->save($PhotoData);
		if($UseTrans) { $Result !== false ? $this->commit() : $this->rollback(); }
		return $Result;
		// @formatter:on
	}

	public function updatePhotos($PhotoList)
	{
		// @formatter:off
		$this->startTrans();
		$Result = 0;
		foreach($PhotoList as $Photo)
		{
			$TempResult = $this->updatePhoto(
					$Photo[$this->DBF->ProductPhoto->ID], 
					$Photo[$this->DBF->ProductPhoto->Title],
					$Photo[$this->DBF->ProductPhoto->Remark], 
					$Photo[$this->DBF->ProductPhoto->DispWeight], false);
			if($TempResult !== false) { $Result += $TempResult; }
			else { $Result = false; break; }
		}
		$Result !== false ? $this->commit() : $this->rollback();
		return $Result;
		// @formatter:on
	}

	/**
	 * 根据指定的ID删除一张相片
	 * @param int $PhotoID 相片ID
	 * @return false/受影响行数
	 */
	public function deletePhoto($PhotoID)
	{
		return $this->where($this->DBF->ProductPhoto->ID . "='" . $PhotoID . "'")->delete();
	}


	/**
	 * 二进制图片文件保存截图
	 */
	public function saveCaptureModel($pid,$xmlstr){
		// ---------- 返回结果 ----------
		$res = array ();
		// ---------- 截图文件处理 ----------
		if ($xmlstr) {
			$processRes = $this->processCaptureModel ( $pid, $xmlstr );
		}
		//header ( 'Content-type: application/json' );
		$res = $processRes;
		return $res;
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
	private function processCaptureModel($PID, $captureBase64, $imgsuffix = 'png') {
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
			/*$PPM = new ProductPhotoModel ();
			$PPM->{$PPM->F->OriginalName} = 'Capture' . $imgDotSuffix;
			$PPM->{$PPM->F->FileName} = $MD5File16Name . $imgDotSuffix;
			$PPM->{$PPM->F->Path} = preg_replace ( '|/o/|', '/', preg_replace ( '|^./|', '/', $original_path, 1 ) );
			$PPM->{$PPM->F->CreateDate} = get_now ();
			$PPM->{$PPM->F->Title} = 'Capture' . $imgDotSuffix;
			$PPM->{$PPM->F->Remark} = 'Capture' . $imgDotSuffix;
			$PPM->{$PPM->F->ProductID} = $PID;
			$res [] = $PPM->add ();*/

			//------------------更新tdf_product和tdf_user_diy中的图片路径----------start
			$imgPath    =getDropDotPath($output_file);

			$PM= new ProductModel();

			$productInfo    = $PM->getProductByID($PID);
			$userDiyId      = $productInfo['p_diy_id'];
			$dataUserDiy['cover']=$imgPath;
			$resultUserDiy  =M('user_diy')->where ( "id=" . $userDiyId )->setField ($dataUserDiy);
			//------------------更新tdf_product和tdf_user_diy中的图片路径----------end

			$UDM    = new UserDiyModel();
			$UCM    = new UserCartModel();
			/******保存DIY商品的显示详情**************start*/
			$userDiyInfo=$UDM->getUserDiyInfoById($userDiyId);
			$diy_unit_info=$userDiyInfo['diy_unit_info'];//加入diy的值信息到$Product中用于下面的getUserCartDiyByProduct方法
			$productInfo['diy_unit_info']=$userDiyInfo['diy_unit_info'];//加入diy的值信息到$Product中用于下面的getUserCartDiyByProduct方法
			if($productInfo['p_cate_4']==1){
				$dataProd['p_intro']="简笔画";
			}else{
				$dataProd['p_intro']=$UCM->getUserCartDiyByProduct($productInfo);
				$dataProd['p_mini']=$productInfo['diy_unit_info'];
			}
			/******保存DIY商品的显示详情**************end*/
			$dataProd['p_cover']    =$imgPath;
			$resultProd =M('product')->where ( "p_id=" . $PID )->setField ($dataProd);
		}
		return $output_file;
	}




}