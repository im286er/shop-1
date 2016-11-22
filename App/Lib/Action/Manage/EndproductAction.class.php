<?php

/**
 * 垂直销售类商品管理
 *
 * @author miaomin 
 * Dec 12, 2014 11:12:32 AM
 *
 * $Id$
 */
class EndproductAction extends CommonAction
{

    /**
     * 首页
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 一键上线
     */
    public function pubdetail()
    {
        try {
            $Pid = I('get.id');
            $PM = new ProductModel();
            $condition = array(
                $PM->F->BelongPid => $Pid
            );
            $saveData = array(
                $PM->F->Slabel => 1
            );
            if ($Pid) {
                $PM->where($condition)->save($saveData);
            }
            redirect(__APP__ . "/Endproduct/listdetail/id/" . $Pid);
        } catch (Exception $e) {}
    }

    /**
     * 一键下线
     */
    public function offdetail()
    {
        try {
            $Pid = I('get.id');
            $PM = new ProductModel();
            $condition = array(
                $PM->F->BelongPid => $Pid
            );
            $saveData = array(
                $PM->F->Slabel => 0
            );
            if ($Pid) {
                $PM->where($condition)->save($saveData);
            }
            redirect(__APP__ . "/Endproduct/listdetail/id/" . $Pid);
        } catch (Exception $e) {}
    }

    /**
     * 专属定制
     */
    public function customize()
    {
        try {
            $UCUM = new UserCustomizeModel();
            $ucumRes = $UCUM->getCustomizeList();
            
            // cList处理
            foreach ($ucumRes as $key => $val) {
                $pidsStr = arrayTransToStr($val['pids'], ',');
                $ucumRes[$key]['pids'] = $pidsStr;
            }
            
            $this->assign('cList', $ucumRes);
            $this->display();
        } catch (Exception $e) {}
    }

    /**
     * 移除专属定制
     */
    public function removecustomize()
    {
        try {
            $uid = I('id');
            
            $UCUM = new UserCustomizeModel();
            $ucumRes = $UCUM->removeCustomizeList($uid);
            
            redirect(__APP__ . "/Endproduct/customize");
        } catch (Exception $e) {}
    }

    /**
     * 编辑专属定制
     */
    public function editcustomize()
    {
        try {
            if ($this->isPost()) {
                
                // 数据校验
                $verifyRes = $this->_verifyCustomize($_POST);
                
                if (! $verifyRes) {
                    $this->_ajaxResponse(0, '请输入正确的信息');
                }
                
                // 专属定制
                $username = I('post.username', '', 'htmlspecialchars');
                $pids = I('post.productids', '', 'htmlspecialchars');
                
                $pidsArr = explode(',', $pids);
                
                $UCUM = new UserCustomizeModel();
                $ucumRes = $UCUM->setCustomizePids($username, $pids);
                
                $ucumRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
            } else {
                $uid = I('id');
                
                $UCUM = new UserCustomizeModel();
                $ucumRes = $UCUM->getCustomizeList('tdf_users.u_id = "' . $uid . '"');
                
                // cList处理
                foreach ($ucumRes as $key => $val) {
                    $pidsStr = arrayTransToStr($val['pids'], ',');
                    $ucumRes[$key]['pids'] = $pidsStr;
                }
                
                $this->assign('cList', $ucumRes);
                $this->display();
            }
        } catch (Exception $e) {}
    }

    /**
     * 添加或者编辑专属定制
     */
    public function addcustomize()
    {
        try {
            if ($this->isPost()) {
                
                // 数据校验
                $verifyRes = $this->_verifyCustomize($_POST);
                
                if (! $verifyRes) {
                    $this->_ajaxResponse(0, '请输入正确的信息');
                }
                
                // 专属定制
                $username = I('post.username', '', 'htmlspecialchars');
                $pids = I('post.productids', '', 'htmlspecialchars');
                
                $pidsArr = explode(',', $pids);
                
                $UCUM = new UserCustomizeModel();
                $ucumRes = $UCUM->setCustomizePids($username, $pids);
                
                $ucumRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
            } else {
                $this->display();
            }
        } catch (Exception $e) {}
    }

