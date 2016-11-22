<?php

class UserDiyModel extends Model{


    /*
     * APP通过接口保存用户diy数据
     */
    public function saveDiy($datainfo){
        $DM=new DiyUnitModel();
        $DU=$DM->getDiyUnitAllByCid($datainfo['cid']);
        //var_dump($DU);
        //exit;
        foreach($datainfo as $key => $value){
             if($key=='attribute'){
                foreach($value as $attkey => $attvalue){
                    $unitId=$this->getIdByUnitName($datainfo['cid'],$attkey);
                    $diyinfo[$unitId]=$attvalue;
                }
            }
        }
        $PM=new ProductModel();
//----------------------------------保存DIY数据到tdf_user_diy中-----start--------------
        $data['u_id']=$datainfo['u_id'];
        $data['title']=$datainfo['attribute']['Textvalue']?$datainfo['attribute']['Textvalue']:"首饰定制";
        $data['diy_unit_info']	=serialize($diyinfo);
        $data['price']			=$datainfo['price'];
        $data['cid']			=$datainfo['cid'];
        $data['cover']          =$PM->getProductCover($datainfo['cid']);
        if($datainfo['udid']){
            $saveResult=M('user_diy')->where("id=".$datainfo['udid'])->save($data);
            $diyId=$datainfo['udid'];
        }else{
            $saveResult=M('user_diy')->add($data);
            $diyId=$saveResult;
        }
//----------------------------------保存DIY数据到tdf_user_diy中-----end----------------

//----------------------------------保存到product表中--------------start---------------
        if($datainfo['stype']==1){ //如果stype=1则为生成productid和加入购物车
            $p_data['p_name']			= $data['title'];
            $p_data['p_cate_4']			= intval($data['cid']);//产品类别 实际为diy的cate
            $p_data['p_creater']		= $data['u_id'];
            $p_data['p_cover']			= $data['cover'];
            $p_data['p_price']			= $data['price'];
            $p_data['p_createdate']		= date("Y-m-d G:i:s",time());
            $p_data['p_createtime']		= time();
            $p_data['p_lastupdate']		= date("Y-m-d G:i:s",time());
            $p_data['p_lastupdatetime']	= time();
            $p_data['p_producttype']	=4;//产品类型 1为数字模型 2为实物商品(3d打印机) 3为打印件 4为DIY产品
            $p_data['p_diy_id']			=$diyId;

            if($PM->getProductByDiyid($p_data['p_diy_id'])){//如果存在有diy_id,修改更新
                if($PM->where("p_diy_id=".$p_data['p_diy_id']."")->save($p_data)){
                    $pm_info=$PM->getProductByDiyid($p_data['p_diy_id']);
                    $pid=$pm_info['p_id'];
                }
            }else{//如果没有diy_id，则新增
                $pid=$PM->add($p_data);
            }
            $UCM=new UserCartModel();//购物车对象
            $isAdded = $UCM->addProduct_diy($pid,$data['u_id']);//加入购物车
            $resultEnd=$isAdded?1:0;
        }else{
            $resultEnd=$saveResult?1:0;
        }
//----------------------------------保存到product表中--------------end-----------------
        return $resultEnd;
    }



/*
 * 根据cid和unitame数组获取tdf_diy_unit中的id
 */
    public function getIdByUnitName($cid,$unitname){
        $result=M('diy_unit')->where("cid='".$cid."' and unit_name='".$unitname."'")->find();
        return $result['id'];
    }

