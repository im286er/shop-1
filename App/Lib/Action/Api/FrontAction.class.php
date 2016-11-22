<?php

/**
 * 用户相关API
 *
 * @author miaomin
 * Oct 15, 2013 11:11:56 AM
 *
 * $Id: UsersAction.class.php 1148 2013-12-20 07:32:44Z miaomiao $
 */
class FrontAction extends CommonAction
{

    // TODO
    // 魔术方法
    public function __call($name, $arguments)
    {
        throw new Exception ($this->RES_CODE_TYPE ['METHOD_ERR']);
    }

    /*
     * APP获取轮播图信息
     */
    public function getproductimages()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        // 获取请求信息
        $postData = json_decode(base64_decode($args ['reqdata']), true);

        $PPM = new ProductPhotoModel();
        $res = $PPM->getPhotosByPID($postData['pid']);

        return $res;
    }

    /*
     * APP获取首页banner
     */
    public function getbanner()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        // 获取首页banner
        $HM = new HelpModel();
        $bannerInfo = $HM->getbanner();
        if (!$bannerInfo) {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res = $bannerInfo;
        return $res;
    }

    /*
     * APP获取首页推荐图片
     */
    public function getapptj()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        // 获取首页banner
        $HM = new HelpModel();
        $bannerInfo = $HM->getapptj();
        if (!$bannerInfo) {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res = $bannerInfo;
        return $res;
    }


    /*
    * APP获取搜索列表
    */
    public function getsearch()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $DatasInfo = json_decode(base64_decode($args ['datas']), true);
        $SearchInfo['tags'] = $DatasInfo['keywords'];
        $SearchInfo['order'] = "score_desc";
        $SearchInfo['page'] = 1;
        $SearchInfo['thumb'] = 1;
        $SearchInfo['disp'] = 1;
        $SearchInfo['count'] = 30;
        $PSM = new ProductSearchModel ($SearchInfo, 'nonediy', true);
        $productlist = $PSM->getResult(1);
        foreach ($productlist as $key => $value) {
            //$productlist[$key]['title2']=$productlist[$key]['p_mini'];
            $productlist[$key]['p_cover'] = WEBROOT_URL . $productlist[$key]['p_cover'];
            unset($productlist[$key]['p_views_disp']);
            unset($productlist[$key]['p_zans']);
            unset($productlist[$key]['p_producttype']);
            unset($productlist[$key]['p_diy_cate_cid']);
            unset($productlist[$key]['p_mini']);
        }
        if (!$productlist) {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res = $productlist;
        return $res;
    }

    /*
     * 获取热门搜索标签
     */
    public function gethottags()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $PTM = new ProductTagsModel();
        $hottags = $PTM->getHotTags();
        if (!$hottags) {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res = $hottags;
        return $res;
    }

    /*获取diy详情, 不含编辑器数据*/
    public function getdiydetail()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $DatasInfo = json_decode(base64_decode($args ['datas']), true);
        $cid = $DatasInfo['cid'];
        $DCM = new DiyCateModel();
        $diyCateInfo = $DCM->appDiyDetailByCid($cid);
        // var_dump($diyCateInfo);
        if (!$diyCateInfo) {
            throw new Exception ($this->RES_CODE_TYPE ['DIYCID_FAIL']);
        }
        // $diyCateInfo['intro']="aab";
        // $diyCateInfo['images']="124";
        $res[] = $diyCateInfo;
        return $res;
    }

    /*获取diy基本数据*/
    public function getdiybase()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $DatasInfo = json_decode(base64_decode($args ['datas']), true);
        $cid = $DatasInfo['cid'];
        $DCM = new DiyCateModel();
        $diyCateInfo = $DCM->appDiyCateByCid($cid);
        if (!$diyCateInfo) {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res[] = $diyCateInfo;
        return $res;
    }

    /*获取diy部件(控件)*/
    public function getdiyunit()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }

        $DatasInfo = json_decode(base64_decode($args ['datas']), true);
        $cid = $DatasInfo['cid'];
        $DUM = new DiyUnitModel();
        //$diyUnitInfo=$DUM->getDiyUnitByCid($cid);
        $diyUnitInfo = $DUM->getDiyUnitByCid($cid);
        if (!$diyUnitInfo) {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res = $diyUnitInfo;
        return $res;
    }

    /**
     * 获取用户DIY方案详情_编辑
     */
    public function userdiyedit()
    {
        // 返回结果
        $res = array();
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        // 处理用户名和密码信息
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load('@.Reginer');
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login($logininfo);
        if (!$loginRes) {
            throw new Exception ($reginer->ErrorCode);
        }
        if ($args['datas']) {
            $datas = json_decode(base64_decode($args['datas']), true);
            $udid = $datas['udid'];
        }

        //--------------------------- 获取userdiy
        $DUM = new DiyUnitModel();
        $diyUnitInfoValue = $DUM->get_udinfo_all($udid);
        $diyUnitInfo = $DUM->getDiyUnitByCid($diyUnitInfoValue['cid']);
        foreach ($diyUnitInfo as $key => $value) {
            if ($value['fieldtype'] == 'NECKLACE') {
                $diyUnitInfo[$key]['unit_default'] = $DUM->getNecklacePrice($value['unit_default']);
            } else {
                $diyUnitInfo[$key]['unit_default'] = $diyUnitInfoValue[$value['unit_name']];
            }
        }
        $res = $diyUnitInfo;
        return $res;
    }


    /*获取材料*/
    public function getmaterial()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $PMM = new PrinterMaterialModel();
        $PMinfo = $PMM->getDiyMaterial();
        if (!$PMinfo) {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res = $PMinfo;
        return $res;
    }


    /*获取分类*/
    public function getcates()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $CM = new CatesModel();
        $CatesInfo = $CM->getCates("pc_type=6 and pc_parentid=1263 ");
        //  var_dump($CatesInfo);
        foreach ($CatesInfo as $key => $value) {
            $CatesInfo[$key]['pc_icon'] = WEBROOT_URL . $value['pc_icon'];
        }
        if (!$CatesInfo) {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res = $CatesInfo;
        return $res;
    }

    /*
      * APP根据分类获取产品列表
      */
    public function getlistbycate()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);

        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $DatasInfo = json_decode(base64_decode($args ['datas']), true);

        @load('@.SearchParser');
        $SP = new SearchParser ();
        $SP->parseUrlInfo(true);
        $SearchInfo = $SP->SearchInfo;
        //$SearchInfo ['page'] = $this->_get ( 'page' );
        $SearchInfo ['count'] = 20;
        $SearchInfo ['order'] = 'dispweight_asc';
        if (!$DatasInfo['pc_id']) {
            $SearchInfo ['category'] = '1263';
        } else {
            $SearchInfo ['category'] = $DatasInfo['pc_id'];
        }
        //var_dump($SearchInfo);
        $SearchInfo['isorignal'] = 1; //对应的设置查询字段为p_lictype 为1是DIY中显示
        $PSM = new ProductSearchModel ($SearchInfo, 'category', true);
        $PSM->DisplayFields = 'tdf_product.p_id,tdf_product.p_producttype,tdf_product.p_price,tdf_product.p_name,';
        $PSM->DisplayFields .= 'tdf_product.p_cover,tdf_product.p_views_disp,tdf_product.p_zans,';
        $PSM->DisplayFields .= 'tdf_product.p_diy_cate_cid,tdf_product.p_dispweight';

        $ProductList = $PSM->getResult($SP->SearchInfo ['page']);

        foreach ($ProductList as $key => $value) {
            $ProductList[$key]['p_cover'] = WEBROOT_URL . $value['p_cover'];
            if ($value['p_diy_cate_cid']) {
                $ProductList[$key]['diyurl'] = WEBROOT_URL . "/index/diy-jewelryeditall-cid-" . $value['p_diy_cate_cid'] . ".html";
            } else {
                $ProductList[$key]['diyurl'] = "";
            }
        }
        if (!$ProductList) {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res = $ProductList;
        return $res;
    }


    /*
   * APP获取APP精选产品列表
   */
    public function getapptoplist()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $SM = new SettingModel();
        $result = $SM->getIdIndex('appjx');
        $IDstr = $result['value'];
        $ProductList = $SM->getProductsByID($IDstr);
        if ($ProductList) {
            foreach ($ProductList as $key => $value) {
                $ProductList[$key]['p_cover'] = WEBROOT_URL . $value['p_cover'];
            }
        } else {
            throw new Exception ($this->RES_CODE_TYPE ['banner_error']);
        }
        $res = $ProductList;
        return $res;
    }

    /*
      * APP获取非DIY商品详情
      */
    public function productdetail()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $DatasInfo = json_decode(base64_decode($args ['datas']), true);
        $pid = $DatasInfo['pid'];

        // 商品信息
        $PM = new ProductModel ();
        $pmRes = $PM->getNoneDiyProductInfoByID($pid);
        $pmRes ['p_gprice'] = $pmRes ['p_price'];

        // 商品图片
        $PPM = new ProductPhotoModel ();
        $ppmRes = $PPM->getPhotosByPID($pid);
        foreach ($ppmRes as $key => $value) {
            $ppImg .= WEBROOT_URL . $value['pp_path'] . "/o/" . $value['pp_filename'] . ",";
        }
        $ppImg = substr($ppImg, 0, strlen($ppImg) - 1);

        //属性选择器
        $frontProduct = A("Front/Product"); // 调用user分组下的sales模块
        $selectorJSON = $frontProduct->_getProductPropSelector($pid);
        $selectorArr = json_decode($selectorJSON, true);

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


        //$productDetail['p_id']    =$pmRes['p_id'];
        $productDetail['p_name'] = $pmRes['p_name'];//商品名称
        $productDetail['productIntroHtml'] = str_replace('<img src="', '<img src="' . WEBROOT_URL, $pmRes['p_intro']);//商品名称
        $productDetail['p_mini'] = $pmRes['p_mini'];//简介
        $productDetail['p_price'] = $pmRes['p_price'];//价格
        $productDetail['p_zans'] = $pmRes['p_zans'];//点赞数
        $productDetail['p_cover'] = WEBROOT_URL . $pmRes['p_cover'];//分享图片
        $productDetail['url'] = WEBROOT_URL . "/index/product-detail-id-" . $pmRes['p_id'] . ".html";//分享地址
        $productDetail['images'] = $ppImg;//图片
        $productDetail['maintype'] = $pmpmRes;//商品主属性
        $productDetail['selector'] = $selectorArr;//属性选择器
        $productDetail['ppvm'] = $ppvmRes;//商品属性值

