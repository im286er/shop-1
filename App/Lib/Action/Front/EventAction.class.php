<?php

class EventAction extends CommonAction
{
    const orderNum = 5;

    public function __construct()
    {
        parent::__construct();
        $this->DBF = new DBF();
        $this->header = "default";
    }

    public function xmas2015()
    {
        $this->assign('header', $this->header);
        $this->_renderPage();
    }
    
    public function caibao()
    {
        $this->assign('header', $this->header);
        $this->_renderPage();
    }
    
    public function newyear2016(){
        $this->assign('header', $this->header);
        $this->_renderPage();
    }
    
    public function vday2016(){
        $this->assign('header', $this->header);
        $this->_renderPage();
    }

    public function womenday2016(){
        $this->assign('header', $this->header);
        $this->_renderPage();
    }
    /***电影专题***/
    public function xyqm2016(){
        $this->display();
    }

    /***预约系列***-----------------------------------*start*/
    public function subscribe2016(){
        $this->assign('header',$this->header);
        $this->_renderPage();
    }
    public function oresult(){
        $msg = $_GET['msg'];
        $id = $_GET['id'];
        if($msg ==1){
            $info = M('user_subscribe')->where("id=$id")->find();
        }
        $this->assign('msg',$msg);
        $this->assign('info',$info);
        $this->_renderPage();
    }
    public function doSubscribe(){
        $arr['timetype'] = $_POST['radio1'];
        $arr['name']=$_POST['name'];
        $arr['tel']=$_POST['phone'];
        $arr['company']=$_POST['company'];
        $arr['ordertime']=$_POST['date'];
        $arr['desc']=$_POST['desc'];
        $TDS = M('user_subscribe');
        $res = $this->getUserNuique($_POST['phone'],$_POST['date']);
        if($res){
            $re = $TDS->add($arr);
            //$res = $this->getNumByTtype($ttype);
            if($re){
                $result = 1;
            }else{
                $result = 2;
            }
        }else{
            $re = M('user_subscribe')->where($arr)->getField('id');
            //请勿重复提交
            $result = 3;
        }
        $this->redirect('Event/oresult', array('msg' => $result,'id'=>$re));
    }
    public function getNumBydate(){
        $num = self::orderNum;
        $ordertime = $_POST['datas'];
        $sql = 'select * from (select timetype,count(*) as num from tdf_user_subscribe where ordertime ="'.$ordertime.'" group by timetype)b where num>='.$num;
        $info = M()->query($sql);
        $this->ajaxReturn($info,JSON);
    }

    /**
     * 验证是否同一天同一人多次提交预约
     * @param $tel 预约手机号
     * @param $ordertime 预约日期
     * @param $timetype 预约时间段
     ***/
    private function getUserNuique($tel,$ordertime){
        $arr['tel']=$tel;
        //$arr['timetype']=$timetype;
        $arr['ordertime']=$ordertime;
        $res = M('user_subscribe')->where($arr)->find();
        if($res){
            return false;
        }
        return true;
    }
    /***预约系列***-----------------------------------*end*/
}
?>