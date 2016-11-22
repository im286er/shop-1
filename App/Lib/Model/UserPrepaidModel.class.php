<?php
class UserPrepaidModel extends Model {

    /**
     *
     * @var DBF
     */
    protected $DBF;

    /**
     *
     * @var DBF_UserPrepaid
     */
    public $F;
    protected $tableName = 'tdf_user_prepaid';
    protected $fields = array (
        'up_id',
        'up_uid',
        'up_amount',
        'up_dealdate',
        'up_ipaddress',
        'up_status',
        'up_paytype',
        'up_orderid',
        'up_express',
        'up_productid',
        'up_type',
        'up_efee',
        'p_id',
        'agent_userid',
        '_pk' => 'up_id',
        '_autoinc' => TRUE
    );
    public function __construct() {
        $this->DBF = new DBF ();

        $this->F = $this->DBF->UserPrepaid;

        $this->trueTableName = $this->F->_Table;

        $this->fields = $this->F->getFields ();

        if (! $this->_map) {
            $this->_map = $this->F->getMappedFields ();
        }

        parent::__construct ();
    }
    public function getPrepaidListByUID($UID) {
        $DBF_UP = $this->DBF->UserPrepaid;
        return $this->where ( $DBF_UP->UserID . "='" . $UID . "'" )->order('ctime desc')->select ();
    }
    public function getPrepaidListByOrderid($Orderid) {
        $DBF_UP = $this->DBF->UserPrepaid;
        return $this->where ( $DBF_UP->OrderID . "='" . $Orderid . "'" )->select ();
    }

    public function getPrepaidListByOrderidNew($Orderid) {
        $DBF_UP = $this->DBF->UserPrepaid;
        return $this->where (  "up_orderid_new='" . $Orderid . "'" )->select ();
    }
    public function getPrepaidListByUpid($upid) {
        $DBF_UP = $this->DBF->UserPrepaid;
        return $this->where ( $DBF_UP->ID . "='" . $upid . "'" )->select ();
    }
    public function getPrepaidListByThirdOrderid($uid,$thirdOrderid){
        $DBF_UP = $this->DBF->UserPrepaid;
        return $this->where ( $DBF_UP->UserID. "='" . $uid . "' and third_orderid='".$thirdOrderid."'" )->select ();
    }


    //填加订单表
    public function addRecord($UID, $Amount, $IP, $Status, $Orderid, $up_paytype, $up_productid = "0", $up_type, $up_express = 0,$ua_id=0) {
        if($ua_id){
            $UAM=new UserAddressModel();
            $address=$UAM->getAddressByID($ua_id);//获取用户地址数组
            $AIPM = new AreaInfoPickerModel();
            $addressArea=$UAM->getDispArea($AIPM,$address['ua_province'],$address['ua_city'],$address['ua_region']);
        }
        $efee = 0;
        if ($up_express == 1) {
            $efee = C ( 'UP_EFEE' );
        }
        $DBF_UP = $this->DBF->UserPrepaid;
        $data = array (
            $DBF_UP->UserID => $UID,
            $DBF_UP->Amount => $Amount,
            $DBF_UP->DealDate => get_now (),
            $DBF_UP->IPAddress => $IP,
            $DBF_UP->OrderID => $Orderid,
            $DBF_UP->PayType => $up_paytype,
            $DBF_UP->ProductID => $up_productid,
            $DBF_UP->Type => $up_type,
            $DBF_UP->Express => $up_express,
            $DBF_UP->Efee => $efee,
            $DBF_UP->Mobile => $address['ua_mobile'],
            $DBF_UP->Addressee => $address['ua_addressee'],
            $DBF_UP->Address => $addressArea.' '.$address['ua_address'],
            $DBF_UP->Status => $Status
        );
        return $this->add ( $data );
    }

    //VR专用,暂时不用
    public function addRecordByVR($UID, $Amount, $IP, $Status, $Orderid, $up_paytype, $up_productid = "0", $up_type, $up_express = 0) {
        $efee = 0;
        if ($up_express == 1) {
            $efee = C ( 'UP_EFEE' );
        }
        $DBF_UP = $this->DBF->UserPrepaid;
        $data = array (
            $DBF_UP->UserID => $UID,
            $DBF_UP->Amount => $Amount,
            $DBF_UP->DealDate => get_now (),
            $DBF_UP->IPAddress => $IP,
            $DBF_UP->OrderID => $Orderid,
            $DBF_UP->PayType => $up_paytype,
            $DBF_UP->ProductID => $up_productid,
            $DBF_UP->Type => $up_type,
            $DBF_UP->Express => $up_express,
            $DBF_UP->Efee => $efee,
            $DBF_UP->Mobile => $address['ua_mobile'],
            $DBF_UP->Addressee => $address['ua_addressee'],
            $DBF_UP->Address => $addressArea.' '.$address['ua_address'],
            $DBF_UP->Status => $Status,
            $DBF_UP->Vrsign =>1
        );
        return $this->add ( $data );
    }


    public function updatePaytypeByOrderid($Orderid, $paytypeid, $uaid = 0, $order_bz='') { // 更新支付方式和收获地址
        $UP = M ( "user_prepaid" );
        $data['up_order_bz'] = $order_bz;
        $data ['up_paytype'] = $paytypeid;
        $data ['up_uaid'] = $uaid;
        $UA = new UserAddressModel ();
        $addressinfo = $UA->getAddressByID ( $uaid );
        $AIPM = new AreaInfoPickerModel ();
        $city = $AIPM->getarea ( $addressinfo ['ua_province'], $addressinfo ['ua_city'], $addressinfo ['ua_region'] );
        if ($addressinfo) {
            $data ['up_address'] = $city . " " . $addressinfo ['ua_address'];
            $data ['up_addressee'] = $addressinfo ['ua_addressee'];
            $data ['up_mobile'] = $addressinfo ['ua_mobile'];
            $data ['up_phone'] = getphonenum ( $addressinfo ['ua_phonepre'], $addressinfo ['ua_phone'], $addressinfo ['ua_phoneext'] );
            $data ['up_zipcode'] = $addressinfo ['ua_zipcode'];
        }
        return $UP->where ( "up_orderid='" . $Orderid . "'" )->setField ( $data );
        // $UP = M("user_prepaid");
        // return $UP-> where("up_orderid='" . $Orderid .
        // "'")->setField('up_paytype',$paytypeid);
    }

