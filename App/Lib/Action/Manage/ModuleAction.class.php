<?php
/*
 * 
 * $Id: ModuleAction.class.php 1067 2013-12-06 05:07:41Z miaomiao $
 */ 
class ModuleAction extends CommonAction {	
    public function index() {
    	$this->display();
    }
    
    public function ContentList(){
    	$Pnodeid	=I("get.Pnodeid",0,"intval");
    	$nodeid		=I("get.nodeid",0,"intval");
    	$searchtype=I('searchtype',0,'intval');
    	$keywords=I('keywords');
    	$allin=I('allin');
    	$SearchArr=Array($searchtype,$keywords,$allin);
    	//主表字段信息数组 
			$MainNodeArr=M("node")->field("MainTable,MainRelationFieldName,AsField,InnerJoinSql,QueryFiterSql,OrderBySql,LimitBySql,GroupBySql")->where("id=".$nodeid)->find();
			//按钮信息
			$FunctionArr=M("node_modulefunction")->field("FunctionName")->where("NodeId=".$nodeid)->order('FunctionOrder,FunctionName')->select();;
			if($nodeid==79){
				$addurl=U('Access/addContentlist','Pnodeid=14&nodeid=67');
			}else{
				$addurl=U('Module/ContentEdit','Pnodeid='.$Pnodeid.'&nodeid='.$nodeid.'');
			}
			$showtable.= "<input type='hidden' id='url' value=".$addurl.">";
			$sfunction="<input type='button' onClick='AddNew();' value='新 增' title='新增一条新记录(Ctrl+N)'>";				
			$CM=M("node_modulecontentlist");
			$ContentListArr=$CM->field("id,OutputFieldName,FieldType,GetValue,AsField,EchoName,EchoOrder,QueryColumn")->where("NodeId=".$nodeid)->order('EchoOrder')->select();//list对应的数据字段数组
			$NodeMainSql=$this->GetNodeSqlByArr($ContentListArr,$MainNodeArr,$SearchArr);//由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
			$NodeMainSql_count=$this->GetNodeSqlByArr($ContentListArr,$MainNodeArr,$SearchArr,1);
			
			import('ORG.Util.Page');// 导入分页类 -------分页的总记录数
			$C_NodeM=new Model();
			$countselect=$C_NodeM->query($NodeMainSql_count);// 查询满足要求的总记录数
			$count=$countselect[0]['count'];//总记录数
			$Page = new Page($count,16);	// 实例化分页类 传入总记录数和每页显示的记录数
			$show = $Page->show();				// 分页显示输出
			
			$NodeM= M();
			$NodeMainSql.="limit ".$Page->firstRow.",".$Page->listRows.""; //主SQL文件
			$Contentlist = $NodeM->query($NodeMainSql);
			
			$showselect="<form method='post'><select name='searchtype'>";
			foreach($ContentListArr as $ka =>$va){
				if($va['QueryColumn']==1){$showsearch=1;}
				if($va['QueryColumn']){
					if($searchtype==$va['id']){
						$showselect.="<option value='".$va['id']."' selected>".$va['EchoName']."</option>";
					}else{
						$showselect.="<option value='".$va['id']."'>".$va['EchoName']."</option>";
					}
				}
			}
			$showselect.="</select> <input type='text' style='width:220px;height:22px;' name='keywords' value=".$keywords."><input type='hidden' name='nodeid' value='".$nodeid."'>";
			$showselect.=" <input name='allin' type='checkbox' ".$this->checkboxvalue($allin)."> 完全匹配 ";
			$showselect.="<input type='submit' name='submit' value='搜索 ' style='width:80px;height:28px;' /></form>";
				
			if($showsearch==1){$showtable.=$showselect;}
			$showtable.="<table width='100%' border='0' cellspacing='0' cellpadding='0' class='tab'>";
			$showtable.="<thead><tr><td>".$sfunction."</td></tr><tr bgcolor=#E0E0E0>";
			
			//var_dump($ContentListArr);
			foreach($ContentListArr as $ka =>$va){
				$showtable.="<td align='center'>".$va['EchoName']."</td>";
			}
			//$showtable.="<td>操作</td>";
			$showtable.="</thead></tr>";
			
			foreach($Contentlist as $k =>$v){//记录数的循环
				$showtable.="<tr>";
				foreach($ContentListArr as $k2 =>$v2){//每条记录数中字段的循环
					//var_dump($v2);
					if($v2['FieldType']=='LINK'){
						$showtable.="<td align='center'><a href='".__APP__."/".$v2['GetValue'].$v[$MainNodeArr['MainRelationFieldName']]."/Pnodeid/".$Pnodeid."/nodeid/".$nodeid."'>".$v[$v2['AsField']]."</a></td>";
					}elseif($v2['FieldType']=='SERIALIZE'){
						$temparr=unserialize($v[$v2['AsField']]);
						//print_r($temparr);
						$showtable.="<td align='center'>".$temparr."</td>";
					}else{
						$showtable.="<td align='center'>".$v[$v2['AsField']]."</td>";
					}
				}
				$showtable.="</tr>";
			}
			$showtable.="</table>";
			$this->assign('showtable',$showtable);
			$this->assign('page',$show);// 赋值分页输出
			$this->display();
    }
    
