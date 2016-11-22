<?php
class ImageModel extends Model {
	
	
	public function saveImg($imgarr){//保存图片信息到tdf_image表中
		//$himg=$this->haveImg($imgarr['hash']);
		//if(!$himg){
			$data['path']		=  substr($imgarr['savepath'],1).$imgarr['savename'] ;
			$data['md5']		= $imgarr['hash'];
			$data['size']		= $imgarr['size'];
			$data['extension']	= $imgarr['extension'];
			$save_result=M("image")->add($data);
		//}else{
		//	$save_result=$himg;
		//}
		return $save_result;
	}
	
	private function haveImg($img_md5){
		if($img_md5){
			$res=M('image')->field("id")->where("md5='".$img_md5."'")->find();
			$result=$res['id'];
		}else{
			$result=0;
		}
		return $result;
	}

    /**
     * 由tdf_image表的ID获取图片路径
     */
    public function getImgPathById($id){
        if($id){
            $res=M('image')->field("path")->where("id=".$id."")->find();
        }else{
            $res='';
        }

        return $res;
    }
	
	
	
	
}
?>