    /*
     * 得到用户当前看到的订单状态: 1-提交订单 2-等待付款 3-订单制作中 4-已发货 5-完成 @param up_status 订单支付状态
     * @param orderid 订单的处理单号 返回 1到5的状态值
     */
    public function PrepaidStatus($up_status, $up_id) {
        $UPPM = new UserPrepaidProcessModel ();
        $processStatus = $UPPM->getCurrentProcessByUpid ( $up_id );
        if ($up_status == 1) {
            if ($processStatus >= 3 && $processStatus < 6) {
                $result = 3;
            } elseif ($processStatus >= 6 && $processStatus < 7) {
                $result = 4;
            } elseif ($processStatus == 7) {
                $result = 5;
            } else {
                $result = 2;
            }
        } else {
            $result = 1;
        }
        return $result;
    }

    /*
     * 根据状态输出HTML视图 @param PrepaidStatus 用户看到的订单状态 	1-提交订单 2-等待付款 3-订单制作中 4-已发货
     * 5-完成
     */
    public function getPrepaidStatusHtml($PrepaidStatus) {
        switch ($PrepaidStatus) {

            case 1 :
                $result = "<h5 class='tjdd1'>提交订单</h5>";
                $result .= "<h4 class='tjdd2'>付款成功</h4>";
                $result .= "<h3 class='tjdd3'>订单制作中</h3>";
                $result .= "<h2 class='tjdd4'>等待收货</h2>";
                $result .= "<h6 class='tjdd5'>完成</h6>";
                break;
            case 2 :
                $result = "<h5 class='tjdd1'>提交订单</h5>";
                $result .= "<h4 class='ddfk2'>付款成功</h4>";
                $result .= "<h3 class='tjdd3'>订单制作中</h3>";
                $result .= "<h2 class='tjdd4'>等待收货</h2>";
                $result .= "<h6 class='tjdd5'>完成</h6>";
                break;
            case 3 :
                $result = "<h5 class='tjdd1'>提交订单</h5>";
                $result .= "<h4 class='ddfk2'>付款成功</h4>";
                $result .= "<h4 class='ddfk2'>订单制作中</h4>";
                $result .= "<h2 class='tjdd4'>等待收货</h2>";
                $result .= "<h6 class='tjdd5'>完成</h6>";
                break;
            case 4 :
                $result = "<h5 class='tjdd1'>提交订单</h5>";
                $result .= "<h4 class='ddfk2'>等待付款</h4>";
                $result .= "<h4 class='ddfk2'>订单制作中</h4>";
                $result .= "<h4 class='ddfk2'>等待收货</h4>";
                $result .= "<h6 class='tjdd5'>完成</h6>";
                break;
            case 5 :
                $result = "<h5 class='tjdd1'>提交订单</h5>";
                $result .= "<h4 class='ddfk2'>等待付款</h4>";
                $result .= "<h4 class='ddfk2'>订单制作中</h4>";
                $result .= "<h4 class='ddfk2'>等待收货</h4>";
                $result .= "<h6 class='ddsh5'>完成</h6>";
                break;
        }
        return $result;
    }

    /*
     * 得到订单跟踪过程 @param up_status 订单支付状态 @param orderid 订单的处理单号 @param up_date
     * 订单日期
     * add mmode =2为第三版手机端-----------glx-2016/10/13
     */
    public function getPrepaidProcess($up_status, $up_id, $up_date,$mmode=0) {
        $UPPM = new UserPrepaidProcessModel ();
        $process = $UPPM->getProcessByUpid ( $up_id );

        /*################################增加第三版手机端Start##################################################*/
        if($mmode ==2){
            $result .= "<h3>您提交了订单，请等待系统确认。</h3><br/><p>". date('Y-m-d', strtotime( $up_date )) ."</p><br/>";
        }
        /*################################增加第三版手机端End##################################################*/

        else if($mmode==1){
            $result .= "<div>" . "<h5>" . date('Y-m-d', strtotime( $up_date )) . " </h5>" . "<h5>您提交了订单，请等待系统确认。</h5><h5>您</h5></div>";
        }else{
            $result = '<div><h5>处理时间</h5><h5>处理信息</h5><h5>操作人</h5></div>';
            $result .= "<div>" . "<h6>" . $up_date . " </h6>" . "<h6> 您提交了订单，请等待系统确认 </h6><h6>您
</h6></div>";
        }

        $processConArr = L ( 'process' );
        $UPEM = new UserPrepaidExpressModel ();
        if ($up_status == 1) {

            /*################################增加第三版手机端Start##################################################*/
            if($mmode ==2){
                foreach ( $process as $key => $value ) {
                    $result .= "<h3>". $processConArr [$value['done_process']] ."</h3><br/><p>" . date('Y-m-d', strtotime($up_date)) . "</p><br/>";
                }
            }
            /*################################增加第三版手机端End##################################################*/

            else if($mmode==1){
                foreach ( $process as $key => $value ) {
                    $result .= "<div>" . "<h6>" . $value ['done_time'] . " </h6>" . "<h6>" . $processConArr [$value
                        ['done_process']] . "</h6>" . "<h6>" . $value ['done_name'] . " </h6>" . "</div>";
                }
            } else {
                foreach ( $process as $key => $value ) {
                    $result .= "<div>" . "<h6>" . $value ['done_time'] . " </h6>" . "<h6>" . $processConArr
                        [$value ['done_process']] . " </h6>" . "<h6>" . $value ['done_name'] . " </h6>" . "</div>";
                    /*
                     * if($value['done_process']==6){
                     * $result.=$UPEM->getExpressHtml($up_id); }
                     */
                }
            }
        }
        return $result;
    }

