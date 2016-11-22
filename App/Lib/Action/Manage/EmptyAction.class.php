<?php

/*
 * 
 * $Id: ModuleAction.class.php 1159 2013-12-24 09:22:13Z zhangzhibin $
 */

class EmptyAction extends CommonAction {
    public function index() {

        $this->display("Empty:index");
    }

    public function ContentUrl() {//转向特定的URl
        $M = I('M', '0', 'string');
        $Pnodeid = I("get.Pnodeid", 0, "intval");
        $nodeid = I("get.nodeid", 0, "intval");
        var_dump($M);

    }

    /*
     * 设置DIY商品的封面图片
    * by zhangzhibin
    * 2014-12-23
    */
    public function addProdImg() {

        $pid = I('get.id', 0, 'intval');
        $this->assign('pid', $pid);
        $this->display();
    }


    public function ContentList() {
        echo header("Content-type:text/html;charset=utf-8");
        $nowPage =   !empty($_GET['p'])?intval($_GET['p']):1;
        $TP         = new ProductModel();
        $publickey  = C('PUBLIC_KEY');
        $Pnodeid    = I("get.Pnodeid", 0, "intval");
        $nodeid     = I("get.nodeid", 0, "intval");
        $searchtype = I('searchtype', 0, 'intval');
        $keywords   = I('keywords', '', 'string');
        $timeArr['searchTimeStart']     = I('searchTimeStart', '2014-12-25', 'string');
        $timeArr['searchTimeEnd']       = I('searchTimeEnd', get_now('Y-m-d'), 'string');
        $priceArr['searchPriceStart']   = I('searchPriceStart', 0, 'intval');
        $priceArr['searchPriceEnd']     = I('searchPriceEnd', 0, 'intval');
        $allin     = I('allin', 0, 'intval');
        $SearchArr = Array($searchtype, $keywords, $allin, $timeArr, $priceArr);
        //var_dump($SearchArr);
        $PNodeArr = M("node")->field("name")->where("DelSign<>1 and id=" . $Pnodeid)->find();
        //主表字段信息数组
        $MainNodeArr = M("node")->field("name,MainTable,MainRelationFieldName,AsField,InnerJoinSql,QueryFiterSql,OrderBySql,LimitBySql,GroupBySql,ListHaveEdit,ListHaveDel,title,ListHaveCopy,ListHaveCopyToContent,ListHaveDelSign,ListHaveDelSignRecover")->where("id=" . $nodeid)->find();

        $CM = M("node_modulecontentlist");
        $ContentListArr = $CM->field("id,OutputFieldName,FieldType,GetValue,AsField,EchoName,EchoOrder,QueryColumn")->where("NodeId=" . $nodeid . " and CancelSign=0")->order('EchoOrder')->select();//list对应的数据字段数组

        $NodeMainSql = $this->GetNodeSqlByArr($ContentListArr, $MainNodeArr, $SearchArr);//由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
//        echo "<pre/>";
//        print_r($NodeMainSql);
//        exit;
        $NodeMainSql_count = $this->GetNodeSqlByArr($ContentListArr, $MainNodeArr, $SearchArr, 1);

        //按钮信息
        $FunctionArr = M("node_modulefunction")->field("FunctionName,FunctionApp")->where("NodeId=" . $nodeid)->order('FunctionOrder,FunctionName')->select();
        //var_dump($FunctionArr);

        if ($nodeid == 79) {
            $addurl = U('Access/addContentlist', 'Pnodeid=14&nodeid=67');
        } else {
            $addurl = U($PNodeArr['name'] . '/ContentEdit', 'Pnodeid=' . $Pnodeid . '&nodeid=' . $nodeid . '');
        }
        $showtable .= "<input type='hidden' id='url' value=" . $addurl . ">";
        $sfunction  = "<input type='button' onClick='AddNew();' value='新 增' title='新增一条新记录'></td>";

        $listString = $this->getListStringByArr($ContentListArr); //获得字段表头字符串
        $getDataString = base64_encode($NodeMainSql) . '-' . $listString . '-' . $MainNodeArr['title'];
        $NodeMainSql_pubencode = urlencode($getDataString);

        if ($FunctionArr) {
            foreach ($FunctionArr as $fkey => $fvalue) {
                $sfunction .= "<td><input type='button' onClick='" . $fvalue['FunctionApp'] . "' value='" . $fvalue['FunctionName'] . "' title='导出EXCEL格式数据'> ";
            }
        }
        // $sfunction.="<td><input type='button' onClick='doExcel();' value='导出数据' title='导出EXCEL格式数据'> ";
        $doexcelurl = U('empty/doexcel', 'ssq=' . $NodeMainSql_pubencode . '');
        $sfunction .= "<input type='hidden' id='doexcel' value=" . $doexcelurl . ">";

        import('ORG.Util.Page');// 导入分页类 -------分页的总记录数
        $C_NodeM        = new Model();
        $countselect    = $C_NodeM->query($NodeMainSql_count);// 查询满足要求的总记录数
        $count          = $countselect[0]['count'];//总记录数
        $Page           = new Page($count, 50);    // 实例化分页类 传入总记录数和每页显示的记录数
        $show           = $Page->show();                // 分页显示输出

        $NodeM = M();
        $NodeMainSql .= "limit " . $Page->firstRow . "," . $Page->listRows . ""; //主SQL文件
        $Contentlist = $NodeM->query($NodeMainSql);
        //echo($NodeMainSql);
        $showselect = "<table><tr><td><form method='post'><select name='searchtype' id='searchtype'>";
        // var_dump($ContentListArr);
        foreach ($ContentListArr as $ka => $va) {
            if ($va['QueryColumn'] == 1) {
                $showsearch = 1;
                if ($va['FieldType'] == 'TIME') {
                    $showSearchTime = 1;
                }
                if ($va['FieldType'] == 'PRICE') {
                    $showSearchPrice = 1;
                }
            }
            //var_dump($va);
            if ($va['QueryColumn']) {
                if ($searchtype == $va['id']) {
                    $showselect .= "<option value='" . $va['id'] . "' selected>" . $va['EchoName'] . "</option>";
                } else {
                    $showselect .= "<option value='" . $va['id'] . "'>" . $va['EchoName'] . "</option>";
                }
            }
        }
        $showselect .= "</select></td> <td><div id='divSearchKeywords'><input type='text' style='width:220px;height:22px;' name='keywords' value='" . $keywords . "'><input type='hidden' name='nodeid' value='" . $nodeid . "'></div></td>";
        //$showselect.=" <input name='allin' type='checkbox' ".$this->checkboxvalue($allin)."> 完全匹配 ";
        if ($showSearchTime == 1) {
            $showselect .= " <td><span id='divSearchTime' style='display: none'><input type='text' style='width:80px;height:22px;' name='searchTimeStart' id='searchTimeStart' value=" . $timeArr['searchTimeStart'] . "> -- <input type='text' name='searchTimeEnd' style='width:80px;height:22px;' id='searchTimeEnd' value=" . $timeArr['searchTimeEnd'] . "></span></td>";
            $jsdate[0] = "searchTimeStart";
            $jsdate[1] = "searchTimeEnd";
            $this->assign('jsdate', $jsdate);
        }
        if ($showSearchPrice == 1) {
            $showselect .= "<td><span id='divSearchPrice' style='display:none'><input type='text' style='width:50px;height:22px;' name='searchPriceStart' id='searchPriceStart' value=" . $priceArr['searchPriceStart'] . "> -- <input type='text' name='searchPriceEnd' style='width:50px;height:22px;' id='searchPriceEnd' value=" . $priceArr['searchPriceEnd'] . "></span></td>";
        }
        $showselect .= " <td><input type='submit' name='submit' value='搜索 ' style='width:80px;height:28px;' /></form></td>";
        $showselect .= "</tr></table>";

        if ($showsearch == 1) {
            $showtable .= $showselect;
        }
        $showtable .= "<table width='100%' border='0' cellspacing='0' cellpadding='0' class='tab'>";
        $showtable .= "<thead><tr><td>" . $sfunction . "</td></tr><tr bgcolor=#E0E0E0>";

        foreach ($ContentListArr as $ka => $va) {
            $showtable .= "<td align='center'>" . $va['EchoName'] . "</td>";
        }
        if ($MainNodeArr['ListHaveEdit'] || $MainNodeArr['ListHaveDel'] || $MainNodeArr['ListHaveDelSign'] || $MainNodeArr['ListHaveDelSignRecover']) {//列表页显示默认的操作的表头
            if ($nodeid <> 80) {
                $showtable .= "<td align='center'>操作</td>";
            }
        }
        $showtable .= "</thead></tr>";

        foreach ($Contentlist as $k => $v) {//记录数的循环
            $showtable .= "<tr>";
            foreach ($ContentListArr as $k2 => $v2) {//每条记录数中字段的循环

                if ($v2['FieldType'] == 'ContentLINK') {
                    if ($keywords == 79) { //如果当前模块是contentlist是显示的编辑
                        //$showtable.="<td align='center'><a href='".__APP__."/".$v2['GetValue'].$v[$MainNodeArr['MainRelationFieldName']]."/Pnodeid/".$Pnodeid."/nodeid/".$nodeid."/searchtype/".$searchtype."/keywords/".$keywords."'>".$v[$v2['AsField']]."</a></td>";
                    }
                } elseif ($v2['FieldType'] == 'LINK') {
                    if ($nodeid <> 80) {
                        $showtable .= "<td align='center' width='50px;'><a href='" . __APP__ . "/" . $v2['GetValue'] . $v[$MainNodeArr['MainRelationFieldName']] . "/Pnodeid/" . $Pnodeid . "/nodeid/" . $nodeid . "/searchtype/" . $searchtype . "/keywords/" . $keywords . "'>" . $v[$v2['AsField']] . "</a></td>";
                    }
                } elseif ($v2['FieldType'] == 'LINKSELFID') {
                    if ($nodeid <> 80) {
                        $showtable .= "<td align='center'><a href='" . __APP__ . "/" . $v2['GetValue'] . $v[$v2['AsField']] . "/Pnodeid/" . $Pnodeid . "/nodeid/" . $nodeid . "/searchtype/" . $searchtype . "/keywords/" . $keywords . "' target=_blank>" . $v[$v2['AsField']] . "</a></td>";
                    }
                } elseif ($v2['FieldType'] == 'LINKSELF') {
                    if ($nodeid <> 80) {
                        $showtable .= "<td align='left' width='300px;' style='word-break:break-all'><a href='" . $v[$v2['AsField']] . "' target='_blank'>" . $v[$v2['AsField']] . "</a></td>";
                    }
                } elseif ($v2['FieldType'] == 'HIDDEN') {
                    $showtable .= "<td align='center'> - </td>";
                } elseif ($v2['FieldType'] == 'IMAGE') {
                    if ($v[$v2['AsField']]) {
                        $showtable .= "<td align='center'><img src=" . WEBROOT_URL . $v[$v2['AsField']] . " width=100 height=40></td>";
                    } else {
                        $showtable .= "<td align='center'><font color='#ccc'>未传图片</font></td>";
                    }
                } elseif ($v2['FieldType'] == 'SERIALIZE') {
                    $temparr = unserialize($v[$v2['AsField']]);
                    //print_r($temparr);
                    $showtable .= "<td align='center'>" . $temparr . "</td>";
                } elseif ($v2['FieldType'] == 'SERIALIZE_PRODUCT') {
                    $temparr = unserialize($v[$v2['AsField']]);
                    $productString = $TP->getProductNameByArr($temparr);
                    $showtable .= "<td align='center' width='120px'>" . $productString . "</td>";
                } elseif ($v2['FieldType'] == 'TEXTAREA') {
                    $showtable .= "<td align='left' width='300px;' style='word-break:break-all'>" . $v[$v2['AsField']] . "</td>";
                }elseif($v2['FieldType'] == 'SALTID'){
                    $showtable .= "<td align='left' width='100px;' style='word-break:break-all'>" . $v['u_salt'] .$v['u_id']. "</td>";
                }elseif($v2['FieldType'] == 'TIME'){
                    $showtable .= "<td align='center' width='70px;'>" . substr($v[$v2['AsField']], 0, 50) . "</td>";
                }elseif($v2['FieldType'] == 'TEXTUSER'){
                    $showtable .= "<td align='center' width='50px;'>" . substr($v[$v2['AsField']], 0, 50) . "</td>";
                }else{
                    $showtable .= "<td align='center' width='80px;'>" . substr($v[$v2['AsField']], 0, 50) . "</td>";
                }
            }

            if ($MainNodeArr['ListHaveEdit'] || $MainNodeArr['ListHaveDel'] || $MainNodeArr['ListHaveDelSign'] || $MainNodeArr['ListHaveCopy'] || $MainNodeArr['ListHaveDelSignRecover']) {//列表页显示默认的操作
                $showtable .= "<td align='center' width='60px;'>";
                if ($MainNodeArr['ListHaveEdit']) {
                    $showtable .= " <a href=__APP__/empty/ContentEdit/Pnodeid/" . $Pnodeid . "/nodeid/" . $nodeid . "/id/" . $v[$MainNodeArr['MainRelationFieldName']] . "/searchtype/" . $searchtype . "/keywords/" . $keywords . "/ >编辑</a>";
                }
                if ($MainNodeArr['ListHaveDel']) {
                    $showtable .= ' <a href="javascript:if(confirm(\'确实要删除吗?\'))location=\'__APP__/empty/ContentDel/Pnodeid/'.$Pnodeid.'/nodeid/'.$nodeid.'/id/'.$v[$MainNodeArr['MainRelationFieldName']].'/action/1/searchtype/'.$searchtype.'/keywords/'.$keywords.' \'">删除</a>';
                }
                if ($MainNodeArr['ListHaveCopy']) {
                    $showtable .= " <a href=__APP__/empty/ContentCopy/Pnodeid/" . $Pnodeid . "/nodeid/" . $nodeid . "/id/" . $v[$MainNodeArr['MainRelationFieldName']] . "/action/1/searchtype/" . $searchtype . "/keywords/" . $keywords . " >复制</a>";
                }
                if ($MainNodeArr['ListHaveCopyToContent']) {
                    $showtable .= " <a href=__APP__/empty/ContentCopyToContent/Pnodeid/" . $Pnodeid . "/nodeid/" . $nodeid . "/id/" . $v[$MainNodeArr['MainRelationFieldName']] . "/action/1/searchtype/" . $searchtype . "/keywords/" . $keywords . "  >复制到Content</a>";
                }
                if ($MainNodeArr['ListHaveDelSign']) {
                    $showtable .= ' <a href="javascript:if(confirm(\'确实放入回收站吗?\'))location=\'__APP__/empty/ContentDelSign/Pnodeid/'.$Pnodeid.'/nodeid/'.$nodeid.'/id/'.$v[$MainNodeArr['MainRelationFieldName']].'/action/1/searchtype/'.$searchtype.'/keywords/'.$keywords.'/p/'.$nowPage.' \'"><img src=__DOC__/static/Manage/Img/delete.jpg width=20px height=20px></a>';
                }
                if ($MainNodeArr['ListHaveDelSignRecover']) {
                    $showtable .= ' <a href="javascript:if(confirm(\'移出回收站吗?\'))location=\'__APP__/empty/ContentDelSignRecover/Pnodeid/'.$Pnodeid.'/nodeid/'.$nodeid.'/id/'.$v[$MainNodeArr['MainRelationFieldName']].'/action/1/searchtype/'.$searchtype.'/keywords/'.$keywords.'/p/'.$nowPage.'\'"><img src=__DOC__/static/Manage/Img/delete.jpg width=20px height=20px></a>';
                }
                $showtable .= "</td>";
            }
            $showtable .= "</tr>";
        }
        $showtable .= "</table>";
        $this->assign('showtable', $showtable);
        $this->assign('page', $show);// 赋值分页输出

        //nav导航的显示
        if ($MainNodeArr['title'] == "ContentList") {
            $currentNav = "<div class=current>" . $MainNodeArr['title'] . "</div>  <div class=dnav><a href=__APP__/empty/ContentList/Pnodeid/" . $Pnodeid . "/nodeid/80/searchtype/166/keywords/" . $keywords . ">Content</a>";
        } elseif ($MainNodeArr['title'] == "Content") {
            $currentNav = "<a href=__APP__/empty/ContentList/Pnodeid/" . $Pnodeid . "/nodeid/79/searchtype/165/keywords/" . $keywords . ">ContentList</a> <div class=current> Content</div>";
        } else {
            $currentNav = $MainNodeArr['title'];
        }
        $currenttitle   = $MainNodeArr['title'];
        //var_dump($showtable);
        //die();
        $this->assign('currenttitle', $currenttitle);
        $this->assign('currentNav', $currentNav);
        $this->display("Empty:contentlist");
    }

