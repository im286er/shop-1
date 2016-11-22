<?php
/**
 * 模型类
 *
 * @author miaomin 
 * Jul 8, 2013 1:44:36 PM
 */
class ModelsAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * 最热门
	 */
	public function hot() {
	}
	
	/**
	 * 发布
	 */
	public function publish() {
		$id = $this->_get ( 'pid' );
		$Product = D ( 'Product' );
		$Product->find ( $id );
		
		if ($Product->p_creater !== $this->_session ( 'f_userid' )) {
			$this->error ( L ( 'no_permission' ), WEBROOT_PATH . '/user.php' );
		} else {
			$s_lable = $this->_get ( 'val' ) == 2 ? 2 : 0;
			$Product = D ( 'Product' );
			if ($Product->where ( "p_id='$id'" )->setField ( $this->DBF->Product->Slabel, $s_lable )) {
				$this->success ( L ( 'save_success' ), WEBROOT_PATH . '/user.php' );
			} else {
				$this->error ( L ( 'error_tips' ), WEBROOT_PATH . '/user.php' );
			}
		}
	}
	/**
	 * 详情
	 */
	public function detail() {
		$this->assign ( "currenturl", pub_encode_pass ( urlencode ( get_url () ), "3dcity", "encode" ) ); // 赋值当前url到模板
		$CM = new CatesModel ();
		$main_cate = $CM->getCateList ( 0, 0, false, 0, true, 'c.pc_type=0 ' );
		$main_cateinfo = $this->unlimitedForLayer ( $main_cate );
		$this->assign ( 'main_cateinfo', $main_cateinfo );
		$CMP = M ();
		$sql_p = "select pc_id,pc_name,pc_count from tdf_product_cate where pc_type=1";
		$print_cate = $CMP->query ( $sql_p );
		$this->assign ( 'print_cate', $print_cate );
		try {
			$Product = $this->verificationRequest ( 'get' );
			$curract = I ( 'get.curract', 0, 'intval' );
			if ($Product) {
				// miaomin added
				$users = D ( 'Users' );
				$users->find ( $Product ['p_creater'] );
				// var_dump($users->getLastSql());
				$ppp = D ( 'Product' );
				$postnum = $ppp->where ( 'p_creater=' . $users->u_id )->count ();
				$userinfo = $users->data ();
				//
				$UF = new UsersModel ();
				$userinfo ['u_domain'] = $UF->getUserProByID ( $userinfo ['u_id'] )->u_domain;
				$userinfo ['postnum'] = $postnum;
				if ($userinfo ['u_domain']) {
					$userinfo ['dis_u_domain'] = $userinfo ['u_domain'];
				} else {
					$userinfo ['dis_u_domain'] = "u" . $userinfo ['u_id'];
				}
				if ($userinfo ['u_avatar']) {
					$userinfo ['u_avatar'] = WEBROOT_PATH . '/upload/avatar/' . $userinfo ['u_avatar'];
				} else {
					$userinfo ['u_avatar'] = WEBROOT_PATH . '/static/images/avatar/default.png';
				}
				$this->assign ( 'user', $userinfo );
				$Product ['Tags'] = $this->formatTags ( $Product );
				$PID = $Product [$this->DBF->Product->ID];
				$PPM = new ProductPhotoModel ();
				$ProductPhoto = $PPM->getPhotosByPID ( $PID );
				$CM = new CatesModel ();
				if ($Product [$this->DBF->Product->Cate_2]) {
					$Category = $CM->callGetParentByCID ( $CM->getCategoryByCID ( $Product [$this->DBF->Product->Cate_2] ) );
					$Category ['pc_name'] = '3D打印模型';
					$Category ['pc_name_link'] = '/models-index-filter-ar-thumb-2-count-30-order-sd';
				} else {
					$Category = $CM->callGetParentByCID ( $CM->getCategoryByCID ( $Product [$this->DBF->Product->Cate_1] ) );
					$Category ['pc_name'] = 'CG模型';
					$Category ['pc_name_link'] = '/models-index-thumb-2-count-30-order-sd-filter-np';
				}
				
				// miaomin added by 2013/4/16 start
				$PR = D ( 'ProductReality' );
				$realityList = $PR->getRealityList ( $PID );
				foreach ( $realityList as $key => $val ) {
					if ($val ['r_type'] == 1) {
						$this->assign ( 'ModelVR', $val );
					} elseif ($val ['r_type'] == 2) {
						$this->assign ( 'ModelAR', $val );
					}
				}
				if (isset ( $_GET ['show'] )) {
					switch ($_GET ['show']) {
						case 'vr' :
							$this->assign ( 'show', 1 );
							break;
						case 'ar' :
							$this->assign ( 'show', 2 );
							break;
					}
				} else {
					$this->assign ( 'show', 0 );
				}
				$this->assign ( 'ModelDetai', '__APP__/models-detail-id-' );
				// miaomin added by 2013/4/16 end
				$SimilarInfo = Array (
						'category' => $Product [$this->DBF->Product->Cate_1],
						'getpartentcate' => false,
						'count' => 4,
						'order' => 'createdate_desc' 
				);
				$PSM = new ProductSearchModel ( $SimilarInfo );
				$SimilarProduct = $PSM->getResult ();
				// miaomin added by 2013/7/15 begin
				// 作者相关作品
				// miaomin added by 2013/4/16 end
				$CreaterInfo = Array (
						// 'category' => $Product [$this->DBF->Product->Cate_1],
						'getpartentcate' => false,
						'count' => 4,
						'order' => 'createdate_desc',
						'creater' => $Product [$this->DBF->Product->Creater] 
				);
				$PSM = new ProductSearchModel ( $CreaterInfo );
				$CreaterProduct = $PSM->getResult ();
				// 产品评论
				$nowPage = $this->_get ( 'page' );
				(empty ( $nowPage )) ? $nowPage = 1 : $nowPage = $this->_get ( 'page' );
				$page_limit = 10;
				$start = ($nowPage - 1) * $page_limit;
				$UC = D ( 'UserComment' );
				$ProductComment = $UC->join ( "tdf_users ON tdf_users.u_id = tdf_user_comment.u_id" )->where ( "tdf_user_comment.uc_slabel = 1 and tdf_user_comment.uc_pid = '" . $PID . "'" )->order ( 'tdf_user_comment.uc_id DESC' )->limit ( $start, $page_limit )->select ();
				
				// 判断评论头像是否存在，不存在用默认图片替换 start
				foreach ( $ProductComment as $key => $value ) {
					if ($value ['u_avatar']) {
						$ProductComment [$key] ['u_avatar'] = WEBROOT_PATH . '/upload/avatar/' . $value ['u_avatar'];
					} else {
						$ProductComment [$key] ['u_avatar'] = WEBROOT_PATH . '/static/images/avatar/default.png';
					}
				}
				// 判断评论头像是否存在，不存在用默认图片替换 end
				// 评论分页
				$comment_count = $UC->join ( "tdf_users ON tdf_users.u_id = tdf_user_comment.u_id" )->where ( "tdf_user_comment.uc_slabel = 1 and tdf_user_comment.uc_pid = '" . $PID . "'" )->count ();
				@load ( '@.Paging' );
				$this->assign ( 'Paging', getPagingInfo2 ( $comment_count, $this->_get ( 'page' ), $page_limit, 4, __SELF__ ) );
				// miaomin added by 2013/7/15 end
				// 下载限制
				
				$DLimit = $Product ['p_downloadlimit'];
				$IsLogined = isset ( $_SESSION ['f_userid'] );
				/*
				 * switch ($DLimit) { case 0 : { break; } case 1 : { $DLimit =
				 * $IsLogined ? 0 : 1; break; } case 2 : { if ($IsLogined) {
				 * $UOPM = new UserOwnProductModel (); $IsPurchased =
				 * $UOPM->IsUserBuyProduct ( $_SESSION ['f_userid'], $PID ); if
				 * ($IsPurchased || $_SESSION ['f_userid'] == $Product
				 * [$this->DBF->Product->Creater]) { $DLimit = 0; } } break; }
				 * default : { break; } }
				 */
				// current login userinfo
				if ($this->_isLogin ()) {
					$loginUser = D ( 'Users' );
					$loginUser->find ( $this->_session ( 'f_userid' ) );
					$this->assign ( 'LoginUser', $loginUser->data () );
				}
				// 收藏信息
				$UF = D ( 'UserFavor' );
				$condition ['u_id'] = $this->_session ( 'f_userid' );
				$condition ['uf_id'] = $Product ['p_id'];
				$condition ['uf_type'] = $Product ['p_producttype'];
				$uf_res = $UF->where ( $condition )->find ();
				if (is_array ( $uf_res )) {
					$this->assign ( 'UserFavor', $uf_res );
				} else {
					$this->assign ( 'UserFavor', 0 );
				}
				// 赞信息
				$this->assign ( 'UserZan', $Product ['p_zans'] );
				
				// 是否允许直接下载
				$UOPM = new UserOwnProductModel ();
				$IsPurchased = $UOPM->IsUserBuyProduct ( $_SESSION ['f_userid'], $PID );
				if (is_array ( $IsPurchased )) {
					$this->assign ( 'AllowDown', 1 );
				} else {
					$this->assign ( 'AllowDown', 0 );
				}
				// var_dump($this->DBF->ProductCategory);
				// var_dump($Product);
				
				// 打印模型
				// ProductPrintModels
				$PRODUCTTYPE = C ( 'PRODUCT.TYPE' );
				if ($Product ['p_producttype'] == $PRODUCTTYPE ['PRINTMODEL']) {
					$PRM = new ProductPrintModelsModel ();
					$prmRes = $PRM->getByp_id ( $PID );
					
					// 打印材料信息
					$materialsArr = $PRM->getMaterials ();
					
					// ProductSupportMaterials
					$PSM = new ProductPMMaterialModel ();
					$condition = array (
							$PSM->F->PID => $PID 
					);
					$psmRes = $PSM->where ( $condition )->select ();
					
					//print_r($psmRes);
					
					if ((count ( $psmRes ) == 1) && ($psmRes [0] [$PSM->F->MATERIALID] == 0)) {
						$supportAllMaterials = 1;
					} else {
						$supportAllMaterials = 0;
					}
					if ((! $supportAllMaterials) && ($psmRes)) {
						foreach ( $materialsArr as $key => $val ) {
							$ChildCount = count ( $val ['Child'] );
							$addCount = 0;
							foreach ( $val ['Child'] as $k => $v ) {
								foreach ( $psmRes as $kp => $vp ) {
									if ($v ['pma_id'] == $vp ['pma_id']) {
										$addCount += 1;
										$materialsArr [$key] ['Child'] [$k] ['checked'] = 1;
										
										if ($vp['ppm_enabled'] == 1){
											$materialsArr [$key] ['Child'] [$k] ['enchecked'] = 1;
										}else{
											$materialsArr [$key] ['Child'] [$k] ['enchecked'] = 0;
										}
									}
								}
								if ($addCount == $ChildCount) {
									$materialsArr [$key] ['checked'] = 1;
								}elseif ($addCount == 0){
									$materialsArr [$key] ['checked'] = 0;
								}else {
									$materialsArr [$key] ['checked'] = 1;
								}
							}
						}
					}
					// 赋值
					// print_r($prmRes);
					
					// echo $supportAllMaterials;
					
					// print_r($materialsArr);
					
					$this->assign ( 'allmaterials', $supportAllMaterials );
					$this->assign ( 'supportMaterials', $materialsArr );
					$this->assign ( 'PrintModels', $prmRes );
				}
				// print_r($Product);
				$this->assign ( 'curract', $curract );
				$this->assign ( 'DBF_P', $this->DBF->Product );
				$this->assign ( 'DBF_PC', $this->DBF->ProductCategory );
				$this->assign ( 'DBF_PM', $this->DBF->ProductModel );
				$this->assign ( 'DBF_PP', $this->DBF->ProductPhoto );
				$this->assign ( 'DBF_PCT', $this->DBF->ProductCreateTool );
				$this->assign ( 'DBF_PF', $this->DBF->ProductFile );
				$this->assign ( 'IsLogined', $IsLogined );
				$this->assign ( 'DLimit', $DLimit );
				$this->assign ( 'Category', $Category );
				$this->assign ( 'Product', $Product );
				$this->assign ( 'ProductPhoto', $ProductPhoto );
				$this->assign ( 'SimilarProduct', $SimilarProduct );
				$this->assign ( 'SimilarNum', count ( $SimilarProduct ) );
				$this->assign ( 'CreaterProduct', $CreaterProduct );
				$this->assign ( 'CreaterNum', count ( $CreaterProduct ) );
				$this->assign ( 'ProductComment', $ProductComment );
				
				$showtitle = cutproductname ( $Product ['p_name'] ) . $this->catechild ( $Category );
				foreach ( $Product ['Tags'] as $key => $value ) {
					$keywords .= $value . ",";
				}
				$description = strip_tags ( str_replace ( "&nbsp;", " ", msubstr ( trim ( $Product ['p_intro'] ), 0, 80 ) ) );
				$description = preg_replace ( "/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $description );
				
				$this->assign ( 'showtitle', $showtitle );
				$this->assign ( 'keywords', $keywords );
				$this->assign ( 'description', $description );
				// miaomin added
				// $this->assign('WEB3D_ENABLED',C('WEB3D_ENABLED'));
				
				if (isset ( $_GET ['show'] )) {
					switch ($_GET ['show']) {
						case 'vr' :
							$this->assign ( 'show', 1 );
							break;
						case 'ar' :
							$this->assign ( 'show', 2 );
							break;
					}
				} else {
					$this->assign ( 'show', 0 );
				}
				// 刷新浏览数
				$PM = new ProductModel ();
				$pmRes = $PM->find ( $Product [$PM->F->ID] );
				if ($pmRes) {
					$PM->{$PM->F->Views} += 1;
					$PM->{$PM->F->Views_disp} += 1;
					$PM->save ();
				}
				$this->_renderPage ();
			}
		} catch ( Exception $e ) {
			// echo $e->getMessage ();
		}
	}
	public function catechild($Ca) {
		// $res="3D城".$Ca['pc_name'];
		if (is_array ( $Ca ['Child'] )) {
			$res .= "-" . $Ca ['Child'] ['pc_name'];
			if (is_array ( $Ca ['Child'] ['Child'] )) {
				$res .= "-" . $Ca ['Child'] ['Child'] ['pc_name'];
			}
		}
		$res = $res . "-" . $Ca ['pc_name'] . "-3D城";
		return $res;
	}
	
	/**
	 * 首页
	 */
	public function index() {
		$this->assign ( "currenturl", pub_encode_pass ( urlencode ( get_url () ), "3dcity", "encode" ) ); // 赋值当前url到模板
		try {
			$CFG_FORMAT_NUM = 2; // 显示所有格式的条目数
			$CFG_CATE_NUM = 10; // 显示所有分类的条目数
			                    // 新建一个搜索类
			load ( "@.SearchParser" );
			$SP = new SearchParser ();
			// 捕获提交来的参数
			if ($this->isPost ()) {
				// dump ( $this->_post () );
				// die();
				if ($this->_post ( 'format_clear' ) == 1) {
					unset ( $_POST ['format'] );
					unset ( $_POST ['format_clear'] );
				}
				if ($this->_post ( 'allmodels' ) == 1) {
					unset ( $_POST ['isanimation'] );
					unset ( $_POST ['istexture'] );
					unset ( $_POST ['isar'] );
					unset ( $_POST ['isprint'] );
					unset ( $_POST ['allmodels'] );
				}
				if ($SP->parseSearchInfo ( true )) {
					// 获得url转向（格式为 参数/参数值）
					$Url = $SP->getFormattedUrl_new ();
					// var_dump($Url);
					$RedirectUrl = __APP__ . '/models-index-' . substr ( $Url ['url'], 1 );
					// echo $RedirectUrl;
					// exit;
					redirect ( $RedirectUrl );
				}
			} else {
				
				if ($SP->parseUrlInfo ( true )) {
					// var_dump($SP->SearchInfo);
					// exit;
					@load ( '@.Paging' );
					$PSM = new ProductSearchModel ( $SP->SearchInfo, 'model', true );
					$PSM->IsOrderByScore = true;
					if ($SP->SearchInfo ['isprint']) {
						$CateGroupRes = $PSM->getCateGroupRes_print ();
					} else {
						$CateGroupRes = $PSM->getCateGroupRes ();
					}
					// var_dump($CateGroupRes);
					// exit;
					$this->assign ( 'CateGroupResult', $CateGroupRes );
					
					$this->assign ( 'SearchResult', $PSM->getResult ( $SP->SearchInfo ['page'] ) );
					// exit;
					$this->assign ( 'search_key_fmt', trim ( $this->_get ( 'tags' ) ) );
					$this->assign ( 'DBF_P', $this->DBF->Product );
					$this->assign ( 'DBF_PCT', $this->DBF->ProductCreateTool );
					$this->assign ( 'DBF_PF', $this->DBF->ProductFile );
					$this->assign ( 'PI', getPagingInfo ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SP->SearchInfo ['count'], 4 ) );
					$Url = $SP->getFormattedUrl_new ();
					$BaseUrl = __APP__ . '/models-index-' . substr ( $Url ['url'], 1 );
					// echo $BaseUrl;
					// exit;
					// var_dump( $PSM->TotalCount);
					
					$this->assign ( 'BaseUrl', $BaseUrl );
					$paging = getPagingInfo2 ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SP->SearchInfo ['count'], 4, $BaseUrl );
					$this->assign ( 'Paging', $paging );
				}
			}
			$SearchInfo = $SP->SearchInfo;
			if ($SearchInfo ['noprint']) {
				$sprint = "np";
			} elseif ($SearchInfo ['isprint']) {
				$sprint = "pr";
			} else {
				$sprint = "";
			}
			$this->assign ( 'sprint', $sprint ); // 用于区分CG模型和3D打印模型时的搜索
			
			if ($SearchInfo ['category']) {
				$CM = new CatesModel ();
				$SearchCate = $CM->getCategoryByCID ( $SearchInfo ['category'] );
				if ($SearchCate) {
					$this->assign ( 'SearchCate', $SearchCate ['pc_name'] );
				}
			}
			
			// 显示样式
			
			$disp_url = array ();
			
			$disp_url ['1'] = trim_slash_url ( preg_replace ( '|-disp-(\d*)|', '', __SELF__ ) ) . '-disp-1';
			$disp_url ['2'] = trim_slash_url ( preg_replace ( '|-disp-(\d*)|', '', __SELF__ ) ) . '-disp-2';
			$dispOpt = get_search_opt ( 'disp' );
			foreach ( $dispOpt as $key => $val ) {
				if ($val ['opt'] == $SearchInfo ['disp']) {
					$dispOpt [$key] ['url'] = $disp_url [$val ['opt']];
				} else {
					$dispOpt [$key] ['url'] = $disp_url [$val ['opt']];
				}
			}
			$this->assign ( 'dispopt', $dispOpt );
			
			// 文件格式
			$fileOption = $PSM->getFileOptionList ();
			$test = APP_DEBUG * false;
			foreach ( $fileOption as $key => $val ) {
				if ($SearchInfo ['format'] == $val ['pf_createtool']) {
					$fo_url = trim_slash_url ( preg_replace ( '|-format-(\d*)|', '', __SELF__ ) );
					$fileOption [$key] ['del'] = 1;
				} else {
					$fo_url = trim_slash_url ( preg_replace ( '|-format-(\d*)|', '', __SELF__ ) ) . '-format-' . $val ['pf_createtool'];
					$fileOption [$key] ['del'] = 0;
				}
				$fo_url = process_filter_page_url ( $fo_url );
				$fileOption [$key] ['url'] = process_filter_slash_url ( $fo_url );
			}
			// 更多格式
			if (count ( $fileOption ) <= $CFG_FORMAT_NUM) {
				$moreOption = '';
			} else {
				$moreOption = '<li class="linkitem" onclick="initfrm(\'moreformat\')"><img src="__PUBLIC__/images/comm/cog_2_002.png" />更多模型格式 &raquo;</li>';
			}
			$this->assign ( 'fileopt', $fileOption );
			$this->assign ( 'moreopt', $moreOption );
			// 更多分类
			// dump(count ( $CateGroupRes ));
			if ((count ( $CateGroupRes ) <= $CFG_CATE_NUM) && (! $SP->SearchInfo ['category'])) {
				$moreCate = '';
			} else {
				$moreCate = '<li><a class="morecate"></a></li>';
			}
			$this->assign ( 'morecate', $moreCate );
			
			$current_url = str_replace ( ".html", "", __SELF__ );
			// var_dump($current_url);
			// 分类筛选,去除分页参数
			$cate_url = trim_slash_url ( preg_replace ( '|-cate-(\d*)|', '', $current_url ) );
			$cate_url = process_filter_page_url ( $cate_url );
			$cate_url_rtn = process_filter_slash_url ( $cate_url );
			$this->assign ( 'cate_url', $cate_url );
			$this->assign ( 'cate_url_rtn', $cate_url_rtn );
			
			// 搜索工具筛选
			preg_match ( '|filter-([a-z]+)\-|', $current_url, $matches );
			$SearchInfo_url = process_filter_slash_url ( process_filter_page_url ( $current_url ) );
			$filter_url = array ();
			if (count ( $matches ) > 0) {
				if (strlen ( $matches [1] ) == 2) {
					$filter_url ['te_cancel'] = trim_slash_url ( preg_replace ( '|filter-([a-z]+)\-|', '', $SearchInfo_url ) );
					$filter_url ['ma_cancel'] = trim_slash_url ( preg_replace ( '|filter-([a-z]+)\-|', '', $SearchInfo_url ) );
					$filter_url ['ri_cancel'] = trim_slash_url ( preg_replace ( '|filter-([a-z]+)\-|', '', $SearchInfo_url ) );
					$filter_url ['an_cancel'] = trim_slash_url ( preg_replace ( '|filter-([a-z]+)\-|', '', $SearchInfo_url ) );
					$filter_url ['uv_cancel'] = trim_slash_url ( preg_replace ( '|filter-([a-z]+)\-|', '', $SearchInfo_url ) );
					$filter_url ['re_cancel'] = trim_slash_url ( preg_replace ( '|filter-([a-z]+)\-|', '', $SearchInfo_url ) );
					$filter_url ['vr_cancel'] = trim_slash_url ( preg_replace ( '|filter-([a-z]+)\-|', '', $SearchInfo_url ) );
					$filter_url ['ar_cancel'] = trim_slash_url ( preg_replace ( '|filter-([a-z]+)\-|', '', $SearchInfo_url ) );
				} else {
					$filter_url ['te_cancel'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|te|', '', $matches [1] ) . '-', $SearchInfo_url );
					$filter_url ['ma_cancel'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|ma|', '', $matches [1] ) . '-', $SearchInfo_url );
					$filter_url ['ri_cancel'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|ri|', '', $matches [1] ) . '-', $SearchInfo_url );
					$filter_url ['an_cancel'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|an|', '', $matches [1] ) . '-', $SearchInfo_url );
					$filter_url ['uv_cancel'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|uv|', '', $matches [1] ) . '-', $SearchInfo_url );
					$filter_url ['re_cancel'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|re|', '', $matches [1] ) . '-', $SearchInfo_url );
					$filter_url ['vr_cancel'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|vr|', '', $matches [1] ) . '-', $SearchInfo_url );
					$filter_url ['ar_cancel'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|ar|', '', $matches [1] ) . '-', $SearchInfo_url );
				}
				$filter_url ['te_add'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|te|', '', $matches [1] ) . 'te-', $SearchInfo_url );
				$filter_url ['ma_add'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|ma|', '', $matches [1] ) . 'ma-', $SearchInfo_url );
				$filter_url ['ri_add'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|ri|', '', $matches [1] ) . 'ri-', $SearchInfo_url );
				$filter_url ['an_add'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|an|', '', $matches [1] ) . 'an-', $SearchInfo_url );
				$filter_url ['uv_add'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|uv|', '', $matches [1] ) . 'uv-', $SearchInfo_url );
				$filter_url ['re_add'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|re|', '', $matches [1] ) . 're-', $SearchInfo_url );
				$filter_url ['vr_add'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|vr|', '', $matches [1] ) . 'vr-', $SearchInfo_url );
				$filter_url ['ar_add'] = preg_replace ( '|filter-([a-z]+)\-|', 'filter-' . preg_replace ( '|ar|', '', $matches [1] ) . 'ar-', $SearchInfo_url );
			} else {
				$filter_url ['te_add'] = $SearchInfo_url . '-filter-te-';
				$filter_url ['ma_add'] = $SearchInfo_url . '-filter-ma-';
				$filter_url ['ri_add'] = $SearchInfo_url . '-filter-ri-';
				$filter_url ['an_add'] = $SearchInfo_url . '-filter-an-';
				$filter_url ['uv_add'] = $SearchInfo_url . '-filter-uv-';
				$filter_url ['re_add'] = $SearchInfo_url . '-filter-re-';
				$filter_url ['vr_add'] = $SearchInfo_url . '-filter-vr-';
				$filter_url ['ar_add'] = $SearchInfo_url . '-filter-ar-';
			}
			$this->assign ( filter_url, $filter_url );
			
			// 清除所有筛选条件
			$clear_all_url = preg_replace ( '|-filter-(.*)-|', '', $current_url );
			$clear_all_url = preg_replace ( '|-format-(.*)|', '', $clear_all_url );
			$clear_all_url = process_filter_page_url ( $clear_all_url );
			$this->assign ( clear_all_url, $clear_all_url );
			
			// 缩略图
			$thumb_url = array ();
			$thumb_url ['1'] = trim_slash_url ( preg_replace ( '|-thumb-(\d*)|', '', $current_url ) ) . '-thumb-1';
			$thumb_url ['2'] = trim_slash_url ( preg_replace ( '|-thumb-(\d*)|', '', $current_url ) ) . '-thumb-2';
			$thumbOpt = get_search_opt ( 'thumb' );
			foreach ( $thumbOpt as $key => $val ) {
				if ($val ['opt'] == $SearchInfo ['thumb']) {
					$nowThumb = $val ['name'];
					$thumbOpt [$key] ['del'] = 1;
					$thumbOpt [$key] ['url'] = $thumb_url [$val ['opt']];
				} else {
					$thumbOpt [$key] ['del'] = 0;
					$thumbOpt [$key] ['url'] = $thumb_url [$val ['opt']];
				}
			}
			
			$this->assign ( 'nowThumb', $nowThumb );
			$this->assign ( 'thumbopt', $thumbOpt );
			
			// 缩略图(新版)
			$thumb_opt_arr = C ( 'SEARCH.RES_THUMB_OPTION2' );
			$thumb_opt_str = get_dropdown_option ( $thumb_opt_arr, $SearchInfo ['thumb'] );
			$this->assign ( 'thumb_opt', $thumb_opt_str );
			
			// 每页显示
			$paging_url = array ();
			$paging_url ['20'] = trim_slash_url ( preg_replace ( '|-count-(\d*)|', '', $current_url ) ) . '-count-20';
			$paging_url ['35'] = trim_slash_url ( preg_replace ( '|-count-(\d*)|', '', $current_url ) ) . '-count-35';
			$paging_url ['45'] = trim_slash_url ( preg_replace ( '|-count-(\d*)|', '', $current_url ) ) . '-count-45';
			$paging_url ['60'] = trim_slash_url ( preg_replace ( '|-count-(\d*)|', '', $current_url ) ) . '-count-60';
			$paging_url ['75'] = trim_slash_url ( preg_replace ( '|-count-(\d*)|', '', $current_url ) ) . '-count-75';
			
			$paging_url ['20'] = trim_slash_url ( preg_replace ( '|-page-(\d*)|', '', $paging_url ['20'] ) );
			$paging_url ['35'] = trim_slash_url ( preg_replace ( '|-page-(\d*)|', '', $paging_url ['35'] ) );
			$paging_url ['45'] = trim_slash_url ( preg_replace ( '|-page-(\d*)|', '', $paging_url ['45'] ) );
			$paging_url ['60'] = trim_slash_url ( preg_replace ( '|-page-(\d*)|', '', $paging_url ['60'] ) );
			$paging_url ['75'] = trim_slash_url ( preg_replace ( '|-page-(\d*)|', '', $paging_url ['75'] ) );
			
			$pageOpt = get_search_opt ( 'count' );
			foreach ( $pageOpt as $key => $val ) {
				if ($val ['opt'] == $SearchInfo ['count']) {
					$nowPage = $val ['name'];
					$pageOpt [$key] ['del'] = 1;
					$pageOpt [$key] ['url'] = $paging_url [$val ['opt']];
				} else {
					$pageOpt [$key] ['del'] = 0;
					$pageOpt [$key] ['url'] = $paging_url [$val ['opt']];
				}
			}
			$this->assign ( 'nowPage', $nowPage );
			$this->assign ( 'pageopt', $pageOpt );
			
			// 每页显示(新版)
			$count_opt_arr = C ( 'SEARCH.RES_COUNT_OPTION2' );
			/*
			 * if ($SearchInfo ['thumb'] !== 1) { // 大图模式 $count_opt_arr = C (
			 * 'SEARCH.RES_COUNT_OPTION3' ); } else { $count_opt_arr = C (
			 * 'SEARCH.RES_COUNT_OPTION2' ); }
			 */
			
			$count_opt_str = get_dropdown_option ( $count_opt_arr, $SearchInfo ['count'] );
			$this->assign ( 'count_opt', $count_opt_str );
			
			// 排序方式
			$order_url = process_filter_page_url ( $current_url );
			$ording_url = array ();
			$ording_url ['createdate_desc'] = trim_slash_url ( preg_replace ( '|-order-[a-z]{3}|', '', $order_url ) ) . '-order-crd';
			$ording_url ['view_desc'] = trim_slash_url ( preg_replace ( '|-order-[a-z]{3}|', '', $order_url ) ) . '-order-vid';
			$ording_url ['downloads_desc'] = trim_slash_url ( preg_replace ( '|-order-[a-z]{3}|', '', $order_url ) ) . '-order-dod';
			$orderOpt = get_search_opt ( 'order' );
			foreach ( $orderOpt as $key => $val ) {
				if ($val ['opt'] == $SearchInfo ['order']) {
					$nowOrder = $val ['name'];
					$orderOpt [$key] ['del'] = 1;
					$orderOpt [$key] ['url'] = $ording_url [$val ['opt']];
				} else {
					$orderOpt [$key] ['del'] = 0;
					$orderOpt [$key] ['url'] = $ording_url [$val ['opt']];
				}
			}
			$this->assign ( 'nowOrder', $nowOrder );
			$this->assign ( 'orderopt', $orderOpt );
			
			// 排序方式(新版)
			if ($SearchInfo ['isfree']) {
				$order_opt_arr = C ( 'SEARCH.RES_ORDER_OPTION2' );
			} else {
				$order_opt_arr = C ( 'SEARCH.RES_ORDER_OPTION3' );
			}
			
			if ($SearchInfo ['isfree']) 			// 带参数，如果是免费fr赋值
			{
				$addSearchInfo = "fr";
			} else {
				$addSearchInfo = "or";
			}
			
			$order_opt_str = get_dropdown_option ( $order_opt_arr, $SearchInfo ['order'] );
			$this->assign ( 'order_opt', $order_opt_str );
			
			// 星级筛选(新版)
			$where_star_arr = C ( 'SEARCH.RES_WHERE_STAR' );
			$where_star_str = get_dropdown_option ( $where_star_arr, $SearchInfo ['star'] );
			$this->assign ( 'where_star', $where_star_str );
			
			if ($SP->SearchInfo ['isprint']) {
				$show_alt_text = '3D打印模型 ';
			} else {
				$show_alt_text = 'CG模型 ';
			}
			$sarr = $SP->SearchInfo;
			if ($sarr ['noprint'] == 1) {
				$prtitle = $SearchCate ['pc_name'] . " CG模型 ";
			} elseif ($sarr ['isar'] == 1) {
				$prtitle = $SearchCate ['pc_name'] . " Web3D";
			} else {
				$prtitle = $SearchCate ['pc_name'] . " 3D打印模型 ";
			}
			$this->assign ( 'showtitle', $prtitle . "-3DCity.com" );
			$description = $prtitle . " 3d模型 3dcity.com";
			$this->assign ( 'keywords', $prtitle . " 3d模型 " );
			$this->assign ( 'description', $description );
			
			$this->assign ( 'show_alt_text', $show_alt_text );
			$this->assign ( 'SearchInfo', $SP->SearchInfo );
			$this->assign ( 'addSearchInfo', $addSearchInfo );
			$this->assign ( 'HtmlCtrl', $SP->getHtmlCtrls () );
			$this->assign ( 'FormAction', U ( 'Index/index/search' ) );
			$this->assign ( 'Show_format_num', $CFG_FORMAT_NUM );
			$this->assign ( 'Show_cate_num', $CFG_CATE_NUM );
			// $this->display ();
			$this->_renderPage ();
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	private function verificationRequest($RequestVM, $AutoJump = true) {
		$PID = $this->getPID ( $RequestVM );
		if ($PID) {
			$PM = new ProductModel ();
			$Product = $PM->getProductInfoByID ( $PID );
			if (! $Product) {
				return $AutoJump ? $this->error ( L ( 'ITEM_NOT_EXIST' ), U ( 'Front/index' ) ) : false;
			}
			// miaomin added @2014.3.26
			// 如果模型被标志为删除状态，则只有购买者才能访问。
			if ($Product ['p_slabel'] == 2) {
				$UOPM = new UserOwnProductModel ();
				$IsPurchased = $UOPM->IsUserBuyProduct ( $_SESSION ['f_userid'], $Product [$this->DBF->Product->ID] );
				if (! $IsPurchased) {
					return $AutoJump ? $this->error ( L ( 'ITEM_NOT_EXIST' ), U ( 'Front/index' ) ) : false;
				}
			}
			return $Product;
		} else {
			header ( "HTTP/1.0 404 Not Found" );
			$this->display ( 'Public:404' );
			// return $AutoJump ? $this->error ( L ( 'POST_VALUE_ERROR' ), U (
			// 'Front/index' ) ) : false;
		}
	}
	private function getPID($CVM) {
		/*
		 * $pvc = new PageValueCollector($CVM); $pvc-> Selector = array( array(
		 * $pvc::$INIT_Key => 'id', $pvc::$INIT_ValidationMode =>
		 * $pvc::$VM_DataType, $pvc::$INIT_DataType => $pvc::$DT_Int,
		 * $pvc::$INIT_EmptyValidate => $pvc::$EV_NotNull,
		 * $pvc::$INIT_ErrorMessage => '')); $pvc->validationData();
		 */
		$pvc = new PVC2 ( $CVM );
		$pvc->isInt ()->validateMust ()->add ( 'id' );
		$pvc->verifyAll ();
		
		if (! count ( $pvc->Error )) {
			return $pvc->ResultArray ['id'];
		} else {
			return null;
		}
	}
	private function formatTags($Product) {
		$Tags = $Product [$this->DBF->Product->Tags];
		$Tags = split ( ' ', $Tags );
		return $Tags;
	}
}
?>