    /**
     * 得到订单的快递流程 @param up_id 订单的处理单号 输出数组
     */
    public function getPrepaidExpress($up_id) {
        $sql = "select done_process from tdf_user_prepaid_process where up_id=" . $up_id . " and done_process=6";
        $UPPM = M ( 'user_prepaid_process' )->query ( $sql );
        if ($UPPM) {
            $UPEM = new UserPrepaidExpressModel ();
            $processInfo = $UPEM->getExpressByUpid ( $up_id );
            if ($processInfo ['express_number']) {
                $result [0] = 1;
                $process = $UPEM->getExpressHtml ( $up_id );
                $result [1] = $process;
            } else {
                $result [0] = 0;
                $result [1] = 0;
            }
        } else {
            $result [0] = 0;
            $result [1] = 0;
        }
        return $result;
    }

    /**
     * 通过接口查询kudi100的物流状态
     *2016-08-29 zhangzhibin
     */
    public function getExpressApi($up_id){
        $UPEM = new UserPrepaidExpressModel ();
        $expressInfo = $UPEM->getExpressByUpid ( $up_id );
        if($expressInfo['express_com']=='shunfeng'){
            $com=$expressInfo['express_com'];
            $nu=$expressInfo['express_number'];
            $result=$UPEM->getExpressStatus($com,$nu);

        }

    }


    /**
     * 获取带筛选条件的订单数量
     *
     * @param array $condition
     * @return int
     */
    public function getPrepaidListNumByCondition(array $condition) {
        return $this->where ( $condition )->count ();
    }

    /*更新订单的金额，加入up_amount_account用户余额、up_amount_total订单总额、up_amount用户应付
     * @param string orderid
     * @param Array amount_arr 更新的数组 ($amount_arr['up_amount_account'],$amount_arr['up_amount_total'],$amount_arr['up_amount'])
     */
    public function updateAmountByOrderid($Orderid, $amount_arr,$UserAccountInfo) {
        $up_info=$this->getPrepaidListByOrderid($Orderid);
        $up_id=$up_info[0]['up_id'];

        $UPM = M ( "user_prepaid" );
        $UPM->startTrans (); // 在d模型中启动事务
        $step1=$UPM->where("up_orderid='".$Orderid."'")->save($amount_arr);

        $UAM =new UserAccountModel();//更新用户rcoin
        $step2=$UAM->changeRCoin ( $UserAccountInfo, - $amount_arr['up_amount_account'], - $amount_arr['up_amount_account']);

        $LRTM = new LogRTransModel ();//更新日志
        $step3 = $LRTM->addLog ( $UserAccountInfo, $amount_arr['up_amount_account'], 0, 2, $up_id );

        if($step1 && $step2 && $step3){
            $UPM->commit (); // 提交事务
        }else{
            $UPM->rollback (); // 事务回滚
        }
        return $UPM->where ( "up_orderid='" . $Orderid . "'" )->setField ( $amount_arr );
    }

    /*删除订单时，余额返还到用户account中
     * @param string orderid
    * @param Array amount_arr 更新的数组 ($amount_arr['up_amount_account'],$amount_arr['up_amount_total'],$amount_arr['up_amount'])
    */
    public function delAmountByOrderid($Orderid) {

        $up_info=$this->getPrepaidListByOrderid($Orderid);
        $up_id=$up_info[0]['up_id'];
        $up_uid=$up_info[0]['up_uid'];
        $UPM = M ( "user_prepaid" );

        if($up_info[0]['up_amount_account'] ==0){//用户余额支付数等于0
            $data['delsign']	=1;//设置删除标记为1
            $result=$UPM->where("up_orderid='".$Orderid."'")->save($data);
        }else{
            $up_amount_account			=$up_info[0]['up_amount_account']; //返还用户账户金额
            $data['up_amount']			=$up_info[0]['up_amount_total'];//重置用户订单支付金额
            $data['up_amount_account']	=0;//重置用户余额支付数为0
            $data['delsign']			=1;//设置删除标记为1
            $UPM->startTrans (); // 在d模型中启动事务
            $step1=$UPM->where("up_orderid='".$Orderid."'")->save($data);

            $UAM =new UserAccountModel();//更新用户rcoin
            $UserAccountInfo = $UAM->getUserAccountByUid($up_uid);

            $step2=$UAM->changeRCoin ( $UserAccountInfo, $up_amount_account, $up_amount_account);

            $LRTM = new LogRTransModel ();//更新日志
            $step3 = $LRTM->addLog ( $UserAccountInfo, $up_amount_account, 1, 2, $up_id );

            if($step1 && $step2 && $step3){
                $UPM->commit (); // 提交事务
            }
            $result=$UPM->where ( "up_orderid='" . $Orderid . "'" )->setField ( $data );
        }
        return $result;
    }


    public function updateAccountPaytypeByOrderid($Orderid) { // 用余额支付时更新支付方式
        $UP = M ( "user_prepaid" );
        $data ['up_paytype'] = 153;
        return $UP->where ( "up_orderid='" . $Orderid . "'" )->setField ( $data );
    }

