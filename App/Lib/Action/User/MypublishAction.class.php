<?php
class MypublishAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		//
		parent::__construct ();
		
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
		
		//load ( "@.DBF" );
		$this->DBF = new DBF ();
		

	}
	
	public function uploadmodel(){
		$this->_renderPage ();
	}
	
	/**
	 * 我的发布
	 *
	 * @access public
	 * @return mixed
	 */
	public function index() {
		try {
			@load ( '@.Paging' );
			@load ( "@.SearchParser" );
			$SP = new SearchParser ();
			$SP->parseUrlInfo ( true );
			$SearchInfo = $SP->SearchInfo;
			$SearchInfo['category'] = 1;
			$SearchInfo['page'] = $this->_get('page');
			$SearchInfo['creater'] = $this->_session ( 'f_userid' );
			//print_r($SearchInfo);
			$PSM = new ProductSearchModel ( $SearchInfo, 'model', false );
			$UserPublish = $PSM->getResult ();
			$this->assign ( 'SearchResult', $PSM->getResult ( $SP->SearchInfo ['page'] ) );
			$this->assign ( 'DBF_P', $this->DBF->Product );
			$this->assign ( 'DBF_PCT', $this->DBF->ProductCreateTool );
			$this->assign ( 'DBF_PF', $this->DBF->ProductFile );
			$this->assign ( 'PI', getPagingInfo ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SP->SearchInfo ['count'], 4 ) );
			$Url = $SP->getFormattedUrl ();
			$this->assign ( 'BaseUrl', U ( 'Front/models/index/?' . substr ( $Url ['url'], 1 ) ) );
			$this->assign ( 'SearchInfo', $SP->SearchInfo );
			$this->assign ( 'HtmlCtrl', $SP->getHtmlCtrls () );
			$this->assign ( 'FormAction', U ( 'Front/models/index' ) );
			
			// 缩略图
			$thumb_url = array ();
			$thumb_url ['1'] = trim_slash_url ( preg_replace ( '|/thumb/(\d*)|', '', __SELF__ ) ) . '/thumb/1';
			$thumb_url ['2'] = trim_slash_url ( preg_replace ( '|/thumb/(\d*)|', '', __SELF__ ) ) . '/thumb/2';
			$thumbOpt = get_search_opt ( 'thumb' );
			
			foreach ( $thumbOpt as $key => $val ) {
				if ($val ['opt'] == $SearchInfo ['thumb']) {
					$nowThumb = $val ['name'];
					$thumbOpt [$key] ['del'] = 1;
					$thumbOpt [$key] ['url'] = $thumb_url [$val ['opt']];
				} else {
					$thumbOpt [$key] ['del'] = 0;
					$thumbOpt [$key] ['url'] = $thumb_url [$val ['opt']];
				}
			}
			//print_r($thumbOpt);
			$this->assign ( 'nowThumb', $nowThumb );
			$this->assign ( 'thumbopt', $thumbOpt );
			
			// 每页显示
			$paging_url = array ();
			$paging_url ['20'] = trim_slash_url ( preg_replace ( '|/count/(\d*)|', '', __SELF__ ) ) . '/count/20';
			$paging_url ['30'] = trim_slash_url ( preg_replace ( '|/count/(\d*)|', '', __SELF__ ) ) . '/count/30';
			$paging_url ['50'] = trim_slash_url ( preg_replace ( '|/count/(\d*)|', '', __SELF__ ) ) . '/count/50';
			$paging_url ['100'] = trim_slash_url ( preg_replace ( '|/count/(\d*)|', '', __SELF__ ) ) . '/count/100';
			
			$paging_url ['20'] = trim_slash_url ( preg_replace ( '|/page/(\d*)|', '', $paging_url ['20'] ) );
			$paging_url ['30'] = trim_slash_url ( preg_replace ( '|/page/(\d*)|', '', $paging_url ['30'] ) );
			$paging_url ['50'] = trim_slash_url ( preg_replace ( '|/page/(\d*)|', '', $paging_url ['50'] ) );
			$paging_url ['100'] = trim_slash_url ( preg_replace ( '|/page/(\d*)|', '', $paging_url ['100'] ) );
			
			$pageOpt = get_search_opt ( 'count' );
			foreach ( $pageOpt as $key => $val ) {
				if ($val ['opt'] == $SearchInfo ['count']) {
					$nowPage = $val ['name'];
					$pageOpt [$key] ['del'] = 1;
					$pageOpt [$key] ['url'] = $paging_url [$val ['opt']];
				} else {
					$pageOpt [$key] ['del'] = 0;
					$pageOpt [$key] ['url'] = $paging_url [$val ['opt']];
				}
			}
			$this->assign ( 'nowPage', $nowPage );
			$this->assign ( 'pageopt', $pageOpt );
			
			// 排序方式
			$order_url = process_filter_page_url ( __SELF__ );
			$ording_url = array ();
			$ording_url ['createdate_desc'] = trim_slash_url ( preg_replace ( '|/order/[a-z]{3}|', '', $order_url ) ) . '/order/crd';
			$ording_url ['createdate_asc'] = trim_slash_url ( preg_replace ( '|/order/[a-z]{3}|', '', $order_url ) ) . '/order/cra';
			$orderOpt = get_search_opt ( 'order' );

			foreach ( $orderOpt as $key => $val ) {
				if ($val ['opt'] == $SearchInfo ['order']) {
					$nowOrder = $val ['name'];
					$orderOpt [$key] ['del'] = 1;
					$orderOpt [$key] ['url'] = $ording_url [$val ['opt']];
				} else {
					$orderOpt [$key] ['del'] = 0;
					$orderOpt [$key] ['url'] = $ording_url [$val ['opt']];
				}
			}
			$this->assign ( 'nowOrder', $nowOrder );
			$this->assign ( 'orderopt', $orderOpt );
			
			// 显示样式
			$disp_url = array ();
			
			$disp_url ['1'] = trim_slash_url ( preg_replace ( '|/disp/(\d*)|', '', __SELF__ ) ) . '/disp/1';
			$disp_url ['2'] = trim_slash_url ( preg_replace ( '|/disp/(\d*)|', '', __SELF__ ) ) . '/disp/2';
			$dispOpt = get_search_opt ( 'disp' );
			foreach ( $dispOpt as $key => $val ) {
				if ($val ['opt'] == $SearchInfo ['disp']) {
					$dispOpt [$key] ['url'] = $disp_url [$val ['opt']];
				} else {
					$dispOpt [$key] ['url'] = $disp_url [$val ['opt']];
				}
			}
			$this->assign ( 'dispopt', $dispOpt );
			$this->_renderPage ();
		} catch ( Exception $e ) {
			//
			// echo $e->getMessage ();
		}
	}
	

	//<--------------------------------------------upload webgl AR by zhangzhibin  start
	public function uploadar(){
		$pid=I('pid');
		//echo $pid;
		$PM=new ModelsModel;
		$pmodel	= $PM->getProductModelByID($pid);
		if($this->isPost()){
			$fileurl=I('fileurl');
			$p_id=I('p_id');
			$url = "https://api.sketchfab.com/v1/models";
 			$path = "./";
			$filename = $fileurl;
			$description = "Test of the api with a simple model";
			$token_api = "b5dad6cb320141d190c65d3e7a77a6a2";
			$title = "AR".time();
			$tags = "test collada glasses";
			$private = 0;
			$password = "11111";
			$data = array(
			    "title" => $title, 
			    "description" => $description,
					"fileModel" => "@".$path.$fileurl,
			    "filenameModel" => 'VICTORY.3DS',
			    "tags" => $tags,
			    "token" => $token_api,
			    "private" => $private,
			    "password" => $password
			);
			//var_dump($data);
			//exit;
			$ch = curl_init();
			curl_setopt_array($ch, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_SSL_VERIFYPEER => FALSE,
			    CURLOPT_SSL_VERIFYHOST => FALSE,
			    CURLOPT_URL => $url,
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $data
			));
			$response = curl_exec($ch);
			curl_close($ch);
			//echo "<br>response:".$response;
			$js_arr=json_decode($response,true);
		
			//var_dump($js_arr);
			$result_id=$js_arr['result']['id'];
			$result_success=$js_arr['success'];
			//echo "<br>result_id:".$result_id;
			//echo "<br>result_success:".$result_success;
			//exit;
			if($result_success==1){
				$AR =M('ProductModel'); 
				// 要修改的数据对象属性赋值
				$data_up['pm_isar'] 		= 1;
				$data_up['pm_arcode']	= $result_id;
				$AR->where('p_id="' . $p_id . '"')->save($data_up);// 根据条件保存修改的数据

				/*$AR = M("product_reality"); // 实例化User对象
				$data['p_id'] 				= $_POST['title'];//模型ID
				$data['r_type']				= 1;//1为AR
				$data['r_mdname'] 		= $fileurl;//上传文件名
				$data['r_createdate'] = date('Y-m-d H:i:s',NOW_TIME);//创建时间
				$data['r_lastupdate'] =	date('Y-m-d H:i:s',NOW_TIME);//更新时间
				$data['r_enable'] 		= 1;
				$AR->add($data);*/
				//exit;
				$this->success(L('success_tips'), '__DOC__/user.php/mypublish/index');
			}
		}
		
		
		if($pmodel['pm_isar']==0){
			$element.="<div class='upleft'></div>";
			$element.="<div class='upright'>上传AR演示文件:<br><br>";
			$element.="<input type='hidden' value='".$pmodel['pm_arcode']."'>";
			$element.="<input type='hidden' name='token' value='b5dad6cb320141d190c65d3e7a77a6a2' ><br>";
			$element.= "<input type='hidden' name='p_id' value='".$pmodel['p_id']."'><br>";
			$element.= "<input type='hidden' name='title' value='".$pmodel['p_id']."'><br>";
			$element.="<input type='text' name='fileurl' id='fileurl' value=''><input type='button' id='insertfile' value='选择文件' />";
			$element.="<br><br><input type='submit' name='button' id='button' value='提 交'>";
			$element.="</div>";
		}else{
			$element.="<div class='upleft'><iframe frameborder=0 width=300 height=350 webkitallowfullscreen=true mozallowfullscreen=false src=http://sketchfab.com/embed/".$pmodel['pm_arcode']."?autostart=1;nocamera=0;transparent=1;autospin=23;controls=0;watermark=0;desc_button=1;stop_button=1></iframe></div>";
			$element.="<div class='upright'>上传AR演示文件:<br><br>";
			$element.="<input type='hidden' value='".$pmodel['pm_arcode']."'>";
			$element.="<input type='hidden' name='token' value='b5dad6cb320141d190c65d3e7a77a6a2' ><br>";
			$element.= "<input type='hidden' name='p_id' value='".$pmodel['p_id']."'><br>";
			$element.= "<input type='hidden' name='title' value='".$pmodel['p_id']."'><br>";
			$element.="<input type='text' name='fileurl' id='fileurl' value=''><input type='button' id='insertfile' value='选择文件' />";
			$element.="<br><br><input type='submit' name='button' id='button' value='提 交'>";
			$element.="</div>";
		}
		
		$temp_postfile=$_FILES;
		$temp_a=$_POST['token'];
		//echo "post:".$temp_postfile;
		$show_html="<form action='' method='post' enctype='multipart/form-data'>";
		$show_html.=$element;
	
		$show_html.="</form>";
		$this->assign('showhtml',$show_html);
		$this->_renderPage ();
	}
	//<--------------------------------------------by zhangzhibin  end >
	
	
	//<--------------------------------------------upload PrintModel to shapeways by zhangzhibin  start
	public function uploadprintmodel(){
		Vendor('Shapeways.Swapi');//包含扩展shapeways的api
		
		$pid=I('pid');
		//echo $pid;
		$PM=new ModelsModel;
		$pmodel	= $PM->getProductModelByID($pid);
	
		if($this->isPost()){
			$fileurl=I('fileurl');
			$p_id=I('p_id');
						
			$shapways=new ShapewaysApi();
			$result_json=$shapways->uploadmodel($fileurl);

			//echo "out:";
			var_dump($result_json);
			//echo "<br>re:".$result_json->result;
			//exit;
			if($result_json->result=="success"){
				$AR =M('ProductModel');
				$data_up['pm_shapewaysid']	= $result_json->modelId;
				$AR->where('p_id="' . $p_id . '"')->save($data_up);// 根据条件保存修改的数据
	
				$this->success(L('success_tips'), '__DOC__/user.php/mypublish/uploadprintmodel/pid/'.$p_id);
			}
		}
	
		if($pmodel['pm_shapewaysid']==0){
			$element.="<div class='upleft'></div>";
			$element.="<div class='upright'>上传可以打印的3D模型<br><br>";
			$element.= "<input type='hidden' name='p_id' value='".$pmodel['p_id']."'><br>";
			$element.= "<input type='hidden' name='title' value='".$pmodel['p_id']."'><br>";
			$element.="<input type='text' name='fileurl' id='fileurl' value=''><input type='button' id='insertfile' value='选择文件' />";
			$element.="<br><br><input type='submit' name='button' id='button' value='提 交'>";
			$element.="</div>";
		}else{
			
			$shapways=new ShapewaysApi();
			$modelinfo_obj=$shapways->modelget($pmodel['pm_shapewaysid']);
			$modelinfo_arr=$this->object_to_array($modelinfo_obj);//模型信息
			
			//$modeldownload_obj=$shapways->modeldownload($pmodel['pm_shapewaysid']);			
			//$modeldownload_arr=$this->object_to_array($modeldownload_obj);
			//var_dump($modeldownload_arr);		
			
			
			//echo "<br>是否可以打印：".$modelinfo_arr['printable']."<br><br>";
			//echo "<br>该模型的打印材料:";
		//var_dump($modelinfo_arr);
			//echo "<br><br><br><br>";
			$show_material=" 模型ID: ".$modelinfo_arr['modelId']."<br>";
			$show_material.=" 是否可以打印: ".$modelinfo_arr['printable']."&nbsp;&nbsp;";
			$show_material.="<a href='__DOC__/user.php/mypublish/getlistinfo/listtype/printer/' target=_blank>打印机参数</a>";
			$show_material.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='__DOC__/user.php/mypublish/getlistinfo/listtype/material/' target=_blank>材料参数</a>";
			$show_material.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='__DOC__/user.php/mypublish/getlistinfo/listtype/price/' target=_blank>pricemodel</a>";
			$show_material.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='__DOC__/user.php/mypublish/getmodeldownload/modelid/".$modelinfo_arr['modelId']."' target=_blank>模型下载信息</a>";
			$show_material.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='__DOC__/user.php/mypublish/getmodelget/modelid/".$modelinfo_arr['modelId']."' target=_blank>模型get</a>";
			$show_material.="<br><br><br>";
			
			$show_material.="<table height=600><tr height=30><td width=80>ID</td><td width=280>材料名称</td><td width=100>打印价格</td><td width=120>是否可以打印</td></tr>";
			foreach($modelinfo_arr['materials'] as $key => $val ){
				$show_material.="<tr height=30>";
				foreach($val as $k =>$v){
					if($k=="materialId"){$show_material.="<td>".$v."</td>";}
					if($k=="name"){$show_material.="<td><a href='__DOC__/user.php/mypublish/getmaterialinfo/materialid/".$val['materialId']."' target=_blank>".$v."</a></td>";}
					if($k=="isPrintable"){$show_material.="<td>".$v."</td>";}
					if($k=="basePrice"){$show_material.="<td>".$v."</td>";}
						//if($k=="")
				}
				$show_material.="</tr>";
			}
			
			$show_material.="</table>";
			$element.="<div class='upleft'></div>";
			$element.="<br><br><br><div class='upright'>重新上传可以打印的3D模型文件:";
			$element.= "<input type='hidden' name='p_id' value='".$pmodel['p_id']."'><br>";
			$element.= "<input type='hidden' name='title' value='".$pmodel['p_id']."'><br>";
			$element.="<input type='text' name='fileurl' id='fileurl' value=''><input type='button' id='insertfile' value='选择文件' />";
			$element.="<br><br><input type='submit' name='button' id='button' value='提 交'>";
			$element.="</div>";
		}
	
		$temp_postfile=$_FILES;
		$temp_a=$_POST['token'];
		//echo "post:".$temp_postfile;
		$show_html="<form action='' method='post' enctype='multipart/form-data'>";
		$show_html.=$show_material;
		$show_html.=$element;
		
		$show_html.="</form>";
		$this->assign('showhtml',$show_html);
		$this->_renderPage ();
	}
	//<--------------------------------------------by zhangzhibin  end >

	function getlistinfo(){//得到打印材料等的list列表
		Vendor('Shapeways.Swapi');//包含扩展shapeways的api
		$listtype=I('listtype',"printer");
		$shapways=new ShapewaysApi();
		echo header ( "Content-Type:text/html; charset=utf-8" );
		if($listtype=="printer"){
			$back_obj=$shapways->printerlist();
			$back_arr=$this->object_to_array($back_obj);
	
			$showtable.="<table>";
			$showtable.="<tr><td>ID</td><td width=80>Title</td>";
			$showtable.="<td width=80>xBoundMin</td><td width=80>xBoundMax</td>";
			$showtable.="<td width=80>yBoundMin</td><td width=80>yBoundMax</td>";
			$showtable.="<td width=80>zBoundMin</td><td width=80>zBoundMax</td></tr>";
			foreach($back_arr['printers'] as $key=>$val){
				//var_dump($val);
				$showtable.= "<tr height=35><td>".$val['printerId']."</td><td>".$val['title']."</td>";
				$showtable.="<td>".$val['xBoundMin']."</td><td>".$val['xBoundMax']."</td>";
				$showtable.="<td>".$val['yBoundMin']."</td><td>".$val['yBoundMax']."</td>";
				$showtable.="<td>".$val['zBoundMin']."</td><td>".$val['zBoundMax']."</td>";
				$showtable.="</tr>";
			}
			$showtable.="<table>";
			$showtable.="<br><br><font color=#ccc>单位：cm</font>";	
		}elseif($listtype=="api"){
			$back_obj=$shapways->apilist();
			$back_arr=$this->object_to_array($back_obj);
		}elseif($listtype=="material"){
			$back_obj=$shapways->materiallist();
			$back_arr=$this->object_to_array($back_obj);
		//	var_dump($back_arr);
			$showtable.="<table>";
			$showtable.="<tr><td width=80>ID</td><td width=80>Title</td>";
			$showtable.="<td width=80>图片</td></tr>";
				foreach($back_arr['materials'] as $key=>$val){
					//var_dump($val);
					$showtable.= "<tr height=35><td>".$val['materialId']."</td><td>".$val['title']."</td>";
					$showtable.="<td><img src='".$val['swatch']."'></td>";
	
					$showtable.="</tr>";
				}
				$showtable.="<table>";
		}elseif($listtype=="price"){
			$back_obj=$shapways->pricemodel();
			$back_arr=$this->object_to_array($back_obj);
			
			$showtable.="<table>";
			$showtable.="<tr><td width=80>ID</td><td width=80>price</td>";
			$showtable.="<td width=80>currency</td></tr>";
			foreach($back_arr['prices'] as $key=>$val){
				//var_dump($val);
				$showtable.= "<tr height=35><td>".$val['materialId']."</td><td>".$val['price']."</td>";
				$showtable.="<td>".$val['currency']."</td>";
			
				$showtable.="</tr>";
			}
			$showtable.="<table>";
			
			//echo $showtable;
			//var_dump($back_arr);
		}
			
		echo $showtable;
		echo "<br><br><br><br>";
	
	}

	
	function getmaterialinfo(){
		Vendor('Shapeways.Swapi');//包含扩展shapeways的api
		$materialId=I('materialid');
		$shapways=new ShapewaysApi();
		$back_obj=$shapways->materialinfo($materialId);
		$back_arr=$this->object_to_array($back_obj);
		echo header ( "Content-Type:text/html; charset=utf-8" );
		echo "材料ID：".$back_arr['materialId'];
		echo "<br><br><br><br>材料样片:<img src='".$back_arr['swatch']."'>";
		//var_dump($back_arr);
	}
	
	function getmodelget(){
		Vendor('Shapeways.Swapi');//包含扩展shapeways的api
		//$modelId=I('modelid');
		$shapways=new ShapewaysApi();
		// $back_obj=$shapways->modelget($modelId);
		$back_obj=$shapways->modelpropget(1);
		$back_arr=$this->object_to_array($back_obj);
		var_dump($back_arr);
	}
	
	function getmodeldownload(){
		Vendor('Shapeways.Swapi');//包含扩展shapeways的api
		$modelId=I('modelid');
		$shapways=new ShapewaysApi();
		$back_obj=$shapways->modeldownload($modelId);
		$back_arr=$this->object_to_array($back_obj);
		var_dump($back_arr);
	}

	

function object_to_array($obj) { //obj to array
	$_arr = is_object($obj) ? get_object_vars($obj) : $obj; 
	foreach ($_arr as $key => $val){ 
		$val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val; 
		$arr[$key] = $val; 
	}
	return $arr; 
}
	
	
}
?>