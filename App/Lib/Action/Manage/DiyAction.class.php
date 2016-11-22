<?php
/**
 * 后台diy订单
 * @author zhangzhibin 
 * 2014-11-11
 */
class DiyAction extends CommonAction {
	
	/*
	 * 非DIY指定分类
	 */
	public function AddNDiyToProduct() {
		$pid = I ( 'id', 0, 'intval' );
		
		$PM = new ProductModel ();
		$PCM = new CatesModel ();
		
		$Product = $PM->find ( $pid );
		
		// 6是什么意思
		$pc_type = 6;
		
		$cate_list_checkbox = $PCM->getCateCheckbox ( 0, $Product ['p_cate'], true );
		
		if ($_POST) {
			//
			$p_cate_arr = I ( 'cate', 0, 'intval' );
			$p_cate_string = arrayTransToStr ( $p_cate_arr, ',' );
			$PM->{$PM->F->Cate} = $p_cate_string;
			$PM->save();
			
			$PCI = new ProductCateIndexModel ();
			
			$PCI->addCate ( $p_cate_arr, $pid, $pc_type );
			
			redirect ( __APP__ . "/empty/ContentList/Pnodeid/114/nodeid/105/" );
		}
		
		$this->assign ( 'id', $pid );
		$this->assign ( 'cate_list_checkbox', $cate_list_checkbox );
		$this->display ( 'adddiytoproduct' );
	}
	
	/*
	 * diy产品发布到product中，用于前台列表显示和搜索
	 */
	public function AddDiyToProduct() {
		$id = I ( 'id', 0, 'intval' );
		
		$DCM = new DiyCateModel ();
		$diyinfo = $DCM->getDiyCateByCid ( $id );
		$PM = new ProductModel ();
		$ProdDiyinfo = $PM->getProductByDiyCateCid ( $id ); // 根据p_diy_cate_cid得到product信息数组		
		$pid = $ProdDiyinfo ? $ProdDiyinfo ['p_id'] : 0;
		
		$p_cate = $pid ? $ProdDiyinfo ['p_cate'] : 0;
		$PCM = new CatesModel ();
		$pc_type = 6;
		$cate_list_checkbox = $PCM->getCateCheckbox ( 0, $p_cate, true );
		
		if ($_POST) {
			$pc_type = 6;
			$id = I ( 'id', 0, 'intval' );
			$p_cate_arr = I ( 'cate', 0, 'intval' );
			$p_cate_string = arrayTransToStr ( $p_cate_arr, ',' );
			$p_data ['p_name'] = $diyinfo ['cate_name'];
			$p_data ['p_creater'] = $diyinfo ['u_id'];
			//$p_data ['p_cover'] = $diyinfo ['cate_icon_list'];
			$p_data ['p_price'] = $diyinfo ['startprice']; // 起始价
			$p_data ['p_createdate'] = date ( "Y-m-d G:i:s", time () );
			$p_data ['p_createtime'] = time ();
			$p_data ['p_lastupdate'] = date ( "Y-m-d G:i:s", time () );
			$p_data ['p_lastupdatetime'] = time ();
			$p_data ['p_producttype'] = 7; // 产品类型 1为数字模型 2为实物商品(3d打印机) 3为打印件
			                               // 4为DIY产品
			$p_data ['p_diy_cate_cid'] = $diyinfo ['cid'];
			$p_data ['p_slabel'] = $diyinfo ['p_slabel'];
			$p_data ['p_cate'] = $p_cate_string;
			if ($ProdDiyinfo ['p_producttype'] == 5) { // 如果type为5，是垂直类商品，不用新增和更新product数据
				$pid = $id;
			} else {
				if ($ProdDiyinfo) { // 如果有记录,修改更新tdf_product表
					if ($PM->where ( "p_diy_cate_cid=" . $p_data ['p_diy_cate_cid'] . "" )->save ( $p_data )) {
						$pm_info = $ProdDiyinfo;
						$pid = $ProdDiyinfo ['p_id'];
					}
				} else { // 如果没有，则新增
					$pid = $PM->add ( $p_data );
				}
			}
			$PCI = new ProductCateIndexModel ();
			$PCI->addCate($p_cate_arr,$pid,$pc_type);//更新分类的索引表(tdf_product_cate_index);
			redirect ( WEBROOT_URL . "/manage.php/empty/ContentList/Pnodeid/114/nodeid/105/" );
		}
		$this->assign ( 'id', $id );
		$this->assign ( 'cate_list_checkbox', $cate_list_checkbox );
		$this->display ();
	}
	
	/*
	 * 设置DIY商品的封面图片
	 * by zhangzhibin
	 * 2014-12-23
	 */
	public function addDiyProdCover(){
		$p_diy_cate_cid=I('get.id',0,'intval');
	//	echo $p_diy_cate_cid;
		$PM=new ProductModel();
		$prodInfo=$PM->getProductByDiyCateCid($p_diy_cate_cid);
		//var_dump($prodInfo);
		if($prodInfo){
			$pid=$prodInfo['p_id'];
		}else{
			$pid=0;
		}
		$this->assign('p_diy_cate_cid',$p_diy_cate_cid);
		
		$this->assign('pid',$pid);
		$this->display();
	}
	
	/*
	 * 设置DIY商品的封面图片
	* by zhangzhibin
	* 2014-12-23
	*/
	public function addDiyProdTag(){
		$p_diy_cate_cid=I('get.id',0,'intval');
		//	echo $p_diy_cate_cid;
		$PM=new ProductModel();
		$prodInfo=$PM->getProductByDiyCateCid($p_diy_cate_cid);
		//var_dump($prodInfo);
		if($prodInfo){
			$pid=$prodInfo['p_id'];
		}else{
			$pid=0;
		}
		$this->assign('p_diy_cate_cid',$p_diy_cate_cid);
		$this->assign('pid',$pid);
		$this->display();
	}