    /**
     * 快捷修改上下架
     */
    public function opdetailstat()
    {
        try {
            if ($this->isGet()) {
                $Pid = I('get.pid');
                $Stat = I('get.status');
                
                $PM = new ProductModel();
                $PM->find($Pid);
                if ($Stat) {
                    $newStat = 0;
                } else {
                    $newStat = 1;
                }
                $PM->{$PM->F->Slabel} = $newStat;
                $pmSave = $PM->save();
                
                $pmSave ? $this->_ajaxResponse(1, '信息保存成功', '', array(
                    'status' => $newStat
                )) : $this->_ajaxResponse(0, '信息保存失败');
            }
        } catch (Exception $e) {}
    }

    /**
     * 快捷修改显示排序
     */
    public function opdispweight()
    {
        try {
            if ($this->isPost()) {
                $Pid = I('post.pid');
                $Dispweight = I('post.dispweight');
                
                $PM = new ProductModel();
                $PM->find($Pid);
                $PM->{$PM->F->Dispweight} = $Dispweight;
                $PM->save();
            }
        } catch (Exception $e) {}
    }

    /**
     * 快捷修改价格
     */
    public function opdetialprice()
    {
        try {
            if ($this->isPost()) {
                $Pid = I('post.pid');
                $Price = I('post.price');
                
                $PM = new ProductModel();
                $PM->find($Pid);
                $PM->{$PM->F->Price} = $Price;
                $PM->save();
            }
        } catch (Exception $e) {}
    }

    /**
     * 商品明细选择器
     */
    public function selectdetail()
    {
        try {
            if ($this->isGet()) {
                
                // PID
                $Pid = I('get.id');
                
                // Init
                $PM = new ProductModel();
                $PMPM = new ProductMainPropModel();
                $Product = $PM->getNoneDiyProductInfoByID($Pid);
                $listAvDetail = $PM->getBelongAvProductList($Pid);
                $listProp = $PMPM->getPropByMainType($Product[$PM->F->MainType]);
                
                // 根据属性权限重排
                foreach ($listAvDetail as $key => $val) {
                    
                    $newPropIdSpec = '';
                    
                    $specArr = explode('#', $val[$PM->F->PropIdSpec]);
                    $tempArr = array();
                    foreach ($specArr as $k => $v) {
                        $specDetailArr = explode(':', $v);
                        $tempArr[$specDetailArr[0]] = $specDetailArr[1];
                    }
                    
                    foreach ($listProp as $k => $v) {
                        $newPropIdSpec .= $v[$PMPM->F->ID] . ':' . $tempArr[$v[$PMPM->F->ID]] . '#';
                    }
                    
                    if (substr($newPropIdSpec, - 1) == '#') {
                        $newPropIdSpec = substr($newPropIdSpec, 0, - 1);
                    }
                    
                    $listAvDetail[$key][$PM->F->PropIdSpec] = $newPropIdSpec;
                }
                
                // 获取商品明细
                $detailPropArr = array();
                foreach ($listAvDetail as $key => $val) {
                    $specArr = explode('#', $val[$PM->F->PropIdSpec]);
                    $tempArr = array();
                    foreach ($specArr as $k => $v) {
                        $specDetailArr = explode(':', $v);
                        $tempArr[$specDetailArr[0]] = $specDetailArr[1];
                    }
                    $detailPropArr[$val[$PM->F->ID]]['spec'] = $tempArr;
                }
                
                // print_r ( $detailPropArr );
                
                // 属性选择器
                $res = $this->_recurGenPropArr($detailPropArr);
                
                print_r(json_encode($res));
            }
        } catch (Exception $e) {}
    }

    /**
     * 构成属性选择器数组
     *
     * @param array $detailPropArr            
     */
    private function _recurGenPropArr($detailPropArr)
    {
        $res = array();
        
        $PM = new ProductModel();
        
        foreach ($detailPropArr as $key => $val) {
            $testArr = $val['spec'];
            
            $condition = array(
                $PM->F->ID => $key
            );
            $pmRes = $PM->where($condition)
                ->field($PM->F->ID . ',' . $PM->F->Price)
                ->find();
            
            $evalStr = "\$res";
            foreach ($testArr as $key => $val) {
                $evalStr .= "[" . $val . "]";
            }
            $evalStr .= " = \$pmRes;";
            
            eval($evalStr);
        }
        
        return $res;
    }