    /**
     * 根据uid获取userdiylist列表
     */
    public function getUserDiyList($u_id)
    {

        $sql = "select TUD.id,TUD.title,TUD.ctime,TU.u_email,TUD.diy_unit_info,TUD.cover,TUD.price,TUD.cid,TDC.cate_name from tdf_user_diy as TUD ";
        $sql .= "Left Join tdf_users as TU On TU.u_id=TUD.u_id ";
        $sql .= "Left Join tdf_diy_cate as TDC On TDC.cid=TUD.cid ";
        $sql .= "where TUD.delsign=0 and TUD.u_id=" . $u_id . " order by TUD.ctime desc";
        $udlist = M('user_diy')->query($sql);
        $DUM = new DiyUnitModel();
        foreach ($udlist as $key => $value) {
            $unit_info = unserialize($value['diy_unit_info']);
            $udlist[$key]['text'] = $unit_info[7];
            $udlist[$key]['diyInfo'] = $DUM->getUnitByUserDiy($value['cid'], $unit_info);
        }
        return $udlist;
    }

    /**
     * 删除用户diy方案
     */
    public function delUserDiy($udid,$uid){
        $UDM=M("user_diy");
        $userDiy=$UDM->where("id=".$udid." and u_id=".$uid."")->find();
        if($userDiy){
            $result=$UDM->where("id=".$udid."")->delete();
        }else{
            $result=0;
        }
        return $result;
    }


    public function getUserDiyInfoById($id){
        $UDM=M("user_diy");
        $userDiy=$UDM->where("id=".$id)->find();
        if($userDiy){
            $result=$userDiy;
        }else{
            $result=0;
        }
        return $result;
    }

    //简笔画保存(先保存到user_diy中,再生产product保存至tdf_product中)
    public function saveDrawDiyProduct($pid,$projDataArr){
        $modelData      = $projDataArr['projectData'];                        //简笔画json文件
        $imageData      = $projDataArr['captureData'];        //简笔画截图
        $image2dData    = $projDataArr['capture2dData'];        //简笔画2d截图
        //$agentId      = $projDataArr['agentId'];           //代理商ID
        $productKey     = $projDataArr['productKey'];       //APP生成产品的唯一p_key
        $clienttype     = $projDataArr['clienttype'];       //客户端类型
        $category       = $projDataArr['category'];         //类型:手链 单环项链 双环项链
        $cid            = $projDataArr['cid'];              //diy类别id
        $clientUserId   = $projDataArr['clientUserId'];      //APP 发的用户id(pub_encode_pass过的)
        if($clienttype==2){
            $uid = pub_encode_pass($clientUserId,'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm','decode');//用户id
        }else{
            $uid = $projDataArr['uid'];                             //用户id
        }
        $material       = $projDataArr['material']?$projDataArr['material']:'925银';//材质
        $price          = $projDataArr['price']?$projDataArr['price']:0;        //价格
        $MD5File16Name  = substr ( md5 ( $modelData ), 8, 16 );          //文件名
        $modelDotSuffix = '.' . 'json'; //后缀名
        $TPM=new ProductModel();

        if($pid){
            $productInfo=$TPM->getProductByID($pid);
            $p_diy_id=$productInfo['p_diy_id'];
            $diyResult=$this->getUserDiyInfoById($p_diy_id);
            //var_dump($diyResult);
            $diyUnitInfo=unserialize($diyResult['diy_unit_info']);
            $output_file=".".current($diyUnitInfo);
            $productImagePath=$diyResult['cover'];

        }else{
            $SavePath = C ( 'UPLOAD_PAHT.PROJECT' ).'/'.date('Ymd',time());
            $SubDir = '/jsonfile/';
            $target_path = $SavePath . $SubDir;
            if (!file_exists ( $target_path )) {
                mkdir ( $target_path, 0777, true );
            }
            $output_file = $target_path . $MD5File16Name . $modelDotSuffix;

            //$imagePath=$this->saveProjectImage($imageData);
            //$image2dPath=$this->saveProjectImage($image2dData);
            //$jsonFileResult=file_put_contents ( $output_file, $modelData );
        }
        if(SAVETOOSS){//如果上传到本地服务器保存
            $imagePath=$this->saveProjectImageToOss($imageData,$productImagePath);//保存图片
            $image2dPath=$this->saveProjectImageToOss($image2dData,$productImagePath);//保存图片
            $this->saveProjectToOss($output_file, $modelData);
        }else{
            $imagePath=$this->saveProjectImage($imageData,$productImagePath);
            $image2dPath=$this->saveProjectImage($image2dData);
            $jsonFileResult=file_put_contents ( $output_file, $modelData );
        }

        $material=$material?$material:'925银';
        $result_pid=$this->updateDrawDiyProduct($pid,$uid,$output_file,$material,$price,$imagePath,$productKey,$image2dPath,$cid,$category);
        if($result_pid){
            $UCM=new UserCartModel();
            //---------------------------APP直接从diy产品生成订单------start
            if($clienttype==3) {
                $result['pid']      = $result_pid;
                $res = $UCM->procuct_cart_order($result_pid);//APP直接生成订单
            }elseif($clienttype==1){//1为第三方接入 不用加购物车和生成订单
                $diy_id         = $TPM->getUdidByPid($result_pid);
                $result['pid']  = $diy_id;
                $res=1;
            }else{
                $result['pid']      = $result_pid;
                $res = $UCM->addProduct($result_pid,$uid);//加入购物车
            }
            //---------------------------APP直接从diy产品生成订单------end
            $result['img']      = WEBROOT_URL.getDropDotPath($image2dPath);
            $result['material'] = $material;
            $result['price']    = $price;
            $result['category'] = $category;

        }
        return $result;
    }