    /*设置DIY商品的前台显示
     *by zhangzhibin
     *2015-05-27 */
	public function setDiyProductShow(){
        $p_diy_cate_cid=I('get.id',0,'intval');
        $PM=new ProductModel();
        $prodInfo=$PM->getProductByDiyCateCid($p_diy_cate_cid);
        if($prodInfo){
            $pid=$prodInfo['p_id'];
        }else{
            $pid=0;
        }
        if ($_POST) {
            $p_id=I('p_id',0,intval);
            $fieldData['p_slabel']=I('p_slabel','0','string');
           //var_dump($p_id);
            $proResult=M('product')->where( "p_id='".$p_id."'" )->setField ($fieldData);
            //var_dump($proResult);
            redirect( WEBROOT_URL . "/manage.php/empty/ContentList/Pnodeid/114/nodeid/105/" );
        }
        //var_dump($prodInfo);
        $this->assign('p_slabel',$prodInfo['p_slabel']);
        $this->assign('p_diy_cate_cid',$p_diy_cate_cid);
        $this->assign('pid',$pid);
        $this->display();
    }


	/**设置Android幻灯片前台显示
	 * create guolixun 2016-09-26
	 */
	public function setAndroidShow(){
		$id = I('id');
		$type=I('type');
		if($type==1){
			//隐藏
			M('help_doc')->where( "id = $id" )->save(array('status'=>1));
		}else{
			//发布
			M('help_doc')->where( "id = $id" )->save(array('status'=>'0'));
		}
		redirect( WEBROOT_URL . "/manage.php/empty/ContentList/Pnodeid/134/nodeid/199/" );

	}

	/**
	 * 订单详情
	 */
	public function PrepaidDetail() {
	    //<<--------------------短信通知 edit by lifangyuan 需添加user表字段sentsms
	    $uparr = M('user_prepaid')->getByUp_id($this->_post('up_id'));
	    $u_sentsms = M('users')->getFieldByU_id($uparr['up_uid'],'u_sentsms');
	    if($this->_post('done_process') == "6" && $this->_post('sms') == "sent"){
	        if($u_sentsms == 1){
	            $to = $uparr['up_mobile'];	            
	            $datas[0] = $uparr['up_orderid'];//订单号
	            $express_com = "";
	            switch ($this->_post('express_com')){
	                case "shunfeng";
	                $express_com = "顺丰快递";
	                break;
	                case "shentong";
	                $express_com = "申通快递";
	                break;
	                case "debangwuliu";
	                $express_com = "德邦物流";
	                break;
	                case "huitongkuaidi";
	                $express_com = "百世汇通";
	                break;
	                case "ems";
	                $express_com = "EMS";
	                break;
	                case "tiandihuayu";
	                $express_com = "华宇物流";
	                break;
	                case "tiantian";
	                $express_com = "天天快递";
	                break;
	                case "yuantong";
	                $express_com = "圆通快递";
	                break;
	                default;
	                $express_com = "顺丰快递";
	                break;                    
	            }
	            $datas[1] = $express_com;//快递公司
	            $datas[2] = $this->_post('express_number');//快递单号
                $datas[3] = $this->_post('express_number');//快递单号
	            $tempid = "102939";
	            smssent($to,$datas,$tempid);
	        }
	    }
	    if($this->_post('done_process') == "2" && $this->_post('sms') == "sent"){
	        if($u_sentsms == 1){
	            $to = $uparr['up_mobile'];
	            $datas[0] = $uparr['up_orderid'];//订单号
	            $tempid = "102940";
	            smssent($to,$datas,$tempid);
	        }
	         
	    }
	    //<<-------------------------------------------------短信通知end 
	    
	    
		$up_id = I ( "id", 0, "intval" );
        $workOrder=$this->getWorkOrder($up_id);

        $UPP = new UserPrepaidProcessModel ();
		if ($_POST) {
			$done_process = intval ( $_POST ['done_process'] );
			$up_id = I ( 'up_id', 0, 'intval' );
			$process_result = $UPP->updateProcessByUpidPro ( $up_id, $_POST );
			$prepaidUpdateStatus ["up_done_status"] = $this->getProcessText ( $done_process );
			$updateStatus = M ( "user_prepaid" )->where ( "up_id=" . $up_id )->setField ( $prepaidUpdateStatus ); // 更新订单表中的状态，冗余字段，便于列表显示
			if ($process_result) {
			    /*if($done_process == 6 && $){
			    }*/
				redirect ( __SELF__ );
			}
		}
		$sqla = "select * from tdf_user_prepaid as TUP ";
		$sqla .= "Left Join tdf_users as TU On TU.u_id=TUP.up_uid ";
		$sqla .= "where TUP.up_id=" . $up_id . "";
		$prepaidInfo = M ()->query ( $sqla );
			
		$prepaidInfo [0] ['total_amount'] = $prepaidInfo [0] ['up_amount'] + $prepaidInfo [0] ['up_efee']; // 订单总价
		$ProductList = $this->getProductByUpid ( $up_id );


		//var_dump($up_id);
		$PFM = new ProductFileModel ();
		$prepaidInfo [0] ['isdown'] = 1;
        //创建人
        if($prepaidInfo [0]['u_email']){
            $prepaidInfo [0]['showcreater']=$prepaidInfo [0]['u_email'];
        }else{
            $prepaidInfo [0]['showcreater']=$prepaidInfo [0]['u_mob_no'];
        }

        $SRM=new SalesReportModel();
		foreach ( $ProductList as $key => $value ) {
			if ($product_file = $PFM->getFileByProduct ( $value ['p_id'] )) {
				$ProductList [$key] ['file'] = $product_file [0] ['pf_path'] . $product_file [0] ['pf_filename'];
			} else {
				$ProductList [$key] ['file'] = null;
				$prepaidInfo [0] ['isdown'] = 0; // 如果有一个产品没有生成模型,订单不完整
			}
			$ProductList [$key] ['cover_64'] = str_replace ( '/o/', '/s/220_220_', $value ['cover'] );
            if($prepaidInfo [0] ['up_status'] ==1){
                $ProductList [$key] ['work_order']=$SRM->getWorkOrder($up_id,$value ['p_id']);
            }else{
                $ProductList [$key] ['work_order']=0;
            }
			$ProductList[$key]['p_producttype']=$ProductList [$key] ['uc_producttype'];
		}
		$prepaidInfo [0] ['up_done_status_text'] = $this->getUpDoneStatusText ( $prepaidInfo [0] ['up_done_status'] );
		
		$process = $UPP->getProcessByUpid ( $up_id );
		
		foreach ( $process as $key => $value ) {
			$process [$key] ['done_remark'] = str_replace ( array (
					"\r\n",
					"\r",
					"\n" 
			), "", $value ['done_remark'] );
		}
		$currentProcess = $UPP->getCurrentProcess ( $process );
		$order_process = $this->order_process ( $prepaidInfo [0], $currentProcess, $process );
		// $order_process_user=$this->order_process_user($prepaidInfo[0],$currentProcess,$process);
		$express = M ( "user_prepaid_express" )->where ( "up_id=" . $up_id )->find ();
		
		// 查询订单对应的快递信息---------------------<<<<<<<<<

        if($express){//订单发货时间
            if(strtotime($express['express_time'])){
                $prepaidInfo [0] ['show_express_time']=date('Y-m-d',strtotime($express['express_time']));
            }else{
                $prepaidInfo [0] ['show_express_time']='<font color="#ccc">未填写</font>';
            }
        }else{
            $prepaidInfo [0] ['show_express_time']='未发货';
        }

		$UPM = new UserPrepaidModel ();
		$PrepaidExpress = $UPM->getPrepaidExpress ( $up_id );
		if ($PrepaidExpress [0]) {
			$this->assign ( "isexpress", $PrepaidExpress [0] );
		}
		// 查询订单对应的快递信息--------------------->>>>>>>>>

//如果有发票信息，查询发票信息----------start
    $UserPaybill=M('user_paybill')->where("up_orderid='".$prepaidInfo [0] ['up_orderid']."'")->find();
    //var_dump($prepaidInfo [0] ['up_orderid']);
    if($UserPaybill){
        $prepaidInfo [0] ['show_paybill']=$this->showPaybill($UserPaybill);
    }else{
        $prepaidInfo [0] ['show_paybill']="无";
    }
//如果有发票信息，查询发票信息----------end

//-----------------支付方式------------
        $PTM=new PayTypeModel();
        $paytypeInfo=$PTM->get_paytypeByPtid($prepaidInfo[0]['up_paytype']);
        $prepaidInfo [0] ['show_paytype']=  $paytypeInfo[0]['payname'];
//-----------------支付方式------------

	//----------------如果有使用优惠券，查询优惠券的记录-----------------start
	if($prepaidInfo[0]['up_amount_coupon']){
		$log_coupon=M('log_coupon')->field("log_eccode,log_ecamount")->where("log_orderid='".$prepaidInfo[0]['up_orderid']."'")->find();
		//var_dump(M('log_coupon')->getlastsql());
		//var_dump($log_coupon);
		$this->assign("log_coupon",$log_coupon);
	}
	//----------------如果有使用优惠券，查询优惠券的记录------------------end
		$process_status = L ( 'process' );
		$express_com = L ( 'express_com' );
        $this->assign("admin_user",$_SESSION['email']);
		$this->assign ( "express", $express );
		$this->assign ( "express_com", $express_com );
		$this->assign ( "order_process", $order_process );
		$this->assign ( "process", $process );
		$this->assign ( "process_status", $process_status );
		$this->assign ( "upDoneStatusArr", L ( "up_done_status" ) );
		$this->assign ( "prepaidInfo", $prepaidInfo [0] );
		$this->assign ( "productList", $ProductList );
		$this->assign ( "expressIframe", $PrepaidExpress [1] );
		//------------------------------------->
		//发送短信物流通知
		
		//<<-------------------------------------------------end
		//<<--------------------------------------发票申请 edit lifangyuan
		$arr = M('user_paybill')->query("select * from tdf_user_paybill where up_orderid = '{$prepaidInfo[0]['up_orderid']}' order by asktime desc limit 1");
		
		if($arr[0]['status'] == '1'){
		    $this->assign('pbstatus',"<a href='__URL__/applypb/id/{$prepaidInfo[0]['up_orderid']}'>申请发票</a>");
		}elseif($arr[0]['status'] == '2'){
		    $this->assign('pbstatus',"已经提交财务");
		}else{
		    $this->assign('pbstatus',"<a href='__URL__/goappendpb/id/{$prepaidInfo[0]['up_orderid']}'>追加发票</a>");
		}
		//<<----------------------
		/*-----------订单价格修改记录查询----------------------Start*/
		$LS = M('log_smorder');
		$logOrderInfo = $LS->where("sm_oid='".$prepaidInfo[0]['up_orderid']."'")->select();
		$this->assign('logOrderInfo',$logOrderInfo);

		/*-----------订单价格修改记录查询----------------------End*/

		$this->display ();
		
	}
	
	
	public function processdel() {
		$processid = I ( "processID", "0", "intval" );
		$upid = I ( "up_id", "0", "intval" );
		if ($processid) {
			$UPPM = new UserPrepaidProcessModel ();
			if ($UPPM->delProcessByID ( $processid )) {
				redirect ( "./PrepaidDetail/id/" . $upid . "" );
			}
		}
	}
	
