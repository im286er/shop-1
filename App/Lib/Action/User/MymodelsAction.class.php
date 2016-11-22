<?php
class MymodelsAction extends CommonAction {
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
		
		//load ( "@.DBF" );
		$this->DBF = new DBF ();
	}
	
	/**
	 * 我的发布
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
			// 我购买的作品
			$SP = new SearchParser ();
			$SP->parseUrlInfo ( true );
			$SearchInfo = $SP->SearchInfo;
			$SearchInfo ['category'] = 1;
			$SearchInfo ['page'] = $this->_get ( 'page' );
			$SearchInfo ['count'] = 16;
			$SearchInfo ['owner'] = $this->_session ( 'f_userid' );
			$PSM = new ProductSearchModel ( $SearchInfo, 'model', false );
			$this->assign ( 'SearchResult', $PSM->getResult ( $SP->SearchInfo ['page'] ) );
			$this->assign ( 'DBF_P', $this->DBF->Product );
//var_dump($PSM->getLastSql());
//var_dump($PSM->TotalCount);

			$this->assign('showtitle',"我的购买-3D城");
			$this->assign ( 'SearchResultCount', $PSM->TotalCount );
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
			// 分页
			$this->assign ( 'PI', getPagingInfo ( $PSM->TotalCount, $SP->SearchInfo ['page'], $SearchInfo ['count'], 4 ) );
			$Url = $SP->getFormattedUrl ();
			$BaseUrl = C ( 'REDIRECT_PRE_URL' ) . U ( 'mymodels/index?' . substr ( $Url ['url'], 1 ) );
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