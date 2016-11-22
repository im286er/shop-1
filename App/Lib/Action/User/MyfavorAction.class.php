<?php
/**
 * 我的收藏类
 *
 * @author miaomin 
 * Jul 17, 2013 1:17:32 PM
 */
class MyfavorAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		//
		parent::__construct ();
		
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
		
		// load ( "@.DBF" );
		$this->DBF = new DBF ();
	}
	
	/**
	 * 移除收藏
	 *
	 * @access public
	 * @return mixed
	 */
	public function remove() {
		try {
			if ($this->isGet ()) {
				$pid = $this->_get ( 'id' );
				$Product = D ( 'Product' );
				if (! $Product->find ( $pid )) {
					$this->error ( L ( 'no_pid' ), WEBROOT_PATH . '/index.php' );
				}
				
				// 如果没收藏，就别再操作了
				$UF = D ( 'UserFavor' );
				$condition ['u_id'] = $this->_session ( 'f_userid' );
				$condition ['uf_id'] = $Product->p_id;
				$condition ['uf_type'] = $Product->p_producttype;
				if (! $UF->where ( $condition )->find ()) {
					throw new Exception ( 'no_favor' );
				}
				
				// 删表
				if (! $UF->where ( $condition )->delete ()) {
					throw new Exception ( 'remove_fail' );
				}
				
				// 收藏次数-1
				$Product->p_favors -= 1;
				($Product->p_favors < 0) ? $Product->p_favors = 0 : $Product->p_favors;
				$Product->save ();
				
				// print_r ( $_SERVER );
				// redirect ( WEBROOT_PATH . '/index.php/models/detail/id/' .
				// $pid );
				redirect ( $this->_server ( 'HTTP_REFERER' ) );
			}
		} catch ( Exception $e ) {
			
			$this->error ( L ( $e->getMessage () ), WEBROOT_PATH . '/index/models-detail-id-' . $pid );
			// echo $e->getMessage ();
		}
	}
	
	/**
	 * 添加收藏
	 *
	 * @access public
	 * @return mixed
	 */
	public function add() {
		try {
			if ($this->isGet ()) {
				$pid = $this->_get ( 'id' );
				$Product = D ( 'Product' );
				if (! $Product->find ( $pid )) {
					$this->error ( L ( 'no_pid' ), WEBROOT_PATH . '/index.php' );
				}
				
				// 如果收藏过了，就别再操作了
				$UF = D ( 'UserFavor' );
				$condition ['u_id'] = $this->_session ( 'f_userid' );
				$condition ['uf_id'] = $Product->p_id;
				$condition ['uf_type'] = $Product->p_producttype;
				if ($UF->where ( $condition )->find ()) {
					throw new Exception ( 'have_favor' );
				}
				
				// 计表
				$UF->create ();
				$UF->u_id = $this->_session ( 'f_userid' );
				$UF->uf_id = $Product->p_id;
				$UF->uf_type = $Product->p_producttype;
				$UF->uf_createdate = get_now ();
				if (! $UF->add ()) {
					throw new Exception ( 'add_fail' );
				}
				
				// 收藏次数+1
				$Product->p_favors += 1;
				if (! $Product->save ()) {
					throw new Exception ( 'favors_fail' );
				}
				
				redirect ( WEBROOT_PATH . '/index/models-detail-id-' . $pid );
			}
		} catch ( Exception $e ) {
			
			$this->error ( L ( $e->getMessage () ), WEBROOT_PATH . '/index.php/models-detail-id-' . $pid );
			// echo $e->getMessage ();
		}
	}
	
	/**
	 * 我的收藏
	 *
	 * @access public
	 * @return mixed
	 */
	public function index() {
		try {
			// 渲染页面
			$Users = new UsersModel ();
			$Users->find ( $this->_session ( 'f_userid' ) );
			$UP = $Users->getUserProfile ();
			$UA = $Users->getUserAcc ();
			// var_dump($UA);
			$birth_year_combo = get_dropdown_option ( genBirthYear (), $UP->u_bir_y );
			$birth_month_combo = get_dropdown_option ( genBirthMonth (), $UP->u_bir_m );
			$birth_day_combo = get_dropdown_option ( genBirthDay (), $UP->u_bir_d );
			// 处理省市联动
			$province_combo = get_dropdown_option ( genCHNProvince (), $UP->u_province_fid );
			$city_combo = get_dropdown_option ( genCHNCity ( $UP->u_province ), $UP->u_city_no );
			// 我的关注数和我的粉丝数
			$UR = new UserRelationModel ();
			$urRes = $UR->getList ( $this->_loginUserObj->{$this->_loginUserObj->F->ID} );
			if ($urRes) {
				$urListArr = unserialize ( $urRes [$UR->F->CountList] );
			} else {
				$urListArr = $UR->getListCountStorage ()->getListCount ();
			}
			$this->assign ( 'urList', $urListArr );
			// 我的工作经历和教育经历
			$UED = new UserEducationModel ();
			$UWE = new UserWorkModel ();
			$workexpList = $UWE->getUserWork ( $this->_loginUserObj->{$this->_loginUserObj->F->ID} );
			$eduexpList = $UED->getUserEdu ( $this->_loginUserObj->{$this->_loginUserObj->F->ID} );
			$this->assign ( 'DBF_UWE', $UWE->F );
			$this->assign ( 'DBF_UED', $UED->F );
			$this->assign ( 'workexpList', $workexpList );
			$this->assign ( 'eduexpList', $eduexpList );
			// 我的作品数
			@load ( '@.Paging' );
			@load ( '@.SearchParser' );
			$myWorksNum = $Users->getUserWorksNum($this->_session ( 'f_userid' ));
			$this->assign ( 'MyModelCount', $myWorksNum );
			// 我收藏的作品
			$SP = new SearchParser ();
			$SP->parseUrlInfo ( true );
			$SearchInfo = $SP->SearchInfo;
			$SearchInfo ['category'] = 1;
			$SearchInfo ['page'] = $this->_get ( 'page' );
			$SearchInfo ['count'] = 16;
			$SearchInfo ['favor'] = $this->_session ( 'f_userid' );
			$PSM = new ProductSearchModel ( $SearchInfo, 'model', false );
			$this->assign ( 'SearchResult', $PSM->getResult ( $SP->SearchInfo ['page'] ) );
			$this->assign ( 'DBF_P', $this->DBF->Product );
			$this->assign ( 'SearchResultCount', $PSM->TotalCount );
			// 用户信息
			$this->assign ( 'userBasic', $Users->data () );
			$this->assign ( 'userPro', $UP->data () );
			$this->assign ( 'userProf', explode ( '#', $UP->u_prof ) );
			$this->assign ( 'userAcc', $UA->u_vcoin_av );
			$this->assign ( 'userAcc_rmb', $UA->u_rcoin_av );
			$this->assign ( 'yearCombo', $birth_year_combo );
			$this->assign ( 'monthCombo', $birth_month_combo );
			$this->assign ( 'dayCombo', $birth_day_combo );
			$this->assign ( 'provinceCombo', $province_combo );
			$this->assign ( 'cityCombo', $city_combo );
			$this->assign('showtitle',"我的收藏-3D城"); 
				
			// 分页
			$this->assign ( 'PI', getPagingInfo ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SearchInfo ['count'], 4 ) );
			$Url = $SP->getFormattedUrl ();
			$BaseUrl = C ( 'REDIRECT_PRE_URL' ) . U ( 'myfavor/index?' . substr ( $Url ['url'], 1 ) );
			$this->assign ( 'BaseUrl', $BaseUrl );
			$paging = getPagingInfo2_yuan ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SearchInfo ['count'], 4, $BaseUrl );
			$this->assign ( 'Paging', $paging );
			$this->_renderPage ();
		} catch ( Exception $e ) {
			//
			// echo $e->getMessage ();
		}
	}
}
?>