    /*
     * 检验checkbox的值并返回
     */
    function checkboxvalue($tvalue){
    	if($tvalue){
    		return "checked=checked";
    	}else{
    		return "";
    	}
    }
    
		/*
		 * @返回Node主SQL语句 
		 * @DATA: $CLArr内容数组 $MainNodeArr主节点模块数组 $idvalue为主键的值
		 *
		 */
    function GetNodeSqlByEdit($CLArr,$NodeArr,$idvalue){ //由$ContentArr和$MainNodeArr获得$NodeMainSql语句
      $sqlfield=$NodeArr['MainRelationFieldName'].",";
    		foreach($CLArr as $k =>$v){
    			$sqlfield.=$v['FieldName'].",";
    		}
	    	$sqlfield=substr($sqlfield,0,-1);
	    	$sqlfield =str_replace("`", '"', $sqlfield);
	    	$NodeSql="select ".$sqlfield." from ".$NodeArr['MainTable']." as ";
	    	$NodeSql.=(isset($NodeArr['AsField']))? $NodeArr['AsField'] : $NodeArr['MainTable'];
	    	//$NodeSql.=" ".$NodeArr['InnerJoinSql'];
	    	$NodeSql.=(isset($MainNodeArr['QueryFiterSql']))? " where ".$NodeArr['QueryFiterSql'] : " where 1=1";//配置表中的查询条件
	    	$NodeSql.=" and ".$NodeArr['AsField'].".".$NodeArr['MainRelationFieldName']."=".$idvalue;
	    	$NodeSql.=(isset($MainNodeArr['OrderBySql']))?" order by ".$NodeArr['OrderBySql'] : " order by ".$NodeArr['MainRelationFieldName'];
	    	$NodeSql.=" limit 0,1";
	    	$NodeSql.=(isset($MainNodeArr['GroupBySql']))?" group by ".$NodeArr['GroupBySql'] : " ";
		   return $NodeSql;
    }
    
    
		function GetNodeSqlByArr($CLArr,$NodeArr,$SArr=0,$sqltype=0){ //由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
			$sqlfield=$NodeArr['AsField'].".".$NodeArr['MainRelationFieldName'].",";
			foreach($CLArr as $k =>$v){
				$tempsql=$v['OutputFieldName'];
				$field=str_replace("`", '"', $tempsql);//转义'为"
				switch ($v['FieldType']){
					case "TEXT":
						$sqlfield.=isset($v['AsField'])?$field." as ".$v['AsField']."," : $field.",";
						break;
					case "RADIO";
						$GetValueArr=explode(";",$v['GetValue']);
						$sqlfield.="case";
						foreach($GetValueArr as $key =>$value){
							$varr=explode("=",$value);
							$cw=" when ".$v['OutputFieldName']."= ".$varr['0']." Then ".$varr['1']." ";
							$cw=str_replace("`", '"', $cw);//转义'为"
							$sqlfield.=$cw;
						}
						$sqlfield.=" end as ".$v['AsField'].",";
						break;
					default:
						$sqlfield.=isset($v['AsField'])?$field." as ".$v['AsField']."," : $field.",";
						break;
				}
			}
			$sqlfield=substr($sqlfield,0,-1);
			$sqlfield =str_replace("`", '"', $sqlfield);
		
			if($sqltype==1){
				$sqlfield=" count(*) as count";
			}
			
			$NodeSql="select ".$sqlfield." from ".$NodeArr['MainTable']." as ";
			$NodeSql.=(isset($NodeArr['AsField']))? $NodeArr['AsField'] : $NodeArr['MainTable'];
			$NodeSql.=" ".$NodeArr['InnerJoinSql'];
			$NodeSql.=(!empty($NodeArr['QueryFiterSql']))? " where ".$NodeArr['QueryFiterSql'] : " where 1=1";//配置表中的查询条件
			if($SArr[0]){
					$FnGvArr=$this->OutputFieldNameByLID($SArr[0]);
					if($FnGvArr['FieldType']=="RADIO"){
						$NodeSql.=" and ".$FnGvArr['OutputFieldName']." = ".$this->getwherefield($SArr[1],$FnGvArr['GetValue']);
					}else{
						if($SArr[2]){
							$NodeSql.=" and ".$FnGvArr['OutputFieldName']." = ".$SArr[1];
						}else{
							$NodeSql.=" and ".$FnGvArr['OutputFieldName']." like '%".$SArr[1]."%'";
						}		
					}
			}
			$NodeSql.=(!empty($NodeArr['OrderBySql']))?" order by ".$NodeArr['OrderBySql'] : " order by ".$NodeArr['MainRelationFieldName'];
			$NodeSql.=(!empty($NodeArr['GroupBySql']))?" group by ".$NodeArr['GroupBySql'] : " ";
			return $NodeSql;
		}
		
		
		/*
		 * 转义RADIO的查询关键字
		 * @data: value为关键字的值  getvalue为转义的格式
		 * 
		 */
		public function getwherefield($value,$getvalue){
			$GvArr=explode(";", $getvalue);
			foreach ($GvArr as $k =>$v){
				$tempArr=explode("=",$v);
				if(is_int(strpos($tempArr[1],$value))){
					$result=$tempArr[0];
				}
			}
			return $result;
		}
		
		
		