//print_r($productDetail);
//exit;
        if (!$pmRes) {
            throw new Exception ($this->RES_CODE_TYPE ['DETAIL_ERROR']);
        }
        $res[] = $productDetail;
        return $res;
    }


    /*
    * APP获取微信支付参数配置
    */
    public function getappwxconf()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        Vendor('Wxpay.WxNative.WxPayPubHelper');
        $appConf['APP_ID'] = WxPayConf_pub::APPID;
        $appConf['APP_SECRET'] = WxPayConf_pub::APPSECRET;
        $appConf['PARTNER_ID'] = WxPayConf_pub::KEY;
        if (!$appConf) {
            throw new Exception ($this->RES_CODE_TYPE ['APPCONF_ERROR']);
        }
        $res[] = $appConf;
        return $res;
    }

    /**
     * APP微信用户登录
     */
    public function wxuserlogin()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $DatasInfo = json_decode(base64_decode($args ['datas']), true);
        $wxResArr['unionid'] = $DatasInfo['unionid'];
        $wxResArr['nickname'] = $DatasInfo['nickname'];

        // 需要在本地DB更新相关数据
        $UAM = new UserAuthModel ();
        $authRes = $UAM->getOpenId($wxResArr['unionid'], 2);
        if ($authRes === null) {
            // 查询结果为空,补齐绑定数据
            $currentBindType = 'userinfo';
            $wxResArr ['bindtype'] = $currentBindType;
            $wxResArr ['from'] = 6;
            $wxResArr ['authtype'] = 2;
            //print_r($wxResArr);
            //exit;
            $uid = $UAM->bindByWXOpenId($wxResArr ['unionid'], $wxResArr);
        } else {
            $uid = $authRes ['u_id']; // 已绑定
        }
        if ($uid) {
            $UM = new UsersModel();
            $userInfo = $UM->getUserByID($uid);
            $result['u_mob_no'] = $userInfo['u_email'];
            $result['u_pass'] = '3dcity2014';
            $result['u_id'] = $userInfo['u_id'];
            $result['u_avatar'] = $userInfo['u_avatar'];
            $result['u_dispname'] = $userInfo['u_dispname'];
            $result['u_status'] = $userInfo['u_status'];
            $result['u_salt'] = $userInfo['u_salt'];

        } else {
            throw new Exception ($this->RES_CODE_TYPE ['APPWX_LOGIN_ERR']);
        }
        $res[] = $result;
        return $res;
    }

    /**
     * 点赞动作
     */
    public function addzans()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $DatasInfo = json_decode(base64_decode($args ['datas']), true);
        $p_id = $DatasInfo['p_id'];
        $ip = $DatasInfo['ip'];
        $uid = $logindata[0] == 1 ? 0 : $logindata[0];
        $PM = new ProductModel();
        $result['result'] = $PM->zanAdd($uid, $p_id, $ip);
        $res[] = $result;
        return $res;
    }


    /*
     * 获取客服电话
     */
    public function gettel()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $SM = new SettingModel();
        $telInfo = $SM->getIdIndex('webconfig');
        $telArr['tel'] = $telInfo['value'];
        if (!$telArr) {
            throw new Exception ($this->RES_CODE_TYPE ['TEL_ERR']);
        }
        $res[] = $telArr;
        return $res;
    }

    /**
     * app获取付款url用来生成二维码
     */
    public function getpayurl()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $pkey = json_decode(base64_decode($args ['datas']), true);
        $p_key = $pkey['pkey'];
        $TPM = new ProductModel();
        $p_id = $TPM->getPidByPkey($p_key);
        $UPM = new UserPrepaidModel();
        $up_orderid = $UPM->getPrepaidOidByPid($p_id);
        $up_orderid_en = $UPM->encode_pass($up_orderid, '1', "encode");
        $result['url'] = WEBROOT_URL . "/user.php/wxuser/orderdetail/ordertype/1/orderid/" . $up_orderid_en;
        if (!$result) {
            throw new Exception ($this->RES_CODE_TYPE ['TEL_ERR']);
        }
        $res[] = $result;
        return $res;
    }

    /*
     * App点击线下付款接口 ： 返回订单信息
     */
    public function paycash()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        /*  $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
          if(!$logindata){
              throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
          }*/
        $pkey = json_decode(base64_decode($args ['datas']), true);
        $p_key = $pkey['pkey'];
        $TPM = new ProductModel();
        $productInfo = $TPM->getProductinfoByPkey($p_key);
        $p_id = $productInfo['p_id'];
        $UPM = new UserPrepaidModel();
        $orderInfo = $UPM->getPrepaidInfoByPid($p_id);
        $UDM = new UserDiyModel();
        $UCM = new UserCartModel();
        $result['up_id'] = $orderInfo['up_id'];
        $result['up_orderid'] = $orderInfo['up_orderid'];
        $result['total_price'] = $orderInfo['up_amount'];
        $result['up_status'] = $orderInfo['up_status'];
        $result['ctime'] = $orderInfo['ctime'];
        $result['p_name'] = $productInfo['p_name'];
        if ($productInfo['p_producttype'] == 4) {
            $userDiyInfo = $UDM->getUserDiyInfoById($productInfo['p_diy_id']);
            $productInfo['diy_unit_info'] = $userDiyInfo['diy_unit_info'];
            if ($productInfo['p_cate_4'] == 1) {
                $result['p_description'] = "简笔画";
            } else {
                $result['p_description'] = $UCM->getUserCartDiyByProduct($productInfo);
            }
        } else {
            $result['p_description'] = '';
        }
        if (!$result) {
            throw new Exception ($this->RES_CODE_TYPE ['TEL_ERR']);
        }
        //var_dump($productInfo);
        $res[] = $result;
        return $res;

    }

    /*
     * App点击在线付款接口 ： 返回订单信息
     */
    public function paycashonline()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);// 解析用户信息
        /*  $logindata = $this->parseRequestUserHandle ( $args ['visa'] );
          if(!$logindata){
              throw new Exception ( $this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR'] );
          }*/
        $pkey = json_decode(base64_decode($args ['datas']), true);
        $orderid = $pkey['orderid'];
        $UPM = new UserPrepaidModel();
        $orderInfo = $UPM->getPrepaidInfoByPidonline($orderid);
        $TPM = new ProductModel();
        $productInfo = $TPM->getProductinfoByPid($orderInfo['p_key']);

        $UDM = new UserDiyModel();
        $UCM = new UserCartModel();
        $result['up_id'] = $orderInfo['up_id'];
        $result['up_orderid'] = $orderInfo['up_orderid'];
        $result['total_price'] = $orderInfo['up_amount'];
        $result['up_status'] = $orderInfo['up_status'];
        $result['ctime'] = $orderInfo['ctime'];
        $result['p_name'] = '智绘首饰';
        if ($productInfo['p_producttype'] == 4) {
            $userDiyInfo = $UDM->getUserDiyInfoById($productInfo['p_diy_id']);
            $productInfo['diy_unit_info'] = $userDiyInfo['diy_unit_info'];
            if ($productInfo['p_cate_4'] == 1) {
                $result['p_description'] = "简笔画";
            } else {
                $result['p_description'] = $UCM->getUserCartDiyByProduct($productInfo);
            }
        } else {
            $result['p_description'] = '';
        }
        if (!$result) {
            throw new Exception ($this->RES_CODE_TYPE ['TEL_ERR']);
        }
        //var_dump($productInfo);
        $res[] = $result;
        return $res;

    }


    /**
     * guolixun add@2016.06.12
     *
     * 分享接口
     */
    public function getshareurl()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        // 获取请求信息
        $pkey = json_decode(base64_decode($args ['datas']), true);
        $pkey = $pkey['pkey'];
        $TPM = new ProductModel();
        $p_id = $TPM->getPidByPkey($pkey);
        $result['url'] = WEBROOT_URL . "/index.php/wx-redirecturl?pid=" . $p_id;
        // return ['url']=WEBROOT_URL;
        $res[] = $result;
        return $res;
    }

    /*
     * APP获取启动页
     */
    public function getstartpage()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        // 获取启动页
        $HM = new HelpModel();
        $Info = $HM->getstartpage();
        if (!$Info) {
            throw new Exception ($this->RES_CODE_TYPE ['startPages_error']);
        }
        $res = $Info;
        return $res;
    }