    /**
     * 生成商品明细
     */
    public function gendetail()
    {
        try {
            $Pid = I('get.id');
            $PM = new ProductModel();
            $listDetail = $PM->getBelongProductList($Pid);
            $Product = $PM->getNoneDiyProductInfoByID($Pid);
            
            //
            $PMTM = new ProductMainTypeModel();
            $mainType = $PMTM->find($Product[$PM->F->MainType]);
            
            // 不能根据权重排序必须要以ID排序因为权重会改ID是固定的
            $PMPM = new ProductMainPropModel();
            $condition = array(
                $PMPM->F->MAINTYPE => $Product[$PM->F->MainType]
            );
            $listProp = $PMPM->where($condition)
                ->order($PMPM->F->ID . ' asc')
                ->select();
            
            $PPVM = new ProductPropValModel();
            
            if ($listProp) {
                $propArr = array();
                foreach ($listProp as $key => $val) {
                    $listProp[$key][$PMPM->F->PROPVALS] = jsonstrTransToStr($listProp[$key][$PMPM->F->PROPVALS]);
                    
                    // 处理propArr
                    $condition = array(
                        $PPVM->F->PROPID => $val['ipp_id']
                    );
                    $valRes = $PPVM->where($condition)->select();
                    
                    $propArr[$val['ipp_id']] = $valRes;
                }
                
                $PropSpecArr = array();
                foreach ($propArr as $key => $val) {
                    $tempArr = array();
                    foreach ($val as $k => $v) {
                        $tempArr[] = $key . ':' . $v['ipv_id'];
                    }
                    $PropSpecArr[] = $tempArr;
                }
                
                // 生成排列组合数组
                $combineArr = combos($PropSpecArr);
                
                // print_r($combineArr);
                
                $insertQuery = '';
                foreach ($combineArr as $key => $val) {
                    $specStr = '';
                    
                    foreach ($val as $k => $v) {
                        $specStr .= $v . '#';
                    }
                    
                    if (substr($specStr, - 1) == '#') {
                        $specStr = substr($specStr, 0, - 1);
                    }
                    // 准备INSERT语句
                    $insertQuery .= "INSERT INTO " . $PM->F->_Table . "(" . $PM->F->Name . "," . $PM->F->Creater . "," . $PM->F->Price . "," . $PM->F->CreateDate . "," . $PM->F->CreateTime . "," . $PM->F->LastUpdate . "," . $PM->F->LastUpdateTime . "," . $PM->F->ProductType . "," . $PM->F->MainType . "," . $PM->F->BelongPid . "," . $PM->F->PropIdSpec . "," . $PM->F->Cover . "," . $PM->F->Cover_ID . ") VALUES ('" . addslashes($Product[$PM->F->Name]) . "','" . $Product[$PM->F->Creater] . "','" . $Product[$PM->F->Price] . "','" . get_now() . "','" . time() . "','" . get_now() . "','" . time() . "','" . $Product[$PM->F->ProductType] . "','" . $Product[$PM->F->MainType] . "','" . $Product[$PM->F->ID] . "','" . $specStr . "','" . $Product[$PM->F->Cover] . "','" . $Product[$PM->F->Cover_ID] . "');";
                }
                // echo $insertQuery;
                
                if (count($listDetail)) {
                    // 删除后重新刷新
                    $condition = array(
                        $PM->F->BelongPid => $Pid
                    );
                    $PM->where($condition)->delete();
                    $PM->execute($insertQuery);
                } else {
                    // 新生成
                    $PM->execute($insertQuery);
                }
                
                // 成功后将BelongPid slabel置为1
                if ($Product[$PM->F->Slabel] == 0) {
                    $PM->find($Pid);
                    $PM->{$PM->F->Slabel} = 1;
                    $PM->save();
                }
            }
            
            redirect(__APP__ . '/Endproduct/listdetail/id/' . $Pid);
        } catch (Exception $e) {}
    }

