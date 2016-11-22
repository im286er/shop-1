<?php
class CouponTypeModel extends Model{
    
    public function __construct(){
        parent::__construct();
    }
    
    protected $_auto = array(
        array("et_createdate","get_now",1,"function")
    );
    
    protected $_map = array(
        'name'=>'et_name',
        'limitamount'=>'et_limitamount',
        'type'=>'et_type',
        'private'=>'et_private',
        'percent'=>'et_percent',
        'amount'=>'et_amount',
        'mamount'=>'et_mamount',
        'usecount'=>'et_usecount',
        'expiredate'=>'et_expiredate'
    );
    protected $fields = array(
        'et_name',
        'et_private',
        'et_type',
        'et_percent',
        'et_amount',
        'et_mamount',
        'et_usecount',
        'et_createdate',
        'et_limitamount',
        'et_operator',
        'et_expiredate',
        '_pk' => 'et_id'//切记，关联表的主键设置
    
    );
}
?>