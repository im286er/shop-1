<?php

class JewelryAction extends CommonAction
{

    public function __construct()
    {
        parent::__construct();
        $this->DBF = new DBF();
        $this->header = "jewelry";
    }

    public function mycustomize()
    {
        // 必须登录查看
        if (! $this->_isLogin()) {
            $this->_needLogin();
        }
        
        // 查看
        $UCUM = new UserCustomizeModel();
        $ucumRes = $UCUM->getCustomizeList('tdf_users.u_id = "' . $_SESSION['f_userid'] . '"');
        
        if (count($ucumRes[$_SESSION['f_userid']]['pids']) > 0) {
            $where = db_create_in($ucumRes[$_SESSION['f_userid']]['pids'], 'tdf_product.p_id');
            $sql = "SELECT tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_price,tdf_product.p_producttype FROM tdf_product WHERE " . $where;
            $SearchResult = $UCUM->query($sql);
            $SearchResultCount = count($SearchResult);
            
            $this->assign('DBF_P', $this->DBF->Product);
            $this->assign('SearchResult', $SearchResult);
            $this->assign('SearchResultCount', $SearchResultCount);
        }
        
        $this->assign('header', $this->header);
        $this->_renderPage();
    }

    public function index()
    {
        exit();
        // @load ( '@.SearchParser' );
        // $SP = new SearchParser ();
        // $SP->parseUrlInfo ( true );
        // $SearchInfo = $SP->SearchInfo;
        // $SearchInfo ['page'] = 1;
        // $SearchInfo ['count'] = 8;
        // $SearchInfo ['cateory'] = '1263';
        // $SearchInfo ['order'] = 'dispweight_desc';
        
        // $PSM = new ProductSearchModel ( $SearchInfo, 'model', true );
        // $SearchResult = $PSM->getResult ( $SP->SearchInfo ['page'] );
        
        // 由后台填写的p_id显示首页15
        $IND = new SettingModel();
        $result = $IND->getIdIndex();
        $IDstr = $result['value'];
        $ProductList = $this->getProductsByID($IDstr);
        $this->assign('DBF_P', $this->DBF->Product);
        $this->assign('SearchResult', $ProductList);
        
        // 创意首饰显示设计师 start
        $designer = array(
            3173,
            1713,
            1458,
            1845
        ); // 设置创意首饰首页显示的设计师ID
        $UM = new UsersModel();
        $designerlist = $UM->getUsersProfByIDList($designer);
        // 创意首饰显示设计师 end
        
        $this->assign('designerlist', $designerlist);
        $this->assign('header', $this->header);
        $this->_renderPage();
    }

    private function getDesignerByID($IDArray)
    {
        return $Result;
    }

    public function test()
    {
        $test = 'a:1:{i:0;a:76:{s:5:"uc_id";s:3:"761";s:4:"p_id";s:5:"27834";s:4:"u_id";s:2:"25";s:8:"uc_count";i:1;s:14:"uc_producttype";s:1:"5";s:9:"uc_isreal";s:1:"1";s:13:"uc_lastupdate";s:19:"2015-02-09 17:56:07";s:8:"uc_ctime";s:10:"1423475767";s:9:"uc_isbind";s:1:"0";s:10:"uc_bindids";s:0:"";s:11:"uc_masterid";s:1:"0";s:11:"uc_handleuc";s:1:"0";s:6:"p_name";s:15:"Fresh-鹿角戒";s:9:"p_creater";s:2:"25";s:8:"p_cate_1";s:1:"0";s:8:"p_cate_2";s:1:"0";s:8:"p_cate_3";s:1:"0";s:8:"p_cate_4";s:1:"0";s:8:"p_cate_5";s:1:"0";s:7:"p_cover";s:54:"/upload/productphoto/0/c3/27712/o/0470497f4c9699bc.png";s:10:"p_cover_id";s:1:"0";s:7:"p_price";s:3:"258";s:8:"p_vprice";s:1:"0";s:6:"p_tags";s:0:"";s:7:"p_intro";s:0:"";s:6:"p_mini";s:0:"";s:8:"p_author";s:0:"";s:12:"p_createdate";s:19:"2014-12-22 11:45:01";s:12:"p_createtime";s:10:"1419219901";s:12:"p_lastupdate";s:19:"2014-12-22 11:45:01";s:16:"p_lastupdatetime";s:10:"1419219901";s:7:"p_downs";s:1:"0";s:12:"p_downs_disp";s:1:"0";s:7:"p_views";s:1:"0";s:12:"p_views_disp";s:1:"0";s:7:"p_score";s:1:"0";s:10:"p_comments";s:1:"0";s:8:"p_photos";s:1:"0";s:12:"p_dispweight";s:1:"0";s:8:"p_slabel";s:1:"1";s:13:"p_producttype";s:1:"5";s:9:"p_lictype";s:1:"0";s:9:"p_license";s:1:"0";s:15:"p_downloadlimit";s:1:"0";s:8:"p_formal";s:1:"0";s:8:"p_choice";s:1:"0";s:8:"p_source";s:1:"0";s:10:"p_purchase";s:1:"0";s:15:"p_purchase_disp";s:1:"0";s:9:"p_ctprime";s:1:"1";s:8:"p_verify";s:1:"0";s:9:"p_vfy_uid";s:1:"0";s:11:"p_vfy_uname";s:0:"";s:10:"p_vfy_date";s:19:"0000-00-00 00:00:00";s:8:"p_favors";s:1:"0";s:5:"ctime";s:19:"2014-12-22 11:44:34";s:10:"p_mainfile";s:1:"0";s:15:"p_mainfile_disp";s:0:"";s:5:"p_dvs";s:1:"0";s:16:"p_dvs_createtool";s:1:"0";s:10:"p_dvs_pfid";s:1:"0";s:6:"p_zans";s:1:"0";s:8:"p_awards";s:0:"";s:5:"p_oss";s:1:"0";s:10:"p_count_kc";s:1:"0";s:14:"p_price_design";s:1:"0";s:8:"p_diy_id";s:1:"0";s:10:"p_maintype";s:2:"10";s:7:"p_image";s:1:"0";s:11:"p_belongpid";s:5:"27712";s:13:"p_propid_spec";s:20:"26:109#27:113#28:115";s:15:"p_propname_spec";s:0:"";s:10:"p_relation";s:0:"";s:14:"p_diy_cate_cid";s:1:"0";s:6:"p_cate";s:1:"0";s:7:"p_count";i:1;}}';
        echo $test;
        print_r(unserialize($test));
    }