    /**简笔画工程文件保存到阿里云OSS上--------不生成缩略图(缩略图由阿里云图片服务来处理)
     * @param $captureData        图片base64数据
     * @param string $imagePath   图片完整路径
     * @return bool|string
     */
    public function saveProjectToOSS($output_file,$projectData){
        $ossclient=new AliyunossModel();
        $ossclient->putObject($output_file,$projectData);
    }

    /**简笔画保存截图----保存到阿里云OSS上--------不生成缩略图(缩略图由阿里云图片服务来处理)
     * @param $captureData        图片base64数据
     * @param string $imagePath   图片完整路径
     * @return bool|string
     */
    public function saveProjectImageToOSS($captureData,$imagePath=''){
        // ---------- BASE64编码在HTTP传输过程中会自动将加号替换成空格必须再进行反处理 ----------
        $capture_str=substr($captureData,0,strpos($captureData,";base64"));
        $isjpg=strpos($capture_str,"jpg");
        if($isjpg){//如果是jpg图片
            $imgData = base64_decode ( str_replace ( ' ', '+', str_replace ( 'data:image/jpg;base64,', '', $captureData ) ) );
            $imgDotSuffix = '.jpg';
        }else{
            $imgData = base64_decode ( str_replace ( ' ', '+', str_replace ( 'data:image/png;base64,', '', $captureData ) ) );
            $imgDotSuffix = '.png';
        }
        if($imagePath==''){
            $MD5File16Name = substr ( md5 ( $imgData ), 8, 16 );
            // ---------- 截图文件保存路径设定 ----------
            $SavePath = C ( 'UPLOAD_PAHT.PROJECT' ).'/'.date('Ymd',time());
            $SubDir = '/image/';
            $target_path = $SavePath . $SubDir;
            $original_path = $target_path . 'o/';
            // ---------- 截图文件保存 ----------
            $output_file = $original_path . $MD5File16Name . $imgDotSuffix;
        }else{
            $output_file=".".$imagePath;
        }
        //-----------------------------------文件保存到阿里云OSS上----------------start--------
        $ossclient=new AliyunossModel();
        $ossclient->putObject($output_file,$imgData);
        //-----------------------------------文件保存到阿里云OSS上----------------End--------
        $result=$output_file;
        return $result;
    }



