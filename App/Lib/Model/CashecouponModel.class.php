<?php
class CashecouponModel extends Model{
    public function __construct(){
        parent::__construct();  
    }
    protected $_map = array(
        "amount"=>"ca_amount",
        "expiredate"=>"ca_expiredate"
    );
    protected  $_auto = array(
        //array("ca_status",1),
        array("ca_code","getCode",1,"callback"),
        array("ca_createdate","get_now",1,"function")
    );
    
    protected function getCode($num=10){
        $data = substr(microtime(),2,6);
        $data .=generate_password(26);
        $data = md5($data);
        $data = substr($data, mt_rand(0,strlen($data)-$num), $num);
        $data = strtoupper($data);
        return $data;
    }
        
}
?>