	/*
	 * 订单后台显示状态图 @param $up_status 订单支付状态 @param $processArr 订单处理流程数组
	 */
	private function order_process($prepaidInfo, $currentProcess, $processArr) { //
		if ($prepaidInfo ['up_status'] == 1) { // 订单已经成功支付
			$showtable = "<table class='tab'><tr><td>处理时间</td><td>处理信息</td><td>操作人</td><td>备注</td><td>操作</td>";
			$showtable .= $this->getTableProcess ( $currentProcess, $processArr, $prepaidInfo ['up_dealdate'] );
			$showtable .= "</table>";
		} else {
			/*
			 * $showtable="<div class='orderpro'><ul><li>用户看到的状态</li><li>提交订单 ->
			 * 等待付款 </li> <li>-> 付款成功 -> 打印制作中 -> 已发快递 -> 完成 </li>";
			 * $showtable.=""; $showtable.="</ul></div>";
			 */
		}
		return $showtable;
	}
	private function getTableProcessUser() {
	}
	private function getTableProcess($currentProcess, $processArr, $orderdate) { // 输出显示处理状态
		$processConArr = L ( 'process' );
		$processCount = count ( $processArr );
		// var_dump($processConArr);
		// var_dump($currentProcess);
		$showtable .= "<tr><td>" . $orderdate . "</td><td>" . $processConArr [1] . "</td><td>系统</td><td>--</td><td>" . $this->showProAction ( 1 ) . "</td>";
		// if(!$currentProcess){$showtable.="<tr><td></td><td>".$processConArr[2]."</td><td></td><td>".$this->showProAction(2)."</td>";}
		foreach ( $processArr as $key => $value ) {
			$showtable .= "<tr>";
			$showtable .= "<td>" . $value ['done_time'] . "</td>";
			$showtable .= "<td>" . $processConArr [$value ['done_process']] . '</td>'; // 处理信息
			$showtable .= "<td>" . $value ['done_name'] . "</td>"; // 操作人
			$showtable .= "<td>" . $value ['done_remark'] . "</td>"; // 备注
			$showtable .= "<td>" . $this->showProAction ( $value ['done_process'] ); // 操作
			
			if ($key == $processCount - 1) {
				$showtable .= $this->showProDel ( $value ['id'] );
			}
			$showtable .= "</td>";
			
			$showtable .= "<tr>";
		}
		return $showtable;
	}
	