    public function updateOrderSuffix($orderid){//订单号后缀加1
        $order_info=$this->getPrepaidListByOrderid($orderid);
        $up_orderid_suffix=$order_info[0]['up_orderid_suffix'];
        $UP = M( "user_prepaid" );
        if($up_orderid_suffix>0){//如果后缀大于0，改变新订单号，后缀加1
            $data['up_orderid_new']=$orderid.($up_orderid_suffix+1);
        }else{//如果后缀=0，不改变订单号，后缀加1
            $data['up_orderid_new']=$orderid;
        }

        if($UP->where ( "up_orderid='" . $orderid . "'" )->setField ($data)){
            $result=$data['up_orderid_new'];
        }else{
            $result=$orderid;
        }

        $UP->where ( "up_orderid='" . $orderid . "'" )->setInc ( "up_orderid_suffix" );
        return $result; //返回订单号
    }


    //由订单up_id得到productlist
    public function getProductListByUpid($up_id){
        $PPD = new UserPrepaidDetailModel ();
        $ProductInfo = $PPD->getPrepaidDetailByUpid ( $up_id );

        $ProductArr = unserialize ( $ProductInfo ['up_product_info'] );
        $UCM=new UserCartModel();
        $DPM = new DiyPrepaidModel ();
        foreach ( $ProductArr as $key => $Product ) {
            switch ($Product ['uc_producttype']) {
                case 1 :
                    $pid = $Product ['p_id'];
                    $product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
                    $product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
                    $product [$key] ['p_name'] = $Product ['p_name']; // 名称
                    $product [$key] ['p_price'] = $Product ['p_price']; // 单价
                    $product [$key] ['p_count'] = $Product ['p_count']; // 数量
                    $product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
                    $product [$key] ['cover'] = $Product ['p_cover']; // 截图
                    $product [$key] ['p_id'] = $Product ['p_id']; // 对应tdf_product产品p_id
                    $product [$key] ['cid'] = $Product ['p_cate_3'];
                    $product [$key] ['p_propid_spec_desc'] = '';
                    break;
                case 2 :
                    $pid = $Product ['p_id'];
                    $product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
                    $product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
                    $product [$key] ['p_name'] = $Product ['p_name']; // 名称
                    $product [$key] ['p_price'] = $Product ['p_price']; // 单价
                    $product [$key] ['p_count'] = $Product ['p_count']; // 数量
                    $product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
                    $product [$key] ['cover'] = $Product ['p_cover']; // 截图
                    $product [$key] ['p_id'] = $Product ['p_id']; // 对应tdf_product产品p_id
                    $product [$key] ['cid'] = $Product ['p_cate_3'];
                    $product [$key] ['p_propid_spec_desc'] = '';
                    break;
                case 4 : // DIY产品
                    $pid = $Product ['p_id'];
                    $upid = $up_id;
                    $udinfo [$key] = $DPM->getUdinfoByUpid ( $upid, $pid );

                    $product [$key] ['uc_producttype']      = $Product ['uc_producttype'];
                    $product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
                    $product [$key] ['p_name']      = $udinfo [$key] ['p_name']; // 名称
                    $product [$key] ['p_price']     = $udinfo [$key] ['price']; // 单价
                    $product [$key] ['p_count']     = $udinfo [$key] ['p_count']; // 数量
                    $product [$key] ['totle_price'] = $udinfo [$key] ['p_count'] * $product [$key] ['p_price']; // 小计价格
                    $product [$key] ['cover']       = WEBROOT_URL.$udinfo [$key] ['cover']; // 截图
                    $product [$key] ['p_id']        = $udinfo [$key] ['p_id']; // 对应tdf_product产品p_id
                    $product [$key] ['cid']         = $udinfo [$key] ['p_cate_4']; // 对应tdf_product产品p_id
                    $value['diy_unit_info']         =$udinfo [$key]['diy_unit_info'];
                    $value['p_cate_4']              = $udinfo [$key] ['p_cate_4'];
                    $value['p_intro']               = $Product ['p_intro'];
                    $product [$key] ['p_propid_spec_desc'] = $UCM->getUserCartDiyByProduct($value); // 对应tdf_product产品p_id
                    break;
                case 5 :
                    $pid = $Product ['p_id'];
                    $product [$key] ['uc_producttype'] = $Product ['uc_producttype'];
                    $product [$key] ['uc_producttype_name'] = show_product_type ( $Product ['uc_producttype'] );
                    $product [$key] ['p_name'] = $Product ['p_name'];   //名称
                    $product [$key] ['p_price'] = $Product ['p_price']; //单价
                    $product [$key] ['p_count'] = $Product ['p_count']; //数量
                    $product [$key] ['totle_price'] = $Product ['p_count'] * $Product ['p_price']; // 小计价格
                    $product [$key] ['cover'] = WEBROOT_URL.$Product ['p_cover']; // 截图
                    $product [$key] ['p_id'] = $Product ['p_belongpid']?$Product ['p_belongpid']:$Product ['p_id']; // 对应tdf_product产品p_id
                    $product [$key] ['cid'] = $Product ['p_cate_3']; // 对应tdf_product产品p_id
                    $product [$key] ['p_propid_spec_desc'] =ProductPropValModel::parseCombinePropVals ( $Product ['p_propid_spec'],
                        ' -- '); // 对应tdf_product产品p_id
                    break;
                default :
            }
        }
        foreach ( $product as $key => $value ) {
            $product [$key] ['cover_64'] = str_replace ( '/o/', '/s/64_64_', $value ['cover'] );
        }
        return $product;
    }






