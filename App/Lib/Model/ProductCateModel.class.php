<?php
class ProductCateModel extends Model {
	protected $tableName = 'tdf_product_cate';

     /*//根据分类id获得分类的TDK信息
     *  @param $cateid
      *
     */
    public function getCateTDKByCateId($cateid){
        $PC=M('product_cate')->field('tdk_title,tdk_keywords,tdk_description')->where("pc_id=".$cateid)->find();
        if($PC){
            return $PC;
        }else{
            return false;
        }
    }

    //
    /*根据p_cate的值 获取 product_cate 信息
     *@param $p_cate   带","的string，需要截取第一个cateid
     *@productName 产品名称
     *返回TDK数组
     */
    public function getCateTDKByPcate($p_cate,$productName){
        if(strpos($p_cate,',')){
            $p_cate_one=substr($p_cate,0,strpos($productInfo['p_cate'],','));//取出第一个cate的id
        }else{
            $p_cate_one=$p_cate;//取出第一个cate的id
        }
        $CateTDK=$this->getCateTDKByCateId($p_cate_one);
        if($CateTDK){
            foreach($CateTDK as $key=>$value){
                $replacement['productname']=$productName;
                $result[$key]=replace_string_vars($value, $replacement);
            }
        }else{
            $result=false;
        }
        return $result;
    }




	/* * 添加cateid和pid到产品分类对应表
	 * @param $pc_id_arr  分类数组
	 */
	/*public function addCate____($pc_id_arr,$p_id,$pc_type){

    $PCIM=M('product_cate_index');
    $PCIM->where("pc_type=".$pc_type." and p_id=".$p_id."")->delete();
    foreach($pc_id_arr as $key => $value){
        $data['pc_id']=$value;
        $data['p_id']=$p_id;
        $data['pc_type']=$pc_type;
        $id=$PCIM->add($data);
    }
}


    public function getPcidByPid__($p_id,$pc_type){
        $PCI=M();
        $sql="select TPCI.pc_id from tdf_product_cate_index as TPCI Left Join tdf_product_cate as TPC On TPC.pc_id=TPCI.pc_id ";
        $sql.="where TPC.pc_type=".$pc_type." and TPCI.p_id=".$p_id."";
        $PCIinfo=$PCI->query($sql);
        return $PCIinfo;
    }*/
    
    /**
     * 获取商品促销水印选项
     *
     * @param array $whereArr
     * @param string $select
     */
    public function getOptionCtrl($whereArr = array(), $select = null){
        // find
        $pcm = M('product_cate');
        $findRes = $pcm->where ( $whereArr )->select ();
        
        $optArr = transDBarrToOptarr ( $findRes, 'pc_name', 'pc_id' );
         
        if ($select !== null) {
            $optRes = get_dropdown_option ( $optArr, $select );
        } else {
            $optRes = get_dropdown_option ( $optArr );
        }
        
        return $optRes;
    }
	
}
?>