    /**
     * 列表商品明细
     */
    public function listdetail()
    {
        try {
            $Pid = I('get.id');
            $PM = new ProductModel();
            $listTable = $PM->getBelongProductList($Pid);
            $Product = $PM->getNoneDiyProductInfoByID($Pid);
            
            foreach ($listTable as $key => $val) {
                $listTable[$key][$PM->F->PropNameSpec] = ProductPropValModel::parseCombinePropVals($val[$PM->F->PropIdSpec]);
            }
            
            // 赋值
            $this->assign('Product', $Product);
            $this->assign('listCount', count($listTable));
            $this->assign('listTable', $listTable);
            $this->display();
        } catch (Exception $e) {}
    }

    /**
     * 列表商品
     */
    public function listproduct()
    {
        try {
            load('@.Paging');
            load("@.SearchParser");
            $SP = new SearchParser();
            
            if ($this->isPost()) {
                if ($SP->parseSearchInfo(true)) {
                    // print_r($_POST);
                    $Url = $SP->getFormattedUrl();
                    // var_dump($Url);
                    // exit();
                    $RedirectUrl = __APP__ . '/Endproduct/listproduct/?' . substr($Url['url'], 1);
//                     echo $RedirectUrl;
//                     exit;
                    redirect($RedirectUrl);
                }
            } else {
                 //print_r($_GET);
                $SP->parseUrlInfo(true);
                 //print_r($SP->SearchInfo);
                $PSM = new ProductSearchModel($SP->SearchInfo, 'nonediy', false);
                $PSM->DisplayFields = 'tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_price,tdf_product.p_dispweight,tdf_product.p_createdate,tdf_product.p_lastupdate,tdf_product.p_views_disp,tdf_product.p_score,tdf_product.p_zans,tdf_product.p_maintype,tdf_product.p_slabel,tdf_product.p_onsaleintro,tdf_product.p_wpid,tdf_product_waterproof.pwp_title,tdf_info_producttype.ipt_name,tdf_users.u_dispname,tdf_users.u_realname,tdf_users.u_title,tdf_users.u_avatar';
                $searchResult = $PSM->getResult($SP->SearchInfo['page']);
                foreach ($searchResult as $key => $val) {
                    $pattern = '/\/o\//';
                    $searchResult[$key]['p_cover'] = preg_replace($pattern, '/s/64_64_', $searchResult[$key]['p_cover']);
                }
                // 控件
                $PWPM = new ProductWaterProofModel();
                $pwpCtrlDef = $_GET['wp'] ? $_GET['wp'] : 0;
                $pwpCtrl = $PWPM->getOptionCtrl(array(
                    '1=1',
                    $PWPM->F->ISENABLED => 1
                ), $pwpCtrlDef);
                // 控件
                $PCM = new ProductCateModel();
                $pcOptionCtrlDef = $_GET['cate'] ? $_GET['cate'] : null;
                $pcOptionCtrl = $PCM->getOptionCtrl(array(
                    '1=1',
                    'pc_parentid' => '1263'
                ),$pcOptionCtrlDef);
                // 控件
                $slabArr = array(
                    array(
                        'value' => '1',
                        'key' => '发布'
                    ),
                    array(
                        'value' => '2',
                        'key' => '未发布'
                    )
                );
                if ($_GET['audit'] === 'all'){
                    $slabCtrlDef = null;
                }else{
                    $slabCtrlDef = $_GET['audit'];
                }
                $slabCtrl = get_dropdown_option($slabArr, $slabCtrlDef);
                
                //
                if ($_GET['tlike']) {
                    $this->assign('producttitle', $_GET['tlike']);
                }
                
                //
                if ($_GET['lprice']) {
                    $this->assign('lo_price', $_GET['lprice']);
                }
                
                //
                if ($_GET['hprice']) {
                    $this->assign('hi_price', $_GET['hprice']);
                }
            }
            // 赋值
            $this->assign('pslabctrl', $slabCtrl);
            $this->assign('pwpctrl', $pwpCtrl);
            $this->assign('pcatectrl', $pcOptionCtrl);
            $this->assign('listTable', $searchResult);
            $this->assign('PI', getPagingInfo($PSM->TotalCount, $SP->SearchInfo['page'], $SP->SearchInfo['count'], 4));
            $Url = $SP->getFormattedUrl();
            $this->assign('BaseUrl', U('Manage/Endproduct/listproduct/?' . substr($Url['url'], 1)));
            $this->display();
        } catch (Exception $e) {}
    }

