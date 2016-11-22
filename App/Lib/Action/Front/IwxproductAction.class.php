<?php
/**
 * Created by PhpStorm.
 * User: guolixun
 * Date: 2016/8/24
 * Time: 11:00
 */
class IwxproductAction extends CommonAction {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct ();
    }

    /**
     * 构成属性选择器数组
     *
     * @param array $detailPropArr
     */
    private function _recurGenPropArr($detailPropArr) {
        $res = array ();

        $PM = new ProductModel ();

        foreach ( $detailPropArr as $key => $val ) {
            $testArr = $val ['spec'];

            $condition = array (
                $PM->F->ID => $key
            );
            $pmRes = $PM->where ( $condition )->field ( $PM->F->ID . ',' . $PM->F->Price )->find ();

            $evalStr = "\$res";
            foreach ( $testArr as $key => $val ) {
                $evalStr .= "[" . $val . "]";
            }
            $evalStr .= " = \$pmRes;";

            eval ( $evalStr );
        }

        return $res;
    }

    /**
     * 获取属性选择器
     *
     * @param int $pid
     * @return string
     */
    public function _getProductPropSelector($Pid) {
        //echo $Pid;

        // Init
        $PM = new ProductModel ();
        $PMPM = new ProductMainPropModel ();
        $Product = $PM->getNoneDiyProductInfoByID ( $Pid );
        $listAvDetail = $PM->getBelongAvProductList ( $Pid );
        $listProp = $PMPM->getPropByMainType ( $Product [$PM->F->MainType] );

        // 根据属性权限重排
        foreach ( $listAvDetail as $key => $val ) {

            $newPropIdSpec = '';

            $specArr = explode ( '#', $val [$PM->F->PropIdSpec] );
            $tempArr = array ();
            foreach ( $specArr as $k => $v ) {
                $specDetailArr = explode ( ':', $v );
                $tempArr [$specDetailArr [0]] = $specDetailArr [1];
            }

            foreach ( $listProp as $k => $v ) {
                $newPropIdSpec .= $v [$PMPM->F->ID] . ':' . $tempArr [$v [$PMPM->F->ID]] . '#';
            }

            if (substr ( $newPropIdSpec, - 1 ) == '#') {
                $newPropIdSpec = substr ( $newPropIdSpec, 0, - 1 );
            }

            $listAvDetail [$key] [$PM->F->PropIdSpec] = $newPropIdSpec;
        }

        // 获取商品明细
        $detailPropArr = array ();
        foreach ( $listAvDetail as $key => $val ) {
            $specArr = explode ( '#', $val [$PM->F->PropIdSpec] );
            $tempArr = array ();
            foreach ( $specArr as $k => $v ) {
                $specDetailArr = explode ( ':', $v );
                $tempArr [$specDetailArr [0]] = $specDetailArr [1];
            }
            $detailPropArr [$val [$PM->F->ID]] ['spec'] = $tempArr;
        }

        //print_r ( $detailPropArr );
        //exit;
        // 属性选择器
        $res = $this->_recurGenPropArr ( $detailPropArr );

        return json_encode ( $res );
    }

    /**
     * COPY详情
     */
    public function copydetail() {
    }

    /**
     * 详情
     */
    public function detail() {
        try {
            $this->header = "jewelry";
            // PID
            $pid = I ( 'get.id' );

            // 商品信息
            $PM = new ProductModel ();
            $pmRes = $PM->getNoneDiyProductInfoByID ( $pid );
            $pmRes ['p_gprice'] = $pmRes ['p_price'];


            //获取TDK信息 start
            $TPCM=new ProductCateModel();
            $TDK=$TPCM->getCateTDKByPcate($pmRes['p_cate'],$pmRes['p_name']);
            $this->assign("TDK",$TDK);
            //获取TDK信息 end
            // 商品图片
            $PPM = new ProductPhotoModel ();
            $ppmRes = $PPM->getPhotosByPID ( $pid );

            // 属性选择器
            $selectorJSON = $this->_getProductPropSelector ( $pid );

            // 商品主属性
            $PMPM = new ProductMainPropModel ();
            $pmpmRes = $PMPM->getPropByMainType ( $pmRes [$PM->F->MainType] );

            // 商品属性值
            $PPVM = new ProductPropValModel ();
            $condition = array (
                $PPVM->F->MAINTYPE => $pmRes [$PM->F->MainType]
            );
            $ppvmRes = $PPVM->where ( $condition )->field ( $PPVM->F->ID . ',' . $PPVM->F->PROPVAL )->select ();
            $ppvmRes = trans_pk_to_key ( $ppvmRes, $PPVM->F->ID );
            foreach ( $ppvmRes as $key => $val ) {
                $ppvmRes [$key] = $val [$PPVM->F->PROPVAL];
            }

            // 关联商品
            if ($pmRes [$PM->F->Relation]) {

                $relationLowPrice = 9999999;

                $relationPmRes = $PM->getRelationProduct($pid);

                foreach ( $relationPmRes as $key => $val ) {
                    if ($val ['p_price'] <= $relationLowPrice) {
                        $relationLowPrice = $val ['p_price'];
                    }
                }

                $this->assign ( 'RelationProductList', $relationPmRes );

                $pmRes ['p_gprice'] = $pmRes ['p_price'] + $relationLowPrice;
            }

            // 更多推荐
            @load ( '@.SearchParser' );
            $SP = new SearchParser ();
            $SP->parseUrlInfo ( true );
            $SearchInfo = $SP->SearchInfo;
            $SearchInfo ['page'] = 1;
            $SearchInfo ['count'] = 4;
            $SearchInfo ['producttype'] = 5;
            $PSM = new ProductSearchModel ( $SearchInfo, 'model', true );
            $this->assign ( 'SearchResult', $PSM->getResult ( $SP->SearchInfo ['page'] ) );

            // 赋值
            $this->assign ( 'Product', $pmRes );
            $this->assign ( 'ProductPhotoList', $ppmRes );
            $this->assign ( 'Selector', $selectorJSON );
            $this->assign ( 'PropList', json_encode ( $pmpmRes ) );
            $this->assign ( 'PropValList', json_encode ( $ppvmRes ) );

            $this->_renderPage ();
        } catch ( Exception $e ) {
            // echo $e->getMessage ();
        }
    }

    /**
     * 设计师作品详情
     */
    public function worksdetail() {
        try {
            $this->header = "square";

            // PID
            $pid = I ( 'get.id' );

            // 商品信息
            $PM = new ProductModel ();
            $pmRes = $PM->getNoneDiyProductInfoByID ( $pid );

            // 商品图片
            $PPM = new ProductPhotoModel ();
            $ppmRes = $PPM->getPhotosByPID ( $pid );

            // 属性选择器
            $selectorJSON = $this->_getProductPropSelector ( $pid );

            // 商品主属性
            $PMPM = new ProductMainPropModel ();
            $pmpmRes = $PMPM->getPropByMainType ( $pmRes [$PM->F->MainType] );

            // 商品属性值
            $PPVM = new ProductPropValModel ();
            $condition = array (
                $PPVM->F->MAINTYPE => $pmRes [$PM->F->MainType]
            );
            $ppvmRes = $PPVM->where ( $condition )->field ( $PPVM->F->ID . ',' . $PPVM->F->PROPVAL )->select ();
            $ppvmRes = trans_pk_to_key ( $ppvmRes, $PPVM->F->ID );
            foreach ( $ppvmRes as $key => $val ) {
                $ppvmRes [$key] = $val [$PPVM->F->PROPVAL];
            }

            // 更多推荐
            @load ( '@.SearchParser' );
            $SP = new SearchParser ();
            $SP->parseUrlInfo ( true );
            $SearchInfo = $SP->SearchInfo;
            $SearchInfo ['page'] = 1;
            $SearchInfo ['count'] = 4;
            $SearchInfo ['producttype'] = 5;
            $PSM = new ProductSearchModel ( $SearchInfo, 'model', true );
            $this->assign ( 'SearchResult', $PSM->getResult ( $SP->SearchInfo ['page'] ) );

            foreach ( $ppmRes as $k => $v ) {
                $file_960 = "/alidata1/3dcity" . WEBROOT_PATH . $v ['pp_path'] . "o/" . str_replace ( ".", "_960.", $v ['pp_filename'] );
                $imgurl_960 = WEBROOT_PATH . $v ['pp_path'] . "o/" . str_replace ( ".", "_960.", $v ['pp_filename'] );
                $imgurl_600 = DOMAIN . WEBROOT_PATH . $v ['pp_path'] . "o/" . str_replace ( ".", "_600.", $v ['pp_filename'] );
                if (file_exists ( $file_960 )) {
                    $ppmRes [$k] ['imgpath'] = WEBROOT_PATH . $v ['pp_path'] . "o/" . str_replace ( ".", "_960.", $v ['pp_filename'] );
                } else {
                    $ppmRes [$k] ['imgpath'] = $imgurl_600;
                }
            }

            // var_dump($ppmRes);
            // 赋值
            $this->assign ( 'Product', $pmRes );
            $this->assign ( 'ProductPhotoList', $ppmRes );
            $this->assign ( 'Selector', $selectorJSON );
            $this->assign ( 'PropList', json_encode ( $pmpmRes ) );
            $this->assign ( 'PropValList', json_encode ( $ppvmRes ) );

            $this->_renderPage ();
        } catch ( Exception $e ) {
            // echo $e->getMessage ();
        }
    }

    /**
     * 首页
     */
    public function index() {
        try {
            // 使用指定的头部
            $this->header = "jewelry";

            // 新建一个搜索类
            load ( "@.SearchParser" );
            $SP = new SearchParser ();
            // 捕获提交来的参数
            if ($this->isPost ()) {
                $_POST['count'] = 12;
                // dump ( $this->_post () );
                // die();
                if ($SP->parseSearchInfo ( true )) {

                    // 获得url转向（格式为 参数/参数值）
                    $Url = $SP->getFormattedUrl_new ();
                    // var_dump($Url);
                    $RedirectUrl = __APP__ . '/iwxproduct-index-' . substr ( $Url ['url'], 1 );
                    // echo $RedirectUrl;
                    // exit;
                    redirect ( $RedirectUrl );
                }
            } else {

                if ($SP->parseUrlInfo ( true )) {
                    //var_dump($SP->SearchInfo);
                    // @load ( '@.Paging' );
                    $PSM = new ProductSearchModel ( $SP->SearchInfo, 'nonediy', true );
                    //var_dump($SP->SearchInfo);
                    $result = $PSM->getResult ( $SP->SearchInfo ['page'] ,2);
//					echo "<pre>";
//					print_r($result);
//					exit;
                    if ($result){
                        $this->assign ( 'SearchResult', $result );
                        $this->assign ( 'search_key_fmt', trim ( $this->_get ( 'tags' ) ) );
                        $this->assign ( 'DBF_P', $this->DBF->Product );
                        $this->assign ( 'PI', getPagingInfo ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SP->SearchInfo ['count'], 4 ) );
                        $Url = $SP->getFormattedUrl_new ();
                        $BaseUrl = __APP__ . '/iwxproduct-index-' . substr ( $Url ['url'], 1 );
                        $this->assign ( 'BaseUrl', $BaseUrl );
                        $paging = getPagingInfo2 ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SP->SearchInfo ['count'], 4, $BaseUrl,'active');
                        //$this->assign ( 'Paging', $paging );
                        $this->assign ( 'SearchResultCount', $PSM->TotalCount );
                        //新增参数
                        $totalPage=ceil(($PSM->TotalCount)/($SP->SearchInfo['count']));
                        $currentPage = I('page',1,'intval');
                        $this->assign('currentPage',$currentPage);
                        $this->assign('totalPage',$totalPage);
                        $this->assign('disp',$SP->SearchInfo['disp']);
                        $this->assign('thumb',$SP->SearchInfo['thumb']);
                        //新增参数
                    }else{
                    }
                }
                $this->assign('searchtype',1);
            }
            $this->assign ( 'search_key_fmt', trim ( $this->_get ( 'tags' ) ) );
            $this->_renderPage ();
        } catch ( Exception $e ) {

            echo $e->getMessage ();
        }
    }

    /**
     * 通过AJAX调用获取相关商品
     *
     * @param int $pid
     * @return string
     */
    public function getrelproduct() {

        // 返回结果的格式
        $ajaxRes = array(
            'isSuccess' => false,
            'data' => array()
        );

        // PID
        $pid = I ( 'get.id' );

        // 关联商品
        $PM = new ProductModel ();
        $relationPmRes = $PM->getRelationProduct($pid);

        if ($relationPmRes){
            $ajaxRes['isSuccess'] = true;
            $ajaxRes['data'] = $relationPmRes;
        }

        echo $this->ajaxReturn($ajaxRes);
    }
}
?>