    //APP生成订单
    /**
     * @param $UID
     * @param $Amount
     * @param $up_productid
     * @param $ua_id
     * @return mixed
     */
    public function appPrepaidadd($UID,$ucidString,$ua_id){
       //var_dump($ucidString);
        $UCM=new UserCartModel();
        $UCProductList=$UCM->getProductByUcid($UID,$ucidString);

             //构造pid_array（"pid,count"的数组）
        foreach($UCProductList as $key =>$value){
            $pid_array[]    =$value['p_id'].",".$value['uc_count'];
            $price          =$value['uc_count'] * $value['p_price'];
            $Amount+=$price;
        }

        $DBF_UP = $this->DBF->UserPrepaid;
        //--------------------保存订单数据 2015.07.13
        $up_orderid = $this->get_umorderid();
        $IP="1.1.1.1";
        $up_type=4; //diy订单
        $up_express=1;
        $UPD = new UserPrepaidDetailModel ();
        $this->startTrans();
        $upid = $this->addRecord ( $UID, $Amount, $IP, 0, $up_orderid, 0, serialize ( $pid_array ), $up_type, $up_express,$ua_id);//生成订单记录
            $ProductList=$this->getDiyPrepaidDetailProductList($UCProductList);
            $up_product_info = serialize ( $ProductList ); // 存储到订单商品快照中的商品信息
            $upd_id = $UPD->addRecord ( $upid, $up_product_info );//生成订单详情ID
            $ucDel  = $UCM->deleteProductByUcidString($ucidString);
        if ($upid && $upd_id && $ucDel) {
            $this->commit (); // 提交事务
            $result['result']=$up_orderid;
            $result['orderUpid']=$upid;
        } else {
            $this->rollback (); // 事务回滚
            $result=0;
        }
        return $result;
    }


    //根据$pidArr（pid数组）重新生成PrepaidDetail的productlist
    public function getDiyPrepaidDetailProductList($ProductList){
        foreach ( $ProductList as $k => $Product ) {
            $ProductList [$k] ['p_count'] = $Product['uc_count'];   // 数量同步
            $TotalPrice += $Product [$this->DBF->Product->Price] * $Product['uc_count']; // 价格小计
            if ($Product ['p_producttype'] == 4) { // 如果是DIY件，需保存DIY的快照参数(增加tdf_user_diy中的diy_unit_info和cover、price字段)
                $udinfo = M ( 'user_diy' )->where ( 'id=' . $ProductList [$k] ['p_diy_id'] )->find ();
                $ProductList [$k] ['diy_unit_info'] = $udinfo ['diy_unit_info'];
                $ProductList [$k] ['cover'] = $udinfo ['cover'];
                $ProductList [$k] ['price'] = $udinfo ['price'];
            }
        }
        return $ProductList;
        /*// 处理捆绑商品
        foreach ( $ProductList as $key => $Product ) {
            if (key_exists ( 'binditems', $Product )) {
                foreach ( $Product ['binditems'] as $k => $BindProduct ) {
                    if ($BindProduct ['p_producttype'] == 4) {
                        // DIY
                        $TotalPrice += $BindProduct [$this->DBF->Product->Price];
                    } elseif ($BindProduct ['p_producttype'] == $producttypeArr ['NDIY']) {
                        // 非DIY类商品
                        $CIF = FactoryCartItemModel::init ( $BindProduct );
                        $cifArgs = $CIF->getArgs ();
                        $cifArgs ['uid'] = $Product ['u_id'];
                        $CIF->setArgs ( $cifArgs );
                        $TotalPrice += $CIF->amount ();
                    }
                }
            }
        }*/
    }

    /**单产品生成订单
     * @param $UID
     * @param $Amount
     * @param $IP
     * @param $Status
     * @param $Orderid
     * @param $up_paytype
     * @param string $up_productid
     * @param $up_type
     * @param int $up_express
     * @return mixed
     */
    public function addRecordAPI($UID, $Amount, $IP, $Status, $Orderid, $up_paytype, $up_productid = "0", $up_type, $up_express = 0) {
        $efee = 0;
        if ($up_express == 1) {
            $efee = C ( 'UP_EFEE' );
        }
        $DBF_UP = $this->DBF->UserPrepaid;
        $data = array (
            $DBF_UP->UserID => $UID,
            $DBF_UP->Amount => $Amount,
            $DBF_UP->DealDate => get_now (),
            $DBF_UP->IPAddress => $IP,
            $DBF_UP->OrderID => $Orderid,
            $DBF_UP->PayType => $up_paytype,
            $DBF_UP->ProductID => $up_productid,
            $DBF_UP->Type => $up_type,
            $DBF_UP->Express => $up_express,
            $DBF_UP->Efee => $efee,
            $DBF_UP->Status => $Status
        );
        return $this->add ( $data );
    }


    public function get_umorderid(){// 产生orderid
        $tempid = time () . $this->generate_rand ( 8 );
        return $tempid;
    }

    public function generate_rand($l) { // 产生随机数$l为多少位
        $c = "0123456789";
        // $c= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        srand ( ( double ) microtime () * 1000000 );
        for($i = 0; $i < $l; $i ++) {
            $rand .= $c [rand () % strlen ( $c )];
        }
        return $rand;
    }


    /**
     * APP获取用户订单list. productCover为产品缩略图。productCount为产品数量
     */
  public function appGetUserOrder($UID){
      $orderList=$this->getPrepaidListByUID($UID);

      foreach($orderList as $key => $value){
          $product[$value['up_id']]=$this->getProductListByUpid($value['up_id']);
          //echo $value['up_id'];
          //var_dump($product);

          //echo "<br>".count($product[$value['up_id']]);
          //echo "<br>".$value['up_id'];
          //var_dump($product[$value['up_id']]);

          $userOrderList[$key]['orderUpid'] = $value['up_id'];
          $userOrderList[$key]['result']    = $value['up_orderid'];
          $userOrderList[$key]['orderDate'] = $value['ctime'];
          $userOrderList[$key]['orderPrice']= $value['up_amount'];
          $userOrderList[$key]['orderState']= $value['up_status'];

          $userOrderList[$key]['productArr']=$product[$value['up_id']];
          //var_dump( $userOrderList[$key]['productArr']);
          /*if(count($product[$value['up_id']])>1){//多个商品
                foreach($product[$value['up_id']] as $pkey => $pvalue){
                    $userOrderList[$key]['productIcon'].=$pvalue['cover_64'].',';
                   // $userOrderList[$key]['productCount']=$orderList[$key]['productCount']+1;
                }
              $userOrderList[$key]['productIcon']=substr($userOrderList[$key]['productIcon'],0,-1);
          }else{//1个商品
              $userOrderList[$key]['productIcon']=$product[$value['up_id']][0]['cover_64']?$product[$value['up_id']][0]['cover_64']:"";
          }*/
          //$userOrderList[$key]['productArr']=$this->getAppOrderProduct($product[$value['up_id']);
      }
       // var_dump($userOrderList);
      //exit;
      return $userOrderList;
  }

