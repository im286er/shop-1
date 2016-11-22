<?php
/**
 * diy_diamond
 *
 */
class DiyDiamondModel extends Model {
	protected $tableName = 'diy_diamond';

    /**
     * 获取所有可用宝石信息列表
     */
    public function getDiyDiamondAll($type=0){
        $sql="select * from tdf_diy_diamond where delsign=0";
        $diamondArr=M()->query($sql);
        //var_dump($diamondArr);
        if($type==1){//1为只返回宝石ID对应价格的数组
            foreach($diamondArr as $key => $value){
                $result[$value['id']]=$value['price'];
            }
            return $result;
        }else{
            return $diamondArr;
        }
    }


    /**
     * 通过宝石ID(可以是带','的字串)获取宝石信息列表
     */
    public function getDiyDiamondByArrId($IDstr){
        $sql="select * from tdf_diy_diamond where id in(".$IDstr.")";
        $diamondArr=M()->query($sql);
        $diamondStyleArr=L('diamond_style');
        foreach($diamondArr as $key => $value){
            $diamondArr[$key]['styleDiamondValue']=$diamondStyleArr[$value['style']];
        }
//        var_dump($diamondArr);
        return $diamondArr;
    }

    /**
     * 通过宝石ID(可以是带','的字串)获取宝石信息列表
     */
    public function getDiyDiamondPriceByArrId($IDstr){
        $sql="select id,price from tdf_diy_diamond where id in(".$IDstr.")";
        $diamondArr=M()->query($sql);
        foreach($diamondArr as $key =>$value){
            $result[$value['id']]=intval($value['price'])+5; //宝石价格加5元,以后更新到后台的价格中
        }
        return $result;
    }

    /**
     * 通过宝石ID获取宝石信息
     */
    public function getDiamondInfoByid($id){
        $result=M('diy_diamond')->where("id=".$id."")->find();
        return $result;
    }
    /**
     * 根据传入的数组返回宝石价格
     */
    public function getPriceByPosDidArr($id,$posArr){

        foreach($posArr as $key => $value){
            if($id==$value['id']){
                $result=intval($value['price']);
            }
        }
        $result=$result?$result:0;
        return $result;
    }

    /**
     * 根据传入参数返回部件的价格因素数组
     */
    public function getPriceArr($diamondid,$diamondArr,$unit_diamond_num,$unit_price,$visiable,$priceCount){
        foreach($diamondArr as $key => $value){
            if($diamondid==$value['id']){
                $result['diamond_price']=intval($value['price']);
            }
        }
        $result['diamond_num']=$unit_diamond_num;
        $result['price']=$unit_price;
        if(!$visiable){
            $result['visiable']=1;
        }else{
            $result['visiable']=$visiable;
        }
        $result['price_count']=$priceCount;
        return $result;
    }

    /**
     * 根据宝石的用户保存方案详情
     * @ valueArr 传入宝石方案值
     */
    public function getDimondValue($valueArr,$unit_showname){
       if(is_array($valueArr)){
           if($valueArr['visiable']==1) {
               $diamondInfo = $this->getDiamondInfoByid($valueArr['value']);
               $PMM = new PrinterMaterialModel();
               $material = $PMM->getMaterialNameByID($valueArr['material']);
               $result = $unit_showname.":".$diamondInfo['dname'] . "," . $material.";";
           }else{
               $result='';
           }
       }else{
           $diamondInfo = $this->getDiamondInfoByid($valueArr);
           $result = $unit_showname.":".$diamondInfo['dname']." ";
       }
       return $result;
    }

	
	
}
?>