//新的diy编辑器(react)读取diy配置和数据接口
    public function getdiyinfo()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        // echo "abc";

        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        // 获取请求信息
        $datas = json_decode(base64_decode($args ['datas']), true);
        //$datas = json_decode($args ['datas'],true);
        $cid = $datas['cid'];
        $DCM = new DiyCateModel();
        $cateArr = $DCM->getDiyCateByCid($cid);
        $res['conf_json'] = json_decode($cateArr['conf_json']);
        $diyId = $datas['diyid'];
        if ($diyId) {
            $UDM = new UserDiyModel();
            $diyInfo = $UDM->getUserDiyInfoById($diyId);
            if ($cateArr['cate_type'] == 5) {
                $diyUnitInfo = unserialize($diyInfo['diy_unit_info']);
                $res['proj_json'] = PUBPATH . current($diyUnitInfo);
                $res['proj_img'] = $this->getimgurl($diyInfo['img']);
               //echo $diyInfo['img'];
               //exit;
            } else {
                $res['proj_json'] = json_decode($diyInfo['diy_unit_info']);//小鱼
            }
        } else {
            if ($cateArr['cate_type'] == 5) {
                if ($cid == 56 || $cid == 57 || $cid == 58) {
                    $res['proj_json'] = '';
                    $res['proj_img'] = '';
                } else {
                    $res['proj_json'] = WEBROOT_URL . '/upload/project/20160426/jsonfile/3d2e88834544ac04.json';
                    $res['proj_img'] = WEBROOT_URL . '/upload/project/20160426/image/o/e8d0f082aebe7168.png';
                }
            } else {
                $res['proj_json'] = '';
            }
        }

        $TPM = new PrinterMaterialModel();
        $PMInfo = $TPM->getDiyMaterial(1);
        //var_dump($PMInfo);

        foreach ($PMInfo as $key => $value) {
            /* $PM[$value['pma_id']]['pma_diy_formula_s']=$value['pma_diy_formula_s']."+".$value['pma_necklace_price'];
             $PM[$value['pma_id']]['pma_diy_formula_b']=$value['pma_diy_formula_b']."+".$value['pma_necklace_price']; */
            $PM[$value['pma_id']]['pma_diy_formula_s'] = $value['pma_diy_formula_s'];
            $PM[$value['pma_id']]['pma_diy_formula_b'] = $value['pma_diy_formula_b'];
        }
        //var_dump($PM);
        $TDM = new DiyDiamondModel();
        $DiamondInfo = $TDM->getDiyDiamondPriceByArrId('85,86,87,88,89,90');
        $res['pma_formula'] = $PM;
        $res['pma_diamond'] = $DiamondInfo;
        return $res;
    }

    /**
     * @param $img
     */
    public function getimgurl($img){
        if(SAVETOOSS){
            $result=PUBIMGPATH.$img."@1wh.jpg";
        }else{
            $result=PUBIMGPATH.$img;
        }
        return $result;
    }