    /**
     * 添加新商品
     */
    public function addproduct()
    {
        try {
            if ($this->isPost()) {
                // 数据校验
                $verifyRes = $this->_verifyEndProduct($_POST);
                if (! $verifyRes) {
                    $this->_ajaxResponse(0, '请输入正确的信息');
                } else {
                    
                    $PRODUCTTYPE = C('PRODUCT.TYPE');
                    
                    // 添加商品
                    $PM = new ProductModel();
                    $PM->create($_POST);
                    $PM->{$PM->F->ProductType} = $PRODUCTTYPE['NDIY'];
                    $PM->{$PM->F->CreateDate} = get_now();
                    $PM->{$PM->F->CreateTime} = time();
                    $PM->{$PM->F->LastUpdate} = get_now();
                    $PM->{$PM->F->LastUpdateTime} = time();
                    $addRes = $PM->add();
                    
                    if ($addRes) {
                        // 标签信息
                        $tags_all = trim($_POST['tags']);
                        $tags_array = explode(' ', $tags_all);
                        
                        $PT = D('ProductTags');
                        $PTI = D('ProductTagsIndex');
                        foreach ($tags_array as $key => $val) {
                            $pt_id = $PT->addTag($val);
                            // 标签索引
                            $PTI->create();
                            $PTI->pt_id = $pt_id;
                            $PTI->p_id = $addRes;
                            $PTI->add();
                        }
                    }
                    
                    $addRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
                }
            } else {
                
                $PMTM = new ProductMainTypeModel();
                $User = new UsersModel();
                $PWPM = new ProductWaterProofModel();
                
                $mtOptionCtrl = $PMTM->getOptionCtrl(array(
                    '1=1',
                    $PMTM->F->ISENABLE => 1
                ));
                
                $wpOptionCtrl = $PWPM->getOptionCtrl(array(
                    '1=1',
                    $PWPM->F->ISENABLED => 1
                ), 0);
                
                $authorOptionCtrl = $User->getUsersCombo($User->F->Group . "!='0'");
                
                $this->assign('mtOptionCtrl', $mtOptionCtrl);
                $this->assign('wpOptionCtrl', $wpOptionCtrl);
                $this->assign('authorOptionCtrl', $authorOptionCtrl);
                
                $this->display();
            }
        } catch (Exception $e) {}
    }

    /**
     * 编辑新商品
     */
    public function editproduct()
    {
        try {
            if ($this->isPost()) {
                // 数据校验
                $verifyRes = $this->_verifyEndProduct($_POST);
                if (! $verifyRes) {
                    $this->_ajaxResponse(0, '请输入正确的信息');
                } else {

                    $PRODUCTTYPE = C('PRODUCT.TYPE');
                    $ptype = $PRODUCTTYPE['NDIY'];
                    
                    // 编辑商品
                    $PM = new ProductModel();
                    $PM->create($_POST);
                    $PM->{$PM->F->ID} = $_POST['pid'];
                    $PM->{$PM->F->LicType} = $_POST['p_lictype'];
                    $PM->{$PM->F->Mintro} = $_POST['mdesc'];
                    // $PM->{$PM->F->ProductType} = $ptype;
                    $PM->{$PM->F->LastUpdate} = get_now();
                    $PM->{$PM->F->LastUpdateTime} = time();
                    $updateRes = $PM->save();

                    if ($updateRes) {
                        // 标签信息
                        if (trim($this->_post('tags')) !== trim($this->_post('oldtags'))) {
                            $tags_array = explode(' ', trim($_POST['tags']));
                            
                            $this->saveTags($_POST['pid'], $tags_array);
                        }
                    }
                    
                    $updateRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
                }
            } else {

                $pid = I('get.id');
                $PWPM = new ProductWaterProofModel();
                $PMTM = new ProductMainTypeModel();
                $User = new UsersModel();
                $PM = new ProductModel();
                $pmRes = $PM->find($pid);
                
                // $mtOptionCtrl = $PMTM->getOptionCtrl ( array (), $pmRes [$PM->F->MainType] );
                $mtOptionCtrl = $PMTM->getOptionCtrl(array(
                    '1=1',
                    $PMTM->F->ISENABLE => 1
                ), $pmRes[$PM->F->MainType]);
                $authorOptionCtrl = $User->getUsersCombo($User->F->Group . "!='0'", $pmRes[$PM->F->Creater]);
                $wpOptionCtrl = $PWPM->getOptionCtrl(array(
                    '1=1',
                    $PWPM->F->ISENABLED => 1
                ), $pmRes[$PM->F->WaterProofId]);
//var_dump($pmRes);
                $this->assign('mtOptionCtrl', $mtOptionCtrl);
                $this->assign('wpOptionCtrl', $wpOptionCtrl);
                $this->assign('authorOptionCtrl', $authorOptionCtrl);
                $this->assign('product', $pmRes);
                $this->assign('act', 'editproduct');
                $this->display('addproduct');
            }
        } catch (Exception $e) {}
    }