		public function OutputFieldNameByLID($LID){
			$FM=M('node_modulecontentlist')->field('OutputFieldName,GetValue,FieldType')->where('id='.$LID)->find();
			return $FM;
//			return $FM['OutputFieldName'];
		}
		
		public function Editprepaid(){
			$up_id=I("up_id",0,"intval");
			$PP=new UserPrepaidModel();
			$orderinfo=$PP->getPrepaidListByup_id($up_id);
			//echo $up_id;
			//<<---------------------------------------------得到订单地址
			$AD			=new UserAddressModel();
			$AdInfo =$AD->getAddressByID($orderinfo['up_uaid']);
			$AIPM = new AreaInfoPickerModel();
			$orderinfo['area_disp'] = $this->getDispArea($AIPM, $AdInfo['ua_province'], $AdInfo['ua_city'], $AdInfo['ua_region']);
			$orderinfo['area_disp'].=" ".$AdInfo['ua_address'];
			//--------------------------------------------------->>得到订单地址
			$PPD=new UserPrepaiddetailsModel();
			$ProductList =$PPD->getByOrderid($orderinfo['up_orderid']);
			
			//var_dump($ProductList);
			$PM		=new PrinterModelModel();
			$PMM	=new PrinterMaterialPickerModel();
			$UF		=new UserFilesModel();
			//var_dump($ProductList);
			
			foreach ($ProductList as $key=>$Product){
				$pm_info=$PM->getPrinterModelByyfid($Product['yf_id']);//模型文件信息数组
				$ProductList[$key]['pm_info']=$pm_info;
			
				$pma_info=$PMM->getItemByID($Product['pma_id']);//模型材料数组颜色类
				$ProductList[$key]['pma_info']=$pma_info;
			
				$pma_info_parent=$PMM->getPartentByID($Product['pma_id']);//模型材料数组大分类
				$ProductList[$key]['pma_info']['pma_info_parent']=$pma_info_parent['pma_name'];//附加到材料数组中
				$args=array(
						"sprice"=>$pma_info['pam_startprice'],
						"uprice"=>$pma_info['pam_unitprice'],
						"volume"=>$pm_info['pm_volume'],
				);
				$ProductList[$key]['amount']=$PM->calcModelPrice($args); //计算价格
				$uf_info=$UF->getUserfilesByyfid($Product['yf_id']);
				$ProductList[$key]['uf_fullname']=$uf_info['uf_fullname'];
				$TotalPrice += $pma_info['pam_unitprice']*$Product['uc_count'];
			}
			
			$ordertable="<table class='tab'>";
			$ordertable.="<tr><td>订单编号:</td><td width=230>".$orderinfo['up_orderid']."</td><td>创建时间:</td><td width=200>".$orderinfo['up_dealdate']."</td>";
			$ordertable.="<td>订单状态:</td><td>".replace_int_vars($orderinfo['up_status'])."</td></tr>";
			$ordertable.="<tr ><td height=30>收件人:</td><td>".$orderinfo['up_addressee']."</td><td>收件地址:</td><td width=330>".$orderinfo['up_address']."</td><td>邮编:</td><td>".$orderinfo['up_zipcode']."</td></tr>";
			$ordertable.="<tr height=30><td>收件人手机:</td><td>".$orderinfo['up_mobile']."</td><td>联系电话:</td><td>".$orderinfo['up_phone']."</td><td>快递费用:</td><td>".$orderinfo['up_efee']."</td></tr>";
			$ordertable.="</table>";
			$ptable="<table class='tab'>";
			$ptable.="<tr><th>ID</th><th width=200 height=30>商品或服务</th><th width=100>打印材料</th><th width=80>单价</th><th width=80>数量</th>";
			$ptable.="<th width=80>合计</th><th width=80>模型状态</th><th width=220>长  * 宽  * 高</th><th width=80>体积</th><th>需要修复</th><th>需要审核状态</th><th>操作</th>";
			$ptable.="</tr>";
			$PMP=new PrinterModelModel();
			
			$pm_status_disp = L('PM_STATUS_DISP');
			$pm_needfix_disp=L('PM_NEEDFIX');
			$pm_needverify_disp=L('PM_NEEDVERIFY');
			//var_dump($ProductList);
			foreach($ProductList as $k =>$v){
				$ptable.="<tr><td>".$v['yf_id']."</td><td height=30><img src='".$v['pm_info']['pm_cover']."'>".$v['uf_fullname']."</td><td>".$v['pma_name']."</td><td>".$v['p_price']."</td><td>".$v['p_count']."</td><td>".$v['p_mount']."</td>";
				$ptable.="<td>". $pm_status_disp[$v['pm_info']['pm_status']]."</td><td>".$v['pm_info']['pm_length']." * " .$v['pm_info']['pm_width']." * ".$v['pm_info']['pm_height']."</td>";
				$ptable.="<td>".$v['pm_info']['pm_volume']."</td><td>".$pm_needfix_disp[$v['pm_info']['pm_needfix']]."</td><td align=center>".$pm_needverify_disp[$v['pm_info']['pm_needverify']]."</td>";
				$ptable.="<td><a href='__APP__/printer_model_audit/audit/id/".$v['yf_id']."'>继续审核</</td>";
				$ptable.="</tr>";
			}
			//var_dump($ProductList);
			$ptable.="</table>";
				
			$this->assign('ordertable',$ordertable);
			$this->assign('ptable',$ptable);
			//var_dump($pplist);
			$this->display();
		}
		
