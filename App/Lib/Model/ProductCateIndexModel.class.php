<?php
class ProductCateIndexModel extends Model {
	protected $tableName = 'tdf_product_cate_index';
	
	
	/*
	 * 添加cateid和pid到产品分类对应表
	 * @param $pc_id_arr  分类数组
	 */
	public function addCate($pc_id_arr,$p_id,$pc_type){

		$PCIM=M('product_cate_index');
		$PCIM->where("pc_type=".$pc_type." and p_id=".$p_id."")->delete();
		foreach($pc_id_arr as $key => $value){
			$data['pc_id']=$value;
			$data['p_id']=$p_id;
			$data['pc_type']=$pc_type;
			$id=$PCIM->add($data);
		}
	}
	
	
	public function getPcidByPid($p_id,$pc_type){
		$PCI=M();
		$sql="select TPCI.pc_id from tdf_product_cate_index as TPCI Left Join tdf_product_cate as TPC On TPC.pc_id=TPCI.pc_id ";
		$sql.="where TPC.pc_type=".$pc_type." and TPCI.p_id=".$p_id."";
		$PCIinfo=$PCI->query($sql);
		return $PCIinfo;
	}
	
}
?>