    /**简笔画保存截图----保存到本地服务器上------------如果用阿里OSS存储请无视
     * @param $captureData          图片base64数据
     * @param string $imagePath   图片完整路径
     * @return bool|string
     */
    public function saveProjectImage($captureData,$imagePath=''){
        // ---------- BASE64编码在HTTP传输过程中会自动将加号替换成空格必须再进行反处理 ----------
        $capture_str=substr($captureData,0,strpos($captureData,";base64"));
        $isjpg=strpos($capture_str,"jpg");
        if($isjpg){//如果是jpg图片
            $imgData = base64_decode ( str_replace ( ' ', '+', str_replace ( 'data:image/jpg;base64,', '', $captureData ) ) );
            $imgDotSuffix = '.jpg';
        }else{
            $imgData = base64_decode ( str_replace ( ' ', '+', str_replace ( 'data:image/png;base64,', '', $captureData ) ) );
            $imgDotSuffix = '.png';
        }
        if($imagePath==''){
            $MD5File16Name = substr ( md5 ( $imgData ), 8, 16 );
            // ---------- 截图文件保存路径设定 ----------
            $SavePath = C ( 'UPLOAD_PAHT.PROJECT' ).'/'.date('Ymd',time());
            $SubDir = '/image/';
            $target_path = $SavePath . $SubDir;
            $original_path = $target_path . 'o/';
            if (! file_exists ( $original_path )) {
                mkdir ( $original_path, 0777, true );
            }
            // ---------- 截图文件保存 ----------
            $output_file = $original_path . $MD5File16Name . $imgDotSuffix;
            $thumbPath = $target_path . 's/';
            $imageName=$MD5File16Name.$imgDotSuffix;
        }else{
            $output_file=".".$imagePath;
            $thumbPath=substr(".".$imagePath,0,strrpos($imagePath,'/o/')+1).'/s/'; //缩略图路径
            $imageName=substr(".".$imagePath,strrpos($imagePath,'/')+2);        //原图片名称(带后缀 .png)
        }
         $writeRes = file_put_contents ( $output_file, $imgData );

        // ---------- 截图文件生成缩略图 ----------
       // if ($writeRes) {
            import ( 'ORG.Net.ImageCheck' );
            $imagea = new ImageCheck ();
            $imageSizeArr = getimagesize ( $output_file );
            //--------------裁剪为正方形--------start
            /*if (($imageSizeArr [0] < C ( 'WEBGL.CAPTURE_NORMAL_WIDTH' )) || ($imageSizeArr [1] < C ( 'WEBGL.CAPTURE_NORMAL_HEIGHT' ))) {
              //  $imagea->imagezoom ( $output_file, $output_file, C ( 'WEBGL.CAPTURE_NORMAL_WIDTH' ), C ( 'WEBGL.CAPTURE_NORMAL_HEIGHT' ), "#FFFFFF" );
            }*/
            //--------------裁剪为正方形--------start
            $imagea->imagezoom ( $output_file, $output_file, $imageSizeArr [0], $imageSizeArr [1], "#FFFFFF" ); //裁剪图片并填充白色,会速度变慢
            //$imagea->imagefillcolor( $output_file, $output_file, $imageSizeArr [0], $imageSizeArr [1], "#FFFFFF" ); //填充白色
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
            $len = count ( $thumbWidth );
            for($i = 0; $i < $len; $i++) {
                $prefix = $thumbWidth [$i] . '_' . $thumbWidth [$i] . '_';
                $thumbname = $prefix . $imageName;
                Image::thumb2 ( $output_file, $thumbPath . $thumbname, '', $thumbWidth [$i], $thumbWidth [$i], true );
            }
        //}
    $result = $output_file;
    return $result;
    }