		private function getDispArea($AIPM, $Province, $City, $Region)
		{
			$Result .= $AIPM->getItemNameByID($Province) . ' ';
			$Result .= $AIPM->getItemNameByID($City) . ' ';
			$Result .= $AIPM->getItemNameByID($Region);
			return $Result;
		}
		
		public function ContentEdit(){
			$Pnodeid	=I("get.Pnodeid",0,"intval");
			$nodeid		=I("get.nodeid",0,"intval");
			$selectid	=I("get.id",0,"intval");
			$action		=I("action","0","string");
			$MainNodeArr=$this->getMainNodeArr($nodeid);//主节点信息
		  $EditField	=$this->getEditField($nodeid); 	//需要编辑的字段array
			//var_dump($EditField);
			$NodeMainSql=$this->GetNodeSqlByEdit($EditField,$MainNodeArr,$selectid);//由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
			if($action=="0"){
					//主表字段信息数组
					$from_title=array();
					$from_data=array();
					if($selectid==0){//新增
						foreach($EditField as $key =>$value){
							//echo $value['FieldType'];
							$farr=array(
									"FieldType"				=>$value['FieldType'],
									"FieldName"				=>$value['FieldName'],
									"OutputFieldName"	=>$value['OutputFieldName'],
									"EchoName"				=>$value['EchoName'],
									"GetValue"				=>$value['GetValue'],
									"dvalue"					=>$value['DefaultValue']
							);
							$from_arr		=$this->getFromField($farr);
							$from_title	=array_merge($from_title,$from_arr['title']);
							$from_data	=array_merge($from_data,$from_arr['data']);
						}
					}else{//修改
						$MT=M();
						$DataArr= $MT->query($NodeMainSql);
						foreach($EditField as $key =>$value){
							$farr=array(
									"FieldType"				=>$value['FieldType'],
									"FieldName"				=>$value['FieldName'],
									"OutputFieldName"	=>$value['OutputFieldName'],
									"EchoName"				=>$value['EchoName'],
									"GetValue"				=>$value['GetValue'],
									"dvalue"					=>$DataArr[0][$value['FieldName']]
							);
							if($value['ReadOnly']==1){
								if(strpos($value['OutputFieldName'],",")){
									$farr['dvalue']=$this->getValueFromJoinTable($farr['OutputFieldName'],$farr['dvalue']);
								}
								$farr['ReadOnly']="ReadOnly";
							}else{
								$farr['ReadOnly']="css_text";
							}
							//-------------------如果有HTML富文本编辑器，复制名称到页面变量用于js取值
							if($farr['FieldType']=="HTML"){
								$HtmlName="m_".$farr['FieldName'];
							}else{
								$HtmlName=0;
							}
							$this->assign('HtmlName',$HtmlName);//
							//-------------------如果有HTML富文本编辑器，复制名称到页面变量用于js取值
											
							$from_arr	=$this->getFromField($farr);
					  	$from_title	=array_merge($from_title,$from_arr['title']);
							$from_data	=array_merge($from_data,$from_arr['data']);
						}
					}
				
					$from_cont  = array('control'=>array('con_list'=>array('modi'=>'修改','dele'=>'删除'),'con_parse'=>array('?module=xxx&action=aaa','const')));
						$from_tags  = array('tags'=>array('name'=>'table','attr'=>array('class'=>'list')),
							'tagsrow'=>array('name'=>'tr','rules'=>array('class'=>array('title1','list_self'),'rul'=>0)),
							'tagscol'=>array('name'=>'td','rules'=>array('class'=>array('title1','list_self'),'rul'=>2)),
							'tagstitle'=>array('name'=>'legend','title'=>'修改数据'),
							'tagsform'=>array('attr'=>array('name'=>'myform', 'method'=>'post'),
							'button'=>array('submit','submit','提交','css_submit'),
							'custom'=>'<input name="action" type="hidden" value="save" /><input name="hidden2" type="hidden" value="hidden2" />')
					);
					$FORMA=new MakeTableModel();
					$showfrom = $FORMA->setTitle($from_title)->setName('myAlist1')->setPK('ml_id')->setData($from_data)->setControl($from_cont)
					->setTags($from_tags)->showTableSubmit();
					$this->assign('showfrom',$showfrom);
					$this->display();
			}elseif($action=="save"){//保存记录
				header('Content-Type:application/json; charset=utf-8');
				//exit;
				$getpost=array();
				foreach($EditField as $key =>$value){
					$fvarr['FieldType']	=$value['FieldType'];
					$fvarr['v']		 			=I("post.m_".$value['FieldName']);
					$fvarr['FieldName']	=$value['FieldName'];
					//pre($fvarr);
					$postarr=$this->getFromFieldValue($fvarr,$selectid);
					$getpost=array_merge($getpost,$postarr);
				}
				//var_dump($getpost);
				//echo "<br>";
				foreach($getpost as $key => $value){
					//echo $value."<br>";
					$getpost[$key]=$this->replace_str($value);
					//$getpost[$key]=$value;
				}
				//var_dump($getpost);
				//exit;
				
				
				$UD= M(cutprefix($MainNodeArr['MainTable']));	
				if($selectid==0){//新增
					$add_id=$UD->add($getpost);
					if($add_id){
						$adminlog = $this->addLog(2,"新增数据",$nodeid,$_SESSION['my_info']['aid'],$add_id);//记录后台日志
						$this->error("保存成功", U('/Module/ContentList/Pnodeid/'.$Pnodeid.'/nodeid/'.$nodeid));
					}else{
						$this->error("数据未更新!", U('/Module/ContentList/Pnodeid/'.$Pnodeid.'/nodeid/'.$nodeid));
					}
					$this->display();
				}else{//修改
					if($UD->where($MainNodeArr['MainRelationFieldName']."=".$selectid)->save($getpost)){
						
						$adminlog = $this->addLog(3,"更新数据",$nodeid,$_SESSION['my_info']['aid'],$selectid);//记录后台日志
						$this->error("保存成功", U('/Module/ContentList/Pnodeid/'.$Pnodeid.'/nodeid/'.$nodeid),1);
					}else{
						$this->error("数据未更新!", U('/Module/ContentList/Pnodeid/'.$Pnodeid.'/nodeid/'.$nodeid),1);
					}
					$this->display();
				}
			}
		}
		
	
		public function replace_str($string) {
			$result = preg_replace('/&gt;/','>',$string);
			$result = preg_replace('/&lt;/','<',$result);
			return $result;
		}
		
