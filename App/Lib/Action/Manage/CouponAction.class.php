<?php

 class CouponAction extends CommonAction{
     
     public function __construct(){
         parent::__construct();
     }
     
     public function index(){
         $this->display();
     }
     /*
      * 生成
      */
     public function generate(){
         /*if($this->isPost()){
             if($this->_post('num') == NULL){
                 $num = 1;
             }
             else{
                 $num = $this->_post('num');
             }
             $this->verifyinput();
             $this->createcoupon($num);
         }
         */
         if($this->isPost()){
             if($this->_post('etid') == "类别"){
                 $this->error("类别为空");
             }
             $this->createcoupon($this->_post('num'),$this->_post('etid'));
         }
         $ET = new CouponTypeModel();
         $arr = $ET->select();
         $this->assign('list',$arr);
         $this->display();
     }
     
     private function createcoupon($num,$etid){
         $etm = new CouponTypeModel();
         $etmRes = $etm->find($etid);
         if ($etmRes){
             for ($i=0;$i<$num;$i++){
                 $Coupon = new CouponModel();
                 $Coupon->create($this->_post());
                 $Coupon->ec_creater = $this->_session('authId');
                 $Coupon->etId = $etid;
                 $Coupon->ec_uc = $etmRes['et_usecount'];
                 $Coupon->add();
             }   
         }
     }
     /*
      * 管理
      */
     public function manage($orderby = "ec_id", $desc = "desc")
     {
         $admin = $this->_session('authId');
         $searchType = I('searchSelect', 0, 'intval');
         //判断查询条件
         $status = I('status', 4, 'intval');
         if ($searchType == 0) {
             //状态查询,判断查询状态
             $status = I('status', 4, 'intval');
             if ($status == 4) {
                 $condition = "1=1";
             } else {
                 $condition['a.ec_status'] = $status;
             }
         } else if ($searchType == 1) {
             //序号查询
             $id = $_POST['ec_id'];
             $condition['a.ec_id'] = $id;
         } else if ($searchType == 2) {
             //名称查询
             $et_name = I('et_name');
             $condition['b.et_name'] = array('like', '%' . $et_name . '%');
         } else {
             //生成时间查询
             $timeStart = I('timeStart', '2014-12-25', 'string');
             $timeEnd = I('timeEnd', get_now('Y-m-d'), 'string');
             $condition['a.ec_ctime'] = array(array('egt', $timeStart), array('elt', $timeEnd));
         }
         $count = M('coupon')->alias('a')->join('tdf_coupon_type b on b.et_id=a.etId ')->where($condition)->count();
         import("ORG.Util.Page");
         $Page = new Page($count, 15);
         $show = $Page->show();
         $arr = M('coupon')->alias('a')->join('tdf_coupon_type b on b.et_id=a.etId ')->order($orderby . " " . $desc)->limit($Page->firstRow . ',' . $Page->listRows)->where($condition)->select();
         /********将参数传至页面***********/
         if (empty($timeStart)) {
             $timeStart = '2014-12-25';
         }
         if (empty($timeEnd)) {
             $timeEnd = date('Y-m-d', time());
         }
         $this->assign('searchType', $searchType);
         $this->assign('status', $status);
         $this->assign('ecid', $id);
         $this->assign('etname', $et_name);
         $this->assign('timeStart', $timeStart);
         $this->assign('timeEnd', $timeEnd);
         /******************************/
         $this->assign('list', $arr);
         $this->assign('page', $show);
         $this->display();
     }

     //优惠券导出功能
     public function doExcel()
     {
         echo header("Content-type:text/html;charset=utf-8");
         $arr = $this->_post();
         if ($arr['status'] == '4') {

         } else {
             $condition['a.ec_status'] = $arr['status'];
         }
         if (!empty($arr['ec_id'])) {
             $condition['a.ec_id'] = $arr['ec_id'];
         }
         if (!empty($arr['et_name'])) {
             $condition['b.et_name'] = array('like', '%' . $arr['et_name'] . '%');
         }
         if (!empty($arr['timeStart']) && !empty($arr['timeEnd'])) {
             $condition['a.ec_ctime'] = array(array('egt', $arr['timeStart']), array('elt', $arr['timeEnd']));
         }
         $xlsData = M('coupon')->alias('a')->join('tdf_coupon_type b on a.etId=b.et_id ')->where($condition)->select();
         $xlsName = '优惠码' . date('y/m/d', time());

         Vendor('PHPExcel.PHPExcel', '', '.php');
         $objExcel = new PHPExcel();
         //设置一些基本参数
         $objExcel->getProperties()->setCreator("Mr.guo")//创建者
         ->setLastModifiedBy("Mr.guo")//最后修改者
         ->setTitle("优惠码列表");//标题
         //设置表的名称标题
         $objExcel->setActiveSheetIndex(0)
             ->setCellValue('A1', "序号")
             ->setCellValue('B1', "名称")
             ->setCellValue('C1', "优惠码")
             ->setCellValue('D1', "类型")
             ->setCellValue('E1', "抵扣（元）")
             ->setCellValue('F1', "折扣")
             ->setCellValue('G1', "公开状态")
             ->setCellValue('H1', "使用次数")
             ->setCellValue('I1', "过期时间")
             ->setCellValue('J1', "生成时间")
             ->setCellValue('K1', "订单金额限制");
         //循环写入数据到EXCEL
         foreach ($xlsData as $k => $v) {
             $num = $k + 2;
             $objExcel->setActiveSheetIndex(0)
                 ->setCellValue('A' . $num, $v['ec_id'])
                 ->setCellValue('B' . $num, $v['et_name'])
                 ->setCellValue('C' . $num, ' '.$v['ec_code'])//确保导出首位为0不显示问题
                 ->setCellValue('D' . $num, $v['et_type'])
                 ->setCellValue('E' . $num, $v['et_amount'])
                 ->setCellValue('F' . $num, $v['et_percent'] . '%')
                 ->setCellValue('G' . $num, $v['et_private'])
                 ->setCellValue('H' . $num, $v['ec_creater'])
                 ->setCellValue('I' . $num, $v['et_expiredate'])
                 ->setCellValue('J' . $num, $v['ec_ctime'])
                 ->setCellValue('K' . $num, $v['et_limitamount']);
         }
         $objExcel->getActiveSheet()->setTitle('优惠码信息列表');
         $objExcel->setActiveSheetIndex(0);
         ob_end_clean();//清除缓冲区，避免乱码
         header('Content-Type: application/vnd.ms-excel');
         header('Content-Disposition: attachment;filename="' . $xlsName . '.xls"');
         header('Cache-Control: max-age=0');
         $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
         $objWriter->save('php://output');
         exit;
     }

     /*    public function manage1($orderby = "ec_id",$desc = "desc",$fieldn = "",$fieldv = ""){
             $admin = $this->_session('authId');
             $Coupon = new CouponModel();
             //$condition['ec_creater'] = $admin;
             $condition[$fieldn] = $fieldv;
             $count = $Coupon->where($condition)->count();
             import("ORG.Util.Page");
             $Page = new Page($count,2);
             $show = $Page->show();
             $arr = $Coupon->order($orderby." ".$desc)->limit($Page->firstRow.','.$Page->listRows)->where($condition)->relation(true)->select();
             //dump($Coupon);
             //echo $Coupon->getLastSql();
             $this->assign('list',$arr);
             $this->assign('page',$show);
             $this->display();
             
         }*/

     /*
      * 搜索
      */
     public function search(){
         if($this->isPost()){
             $field = $this->_post('xx');
             $code = $this->_post('code');
             $Coupon = new CouponModel();
             $sql = "select t4.*,t3.up_id,t1.* ,t2.log_orderid,t2.log_usedate from tdf_coupon t1 left join tdf_log_coupon t2 on t1.ec_code=t2.log_eccode Left join tdf_user_prepaid t3 on t3.up_orderid = t2.log_orderid left join tdf_coupon_type t4 on t4.et_id=t1.etId where $field = '{$code}'";
             //$sql = "select t3.up_id,t1.* ,t2.log_orderid,t2.log_usedate from tdf_coupon t1 left join tdf_log_coupon t2 on t1.ec_code=t2.log_eccode Left join tdf_user_prepaid t3 on t3.up_orderid = t2.log_orderid where t1.$field = '{$code}'";
             //$arr = $Coupon->where("{$field} = '{$code}' and ec_creater = '{$this->_session('authId')}'")->select();
             $arr = $Coupon->query($sql);
             $this->assign('list',$arr);
             $this->display();

         }
         else{
             $Coupon = new CouponModel();
             
             import('ORG.Util.Page');
             $count = $Coupon->count();
             $Page = new Page($count,25);
             $sql = "select t4.*,t3.up_id,t1.* ,t2.log_orderid,t2.log_usedate from tdf_coupon t1 left join tdf_log_coupon t2 on t1.ec_code=t2.log_eccode Left join tdf_user_prepaid t3 on t3.up_orderid = t2.log_orderid left join tdf_coupon_type t4 on t4.et_id=t1.etId limit $Page->firstRow,$Page->listRows";
             
             
             $show = $Page->show();
             $arr = $Coupon->query($sql);

             $this->assign('list',$arr);
             
             $this->assign('page',$show);
             $this->display();
         }
         
     }
     /*
      * 生成验证输入
      */
     private function verifyinput(){
         if($this->_post('num')>200 || $this->_post('num')< 0){
             $this->error('数量不能一次超过200');
         }
         //if($this->_post('email') == NULL){
          //   $this->error('emial不能为空');
         //}
         if($this->_post('percent') > 100 || $this->_post('percent') < 0){
            $this->error('折扣百分比请输入1-100之间的数字');
         }
         if($this->_post('expiredate') != 0 && $this->_post('expiredate') < get_now()){
             $this->error('过期日期不能小于现在');
         }
     }
     /*
      * 公共优惠券启用停用
      */
    public function publicoperation($action,$id){
        $Coupon = M('coupon');
        
        if($action == "stopec"){
            $data['ec_status'] = 0;
            $Coupon->where("ec_id = '{$id}'")->save($data);
            $this->success("操作成功","__URL__/manage");
        }
        elseif($action == "startec"){
            //暂停所有公开券
            //$data1['ec_status'] = 0;
            //$Coupon->where("et_private = 1")->save($data1);
            //启用一个
            $data['ec_status'] = 1;         
            $Coupon->where("ec_id = '{$id}'")->save($data);
            $this->success("操作成功","__URL__/manage");
        }
        else{
            $this->error("操作失败");
        }
        
    }
     
    /*
     * 批量启用
     */
    public function activelists(){
        if($this->_post('ec_owner') == NULL){
            $this->error("邮箱不能为空");
        }
        $arr = $this->_post('n');
        $ecowner = explode(";", trim( $this->_post('ec_owner') ));
        if( count($arr) != count($ecowner) ){
            $this->error("邮箱与优惠码数量需要匹配");
        }
        $Coupon = new CouponModel();
        foreach ($arr as $key=>$id){
            $Coupon->relation(true)->getByEc_id($id);
            if( $Coupon->ec_status == 0){
                $data['ec_status'] = 1;
                if($Coupon->et_private == 2){
                    $data['ec_owner'] = trim($ecowner[$key]);
                }
                $Coupon->where("ec_id='{$id}'")->save($data);
                $this->success("启用成功 ".$Coupon->ec_code);
            }
            else{
                $this->error("启用失败  ".$Coupon->ec_code);
            }
        }
        
    }
    /*
     * 批量邮箱发送启用
     */
    public function maillists(){
        $preg = '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/';
        if( preg_match($preg, $this->_post('ec_owner')) !=  1){
            $this->error("邮箱验证错误");
        }
        $arr = $this->_post('n');
        $Coupon = new CouponModel();
        foreach ($arr as $id){
            $Coupon->relation(true)->getByEc_id($id);
            if( $Coupon->ec_status == 0){
                $data['ec_status'] = 1;
                if($Coupon->et_private == 2){
                    $data['ec_owner'] = $this->_post('ec_owner');
                }
                //邮箱发送
                $to = $this->_post('ec_owner');
                $name = $to;
                $title="曼恒蔚图发来的优惠券";
                $content = "$Coupon->ec_code";
                if( $Coupon->where("ec_id='{$id}'")->save($data) ){
                    think_send_mail($to, $name,$title,$content);
                    // exit();
                    $this->success("发送成功 ".$Coupon->ec_code);
                }
                else{
                    $this->error("发送失败");
                }
                
            }
            else{
                $this->error("启用失败  ".$Coupon->ec_code);
            }
        }
    }
    /*
     * 批量删除
     */
    public function droplists(){
        $arr = $this->_post('n');
        $Coupon = M('coupon');
        foreach ($arr as $id){
            $Coupon->getByEc_id($id);
            if( $Coupon->ec_status != 2){
                $Coupon->where("ec_id='{$id}'")->delete();
                $this->success("删除成功 ".$Coupon->ec_code);
            }
            else{
                $this->error("删除失败  ".$Coupon->ec_code);
            }
        }
    }
    /*
     * 批量无条件激活(私人券)
     */
    public function freeactive(){
        $arr = $this->_post('n');
        $Coupon = new CouponModel();
        foreach ($arr as $id){
            $Coupon->relation(true)->getByEc_id($id);
            if( $Coupon->ec_status == 0){
                $data['ec_status'] = 1;
                $Coupon->where("ec_id='{$id}'")->save($data);
                $this->success("启用成功 ".$Coupon->ec_code);
            }
            else{
                $this->error("启用失败  ".$Coupon->ec_code);
            }           
        }
    
    }
    
    public function test(){
        $CO = new CouponModel();
        $arr = $CO->relation(true)->find(58);


        //echo $CO->getLastSql();
        $CT = new CouponTypeModel();
        

    }
    /*
     * 字典添加
     */
    public function addi(){
        $ET = new CouponTypeModel();
        if($this->isPost()){
            $ET = new CouponTypeModel();
            $ET->create();
            $ET->et_operator = session('authId');
            if($ET->add()){
                $this->success("添加成功","__URL__/generate");
            }
            else{
                $this->error("错误");
            }
        }
        $arr = $ET->select();
        $this->assign('list',$arr);
        $this->display();
    }
    /*
     * 字典编辑
     */
    public function eddi($et_id){
        $list = M('coupon_type')->getByEt_id("$et_id");
        $this->assign('list',$list);
        $this->display();
    }
    public function guimijiedetail(){
        $sql2 = "select count(*) n from tdf_coupon t1 left join tdf_coupon_type t2 on t1.etId = t2.et_id where t2.et_name='闺蜜节';";        
        import('ORG.Util.Page');
        $num = M('coupon_tpye')->query($sql2);
        $Page = new Page($num[0]['n'],20);
        $show = $Page->show();        
        $sql = "select t3.u_dispname, t1.ec_status,t1.ec_id,t1.ec_code,ec_owner,t1.ec_ctime from tdf_coupon t1 left join tdf_coupon_type t2 on t1.etId = t2.et_id left join tdf_users t3 on t3.u_email = t1.ec_owner where t2.et_name='闺蜜节' limit {$Page->firstRow},{$Page->listRows};";
        $list = M('coupon')->query($sql);
        //dump($list);
        
        $sql3 = "select count(*) n from tdf_coupon t1 left join tdf_coupon_type t2 on t1.etId = t2.et_id where t2.et_name='闺蜜节' and t1.ec_status = 2;";
        $num_used = M('coupon_tpye')->query($sql3);
        
        $this->assign('num',$num[0]['n']);
        $this->assign('num_used',$num_used[0]['n']);
        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }
    
     
     
     
     
     
     
     
     
     
     
     
     
 }
 ?>