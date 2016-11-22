<?php
/**
 * 首页类
 *
 * @author miaomin 
 * Jul 8, 2013 1:34:10 PM
 */
class IndexAction extends CommonAction {
	
	/**
	 * 构造函数
	 */
    public function __construct() {
        parent::__construct();
        $this->header = "default";
    }

    public function ttt() {

        $UM = new UsersModel ();
        $res = $UM->getUserInfo('wow730@gmail.com', '2014bitmap');

        $LULM = new LogUserLoginModel ();
        $LULM->addLog($res [0] ['u_id'], 3);

        print_r($res);

    }
	
	
	public function index() {

	//	@load ( '@.SearchParser' );
		//$SP = new SearchParser ();
	//	$SP->parseUrlInfo ( true );
		//$SearchInfo = $SP->SearchInfo;
		//$SearchInfo ['page'] = 1;
		//$SearchInfo ['count'] = 8;
		//$SearchInfo ['cateory'] = '1263';
		//$SearchInfo ['order'] = 'dispweight_desc';
		
		//$PSM = new ProductSearchModel ( $SearchInfo, 'model', true );
		//$SearchResult = $PSM->getResult ( $SP->SearchInfo ['page'] );

		//由后台填写的p_id显示首页15
		$IND=new SettingModel();
		$result=$IND->getIdIndex();
		$IDstr=$result['value'];
		$ProductList = $this->getProductsByID($IDstr);
		$this->assign ( 'DBF_P', $this->DBF->Product );
		$this->assign ( 'SearchResult', $ProductList );

		// 创意首饰显示设计师 start
		$designer = array (
				3173,
				1713,
				1458,
				1845 
		); // 设置创意首饰首页显示的设计师ID
		$UM = new UsersModel ();
		$designerlist = $UM->getUsersProfByIDList ( $designer );
		// 创意首饰显示设计师 end

		$this->assign ( 'designerlist', $designerlist );
		$this->assign ( 'header', $this->header );
		$this->_renderPage ();
	}
	
	/**
	 * 首页
	 */
	public function index_20150422(){
		// exit;   
		$this->assign("currenturl",pub_encode_pass(urlencode(get_url()),"3dcity","encode")); //赋值当前url到模板
				
		$this->assign('indextext',L('indextext'));
		//$IDArray = array(6456,5869,5930,5842,5835,6089,6074,5724,6329);
	  //$IDArray = array(783,5869,5930,3678,644,6089,6074,3910,6329);
	  //$IDArray = array(5842,5869,5930,3678,644,6089,6074,3910,6329);
	  //$IDArray = array(5842,5869,5930,3678,6501,6089,6074,3910,6329,5724,3623,644,359,2560,5835,783);
	  //$IDArray = array(5856,5869,4166,4161,3280,1852,4618,2630,6000,3259,2307,10,3595,4107,3342,3856);
	  //$IDArray = array(6936,4189,7016,6497,7018,7013,5412,6163,7009,7010,7011,7012,7017,5130,7015,5835);
	  //$IDArray = array(6936,10120,10211,8323,9428,7013,3704,7009,7010,7011,5130,127,7012,5412,10132,10314);
	  
	  //由后台填写的p_id显示首页15		
	  $IND=new SettingModel();
	  $result=$IND->getIdIndex();
	  $IDArray=unserialize($result['value']);
	  
	
	//首页显示15个最新上传的模型
 	/* $INM=M();
	$sql="select p_id from tdf_product where p_slabel=1 order by p_createdate desc limit 0,15";
	$result1=$INM->query($sql);
	foreach($result1 as $key => $value){
		$IDArray[$key]=$value['p_id'];
	} */
	 

	//-----------------首页设计师------- begin
	$IND=new SettingModel();
	$index_uid=$IND->getIndexuid(); //获得首页设计师UID
	$designinfo=$this->get_designinfo($index_uid);
	$links=$this->get_links();
	//var_dump($designinfo);
	//-----------------首页设计师------- end
	  // $IDArray = array(555,4189,7016,6497,7018,7013,7014,6163,7009,7010,7011,7012,7017,5130,7015,5835);
	  //var_dump($IDArray);
	  //exit;
	  $ProductList = $this->getProductsByID($IDArray);

	  $IndexPic=$this->getip();
	
	  	$ztinfo_top=$this->getzt_top();
	  	$ztinfo_list=$this->getzt_list();
	  	$designuser=$this->get_designuser();
	 	
	  
	  	$this->assign('links',$links);
	  	$this->assign('designinfo',$designinfo);
	  	$this->assign('ztinfo_top',$ztinfo_top);
	  	$this->assign('ztinfo_list',$ztinfo_list);
	  	$this->assign('designuser',$designuser);
	  	$this->assign('IndexPic',$IndexPic);
	  	$this->assign('ProductList',$ProductList);
		$this->assign ( 'DBF_P', $this->DBF->Product );
		$this->assign ( 'DBF_PCT', $this->DBF->ProductCreateTool );
		$this->assign ( 'DBF_PF', $this->DBF->ProductFile );
		$this->_renderPage();
	}
	
		
	private function get_designinfo($uid=186){
		$DIM=M();
		$sql ="select TD.p_id,TD.p_name,TD.p_score,TU.u_avatar,TU.u_id,TUP.u_intro,TU.u_dispname,TUP.u_domain,TD.p_cover from tdf_product as TD ";
		$sql.="Left Join tdf_users as TU On TU.u_id=TD.p_creater ";
		$sql.="Left Join tdf_user_profile as TUP On TU.u_id=TUP.u_id ";
		$sql.="where p_creater=".$uid." order by p_score desc limit 0,6";
		$result=$DIM->query($sql);
		foreach($result as $key =>$value){
			if($value['u_domain']){
				$result [$key]['dis_u_domain'] = $result[$key]['u_domain'];
			}else{
				$result [$key]['dis_u_domain'] = "u" .$result[$key]['u_id'];
			}
		}
		return $result;
	}
	
