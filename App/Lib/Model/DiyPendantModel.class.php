<?php
/**
 * diy_diamond
 *
 */
class DiyPendantModel extends Model {
	protected $tableName = 'diy_pendant';

    /**
     * 获取所有可用宝石信息列表
     */
    public function getDiyPendantAll($type=0){
        $sql="select * from tdf_diy_pendant where delsign=0";
        $diamondArr=M()->query($sql);
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
    public function getDiyPendantByArrId($IDstr){
        $sql="select * from tdf_diy_pendant where id in(".$IDstr.")";
        $pendantArr=M()->query($sql);
        $pendantStyleArr=L('pendant_style');
        foreach($pendantArr as $key => $value){
            $pendantArr[$key]['stylePendantValue']=$pendantStyleArr[$value['style']];
        }

        return $pendantArr;
    }



    /**
     * 通过宝石ID获取宝石信息
     */
    public function getPendantInfoByid($id){
        $result=M('diy_pendant')->where("id=".$id."")->find();
        return $result;
    }
    /**
     * 根据传入的数组返回宝石价格
     */
    public function getPriceByPosPidArr($id,$posArr){

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
    /* public function getPriceArr($diamondid,$diamondArr,$unit_diamond_num,$unit_price,$visiable=1){

         foreach($diamondArr as $key => $value){
             if($diamondid==$value['id']){
                 $result['diamond_price']=intval($value['price']);
             }
         }
         $result['diamond_num']=$unit_diamond_num;
         $result['price']=$unit_price;
         $result['visiable']=$visiable;
         return $result;
     }*/

    /**
     * 根据宝石的用户保存方案详情
     * @ valueArr 传入宝石方案值
     */
    public function getPendantValue($valueArr){
           $pendantInfo = $this->getPendantInfoByid($valueArr);
           $result = $pendantInfo['dname']." ";
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
        $result['visiable']=$visiable;
        $result['price_count']=$priceCount;
        return $result;
    }
	
}
?>