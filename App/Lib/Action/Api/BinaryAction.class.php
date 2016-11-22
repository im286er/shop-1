<?php

class BinaryAction extends Action {
	public function __construct() {

		header('Access-Control-Allow-Origin: *');//跨域提交
		header('Access-Control-Allow-Headers: Content-Type');
		header('Access-Control-Allow-Methods: *');
	}

	
	public function index(){ //接收get数据和二进制的post数据

		$pid=I("get.pid",0,"intval");
		$method=I('get.method',0,'string');
		$idArr['pid']=$pid;
		$xmlstr =  $GLOBALS[ 'HTTP_RAW_POST_DATA' ];
		$xmlstr = file_get_contents('php://input');
		if(!$method){
			$xmlstrJsonArr=json_decode($xmlstr,true);
			$method=$xmlstrJsonArr['method'];
		}
		//var_dump($xmlstr);
		//exit;
		if($method=='savemodel'){
			if(intval($pid) < 25000){
				$PM=new ProductModel();
				$pid=$PM->getPidByUdid($pid);
			}
			$saveResult=$this->saveModel($pid, $xmlstr);
		}elseif($method=='savecapture') {
            //var_dump($xmlstr);
            //exit;
           // if ($xmlstr == 'undefined') {
            //    echo 0;
            //} else {
                $saveResult = $this->saveCapture($pid, $xmlstr);
                echo $saveResult;
           // }
        }elseif($method=='savecaptureNowebgl') {

			$saveResult = $this->saveCaptureNoWebGL($pid, $xmlstr);
			echo $saveResult;

		}elseif($method=='saveproject') {
			$saveResult = $this->saveProject($pid, $xmlstr);

		}elseif($method=='clientfile'){
			$cid=I("cid",0,"intval");
			$this->clientfile($cid);
		}else{
			echo "error";
		}
		// ---------- 日志处理 ----------
		$result = 'Date: ' . get_now () . ' | PID: ' . $pid . ' || Output: ' . $saveResult. '|| End!';
		$logpath=".\\logs\\diy\\".$method."a.txt";
		$this->logText($logpath,$result);
	}



        public function getRandOnlyId() {//得到随机唯一id
            //新时间截定义,基于世界未日2012-12-21的时间戳。
            $endtime=1356019200;//2012-12-21时间戳
            $curtime=time();//当前时间戳
            $newtime=$curtime-$endtime;//新时间戳
            $rand=rand(0,99);//两位随机
            $all=$rand.$newtime;
            $onlyid=base_convert($all,10,36);//把10进制转为36进制的唯一ID
            return $onlyid;
        }




	/*
	 * 记录日志文件
	 * zhangzhibin
	 * @param str $textpath 文件路径
	 * @param str $res 记录内容
	 */
	public function logText($textpath,$res){//记录日志文件
		$logContent = $res . "\r\n\r\n";
		$fp = fopen ( $textpath, 'a' );
		$result=fwrite ( $fp, $logContent );
		fclose ( $fp );
		return $result;
	}

	/**保存简笔画文件和图片
	 * @param int $pid
	 * @param int $xmlstr
	 * @return string
	 */
	public function saveProject($pid=0,$xmlstr=0){
		if ($xmlstr) {
			$projDataArr=json_decode($xmlstr);
			//var_dump($projDataArr);

			$UDM=new UserDiyModel();
			$result=$UDM->saveDrawDiyProduct($pid,$projDataArr);
			if($projDataArr->agentId){
				$res= "../index/index-showordercode-pkey-".$projDataArr->productKey;
			}else{
				$res= "../user.php/cart";
			}
		}else{
			$res="error！无数据。";
		}
		return $res;
	}

	/*//简笔画文件保存为json文件和画布截图
	public function saveProjecToJson($pid,$projDataArr){
		$UDM=new UserDiyModel();
		$result=$UDM->saveDrawDiyProduct($pid,$projDataArr);
		//$output_file_size=filesize($output_file);//文件大小
		return $result;
	}*/
	
	public function saveModel($pid,$xmlstr){
		$idArr['pid']=$pid;
       //var_dump($idArr['pid']);
		// ---------- 模型文件处理 ----------
		if ($xmlstr) {
			$processRes = $this->processModel ( $idArr,$xmlstr );
			$res=$processRes['logtext'];
		}else{
			$res="error！无数据。";
		}
		
		return $res;
	}
	
	public function saveCapture($pid,$xmlstr){
		// ---------- 返回结果 ----------
		$res = array ();
		// ---------- 截图文件处理 ----------
		if ($xmlstr) {
			$processRes = $this->processCapture ( $pid, $xmlstr );
		}
		header ( 'Content-type: application/json' );
		//echo $processRes;
		$res = $processRes;
		return $res;
		
	}