	private function get_links(){
		$HM=new HelpModel();
		$result=$HM->getlinks_list();
		//var_dump($result);
		return $result;
	}
	
	
	private function get_designuser(){
		$UI=M();
		$sql="select * from tdf_users where u_id in (398,395,439,397,453)";
		$result=$UI->query($sql);
		//var_dump($result);
		return $result;
	}
	
	
	private function getzt_top(){
		$IND=new SettingModel();
		$result=$IND->getIdIndex();
		$info['id']		=$result['id'];
		$info['idarr']	=$result['leftvalue'];
		$IDArr=unserialize($result['leftvalue']);
	
		//exit;
		$ProductList = $this->getProductsByID($IDArr);
		$resultarr=$ProductList[1];
		$resultarr['p_intro']=msubstr($ProductList[1]['p_intro'],0,47);
		//var_dump($resultarr);
		return $resultarr;
		//$zt=new HelpModel();
		//return $zt->getzhuanti_top();		
	}
	
	private function getzt_list(){
		$IND=new SettingModel();
		$result=$IND->getIdIndex();
		$info['id']		=$result['id'];
		$info['idarr']	=$result['leftvalue'];
		$IDArr=unserialize($result['leftvalue']);
		//exit;
		$ProductList = $this->getProductsByID($IDArr);
		foreach($ProductList as $key =>$value){
			if($key>1){
			$resultarr[$key]=$ProductList[$key];
			$resultarr[$key]['p_intro']=msubstr($ProductList[$key]['p_intro'],0,11);
			}
		}
//		$resultarr[0]=$ProductList[2];
	//	$resultarr[1]=$ProductList[3];
		//$resultarr[2]=$ProductList[4];
		
		return $resultarr;
	}
	
	/*
	 * 获得首页幻灯片信息
	 */
	private function getip(){
		$IP=new HelpModel();
		$result=$IP->getindexpic();
		return $result;
	}
	
	private function getProductsByID($WhereIn){
		$PM = new ProductModel();
		$sql="select p_id,p_producttype,p_price,p_name,p_cover,p_views_disp,p_zans,p_diy_cate_cid,p_wpid,p_awards from tdf_product where p_id in (" . $WhereIn . ")  order by instr('".$WhereIn."',p_id ) ";
		$Result=$PM->query($sql);
		//var_dump($PM->getlastsql());
		return $Result;
	}
	
	
	
	private function getProductsByID_20150422($IDArray)
	{	
		$WhereIn = implode(',',$IDArray);
		$PM = new ProductModel();
		
		$DBF_P_PID = $PM->F->_Table . "." . $PM->F->ID;
		$DBF_P_UID = $PM->F->_Table . "." . $PM->F->Creater;
		$DBF_U_UID = $this->DBF->Users->_Table . "." . $this->DBF->Users->ID;
		
		$Result = $PM->join($this->DBF->Users->_Table . " ON " . $DBF_P_UID . "=" . $DBF_U_UID)
		->where($DBF_P_PID. " IN (" . $WhereIn . ")")->select();
		
		if($Result !== false)
		{
			for($i=0;$i<count($Result);$i++)
			{
				$File = $Result[$i];
				//getFileList
				$PIDWhere = '';
				$ProductFile = $this->DBF->ProductFile->_Table;
				$PCF_PID = $ProductFile . '.' . $this->DBF->ProductFile->ProductID;
				$PIDWhere.= $PCF_PID . "='" . $File[$PM->F->ID] . "'";
				$FileList = $this->getFileList($PIDWhere);
				$Result[$i]['filelist'] = $FileList;
			}
		}
	
		foreach($IDArray as $key =>$v){
			foreach($Result as $t){
				if($t['p_id']==$v){
					$Result_a[$key]=$t;
				}
			}
		}
		return $Result_a;
	}
	