	/*
	 * 返回显示状态表格 @param Array [0] 状态值 [1] 操作人
	 */
	private function showstatus($processArr) {
		switch ($processArr [0]) {
			case 0 :
				$result = "<td>1.</td><td>您提交了订单,系统正在生成3D文件</td><td>系统</td>";
				break;
			case 1 :
				$result = "<td>2.</td><td>您的订单文件正在检查,下一步送往3D打印中心制作</td><td>" . $processArr [1] . "</td>";
		}
	}
	private function showProDetail($processArrCu) { // 显示已经发生的操作数据
	                                                // var_dump($processArrCu);
		return "<font color='#999999'><br>" . $processArrCu ['done_usermail'] . "<br>" . $processArrCu ['done_time'] . "<br>" . $processArrCu ['done_remark'] . "<br>" . $processArrCu ['express_number'] . "</font>";
	}
	private function showProDel($pid) {
		if ($pid) {
			$result = "<a href='#' onclick='funProcessDel($pid);'>撤销</a>";
		}
		return $result;
	}
	private function showProAction($processAction) { // 显示处理操作按钮
		if ($processAction == 7 or $processAction > 7) {
			$result = " ";
		} else {
			$result = "<a href='#' class='btn_in2' onclick='opendiv($processAction);'>下一步</a>";
		}
		return $result;
	}
	private function getUpDoneStatusText($svalue) { // 根据订单的处理状态返回处理状态显示文字
		$up_done_status_arr = L ( "up_done_status" );
		return $up_done_status_arr [$svalue];
	}
	private function getProcessText($svalue) { // 根据订单的处理状态返回处理状态显示文字
		$up_done_status_arr = L ( "process" );
		return $up_done_status_arr [$svalue];
	}
	private function getProductArr($up_id) { // 根据订单的upid返回订单产品数组
		$PPD = new UserPrepaidDetailModel ();
		$ProductInfo = $PPD->getPrepaidDetailByUpid ( $up_id );
		$ProductArr = unserialize ( $ProductInfo ['up_product_info'] );
		return $ProductArr;
	}
	
	/*
	 * 返回订单快照详情 param $up_id INT 订单up_id
	 */
	public function getProductByUpid($up_id) {
		$ProductArr = $this->getProductArr ( $up_id );

		//dump($ProductArr);
		$DPM = new DiyPrepaidModel ();
		$UCM=new UserCartModel();
		foreach ( $ProductArr as $key => $Product ) {
			switch ($Product ['uc_producttype']) {
				case 1 :
					$pid = $Product ['p_id'];
					$product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
					$product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
					$product [$key] ['p_name'] = $Product ['p_name']; // 名称
					$product [$key] ['p_price'] = $Product ['p_price']; // 单价
					$product [$key] ['p_count'] = $Product ['p_count']; // 数量
					$product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
					$product [$key] ['cover'] = $Product ['p_cover']; // 截图
					$product [$key] ['p_id'] = $Product ['p_id']; // 对应tdf_product产品p_id
					$product [$key] ['cid'] = $Product ['p_cate_3']; // 对应tdf_product产品p_id
					$product [$key] ['p_propid_spec_desc'] = ''; // 对应tdf_product产品p_id
					break;
				case 2 :
					$pid = $Product ['p_id'];
					$product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
					$product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
					$product [$key] ['p_name'] = $Product ['p_name']; // 名称
					$product [$key] ['p_price'] = $Product ['p_price']; // 单价
					$product [$key] ['p_count'] = $Product ['p_count']; // 数量
					$product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
					$product [$key] ['cover'] = $Product ['p_cover']; // 截图
					$product [$key] ['p_id'] = $Product ['p_id']; // 对应tdf_product产品p_id
					$product [$key] ['cid'] = $Product ['p_cate_3']; // 对应tdf_product产品p_id
					$product [$key] ['p_propid_spec_desc'] = ''; // 对应tdf_product产品p_id
					break;
				case 4 : // DIY产品
					$pid = $Product ['p_id'];
					$upid = $up_id;
					$udinfo [$key] = $DPM->getUdinfoByUpid ( $upid, $pid );

					$product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
					$product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
					$product [$key] ['p_name']      = $udinfo [$key]['Textvalue']; // 名称
					$product [$key] ['p_price']     = $udinfo [$key]['price']; // 单价
					$product [$key] ['p_count']     = $udinfo [$key]['p_count']; // 数量
					$product [$key] ['totle_price'] = $udinfo [$key]['p_count'] * $product [$key]['p_price']; // 小计价格
					$product [$key] ['cover']       = $udinfo [$key]['p_cover']; // 截图
					$product [$key] ['p_diy_id']    = $Product ['p_diy_id']; // 对应tdf_product产品p_id
					$product [$key] ['p_id']        = $udinfo [$key]['p_id']; // 对应tdf_product产品p_id
					$product [$key] ['cid']         = $udinfo [$key]['p_cate_4']; // 对应tdf_product产品p_id
					$value['diy_unit_info']         = $udinfo [$key]['diy_unit_info'];
					$value['p_cate_4']              = $udinfo [$key]['p_cate_4'];
					$value['p_intro']               = $Product ['p_intro'];
					$product [$key] ['p_propid_spec_desc'] = $UCM->getUserCartDiyByProduct($value); // 产品详情
					break;
				case 5 :
					$pid = $Product ['p_id'];
					$product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
					$product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
					$product [$key] ['p_name'] = $Product ['p_name']; // 名称
					$product [$key] ['p_price'] = $Product ['p_price']; // 单价
					$product [$key] ['p_count'] = $Product ['p_count']; // 数量
					$product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
					$product [$key] ['cover'] = $Product ['p_cover']; // 截图
					$product [$key] ['p_id'] = $Product ['p_id']; // 对应tdf_product产品p_id
					$product [$key] ['cid'] = $Product ['p_cate_3']; // 对应tdf_product产品p_id
					$product [$key] ['p_propid_spec_desc'] =ProductPropValModel::parseCombinePropVals ( $Product ['p_propid_spec'], ' -- '); // 对应tdf_product产品p_id
					break;
				default :
					$pid = $Product ['p_id'];
					$product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
					$product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
					$product [$key] ['p_name'] = $Product ['p_name']; // 名称
					$product [$key] ['p_price'] = $Product ['p_price']; // 单价
					$product [$key] ['p_count'] = $Product ['p_count']; // 数量
					$product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
					$product [$key] ['cover'] = $Product ['p_cover']; // 截图
					$product [$key] ['p_id'] = $Product ['p_id']; // 对应tdf_product产品p_id
					$product [$key] ['cid'] = $Product ['p_cate_3']; // 对应tdf_product产品p_id
					$product [$key] ['p_propid_spec_desc'] =$Product ['p_name']; // 名称
						//ProductPropValModel::parseCombinePropVals ( $Product ['p_propid_spec'], ' -- '); // 对应tdf_product产品p_id
					break;
			}
		}
		
		return $product;
	}
	 
