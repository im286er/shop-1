<?php

class CouponModel extends RelationModel
{

    public function  __construct()
    {
        parent::__construct();
    }

    protected $_auto = array(
        array(
            'ec_ctime',
            'get_now',
            1,
            'function'
        ),
        array(
            'ec_code',
            'getCodeDigi',
            1,
            'callback'
        )
    )
    // array('ec_status',1)
    ;

    protected $_map = array()
    // 'email'=>'ec_owner',
    // 'amount'=>'ec_amount',
    // 'percent'=>'ec_percent',
    // 'ectype'=>'ec_type',
    // 'private'=>'ec_private',
    // 'expiredate'=>'ec_expiredate',
    // 'limitamount'=>'ec_limitamount'
    ;

    protected $_link = array(
        'CouponType' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'CouponType', // 可以不用
            'foreign_key' => 'etId',
            'as_fields' => 'et_type,et_private,et_percent,et_amount,et_mamount,et_usecount,et_createdate,et_expiredate,et_limitamount,et_name'
        )
    )
    ;

    public function getCode($num = 10)
    {
        $data = substr(microtime(), 2, 6);
        $data .= generate_password(26);
        $data = md5($data);
        $data = substr($data, mt_rand(0, strlen($data) - $num), $num);
        $data = strtoupper($data);
        return $data;
    }

    /**
     * miaomin added@2016.5.19
     *
     * @param int $num
     */
    public function getCodeDigi(){
        return substr(substr(uniqid('', true), 15).substr(microtime(), 2, 8),2,8);
    }

    public function getUnuseNum($uid)
    {
        $User = M('users');
        $User->getByU_id($uid);
        $getnow = get_now();
        $enum = $this->join("tdf_coupon_type ON (tdf_coupon.etId = tdf_coupon_type.et_id)")
            ->where("tdf_coupon.ec_owner = '{$User->u_email}' and (tdf_coupon_type.et_expiredate > '$getnow' or tdf_coupon_type.et_expiredate = 0) AND tdf_coupon.ec_status = 1")
            ->count();
        return $enum;
    }
    
    // 获得当前用户的有效优惠券
    /**
     * miaomin edited@2015.8.6
     *
     * 在用户选择优惠券时应考虑到活动用券
     *
     * @return NULL|Ambigous boolean, mixed, multitype:multitype: >
     */
    public function getcodes($productList = NULL)
    {
        $user = M('users')->getFieldByU_id(session('f_userid'), 'u_email');
        if ($user == NULL) {
            return NULL;
        } else {
            $sql = "select t2.et_limitamount,t2.et_name,t2.et_type,t1.ec_code,t2.et_type,t2.et_amount,t2.et_percent from tdf_coupon t1 left join tdf_coupon_type t2 on t1.etId = t2.et_id where t1.ec_owner = '{$user}' and t1.ec_status = 1 and (t2.et_expiredate = 0 or t2.et_expiredate > now());";
            $arr = $this->query($sql);
            // print_r($arr);
            
            // 符合活动条件的优惠券
            /*
            $Userprepaid = M('user_prepaid');
            $UParr = $Userprepaid->getByUp_orderid($up_orderid);
            $UPDM = new UserPrepaidDetailModel();
            $updmRes = $UPDM->getPrepaidDetailByUpid($UParr['up_id']);
            $odPList = unserialize($updmRes['up_product_info']);
            echo 'xxxx';
            print_r($odPList);
            */
            $odPList = $productList;
            if ($odPList) {
                $SPProductM = new SPProductModel();
                $SPPropM = new SPPropModel();
                $spDeatailArr = array();
                
                import('App.Model.CartItem.AbstractCartItem');
                import('App.Model.CartItem.CartItemDiyModel');
                foreach ($odPList as $key => $val) {
                    // 非DIY
                    if ($val['p_producttype'] == 5) {
                        $spDetailItem = $SPProductM->getSPDetailByPid($val['p_id']);
                        if ($spDetailItem) {
                            foreach ($spDetailItem as $k => $v) {
                                if ($v['spm_type'] == 2) {
                                    $spDeatailArr[] = $v;
                                }
                            }
                        }
                    } elseif ($val['p_producttype'] == 4) {
                        // 根据P_DIY_ID去TDF_USER_DIY找到P_DIY_CATE_CID再根据P_DIY_CATE_CID找BELONGPID
                        $belongPID = CartItemDiyModel::getBelongPid($val['p_id']);
                        if ($belongPID) {
                            $spDetailItem = $SPProductM->getSPDetailByPid($belongPID);
                            foreach ($spDetailItem as $k => $v) {
                                if ($v['spm_type'] == 2) {
                                    $spDeatailArr[] = $v;
                                }
                            }
                        }
                    }
                }
                // print_r($spDeatailArr);
                foreach ($spDeatailArr as $key => $val) {
                    $condition = array(
                        $SPPropM->F->SPID => $val['spm_id'],
                        $SPPropM->F->SPITEMID => 9
                    );
                    $sppropRes = $SPPropM->where($condition)->find();
                    if ($sppropRes) {
                        $sql = "select t2.et_limitamount,t2.et_name,t2.et_type,t1.ec_code,t2.et_type,t2.et_amount,t2.et_percent from tdf_coupon t1 left join tdf_coupon_type t2 on t1.etId = t2.et_id where t1.etId = '{$sppropRes[$SPPropM->F->SPPVAL]}'";
                        $spCouponItem = $this->query($sql);
                        if ($spCouponItem) {
                            $arr[] = $spCouponItem[0];
                        }
                    }
                }
            }
            // print_r($arr);
            return $arr;
        }
    }
}
?>