	private function getFileList($PIDWhere)
	{
		$DBF = new DBF();
		$PFM = new ProductFileModel();
		$ProductFile = $DBF->ProductFile->_Table;
		$PF_PID = $ProductFile . '.' . $DBF->ProductFile->ProductID;
		$PF_CreateTool = $ProductFile . '.' . $DBF->ProductFile->CreateTool;
	
		$ProductCreateTool = $DBF->ProductCreateTool->_Table;
		$PCT_ID = $ProductCreateTool . '.' . $DBF->ProductCreateTool->ID;
	
		$PFM->join($ProductCreateTool . ' ON ' . $PF_CreateTool . ' = ' . $PCT_ID);
		$Result =  $PFM->where($PIDWhere)->select();
		return $Result;
	}
	
	
	public function rp360(){
		$CM=new ApicurlModel();
		$version_json=$CM->getversion();
		$version_arr=json_decode($version_json);
		$version=$version_arr[1];
		$this->assign('version',$version);
		$this->display();
	}


    //用hush实现frame跨域
    public function frame2(){
        $this->display();
    }


	//简笔画微信支付二维码显示页面
	public function showOrderCode(){
		$productKey=I('pkey','0','string');
		if($productKey){
			$UPM    = new UserPrepaidModel();
			$url    = $UPM->getWxPayUrl($productKey);
			$PM     = new ProductModel();
			$productInfo    = $PM->getProductinfoByPkey($productKey);
			$productCover   = $productInfo['p_cover'];
			$productCover_no= substr ( $productCover, 1 );
			$pid            = $productInfo['p_diy_id'];
			$productId      = $productInfo['p_id'];
			$UM             = new UsersModel();
			$userInfo       = $UM->getUserByID($productInfo['p_creater']);
			$agentId        = $userInfo['u_salt'].$userInfo['u_id'];
			$url_share      = WEBROOT_URL."/index/wx-shareabout-pid-".$productId."-showtype-10.html";
		}
		$this->assign('url',$url);
		$this->assign('url_share',$url_share);
		$this->assign('productCover',$productCover);
		$this->assign('productCover_no',$productCover_no);
		$this->assign('pid',$pid);
		$this->assign('agentId',$agentId);
		$this->display();
	}

	//微信支付二维码显示页面
	public function showOrderCodeVR(){
		//$up_orderid=I('up_orderid','0','string');

		/*$this->assign('url_share',$url_share);
		$this->assign('productCover',$productCover);
		$this->assign('productCover_no',$productCover_no);
		$this->assign('pid',$pid);
		$this->assign('agentId',$agentId);*/
		$this->display();
	}

	public function printAgent(){
		$useragent = isset ( $_SERVER ['HTTP_USER_AGENT'] ) ? $_SERVER ['HTTP_USER_AGENT'] : '';
		$this->assign ( 'mobileAgent', $useragent);
		$this->display();
	}

	/**
	 * creater guolixun 2016-09-24
	 * 触摸屏商品
	 */
	public function androidIndex(){
		$agentid = I('agentid','','string');
		$info = M('help_doc')->where('cate = 12 and status =0')->field('title,pic,pic_link')->select();
		$this->assign('agentid',$agentid);
		$this->assign('info',$info);
		//dump($info);
		$this->display();
	}

	/**
	 *vr购物订单获取
	 */
	public function getorderbyvr(){
		$sql="select up_id,up_orderid,up_amount,up_productid from tdf_user_prepaid where vr_sign=1 ORDER BY up_dealdate DESC LIMIT 0,1";
		$orderInfo=M('user_prepaid')->query($sql);
		$orderInfo=$orderInfo[0];
		$productArr =unserialize($orderInfo['up_productid']);
		$TPM = new ProductModel();
		foreach($productArr as $key => $value){
			$productExArr           = explode(",",$value);
			$productlist[$key]['p_id']    = $productExArr[0];
			$productlist[$key]['count']   = $productExArr[1];
			$productOne=$TPM->getProductByID($productExArr[0]);
			$productlist[$key]['cover']   = $productOne['p_cover'];
			$productlist[$key]['cover_small']   = str_replace('/o/','/s/240_180_',$productOne['p_cover']);
			//replace:'/o/':'/s/240_180_'
			$productlist[$key]['price']   = $productOne['p_price'];
			$productlist[$key]['name']   = $productOne['p_name'];
		}
		$orderInfo['product']   = $productlist;
		$UPM    = new UserPrepaidModel();
		$url    = $UPM->getWxPayUrlByVR($orderInfo['up_orderid']);
		$orderInfo['url']       = $url;
		$orderInfoJson=json_encode($orderInfo);
		//dump($orderInfo);
		echo $orderInfoJson;
	}
	
	

}