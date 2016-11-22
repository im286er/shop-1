<?php
class PaybillAction extends Action{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        
    }   
    private function pbif($id){
        $sql = "select t1.id,t1.billtype,t1.billcompany,t2.up_amount,t2.up_orderbacktime,t2.up_addressee from tdf_user_paybill t1 left join tdf_user_prepaid t2 on t1.up_orderid=t2.up_orderid where t1.up_orderid='{$id}' and t1.status='1' order by asktime desc limit 1;";
    
        $arr = M('user_paybill')->query($sql);
        $ar = $arr[0];
    
        /*
         * 如果是多个商品，应更改$KBNR = "开票内容1#2#3" $DJ='1#2#3' $ SL ='1#1#1' 可供以后扩展
        */
        $RQ = $ar['up_orderbacktime'];
        $FPLX = 0;
        $PNAME = 0;
        $KPR = $ar['billtype'] == 1?0:1;
        $KHMC = $ar['billtype'] == 1?$ar['up_addressee']:$ar['billcompany'];
        $CPLX = 3;
        $KBNR = "服务费";
        $DJ = $ar['up_amount']."";
        if($FPLX == 1){//可以供以后扩展专用发票
            $NSRDJH = "";
            $DZ = "";
            $DH = "";
            $KHH = "";
            $YHZH = "";
        }
        else{
            $NSRDJH = "";
            $DZ = "";
            $DH = "";
            $KHH = "";
            $YHZH = "";
        }
    
    
        $DDBH = $id;
        $TIME = get_now();
        $SECRETCODE = md5($TIME."GDI+2015");
        $SL = "1";
    
        $url = "RQ=$RQ&FPLX=$FPLX&PNAME=$PNAME&KPR=$KPR&KHMC=$KHMC&CPLX=3&NSRDJH=$NSRDJH&DZ=$DZ&DH=$DH&KHH=$KHH&YHZH=$YHZH&DDBH=$DDBH&TIME=$TIME&SECRETCODE=$SECRETCODE&SL=$SL&KBNR=$KBNR&DJ=$DJ";
        try {
            $client = new SoapClient("http://140.207.154.14:9000/bpm/YZSoft/WebService/MHWTWebService.asmx?wsdl",array('encoding'=>'UTF-8'));
            $res = $client->CreateMHWT_KTPSQD_Process(array('json'=>$url));
            $output = $res->CreateMHWT_KTPSQD_ProcessResult;
            //添加请求记录
            $LP = M('log_paybill');
            $data['lp_orderid'] = $id;
            $data['lp_applystr'] = $url;
            $data['lp_return'] = $output;
            $data['lp_operateid'] = 0;
            $LP->add($data);
            if($output == "Success 200"){
                //更新paybill状态
                $data['status'] = 2;
                if(M('user_paybill')->where("id = {$ar['id']}")->save($data)){
                    return true;
                }
                else{
                    return false;
                }
            }
            else{
                return false;
            }
        } catch (SOAPFault $e) {
            return false;
        }
    
    }
    /*
     * 自动开票
     */
    public function autoapply(){
        $sql = "select up_orderid from tdf_user_paybill where status = '1' and asktime>'2015-03-30 00:00:00';";
        $arr = M('user_paybill')->query($sql);
        //var_dump($arr);
        foreach($arr as $ar){
            var_dump($this->pbif($ar['up_orderid']));
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
}
?>