    /**
     * @param $uid 用户id
     * @param $jsonFileUrl 简笔画json文件路径
     * @param $price 价格
     */
    public function updateDrawDiyProduct($pid,$uid=0,$jsonFileUrl,$material='925银',$price=0,$imagePath,$productKey,$image2dPath="",$cid=1,$category){
        $imagePath      = getDropDotPath($imagePath);
        $image2dPath    = getDropDotPath($image2dPath);
        $DM=new DiyUnitModel();
        $DU=$DM->getDiyUnitAllByCid($cid);
        $PM=new ProductModel();
        foreach($DU as $key=>$value ){
            if($value['fieldtype']=='JSONFILE'){
                $diyInfo[$value['id']]=substr($jsonFileUrl,1);
            }elseif($value['fieldtype']=='MATERIAL'){
                $diyInfo[$value['id']]=$material;
            }elseif($value['fieldtype']=='NECKLACE'){
                $diyInfo[$value['id']]=$category;
            }
        }
        $DCM    = new DiyCateModel();
        $diyCateInfo                = $DCM->getDiyCateByCid($cid);
        $data['u_id']               = $uid;
        $data['title']              = $diyCateInfo['cate_name'];
        $data['diy_unit_info']	    = serialize($diyInfo);
        $data['price']			    = $price;
        $data['cid']			    = $cid;
        $data['cover']              = $imagePath;
        $data['img']                = $image2dPath;
        $p_data['p_name']			= $data['title'];
        $p_data['p_cate_4']			= intval($data['cid']);//产品类别 实际为diy的cate
        $p_data['p_creater']		= $data['u_id'];
        $p_data['p_cover']			= $data['cover'];
        $p_data['p_price']			= $data['price'];
        $p_data['p_createdate']		= date("Y-m-d G:i:s",time());
        $p_data['p_createtime']		= time();
        $p_data['p_lastupdate']		= date("Y-m-d G:i:s",time());
        $p_data['p_lastupdatetime']	= time();
        $p_data['p_producttype']	= 4;//产品类型 1为数字模型 2为实物商品(3d打印机) 3为打印件 4为DIY产品
        $p_data['p_key']	        = $productKey;
        $p_data['p_intro']			= $material." ".$category;
        if(!$pid) {
            $diyId=$this->add($data);
            $p_data['p_diy_id']		= $diyId;
            $productID=$PM->add($p_data);
            //var_dump($productID);
        }else{
            $productInfo=$PM->getProductByID($pid);
            $diyid=$productInfo['p_diy_id'];
            $this->where("id=".$diyid)->save($data);
            $PM->where("p_id=".$pid)->save($p_data);
            $productID=$pid;
        }
        return $productID;
    }