    /**
     * APP获取订单详情(根据订单处理单号 up_id)
     */
   public function appGetOrderDetail($up_id){
       $orderDetail=$this->getPrepaidListByUpid($up_id);
       $productList=$this->getProductListByUpid($up_id);
       $detail['orderUpid']=$orderDetail[0]['up_id'];
       $detail['result']=$orderDetail[0]['up_orderid'];
       $detail['orderDate']=$orderDetail[0]['ctime'];
       $detail['orderPrice']=$orderDetail[0]['up_amount'];
       $detail['orderState']=$orderDetail[0]['up_status'];
       $detail['up_addressee']=$orderDetail[0]['up_addressee'];
       $detail['up_address']=$orderDetail[0]['up_address'];
       $detail['up_mobile']=$orderDetail[0]['up_mobile'];
       $detail['product']=$productList;
       return $detail;
   }
    
   /**
    * 将订单数据从表里读出显示到订单页面中需要处理的商品数据作为一个函数来处理
    * 目的是为了压缩CartAction.class.php中pay函数的代码量
    *
    * @author miaomin@2015.10.28
    * @param string $orderid
    * @param int $UID
    * @return mixed
    */
    public function processOrder2payProducts($orderid,$UID){
        $UP = M ();
        
        $sql = "select TUP.up_amount_account,TUP.up_amount_coupon,TUP.up_orderid,TUP.up_paytype,TP.payname,TUP.up_amount,TUP.up_status,TUP.up_dealdate,TUP.up_efee,TUP.up_amount_account,TUP.up_amount_total,TUP.up_order_bz,";
        $sql .= "TUP.up_productid,TUP.up_uid,TUP.up_type,TPD.up_product_info,TUP.up_express,TUP.up_address,TUP.up_addressee,TUP.up_mobile,TUP.up_id,TUP.up_done_status,TUP.up_expressname,TUP.up_expressid ";
        $sql .= "from tdf_user_prepaid as TUP ";
        $sql .= "Left Join tdf_paytype as TP On TP.pt_id=TUP.up_paytype ";
        $sql .= "Left Join tdf_user_prepaid_detail as TPD On TUP.up_id=TPD.up_id ";
        $sql .= "where TUP.up_orderid=" . $orderid . "";
        
        $OD = $UP->query ( $sql );
        $OD = $OD [0];
        
        if ($OD){
            $OD ['up_orderid_en'] = $this->encode_pass ( $OD ['up_orderid'], $UID );
            $temp_product_info = unserialize ( $OD ['up_product_info'] );
            $temp_product_info = array_values ( $temp_product_info );
            // 处理主商品
            foreach ( $temp_product_info as $key => $value ) {
                unset ( $temp_product_info [$key] ['p_intro'] );
                unset ( $temp_product_info [$key] ['p_author'] );
                foreach ( $temp_product_info as $k => $v ) {
                    foreach ( $v as $k1 => $v1 ) {
                        if ($k1 !== 'p_propid_spec' & $k1 !== 'p_belongpid' & $k1 !== 'p_id' & $k1 !== 'p_cover' & $k1 !== 'p_name' & $k1 !== 'p_cate_3' & $k1 !== 'p_producttype' & $k1 !== 'p_price' & $k1 !== 'cartitem' & $k1 !== 'uc_isreal' & $k1 !== 'uc_count' & $k1 !== 'binditems') {
                            unset ( $v [$k1] );
                        } // 消除产品其他数组元素
                    }
                    $temp_product_info [$k] = $v;
                    // 判断后设置链接的模块名
                    if ($v ['p_producttype'] == 5) {
                        $temp_product_info [$k] ['linkedmodel'] = 'product';
                    } else {
                        $temp_product_info [$k] ['linkedmodel'] = 'models';
                    }
                    $temp_product_info [$k] ['propspec'] = ProductPropValModel::parseCombinePropVals ( $temp_product_info [$k] ['p_propid_spec'], '<br>' );
                }
            }
            $OD ['up_product_infoarr'] = $temp_product_info;
            $OD ['up_status_text'] = replace_int_vars ( $OD ['up_status'] );
            if ($OD ['up_type'] == 1 || $OD ['up_type'] == 4) {
                $product = unserialize ( $OD ['up_productid'] );
                $OD ['detail'] = $OD ['up_product_infoarr'];
                $OD ['distype'] = 1;
            } else{
                $OD ['detail'] = "订单操作处理成功";
                $OD ['distype'] = 0;
            }
            $order_do_status = L ( "up_done_status" );
            $OD ['up_done_status_text'] = $order_do_status [$OD ['up_done_status']];
            $PR = M('product');
            foreach($OD['detail'] as $k=>$a){
                $PR->getByP_id($a['p_id']);
                $OD['detail'][$k]['p_diy_id'] = $PR->p_diy_id;
            }
            return $OD;
        }
    }
    