//新的diy编辑器(react)保存diy数据接口(小鱼)
    public function savediyinfo()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        $logindata = $this->parseRequestUserHandle($args ['visa']);        // 解析用户信息
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        // 获取请求信息
        $datas = json_decode(base64_decode($args ['datas']), true);
        $UDM = new UserDiyModel();
        $result = $UDM->saveDiyJson($datas);
        $res['result'] = $result;
        return $res;
    }

    //新的diy编辑器(react)保存diy数据接口(简笔画)
    public function saveproject()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        // 获取请求信息
        $datas = json_decode(base64_decode($args ['datas']), true);
        $PM = new ProductModel();
        $diyId = $datas['diyid'];
        if ($diyId) {
            $pm_info = $PM->getProductByDiyid($diyId);
            $pid = $pm_info['p_id'];
        } else {
            $pid = 0;
        }
        $UDM = new UserDiyModel();
        $result = $UDM->saveDiyJsonProject($pid, $datas);
        $res['result'] = $result;
        return $res;
    }


//第三方获取产品价格,
    public function productprice()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle_nopubencode($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load('@.Reginer');
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login($logininfo);
        if (!$loginRes) {
            throw new Exception ($reginer->ErrorCode);
        }
        $datas = json_decode(base64_decode($args ['datas']), true);
        $PM = new ProductModel();
        $diy_id = $datas['pid'];
        $result = $PM->getProductByDiyid($diy_id);

        if (!$result) {
            throw new Exception ($this->RES_CODE_TYPE ['PRODUCT_ID_DOES_NOT_EXIST']);
        }
        $res['price'] = $result['p_price'];
        return $res;
    }


    //第三方通过pid生成订单接口
    public function ordernotify()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle_nopubencode($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load('@.Reginer');
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login($logininfo);
        if (!$loginRes) {
            throw new Exception ($reginer->ErrorCode);
        }
        $datas = json_decode(base64_decode($args ['datas']), true);
        $PM = new ProductModel();
        $diy_id = $datas['pid'];
        $uid = $loginRes['u_id'];
        $pcount = $datas['count'];
        $result = $PM->productToOrder($diy_id, $uid, $pcount);
        if (!$result) {
            throw new Exception ($this->RES_CODE_TYPE ['PRODUCT_PREPAID_ERR']);
        }
        $res[] = $result;
        return $res;
    }

    /**orderconfirm    麦德龙
     * 第三方通过多个detail(pid:数量,pid:数量)生成订单接口
     * @return array
     * @throws Exception
     */
    public function orderconfirm()
    {
        header("Content-type: text/html; charset=utf-8");
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle_nopubencode($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load('@.Reginer');
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login($logininfo);
        if (!$loginRes) {
            throw new Exception ($reginer->ErrorCode);
        }

        /*$res[]=$args ['datas'];
         return $res;
         exit;*/
        $datasBase64Decode = base64_decode($args['datas']);
        if (is_null(json_decode($datasBase64Decode, true))) {
            throw new Exception ($this->RES_CODE_TYPE ['JSON_DATA_ERROR']);
        }
        $datas = json_decode($datasBase64Decode, true);
        $PM = new ProductModel();
        $postData['thirdOrderid'] = $datas['orderid'];      //第三方的orderid
        $postData['detail'] = $datas['detail'];       //pid1:count1,pid2:count2
        $postData['uid'] = pub_encode_pass($datas['clientuserid'], $this->_publicKey, 'decode');      //uid
        $postData['addressee'] = $this->unicode_decode($datas['addressee']);   //收件人姓名
        $postData['address'] = $this->unicode_decode($datas['address']);     //收件人地址
        $postData['mobile'] = $datas['mobile'];      //收件人手机
        /*$res[]=$postData;
         return $res;
         exit;*/
        $result = $PM->productToOrder($postData);
        if (!$result) {
            throw new Exception ($this->RES_CODE_TYPE ['PRODUCT_PREPAID_ERR']);
        } elseif ($result == -1) {
            throw new Exception ($this->RES_CODE_TYPE ['PRODUCT_PRICE_ERR']);
        } else {
            $resultEnd['status'] = 1;
            $resultEnd['upid'] = $result;
        }
        $res[] = $resultEnd;
        return $res;
    }


    /**orderstatus    麦德龙
     * 第三方通过第三方orderid查询订单状态
     * @return array
     * @throws Exception
     */
    public function orderstatus()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle_nopubencode($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load('@.Reginer');
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login($logininfo);
        if (!$loginRes) {
            throw new Exception ($reginer->ErrorCode);
        }
        $datas = json_decode(base64_decode($args ['datas']), true);
        $postData['uid'] = pub_encode_pass($datas['clientuserid'], $this->_publicKey, 'decode');      //uid
        $postData['thirdOrderid'] = $datas['orderid'];
        $pcount = $datas['count'];
        $UPM = new UserPrepaidModel();
        $result = $UPM->productOrderStatus($postData);
        if ($result['status'] == -1) {
            throw new Exception ($this->RES_CODE_TYPE ['ORDERID_NOT_EXIEXT']);
        }
        $res[] = $result;
        return $res;
    }

    /**orderexpress    麦德龙订单物流状态
     * 第三方通过第三方orderid查询物流状态
     * @return array
     * @throws Exception
     */
    public function orderexpress()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle_nopubencode($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];
        // 登录
        load('@.Reginer');
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login($logininfo);
        if (!$loginRes) {
            throw new Exception ($reginer->ErrorCode);
        }
        $datas = json_decode(base64_decode($args ['datas']), true);
        $postData['uid'] = pub_encode_pass($datas['clientuserid'], $this->_publicKey, 'decode');      //uid
        $postData['thirdOrderid'] = $datas['orderid'];
        $UPM = new UserPrepaidModel();
        $result = $UPM->productOrderStatus($postData);
        if ($result['status'] == -1) {
            throw new Exception ($this->RES_CODE_TYPE ['ORDERID_NOT_EXIEXT']);
        }
        $res[] = $result;
        return $res;
    }