    /**
     * 添加商品主类型
     */
    public function addmaintype()
    {
        try {
            if ($this->isPost()) {
                
                // 数据校验
                $verifyRes = $this->_verifyMaintype($_POST);
                
                if (! $verifyRes) {
                    $this->_ajaxResponse(0, '请输入正确的信息');
                } else {
                    // 添加商品主类型
                    $PMTM = new ProductMainTypeModel();
                    $PMTM->create($_POST);
                    $addRes = $PMTM->add();
                    $addRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
                }
            } else {
                $this->display();
            }
        } catch (Exception $e) {}
    }

    /**
     * 列表商品主类型
     */
    public function listmaintype()
    {
        try {
            
            $PMTM = new ProductMainTypeModel();
            $condition = array(
                '1=1',
                $PMTM->F->ISENABLE => 1
            );
            $listRes = $PMTM->where($condition)->select();
            
            // 处理
            if ($listRes) {
                foreach ($listRes as $key => $val) {
                    $listRes[$key][$PMTM->F->TYPEPROPS] = jsonstrTransToStr($listRes[$key][$PMTM->F->TYPEPROPS]);
                }
            }
            
            // 赋值
            $this->assign('listTable', $listRes);
            $this->display();
        } catch (Exception $e) {}
    }

    /**
     * 定义属性
     */
    public function defineprop()
    {
        try {
            
            $iptId = I('get.id');
            
            if ($iptId) {
                
                $PMTM = new ProductMainTypeModel();
                $mainType = $PMTM->find($iptId);
                
                $PMPM = new ProductMainPropModel();
                $listRes = $PMPM->getPropByMainType($iptId);
                
                if ($listRes) {
                    foreach ($listRes as $key => $val) {
                        $listRes[$key][$PMPM->F->PROPVALS] = jsonstrTransToStr($listRes[$key][$PMPM->F->PROPVALS]);
                    }
                }
                // 赋值
                $this->assign('mainType', $mainType);
                $this->assign('listTable', $listRes);
                $this->assign('hasRes', count($listRes));
                $this->display();
            }
        } catch (Exception $e) {}
    }

    /**
     * 添加属性
     */
    public function addprop()
    {
        try {
            
            $iptId = I('get.id');
            
            if ($iptId) {
                
                if ($this->isPost()) {
                    
                    // 数据校验
                    $verifyRes = $this->_verifyMainprop($_POST);
                    
                    if (! $verifyRes) {
                        $this->_ajaxResponse(0, '请输入正确的信息');
                    } else {
                        
                        // 添加商品主类型属性
                        $PMPM = new ProductMainPropModel();
                        $PMPM->create($_POST);
                        $PMPM->{$PMPM->F->PROPVALS} = delimiterStrTransToJson($_POST['prop_vals'], ',');
                        $addRes = $PMPM->add();
                        
                        if ($addRes) {
                            
                            // 插入属性值表
                            $PPVM = new ProductPropValModel();
                            $PPVM->insertPropVals($_POST['prop_vals'], $iptId, $addRes);
                            
                            // 更新主类型
                            $PMTM = new ProductMainTypeModel();
                            $findRes = $PMTM->find($iptId);
                            if ($findRes) {
                                $PMTM->updateProps();
                            }
                        }
                        
                        $addRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
                    }
                } else {
                    
                    $PMTM = new ProductMainTypeModel();
                    $mainType = $PMTM->find($iptId);
                    
                    // 赋值
                    $this->assign('mainType', $mainType);
                    $this->display();
                }
            }
        } catch (Exception $e) {}
    }