		/*获取关联表中的对应字段的值
		 * @Data： $OFN:对应的表信息字串(需要关联的表名,关联的查询字段名称,输出的字段名称)
		 * @Data：$value:对应的值
		 */
		public function getValueFromJoinTable($OFN,$value){
			$tarr=explode(",",$OFN);
			$TM=M($tarr[2]);
			$result=M(cutprefix($tarr[0]))->where($tarr[1].'='.$value)->getField($tarr[2]);
			return $result;
		}
		
		/*
		 * 得到密码的MD5值
		 * @data:$passvalue 传入原密码值 $uid 用户id
		 */
		function getMd5ByPassUid($passvalue,$uid){
			$UM = new UsersModel();
			$UserInfo = $UM->getUserByID($uid);
			if($UserInfo === false) { return $this->error('更新失败', U('Admin/Module/ContentList')); }
			if($UserInfo === null) { return $this->error('账户异常，请重新登陆', U('Admin/Module/ContentList')); }
			$NewSaltPass = $this->getSaltPass($passvalue,$UserInfo[$UM->F->Salt]);
			return $NewSaltPass;
		} 
		
		private function getSaltPass($Pass, $Salt) { return md5(md5($Pass).$Salt); }
		/*function getmd(){
			echo md5(md5("111111")."ZAJNS");
		}*/
		
