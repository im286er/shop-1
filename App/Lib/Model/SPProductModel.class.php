<?php

/**
 * 活动商品表基本类
 * 
 * @author miaomin
 * Jul 16, 2015 9:50:45 AM
 *
 */
class SPProductModel extends Model
{

    /**
     *
     * @var DBF
     */
    protected $DBF;

    /**
     *
     * @var DBF_SPProduct
     */
    public $F;

    /**
     * 活动商品表基本类
     */
    public function __construct()
    {
        $this->DBF = new DBF();
        $this->F = $this->DBF->SPProduct;
        $this->trueTableName = $this->F->_Table;
        $this->fields = $this->F->getFields();
        
        if (! $this->_map) {
            $this->_map = $this->F->getMappedFields();
        }
        
        parent::__construct();
    }

    /**
     * add PIDS
     *
     * @param array $pidsArr            
     * @param int $spid            
     */
    public function addPids($pidsArr, $spid)
    {
        if ($spid) {
            // 先删除
            $this->removePids($spid);
            
            foreach ($pidsArr as $key => $val) {
                // 再建立索引
                $newdata = array(
                    $this->F->SPID => $spid,
                    $this->F->PID => $val
                );
                $this->data($newdata)->add();
            }
        }
    }

    /**
     * remove PIDS
     *
     * @param int $spid            
     */
    public function removePids($spid)
    {
        if ($spid) {
            $condition = array(
                $this->F->SPID => $spid
            );
            $this->where($condition)->delete();
        }
    }

    /**
     * 根据商品ID获取该商品参加促销活动的详情
     *
     * @param int $pid            
     */
    public function getSPDetailByPid($pid)
    {
        if ($pid) {
            $todayStr = date('Y-m-d');
            $condition = array(
                $this->F->PID => $pid,
                'tdf_spmain.spm_begin' => array(
                    'elt',
                    $todayStr
                ),
                'tdf_spmain.spm_end' => array(
                    'egt',
                    $todayStr
                )
            );
            $res = $this->join(" tdf_spmain ON tdf_spproduct.spm_id = tdf_spmain.spm_id")
                ->where($condition)
                ->select();
           if ($res){
               return $res;
           }else{
               $PM = new ProductModel();
               $pmRes = $PM->find($pid);
               if ($pmRes){
                   $condition = array(
                       $this->F->PID => $pmRes[$PM->F->BelongPid],
                       'tdf_spmain.spm_begin' => array(
                           'elt',
                           $todayStr
                       ),
                       'tdf_spmain.spm_end' => array(
                           'egt',
                           $todayStr
                       )
                   );
                   return $this->join(" tdf_spmain ON tdf_spproduct.spm_id = tdf_spmain.spm_id")
                   ->where($condition)
                   ->select();
               }
           }
        }
        return false;
    }

    /**
     * 根据商品ID获取该商品参加促销活动的详情
     *
     * @param int $spid            
     */
    public function getSPDetailRenderByPid($pid)
    {
        if ($pid) {
            $res = $this->getSPDetailByPid($pid);
            if ($res) {
                $returnRes = "<font color='red'>促销活动：";
                foreach ($res as $key => $val) {
                    $returnRes .= $val['spm_title'] . "," . $val['spm_intro'] . "<br>";
                }
                $returnRes .= "</font>";
                return $returnRes;
            }
        }
        
        return '';
    }

    /**
     * 根据商品ID获取该商品参加促销活动后的价格
     *
     * @param int $spid
     * @param int $oldprice            
     */
    public function calcSPPriceByPid($pid,$oldprice=null)
    {
        if ($pid) {
            $PM = new ProductModel();
            $pmRes = $PM->find($pid);
            
            // 计算活动价格
            if ($oldprice === null){
                $returnP = $pmRes[$PM->F->Price];
            }else{
                $returnP = $oldprice;   
            }
            
            // 是否参加活动
            $res = $this->getSPDetailByPid($pid);
            if ($res) {
                $SPPropM = new SPPropModel();
                foreach ($res as $key => $val) {
                    switch ($val['spm_type']) {
                        case 1:
                            
                            // 活动价
                            $sppropRes = $SPPropM->getPropListBySPID($val['spm_id']);
                            if ($sppropRes) {
                                foreach ($sppropRes as $k => $v) {
                                    if ($v[$SPPropM->F->SPPVAL] != 0) {
                                        switch ($v[$SPPropM->F->SPITEMID]) {
                                            case 6:
                                                
                                                // 折扣
                                                $returnP = round($returnP * ($v[$SPPropM->F->SPPVAL] / 100), 2);
                                                if ($returnP < 0){
                                                    $returnP = 0;
                                                }
                                                break;
                                            case 7:
                                                
                                                // 立减
                                                $returnP = round($returnP - $v[$SPPropM->F->SPPVAL], 2);
                                                if ($returnP < 0){
                                                    $returnP = 0;
                                                }
                                                break;
                                        }
                                    }
                                }
                            }
                            break;
                        case 2:
                            
                            // 优惠通用码
                            break;
                        case 3:
                            
                            // 优惠券
                            break;
                        case 4:
                            
                            // 满减
                            break;
                        case 5:
                            
                            // 赠礼
                            break;
                    }
                }
                
                return $returnP;
            }else{
                return $returnP;
            }
        }
        return false;
    }
}