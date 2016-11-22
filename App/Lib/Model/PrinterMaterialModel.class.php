<?php
/**
 * PrintMaterial基本类
 *
 * @author miaomin 
 * Oct 21, 2013 11:18:11 AM
 *
 * $Id: PrinterMaterialModel.class.php 1149 2013-12-23 05:25:35Z miaomiao $
 */
class PrinterMaterialModel extends Model {
	/**
	 *
	 * @var DBF_PrinterMaterial
	 */
	public $F;
	
	/**
	 * 构造
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->PrinterMaterial;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 获取PMA选项
	 *
	 * @param string $where        	
	 * @param string $select        	
	 */
	public function getPMAOption($where = '1=1', $select = '') {
		$pmaRes = $this->where ( $where )->select ();
		$pmaOptArr = transDBarrToOptarr ( $pmaRes, 'pma_name', 'pma_id' );
		if ($select) {
			$pmaOpt = get_dropdown_option ( $pmaOptArr, $select );
		} else {
			$pmaOpt = get_dropdown_option ( $pmaOptArr );
		}
		
		return $pmaOpt;
	}
	
	/**
	 * 获取可打印的最大尺寸
	 */
	public function getMaxsize(){
		$pmRes = $this->order('pma_weight DESC')->where("pma_parentid <> '0'")->field("pma_id,pma_name,pma_maxlength,pma_maxwidth,pma_maxheight,pma_color,pma_parentname,pma_minlength,pma_minwidth,pma_minheight,pma_feature,pma_application")->select();
		return $pmRes;
	}

    /*
     * 获取diy可打印的材料
     */
    public function getDiyMaterial($type=0){
        $sql="select TPM.pma_id,TPM.pma_name as TPM_name,TPMP.pma_name as TPMP_name,TPM.pma_diy_formula_s,TPM.pma_diy_formula_b,TPM.pma_necklace_price from tdf_printer_material as TPM ";
        $sql.="Left Join tdf_printer_material as TPMP ON TPMP.pma_id=TPM.pma_parentid ";
        $sql.="where TPM.pma_type=1 ";
        if(!$type){
	        $sql.= "and TPM.ishidden=0 ";
        }
	    $sql.="order by TPM.pma_weight ASC ";
	    $mcate=M("printer_material")->query($sql);//打印材料数组，必须
        return $mcate;
    }

    /**
     *根据材料ID获取材料名称
     */
    public function getMaterialNameByID($pma_id){
        $result=M('printer_material')->field("pma_name as name")->where("pma_id=".$pma_id."")->find();
        return $result['name'];
    }


}
?>