		/*
		 * 得到表单post过来的字段对应值
		 * @data:$fvarr(含字段类型和字段值)
		 * @return:value
		 * @author:zhangzhibin
		 */
		public function getFromFieldValue($fvarr,$sid){
			$result=array();
			$tempvalue=mysql_real_escape_string($fvarr['v']);
			//echo $fvarr['v'];
			if($fvarr['FieldType']=="PASSWORDSALT"){
				if($fvarr['v']==0 || !isset($fvarr['v']) || empty($fvarr['v'])){//密码为空不修改
					
				}else{
					$temparr[$fvarr['FieldName']]	=	$this->getMd5ByPassUid($fvarr['v'],$sid);
					$result= array_merge($result,$temparr);
				}
			}elseif($fvarr['FieldType']=="DISPLAY"){//如果是显示类型，不更新数据
				
			}else{
				$temparr[$fvarr['FieldName']]	=	$fvarr['v'];
				$result= array_merge($result,$temparr);
			}
			//print_r($result);
			return $result;
		}

		
		/*
		 * 得到表单数组(向表单中填充)
		 * @data:$farr中包含：filedtype 字段类型      ofname 字段输出的控件名称  echoname字段显示名称   value对应的数据值
		 * @author:zhangzhibin
		 * @return:array
		 */
		public function getFromField($farr){
			$FieldType				=$farr['FieldType'];
			$OutputFieldName	=$farr['OutputFieldName'];
			$EchoName					=$farr['EchoName'];
			$GetValue					=$farr['GetValue'];
			$dvalue						=$farr['dvalue'];
			$ReadOnly					=$farr['ReadOnly'];
			$FieldName				=$farr['FieldName'];
			
			switch ($FieldType){
				case "DISPLAY":
					$fromtitle[$FieldName]	=array('input','',$EchoName,"DISPLAY");
					$fromdata[$FieldName]		=($dvalue=="0")?"":$dvalue;
					break;
				case "TEXT":
					$fromtitle[$FieldName]	=array('input','text',$EchoName,'css_text');
					$fromdata[$FieldName]		=($dvalue=="0")?"":$dvalue;
					break;
				case "IMAGE":
					$fromtitle[$FieldName]	=array('input',$FieldType,$EchoName,'css_textimage');
					$fromdata[$FieldName]		=($dvalue=="0")?"":$dvalue;
					break;
				case "PASSWORDSALT":
					$fromtitle[$FieldName]	=array('input','password',$EchoName,'PASSWORDSALT');
					$fromdata[$FieldName]		="";
					break;
				case "RADIO":
					$fromtitle[$FieldName]	=array('input','radio',array('name'=>$EchoName,'vlist'=>$this->getRadioInfo($GetValue,1)));
					$fromdata[$FieldName]		=($dvalue=="0")?"":$dvalue;
					break;
				case "SELECT":
					$fromtitle[$FieldName]	=array('select','',array('name'=>$EchoName,'vlist'=>$this->getSelectInfo($GetValue,$dvalue)),'css_select');
					$fromdata[$FieldName]		=($dvalue=="0")?"":$dvalue;
					break;
				case "TEXTAREA":
					$fromtitle[$FieldName]	=array('textarea','',$EchoName,'css_textarea');
					$fromdata[$FieldName]		=($dvalue=="0")?"":$dvalue;
					break;
				case "HTML":
					$fromtitle[$FieldName]	=array('input',$FieldType,$EchoName,'css_textarea');
					$fromdata[$FieldName]		=($dvalue=="0")?"":$dvalue;
					break;
				default:
					break;
			}
			$result['title']	=$fromtitle;
			$result['data']		=$fromdata;
			return $result;
		}
		/*
		 * 获得Radio的信息数组
		 * @data:$v传入的表达式串 $type返回的类型：0为值 1为显示内容
		 * @author:zhangzhibin
		 */
		public function getRadioInfo($v,$type){
			$varr=explode(";",$v);
			$result=array();
			foreach($varr as $k =>$val){
				$tempv=explode("=",$val);
				if($type==0){
					$result[]=$tempv[0];
				}else{
					$result[$tempv[0]]=$tempv[1];
				}
			}
			return $result;
		}
		