    public function testDecode()
    {
        $str = 'huwenbao';
        $code = $_GET['code'];
        echo 'Code Lens: ' . strlen($code);
        echo "<br><br>";
        $key = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
        var_dump(pub_encode_pass($code, $key, 'decode'));
    }

    public function jewelrylist()
    {
        $divparse = I('diy',0,'intval');
        // 商品目录
        $CatePicker = new CategoryPickerModel();
        $cateList = $CatePicker->getChildList('1263');
        // 商品
        // @load ( '@.Paging' );
        @load('@.SearchParser');
        $SP = new SearchParser();
        $SP->parseUrlInfo(true);
        $SearchInfo = $SP->SearchInfo;
        $SearchInfo['page'] = $this->_get('page');
        $SearchInfo['count'] = 20;
        $SearchInfo['order'] = 'dispweight_asc';
        //是否为轻定制
        if(!$this->_get('diy')){
            //非轻定制
            if (!$this->_get('cate')) {
                $SearchInfo['category'] = '1263';
            } else {
                $SearchInfo['category'] = $this->_get('cate');
            }
            $PSM = new ProductSearchModel($SearchInfo, 'category', true);

            //为Nav显示高亮新增参数,不影响其他操作，勿删-Start
            $this->assign('cateshownav',1);
            $this->assign('cateval',$SearchInfo['category']);
            //为Nav显示高亮新增参数-End

        }else{

            //为Nav显示高亮新增参数,不影响其他操作，勿删-Start
            $this->assign('divshownav',1);
            //为Nav显示高亮新增参数-End

            //轻定制
            $PSM = new ProductSearchModel($SearchInfo,'diycate',true);
        }
        $PSM->DisplayFields = 'tdf_product.p_id,tdf_product.p_name,tdf_product.p_cover,tdf_product.p_price,tdf_product.p_dispweight,tdf_product.p_createdate,tdf_product.p_lastupdate,tdf_product.p_views_disp,tdf_product.p_zans,tdf_product.p_maintype,tdf_product.p_slabel,tdf_product.p_producttype,tdf_product.p_diy_cate_cid,tdf_product.p_wpid,tdf_product.p_awards';
        if(!$this->_get('diy')){
            $sql=$PSM->getResult($SP->SearchInfo['page']);
        }else{
            //轻定制
            $sql=$PSM->getResult($SP->SearchInfo['page'],3);
        }
//        echo "<pre>";
//        print_r($sql);
//        exit;

        $this->assign('DBF_P', $this->DBF->Product);
        $this->assign('SearchResult', $sql);
        $this->assign('SearchResultCount', $PSM->TotalCount);
        $this->assign('CateList', $cateList);
        /**新增参数*/
        $totalPage=ceil(($PSM->TotalCount)/20);
        $currentPage = I('page',1,'intval');
        $this->assign('diyparse',$divparse);
        $this->assign('currentPage',$currentPage);
        $this->assign('totalPage',$totalPage);
        $this->assign('category',$SearchInfo['category']);
        /**新增参数*/

        //$this->assign('Paging', getPagingInfo2($PSM->TotalCount, $this->_get('page'), 20, 4, __SELF__, 'active'));
        $this->_renderPage();
    }

    private function getProductsByID($WhereIn)
    {
        $PM = new ProductModel();
        $sql = "select p_id,p_producttype,p_price,p_name,p_cover,p_views_disp,p_zans,p_diy_cate_cid from tdf_product where p_id in (" . $WhereIn . ")  order by instr('" . $WhereIn . "',p_id ) ";
        $Result = $PM->query($sql);
        // var_dump($PM->getlastsql());
        return $Result;
    }
}
?>