	/**
	 * 首页
	 */
	public function Product() { // 后台产品显示详情页面，iframe框架
		$pid        = I ( "pid", 0, "intval" );
		$cid        = I ( "cid", 0, "intval" );
		$diyid      = I ( "diyid", 0, "intval" );
		$producttype= I ( "producttype", 0, "intval" );
		$upid       = I ( "upid", 0, "intval" );
		$DCM    = new DiyCateModel();
		$diyCate    = $DCM->getDiyCateByCid($cid);
		$cate_type  = $diyCate['cate_type'];
		$this->assign ( "cate_type", $cate_type );
		$this->assign ( "diyid", $diyid );
		$this->assign ( "upid", $upid );
		$this->assign ( "pid", $pid );
		$this->assign ( "cid", $cid );
		$this->assign ( "producttype", $producttype );
		$this->display ();
	}
	

	
	/*
	 * 下载订单
	 */
	public function downPrepaid() {
		$upid = I ( 'upid', 0, 'intval' );
		$actiontype=I('actiontype',0,'intval');//获得操作类型
		$ProductArr = $this->getProductArr ( $upid );
		// ----------------------------------打印材料数组 ^

		$sql = "select TPM.pma_id,TPM.pma_name as TPM_name,TPMP.pma_name as TPMP_name,TPM.pma_unitprice,TPM.pma_density,TPM.pma_startprice,TPM.pma_diy_formula_s,TPM.pma_diy_formula_b from tdf_printer_material as TPM ";
		$sql .= "Left Join tdf_printer_material as TPMP ON TPMP.pma_id=TPM.pma_parentid ";
		$sql .= "where TPM.pma_type=1 order by TPM.pma_weight ASC ";
		$mcate = M ( "printer_material" )->query ( $sql ); // 打印材料数组，必须
		                                                   // ----------------------------------打印材料数组V		
		$DNM=new DiyNecklaceModel();//项链表模型
        $DDM=new DiyDiamondModel();//宝石表模型
        $DPM=new DiyPendantModel();//吊坠表模型
		foreach ( $mcate as $keyM => $valueM ){
			$materialArr [$valueM ['pma_id']] = $valueM ['TPM_name'];
		}//材料数组
		foreach ( $ProductArr as $k => $v ) {
			$kk=1;
			$productLog .= "\r\n\r\n商品" . $kk . "\r\n";		
			$ProductLogArr [$k] ['product_count'] 	= $v ['p_count'] ;//数量
			$ProductLogArr [$k] ['product_price'] 	= $v ['p_price'] ;//单价
			$ProductLogArr [$k] ['product_cover'] 	= $v ['p_cover'] ;//图片
			$ProductLogArr [$k] ['uc_producttype_name'] =  show_product_type ( $v['uc_producttype'] );//商品类型
			$ProductLogArr [$k] ['product_cover64'] = str_replace ( '/o/', '/s/64_64_', $v['p_cover'] );//64图片
			$ProductLogArr [$k] ['uc_producttype'] 	= $v ['uc_producttype'] ;//图片
			if($v['uc_producttype']==4){ //如果是DIY的商品
				$udinfo = unserialize ( $v ['diy_unit_info'] );
				$DC = M ( 'diy_cate' )->where ( "cid=" . $v ['p_cate_4'] )->find (); // diy产品类型
				$DU = M ( 'diy_unit' )->where ( 'cid=' . $v ['p_cate_4'] . ' and ishidden=0' )->order ( 'sort' )->select (); // 选择tdf_diy_unit
				$ProductLogArr [$k] ['product_name']	= $DC ['cate_name'];
				$ProductLogArr [$k] ['商品类型'] = $DC ['cate_name'];
				$productLog .= "DIY ID:" . $DC ['cid'] . "\r\n";
				$productLog .= "商品名称:" . $DC ['cate_name'] . "\r\n";
				$productLog .= "商品数量:" . $v ['p_count'] . "\r\n";
				$product_type="";
				if($DC['cate_type']==5){ //如果是简笔画 直接取p_intro的产品介绍
					$product_type=$v['p_intro'];
				}else{
					if($ProductArr [$k]['p_cate_4']!=1){
						foreach ( $DU as $keyN => $valueN ) {
							if($valueN ['unit_name'] == "Textvalue"){//输入的主体字符
								$ProductLogArr [$k] [$valueN ['unit_showname']] = $udinfo [$valueN ['id']];
								$productLog .= $valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']] . "\r\n";
								$product_type.=$valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']]."; " ;
							}elseif($valueN ['unit_name'] == "Material") {
								$ProductLogArr [$k] [$valueN ['unit_showname']] = $materialArr [$udinfo [$valueN ['id']]];
								$ProductLogArr [$k] ['product_m'] = $materialArr [$udinfo [$valueN ['id']]];
								$productLog .= "材料:" . $materialArr [$udinfo [$valueN ['id']]] . "\r\n";
								$product_type.=$valueN ['unit_showname'].":".$materialArr [$udinfo [$valueN ['id']]]."; "; //属性加材质
							}elseif($valueN ['unit_name'] == "Chaintype"){
								$ProductLogArr [$k] [$valueN ['unit_showname']] = $DNM->getNecklaceExplainByID($udinfo [$valueN ['id']]);
								$productLog .= $valueN ['unit_showname'] . ":" .$DNM->getNecklaceExplainByID($udinfo [$valueN ['id']]) . "\r\n";
								$product_type.= $valueN ['unit_showname'] . ":" .$DNM->getNecklaceExplainByID($udinfo [$valueN ['id']])."; "; //属性加材质
							}elseif($valueN ['unit_name'] == "Gendertype"){
								$ProductLogArr [$k] [$valueN ['unit_showname']] = $DNM->getSelectValue($udinfo [$valueN ['id']],$valueN ['id']);
								$productLog .= $valueN ['unit_showname'] . ":" .$DNM->getSelectValue($udinfo [$valueN ['id']],$valueN ['id']) . "\r\n";
								$product_type.= $valueN ['unit_showname'] . ":" .$DNM->getSelectValue($udinfo [$valueN ['id']],$valueN ['id'])."; ";
							}else{
								if($valueN ['fieldtype'] == "DIAMOND") {
									$ProductLogArr [$k] [$valueN ['unit_showname']] = $DDM->getDimondValue($udinfo [$valueN ['id']], $valueN['unit_showname']);
									$productLog .= $valueN ['unit_showname'] . ":" . $DDM->getDimondValue($udinfo [$valueN ['id']], $valueN['unit_showname']) . "\r\n";
									$product_type .= $DDM->getDimondValue($udinfo [$valueN ['id']], $valueN['unit_showname']);
								}elseif($valueN ['fieldtype'] == "PENDANT"){
									$ProductLogArr [$k] [$valueN ['unit_showname']] = $DPM->getPendantValue($udinfo [$valueN ['id']],$valueN ['id']);
									$productLog .= $valueN ['unit_showname'] . ":" .$DPM->getPendantValue($udinfo [$valueN ['id']],$valueN ['id']) . "\r\n";
									$product_type.= $valueN ['unit_showname'] . ":" .$DPM->getPendantValue($udinfo [$valueN ['id']],$valueN ['id'])."; ";
								}else{
									$ProductLogArr [$k] [$valueN ['unit_showname']] = $udinfo [$valueN ['id']];
									$productLog .= $valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']] . "\r\n";
									$product_type .= $valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']] . "; ";
								}
							}
						}
					}
				}
				$ProductLogArr[$k]['product_type'] = $product_type;
			}elseif($v['uc_producttype']==5){//如果是垂直类商品
				$productLog .= "商品ID:" . $v ['p_belongpid'] . "\r\n";
				$productLog .= "商品名称:" . $v ['p_name'] . "\r\n";
				$productLog .= "商品数量:" . $v ['p_count'] . "\r\n";
				$productLog .= "商品属性:" . ProductPropValModel::parseCombinePropVals ( $v ['p_propid_spec'], ' -- '). "\r\n";
				$ProductLogArr [$k]['product_name']	= $v ['p_name'];
				$ProductLogArr [$k]['product_type']= ProductPropValModel::parseCombinePropVals ( $v ['p_propid_spec'], ' -- ');
			}
			$kk = $k + 1; //商品数量加1
		}
		$UP = new UserPrepaidModel ();
		$upinfo = $UP->getPrepaidListByUpid ( $upid );
		foreach ( $upinfo [0] as $upKey => $upValue ) { // 订单信息
			switch ($upKey) {
				case "up_id" :
					$log = "处理单号:" . $upValue;
					break;
				case "up_dealdate" :
					$log = "时间:" . $upValue;
					break;
				case "up_amount" :
					$log = "支付金额:" . $upValue;
					break;
				case "up_status" :
					$log = "支付结果:" . replace_int_vars ( $upValue );
					break;
				case "up_addressee" :
					$log = "收货人:" . $upValue;
					break;
				case "up_address" :
					$log = "地址:" . $upValue;
					break;
				case "up_mobile" :
					$log = "联系电话:" . $upValue;
					break;
				default :
					$log = "";
			}
			$resultLog .= $log . "\r\n";
		}
		$logContent = $resultLog . "\r\n"; // 回车
		$logContent .= $productLog;
		
		//---------------订单发票信息--start
		$billSQL="select TUPB.asktime,TUPB.billcompany,TUPB.billtype,TUPB.content from tdf_user_paybill as TUPB ";
		$billSQL.="Left Join tdf_user_prepaid as TUPP On TUPP.up_orderid=TUPB.up_orderid ";
		$billSQL.="where TUPP.up_id=".$upid."";
		$billinfo=M()->query($billSQL);
		if($billinfo){
			$billis=1;
		}else{
			$billis=0;
		}
		$billtype=L('billtype');
		$billcontent=L('billcontent');
		$billinfo[0]['billtypename']	=$billtype[$billinfo[0]['billtype']];
		$billinfo[0]['billcontent']		=$billcontent[$billinfo[0]['content']];
		if($billinfo[0]['billtype']==1){$billinfo[0]['billcompany']='个人';}
				
		$this->assign("billis",$billis);
		$this->assign("billinfo",$billinfo[0]);
		
		//---------------订单发票信息--end
		
		//----------------如果有使用优惠券，查询优惠券的记录-----------------start
		if($upinfo[0]['up_amount_coupon']){
			$log_coupon=M('log_coupon')->field("log_eccode,log_ecamount")->where("log_orderid='".$upinfo[0]['up_orderid']."'")->find();
			//var_dump(M('log_coupon')->getlastsql());
			//var_dump($log_coupon);
			$this->assign("log_coupon",$log_coupon);
		}
		//----------------如果有使用优惠券，查询优惠券的记录------------------end


		if(!$actiontype){//操作为0是下载订单
			if (IS_WIN) {
				$fpath = ".\\upload\\more\\prepaid\\" . $fpath . $upinfo [0] ['up_id'] . "\\"; // windows
			} else {
				$fpath = BASE_PATH . "/upload/more/prepaid/" . $upinfo [0] ['up_id'] . "/"; // linux
			}
			$copyFpath = "./upload/more/prepaid/" . $upinfo [0] ['up_id'] . "/";
			$zipPath = "./upload/more/prepaid/";
			// mkdir($fpath);//建立以订单号命名的文件夹
			mkdir ( $fpath, 0777 );
			// mkdir("/www/3dcity/upload/more/prepaid/1085");
			unlink ( $fpath . $upinfo [0] ['up_id'] . '.txt' );
			$fp = fopen ( $fpath . $upinfo [0] ['up_id'] . '.txt', 'a' );
			fwrite ( $fp, $logContent );
			fclose ( $fp );
			$PFM = new ProductFileModel ();
			foreach ( $ProductArr as $key => $value ) {
				$product_file = $PFM->getFileByProduct ( $value ['p_id'] );
				copy ( "." . $product_file [0] ['pf_path'] . $product_file [0] ['pf_filename'], $copyFpath . $upinfo [0] ['up_id'] . "_" . $value ['p_id'] . ".stl" );
				// copy(".".$product_file[0]['pf_path'].$product_file[0]['pf_filename'],$copyFpath);
			}
			$this->zip2 ( $copyFpath, $copyFpath, $zipPath . $upinfo [0] ['up_id'] . '.zip' );
			$downurl = WEBROOT_PATH . "/upload/more/prepaid/" . $upinfo [0] ['up_id'] . '.zip';
			header ( "Location: " . $downurl );
		}else{//显示打印配送清单
			
			//var_dump($upinfo);
			$UPEM=new UserPrepaidExpressModel();
			$ExpressInfo=$UPEM->getExpressByUpid($upid);

			//var_dump($upinfo[0]);
			if($ExpressInfo){
				if($ExpressInfo['express_number']){
					$ExpressInfo['express']=1;
				}else{
					$ExpressInfo['express']=0;
				}
			}else{
				$ExpressInfo['express']=0;
			}
			//var_dump($ProductLogArr);
			//var_dump($product_type);
            //var_dump($ProductLogArr);
			$this->assign("productlist",$ProductLogArr);
			$this->assign ( "expressinfo", $ExpressInfo ); //快递信息
			$this->assign('upinfo',$upinfo[0]);	//收货人信息
			$this->display();
		}
	}
	
