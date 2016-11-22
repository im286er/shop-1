<?php

// 本类由系统自动生成，仅供测试用途
class BaseAction extends Action {

    public function demoRegion() {
        $province = M('Region')->where(array('pid' => 1))->select();
        $this->assign('province', $province);
        $this->display();
    }

    public function getRegion() {
        $Region = M("Region");
        $map['pid'] = $_REQUEST["pid"];
        $map['type'] = $_REQUEST["type"];
        $list = $Region->where($map)->select();
        echo json_encode($list);
    }

    public function verify_code() {
        $w = isset($_GET['w']) ? (int) $_GET['w'] : 50;
        $h = isset($_GET['h']) ? (int) $_GET['h'] : 30;
        import("ORG.Util.Image");
        Image::buildImageVerify(4, 1, 'png', $w, $h);
    }


    //文件夹下的图片缩略图处理
    public function imgdo(){
        $imgPath='./upload/image';
        $pathNames = $this->myreaddir($imgPath);
        foreach($pathNames as $key => $value){
            $imgFolder=$imgPath.'/'.$value;
            if(is_dir($imgFolder)){
                $this->imgFileCopy($imgFolder);
            }else{
                echo "abc<br>";
            }
        }

    }

    //处理图片,并保存为缩略图
    public function imgFileCopy($fileFolder){
        $fileNames=$this->myreaddir($fileFolder);
        import ( 'ORG.Util.Image' );
        foreach($fileNames as $key => $value){
            $imgPathYuan=$fileFolder."/".$value;
            $imgNameArr=explode(".",$value);
            $thumbname=$imgNameArr[0]."_64.".$imgNameArr[1];

            $imgPathNew=$fileFolder ."/". $thumbname;
            echo "OLD:".$imgPathYuan."New:";

            echo $imgPathNew."<br>";
            Image::thumb2 ( $imgPathYuan, $imgPathNew, '', 64, 64, true );
        }
    }


    //*****************************************************************//
//函数名:myreaddir($dir)
//作用:读取目录所有的文件名
//参数:$dir 目录地址
//返回值:文件名数组
//*****************************************************************//
    function myreaddir($dir) {
        $handle=opendir($dir);
        $i=0;
        while($file=readdir($handle)) {
            if (($file!=".")and($file!="..")) {
                $list[$i]=$file;
                $i=$i+1;
            }
        }
        closedir($handle);
        return $list;
    }
}