    /**
     * 编辑属性
     */
    public function editprop()
    {
        try {
            
            $ippId = I('get.id');
            
            if ($ippId) {
                
                if ($this->isPost()) {
                    
                    // 数据校验
                    $verifyRes = $this->_verifyMainprop($_POST);
                    
                    if (! $verifyRes) {
                        $this->_ajaxResponse(0, '请输入正确的信息');
                    } else {
                        // 编辑商品主类型属性
                        
                        $PMPM = new ProductMainPropModel();
                        $pmpmRes = $PMPM->find($_POST['prop_id']);
                        
                        $PMTM = new ProductMainTypeModel();
                        $pmtmRes = $PMTM->find($pmpmRes[$PMPM->F->MAINTYPE]);
                        
                        // 更新属性
                        $condition = array(
                            $PMPM->F->ID => $_POST['prop_id']
                        );
                        $data = array(
                            $PMPM->F->PROPNAME => $_POST['prop_name'],
                            $PMPM->F->WEIGHT => $_POST['prop_weight'],
                            $PMPM->F->PROPVALS => delimiterStrTransToJson($_POST['prop_vals'], ',')
                        );
                        $updateRes = $PMPM->where($condition)->save($data);
                        
                        if ($_POST['old_propvals'] != $_POST['prop_vals']) {
                            
                            $PPVM = new ProductPropValModel();
                            
                            $oldvalsArr = explode(',', $_POST['old_propvals']);
                            if ($_POST['old_propvals'] == '') {
                                $oldvalsCnt = 0;
                            } else {
                                $oldvalsCnt = count($oldvalsArr);
                            }
                            
                            $newvalsArr = explode(',', $_POST['prop_vals']);
                            if ($_POST['prop_vals'] == '') {
                                $newvalsCnt = 0;
                            } else {
                                $newvalsCnt = count($newvalsArr);
                            }
                            
                            // 增加属性
                            if ($oldvalsCnt < $newvalsCnt) {
                                
                                for ($i = 0; $i < $oldvalsCnt; $i ++) {
                                    array_shift($newvalsArr);
                                }
                                
                                $PPVM->insertPropVals(arrayTransToStr($newvalsArr), $pmtmRes[$PMTM->F->ID], $_POST['prop_id']);
                            }
                            
                            // 编辑属性
                            if ($oldvalsCnt == $newvalsCnt) {
                                
                                $PPVM->updatePropVals($_POST['prop_vals'], $_POST['prop_id']);
                            }
                            
                            // 删除属性
                            if ($oldvalsCnt > $newvalsCnt) {
                                $PPVM->removePropVals($_POST['prop_id']);
                                if ($newvalsCnt > 0) {
                                    $PPVM->insertPropVals($_POST['prop_vals'], $pmtmRes[$PMTM->F->ID], $_POST['prop_id']);
                                }
                            }
                        }
                        
                        // 更新主类型
                        if ($pmtmRes) {
                            $PMTM->updateProps();
                        }
                        
                        $updateRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
                    }
                } else {
                    
                    $PMPM = new ProductMainPropModel();
                    $condition = array(
                        $PMPM->F->ID => $ippId
                    );
                    $propRes = $PMPM->where($condition)->find();
                    
                    if ($propRes) {
                        //
                        $propRes[$PMPM->F->PROPVALS] = jsonstrTransToStr($propRes[$PMPM->F->PROPVALS], ',');
                        
                        $PMTM = new ProductMainTypeModel();
                        $condition = array(
                            $PMTM->F->ID => $propRes[$PMPM->F->MAINTYPE]
                        );
                        $mainType = $PMTM->where($condition)->find();
                        
                        $this->assign('mainType', $mainType);
                        $this->assign('propRes', $propRes);
                    }
                    // print_r ( $propRes );
                    // 赋值
                    $this->assign('act', 'editprop');
                    $this->display('addprop');
                }
            }
        } catch (Exception $e) {}
    }