    /*
   * 新版小鱼通过json数据保存diy信息
   */
    public function saveDiyJson($datas){
        $clienttype     = $datas['clienttype'];
        $productKey     = $datas['productKey'];
        $clientUserId   = $datas['clientUserId'];
        $cid            = $datas['cid'];
        $diyId          = $datas['diyid'];
        if($clientUserId){//如果有clientUserId 为第三方通过iframe嵌入
            $clientUserId= pub_encode_pass($clientUserId,'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm','decode');
            $uid         = $clientUserId;
        }else{
            $uid = $datas['uid'];
        }
        $TDC        = new DiyCateModel();
        $cateInfo   = $TDC->getDiyCateByCid($cid);
        $PM         = new ProductModel();
            $proj_json          = json_encode($datas['proj_json']);
            $price              = intval($datas['price']);
            $stype              = $datas['stype'];      //stype为1时,直接购买,加入购物车.  0为保存方案
            $coverData          = $datas['coverData']; //截图二进制数据
            $title              = $cateInfo['cate_name'];
            $diy_data['title']  = $title;
            $diy_data['u_id']   = $uid;
            $diy_data['price']	= $price;
            $diy_data['cid']    = $cid;
            $diy_data['diy_unit_info']  = $proj_json;
        if($diyId){//更新diy信息
                $result=M('user_diy')->where("id=".$diyId)->save($diy_data);
            }else{//新增diy信息
                $result=M('user_diy')->add($diy_data);
                $diyId=$result;
            }
            $p_data['p_name']			= $title;
            $p_data['p_cate_4']			= intval($cid);//产品类别
            $p_data['p_creater']		= $uid;
            //$p_data['p_cover']		= $cover_path;
            $p_data['p_price']			= $price;
            $p_data['p_createdate']		= date("Y-m-d G:i:s",time());
            $p_data['p_createtime']		= time();
            $p_data['p_lastupdate']		= date("Y-m-d G:i:s",time());
            $p_data['p_lastupdatetime']	= time();
            $p_data['p_producttype']	= 4;//产品类型 1为数字模型 2为实物商品(3d打印机) 3为打印件 4为DIY产品
            $p_data['p_diy_id']			= $diyId;
            $p_data['p_key']			= $productKey; //APP生成产品的唯一p_key 如果存在此值 会保存到订单表中

            if($PM->getProductByDiyid($p_data['p_diy_id'])){//如果存在有diy_id,修改更新
                if($PM->where("p_diy_id=".$p_data['p_diy_id']."")->save($p_data)){
                    $pm_info=$PM->getProductByDiyid($p_data['p_diy_id']);
                    $pid=$pm_info['p_id'];
                }
            }else{//如果没有diy_id，则新增
                $pid=$PM->add($p_data);
            }
            //--------------------------------------------------保存截图-------------------start----------------------
            $PPM=new ProductPhotoModel();
            $saveResult = $PPM->saveCaptureModel($pid, $coverData);
            //--------------------------------------------------保存截图-------------------end------------------------
            if($stype==1){//加入购物车
                $UCM=new UserCartModel();
                if($productKey){//判断是否为APP加入购物车
                    $product_c=$UCM->procuct_cart_order($pid);//APP直接生成订单
                }else{
                    $isAdded = $UCM->addProduct_diy( $pid, $uid);
                }
                $jurl=WEBROOT_URL."/user.php/cart";
            }else{
                $jurl=WEBROOT_URL."/user.php/mydiy/jewelrylist.html";
            //}
             }
        if($clienttype==1) {//如果是第三方接入
            $productInfo = $PM->getProductByID($pid);
            $res['pid'] = $productInfo['p_diy_id'];
            $res['img'] = WEBROOT_URL . $productInfo['p_cover'];
            //$res['info']  = $productInfo['p_intro'];
            //-----------------需做判断,如果是简笔画返回的不同
            $res['info'] = json_decode($productInfo['p_mini']);
            //-----------------
            return $res;
        }elseif($clienttype==3) {//3为触摸屏机器类型  *******需要测试  2016.09.24***************
            $res = "../index/index-showordercode-pkey-" . $productKey;
            return $res;
        }else{
            return $jurl;
        }
    }

    /**保存简笔画文件和图片
     * @param int $pid
     * @param int $xmlstr
     * @return string
     */
    public function saveDiyJsonProject($pid=0,$xmlstr=0){
        if ($xmlstr) {
           // $projDataArr=json_decode($xmlstr);
            $projDataArr=$xmlstr;
            $UDM=new UserDiyModel();
            $result=$this->saveDrawDiyProduct($pid,$projDataArr);
            if($projDataArr['clienttype']==3) {//3为触摸屏机器类型
                $res = "../index/index-showordercode-pkey-" . $projDataArr['productKey'];
            }elseif($projDataArr['clienttype']==1){ //1为第三方iframe接入
                $result['showdes']=$result['material']." ".$result['category'];
                $res=$result;
            }elseif($projDataArr['clienttype']==2){//2为APP返回0,由APP自己跳转
                $res= '../user.php/cart';
            }else{
                if($projDataArr['uid']){
                    $res= "../user.php/cart";
                }else{
                    $PM     = new ProductModel();
                    $diyid  = $PM->getUdidByPid($result['pid']);
                    $res= "../user.php/login/?from_url=".WEBROOT_URL."/index/diy-smartjewelry-pid-".$diyid.".html";
                }
            }
        }else{
            $res="error！无数据。";
        }
        return $res;
    }

    

}