    /*
     * 订单导出功能
     * Creater:glx-2016/10/27
     */
    public function do_Excel(){
        echo header("Content-type:text/html;charset=utf-8");
        $node = I ('nodeid','0','intval');
        if($node=='112'){//已支付
            $status = 1;
            $xname = '已支付';
        }else{
            $status = 0;
            $xname = 'ALL';
        }
        $xlsData = M('user_prepaid')->alias('a')
            ->field('a.ctime time,a.up_addressee,a.up_mobile,a.up_address,a.up_productid,a.up_amount')
            ->join('tdf_users b on a.up_uid=b.u_id ')->where("a.up_status=$status")
            ->select();

        foreach($xlsData as $k =>$v){
            //$xlsData[$k]['product'] = unserialize($v['up_productid']);
            $str = '';
            foreach(unserialize($v['up_productid']) as $ko =>$vo){
                $ar = explode(',',$vo);
                $p_name = M('product')->where("p_id = ".$ar[0])->getField('p_name');
                $str.=$p_name.'*'.$ar[1];
            }
            $xlsData[$k]['productinfo'] = $str;
        }
        $xlsName = '订单-'. $xname . date('y/m/d', time());

        Vendor('PHPExcel.PHPExcel', '', '.php');
        $objExcel = new PHPExcel();
        //设置一些基本参数
        $objExcel->getProperties()->setCreator("Mr.guo")//创建者
        ->setLastModifiedBy("Mr.guo")//最后修改者
        ->setTitle("订单列表");//标题
        //设置表的名称标题
        $objExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', "服务年度")
            ->setCellValue('B1', "企业名称")
            ->setCellValue('C1', "联系人")
            ->setCellValue('D1', "联系方式")
            ->setCellValue('E1', "通讯地址")
            ->setCellValue('F1', "服务内容")
            ->setCellValue('G1', "服务金额")
            ->setCellValue('H1', "服务次数");
        //循环写入数据到EXCEL
        foreach ($xlsData as $k => $v) {
            $num = $k + 2;
            $objExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $v['time'])
                ->setCellValue('B' . $num, ' ')
                ->setCellValue('C' . $num, $v['up_addressee'])
                ->setCellValue('D' . $num, $v['up_mobile'])
                ->setCellValue('E' . $num, $v['up_address'])
                ->setCellValue('F' . $num, $v['productinfo'])
                ->setCellValue('G' . $num, $v['up_amount']);
        }
        $objExcel->getActiveSheet()->setTitle('订单信息列表');
        $objExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区，避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $xlsName . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }





    public function getListStringByArr($mainFieldArr) {
        foreach ($mainFieldArr as $key => $value) {
            $field .= $value['EchoName'] . ',';
        }
        $field = substr($field, 0, strlen($field) - 1);
        return $field;
    }


    /*
     * 检验checkbox的值并返回
     */
    function checkboxvalue($tvalue) {
        if ($tvalue) {
            return "checked=checked";
        } else {
            return "";
        }
    }

    /*
		 * @返回Node主SQL语句 
		 * @DATA: $CLArr内容数组 $MainNodeArr主节点模块数组 $idvalue为主键的值
		 *
		 */
    function GetNodeSqlByEdit($CLArr, $NodeArr, $idvalue) { //由$ContentArr和$MainNodeArr获得$NodeMainSql语句
        $sqlfield = $NodeArr['MainRelationFieldName'] . ",";
        foreach ($CLArr as $k => $v) {
            $sqlfield .= $v['FieldName'] . ",";
        }
        $sqlfield = substr($sqlfield, 0, -1);
        $sqlfield = str_replace("`", '"', $sqlfield);
        $NodeSql = "select " . $sqlfield . " from " . $NodeArr['MainTable'] . " as ";
        $NodeSql .= (isset($NodeArr['AsField'])) ? $NodeArr['AsField'] : $NodeArr['MainTable'];
        //$NodeSql.=" ".$NodeArr['InnerJoinSql'];
        $NodeSql .= (isset($MainNodeArr['QueryFiterSql'])) ? " where " . $NodeArr['QueryFiterSql'] : " where 1=1";//配置表中的查询条件
        $NodeSql .= " and " . $NodeArr['AsField'] . "." . $NodeArr['MainRelationFieldName'] . "=" . $idvalue;
        $NodeSql .= (isset($MainNodeArr['OrderBySql'])) ? " order by " . $NodeArr['OrderBySql'] : " order by " . $NodeArr['MainRelationFieldName'];
        $NodeSql .= " limit 0,1";
        $NodeSql .= (isset($MainNodeArr['GroupBySql'])) ? " group by " . $NodeArr['GroupBySql'] : " ";
        return $NodeSql;
    }


    function GetNodeSqlByArr($CLArr, $NodeArr, $SArr = 0, $sqltype = 0) { //由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
        // var_dump($SArr);
        $sqlfield = $NodeArr['AsField'] . "." . $NodeArr['MainRelationFieldName'] . ",";
        foreach ($CLArr as $k => $v) {
            $tempsql = $v['OutputFieldName'];
            $field = str_replace("`", '"', $tempsql);//转义'为"
            switch ($v['FieldType']) {
                case "TEXT":
                    $sqlfield .= isset($v['AsField']) ? $field . " as " . $v['AsField'] . "," : $field . ",";
                    break;
                case "RADIO";
                    $GetValueArr = explode(";", $v['GetValue']);
                    $sqlfield .= "case";
                    foreach ($GetValueArr as $key => $value) {
                        $varr = explode("=", $value);
                        $cw = " when " . $v['OutputFieldName'] . "= " . $varr['0'] . " Then " . $varr['1'] . " ";
                        $cw = str_replace("`", '"', $cw);//转义'为"
                        $sqlfield .= $cw;
                    }
                    $sqlfield .= " end as " . $v['AsField'] . ",";

                    break;
                case "SALTID";
                    $sqlfield .= "";
                    break;
                default:
                    $sqlfield .= isset($v['AsField']) ? $field . " as " . $v['AsField'] . "," : $field . ",";
                    break;
            }
        }
        $sqlfield = substr($sqlfield, 0, -1);
        $sqlfield = str_replace("`", '"', $sqlfield);

        if ($sqltype == 1) {
            $sqlfield = " count(*) as count";
        }

        $NodeSql = "select " . $sqlfield . " from " . $NodeArr['MainTable'] . " as ";
        $NodeSql .= (isset($NodeArr['AsField'])) ? $NodeArr['AsField'] : $NodeArr['MainTable'];
        $NodeSql .= " " . $NodeArr['InnerJoinSql'];
        $NodeSql .= (!empty($NodeArr['QueryFiterSql'])) ? " where " . $NodeArr['QueryFiterSql'] : " where 1=1";//配置表中的查询条件
        if ($SArr[0]) {
            $FnGvArr = $this->OutputFieldNameByLID($SArr[0]);
            if ($FnGvArr['FieldType'] == "RADIO") {
                $NodeSql .= " and " . $FnGvArr['OutputFieldName'] . " = " . $this->getwherefield($SArr[1], $FnGvArr['GetValue']);
            } elseif ($FnGvArr['FieldType'] == "TIME") {
                $NodeSql .= " and " . $FnGvArr['OutputFieldName'] . " > '" . $SArr[3]['searchTimeStart'] . " 00:00:00' and " . $FnGvArr['OutputFieldName'] . " < '" . $SArr[3]['searchTimeEnd'] . " 23:59:59'";
            } elseif ($FnGvArr['FieldType'] == "PRICE") {
                $NodeSql .= " and " . $FnGvArr['OutputFieldName'] . " > " . $SArr[4]['searchPriceStart'] . " and " . $FnGvArr['OutputFieldName'] . " < " . $SArr[4]['searchPriceEnd'] . "";
            } else {
                if ($SArr[2]) {
                    $NodeSql .= " and " . $FnGvArr['OutputFieldName'] . " = " . $SArr[1];
                } else {
                    $NodeSql .= " and " . $FnGvArr['OutputFieldName'] . " like '%" . $SArr[1] . "%'";
                }
            }
        }
        $NodeSql .= (!empty($NodeArr['OrderBySql'])) ? " order by " . $NodeArr['OrderBySql'] : " order by " . $NodeArr['MainRelationFieldName'];
        $NodeSql .= (!empty($NodeArr['GroupBySql'])) ? " group by " . $NodeArr['GroupBySql'] : " ";

        return $NodeSql;
    }


    /*
		 * 转义RADIO的查询关键字
		 * @data: value为关键字的值  getvalue为转义的格式
		 * 
		 */
    public function getwherefield($value, $getvalue) {
        $GvArr = explode(";", $getvalue);
        foreach ($GvArr as $k => $v) {
            $tempArr = explode("=", $v);
            if (is_int(strpos($tempArr[1], $value))) {
                $result = $tempArr[0];
            }
        }
        return $result;
    }


    public function OutputFieldNameByLID($LID) {
        $FM = M('node_modulecontentlist')->field('OutputFieldName,GetValue,FieldType')->where('id=' . $LID)->find();
        return $FM;
//			return $FM['OutputFieldName'];
    }


    private function getDispArea($AIPM, $Province, $City, $Region) {
        $Result .= $AIPM->getItemNameByID($Province) . ' ';
        $Result .= $AIPM->getItemNameByID($City) . ' ';
        $Result .= $AIPM->getItemNameByID($Region);
        return $Result;
    }

    public function ContentCopyToContent() {
        $Pnodeid = I("get.Pnodeid", 0, "intval");
        $nodeid = I("get.nodeid", 0, "intval");
        $selectid = I("get.id", 0, "intval");
        $action = I("get.action", "0", "intval");
        $searchtype = I("get.searchtype", "0", "intval");
        $keywords = I("get.keywords", "0", "intval");
        $MainNodeArr = $this->getMainNodeArr($nodeid);//主节点信息
        $EditField = $this->getEditField($nodeid);    //需要编辑的字段array
        $NodeMainSql = $this->GetNodeSqlByEdit($EditField, $MainNodeArr, $selectid);//由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
        $UD = M(cutprefix($MainNodeArr['MainTable']));

        $UD_content = M("node_modulecontent");

        if ($action == 1) {
            $yuan_data = $UD->where($MainNodeArr['MainRelationFieldName'] . "=" . $selectid)->find();
            if (strpos($yuan_data['OutputFieldName'], ".")) {//如果输出字段名是带"."的，需截取"."后面的的字段名
                $tarr = explode(".", $yuan_data['OutputFieldName']);
                $OutputFieldName = $tarr[1];
            } else {
                $OutputFieldName = $yuan_data['OutputFieldName'];
            }

            $data['NodeId'] = $yuan_data['NodeId'];
            $data['FieldName'] = $OutputFieldName;
            $data['OutputFieldName'] = $OutputFieldName;
            $data['EchoName'] = $yuan_data['EchoName'] . "_lcopy";
            $data['EchoOrder'] = $yuan_data['EchoOrder'];
            $data['FieldType'] = $yuan_data['FieldType'];
            $data['GetValue'] = $yuan_data['GetValue'];
            //pr($MainNodeArr['MainTable']);
            //pr($UD_content->getLastSql());
            //exit;
            if ($UD_content->add($data)) {
                $adminlog = $this->addLog(6, "复制数据", $nodeid, $_SESSION['my_info']['aid'], $selectid);//记录后台日志
                $this->success("复制成功!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
            } else {
                $this->error("复制未成功!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
            }
        }
    }

    public function ContentCopy() {
        $Pnodeid = I("get.Pnodeid", 0, "intval");
        $nodeid = I("get.nodeid", 0, "intval");
        $selectid = I("get.id", 0, "intval");
        $action = I("get.action", "0", "intval");
        $searchtype = I("get.searchtype", "0", "intval");
        $keywords = I("get.keywords", "0", "intval");
        $MainNodeArr = $this->getMainNodeArr($nodeid);//主节点信息
        $EditField = $this->getEditField($nodeid);    //需要编辑的字段array
        $NodeMainSql = $this->GetNodeSqlByEdit($EditField, $MainNodeArr, $selectid);//由$ContentListArr和$MainNodeArr获得$NodeMainSql语句

        $table_name = cutprefix($MainNodeArr['MainTable']);
        $UD = M($table_name);

        //exit;
        if ($action == 1) {
            $yuan_data = $UD->where($MainNodeArr['MainRelationFieldName'] . "=" . $selectid)->find();
            if ($table_name == 'node_modulecontentlist') {
                $data['NodeId'] = $yuan_data['NodeId'];
                $data['OutputFieldName'] = $yuan_data['OutputFieldName'];
                $data['AsField'] = $yuan_data['AsField'] . "_copy";
                $data['EchoName'] = $yuan_data['EchoName'] . "_copy";
                $data['EchoOrder'] = $yuan_data['EchoOrder'];
                $data['FieldType'] = $yuan_data['FieldType'];
                $data['GetValue'] = $yuan_data['GetValue'];
            } elseif ($table_name == 'node_modulecontent') {
                $data['NodeId'] = $yuan_data['NodeId'];
                $data['OutputFieldName'] = $yuan_data['OutputFieldName'];
                $data['FieldName'] = $yuan_data['FieldName'] . "_copy";
                $data['EchoName'] = $yuan_data['EchoName'] . "_copy";
                $data['EchoOrder'] = $yuan_data['EchoOrder'];
                $data['FieldType'] = $yuan_data['FieldType'];
                $data['GetValue'] = $yuan_data['GetValue'];
            } else {
                $data = $yuan_data;
                unset($data['id']);
            }

            if ($UD->add($data)) {
                $adminlog = $this->addLog(6, "复制数据", $nodeid, $_SESSION['my_info']['aid'], $selectid);//记录后台日志
                $this->success("复制成功!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);

            } else {
                $this->error("复制未成功!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
            }


        }

    }

    public function ContentDel() {
        $Pnodeid = I("get.Pnodeid", 0, "intval");
        $nodeid = I("get.nodeid", 0, "intval");
        $selectid = I("get.id", 0, "intval");
        $action = I("get.action", "0", "intval");
        $searchtype = I("get.searchtype", "0", "intval");
        $keywords = I("get.keywords", "0", "intval");
        $MainNodeArr = $this->getMainNodeArr($nodeid);//主节点信息
        $EditField = $this->getEditField($nodeid);    //需要编辑的字段array
        $NodeMainSql = $this->GetNodeSqlByEdit($EditField, $MainNodeArr, $selectid);//由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
        $UD = M(cutprefix($MainNodeArr['MainTable']));
        if ($action == 1) {
            if ($UD->where($MainNodeArr['MainRelationFieldName'] . "=" . $selectid)->delete()) {
                $adminlog = $this->addLog(5, "删除数据", $nodeid, $_SESSION['my_info']['aid'], $selectid);//记录后台日志
                $this->success("删除成功!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);

            } else {
                $this->error("删除失败!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
            }
        }
    }

    public function ContentDelSign() {
        $Pnodeid    = I("get.Pnodeid", 0, "intval");
        $nodeid     = I("get.nodeid", 0, "intval");
        $selectid   = I("get.id", 0, "intval");
        $action     = I("get.action", "0", "intval");
        $searchtype = I("get.searchtype", "0", "intval");
        $keywords   = I("get.keywords", "0", "intval");
        $p          = I("get.p", "0", "intval");
        $MainNodeArr= $this->getMainNodeArr($nodeid);//主节点信息
        $EditField  = $this->getEditField($nodeid);    //需要编辑的字段array
        $NodeMainSql= $this->GetNodeSqlByEdit($EditField, $MainNodeArr, $selectid);//由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
        $UD = M(cutprefix($MainNodeArr['MainTable']));
        if ($action == 1) {
            if ($UD->where($MainNodeArr['MainRelationFieldName'] . "=" . $selectid)->setField("delsign",1)) {
                $adminlog = $this->addLog(5, "删除数据", $nodeid, $_SESSION['my_info']['aid'], $selectid);//记录后台日志
                $this->success("转入回收站!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords. '/p/' . $p), 1);
            } else {
                $this->error("转入回收站失败!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
            }
        }
    }

    public function ContentDelSignRecover() {
        $Pnodeid    = I("get.Pnodeid", 0, "intval");
        $nodeid     = I("get.nodeid", 0, "intval");
        $selectid   = I("get.id", 0, "intval");
        $action     = I("get.action", "0", "intval");
        $searchtype = I("get.searchtype", "0", "intval");
        $keywords   = I("get.keywords", "0", "intval");
        $MainNodeArr= $this->getMainNodeArr($nodeid);//主节点信息
        $EditField  = $this->getEditField($nodeid);    //需要编辑的字段array
        $NodeMainSql= $this->GetNodeSqlByEdit($EditField, $MainNodeArr, $selectid);//由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
        $UD = M(cutprefix($MainNodeArr['MainTable']));
        if ($action == 1) {
            if ($UD->where($MainNodeArr['MainRelationFieldName'] . "=" . $selectid)->setField("delsign",0)) {
                $adminlog = $this->addLog(5, "删除数据", $nodeid, $_SESSION['my_info']['aid'], $selectid);//记录后台日志
                $this->success("移出回收站!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
            }else{
                $this->error("移出回收站失败!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
            }
        }
    }


    public function ContentEdit() {
        $Pnodeid = I("get.Pnodeid", 0, "intval");
        $nodeid = I("get.nodeid", 0, "intval");
        $selectid = I("get.id", 0, "intval");
        $action = I("action", "0", "string");
        $searchtype = I("searchtype", 0, "intval");
        $keywords = I("keywords", '', "string");
        $MainNodeArr = $this->getMainNodeArr($nodeid);//主节点信息
        $EditField = $this->getEditField($nodeid);    //需要编辑的字段array
        $NodeMainSql = $this->GetNodeSqlByEdit($EditField, $MainNodeArr, $selectid);//由$ContentListArr和$MainNodeArr获得$NodeMainSql语句
        $this->assign('currentNav', "编辑_" . $MainNodeArr['title']);//nav导航名称
        if ($action == "0") {
            //主表字段信息数组
            $from_title = array();
            $from_data = array();
            if ($selectid == 0) {//新增
                foreach ($EditField as $key => $value) {
                    $farr = array(
                        "FieldType" => $value['FieldType'],
                        "FieldName" => $value['FieldName'],
                        "OutputFieldName" => $value['OutputFieldName'],
                        "EchoName" => $value['EchoName'],
                        "GetValue" => $value['GetValue'],
                        "dvalue" => $value['DefaultValue']
                    );
                    $from_arr = $this->getFromField($farr);
                    $from_title = array_merge($from_title, $from_arr['title']);
                    $from_data = array_merge($from_data, $from_arr['data']);

                    //-----------------增加html类型字段	 start
                    if ($farr['FieldType'] == 'HTML') {
                        $HtmlName = "m_" . $farr['FieldName'];
                        $this->assign('HtmlName', $HtmlName);//
                    }
                    //-----------------增加html类型字段   end

                    //-------------------如果有多图上传，赋值名称到页面变量用于js取值
                    if ($farr['FieldType'] == 'MIMG') {
                        $MimgName = "m_" . $farr['FieldName'];
                        $tempArr = Array(
                            '0' => array(
                                'id' => 0,
                                'imgpath' => '',
                            ),
                        );
                        $pinfo = json_encode($tempArr);
                        $this->assign('pinfo', $pinfo);
                        $this->assign('MimgName', $MimgName);
                    }
                    //-------------------如果有多图上传，赋值名称到页面变量用于js取值

                    //-------------------如果有checkbox，需要加入控件
                }
            } else {//修改
                $MT = M();
                $DataArr = $MT->query($NodeMainSql);
                foreach ($EditField as $key => $value) {
                    $farr = array(
                        "FieldType" => $value['FieldType'],
                        "FieldName" => $value['FieldName'],
                        "OutputFieldName" => $value['OutputFieldName'],
                        "EchoName" => $value['EchoName'],
                        "GetValue" => $value['GetValue'],
                        "dvalue" => $DataArr[0][$value['FieldName']]
                    );

                    if ($value['ReadOnly'] == 1) {
                        $farr['ReadOnly'] = "ReadOnly";
                        if (strpos($value['OutputFieldName'], ",")) {
                            $farr['dvalue'] = $this->getValueFromJoinTable($farr['OutputFieldName'], $farr['dvalue']);
                        }
                    } else {
                        $farr['ReadOnly'] = "css_text";

                    }
                    // var_dump($farr);
                    $from_arr = $this->getFromField($farr);
                    $from_title = array_merge($from_title, $from_arr['title']);
                    $from_data = array_merge($from_data, $from_arr['data']);

                    //-------------------如果有HTML富文本编辑器，赋值名称到页面变量用于js取值 start
                    if ($farr['FieldType'] == 'HTML') {
                        $HtmlName = "m_" . $farr['FieldName'];
                    }

                    $this->assign('HtmlName', $HtmlName);// 
                    //-------------------如果有HTML富文本编辑器，赋值名称到页面变量用于js取值 end

                    //-------------------如果有多图上传，赋值名称到页面变量用于js取值
                    if ($farr['FieldType'] == 'MIMG') {
                        $MimgName = "m_" . $farr['FieldName'];
                        $valueArr = explode(",", $farr['dvalue']);
                        $ke = 0;
                        foreach ($valueArr as $k => $v) {
                            $imageInfo = M('image')->where("id=" . $v)->find();
                            $imageA[$ke]['id'] = $v;
                            $imageA[$ke]['imgpath'] = WEBROOT_PATH . $imageInfo['path'];
                            $ke++;
                        }
                        $pinfo = json_encode($imageA);
                    }
                    //-------------------如果有多图上传，赋值名称到页面变量用于js取值
                }

                //-------------------如果有多图上传，赋值名称到页面变量用于js取值 start
                $this->assign('pinfo', $pinfo);
                $this->assign('MimgName', $MimgName);
                //-------------------如果有多图上传，赋值名称到页面变量用于js取值 end
            }

            $from_cont = array('control' => array('con_list' => array('modi' => '修改', 'dele' => '删除'), 'con_parse' => array('?module=xxx&action=aaa', 'const')));
            $from_tags = array('tags' => array('name' => 'table', 'attr' => array('class' => 'list')),
                'tagsrow' => array('name' => 'tr', 'rules' => array('class' => array('title1', 'list_self'), 'rul' => 0)),
                'tagscol' => array('name' => 'td', 'rules' => array('class' => array('title1', 'list_self'), 'rul' => 2)),
                'tagstitle' => array('name' => 'legend', 'title' => '修改数据'),
                'tagsform' => array('attr' => array('name' => 'myform', 'method' => 'post'),
                    'button' => array('submit', 'submit', '提交', 'css_submit'),
                    'custom' => '<input type=hidden name=searchtype value=' . $searchtype . '><input type=hidden name=keywords value=' . $keywords . '><input name="action" type="hidden" value="save" /><input name="hidden2" type="hidden" value="hidden2" />')
            );
            $FORMA = new MakeTableModel();
            $showfrom = $FORMA->setTitle($from_title)->setName('myAlist1')->setPK('ml_id')->setData($from_data)->setControl($from_cont)->setTags($from_tags)->setId($selectid)->showTableSubmit();

            $this->assign('showfrom', $showfrom);
            $this->display("Empty:contentedit");
        } elseif ($action == "save") {//保存记录
            echo header ("Content-Type:text/html; charset=utf-8" );
            echo header('Content-Type:application/json; charset=utf-8');
            //exit;
            $getpost = array();
            foreach ($EditField as $key => $value) {
                $fvarr['FieldType'] = $value['FieldType'];
                //$fvarr['v']		 	=I("post.m_".$value['FieldName']);
                $fvarr['v'] = $_POST['m_' . $value['FieldName']];

                $fvarr['FieldName'] = $value['FieldName'];
                if ($fvarr['FieldType'] == "CHECKBOX") {
                    $fvarr['v'] = arrayTransToStr($fvarr['v']);//获取checkbox的值,把数组转换为字符串
                }
                //print_r($fvarr);
                $postarr = $this->getFromFieldValue($fvarr, $selectid);
                $getpost = array_merge($getpost, $postarr);

            }
            foreach ($getpost as $key => $value) {
                $getpost[$key] = $this->replace_str($value);
            }

            $UD = M(cutprefix($MainNodeArr['MainTable']));
            $keywords=urlencode($keywords);
            if ($selectid == 0) {//新增
                $add_id = $UD->add($getpost);
                if ($add_id) {
                    $adminlog = $this->addLog(2, "新增数据", $nodeid, $_SESSION['my_info']['aid'], $add_id);//记录后台日志
                    $this->error("保存成功", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
                } else {
                    $this->error("数据未更新!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
                }
                $this->display("Empty:contentedit");
            } else {//修改

                if ($UD->where($MainNodeArr['MainRelationFieldName'] . "=" . $selectid)->save($getpost)) {
                    $adminlog = $this->addLog(3, "更新数据", $nodeid, $_SESSION['my_info']['aid'], $selectid);//记录后台日志
                    $this->error("保存成功", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
                } else {
                    $this->error("数据未更新!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
                }
                $this->display("Empty:contentedit");
            }
        }
    }


    public function replace_str($string) {
        $result = preg_replace('/&gt;/', '>', $string);
        $result = preg_replace('/&lt;/', '<', $result);
        return $result;
    }

    /*获取关联表中的对应字段的值
		 * @Data： $OFN:对应的表信息字串(需要关联的表名,关联的查询字段名称,输出的字段名称)
		 * @Data：$value:对应的值
		 */
    public function getValueFromJoinTable($OFN, $value) {
        $tarr = explode(",", $OFN);
        $TM = M($tarr[2]);
        $result = M(cutprefix($tarr[0]))->where($tarr[1] . '=' . $value)->getField($tarr[2]);
        return $result;
    }

    /*
		 * 得到密码的MD5值
		 * @data:$passvalue 传入原密码值 $uid 用户id
		 */
    function getMd5ByPassUid($passvalue, $uid) {
        $UM = new UsersModel();
        $UserInfo = $UM->getUserByID($uid);
        if ($UserInfo === false) {
            return $this->error('更新失败', U('Admin/Module/ContentList'));
        }
        if ($UserInfo === null) {
            return $this->error('账户异常，请重新登陆', U('Admin/Module/ContentList'));
        }
        $NewSaltPass = $this->getSaltPass($passvalue, $UserInfo[$UM->F->Salt]);
        return $NewSaltPass;
    }

    private function getSaltPass($Pass, $Salt) {
        //return md5(md5($Pass).$Salt);
        return md5($Pass . $Salt);
    }
    /*function getmd(){
			echo md5(md5("111111")."ZAJNS");
		}*/

    /*
		 * 得到表单post过来的字段对应值
		 * @data:$fvarr(含字段类型和字段值)
		 * @return:value
		 * @author:zhangzhibin miaomin edited@2016/5/18
		 */
    public function getFromFieldValue($fvarr, $sid) {
        $result = array();
        $tempvalue = mysql_real_escape_string($fvarr['v']);
        if ($fvarr['FieldType'] == "PASSWORDSALT") {
            if ($fvarr['v'] === 0 || !isset($fvarr['v']) || empty($fvarr['v'])) {//密码为空不修改
                // 必须使用严格等于===否则密码如果为字母起始的判断会成立 miaomin edited@2016.5.18

            } else {
                /**
                 * miaomin edited start
                 */
                if ($sid == 0){ //没有uid代表新增一个用户信息
                    $U = new UsersModel();
                    $newSalt = $U->getUserSalt();
                    $newPassword = $this->getSaltPass( $fvarr['v'], $newSalt );
                    $temparr['u_salt'] = $newSalt;
                    $temparr['u_title'] = '普通用户';
                    $temparr['u_createdate'] = get_now();
                    $temparr[$fvarr['FieldName']] = $newPassword;
                    $result = array_merge($result, $temparr);
                }else{
                    $temparr[$fvarr['FieldName']] = $this->getMd5ByPassUid($fvarr['v'], $sid);
                    $result = array_merge($result, $temparr);
                }
                /**
                 * miaomin edited end
                 */
            }
        } elseif ($fvarr['FieldType'] == "DISPLAY") {//如果是显示类型，不更新数据

        } else {
            $temparr[$fvarr['FieldName']] = $fvarr['v'];
            $result = array_merge($result, $temparr);
        }

        return $result;
    }


    /*
		 * 得到表单数组(向表单中填充)
		 * @data:$farr中包含：filedtype 字段类型      ofname 字段输出的控件名称  echoname字段显示名称   value对应的数据值
		 * @author:zhangzhibin
		 * @return:array
		 */
    public function getFromField($farr) {
        $FieldType = $farr['FieldType'];
        $OutputFieldName = $farr['OutputFieldName'];
        $EchoName = $farr['EchoName'];
        $GetValue = $farr['GetValue'];
        $dvalue = $farr['dvalue'];
        $ReadOnly = $farr['ReadOnly'];
        $FieldName = $farr['FieldName'];

        switch ($FieldType) {
            case "DISPLAY":
                $fromtitle[$FieldName] = array('input', '', $EchoName, "DISPLAY");
                $fromdata[$FieldName] = ($dvalue == "0") ? "" : $dvalue;
                break;
            case "TEXT":
                $fromtitle[$FieldName] = array('input', 'text', $EchoName, $ReadOnly);
                $fromdata[$FieldName] = $dvalue;
                break;
            case "DATETIME":
                $fromtitle[$FieldName] = array('input', 'text', $EchoName, $ReadOnly);
                $fromdata[$FieldName] = $dvalue;
                break;
            case "IMAGE":
                $fromtitle[$FieldName] = array('input', $FieldType, $EchoName, 'css_textimage');
                $fromdata[$FieldName] = ($dvalue == "0") ? "" : $dvalue;
                break;
            case "IMAGECOVER":
                $fromtitle[$FieldName] = array('input', $FieldType, $EchoName, 'css_imagecover');
                $fromdata[$FieldName] = ($dvalue == "0") ? "" : $dvalue;
                break;
            case "PASSWORDSALT":
                $fromtitle[$FieldName] = array('input', 'password', $EchoName, 'PASSWORDSALT');
                $fromdata[$FieldName] = "";
                break;
            case "RADIO":
                $fromtitle[$FieldName] = array('input', 'radio', array('name' => $EchoName, 'vlist' => $this->getRadioInfo($GetValue, 1)), 'css_radio');
                $fromdata[$FieldName] = ($dvalue == "0") ? "" : $dvalue;
                break;
            case "CHECKBOX":
                //var_dump($dvalue);
                //$fromtitle[$FieldName]	=array('input','checkbox',array('name'=>$EchoName,'vlist'=>$this->getCheckboxInfo($GetValue,1)),'css_checkbox');
                $fromtitle[$FieldName] = array('input', 'checkbox', array('name' => $EchoName, 'vlist' => $this->getCheckboxInfoBySql($GetValue, 0)), 'css_checkbox');
                $fromdata[$FieldName] = ($dvalue == "0") ? "" : $dvalue;
                break;
            case "SELECT":
                $fromtitle[$FieldName] = array('select', '', array('name' => $EchoName, 'vlist' => $this->getSelectInfo($GetValue, $dvalue)), 'css_select');
                $fromdata[$FieldName] = ($dvalue == "0") ? "" : $dvalue;
                break;
            case "TEXTAREA":
                $fromtitle[$FieldName] = array('textarea', '', $EchoName, 'css_textarea');
                $fromdata[$FieldName] = ($dvalue == "0") ? "" : $dvalue;
                break;
            case "HTML":
                $fromtitle[$FieldName] = array('textarea', $FieldType, $EchoName, 'css_textarea');
                $fromdata[$FieldName] = ($dvalue == "0") ? "" : $dvalue;
                break;
            case "MIMG":
                $fromtitle[$FieldName] = array('input', $FieldType, $EchoName, 'css_textimage');
                $fromdata[$FieldName] = ($dvalue == "0") ? "" : $dvalue;
                break;
            default:
                break;
        }
        $result['title'] = $fromtitle;
        $result['data'] = $fromdata;
        return $result;
    }

    /*
		 * 获得Radio的信息数组
		 * @data:$v传入的表达式串 $type返回的类型：0为值 1为显示内容
		 * @author:zhangzhibin
		 */
    public function getRadioInfo($v, $type) {
        $varr = explode(";", $v);
        $result = array();
        foreach ($varr as $k => $val) {
            $tempv = explode("=", $val);
            if ($type == 0) {
                $result[] = $tempv[0];
            } else {
                $result[$tempv[0]] = $tempv[1];
            }
        }
        return $result;
    }


    /*
		 * 获得Checkbox的信息数组
		* @data:$v传入的表达式串 $type返回的类型：0为值 1为显示内容
		* @author:zhangzhibin
		*/
    public function getCheckboxInfo($v, $type) {
        $varr = explode(";", $v);
        $result = array();

        foreach ($varr as $k => $val) {
            $tempv = explode("=", $val);
            if ($type == 0) {
                $result[] = $tempv[0];
            } else {
                $result[$tempv[0]] = $tempv[1];
            }
        }
        return $result;
    }


    /*
		 * 获得Checkbox的信息数组
		* @data:$v传入的sql语句
		* @author:zhangzhibin
		*/
    public function getCheckboxInfoBySql($v) {
        $ST = M();
        if (strpos($v, ';')) {
            $SArr = explode(';', $v);
            foreach ($SArr as $key => $value) {
                $fvalArr = explode('=', $value);
                $resultlist[$fvalArr[0]] = $fvalArr[1];
            }
        } else {
            $sql = $this->changeSql($v);
            $SArr = $ST->query($sql);
            foreach ($SArr as $ka => $va) {
                $resultlist[$va['id']] = $va['title'];
            }
        }
        return $resultlist;
    }


    /*
		 * 获得Select的信息数组
		* @data:$v传入的sql语句 
		* @author:zhangzhibin
		*/
    public function getSelectInfo($v) {
        //exit;
        $ST = M();
        //msubstr($str, $start=0, $length, $charset="utf-8″, $suffix=true)
        $sqlstatus = $this->getSelectSqlStatus($v);
        $sql = $this->changeSql($v);
        if ($sqlstatus == 1) {//如果sql语句中有pid
            $SArr = $ST->query($v);
            //var_dump($v);
            $result = $this->getMenuTree($SArr);
            foreach ($result as $k => $value) {
                switch ($value['level']) {
                    case 0:
                        $resultlist .= "根 节 点";
                        break;
                    case 1:
                        $resultlist[$value['id']] = "|—" . $value['title'];
                        break;
                    case 2:
                        $resultlist[$value['id']] = "&nbsp;&nbsp;├" . $value['title'];
                        break;
                    case 3:
                        $resultlist[$value['id']] = "&nbsp;&nbsp;&nbsp;&nbsp;┖" . $value['title'];
                        break;
                    default:
                        break;
                }
            }
        } else {
            $SArr = $ST->query($sql);
            foreach ($SArr as $ka => $va) {
                $resultlist[$va['id']] = $va['title'];
            }
        }
        //	print_r($resultlist);

        return $resultlist;
    }


    function getSelectSqlStatus($sql) {//获取selectsql是否带父类ID（pid）)
        $len1 = strpos($sql, "from") - 7;
        $result_value = substr($sql, 6, $len1);
        $result_value = substr_count($result_value, ",");
        if ($result_value == 2) {
            $result_status = 1;
        } else {
            $result_status = 0;
        }
        return $result_status;
    }

    function changeSql($sql) {//转换sql语句
        $len1 = strpos($sql, "from") - 7;
        $result_value = substr($sql, 6, $len1);
        $sql_end = substr($sql, strpos($sql, "from"));
        $arr = explode(",", $result_value);
        $result_sql = "select " . $arr[0] . " as id," . $arr[1] . " as title " . $sql_end;
        return $result_sql;
        //$result_value=substr_count($result_value,",");

    }

    function getMenuTree($arrCat, $parent_id = 0, $level = 0) {
        static $arrTree = array(); //使用static代替global
        if (empty($arrCat)) return FALSE;
        $level++;
        foreach ($arrCat as $key => $value) {
            if ($value['pid'] == $parent_id) {
                $value['level'] = $level;
                $arrTree[] = $value;
                unset($arrCat[$key]); //注销当前节点数据，减少已无用的遍历
                $this->getMenuTree($arrCat, $value['id'], $level);
            }
        }
        return $arrTree;
    }


    /*数据层级化，
		 * @Data:$list需要层级话的数组，包含id，pid
		 * 返回数组
		 */
    function findChildArr($list, $p_id) {
        $r = array();
        foreach ($list as $id => $item) {
            if ($item['pid'] == $p_id) {
                $length = count($r);
                $r[$length] = $item;
                if ($t = $this->findChildArr($list, $item['id'])) {
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
    public function getMainNodeArr($nodeid) {

        return M("node")->field("MainTable,MainRelationFieldName,AsField,InnerJoinSql,QueryFiterSql,OrderBySql,LimitBySql,GroupBySql,ListHaveEdit,ListHaveDel,title,ListHaveCopy,ListHaveCopyToContent")->where("DelSign<>1 and id=" . $nodeid . "")->find();
    }

    /*
		 * 获得编辑的字段信息数组
		 * @data:nodeid节点ID
		 * @author：zhangzhibin
		 * @return:数据集
		 */
    public function getEditField($nodeid) {
        $MC = M("node_modulecontent");
        $result = $MC->where("NodeId=" . $nodeid)->order('EchoOrder')->select();
        //var_dump($MC->getLastSql());
        return $result;
    }

    public function maketable() {
        $_tmp = new MakeTableModel();
        $_datam = array('0' => array('ml_id' => '1', 'name' => 'aasdfldf', 'count' => '11', 'date' => '02-12'), '1' => array('ml_id' => '2', 'name' => 'aasdfldf', 'count' => '11', 'date' => '02-12'));
        $_data = array('ml_id' => '1', 'name' => '0', 'count' => '2', 'date' => '02-12', 'hao' => '1|2', 'texts' => 'sdfsdfasdfasdfasdfsadf');
        $_data_blank = array();

        $_cont = array('control' => array('con_list' => array('modi' => '修改', 'dele' => '删除'), 'con_parse' => array('/index.php?module=xxx&action=aaa', 'const')));

        $_titl = array('name' => array('input', 'radio', array('name' => '性别', 'vlist' => array('男', '女')), 'css1'), 'count' => array('select', '', array('name' => '地区', 'vlist' => array('中国', '美国', '小日本')), 'css2'), 'date' => array('input', 'text', '日期', 'css3'), 'texts' => array('textarea', '', '说明', 'css4'), 'hao' => array('input', 'checkbox', array('name' => '爱好', 'vlist' => array('读书', '写字')), 'css5'));
        $_titlt = array('name' => '名称', 'count' => '数量', 'date' => '日期');
        $_titls = array('name' => array('名称', 'checkbox'), 'count' => '数量', 'date' => '日期');

        $_tags = array('tags' => array('name' => 'fieldset', 'attr' => array('class' => 'list')),
            'tagsrow' => array('name' => 'div', 'rules' => array('class' => array('title1', 'list_self'), 'rul' => 0)),
            'tagscol' => array('name' => 'label'),
            'tagstitle' => array('name' => 'legend', 'title' => '修改数据'),
            'tagsform' => array('attr' => array('action' => '/index.php', 'name' => 'myform'),
                'button' => array('submit', 'submit', '提交'),
                'custom' => '<input name="hidden1" type="hidden" value="hidden1" /><input name="hidden2" type="hidden" value="hidden2" />')
        );
        $_tagb = array('tags' => array('name' => 'fieldset', 'attr' => array('class' => 'list')),
            'tagsrow' => array('name' => 'div', 'rules' => array('class' => array('title1', 'list_self'), 'rul' => 0)),
            'tagscol' => array('name' => 'label'),
            'tagstitle' => array('name' => 'legend', 'title' => '添加数据'),
            'tagsform' => array('attr' => array('action' => '/index.php', 'name' => 'myform'), 'button' => array('submit', 'submit', '提交'))
        );
        $_tagl = array('tags' => array('name' => 'div', 'attr' => array('class' => 'list')),
            'tagsrow' => array('name' => 'ul', 'rules' => array('class' => array('title1', 'list_self'), 'rul' => 0)),
            'tagscol' => array('name' => 'li'),
            'tagstitle' => array('name' => 'h1', 'title' => '添加数据'),
            'tagsform' => array('attr' => array('action' => '/index.php', 'name' => 'myform'), 'button' => array('submit', 'submit', '提交'))
        );
        $_tagm = array('tags' => array('name' => 'table', 'attr' => array('class' => 'list')),
            'tagsrow' => array('name' => 'tr', 'rules' => array('class' => array('title1', 'list_self'), 'rul' => 0)),
            'tagscol' => array('name' => 'td'),
            'tagstitle' => array('name' => 'span', 'title' => '修改数据'),
            'tagsform' => array('attr' => array('action' => '/index.php', 'name' => 'myform'), 'button' => array(array('button1', 'button', '全选'), array('button2', 'button', '反选'), array('button3', 'button', '删除')))
        );
        $_tagt = array('tags' => array('name' => 'table', 'attr' => array('class' => 'list')),
            'tagsrow' => array('name' => 'tr', 'rules' => array('class' => array('title1', 'list_self'), 'rul' => 0)),
            'tagscol' => array('name' => 'td', 'rules' => array('class' => array('title1', 'list_self'), 'rul' => 0)),
            'tagstitle' => array('name' => 'span', 'title' => '修改数据'),
            'tagsform' => array('attr' => array('action' => '/index.php', 'name' => 'myform'))
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
        $_tmpb = $_tmp->setData($_data_blank)->setTags($_tagb)->showTableSubmit();                            //设置空的添加
        $_tmpl = $_tmp->setTags($_tagl)->showTableSubmit();
        $_tmpm = $_tmp->setData($_datam)->setTitle($_titls)->setTags($_tagm)->showTableList(); //list带全选的
        $_tmpt = $_tmp->setData($_datam)->setTitle($_titlt)->setTags($_tagt)->showTableList();    // list 不带全选的


        //echo $_tmps;  // table 标签实现的修改数据提交表单
        //echo $_tmpe;  // DIV 标签实现的修改数据提交表单
        //echo $_tmpb;  // DIV 标签实现的添加数据提交表单
        //echo $_tmpl;  // DIV UL 标签实现的添加数据提交表单
        //echo $_tmpm;  // table 标签实现的带checkbox 带按钮的数据显示列表
        //echo $_tmpt;  // table 标签实现的不带checkbox的数据显示

        $this->assign("tmps", $_tmps);
        //$this->assign("tmpb",$_tmpb);
        //$this->assign("tmpl",$_tmpl);
        //$this->assign("tmpm",$_tmpm);
        //$this->assign("tmpt",$_tmpt);
        $this->display();
    }

    /*
		 * 图片上传
		 */
    public function avatarupload() {
        $uid = I('uid', 0, 'intval');
        $Users = new UsersModel ();
        $Users->find($uid);
        $UP = $Users->getUserProfile();
        //$UA = $Users->getUserAcc ();
        $userBasic = $Users->data();
        $userBasic['u_avatar_96'] = str_replace("/o/", "/s/96_96_", $userBasic['u_avatar']);
        //var_dump($UP->data());
        $this->assign('up', $UP->data());
        $this->assign('userBasic', $userBasic);
        $this->display();
    }

    /*
		 * 多图上传
		 */
    function uploadImg() {
        //exit;
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize = 31457280;// 设置附件上传大小
        $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $savepath = './upload/image/' . date('Ymd') . '/';
        if (!file_exists($savepath)) {
            mkdir($savepath);
        }
        $upload->savePath = $savepath;// 设置附件上传目录
        if (!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        } else {//上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            //-------------------------------------图片信息写入数据库 start
            $SI = new ImageModel();
            $img_id = $SI->saveImg($info[0]);
            //-------------------------------------图片信息写入数据库 end
        }
        $pArr['id'] = $img_id;
        $pArr['imgpath'] = J(__ROOT__ . '/' . $info[0]['savepath'] . '/' . $info[0]['savename']);
        $pinfo = json_encode($pArr);
        print_r($pinfo);//输出到页面
        //print_r(J(__ROOT__.'/'.$info[0]['savepath'].'/'.$info[0]['savename']));

    }

    function del() {
        $src = str_replace(__ROOT__ . '/', '', str_replace('//', '/', $_GET['src']));
        if (file_exists($src)) {
            unlink($src);
        }
        //var_dump($_GET['id']);
        print_r($_GET['id']);
        exit();
    }


    /**
     * 上传头像
     */
    public function uploadAvatar() {
        $uid = I('get.uid', 0, 'intval');

        /*
			 * IMG RESIZE SELECT $upload =
			* 'D:\Zend\WorkSpace\city\tmp_upload\large'; if (move_uploaded_file (
				 * $_FILES ['upl'] ['tmp_name'], $upload . '\\' . $_FILES ['upl']
				 * ['name'] )) { $large_img = '/city/tmp_upload/large/' . $_FILES
			* ['upl'] ['name']; list ( $width, $height ) = getimagesize ( $upload .
				 * '\\' . $_FILES ['upl'] ['name'] ); echo '{"status":"success","url":"'
			* . $large_img . '","width":"' . $width . '","height":"' . $height .
			* '"}'; exit (); }
			*/
        $MD5File16Name = getMD5File16($_FILES ['upl'] [tmp_name]);
        import("ORG.Net.UploadFile");
        $upload = new UploadFile ();
        $upload->uploadReplace = true;
        $upload->maxSize = 3145728; // 头像文件大小限制3M
        $upload->allowExts = array(
            'png',
            'jpg',
            'jpeg',
            'gif'
        ); // 头像文件仅支持jpg格式
        $upload->saveRule = $MD5File16Name . '';
        $upload->thumb = true;
        $upload->thumbMaxWidth = '96,24,180';
        $upload->thumbMaxHeight = '96,24,180';
        $genAvatarPath = getSavePathByID($uid);
        // 上传路径
        $upload->savePath = './upload/avatar/' . $genAvatarPath . 'o/';
        // 缩略图上传路径
        $upload->thumbPath = './upload/avatar/' . $genAvatarPath . 's/';
        $upload->thumbPrefix = '96_96_,24_24_,180_180_';
        $upload->thumbSuffix = '';
        // miaomin added@2014.3.18
        $upload->thumbType = 1;
        if (!$upload->upload()) {
            // TODO				// AJAX的响应会有一个专门的方法来处理
            echo json_encode($upload->getErrorMsg());
        } else {
            $info = $upload->getUploadFileInfo();
            $savename = $info [0] ['savename'];
            $savename_arr = explode('.', $savename);
            // $info [0] ['thumbname'] = $savename_arr [0] . '_200.' . $savename_arr [1];
            $info [0] ['thumbname'] = '96_96_' . $savename_arr [0] . '.' . $savename_arr [1];
            // $info [0] ['thumbsrc'] = TMP_UPLOAD_PATH . '/avatar/' . $genAvatarPath . 's/' . $info [0] ['thumbname'];
            $info [0] ['thumbsrc'] = TMP_UPLOAD_PATH . '/avatar/' . $genAvatarPath . 's/' . $info [0] ['thumbname'];
            // 保存图片
            $Users = D('Users');
            $Users->find($uid);
            // $Users->u_avatar = $genAvatarPath . 's/' . $info [0] ['thumbname'];
            $Users->u_avatar = $genAvatarPath . 'o/' . $savename_arr [0] . '.' . $savename_arr [1];
            $Users->save();
            //var_dump($Users->u_avatar);
            echo header("Content-type:text/html;charset=utf-8");
            echo json_encode($info);
        }
    }


    public function doexcel() {
        $TP = new ProductModel();
        //echo header("Content-type:text/html;charset=utf-8");
        $getString = I('ssq', '0', 'string');
        $getString = urldecode($getString);
echo $getString;
        $getStringArr = explode('-', $getString);
        //echo "<br>";
        //echo substr($getString,0,strpos($getString,'-'));
        $sql = base64_decode($getStringArr[0]);
        $xlsData = M()->query($sql);
        $cellString = $getStringArr[1];
        $cellArr = explode(',', $cellString);
        $xlsCell[] = $cellArr;
        $i = 0;
        foreach ($xlsData[0] as $kid => $vid) {
            $keyid[$i] = $kid;
            $i++;
        }
        echo $sql;
        exit;
        $xlsName = $getStringArr[2];
        // exit;
        //var_dump($keyid);
        foreach ($cellArr as $key => $value) {
            $xlsCell[$key] = array($keyid[$key], $value);
        }
        // $xlsCell=$cellArr;

        foreach ($xlsData as $kdata => $vdata) {
            $xlsData[$kdata]['up_orderid'] = " " . $vdata['up_orderid'];
            $xlsData[$kdata]['up_orderbackid'] = " " . $vdata['up_orderbackid'];
            $temparr = unserialize($vdata['up_productid']);
            $xlsData[$kdata]['up_productid'] = $TP->getProductNameByArr($temparr);

        }

        //var_dump($xlsData);
        //exit;
        //$getString=urldecode($aa);
        //  echo "<br>".$cellString;
        // exit;
        //$listString=I('list','0','string');
        //echo $listString;
        //echo($getString);
        // exit;
        import("PHPExcelEx");
        $excel = new PHPExcelEx();
        $excel->exportExcel($xlsName, $xlsCell, $xlsData);


    }


    /*public function excel(){
        import("PHPExcelEx");
        $excel = new PHPExcelEx();
        $xlsName  = "User";
        $xlsCell  = array(
            array('id','账号序列'),
            array('truename','名字'),
            array('sex','性别'),
            array('res_id','院系'),
            array('sp_id','专业'),
            array('class','班级'),
            array('year','毕业时间'),
            array('city','所在地'),
            array('company','单位'),
            array('zhicheng','职称'),
            array('zhiwu','职务'),
            array('jibie','级别'),
            array('tel','电话'),
            array('qq','qq'),
            array('email','邮箱'),
            array('honor','荣誉'),
            array('remark','备注')
        );
        print_r($xlsCell);
        $xlsData[0]['sex']='1';
        $xlsData[1]['sex']='1';
        $xlsData[2]['sex']='1';
        print_r($xlsData);
        exit;
        $excel->exportExcel($xlsName,$xlsCell,$xlsData);
        echo "abc";
    }*/

    //同步新增数据到外网
    public function synDiyAllDataToWeb() {
        $Pnodeid = I("get.Pnodeid", 0, "intval");
        $nodeid = I("get.nodeid", 0, "intval");
        $searchtype = I("get.searchtype", "0", "intval");
        $ID = I('id');
        $TDCM = M('diy_cate');
        $TDUM = M('diy_unit');

        $diyCateInfo = $TDCM->where("cid=" . $ID . "")->find();
        $diyUnitInfo = $TDUM->where("cid=" . $ID . "")->select();
        //print_r($diyCateInfo);
        //print_r($diyUnitInfo);
//exit;
        $result = $this->saveDiyAllDb2($diyCateInfo, $diyUnitInfo);
        if (!$result) {
            //echo "ID已经存在,同步数据成功！";
            $this->success("ID存在,同步数据成功!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
        } else {
            $this->success("新增数据成功!", U('/empty/ContentList/Pnodeid/' . $Pnodeid . '/nodeid/' . $nodeid . '/searchtype/' . $searchtype . '/keywords/' . $keywords), 1);
        }
        echo $ID;
    }


    private function saveDiyAllDb2($diyCateInfo, $diyUnitInfo) {
        $TDCM_db2 = M("diy_cate", "tdf_", "DB_CONFIG2");
        $TDUM_db2 = M("diy_unit", "tdf_", "DB_CONFIG2");
        $diyCateInfo_db2 = $TDCM_db2->where("cid=" . $diyCateInfo['cid'] . "")->find();
        if ($diyCateInfo_db2) {//执行同步
            $result = 0;
            $result = $TDCM_db2->where("cid=" . $diyCateInfo['cid'] . "")->save($diyCateInfo);
            foreach ($diyUnitInfo as $key => $value) {
                $TDUM_db2->where("id=" . $value['id'] . "")->save($value);
            }
        } else {//执行新增
            $result = $TDCM_db2->add($diyCateInfo);
            foreach ($diyUnitInfo as $key => $value) {
                //unset($value['id']);
                //print_r($value);
                $TDUM_db2->add($value);
            }
        }
        return $result;
    }

//测试json增加产品
    public function productAdd(){
        $mainProductInfo=M('product')->field('p_id,p_price')->where('p_id=33123')->find();
        //var_dump($productInfo);
        $productInfoBlong=M('product')->field('p_id,p_price,p_belongpid')->where("p_belongpid=".$mainProductInfo['p_id']."")->select();
        $mainProductInfo['plist']=$productInfoBlong;
        print_r($mainProductInfo);
        $this->assign('mainProductInfo',$mainProductInfo);
        $this->display();

    }

  
}






