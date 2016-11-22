<?php
/**
 * 活动属性配置表基本类
 * 
 * @author miaomin
 * Jul 27, 2015 1:48:50 PM
 *
 */
class SPConfModel extends Model
{

    /**
     *
     * @var DBF
     */
    protected $DBF;

    /**
     *
     * @var DBF_SPConf
     */
    public $F;

    /**
     * 活动属性配置表基本类
     */
    public function __construct()
    {
        $this->DBF = new DBF();
        $this->F = $this->DBF->SPConfig;
        $this->trueTableName = $this->F->_Table;
        $this->fields = $this->F->getFields();
        
        if (! $this->_map) {
            $this->_map = $this->F->getMappedFields();
        }
        
        parent::__construct();
    }
    
    /**
     * 根据活动类型ID获取活动属性配置集合
     * 
     * @param int $sptid
     */
    public function getPropConfListBySPTID($sptid){
        $condition = array(
            $this->F->SPTYPEID => $sptid
        );
        return $this->join(' tdf_info_spitem ON tdf_spconfig.ispi_id = tdf_info_spitem.ispi_id')->where($condition)->select();
    }
}