    /**
     * 添加/编辑商品主类型数据校验
     *
     * @param array $postData            
     * @return bool
     */
    private function _verifyMaintype(array $postData)
    {
        
        // PVC
        $PVC = new PVC2();
        
        $PVC->setStrictMode(true)->setModeArray()->SourceArray = $postData;
        
        $PVC->isString()
            ->Length(1, 50)
            ->validateMust()
            ->Error('请输入正确的商品主类型名称')
            ->add('maintype_name');
        
        return $PVC->verifyAll() ? true : false;
    }

    /**
     * 添加/编辑专属定制
     *
     * @param array $postData            
     * @return bool
     */
    private function _verifyCustomize(array $postData)
    {
        
        // PVC
        $PVC = new PVC2();
        
        $PVC->setStrictMode(true)->setModeArray()->SourceArray = $postData;
        /* miaomin disabled@2015.10.21
        $PVC->isEMail()
            ->validateMust()
            ->Error('请输入正确的用户名')
            ->add('username');
        */
        $PVC->isString()
            ->Length(1, 250)
            ->validateMust()
            ->Error('请输入正确的商品ID')
            ->add('productids');
        
        return $PVC->verifyAll() ? true : false;
    }

    /**
     * 添加/编辑商品主类型属性数据校验
     *
     * @param array $postData            
     * @return bool
     */
    private function _verifyMainprop(array $postData)
    {
        
        // PVC
        $PVC = new PVC2();
        
        $PVC->setStrictMode(true)->setModeArray()->SourceArray = $postData;
        
        if ($postData['act'] == 'editprop') {
            $PVC->isInt()
                ->validateMust()
                ->Error('缺少商品属性ID')
                ->add('prop_id');
        } else {
            $PVC->isInt()
                ->validateMust()
                ->Error('缺少商品主类型ID')
                ->add('type_id');
        }
        
        $PVC->isString()
            ->Length(1, 50)
            ->validateMust()
            ->Error('请输入正确的属性名')
            ->add('prop_name');
        
        return $PVC->verifyAll() ? true : false;
    }

    /**
     * 添加/编辑非diy商品数据校验
     *
     * @param array $postData            
     * @return bool
     */
    private function _verifyEndProduct(array $postData)
    {
        
        // PVC
        $PVC = new PVC2();
        
        $PVC->setStrictMode(true)->setModeArray()->SourceArray = $postData;
        
        if ($postData['act'] == 'editproduct') {
            $PVC->isString()
                ->validateMust()
                ->Error('缺少商品ID')
                ->add('pid');
        }
        
        $PVC->isString()
            ->validateMust()
            ->Error('缺少主类型ID')
            ->add('product_maintype');
        $PVC->isString()
            ->validateMust()
            ->Error('缺少所有人ID')
            ->add('product_author');
        $PVC->isString()
            ->Length(1, 200)
            ->validateMust()
            ->Error('请输入正确的商品名称')
            ->add('product_name');
        
        return $PVC->verifyAll() ? true : false;
    }

    /**
     * 保存关键字
     *
     * @param int $PID            
     * @param array $Tags            
     * @return boolean
     */
    private function saveTags($PID, $Tags)
    {
        $PTM = new ProductTagsModel();
        $OldTags = $PTM->getTagsByProduct($PID);
        
        if ($OldTags === false) {
            return false;
        }
        $OldTags = $OldTags ? $OldTags : array();
        $ExistTags = array();
        foreach ($OldTags as $OldTag) {
            if (in_array($OldTag[$PTM->F->Name], $Tags)) {
                $ExistTags[] = $OldTag[$PTM->F->Name];
            }
        }
        if (! array_diff($Tags, $OldTags)) {
            return true;
        }
        if ($PTM->changTagsCount($ExistTags, - 1) === false) {
            return false;
        }
        $TagsIDArray = $PTM->addTagsArray($Tags);
        if ($TagsIDArray === false) {
            return false;
        }
        $PTIM = new ProductTagsIndexModel();
        if ($PTIM->where($PTIM->F->ProductID . "='" . $PID . "'")->delete() === false) {
            return false;
        }
        foreach ($TagsIDArray as $TagID) {
            $PTIM->{$PTIM->F->ProductID} = $PID;
            $PTIM->{$PTIM->F->TagsID} = $TagID;
            if ($PTIM->add() === false) {
                return false;
            }
        }
        return true;
    }
}
?>