    public function saveCaptureNoWebGL($pid=0,$xmlstr=0){
        $imgs=explode(',',$xmlstr);
      /*$imgs = array(
            0 => 'http://localhost/city/static/images/earing/4.png',
            1 => 'http://localhost/city/static/images/earing/2.png',
            2 => 'http://localhost/city/static/images/earing/1.png',
            3 => 'http://localhost/city/static/images/earing/3.png'
        );*/

        $imgFilePath=$this->mergerImg($pid,$imgs);
        return $imgFilePath;
    }

    //合并图片,返回图片路径
    public function mergerImg($pid,$imgs) {
        list($max_width, $max_height) = getimagesize($imgs[0]);
        $dests = imagecreatetruecolor($max_width, $max_height);
       // $dests = imagecreate($max_width, $max_height);

        //-----------------白色填充--------------start-------
        $wite = ImageColorAllocate($dests,255,255,255);//填充的背景色你可以重新指定，我用的是白色
        imagefilledrectangle($dests, 0, 0, $max_width, $max_height, $wite);
        ImageColorTransparent($dests, $wite);
        //-----------------白色填充--------------end-------

        $dst_im = imagecreatefrompng($imgs[0]);

        imagecopy($dests,$dst_im,0,0,0,0,$max_width,$max_height);
        imagedestroy($dst_im);
        foreach($imgs as $key => $value){
            if($key !==0){
                $src_im = imagecreatefrompng($imgs[$key]);
                $src_info = getimagesize($imgs[$key]);
                imagecopy($dests,$src_im,0,0,0,0,$src_info[0],$src_info[1]);
                imagedestroy($src_im);
            }
        }
        //-----------------截图文件路径文件名 start------
        $SavePath = C ( 'UPLOAD_PAHT.PRODUCT_PHOTO' );
        $SubDir = getSavePathByID ( $PID );
        $target_path = $SavePath . $SubDir;
        $original_path = $target_path . 'o/';
        $original_path_s = $target_path . 's/';
        if (!file_exists ( $original_path )) {
            mkdir ( $original_path, 0777, true );
            mkdir ( $original_path_s, 0777, true );
        }
        $imgName =$this->getRandOnlyId();
        $imgDotSuffix = '.png';
        $imgPath=$original_path.$imgName.$imgDotSuffix;
        //---------------截图文件路径文件名 end----------
        header("Content-type: image/png");
        imagepng($dests,$imgPath);
        $prefix = '64_64_';
        $thumbname = $prefix . $imgName . $imgDotSuffix;
        import ( 'ORG.Util.Image' );
        if($thumbname){
               Image::thumb2 ( $imgPath, $original_path_s . $thumbname, '', 64, 64, true );
        }
        return $imgPath;
    }
	
	/*
	 * DIYWEBGL文件保存处理 
	 * @author zhangzhibin
	 * @param array $idarr
	 * @param string $modelBase64 模型数据
	 * @param string $modelsuffix 保存文件的后缀名，默认'stl'
	 * @return mixed
	 */
	private function processModel($idArr, $modelData, $modelsuffix = 'stl') {
		$PID=$idArr['pid'];
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
			$PFM->{$PFM->F->Uploader} 		= 25;
			$PFM->{$PFM->F->CreateDate} 	= get_now ();
			$PFM->{$PFM->F->CreateTime} 	= time ();
			$PFM->{$PFM->F->LastUpdate} 	= get_now ();
			$PFM->{$PFM->F->LastUpdateTime} = time ();
			$PFM->{$PFM->F->Path}			= preg_replace ( '|^./|', '/', $target_path, 1 );
			$PFM->{$PFM->F->Ext}			= $modelsuffix;
			if(!$existfile){
				$res[]= $PFM->add();
			}else{
				$res[] = $PFM->where('p_id='.$PID)->save();
			}
			if($res){
				$res['logtext']="模型成功生成,保存路径:".$output_file;
			}else{
				$res['logtext']="模型已生成,保存路径:".$output_file;
			}
		}
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
	 * 客户端请求更新文件
	 * @zhangzhibin 2016-08-04
	 * 返回conf_json和公式pma_formula
	 */
	public function clientfile($cid){
		$res = array();        // 返回结果
		$diyCateInfo=M('diy_cate')->where("cid=".$cid)->find();
		$res['conf_json']    = $diyCateInfo['conf_json'];
		$TPM=new PrinterMaterialModel();
		$PMInfo=$TPM->getDiyMaterial(1);
		foreach($PMInfo as $key => $value){
			$PM[$value['pma_id']]['pma_diy_formula_s']=$value['pma_diy_formula_s']."+".$value['pma_necklace_price'];
			$PM[$value['pma_id']]['pma_diy_formula_b']=$value['pma_diy_formula_b']."+".$value['pma_necklace_price'];
		}
		$res['pma_formula']=$PM;
		echo json_encode($res);
	}
	
}
?>