<?php

/**
 * 活动管理类
 * 
 * @author miaomin
 * Jul 15, 2015 9:52:22 AM
 *
 */
class SalespromAction extends CommonAction
{

    /**
     * 首页
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 活动列表
     */
    public function listsp()
    {
        try {
            load('@.Paging');
            $SPMM = new SPMainModel();
            $SPPropM = new SPPropModel();
            $SPMList = $SPMM->where('1=1')->select();
            foreach ($SPMList as $key => $val) {
                $SPMList[$key][$SPMM->F->BEGIN] = substr($val[$SPMM->F->BEGIN], 0, 10);
                $SPMList[$key][$SPMM->F->END] = substr($val[$SPMM->F->END], 0, 10);
                // 处理活动详情读取PROP表
                $sppropmRes = $SPPropM->getPropListBySPID($val[$SPMM->F->ID]);
                if ($SPPropM) {
                    foreach ($sppropmRes as $k => $v) {
                        $SPMList[$key]['spm_detail'] .= $v[$SPPropM->F->SPITEMNAME] . ': ' . $v[$SPPropM->F->SPPVAL] . '<br>';
                    }
                }
            }
            // 赋值
            $this->assign('listTable', $SPMList);
            $this->display();
        } catch (Exception $e) {}
    }

    /**
     * 添加活动
     */
    public function addsp()
    {
        try {
            if ($this->isPost()) {
                
                // 数据校验
                $verifyRes = $this->_verifySP($_POST);
                
                if (! $verifyRes) {
                    $this->_ajaxResponse(0, '请输入正确的信息');
                }
                
                // 添加活动
                $SPMM = new SPMainModel();
                $SPMM->create($_POST);
                $SPMM->{$SPMM->F->CREATEDATE} = get_now();
                $SPMM->{$SPMM->F->LASTUPDATE} = get_now();
                $SPMM->{$SPMM->F->ENABLED} = 0;
                $SPMM->{$SPMM->F->CREATEUID} = $_SESSION['my_info']['aid'];
                $addRes = $SPMM->add();
                
                // 活动商品
                if ($addRes) {
                    // 商品IDS
                    $pids_all = trim($_POST['sp_pids']);
                    if ($pids_all != 'all') {
                        $pids_array = explode(',', $pids_all);
                        
                        $SPPM = new SPProductModel();
                        $SPPM->addPids($pids_array, $addRes);
                    }
                }
                
                // 活动详情初始化
                $SPPropM = new SPPropModel();
                $SPConfM = new SPConfModel();
                $spconfmRes = $SPConfM->getPropConfListBySPTID($_POST['sp_maintype']);
                // 更新活动详情(删除后重写入)
                if ($addRes) {
                    // 重写属性
                    foreach ($spconfmRes as $key => $val) {
                        $add_data = array(
                            $SPPropM->F->SPID => $addRes,
                            $SPPropM->F->SPITEMID => $val[$SPConfM->F->SPITEMID],
                            $SPPropM->F->SPITEMNAME => $val['ispi_name'],
                            $SPPropM->F->SPPVAL => 0
                        );
                        $SPPropM->add($add_data);
                    }
                }
                $addRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
            } else {
                $SPTM = new SPTypeModel();
                $sptOptCtrl = genDBOptionCtrl($SPTM->select(), $SPTM->F->NAME, $SPTM->F->ID);
                $this->assign('spTypeOptionCtrl', $sptOptCtrl);
                $this->display();
            }
        } catch (Exception $e) {}
    }

