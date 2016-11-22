<?php
/**
 * 帮助文档表
 *
 */
class HelpModel extends Model {
	protected $tableName = 'help_doc';
	protected $fields = array (
			'id',
			'title',
			'cate',
			'content',
			'u_id',
			'sort',
			'status',
			'ctime',
            'title2',
            'tjtext',
			'_pk' => 'id',
			'_autoinc' => TRUE 
	);
	
	public function getindexpic($cate=7){
		$IP=M('help_doc');
		$ipinfo=$IP->where("cate=".$cate)->order('sort')->select();
		return $ipinfo;
	}
	
	public function admin_getindexpic($cate){
		$IP=M('help_doc');
		$ipinfo=$IP->where('cate='.$cate.' and isdel=0')->select();
		return $ipinfo;
	}
	
	public function getzhuanti_top(){
		$ZT=M('help_doc');
		$ztinfo=$ZT->where('cate=8')->order('ctime desc')->limit(0,1)->find();
		return $ztinfo;
	}
	
	public function getzhuanti_list(){
		$ZT=M('help_doc');
		$ztinfo=$ZT->where('cate=8')->order('ctime desc')->limit(1,3)->select();
		return $ztinfo;
	}
	
	public function getlinks_list(){ //cate=9为
		$ZT=M('help_doc');
		$ztinfo=$ZT->field('id,title,pic_link')->where('cate=9 and status=1 and isdel=0')->order('sort desc')->select();
		return $ztinfo;
	}

    public function getbanner($cate=12){
        $IP=M('help_doc');
        $ipinfo=$IP->where("cate=".$cate." and status=0")->order('sort')->limit(0,5)->select();
        foreach($ipinfo as $key =>$value){
            $result[$key]['pic_link']=$ipinfo[$key]['pic_link'];
            $result[$key]['pic']=WEBROOT_URL.$ipinfo[$key]['pic'];
            $result[$key]['title']=$ipinfo[$key]['title'];
        }
        return $result;
    }

	public function getstartpage($cate=99){
		$QD=M('help_doc');
		$info=$QD->where("cate=".$cate." and status=0")->order('sort')->limit(0,3)->select();
		foreach($info as $key =>$value){
			//$result[$key]['pic_link']=$info[$key]['pic_link'];
			$result[$key]['pic']=WEBROOT_URL.$info[$key]['pic'];
			//$result[$key]['title']=$info[$key]['title'];
		}
		return $result;
	}

    public function getapptj($cate=13){
        $IP=M('help_doc');
        $ipinfo=$IP->where("cate=".$cate." and status=0")->order('sort')->limit(0,6)->select();
        foreach($ipinfo as $key =>$value){
            $result[$key]['title']=$ipinfo[$key]['title'];
            $result[$key]['pic']=WEBROOT_URL.$ipinfo[$key]['pic'];
            $result[$key]['idtype']=$ipinfo[$key]['pic_link'];
            $result[$key]['title2']=$ipinfo[$key]['title2'];
            $result[$key]['tjtext']=$ipinfo[$key]['tjtext'];
            $result[$key]['tjtext']=$ipinfo[$key]['tjtext'];
            $result[$key]['productid']=$ipinfo[$key]['productid'];
            $result[$key]['diyurl']=WEBROOT_URL."/index/diy-jewelryeditall-cid-".$ipinfo[$key]['productid'].".html";

        }
        return $result;
    }

    /**
     * 根据文章ID获取文章信息
     */
    public function getdocById($id){
        $HDM=M('help_doc');
        $HDinfo=$HDM->where("id=".$id)->find();
        return $HDinfo['intro'];
    }
	
}
?>