	/*
	 * 压缩为zip文件
	 */
	private function zip2($inzip, $folder_path, $zipfilename) {
		import ( 'ORG.PclZip' );
		if (file_exists_case ( $zipfilename )) {
			$delresult = unlink ( $zipfilename );
		} // 如果文件存在,删除此文件,避免重复压缩
		$archive = new PclZip ( $zipfilename );
		$v_list = $archive->add ( $inzip, PCLZIP_OPT_REMOVE_PATH, $folder_path );
		if ($v_list == 0) {
			$logtxt .= $archive->errorInfo ( true ); // 记录日志
			                                         // die("Error :
			                                         // ".$archive->errorInfo(true));
			unset ( $archive );
		} else {
			$logtxt .= $zipfilename . " 生成;"; // 记录日志
		}
		unset ( $archive );
		echo $logtxt;
		return $logtxt;
	}
	//申请发票
	public function applypb($id){
	    if($this->pbif($id)){
	        $this->success("申请成功");
	    }
	    else{
	        $this->error("请求错误");
	    }
	    //提交开票
	    //更新发票状态字段
	}
	//追加发票
	public function goappendpb($id=""){
	    if($this->isPost()){
	
	        $UP = M('user_paybill');
	        $data['up_orderid'] = $this->_post('up_orderid');
	        $data['u_id'] = $this->_post('u_id');
	        $data['asktime'] = get_now();
	        $data['billtype'] = $this->_post('billtype');
	        $data['status'] = '1';
	        if($this->_post('billtype') == 2){
	            $data['billcompany'] = $this->_post('billcompany');
	        }
	        if($UP->add($data) != false){
	            $this->applypb($this->_post('up_orderid'));
	        }
	
	    }
	    else{
	        $arr = M('user_prepaid')->getByUp_orderid($id);
	        $this->assign('up_orderid',$arr['up_orderid']);
	        $this->assign('uid',$arr['up_uid']);
	        $this->display();
	    }
	
	}
	//发票接口
	private function pbif($id){
	    $sql = "select t1.id,t1.billtype,t1.billcompany,t2.up_amount,t2.up_orderbacktime,t2.up_addressee from tdf_user_paybill t1 left join tdf_user_prepaid t2 on t1.up_orderid=t2.up_orderid where t1.up_orderid='{$id}' and t1.status='1' order by asktime desc limit 1;";
	
	    $arr = M('user_paybill')->query($sql);
	    $ar = $arr[0];
	
	    /*
	     * 如果是多个商品，应更改$KBNR = "开票内容1#2#3" $DJ='1#2#3' $ SL ='1#1#1' 可供以后扩展
	    */
	    //开票日期
	    $RQ = $ar['up_orderbacktime'];
	    //发票类型 0普通发票1专用发票
	    $FPLX = 0;
	    //请求来源 03DCITY1RP360
	    $PNAME = 0;
	    //开票人类型  0个人1公司
	    $KPR = $ar['billtype'] == 1?0:1;
	    //开票人名称
	    $KHMC = $ar['billtype'] == 1?$ar['up_addressee']:$ar['billcompany'];
	    //开票内容 0自主研发软件1外购软件2硬件3服务费
	    $CPLX = 3;
	    //开票内容  服务费#大彩电#大冰箱
	    $KBNR = "服务费";
	    //单价 单价1#单价2#单价3
	    $DJ = $ar['up_amount']."";
	    //订单编号
	    $DDBH = $id;
	    //数量 1#1#1
	    $SL = "1";
	    if($FPLX == 1){//可以供以后扩展专用发票
	        //纳税人登记号
	        $NSRDJH = "";
	        //地址
	        $DZ = "";
	        //电话
	        $DH = "";
	        //开户行
	        $KHH = "";
	        //银行账号
	        $YHZH = "";
	    }
	    else{
	        $NSRDJH = "";
	        $DZ = "";
	        $DH = "";
	        $KHH = "";
	        $YHZH = "";
	    }
	    //加密部分
	    $TIME = get_now();
	    $SECRETCODE = md5($TIME."GDI+2015");
	
	
	    $url = "RQ=$RQ&FPLX=$FPLX&PNAME=$PNAME&KPR=$KPR&KHMC=$KHMC&CPLX=3&NSRDJH=$NSRDJH&DZ=$DZ&DH=$DH&KHH=$KHH&YHZH=$YHZH&DDBH=$DDBH&TIME=$TIME&SECRETCODE=$SECRETCODE&SL=$SL&KBNR=$KBNR&DJ=$DJ";
	    try {
	        $client = new SoapClient("http://140.207.154.14:9000/bpm/YZSoft/WebService/MHWTWebService.asmx?wsdl",array('encoding'=>'UTF-8'));
	        $res = $client->CreateMHWT_KTPSQD_Process(array('json'=>$url));
	        $output = $res->CreateMHWT_KTPSQD_ProcessResult;
	        //添加请求记录
	        $LP = M('log_paybill');
	        $data['lp_orderid'] = $id;
	        $data['lp_applystr'] = $url;
	        $data['lp_return'] = $output;
	        $data['lp_operateid'] = session('authId');
	        $LP->add($data);
	        if($output == "Success 200"){
	            //更新paybill状态
	            $data['status'] = 2;
	            if(M('user_paybill')->where("id = {$ar['id']}")->save($data)){
	                return true;
	            }
	            else{
	                return false;
	            }
	        }
	        else{
	            return false;
	        }
	    } catch (SOAPFault $e) {
	        return false;
	    }
	
	}