		/*
		 * 获得Select的信息数组
		* @data:$v传入的sql语句 
		* @author:zhangzhibin
		*/
		public function getSelectInfo($v){
			$ST=M();
			//msubstr($str, $start=0, $length, $charset="utf-8″, $suffix=true)
			$sqlstatus=$this->getSelectSqlStatus($v);
			$sql=$this->changeSql($v);
			if($sqlstatus==1){//如果sql语句中有pid
				$SArr=$ST->query($v);
				$result=$this->getMenuTree($SArr);
				foreach ($result as $k =>$value){
					switch($value['level']){
						case 0:
							$resultlist.="根 节 点";
							break;
						case 1:
							$resultlist[$value['id']]="|—".$value['title'];
							break;
						case 2:
							$resultlist[$value['id']]="&nbsp;&nbsp;├".$value['title'];
							break;
						case 3:
							$resultlist[$value['id']]="&nbsp;&nbsp;&nbsp;&nbsp;┖".$value['title'];
							break;
						default:
							break;
					}
				}
			}else{
				$SArr=$ST->query($sql);
				foreach($SArr as $ka =>$va){
					$resultlist[$va['id']]=$va['title'];
				}
			}
		//	print_r($resultlist);
				
			return $resultlist;
		}
		
		
		function getSelectSqlStatus($sql){//获取selectsql是否带父类ID（pid）)
			$len1=strpos($sql,"from")-7;
			$result_value=substr($sql,6,$len1);
			$result_value=substr_count($result_value,",");
			if($result_value==2){
				$result_status=1;
			}else{
				$result_status=0;
			}
			return $result_status;
		}
		
		function changeSql($sql){//转换sql语句
			$len1=strpos($sql,"from")-7;
			$result_value=substr($sql,6,$len1);
			$sql_end=substr($sql,strpos($sql,"from"));
			$arr=explode(",",$result_value);
			$result_sql="select ".$arr[0]." as id,".$arr[1]." as title ".$sql_end;
			return $result_sql;
			//$result_value=substr_count($result_value,",");
			
		}
					
		function getMenuTree($arrCat, $parent_id = 0, $level = 0)
		{
			static  $arrTree = array(); //使用static代替global
			if( empty($arrCat)) return FALSE;
			$level++;
			foreach($arrCat as $key => $value)
			{
				if($value['pid' ] == $parent_id)
				{
					$value[ 'level'] = $level;
					$arrTree[] = $value;
					unset($arrCat[$key]); //注销当前节点数据，减少已无用的遍历
					$this->getMenuTree($arrCat, $value[ 'id'], $level);
				}
			}
			return $arrTree;
		}
		
		
		/*数据层级化，
		 * @Data:$list需要层级话的数组，包含id，pid
		 * 返回数组
		 */
		function findChildArr($list,$p_id){    
			$r = array();
			foreach($list as $id=>$item){
				if($item['pid'] == $p_id) {
					$length = count($r);
					$r[$length] = $item;
					if($t = $this->findChildArr($list, $item['id'])){
						$r[$length]['children'] = $t;
					}
				}
			}
			return $r;
		}

		
		
		
		/*
		 * 获得节点表的主信息数组
		 * @data:$nodeid节点id
		 * @return:节点信息数组
		 * @author:zhangzhibin 
		 */
		public function getMainNodeArr($nodeid){
			return M("node")->field("MainTable,MainRelationFieldName,AsField,InnerJoinSql,QueryFiterSql,OrderBySql,LimitBySql,GroupBySql")->where("id=".$nodeid)->find();
		}
		/*
		 * 获得编辑的字段信息数组
		 * @data:nodeid节点ID
		 * @author：zhangzhibin
		 * @return:数据集
		 */
		public function getEditField($nodeid){
			$MC=M("node_modulecontent");
			$result=$MC->where("NodeId=".$nodeid)->order('EchoOrder')->select();
			return $result;
		}
		
