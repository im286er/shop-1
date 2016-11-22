<?php
class BillmanageAction extends CommonAction{
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        
        $BM = M('billmanage');
        $sql = "select t1.*,t2.up_orderbacktime,t2.up_orderid,t2.up_amount_coupon from tdf_billmanage t1 left join tdf_user_prepaid t2 on t1.upid = t2.up_id where t1.bm_status = 1";
        $arr = $BM->query($sql);
        //dump($arr);
        $this->assign('list',$arr);
        $this->display();
    }
    public function edit($id){
        $BM = M('billmanage');
        $sql = "select t1.*,t2.up_orderbacktime,t2.up_orderid,t2.up_amount_coupon from tdf_billmanage t1 left join tdf_user_prepaid t2 on t1.upid = t2.up_id where bm_id='{$id}'";
        $arr = $BM->query($sql);
        $this->assign('list',$arr[0]);
        //dump($arr);
        $this->display();
    }
    public function indexcommon(){
        $BM = M('billmanage');
        $sql = "select t1.*,t2.up_orderbacktime,t2.up_orderid,t2.up_amount_coupon from tdf_billmanage t1 left join tdf_user_prepaid t2 on t1.upid = t2.up_id where t1.bm_status = 1";
        $arr = $BM->query($sql);
        //dump($arr);
        $this->assign('list',$arr);
        $this->display();
    }
    public function savelogs(){
        //保存更改
        $BM = M('billmanage');
        
    }
    public function getdetail($aup_id){
        function getProductArr($up_id) { // 根据订单的upid返回订单产品数组
            $PPD = new UserPrepaidDetailModel ();
            $ProductInfo = $PPD->getPrepaidDetailByUpid ( $up_id );
            $ProductArr = unserialize ( $ProductInfo ['up_product_info'] );
            return $ProductArr;
        }
        
        //$upid = I ( 'upid', 0, 'intval' );
        $upid = $aup_id;
        $actiontype=I('actiontype',0,'intval');//获得操作类型
        $ProductArr = getProductArr ( $upid );
        // ----------------------------------打印材料数组 ^
        
        $sql = "select TPM.pma_id,TPM.pma_name as TPM_name,TPMP.pma_name as TPMP_name,TPM.pma_unitprice,TPM.pma_density,TPM.pma_startprice,TPM.pma_diy_formula_s,TPM.pma_diy_formula_b from tdf_printer_material as TPM ";
        $sql .= "Left Join tdf_printer_material as TPMP ON TPMP.pma_id=TPM.pma_parentid ";
        $sql .= "where TPM.pma_type=1 order by TPM.pma_weight ASC ";
        $mcate = M ( "printer_material" )->query ( $sql ); // 打印材料数组，必须
        // ----------------------------------打印材料数组V
        $DNM=new DiyNecklaceModel();
        foreach ( $mcate as $keyM => $valueM ){
            $materialArr [$valueM ['pma_id']] = $valueM ['TPM_name'];
        }//材料数组
        foreach ( $ProductArr as $k => $v ) {
            //var_dump($v);
            $kk=1;
            $productLog .= "\r\n\r\n商品" . $kk . "\r\n";
            $ProductLogArr [$k] ['product_count'] 	= $v ['p_count'] ;//数量
            $ProductLogArr [$k] ['product_price'] 	= $v ['p_price'] ;//单价
            $ProductLogArr [$k] ['product_cover'] 	= $v ['p_cover'] ;//图片
            $ProductLogArr [$k] ['uc_producttype_name'] =  show_product_type ( $v['uc_producttype'] );//商品类型
            $ProductLogArr [$k] ['product_cover64'] = str_replace ( '/o/', '/s/64_64_', $v['p_cover'] );//64图片
            $ProductLogArr [$k] ['uc_producttype'] 	= $v ['uc_producttype'] ;//图片
            	
            	
            if($v['uc_producttype']==4){ //如果是DIY的商品
                $udinfo = unserialize ( $v ['diy_unit_info'] );
                $DC = M ( 'diy_cate' )->where ( "cid=" . $v ['p_cate_4'] )->find (); // diy产品类型
                $DU = M ( 'diy_unit' )->where ( 'cid=' . $v ['p_cate_4'] . ' and ishidden=0' )->order ( 'fieldgroup,sort' )->select (); // 选择tdf_diy_unit
                $ProductLogArr [$k] ['product_name']	= $DC ['cate_name'];
                $ProductLogArr [$k] ['商品类型'] = $DC ['cate_name'];
                $productLog .= "DIY ID:" . $DC ['cid'] . "\r\n";
                $productLog .= "商品名称:" . $DC ['cate_name'] . "\r\n";
                $productLog .= "商品数量:" . $v ['p_count'] . "\r\n";
                $product_type="";
                foreach ( $DU as $keyN => $valueN ) {
                    if($valueN ['unit_name'] == "Textvalue"){//输入的主体字符
                        $ProductLogArr [$k] [$valueN ['unit_showname']] = $udinfo [$valueN ['id']];
                        $productLog .= $valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']] . "\r\n";
                        $product_type.=$valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']]."; " ;
                    }elseif($valueN ['unit_name'] == "Material") {
                        $ProductLogArr [$k] [$valueN ['unit_showname']] = $materialArr [$udinfo [$valueN ['id']]];
                        $ProductLogArr [$k] ['product_m'] = $materialArr [$udinfo [$valueN ['id']]];
                        $productLog .= "材料:" . $materialArr [$udinfo [$valueN ['id']]] . "\r\n";
                        //$product_type.=$valueN ['unit_showname'].":".$materialArr [$udinfo [$valueN ['id']]]."; "; //属性加材质
                    }elseif($valueN ['unit_name'] == "Chaintype"){
                        $ProductLogArr [$k] [$valueN ['unit_showname']] = $DNM->getNecklaceExplainByID($udinfo [$valueN ['id']]);
                        $productLog .= $valueN ['unit_showname'] . ":" .$DNM->getNecklaceExplainByID($udinfo [$valueN ['id']]) . "\r\n";
                        $product_type.= $valueN ['unit_showname'] . ":" .$DNM->getNecklaceExplainByID($udinfo [$valueN ['id']])."; "; //属性加材质
                    }elseif($valueN ['unit_name'] == "Gendertype"){
                        $ProductLogArr [$k] [$valueN ['unit_showname']] = $DNM->getSelectValue($udinfo [$valueN ['id']],$valueN ['id']);
                        $productLog .= $valueN ['unit_showname'] . ":" .$DNM->getSelectValue($udinfo [$valueN ['id']],$valueN ['id']) . "\r\n";
                        $product_type.= $valueN ['unit_showname'] . ":" .$DNM->getSelectValue($udinfo [$valueN ['id']],$valueN ['id'])."; ";
                    }else{
                        $ProductLogArr [$k] [$valueN ['unit_showname']] = $udinfo [$valueN ['id']];
                        $productLog .= $valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']] . "\r\n";
                        $product_type .= $valueN ['unit_showname'] . ":" . $udinfo [$valueN ['id']] . "; ";
                    }
                }
        
                $ProductLogArr[$k]['product_type'] = $product_type;
                //<--lifangyuan,为了迎合需求，强制转换字符串
                //$ProductLogArr [$k] ['product_mname'] = "";
                //------------------------------------------------------>
                
        
            }elseif($v['uc_producttype']==5){//如果是垂直类商品
                $productLog .= "商品ID:" . $v ['p_belongpid'] . "\r\n";
                $productLog .= "商品名称:" . $v ['p_name'] . "\r\n";
                $productLog .= "商品数量:" . $v ['p_count'] . "\r\n";
                $productLog .= "商品属性:" . ProductPropValModel::parseCombinePropVals ( $v ['p_propid_spec'], ' -- '). "\r\n";
                $ProductLogArr [$k]['product_name']	= $v ['p_name'];
                $ProductLogArr [$k]['product_type']= ProductPropValModel::parseCombinePropVals ( $v ['p_propid_spec'], ' -- ');
                
                //<--lifangyuan,为了迎合需求，强制转换字符串
                preg_match('/材质:[\S]+/',$ProductLogArr [$k]['product_type'],$ls);
                $stam = explode(":", $ls[0]);
                $ProductLogArr [$k] ['product_m'] = $stam[1];
                //------------------------------------------------------>
                $slam1 = $ProductLogArr [$k]['product_type'];
                $ProductLogArr [$k] ['product_mname'] = preg_replace('/材质:[\S]+\s--\s/',"", $slam1);
                
            }
            $kk = $k + 1; //商品数量加1
        }
        
        var_dump($ProductLogArr);
        return $ProductLogArr;
        $this->display();
        
    }
    /*
     * 
     */ 
    public function addproduct($orderid){
        $UP = M('user_prepaid');
        $ar = $UP->field('up_id')->getByUp_orderid($orderid);
        $arr = $this->getdetail($ar['up_id']);
        //var_dump($arr);
        //var_dump($arr);

        //增加记录managebill
        $BM = M('billmanage');
        foreach($arr as $product){
            $data['bm_material'] = $product['product_m'];//材质
            $data['bm_count'] = $product['product_count'];//数量
            $data['bm_price'] = $product['product_price'];//价格
            $data['bm_cover'] = $product['product_cover'];//图片
            $data['bm_type'] = $product['uc_producttype'];//种类
            $data['bm_typename'] = $product['uc_producttype_name'];//种类名称
            $data['bm_name'] = $product['product_name'];//产品名称
            $data['bm_fname'] = $product['product_type'];//产品全名
            $data['bm_mname'] = $product['product_mname'];//万恶的需求强制名称
            $data['upid'] = $ar['up_id'];//订单ID
            $data['bm_status'] = 1;//操作状态
            
            $data['bm_rfile'];//模型
            $data['bm_rcapacity'];//体积
            $data['bm_chain'];//链条属性
            $BM->add($data);
            echo $BM->getLastSql();
        }
       
    } 
    public function test(){
        echo "test";
    }  
    
}