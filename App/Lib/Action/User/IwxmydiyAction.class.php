<?php
/**
 * 第三版手机版我的作品模块
 * Created by PhpStorm.
 * User: guolixun
 * Date: 2016/10/13
 * Time: 17:03
 */
class IwxmydiyAction extends CommonAction {
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
     * 删除diy件
     * @access public
     * @return mixed
     */
    public function remove() {
        try {
            if ($this->isGet ()) {
                $pid = $this->_get ( 'pid' );
                dump($pid);

                // 如果没有diy的id，就别再操作了
                $UD = D ( 'user_diy' );
                $condition ['id'] = $pid;
                if (! $UD->where ( $condition )->find ()) {
                    throw new Exception ( 'no_diy_id' );
                }
                // 标记为删除 tdf_user_diy
                if (! $UD->where ( $condition )->setField('delsign',1)) {
                    throw new Exception ( 'remove_fail' );
                }
                redirect ( $this->_server ( 'HTTP_REFERER' ) );
            }
        } catch ( Exception $e ) {
            $this->error ( L ( $e->getMessage () ), WEBROOT_PATH . '/user.php/mydiy/jewelrylist.html' );
        }
    }

    /**
     * 我的DIY
     * @access public
     * @return mixed
     */
    public function jewelrylist(){	//diy首饰
        if (!$this->_isLogin()){
            $this->_needLogin();
            exit;
        }
        $u_id=$_SESSION ['f_userid'];

        /*################新增分页####################Start*/
        $currPage = I('page',1,'intval');
        $pageSize = '20';
        $totalPageSql = "select * from tdf_user_diy as TUD where TUD.delsign=0 and TUD.u_id=".$u_id;
        $total = M('user_diy')->query($totalPageSql);
        $count = count($total);
        $totalPage = ceil($count/$pageSize);
        $start = ($currPage-1)*$pageSize;
        $this->assign('currPage',$currPage);
        $this->assign('totalPage',$totalPage);
        /*################新增分页####################End*/

        $sql="select TUD.id,TUD.title,TUD.ctime,TU.u_email,TUD.diy_unit_info,TUD.cover,TUD.price,TUD.cid,TDC.cate_name from tdf_user_diy as TUD ";
        $sql.="Left Join tdf_users as TU On TU.u_id=TUD.u_id ";
        $sql.="Left Join tdf_diy_cate as TDC On TDC.cid=TUD.cid ";
        $sql.="where TUD.delsign=0 and TUD.u_id=".$u_id;
        $sql.=" limit ".$start.",".$pageSize;//." order by TUD.ctime desc"
        $udlist=M('user_diy')->query($sql);
        $DUM=new DiyUnitModel();
        foreach($udlist as $key=>$value){
            //$DU=$this->getDiyUnitInfo($value['cid']);
            //var_dump($udlist);
            $unit_info=unserialize($value['diy_unit_info']);
            //var_dump($value['id']);
            $udlist[$key]['text']=$unit_info[7];
            $udlist[$key]['diyInfo']=$DUM->getUnitByUserDiy($value['cid'],$unit_info);
        }
        //var_dump($udlist);
        $this->assign("udlist",$udlist);

        //---------------------------------------
        // 我的关注数和我的粉丝数
        $UR = new UserRelationModel ();
        $urRes = $UR->getList ( $this->_loginUserObj->{$this->_loginUserObj->F->ID} );
        if ($urRes) {
            $urListArr = unserialize ( $urRes [$UR->F->CountList] );
        } else {
            $urListArr = $UR->getListCountStorage ()->getListCount ();
        }

        $this->assign ( 'urList', $urListArr );

        // 我发布的作品
        // @load ( '@.Paging' );
        @load ( '@.SearchParser' );
        $SP = new SearchParser ();
        $SP->parseUrlInfo ( true );
        $SearchInfo = $SP->SearchInfo;
        $SearchInfo ['category'] = 1;
        $SearchInfo ['page'] = $this->_get ( 'page' );
        $SearchInfo ['count'] = 16;
        $SearchInfo ['creater'] = $this->_session ( 'f_userid' );
        $PSM = new ProductSearchModel ( $SearchInfo, 'model', false );
        $this->assign ( 'SearchResult', $PSM->getResult ( $SP->SearchInfo ['page'] ) );
        $this->assign ( 'SearchResultCount', $PSM->TotalCount );

        // 我的工作经历和教育经历
        $UED = new UserEducationModel ();
        $UWE = new UserWorkModel ();
        $workexpList = $UWE->getUserWork ( $this->_loginUserObj->{$this->_loginUserObj->F->ID} );
        $eduexpList = $UED->getUserEdu ( $this->_loginUserObj->{$this->_loginUserObj->F->ID} );
        $this->assign ( 'DBF_UWE', $UWE->F );
        $this->assign ( 'DBF_UED', $UED->F );
        $this->assign ( 'workexpList', $workexpList );
        $this->assign ( 'eduexpList', $eduexpList );
        $this->assign('showtitle',"用户中心-3D城");

        $Users = new UsersModel ();
        $Users->find ( $this->_session ( 'f_userid' ) );
        $UP = $Users->getUserProfile ();
        $UA = $Users->getUserAcc ();
        // 用户信息
        $updata = $Users->data ();
        if ($UP->u_domain) {
            $userdomain = $UP->u_domain;
        }else{
            $userdomain = "u" . $updata ['u_id'];
        }
        $this->assign ( 'userdomain', $userdomain);
        $this->assign ( 'userBasic', $Users->data () );
        $this->assign ( 'userPro', $UP->data() );
        $this->assign ( 'userProf', explode ( '#', $UP->u_prof ) );
        //---------------------------------------
        //var_dump($udlist);
        $this->_renderPage();
    }
}
?>