    // ---------------------------------------加密、解密函数--------------------------start----
    // *********by zhangzhibin***************
    // *********20130709 ***************
    public function encode_pass($tex, $key, $type = "encode") {
        $chrArr = array (
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9'
        );
        if ($type == "decode") {
            if (strlen ( $tex ) < 14)
                return false;
            $verity_str = substr ( $tex, 0, 8 );
            $tex = substr ( $tex, 8 );
            if ($verity_str != substr ( md5 ( $tex ), 0, 8 )) {
                // 完整性验证失败
                return false;
            }
        }
        $key_b = $type == "decode" ? substr ( $tex, 0, 6 ) : $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62] . $chrArr [rand () % 62];
        $rand_key = $key_b . $key;
        $rand_key = md5 ( $rand_key );
        $tex = $type == "decode" ? base64_decode ( substr ( $tex, 6 ) ) : $tex;
        $texlen = strlen ( $tex );
        $reslutstr = "";
        for($i = 0; $i < $texlen; $i ++) {
            $reslutstr .= $tex {$i} ^ $rand_key {$i % 32};
        }
        if ($type != "decode") {
            $reslutstr = trim ( $key_b . base64_encode ( $reslutstr ), "==" );
            $reslutstr = substr ( md5 ( $reslutstr ), 0, 8 ) . $reslutstr;
        }
        return $reslutstr;
    }
    // $psa=encode_pass("phpcode","taintainxousad");
    // echo encode_pass($psa,"taintainxousad",'decode');
    // ---------------------------------------加密、解密函数--------------------------end----

    //根据up_id,up_amount更新订单应付金额
    public function updateOrderAmount($up_id,$up_amount){
        $UPM = M ( "user_prepaid" );
        $data['up_amount']=$up_amount;
        $result=$UPM->where ( "up_id='" . $up_id . "'" )->setField ($data);
        return $result;
    }

    //根据up_id转移订单到$up_user @param usertype:1为email、2为手机号、3为用户ID
    public function changeUpCreaterByUserNo($up_id,$up_user,$usertype){
        $UM=new UsersModel();
        if($usertype==1){
            $userInfo=$UM->getUserByEMail($up_user);
            $UID=$userInfo['u_id'];
        }elseif($usertype==2){
            $userInfo=$UM->getUserByMobno($up_user);
            $UID=$userInfo['u_id'];
        }elseif($usertype==3){
            $UID=$up_user;
        }
        $UPM = M ( "user_prepaid" );
       // echo $UID;
       // exit;
        $data['up_uid']=$UID;
        $data['up_source']=1;
        $result=$UPM->where ( "up_id='" . $up_id . "'" )->setField ($data);
        return $result;
    }

    /*
     * 由产品ID获得订单orderid
     */
    public function getPrepaidOidByPid($pid){
        $prepaidInfo=$this->where("p_id='".$pid."'")->find();
        $up_orderid=$prepaidInfo['up_orderid'];
        return $up_orderid;
    }


    /*
    * 由产品ID获得订单orderid
    */
    public function getPrepaidInfoByPid($pid){
        $prepaidInfo=$this->where("p_id='".$pid."'")->find();
        return $prepaidInfo;
    }

    /*
    * 由订单orderid获取产品信息
    */
    public function getPrepaidInfoByPidonline($orderid){
        $prepaidInfo=$this->where("up_orderid='".$orderid."'")->find();
        return $prepaidInfo;
    }

    /**
     * 第三方接口 由pid生成订单
     */
    public function addPrepaidByProduct($productInfo,$uid,$pcount=1){

        $up_orderid = $this->get_umorderid();
        $IP="1.1.1.1";
        $Amount         =$productInfo['p_price'];
        $pid_array[0]   =$productInfo['p_id'];
        $up_type        =4; //diy订单
        $up_express     =1;
        $UPD = new UserPrepaidDetailModel ();
        $UPM = new UserPrepaidModel();
        $UCM = new UserCartModel();
        $this->startTrans();
        $upid = $this->addRecord ( $uid, $Amount, $IP, 0, $up_orderid, 0, serialize ( $pid_array ), $up_type, $up_express,$ua_id);//生成订单记录
        $ProductList[0]=$productInfo;
        $up_product_info = serialize ( $ProductList ); // 存储到订单商品快照中的商品信息
        $upd_id=$UPD->addRecord ( $upid, $up_product_info );//生成订单详情ID
        $UCM->deleteProduct($productInfo['p_id'],$uid);
        if ($upid && $upd_id) {
            $this->commit (); // 提交事务
            $pidUpdate=$UPM->where("up_id=".$upid."")->setField('p_id',$productInfo['p_id']);
           // $result['orderid']  =$up_orderid;
            $result['upid']     =$upid;
        } else {
            $this->rollback (); // 事务回滚
            $result=0;
        }
        return $result;
    }

//根据productKey获取手机微信的订单支付url
    public function getWxPayUrl($productKey){
        $TPM=new ProductModel();
        $p_id=$TPM->getPidByPkey($productKey);
        $UPM=new UserPrepaidModel();
        $up_orderid=$UPM->getPrepaidOidByPid($p_id);
        $up_orderid_en=$UPM->encode_pass ( $up_orderid,'1',"encode" );
        $result=WEBROOT_URL."/user.php/wxuser/orderdetail/ordertype/1/orderid/".$up_orderid_en;
        return $result;
    }

