<?php
/**
 * 活动属性表基本类
 * 
 * @author miaomin
 * Jul 24, 2015 6:05:03 PM
 *
 */
class SPPropModel extends Model
{

    /**
     *
     * @var DBF
     */
    protected $DBF;

    /**
     *
     * @var DBF_SPProp
     */
    public $F;

    /**
     * 活动属性表基本类
     */
    public function __construct()
    {
        $this->DBF = new DBF();
        $this->F = $this->DBF->SPProp;
        $this->trueTableName = $this->F->_Table;
        $this->fields = $this->F->getFields();
        
        if (! $this->_map) {
            $this->_map = $this->F->getMappedFields();
        }
        
        parent::__construct();
    }
    
    /**
     * 根据活动ID移除活动属性集合
     * 
     * @param unknown $spid
     */
    public function removePropListBySPID($spid){
        if ($spid){
            $condition = array(
                $this->F->SPID => $spid
            );
            return $this->where($condition)->delete();
        }
        
        return false;
    }
    
    /**
     * 根据活动ID获取活动属性集合
     * 
     * @param int $spid
     */
    public function getPropListBySPID($spid){
        $condition = array(
            $this->F->SPID => $spid
        );
        return $this->join(' tdf_info_spitem ON tdf_spprop.ispi_id = tdf_info_spitem.ispi_id')->where($condition)->select();
    }
}