    /**
     * 设置活动详情
     */
    public function setspdetail()
    {
        try {
            /* 循环采集变量 */
            if ($this->isPost()) {
                $spm_id = $_POST['spid'];
                if ($spm_id) {
                    $SPTM = new SPTypeModel();
                    $SPMM = new SPMainModel();
                    $SPPropM = new SPPropModel();
                    $SPConfM = new SPConfModel();
                    // 取数据
                    $spm_id = I('get.id');
                    $spmmRes = $SPMM->find($spm_id);
                    $sppropmRes = $SPPropM->getPropListBySPID($spm_id);
                    $spconfmRes = $SPConfM->getPropConfListBySPTID($spmmRes[$SPMM->F->TYPE]);
                    // 更新活动详情(删除后重写入)
                    $editRes = true;
                    if ($editRes) {
                        // 移除属性
                        $SPPropM->removePropListBySPID($spm_id);
                        // 重写属性
                        foreach ($spconfmRes as $key => $val) {
                            $add_data = array(
                                $SPPropM->F->SPID => $spm_id,
                                $SPPropM->F->SPITEMID => $val[$SPConfM->F->SPITEMID],
                                $SPPropM->F->SPITEMNAME => $val['ispi_name'],
                                $SPPropM->F->SPPVAL => $_POST[$val['ispi_html']]
                            );
                            $editRes = $SPPropM->add($add_data);
                        }
                    }
                    $editRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
                }
            } else {
                $SPTM = new SPTypeModel();
                $SPMM = new SPMainModel();
                $SPPropM = new SPPropModel();
                // 取数据
                $spm_id = I('get.id');
                $spmmRes = $SPMM->find($spm_id);
                $sppropmRes = $SPPropM->getPropListBySPID($spm_id);
                // 赋值
                $this->assign('sppropList', $sppropmRes);
                $this->assign('spmain', $spmmRes);
                $this->assign('act', 'setspdetail');
                $this->display('setspdetail_hdj');
            }
        } catch (Exception $e) {}
    }

    /**
     * 编辑活动
     */
    public function editsp()
    {
        try {
            if ($this->isPost()) {
                
                // 数据校验
                $verifyRes = $this->_verifySP($_POST);
                
                if (! $verifyRes) {
                    $this->_ajaxResponse(0, '请输入正确的信息');
                }
                
                // 编辑活动
                $SPMM = new SPMainModel();
                $SPMM->create($_POST);
                $SPMM->{$SPMM->F->LASTUPDATE} = get_now();
                $SPMM->{$SPMM->F->ID} = $_POST['spid'];
                $editRes = $SPMM->save();
                
                // 活动商品
                if ($editRes) {
                    // 商品IDS
                    $pids_all = trim($_POST['sp_pids']);
                    $SPPM = new SPProductModel();
                    if ($pids_all != 'all') {
                        $pids_array = explode(',', $pids_all);
                        $SPPM->addPids($pids_array, $_POST['spid']);
                    } else {
                        $SPPM->removePids($_POST['spid']);
                    }
                }
                
                $editRes ? $this->_ajaxResponse(1, '信息保存成功') : $this->_ajaxResponse(0, '信息保存失败');
            } else {
                $SPTM = new SPTypeModel();
                $SPMM = new SPMainModel();
                // 取数据
                $spm_id = I('get.id');
                $spmmRes = $SPMM->find($spm_id);
                $spmmRes[$SPMM->F->BEGIN] = substr($spmmRes[$SPMM->F->BEGIN], 0, 10);
                $spmmRes[$SPMM->F->END] = substr($spmmRes[$SPMM->F->END], 0, 10);
                $sptOptCtrl = genDBOptionCtrl($SPTM->select(), $SPTM->F->NAME, $SPTM->F->ID, $spmmRes[$SPMM->F->TYPE]);
                // 赋值
                $this->assign('spmain', $spmmRes);
                $this->assign('act', 'editsp');
                $this->assign('spTypeOptionCtrl', $sptOptCtrl);
                $this->display('addsp');
            }
        } catch (Exception $e) {}
    }

    /**
     * 添加/编辑促销活动数据校验
     *
     * @param array $postData            
     * @return bool
     */
    private function _verifySP(array $postData)
    {
        // PVC
        $PVC = new PVC2();
        
        $PVC->setStrictMode(true)->setModeArray()->SourceArray = $postData;
        
        if ($postData['act'] == 'editsp') {
            $PVC->isString()
                ->validateMust()
                ->Error('缺少活动ID')
                ->add('spid');
        }
        
        $PVC->isString()
            ->validateMust()
            ->Error('缺少活动类型')
            ->add('sp_maintype');
        $PVC->isString()
            ->validateMust()
            ->Error('缺少活动名称')
            ->add('sp_name');
        $PVC->isDate()
            ->validateMust()
            ->Error('缺少开始时间')
            ->add('begindate');
        $PVC->isDate()
            ->validateMust()
            ->Error('缺少结束时间')
            ->add('enddate');
        
        return $PVC->verifyAll() ? true : false;
    }
}
?>