    //获取工单数组，up_id 订单处理单号
    public function getWorkOrder($up_id){
        $workOrder = M('user_prepaid')->field('up_work_order')->where('up_id='.$up_id)->find();
        $workOrderArr=unserialize($workOrder['up_work_order']);
        if($workOrderArr){
            return $workOrderArr;
        }else{
            return false;
        }
    }

    //保存工厂单号
    public function saveworkorder(){
        $up_id=I('up_id',0,'intval');
        $pid=I('pid',0,'intval');
        $workorder=I('workorder',0,'string');
        $SRM=new SalesReportModel();
        $result=$SRM->updateWorkOrder($up_id,$pid,$workorder);
        redirect ( "./PrepaidDetail/id/" . $up_id . "" );
    }

    //修改付款金额
    public function savemodifyorder(){
        $up_id=I('up_id',0,'intval');
        $up_amount=I('up_amount');
		$ou_text=I('ou_text');
        if(!$up_amount){
            $this->error('未输入金额！');
        }
        $UPM=new UserPrepaidModel();
        $prepaidInfo=$UPM->getPrepaidListByUpid($up_id);
        $UM=new UsersModel();
        $userInfo=$UM->getUserByID($prepaidInfo[0]['up_uid']);
        /*if($userInfo['u_email'] =='wow730@gmail.com' || $userInfo['u_email'] =='pengcuihua@gdi.com.cn'){
           $modifyResult=$UPM->updateOrderAmount($up_id,$up_amount);
            redirect ( "./PrepaidDetail/id/" . $up_id . "" );
        }else{
            $this->error('Sorry,不能修改！');
        }*/
		//解除指定用户修改价格的权限
		if($userInfo['u_email'] =='wow730@gmail.com' || $userInfo['u_email'] =='shenqi@gdi.com.cn' || $userInfo['u_email'] =='nxbin@163.com'){
			$modifyResult=$UPM->updateOrderAmount($up_id,$up_amount);
			//写入修改订单价格日志表
			session_start();
			$data=array(
				'sm_uid'=>$_SESSION['my_info']['aid'],
				'sm_uname'=>$_SESSION['my_info']['email'],
				'sm_oid'=>$prepaidInfo[0]['up_orderid'],
				'sm_preamount'=>$prepaidInfo[0]['up_amount'],
				'sm_amount'=>$up_amount,
				'sm_text'=>$ou_text
			);
			$LS = M('log_smorder');
			$res = $LS->add($data);
			//写入文件
			//$message = date('Y-m-d H:i:s',time()).':--修改成功;--操作人ID:'.$userInfo['u_id'].';--操作人手机号:'.$userInfo['u_mob_no'].'--原订单号信息:'.$prepaidInfo[0]['up_orderid'].';--操作金额:'.$up_amount;
			//Log::write($message,'info','','savemodifyorder.log');
			if($modifyResult){
				redirect ( "./PrepaidDetail/id/" . $up_id . "" );
			}else{
				$this->error('Sorry,不能修改！');
			}
		}else{
			$this->error('Sorry,不能修改！');
		}

        echo $up_amount;
    }

