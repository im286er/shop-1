<?php

/**
 * 微信前端分组
 *
 * @author miaomin
 * Jan 30, 2015 11:27:49 AM
 *
 * $Id$
 */
class WxAction extends CommonAction
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->DBF = new DBF ();
        // miaomin edited@2016.8.1 新增登录判断直接登录
        // 判断登录
        if ((!$this->_isLogin()) && (is_weixin())) {
            $this->_needLogin();
        }
    }

    /**
     * 微信未登录
     */
    public function unlogin()
    {
        $this->display();
    }

    /**
     * 首页
     */
    public function indexold()
    {
        $this->_renderPage();
    }

    /**
     * 新版首页
     */
    public function index()
    {
        $this->_renderPage();
    }

    /*
     * 产品列表
     */
    public function jewelrylist()
    {
        $curMenu = I('curMenu');
        $this->assign('curMenu', $curMenu);
        // 商品目录
        $CatePicker = new CategoryPickerModel ();
        $cateList = $CatePicker->getChildList('1263');

        // 商品
        // @load ( '@.Paging' );
        @load('@.SearchParser');
        $SP = new SearchParser ();
        $SP->parseUrlInfo(true);
        $SearchInfo = $SP->SearchInfo;
        $SearchInfo ['page'] = $this->_get('page');
        $SearchInfo ['count'] = 10;
        $SearchInfo ['order'] = 'dispweight_asc';
        // $SearchInfo ['producttype'] = 7;
        if ($this->_get('cate')) {
            $SearchInfo ['cateory'] = '1263';
        } else {
            $SearchInfo ['cateory'] = $this->_get('cate');
        }
        // 获取分类信息
        $CM = new CategoryPickerModel ();
        $cateInfo = $CM->getCategoryByID($this->_get('cate'));
        $this->assign('Cateinfo', $cateInfo);

        $PSM = new ProductSearchModel ($SearchInfo, 'category', true);
        $PSM->DisplayFields = 'tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_price,tdf_product.p_dispweight,tdf_product.p_createdate,tdf_product.p_lastupdate,tdf_product.p_views_disp,tdf_product.p_zans,tdf_product.p_maintype,tdf_product.p_slabel,tdf_product.p_producttype,tdf_product.p_diy_cate_cid,tdf_product.p_wpid';
        $this->assign('DBF_P', $this->DBF->Product);
        $this->assign('SearchResult', $PSM->getResult($SP->SearchInfo ['page']));
        $this->assign('SearchResultCount', $PSM->TotalCount);
        $this->assign('CateList', $cateList);
        $this->assign('Paging', getPagingInfo2($PSM->TotalCount, $this->_get('page'), 10, 4, __SELF__, 'active'));
        $this->assign('wxtitle', "IGNITE伊格纳 创意首饰");
        $this->_renderPage();
    }


    /*
	 * 产品列表
	 */
    public function jewelrylisttest()
    {
        $curMenu = I('curMenu');
        $this->assign('curMenu', $curMenu);
        // 商品目录
        $CatePicker = new CategoryPickerModel ();
        $cateList = $CatePicker->getChildList('1263');

        // 商品
        // @load ( '@.Paging' );
        @load('@.SearchParser');
        $SP = new SearchParser ();
        $SP->parseUrlInfo(true);
        $SearchInfo = $SP->SearchInfo;
        $SearchInfo ['page'] = $this->_get('page');
        $SearchInfo ['count'] = 10;
        $SearchInfo ['order'] = 'dispweight_asc';
        // $SearchInfo ['producttype'] = 7;
        if ($this->_get('cate')) {
            $SearchInfo ['cateory'] = '1263';
        } else {
            $SearchInfo ['cateory'] = $this->_get('cate');
        }
        // 获取分类信息
        $CM = new CategoryPickerModel ();
        $cateInfo = $CM->getCategoryByID($this->_get('cate'));
        $this->assign('Cateinfo', $cateInfo);

        $PSM = new ProductSearchModel ($SearchInfo, 'category', true);
        $PSM->DisplayFields = 'tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_price,tdf_product.p_dispweight,tdf_product.p_createdate,tdf_product.p_lastupdate,tdf_product.p_views_disp,tdf_product.p_zans,tdf_product.p_maintype,tdf_product.p_slabel,tdf_product.p_producttype,tdf_product.p_diy_cate_cid,tdf_product.p_wpid';
        $this->assign('DBF_P', $this->DBF->Product);
        $this->assign('SearchResult', $PSM->getResult($SP->SearchInfo ['page']));
        $this->assign('SearchResultCount', $PSM->TotalCount);
        $this->assign('CateList', $cateList);
        $this->assign('Paging', getPagingInfo2($PSM->TotalCount, $this->_get('page'), 10, 4, __SELF__, 'active'));
        $this->assign('wxtitle', "3DCity 创意首饰");
        $this->_renderPage();
    }

    /*
	 * 产品列表ajax请求
	 */
    public function jewelrylistajax()
    {
        // 商品目录
        $CatePicker = new CategoryPickerModel ();
        $cateList = $CatePicker->getChildList('1263');
        $page = $this->_get('page');
        // 商品
        // @load ( '@.Paging' );
        @load('@.SearchParser');
        $SP = new SearchParser ();
        $SP->parseUrlInfo(true);
        $SearchInfo = $SP->SearchInfo;
        $SearchInfo ['page'] = $page;
        $SearchInfo ['count'] = 10;
        $SearchInfo ['order'] = 'dispweight_asc';
        // $SearchInfo ['producttype'] = 7;
        if ($this->_get('cate')) {
            $SearchInfo ['cateory'] = '1263';
        } else {
            $SearchInfo ['cateory'] = $this->_get('cate');
        }
        // 获取分类信息
        $CM = new CategoryPickerModel ();
        $cateInfo = $CM->getCategoryByID($this->_get('cate'));
        $this->assign('Cateinfo', $cateInfo);

        $PSM = new ProductSearchModel ($SearchInfo, 'category', true);
        $PSM->DisplayFields = 'tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_price,tdf_product.p_dispweight,tdf_product.p_createdate,tdf_product.p_lastupdate,tdf_product.p_views_disp,tdf_product.p_zans,tdf_product.p_maintype,tdf_product.p_slabel,tdf_product.p_producttype,tdf_product.p_diy_cate_cid,tdf_product.p_wpid';
        $result = $PSM->getResult($SP->SearchInfo ['page']);
        //var_dump($result);

        if ($result) {
            echo json_encode($result);
        } else {
            echo false;
        }
        // $result['page']=$SearchInfo ['page'];
        //$result['cate']=$SearchInfo ['cateory'];

        /*$this->assign ( 'DBF_P', $this->DBF->Product );
          $this->assign ( 'SearchResult', $PSM->getResult ( $SP->SearchInfo ['page'] ) );
          $this->assign ( 'SearchResultCount', $PSM->TotalCount );
          $this->assign ( 'CateList', $cateList );
          $this->assign ( 'Paging', getPagingInfo2 ( $PSM->TotalCount, $this->_get ( 'page' ), 10, 4, __SELF__, 'active' ) );
          $this->assign ( 'wxtitle', "3DCity 创意首饰" );
          $this->_renderPage ();*/
    }

    /**
     * 垂直类商品详情
     */
    public function productdetail()
    {
        try {

            if (is_weixin() && !$this->_isLogin()) {
                // 微信登录
                $launchUrl = WX_CALLBACK_DOMAIN . '/index/auth-wx_launch?jump_uri=' . __SELF__;
                redirect($launchUrl);
            }

            //-----------------------根据code记录页面访问日志  start-----
            $data['code'] = I('code');
            if ($data['code']) {
                $data['ip'] = get_client_ip();
                $data['url'] = __SELF__;
                $data['sessionid'] = session_id();
                M('log_active')->add($data);
                $this->assign('code', $data['code']);
            }
            //-----------------------根据code记录页面访问日志  end-------

            $this->header = "jewelry";
            // PID
            $pid = I('get.id');

            // 商品信息
            $PM = new ProductModel ();
            $pmRes = $PM->getNoneDiyProductInfoByID($pid);
            //var_dump($pmRes);
            $pmRes ['p_gprice'] = $pmRes ['p_price'];
            // 商品图片
            $PPM = new ProductPhotoModel ();
            $ppmRes = $PPM->getPhotosByPID($pid);

            // 属性选择器
            $selectorJSON = $this->_getProductPropSelector($pid);
            // 商品主属性
            $PMPM = new ProductMainPropModel ();
            $pmpmRes = $PMPM->getPropByMainType($pmRes [$PM->F->MainType]);
            // 商品属性值
            $PPVM = new ProductPropValModel ();
            $condition = array(
                $PPVM->F->MAINTYPE => $pmRes [$PM->F->MainType]
            );
            $ppvmRes = $PPVM->where($condition)->field($PPVM->F->ID . ',' . $PPVM->F->PROPVAL)->select();
            $ppvmRes = trans_pk_to_key($ppvmRes, $PPVM->F->ID);
            foreach ($ppvmRes as $key => $val) {
                $ppvmRes [$key] = $val [$PPVM->F->PROPVAL];
            }

            // 关联商品
            if ($pmRes [$PM->F->Relation]) {

                $relationLowPrice = 9999999;

                $relationPmRes = $PM->getRelationProduct($pid);

                foreach ($relationPmRes as $key => $val) {
                    if ($val ['p_price'] <= $relationLowPrice) {
                        $relationLowPrice = $val ['p_price'];
                    }
                }

                $this->assign('RelationProductList', $relationPmRes);

                $pmRes ['p_gprice'] = $pmRes ['p_price'] + $relationLowPrice;
            }

            $curMenu = I('curMenu');
            $this->assign('curMenu', $curMenu);

            // 赋值
            $this->assign('Product', $pmRes);
            $this->assign('ProductPhotoList', $ppmRes);
            $this->assign('Selector', $selectorJSON);
            $this->assign('PropList', json_encode($pmpmRes));
            $this->assign('PropValList', json_encode($ppvmRes));

            $this->_renderPage();
        } catch (Exception $e) {
            // echo $e->getMessage ();
        }

    }

    /**
     * 获取属性选择器
     *
     * @param int $pid
     * @return string
     */
    private function _getProductPropSelector($Pid)
    {

        // Init
        $PM = new ProductModel ();
        $PMPM = new ProductMainPropModel ();
        $Product = $PM->getNoneDiyProductInfoByID($Pid);
        $listAvDetail = $PM->getBelongAvProductList($Pid);
        $listProp = $PMPM->getPropByMainType($Product [$PM->F->MainType]);

        // 根据属性权限重排
        foreach ($listAvDetail as $key => $val) {

            $newPropIdSpec = '';

            $specArr = explode('#', $val [$PM->F->PropIdSpec]);
            $tempArr = array();
            foreach ($specArr as $k => $v) {
                $specDetailArr = explode(':', $v);
                $tempArr [$specDetailArr [0]] = $specDetailArr [1];
            }

            foreach ($listProp as $k => $v) {
                $newPropIdSpec .= $v [$PMPM->F->ID] . ':' . $tempArr [$v [$PMPM->F->ID]] . '#';
            }

            if (substr($newPropIdSpec, -1) == '#') {
                $newPropIdSpec = substr($newPropIdSpec, 0, -1);
            }

            $listAvDetail [$key] [$PM->F->PropIdSpec] = $newPropIdSpec;
        }

        // 获取商品明细
        $detailPropArr = array();
        foreach ($listAvDetail as $key => $val) {
            $specArr = explode('#', $val [$PM->F->PropIdSpec]);
            $tempArr = array();
            foreach ($specArr as $k => $v) {
                $specDetailArr = explode(':', $v);
                $tempArr [$specDetailArr [0]] = $specDetailArr [1];
            }
            $detailPropArr [$val [$PM->F->ID]] ['spec'] = $tempArr;
        }

        // print_r ( $detailPropArr );

        // 属性选择器
        $res = $this->_recurGenPropArr($detailPropArr);

        return json_encode($res);
    }

    /**
     * 构成属性选择器数组
     *
     * @param array $detailPropArr
     */
    private function _recurGenPropArr($detailPropArr)
    {
        $res = array();

        $PM = new ProductModel ();

        foreach ($detailPropArr as $key => $val) {
            $testArr = $val ['spec'];

            $condition = array(
                $PM->F->ID => $key
            );
            $pmRes = $PM->where($condition)->field($PM->F->ID . ',' . $PM->F->Price)->find();

            $evalStr = "\$res";
            foreach ($testArr as $key => $val) {
                $evalStr .= "[" . $val . "]";
            }
            $evalStr .= " = \$pmRes;";

            eval ($evalStr);
        }

        return $res;
    }

    /*
     * DIY编辑器
     */
    public function jewelrydetail()
    {
        $curMenu = I('curMenu', 0, 'intval');
        $pid = I('pid', 0, 'intval');
        $showtype = I('showtype', 0, 'intval');
        $TPM = new ProductModel();
        if ($pid) {
            $udinfo = M("user_diy")->where("id=" . $pid)->find();
            $cid = $udinfo ['cid'];
        } else {
            $cid = I('cid', 0, 'intval');
            $productInfo = $TPM->getProductByDiyCateCid($cid);
            //no webgl
            $this->assign("cpid", $productInfo['p_id']);
        }

        // 产品缩略图
        $cinfo = M('diy_cate')->where("cid=" . $cid)->find();
        $iconArr = explode(',', $cinfo ['cate_icon']);
        foreach ($iconArr as $key => $value) {
            $cinfo ['icon'] [$key] = getimgbyID($value);
        }

        // 作者信息(头像、签名)
        $Users = new UsersModel ();
        $Users->find($cinfo ['u_id']);
        $UP = $Users->getUserProfile();
        $UA = $Users->getUserAcc();
        $this->assign('userBasic_au', $Users->data());
        $this->assign('userProf', $UP->data());

        // 推荐款式产品
        $Prodlist = $this->getProd(5, $cid);
        $this->assign('DBF_P', $this->DBF->Product);
        $this->assign('Prodlist', $Prodlist);
        // 推荐款式产品

        $this->assign('showtype', $showtype);
        $this->assign('pid', $pid);
        $this->assign('cid', $cid);
        // var_dump($cinfo);
        $this->assign('cinfo', $cinfo);

        $this->assign('wxtitle', "3DCity 创意首饰");
        $this->assign('curMenu', $curMenu);
        $this->_renderPage();
    }

    // 得到产品列表
    public function getProd($count, $cid)
    {
        @load('@.SearchParser');
        $SP = new SearchParser ();
        $SP->parseUrlInfo(true);
        $SearchInfo = $SP->SearchInfo;
        $SearchInfo ['page'] = $this->_get('page');
        $SearchInfo ['count'] = $count;
        $SearchInfo ['cateory'] = '1263';
        $PSM = new ProductSearchModel ($SearchInfo, 'model', true);
        $SearchResult = $PSM->getResult($SP->SearchInfo ['page']);
        $isun = 0;
        foreach ($SearchResult as $key => $value) {
            if ($value ['p_diy_cate_cid'] == $cid) {
                unset ($SearchResult [$key]);
                $isun = 1;
            }
        }
        if ($isun !== 1) { // 如果没有unset数组，就去除最后一个数组元素
            array_pop($SearchResult);
        }
        return $SearchResult;
    }

    // DIY方案查看
    public function diysnap()
    {
        R('Front/Diy/jewelryeditall');
        //$this->display ();
    }

    public function getUdinfoById($id)
    { // 根据id值获得user_diy中的数据

        $udinfo = M('user_diy')->where('id=' . $id)->find();
        return $udinfo;
    }

    function getUnitDefaultArr($default_value)
    { // 输出链子默认数组
        $sizeArr = explode(";", $default_value); // 链子默认数组(材质ID=>链子ID)
        foreach ($sizeArr as $rkey => $rvalue) {
            $tempsize = explode(":", $rvalue);
            $size [$tempsize [0]] = intval($tempsize [1]);
        }
        return $size;
    }

    // 微信支付成功后的订单结果页面:不登陆即可查看
    public function sendbillresult()
    {
        $ordertype = $_GET ['otype'];
        $mmode = $_GET ['mmode'];
        if ($ordertype) {
            $out_trade_no = pub_encode_pass($_GET ['out_trade_no'], $_SESSION ['f_userid'], 'decode'); // 解密orderid
        } else {
            $out_trade_no = $_GET ['out_trade_no'];
        }
        // $en_out_trade_no = $this->encode_pass ( $out_trade_no, $_SESSION
        // ['f_userid'], 'encode' );
        // echo $out_trade_no;
        $CM = M();
        $sql = "select tu.u_dispname,tup.up_amount,tup.up_status,tup.up_dealdate,tup.up_type,TPT.payname,tu.u_email,tup.up_efee,tup.up_id ";
        $sql .= "from tdf_user_prepaid as tup left join tdf_users as tu On tu.u_id=tup.up_uid ";
        $sql .= "left join tdf_paytype as TPT On TPT.pt_id=tup.up_paytype ";
        $sql .= "where tup.up_orderid='" . $out_trade_no . "'";
        $order_array = $CM->query($sql);
        if ($order_array [0] ['up_type'] == 1 || $order_array [0] ['up_type'] == 4) {
            // $showinfo="<p>订单号:".$out_trade_no."</p>";
            if ($order_array [0] ['up_status'] == 1) {
                if ($mmode == 1) {
                    $buyurl = " <a href=__DOC__/user.php/wxuser/orderdetail/orderid/" . $out_trade_no . "><font color=blue>查看</font></a>";
                } else {
                    $buyurl = " <a href=__DOC__/user.php/sales/orderdetail/orderid/" . $out_trade_no . "><font color=blue>查看</font></a>";
                }
            }
            $showinfo .= "<p><br>类 型：在线购买   " . $buyurl . "</p>";
            $showinfo .= "<p><br>金 额：¥ " . $order_array [0] ['up_amount'] . "</p>";
            $showinfo .= "<p><br>状 态：" . replace_int_vars($order_array [0] ['up_status']) . "</p>";
            $showinfo .= "<p><br>方 式：" . $order_array [0] ['payname'] . "</p>";
            $showinfo .= "<p><br><font color=#cccccc>" . $order_array [0] ['up_dealdate'] . "</font></p>";
            $upinfo ['up_status'] = replace_int_vars($order_array [0] ['up_status']);
            $upinfo ['up_amount'] = $order_array [0] ['up_amount'];
            $upinfo ['up_paytype'] = $order_array [0] ['payname'];
            $upinfo ['reurl'] = $buyurl;
            $upinfo ['up_id'] = $order_array [0] ['up_id'];
        }

        $UPM = new UserPrepaidModel ();
        $productlist = $UPM->getProductListByUpid($upinfo ['up_id']);
        var_dump($upinfo);
        var_dump($buyurl);
        $this->assign('upinfo', $upinfo);
        $this->assign('showhtml', $showinfo);
        $this->assign('ProductList', $productlist);
        $this->assign('mmode', $mmode);
        $this->_renderPage();
    }

    //彩宝页面
    public function caibao()
    {
        $curMenu = I('curMenu');
        $this->assign('curMenu', $curMenu);
        $this->_renderPage();
    }

    //跳转页面
    public function redirecturl()
    {
        $pid = I('pid', 0, 'intval');
        $jump_url = "http://www.ignjewelry.com/index.php/wx-shareabout?pid=" . $pid;
        //echo "<p>".$jump_url."</p>";
        if (is_weixin()) {
            // 微信登录
            $launchUrl = WX_CALLBACK_DOMAIN . '/index/auth-wx_launch?jump_uri=' . $jump_url;
            //echo $launchUrl;
            redirect($launchUrl);
        }
    }

    //分享成功后数据处理
    public function shareafter()
    {
        $arr = array();
        //获取被分享人的信息
        $arr['s_uid'] = I('suid', 0, 'intval');
        $arr['s_time'] = time();
        $arr['s_url'] = I('slnk');
        $arr['s_pid'] = I('spid', 0, 'intval');
        $arr['s_nickname'] = I('snickname');
        $arr['s_headimgurl'] = I('sheadimgurl');
        $re = M('share_log')->add($arr);
        if ($re) {
            $data['code'] = 1;
        } else {
            $data['code'] = 0;
        }
        $this->ajaxReturn($data, JSON);
        //echo "<p>这是处理数据的ACTION</p>";
    }

    //分享
    public function shareabout()
    {
        $pid = I('pid', 0, 'intval');
        $sql="select up_agent_userid,up_orderid from tdf_user_prepaid where p_id=".$pid." ";
        $prepaidInfo    = M('user_prepaid')->query($sql);
        /*$prepaidInfo = M('user_prepaid')->where('p_id=' . $pid)->getField('up_agent_userid,up_orderid');*/
        $prepaidInfo    =$prepaidInfo[0];
        $userAgent  = $prepaidInfo['up_agent_userid'];
        $up_orderid = $prepaidInfo['up_orderid'];
        if ($userAgent == '0') {
            session_start();
            $uid = $_SESSION['f_userid'];
            //说明为首次分享,修改相关表中数据
            $res = M('product')->where('p_id=' . $pid)->save(array('p_creater' => $uid));
            //更新用户定制表中的u_id字段
            $p_diy_id = M('product')->where('p_id=' . $pid)->getField('p_diy_id');
            $re = M('user_diy')->where('id=' . $p_diy_id)->save(array('u_id' => $uid));
            //修改订单表中数据
            $pinfo = M('user_prepaid')->where('p_id=' . $pid)->field('up_uid,up_id')->find();
            $con['up_uid'] = $uid;
            $con['up_agent_userid'] = $pinfo['up_uid'];
            $resp = M('user_prepaid')->where("up_id=" . $pinfo['up_id'])->save($con);
            //订单表修改成功,获取用户的基本信息
            $arr = M('user_auth')->where('u_id=' . $uid)->field('NickName,Headimgurl')->find();
            $messArr['nickname'] = $arr['NickName'];
            $Headimgurl = $arr['Headimgurl'];
            //获取产品的图片信息
            $pinfo = M('product')->where('p_id=' . $pid)->find();
            $messArr['cover'] = $pinfo['p_cover'];
            $messArr['s_uid'] = $uid;
            $messArr['p_diy_id']   = $pinfo['p_diy_id'];

            $yuan_uid=$uid;
        } else {
            session_start();
            $uid = $_SESSION['f_userid'];
            //不是首次分享 获取首次被分享人的信息
            $f_uid = M('user_prepaid')->where('p_id=' . $pid)->getField('up_uid');
            $userInfo = M('user_auth')->where('u_id=' . $f_uid)->field('NickName,Headimgurl')->find();
            $messArr['nickname'] = $userInfo['NickName'];
            $Headimgurl = $userInfo['Headimgurl'];
            //获取产品图片信息
            $productInfo= M('product')->where('p_id=' . $pid)->find();
            //getField('p_cover');
            //获取分享人的用户ID
            $messArr['s_uid'] = $uid;
            $messArr['cover'] = $productInfo['p_cover'];
            $messArr['p_diy_id']   = $productInfo['p_diy_id'];
            $yuan_uid=$f_uid;
        }
        $messArr['pid'] = $pid;
        Vendor('wxshare.jssdk');
        $jssdk = new JSSDK("wxf71f9222d3bc3c2e", "7338ad0158221be35c8572c562a56899");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);
        $this->assign('Headimgurl', $Headimgurl);
        $this->assign('info', $messArr);
        //var_dump($messArr);
        //exit;
        
        $UPM=new UserPrepaidModel();
        $up_orderid_en=$UPM->encode_pass ( $up_orderid,'1',"encode" );
        $orderurl=WEBROOT_URL."/user.php/wxuser/orderdetail/ordertype/2/orderid/".$up_orderid_en;
        $this->assign('orderurl', $orderurl);
        $ismyself=0;
        if($yuan_uid==$_SESSION['f_userid']){
            $ismyself=1;
        }
        $this->assign('ismyself', $ismyself);
        $this->display();
    }

    /***
     * 夏木专题页2016
     */
    public function wxxyqm()
    {
        $this->display();
    }

    /**
     * 中秋活动专题页2016
     */
    public function midautumn(){
        Vendor('wxshare.jssdk');
        $jssdk = new JSSDK("wxf71f9222d3bc3c2e", "7338ad0158221be35c8572c562a56899");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);
        $this->display();
    }

    /***跳转**/
    public function jumpUrl(){
        $furl = urlencode('http://www.ignjewelry.com/index.php/wx');
        $url = urldecode(I('url',$furl,'string'));
        //直接跳转
        echo "<script>window.location.href = '".$url."'</script>";
    }

    //你画我猜活动跳转页面
    public function draw(){
        //$url="http://open.mediabook.com.cn/ignite";
        $stype=I('stype','0','string');
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxb61659fe424a1469&redirect_uri=http://expand.mesplus.cc/opendev/public/ignite%3fXDEBUG_SESSION_START%3d10772&response_type=code&scope=snsapi_base&state=STATE&stype=".$stype."";
        $this->saveLogActive($stype);
        echo "<script>window.location.href = '".$url."'</script>";
        //redirect($url);
    }


    //你画我猜活动跳转到编辑器页面
    public function goign(){
        $img=I('img','0','string');
        $url="http://www.ignjewelry.com/index.php/diy-jewelryeditall-cid-1-img-".$img."";
        echo "<script>window.location.href = '".$url."'</script>";
        //redirect($url);
    }

    /**
     * 保存页面访问日志到log_active
     */
    public function saveLogActive($code){
        $data['code']=$code;
        $data['ip']=get_client_ip();
        $data['url']=__SELF__ ;
        $data['sessionid']=session_id();
        M('log_active')->add($data);
        return true;
    }


    /***了解品牌***/
    public function brandstory(){
        $this->_renderPage();
    }

}