		public function maketable(){
			$_tmp=new MakeTableModel();
			$_datam = array('0'=>array('ml_id'=>'1','name'=>'aasdfldf','count'=>'11','date'=>'02-12'),'1'=>array('ml_id'=>'2','name'=>'aasdfldf','count'=>'11','date'=>'02-12'));
			$_data  = array('ml_id'=>'1','name'=>'0','count'=>'2','date'=>'02-12', 'hao'=>'1|2', 'texts'=>'sdfsdfasdfasdfasdfsadf');
			$_data_blank = array();
				
			$_cont  = array('control'=>array('con_list'=>array('modi'=>'修改','dele'=>'删除'),'con_parse'=>array('/index.php?module=xxx&action=aaa','const')));
				
			$_titl  = array('name'=>array('input','radio',array('name'=>'性别','vlist'=>array('男','女')),'css1'),'count'=>array('select', '', array('name'=>'地区','vlist'=>array('中国','美国','小日本')), 'css2'),'date'=>array('input','text','日期', 'css3'), 'texts'=>array('textarea','','说明','css4'), 'hao'=>array('input','checkbox',array('name'=>'爱好','vlist'=>array('读书','写字')),'css5'));
			$_titlt = array('name'=>'名称','count'=>'数量','date'=>'日期');
			$_titls = array('name'=>array('名称','checkbox'),'count'=>'数量','date'=>'日期');
				
			$_tags  = array('tags'=>array('name'=>'fieldset','attr'=>array('class'=>'list')),
					'tagsrow'=>array('name'=>'div','rules'=>array('class'=>array('title1','list_self'),'rul'=>0)),
					'tagscol'=>array('name'=>'label'),
					'tagstitle'=>array('name'=>'legend','title'=>'修改数据'),
					'tagsform'=>array('attr'=>array('action'=>'/index.php','name'=>'myform'),
					'button'=>array('submit','submit','提交'),
					'custom'=>'<input name="hidden1" type="hidden" value="hidden1" /><input name="hidden2" type="hidden" value="hidden2" />')
			);
			$_tagb = array('tags'=>array('name'=>'fieldset','attr'=>array('class'=>'list')),
					'tagsrow'=>array('name'=>'div','rules'=>array('class'=>array('title1','list_self'),'rul'=>0)),
					'tagscol'=>array('name'=>'label'),
					'tagstitle'=>array('name'=>'legend','title'=>'添加数据'),
					'tagsform'=>array('attr'=>array('action'=>'/index.php','name'=>'myform'),'button'=>array('submit','submit','提交'))
			);
			$_tagl = array('tags'=>array('name'=>'div','attr'=>array('class'=>'list')),
					'tagsrow'=>array('name'=>'ul','rules'=>array('class'=>array('title1','list_self'),'rul'=>0)),
					'tagscol'=>array('name'=>'li'),
					'tagstitle'=>array('name'=>'h1','title'=>'添加数据'),
					'tagsform'=>array('attr'=>array('action'=>'/index.php','name'=>'myform'),'button'=>array('submit','submit','提交'))
			);
			$_tagm = array('tags'=>array('name'=>'table','attr'=>array('class'=>'list')),
					'tagsrow'=>array('name'=>'tr','rules'=>array('class'=>array('title1','list_self'),'rul'=>0)),
					'tagscol'=>array('name'=>'td'),
					'tagstitle'=>array('name'=>'span','title'=>'修改数据'),
					'tagsform'=>array('attr'=>array('action'=>'/index.php','name'=>'myform'),'button'=>array(array('button1','button','全选'),array('button2','button','反选'),array('button3','button','删除')))
			);
			$_tagt = array('tags'=>array('name'=>'table','attr'=>array('class'=>'list')),
					'tagsrow'=>array('name'=>'tr','rules'=>array('class'=>array('title1','list_self'),'rul'=>0)),
					'tagscol'=>array('name'=>'td','rules'=>array('class'=>array('title1','list_self'),'rul'=>0)),
					'tagstitle'=>array('name'=>'span','title'=>'修改数据'),
					'tagsform'=>array('attr'=>array('action'=>'/index.php','name'=>'myform'))
			);
				
			//$_tmp= $_tmp->get_instance('MakeTable');
			$_tmps = $_tmp->setTitle($_titl)
			->setName('myAlist1')
			->setPK('ml_id')
			->setData($_data)
			->setControl($_cont)
			->setTags($_tags)
			->showTableSubmit();
			
			$_tmpe = $_tmp->setTags($_tags)->showTableSubmit();
			$_tmpb = $_tmp->setData($_data_blank)->setTags($_tagb)->showTableSubmit(); 							//设置空的添加
			$_tmpl = $_tmp->setTags($_tagl)->showTableSubmit();
			$_tmpm = $_tmp->setData($_datam)->setTitle($_titls)->setTags($_tagm)->showTableList(); //list带全选的
			$_tmpt = $_tmp->setData($_datam)->setTitle($_titlt)->setTags($_tagt)->showTableList();	// list 不带全选的
				
				
			//echo $_tmps;  // table 标签实现的修改数据提交表单
			//echo $_tmpe;  // DIV 标签实现的修改数据提交表单
			//echo $_tmpb;  // DIV 标签实现的添加数据提交表单
			//echo $_tmpl;  // DIV UL 标签实现的添加数据提交表单
			//echo $_tmpm;  // table 标签实现的带checkbox 带按钮的数据显示列表
			//echo $_tmpt;  // table 标签实现的不带checkbox的数据显示
			
			$this->assign("tmps",$_tmps);
			//$this->assign("tmpb",$_tmpb);
			//$this->assign("tmpl",$_tmpl);
			//$this->assign("tmpm",$_tmpm);
			//$this->assign("tmpt",$_tmpt);
			$this->display();	
		}
		
		
		
		
		
		
}