//获取userid的加密码encode
    public function getEncodeUserId()
    {
        $uid = '6336';
        $uid_encode = pub_encode_pass($uid, $this->_publicKey, "encode");
        echo $uid_encode;
    }


    /**
     * 解析json串
     * @param type $json_str
     * @return type
     */
    public function analyJson($json_str)
    {
        $json_str = str_replace('＼＼', '', $json_str);
        $out_arr = array();
        preg_match('/{.*}/', $json_str, $out_arr);
        if (!empty($out_arr)) {
            $result = json_decode($out_arr[0], TRUE);
        } else {
            return FALSE;
        }
        return $result;
    }


//unicode加码转汉字
    function unicode_encode($name)
    {
        $name = iconv('UTF-8', 'UCS-2', $name);
        $len = strlen($name);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2) {
            $c = $name[$i];
            $c2 = $name[$i + 1];
            if (ord($c) > 0) {    // 两个字节的文字
                $str .= '\u' . base_convert(ord($c), 10, 16) . base_convert(ord($c2), 10, 16);
            } else {
                $str .= $c2;
            }
        }
        return $str;
    }

    function unicode_decode($name)
    {
        // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
        $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
        preg_match_all($pattern, $name, $matches);
        if (!empty($matches)) {
            $name = '';
            for ($j = 0; $j < count($matches[0]); $j++) {
                $str = $matches[0][$j];
                if (strpos($str, '\\u') === 0) {
                    $code = base_convert(substr($str, 2, 2), 16, 10);
                    $code2 = base_convert(substr($str, 4), 16, 10);
                    $c = chr($code) . chr($code2);
                    $c = iconv('UCS-2', 'UTF-8', $c);
                    $name .= $c;
                } else {
                    $name .= $str;
                }
            }
        }
        return $name;
    }

    /** 触摸屏获取首页
     * 大屏幕安卓用户第三方码验证
     **/
    public function getthirdinfo()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle_nopubencode($args ['visa']);

        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $UM = new UsersModel();
        $userCodeArr = json_decode(base64_decode($args['datas']), true);
        $agentid = $userCodeArr['userCode'];
        $result = $UM->getThirdInfo($agentid);
        if ($result) {
            $res['status'] = 1;
            $res['companyname'] = $result['u_dispname'];
            //$res['url']     = WEBROOT_URL."/index/diy-jewelryeditall-cid-1-agentid-".$agentid; //安卓经销商页面返回URL
            $res['url'] = WEBROOT_URL . "/index.php/index-androidindex-agentid-" . $agentid;//*************新的URL地址**************


        } else {
            /*            throw new Exception ($this->RES_CODE_TYPE ['AGENT_CODE_ERR']);*/
            $res['status'] = 0;
            $res['companyname'] = 0;
            $res['url'] = ' ';
        }
        return $res;
    }

    /**
     * VR购物车提交订单
     */
    public function vrcartorder()
    {
        $res = array();        // 返回结果
        $args = func_get_args();
        $args = $this->decodeArguments($args);
        // 解析用户信息
        $logindata = $this->parseRequestUserHandle_nopubencode($args ['visa']);
        if (!$logindata) {
            throw new Exception ($this->RES_CODE_TYPE ['USER_VISA_PARSE_ERR']);
        }
        $logininfo ['mobno'] = $logindata [0];
        $logininfo ['pass'] = $logindata [1];
        $logininfo ['from'] = $this->REQUEST_FROM_TYPE ['APP'];// 登录
        load('@.Reginer');
        $reginer = new Reginer ();
        $reginer->ReqestFrom = $this->REQUEST_FROM_TYPE ['APP'];
        $loginRes = $reginer->Login($logininfo);
        if (!$loginRes) {
            throw new Exception ($reginer->ErrorCode);
        }
        $datas = json_decode(base64_decode($args ['datas']), true);
        $productArr = explode(",", $datas['detail']);
        $UPM = new UserPrepaidModel();
        $result = $UPM->createPrepaidByVR($productArr);
        $res[] = $productArr;
        //$res[]=$result;
        return $res;
    }


}

?>