//根据orderid获取手机微信的订单支付url
    public function getWxPayUrlByVR($up_orderid){
        $UPM=new UserPrepaidModel();
/*        $orderInfo  = $UPM->getPrepaidInfoByOrderid($up_orderid);*/
        $up_orderid_en=$UPM->encode_pass ( $up_orderid,'1',"encode" );
        $result=WEBROOT_URL."/user.php/wxuser/orderdetail/ordertype/1/orderid/".$up_orderid_en;
        return $result;
    }

    public function getPrepaidInfoByOrderid($orderid) {
        $DBF_UP = $this->DBF->UserPrepaid;
        return $this->where ( $DBF_UP->OrderID . "='" . $orderid . "'" )->select ();
    }


    /**第三方通过api增加订单
     * @param $datas
     */
    public function addOrderAPI($datas){
        $uid                    = $datas['uid'];
        $thirdOrderid           = $datas['thirdOrderid'];
        $userPrepaidInfo        = $this->getPrepaidListByThirdOrderid($uid,$thirdOrderid);
        $sDatas['up_amount']    = $datas['up_amount'];
        $sDatas['up_productid'] = serialize($datas['up_productid']);
        $sDatas['up_type']      = 4;
        $sDatas['up_addressee'] = $datas['addressee'];
        $sDatas['up_address']   = $datas['address'];
        $sDatas['up_mobile']    = $datas['mobile'];
        $sDatas['up_status']    = 1;    //已支付
        $sDatas['up_dealdate']  = get_now ();
        $sDatas['up_uid']       = $uid;
        $sDatas['third_orderid']= $thirdOrderid;
        $result=0;
        $UPD    = new UserPrepaidDetailModel();
        if($userPrepaidInfo){//已经存在订单,更新
            $step1  = $this->where("third_orderid='".$thirdOrderid."' and up_uid=".$uid."")->save($sDatas);
            $step2  = $UPD->updatePrepaidDetailByUpid($userPrepaidInfo[0]['up_id'],$datas['product']);
            if($step1){
                $result     = $userPrepaidInfo[0]['up_id'];
            }
        }else{//未存在订单,新增
            $sDatas['up_orderid']  = "9".substr($this->get_umorderid(),0,-1);
            $this->startTrans();
            $step1  = $this->add($sDatas);
            $step2  = $UPD->addRecord($step1,serialize($datas['product']));
            if($step1 && $step2){
                $result   = $step1;
                $this->commit();
            }else{
                $result=0;
                $this->rollback();
            }
        }
        return $result;
    }

    /**
     * 查询订单状态接口
     * zhangzhibin
     */
    public function productOrderStatus($postInfo){
        $uid            = $postInfo['uid'];
        $thirdOrderid   = $postInfo['thirdOrderid'];
        $orderInfo      = $this->getPrepaidListByThirdOrderid($uid,$thirdOrderid);
        $UPPM           = new UserPrepaidProcessModel();
        $TUPE           = new UserPrepaidExpressModel();
        if(!$orderInfo){//如果没有订单信息
            $result['status']=-1;
        }else{
            $up_done_status_arr = L ( "process" );
            $prepaidProcess = $UPPM->getCurrentProcessByUpid($orderInfo[0]['up_id']);
            if($prepaidProcess >= 6){
                $result['status']   = 6;
                $result['express']  = $TUPE->getExpressNumberByUpid($orderInfo[0]['up_id']);
                $result['msg']      = $up_done_status_arr[6];
            }else{
                $result['status']   = 0;
                $result['express']  = '0';
                $result['msg']      = '订单还未发货!';
            }
        }
        return $result;
    }
    /**
     * 查询订单状态接口
     * zhangzhibin
     */
    public function orderExpressStatus($postInfo){
        $uid            = $postInfo['uid'];
        $thirdOrderid   = $postInfo['thirdOrderid'];
        $orderInfo      = $this->getPrepaidListByThirdOrderid($uid,$thirdOrderid);
        $UPPM           = new UserPrepaidProcessModel();
        if(!$orderInfo){//如果没有订单信息
            $result['status']=-1;
        }else{
            $up_done_status_arr = L ( "process" );
            var_dump($up_done_status_arr);
            $prepaidProcess = $UPPM->getCurrentProcessByUpid($orderInfo[0]['up_id']);
            if($prepaidProcess){
                $result['status']   = 0;
                $result['msg']      = '订单还未处理!';
            }else{
                $result['status']   = $prepaidProcess;
                $result['msg']      = $up_done_status_arr[$prepaidProcess];
            }
        }
        return $result;
    }


    /**
     * VR系统购物车生成订单
     * zhangzhibin
     */
    public function createPrepaidByVR($pidArr){
        $UID=6859; //默认的VR_buyer用户ID
        foreach($pidArr as $key => $value){
            $productInfoArr     = explode(":",$value);
            $pidString.= $productInfoArr[0].",";
        }
        $pidString= substr($pidString,0,-1);
        $TPM=new ProductModel();
        $productInfo=M('product')->query('select p_id,p_price,p_cover from tdf_product where p_id in ('.$pidString.')');
        foreach($productInfo as $key => $value){
            foreach($pidArr as $k => $v){
                $productInfoArr     = explode(":",$v);
                if($productInfoArr[0]==$value['p_id']){
                    $productInfo[$key]['count']     = $productInfoArr[1];
                }
            }
        }
        //构造pid_array（"pid,count"的数组）
        foreach($productInfo as $key =>$value){
            $pid_array[]    =$value['p_id'].",".$value['count'];
            $price          =$value['count'] * $value['p_price'];
            $Amount+=$price;
        }
        //--------------------保存订单数据 2015.07.13
        $up_orderid = $this->get_umorderid();
        $IP="1.1.1.1";
        $up_type=4; //diy订单
        $up_express=1;
        $UPD = new UserPrepaidDetailModel ();
        $this->startTrans();
        $upid = $this->addRecordByVR( $UID, $Amount, $IP, 0, $up_orderid, 0, serialize ( $pid_array ), $up_type, $up_express);//生成订单记录
        $ProductList        = $productInfo;
        $up_product_info    = serialize ( $ProductList ); // 存储到订单商品快照中的商品信息
        $upd_id = $UPD->addRecord ( $upid, $up_product_info );//生成订单详情ID
        //$ucDel  = $UCM->deleteProductByUcidString($ucidString); //删除购物车表中数据
        if ($upid && $upd_id) {
            $this->commit (); // 提交事务
            $result['result']=$up_orderid;
            $result['orderUpid']=$upid;
        } else {
            $this->rollback (); // 事务回滚
            $result=0;
        }
        return $result;




    }


}