    //后台订单转移到用户
    public function savemodifyorderuser(){
        $up_id=I('up_id',0,'intval');
        $up_creater=I('up_creater');
        $up_user=I('up_user');
        if(!$up_user){
            $this->error('未输入用户信息！');
        }
        if($up_creater=="shenqi@gdi.com.cn" || $up_creater=="wow730@gmail.com" || $up_creater=="nxbin@163.com"){
            $userType=$this->userNoType($up_user);
            $UPM=new UserPrepaidModel();

            if($UPM->changeUpCreaterByUserNo($up_id,$up_user,$userType)){
				//添加操作日志
				$data = array(
					'ou_uname'=>$up_creater,
					'ou_oid'=>$up_id
				);
				$LOU = M('log_smorderuser');
				$LOU ->add($data);
                redirect ( "./PrepaidDetail/id/" . $up_id . "" );
            }else{
                $this->error('订单转移未成功！！！');
            }
        }else{
            $this->error('订单转移未成功！！！');
        }
    }


    //判断用户是mail、手机号或用户ID
    public function userNoType($userNo){
       if(reg_mail($userNo)){//用户为mail 返回1
            $result=1;
       }elseif(is_numeric($userNo)){
           if(strlen($userNo)==11){
               $result=2;//用户为手机号 返回2
           }else{
               $result=3;//用户为ID 返回3
           }
       }
        return $result;
    }



    //解析订单发票
    public function showPaybill($paybillArr){
        if($paybillArr['billtype']==1){
            $result="个人 ";
        }else{
            $result="公司 ";
            $result.=$paybillArr['billcompany'];
        }
        return $result;
    }

}
?>