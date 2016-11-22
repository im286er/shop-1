<?php
/**
 * diy_cate
 *
 */
class DiyCateModel extends Model {
	protected $tableName = 'diy_cate';
	
	public function getDiyCateByCid($cid){
		$DC=M("diy_cate")->where("cid=".$cid."")->find();
		return $DC;
	}

    public function appDiyCateByCid($cid){
        $DC=M("diy_cate")->where("cid=".$cid."")->find();
        if(!$DC){return false;}
        $result['cid']=$DC['cid'];
        $result['version']=$DC['version'];
        $result['appscript']=WEBROOT_URL . $DC['appscript'];
        if($DC['appmesh']){
            $result['appmesh']=$this->getStrAddUrl($DC['appmesh']);
        }else{
            $result['appmesh']='0';
        }
        $result['cameramatrix']=$DC['cameramatrix']?$DC['cameramatrix']:'0';
        $result['chainmatrix']=$DC['chainmatrix']?$DC['chainmatrix']:'0';
        $result['chainmeshpaths']=$this->getStrAddUrl($DC['chainmeshpaths']);

        return $result;
    }

    /**根据字符串进行循环拼接WEBROOT_URL
     * @param $str
     * @return string
     */
    public function getStrAddUrl($str){
        if($str){
            $strArr=explode(",",$str);
            foreach($strArr as $key =>$value){
                $result.=WEBROOT_URL.$value.",";
            }
            $result=substr($result,0,strlen($result)-1);
        }else{
            $result='0';
        }
        return $result;
    }

    /*
     * 根据cid获取diy详情
     */
    public function appDiyDetailByCid($cid){
        $DC=M("diy_cate")->where("cid=".$cid."")->find();
        if(!$DC){return false;}
            $IM=new ImageModel();
            $imgArr=explode(",",$DC['cate_icon']);
            foreach($imgArr as $key => $value){
                $img=$IM->getImgPathById($value);
                $images.=WEBROOT_URL.$img['path'].",";
            }
            $images=substr($images,0,strlen($str)-1); ;
            $result['intro']    =$DC['intro1'];
            $result['startprice']=$DC['startprice'];
            $result['images']   =$images;
            $result['url']      =WEBROOT_URL."/index/diy-jewelry-cid-".$cid.".html";
            $PM=new ProductModel();
            $productInfo=$PM->getProductByDiyCateCid($cid);
            $result['p_zans']   =$productInfo['p_zans'];
            $result['p_id']     =$productInfo['p_id'];
            //$result['productIntroHtml']     =$DC['intro2'];
            $result['productIntroHtml']     =str_replace('<img src="','<img src="'.WEBROOT_URL,$DC['intro2']);
        return $result;
    }






	

}
?>