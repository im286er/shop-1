<?php
/**
 * 第三版手机端用户类
 * Created by PhpStorm.
 * User: guolixun
 * Date: 2016/10/13
 * Time: 10:38
 */


class IwxuserconfAction extends CommonAction
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 用户收货地址管理
     *
     * @author miaomin@2015.6.3
     *
     */
    public function address()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }

            $UAM = new UserAddressModel();
            // POST数据处理
            $this->assign('mtype','iwx');
            if ($this->isPost()) {
                switch ($_POST['posttype']) {
                    // 添加编辑收货地址
                    case '1':
                        if (! $_POST['id']) {
                            $verifyRes = $UAM->verifyAddAddress($_SESSION['f_userid'], $_POST);
                            if (! $verifyRes) {
                                throw new Exception('您最多保存20个有效的收货地址');
                            }
                        }
                        $res = $UAM->addAddressByUserID($_SESSION['f_userid'], $_POST);
                        if ($_POST['isajax']) {
                            if ($_POST['mmode']) {
                                $chooseAddress = $res;
                                print_r($this->ajaxReturn($chooseAddress, '', true));
                                exit();
                            } else {
                                // 重新获取最新的用户地址信息
                                $UCA = new CartAction();
                                $chooseAddress = $UCA->get_chooseaddress($_SESSION['f_userid'], 0, 0, $_POST['oid']);
                                print_r($this->ajaxReturn($chooseAddress, '', true));
                                exit();
                            }
                        }
                        break;
                    // 删除收货地址
                    case '2':
                        $res = $UAM->removeAddressByUserID($_SESSION['f_userid'], $_POST['opid']);
                        break;
                    // 设置默认收货地址
                    case '3':
                        $res = $UAM->setDefaultAddress($_SESSION['f_userid'], $_POST['addressId']);
                        break;
                    case '4':
                        $AddressList = $UAM->getAddressInfoById($_SESSION['f_userid'], $_POST['addressId']);
                        //dump($AddressList);
                        $AIPM = new AreaInfoPickerModel();
                        $AddressList = $this->setDispArea($AddressList, $AIPM);
                        //print_r($AddressList);
                        if ($AddressList) {
                            print_r($this->ajaxReturn($AddressList, '', true));
                        } else {
                            print_r($this->ajaxReturn('', '', false));
                        }
                        exit();
                        break;
                }
                if (! $res) {
                    throw new Exception('我们检查到您提交的数据有些异常');
                }

                redirect(__SELF__);
            }
            // 新增收货地址地区选择器数据
            // import('ORG.Util.Page'); // 导入分页类
            $AddressCount = $UAM->getAddressCntByUserID($_SESSION['f_userid']);
            // $Page = new Page($AddressCount, 3); // 实例化分页类 传入总记录数和每页显示的记录数
            // $show = $Page->show(); // 分页显示输出
            $AddressList = $UAM->getAddressByUserID($_SESSION['f_userid'],'addresslist');
            $AIPM = new AreaInfoPickerModel();
            $AddressList = $this->setDispArea($AddressList, $AIPM);
            // print_r($AddressList);

            // 筛选默认地址
            $defaultAddress = null;
            foreach ($AddressList as $key => $val) {
                if ($val[$UAM->F->IsDefault] == 1) {
                    $defaultAddress = $val;
                    unset($AddressList[$key]);
                    break;
                }
            }
            if ($defaultAddress) {
                array_unshift($AddressList, $defaultAddress);
                $AddressList = array_values($AddressList);
            }

            // 赋值
            $this->assign('HtmlCtrl', $this->getHtmlCtrl($AIPM));
            $this->assign('AdderssList', $AddressList);
            $this->assign('showtitle', "用户设置-收货地址-3D城");
            $this->assign('curAddressNum', $AddressCount);
            $this->assign('maxAddressNum', UserAddressModel::MAXADDLIMIT);
            // $this->assign('page', $show); // 赋值分页输出
            $this->_renderPage();
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/index');
        }
    }

    /**
     * QQ登录绑定用户
     * 2014-08-28
     */
    public function binduser()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }

            if ($this->isPost()) {
                $PostData = $this->getBindUserPost();

                if (! $PostData) {
                    return $this->error('输入项中存在错误');
                }

                $UM = new UsersModel();
                $UserInfo = $UM->getUserByID($_SESSION['f_userid']);
                if ($UserInfo === false) {
                    return $this->error('连接失败，请稍后再试', U('index'));
                }
                if ($UserInfo === null) {
                    return $this->error('账户异常，请重新登陆', U('loginout'));
                }

                $newUserInfo = $UM->getUserByName($PostData['u_email']);
                $u_pass = $this->getSaltPass($PostData['u_pass'], $newUserInfo['u_salt']);

                // var_dump($u_pass);
                // var_dump($newUserInfo ['u_pass']);
                // exit;
                if ($newUserInfo['u_pass'] != $u_pass) {
                    return $this->error('密码输入错误');
                } else {
                    $UA = new UserAuthModel();
                    $result = $UA->bindbyNewUid($UserInfo['u_id'], $newUserInfo['u_id']);
                    if ($result) {
                        session('f_userid', $newUserInfo['u_id']);
                        session('f_nickname', $newUserInfo['u_dispname']);
                        session('f_logindate', time());
                    }
                }
                // return $this->success('修改成功', U('User/index/changpass'));
            }
            // 以后全部可以用公共类来处理
            // $Users = D ( 'Users' );
            // $Users->find ( $this->_session ( 'f_userid' ) );
            // $UP = $Users->getUserProfile ();
            // $this->assign ( 'userBasic', $Users->data () );
            // $this->assign ( 'userPro', $UP->data () );
            // $this->assign ( 'userProf', explode ( '#', $UP->u_prof ) );
            $this->_renderPage();
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/index');
        }
    }

    /**
     * 修改密码
     *
     * @access public
     * @return null
     */
    public function changepass()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }
            $this->assign('mtype',I('mtype'));
            if ($this->isPost()) {
                $this->assign('mtype',I('mtype'));
                $PostData = $this->getChangPassPost();
                if (! $PostData) {
                    return $this->error('输入项中存在错误');
                }

                $UM = new UsersModel();
                $UserInfo = $UM->getUserByID($_SESSION['f_userid']);

                if ($UserInfo === false) {
                    return $this->error('连接失败，请稍后再试', U('index'));
                }
                if ($UserInfo === null) {
                    return $this->error('账户异常，请重新登陆', U('loginout'));
                }

                $InputSaltPass = $this->getSaltPass($PostData['oldpass'], $UserInfo['u_salt']);
                $NewSaltPass = $this->getSaltPass($PostData['newpass'], $UserInfo['u_salt']);

                if ($UserInfo['u_pass'] != $InputSaltPass) {
                    return $this->error('当前密码输入错误');
                }

                $UM = $this->buildChangPassData($NewSaltPass);
                $UM->startTrans();
                if (! $UM->save()) {
                    $UM->rollback();
                    return $this->error('连接失败，请稍后再试', U('userconf/changepass'));
                }
                $UM->commit();
                return $this->error('修改成功');
                //return $this->success('修改成功', U('User/index/changpass'));
            }

            /*******************新增猜你喜欢************************************************************/
            //--新增参数--start//
            //192.168.52.19上用这个--
            $res = M('help_doc')->where("cate =25")->select();
            $con=explode(' ',$res[0]['intro']);
            //192.168.52.19上用这个

            //www.ignjewelry.com上用这个--
            //$res = M('help_doc')->where("cate =13")->select();
            //$con=explode(' ',$res[0]['content']);
            //www.ignjewelry.com上用这个--

            foreach($con as $k=>$v){
                $info[$k]=D('product')->where("p_id=$v")->select();
            }
            $arr=array_rand($info,4);
            foreach($arr as $k=>$v){
                $info1[$k]=$info[$v];
            }
            $this->assign('info1',$info1);
            /******************新增猜你喜欢**********************************************************/

            // 以后全部可以用公共类来处理
            $Users = D('Users');
            $Users->find($this->_session('f_userid'));
            $UP = $Users->getUserProfile();
            $this->assign('userBasic', $Users->data());
            $this->assign('userPro', $UP->data());
            $this->assign('userProf', explode('#', $UP->u_prof));
            $this->assign('showtitle', "用户设置-修改密码-3D城");
            $this->_renderPage();
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/index');
        }
    }

    /**
     * 保存新密码
     *
     * @param unknown_type $NewSaltPass
     * @return UsersModel
     */
    private function buildChangPassData($NewSaltPass)
    {
        $UM = new UsersModel();
        $UM->u_id = $_SESSION['f_userid'];
        $UM->u_pass = $NewSaltPass;
        $UM->u_lastlogintime = time();
        return $UM;
    }

    /**
     * 获取密码盐值
     *
     * @param unknown_type $Pass
     * @param unknown_type $Salt
     * @return string
     */
    private function getSaltPass($Pass, $Salt)
    {
        return md5($Pass . $Salt);
    }

    /**
     * 密码修改的验证
     *
     * @return boolean multitype:
     */
    private function getChangPassPost()
    {
        $PVC = new PVC2();
        $PVC->setModePost()->setStrictMode(true);
        $PVC->isString()
            ->Length(6, null)
            ->validateMust()
            ->add('oldpass');
        $PVC->isString()
            ->Length(6, null)
            ->validateMust()
            ->add('newpass');
        $PVC->isString()
            ->Confirm('newpass')
            ->validateMust()
            ->add('confirmpass');
        if (! $PVC->verifyAll()) {
            return false;
        }
        return $PVC->ResultArray;
    }

    /**
     * QQ登录绑定用户的验证
     */
    private function getBindUserPost()
    {
        $PVC = new PVC2();
        $PVC->setModePost()->setStrictMode(true);
        $PVC->isString()
            ->Length(6, null)
            ->validateMust()
            ->add('u_email');
        $PVC->isString()
            ->Length(6, null)
            ->validateMust()
            ->add('u_pass');
        if (! $PVC->verifyAll()) {
            return false;
        }
        return $PVC->ResultArray;
    }

    /**
     * 用户首页
     *
     * @access public
     * @return null
     */
    public function index()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }
            $this->assign('mtype','iwx');
            if ($this->isPost()) {
                // 数据验证
                $this->_validProfile($this->_post());

                $Users = D('Users');
                $Users->find($this->_session('f_userid'));

                $UP = $Users->getUserProfile();

                $UP->u_bir_y = $this->_post('year');
                $UP->u_bir_m = $this->_post('month');
                $UP->u_bir_d = $this->_post('day');
                $UP->u_sig = $this->_post('sig');
                $province_id = $this->_post('province') + 1;
                $Province = D('Province');
                $Province->find($province_id);
                $City = D('City');
                $City->where('pi_id=' . $province_id . ' AND pi_no=' . $this->_post('city'))
                    ->find();
                $UP->u_province = $Province->pi_id;
                $UP->u_province_name = $Province->pi_name;
                $UP->u_province_fid = $Province->pi_fid;
                $UP->u_city = $City->ci_id;
                $UP->u_city_name = $City->ci_name;
                $UP->u_city_no = $this->_post('city');

                $result_1 = $UP->save();

                $Users->u_dispname = $this->_post('nickname');
                $result_2 = $Users->save();

                // 跳转赋值
                $this->_assignLoginInfo();

                $this->error(L('save_success') . $showmessage, HOMEPAGE . '/user.php/iwxuserconf', 3);
            } else {
                // 渲染页面
                $Users = new UsersModel();
                $Users->find($this->_session('f_userid'));
                $UP = $Users->getUserProfile();
                $UA = $Users->getUserAcc();
                // var_dump($UA);
                $birth_year_combo = get_dropdown_option(genBirthYear(), $UP->u_bir_y);
                $birth_month_combo = get_dropdown_option(genBirthMonth(), $UP->u_bir_m);
                $birth_day_combo = get_dropdown_option(genBirthDay(), $UP->u_bir_d);
                // 处理省市联动
                $province_combo = get_dropdown_option(genCHNProvince(), $UP->u_province_fid);
                //dump($province_combo);
                $city_combo = get_dropdown_option(genCHNCity($UP->u_province), $UP->u_city_no);
                //dump($city_combo);

                //-----------推荐商品新增参数--------------------------------------------------------start//
                //$con=array('28381','30051','30998','27587','28260','30854','30715','28203');

                //192.168.52.19上用这个--
                $res = M('help_doc')->where("cate =25")->select();
                $con=explode(' ',$res[0]['intro']);
                //192.168.52.19上用这个

                //www.ignjewelry.com上用这个--
                //$res = M('help_doc')->where("cate =13")->select();
                //$con=explode(' ',$res[0]['content']);
                //www.ignjewelry.com上用这个--

                foreach($con as $k=>$v){
                    $info[$k]=D('product')->where("p_id=$v")->select();
                }
                $arr=array_rand($info,4);
                foreach($arr as $k=>$v){
                    $info1[$k]=$info[$v];
                }
                $this->assign('info1',$info1);
                if(!empty($Users->u_mob_no)){
                    $login_name=$Users->u_mob_no;
                }else{
                    $login_name=$Users->u_email;
                }
                $this->assign('login_name',$login_name);
                //----------------------推荐商品新增参数----------------------------------------------end//

                $this->assign('userBasic', $Users->data());
                $this->assign('userPro', $UP->data());
                $this->assign('userProf', explode('#', $UP->u_prof));
                $this->assign('userAcc', $UA->u_vcoin_av);
                $this->assign('userAcc_rmb', $UA->u_rcoin_av . " " . L('ACCOUNT_UNIT'));
                $this->assign('yearCombo', $birth_year_combo);
                $this->assign('monthCombo', $birth_month_combo);
                $this->assign('dayCombo', $birth_day_combo);
                $this->assign('provinceCombo', $province_combo);
                $this->assign('cityCombo', $city_combo);
                $this->assign('homePage', HOMEPAGE);
                $this->assign('showtitle', "用户设置-基本信息-3D城");
                $this->assign('DBF_P', $this->DBF->Product);

                $this->_renderPage();
            }
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/iwxuserconf');
        }
    }
    /**
     * 第三版手机版用户基本信息设置
     */
    public function iwxuserset(){
        try{
            $Users = new UsersModel();
            $Users->find($this->_session('f_userid'));
            $UP = $Users->getUserProfile();
            $UA = $Users->getUserAcc();
            // 处理省市联动
            $province_combo = get_dropdown_option(genCHNProvince(), $UP->u_province_fid);
            //dump($province_combo);
            $city_combo = get_dropdown_option(genCHNCity($UP->u_province), $UP->u_city_no);
            //dump($city_combo);
            if(!empty($Users->u_mob_no)){
                $login_name=$Users->u_mob_no;
            }else{
                $login_name=$Users->u_email;
            }
            $this->assign('login_name',$login_name);
            $this->assign('userBasic', $Users->data());
            $this->assign('provinceCombo', $province_combo);
            $this->assign('cityCombo', $city_combo);
            $this->_renderPage();

        }catch(Exception $e){
            $this->error($e->getMessage(), '__APP__/userconf/?type=iwx');
        }
    }
    /**
     * 生成校验码
     *
     * @access public
     * @return mix
     */
    public function captcha()
    {
        vendor('Captcha.captcha');
        $fonts = C('CAPTCHA_FONTS_PATH');
        $v_captcha = new PhpCaptcha($fonts, 120, 42);
        $v_captcha->SetNumChars(4);
        $v_captcha->Create();
    }

    /**
     * 激活
     *
     * @access public
     * @return mixed
     */
    public function active()
    {
        try {

            $active_code = $this->_request('sv');
            $UserAct = D('UserActive');
            $UserAct->startTrans();
            $reArr = array();

            if (empty($active_code)) {
                throw new Exception(L('active_fail'));
            }
            $res = $UserAct->getByuc_code($active_code);

            $validtime = $UserAct->validtime($res['uc_createdate']);
            // var_dump($validtime);
            // exit;

            if (is_array($res) && ($res['uc_active'] == 0) && ($validtime)) {
                // 处理激活码

                $UserAct->uc_active = 1;
                $UserAct->uc_activedate = get_now();
                $reArr[] = $UserAct->save();
                // 处理用户
                $Users = D('Users');
                $Users->find($res['u_id']);
                // var_dump($Users);
                // exit;
                $Users->u_mail_verify = 1;
                $reArr[] = $Users->save();
                // 结果

                if (in_array(false, $reArr)) {
                    throw new Exception(L('active_fail'));
                } else {
                    $result_1 = $UserAct->commit();
                    if ($result_1) {
                        // 任务系统
                        $HJ = new HookJobsModel();
                        $jf_result = $HJ->run($res['u_id'], __METHOD__);
                        if ($jf_result) {
                            // $showmessage = ", 积分增加" . $jf_result [0] ['val'] . " ";
                            $showmessage = " 您的帐号已经激活.";
                        }
                    }
                    $this->success(L('success_tips') . $showmessage, '__APP__/login/index');
                }
            } else {
                throw new Exception(L('active_fail'));
            }
        } catch (Exception $e) {
            $UserAct->rollback();
            $this->error($e->getMessage(), '__APP__/index');
        }
    }

    /**
     * ajax操作
     *
     * @access public
     * @return null
     */
    public function ajax()
    {
        try {
            switch ($this->_request('act')) {
                // TODO
                // 之后可以考虑直接用魔术方法__call()
                case 'isReg':
                    $res = $this->isReg($this->_request());
                    make_json_result($res);
                    break;
            }
        } catch (Exception $e) {
            make_json_error($e->getMessage());
        }
    }

    /**
     * 是否能注册
     *
     * @access private
     * @param array $reginfo
     * @return mixed
     */
    private function isReg($reginfo)
    {
        // TODO
        // 能否将一个用户对象传进来？
        $keys = array_keys($reginfo);
        foreach ($keys as $k => $v) {
            // TODO
            // 代码提炼
            switch ($v) {
                case 'account':
                    $account = $reginfo['account'];
                    $Users = D('Users');
                    $userCount = $Users->where("u_email='$account'")->count("u_id");

                    if ($userCount) {
                        throw new Exception(L('account_exist'));
                    }
                    break;
                case 'nickname':
                    $nickname = $reginfo['nickname'];
                    $Users = D('Users');
                    $userCount = $Users->where("u_dispname='$nickname'")->count("u_id");
                    if ($userCount) {
                        throw new Exception(L('nickname_exist'));
                    }
                    // 屏蔽字
                    $NK = D('NoKeywords');
                    $nkCount = $NK->where("nk_words='$nickname'")->count("nk_id");
                    if ($nkCount) {
                        throw new Exception(L('nickname_nokeywords'));
                    }
                    break;
            }
        }
        return true;
    }

    /**
     * 验证教育经历信息
     *
     * @param array $req
     * @throws Exception
     * @return boolean
     */
    protected function _validEduExp(array $req)
    {
        $PVC = new PVC2();
        $PVC->setStrictMode(true)->setModeArray()->SourceArray = $req;
        // 学校名称验证
        $PVC->Length(1, 50)
            ->Error('no_school_name')
            ->add('school_name');
        // 院校班级验证
        $PVC->Length(1, 20)
            ->Error('no_class_name')
            ->add('class_name');
        // 入学时间验证
        $PVC->Between(1970, 2014)
            ->Error('start_time_invalid')
            ->add('starttime');
        $PVC->Between(1970, 2014)
            ->Error('start_time_invalid')
            ->add('endtime');
        $PVC->Gteq($req['starttime'])
            ->Error('start_time_must_less_than_end_time')
            ->add('endtime');
        $verifyRes = $PVC->verifyAll();
        if (! $verifyRes) {
            throw new Exception(L($PVC->Error));
        }
        // 学校名称是否含有屏蔽字
        $NK = D('NoKeywords');
        $nkCount = $NK->where("nk_words='" . $req['school_name'] . "'")->count("nk_id");
        if ($nkCount) {
            throw new Exception(L('school_name_includes_keywords'));
        }
        // 院校班级是否含有屏蔽字
        $NK = D('NoKeywords');
        $nkCount = $NK->where("nk_words='" . $req['class_name'] . "'")->count("nk_id");
        if ($nkCount) {
            throw new Exception(L('class_name_includes_keywords'));
        }
        // 所在地验证
        $province_id = $req['province'] + 1;
        $Province = D('Province');
        $provinceRes = $Province->find($province_id);
        $City = D('City');
        $cityRes = $City->where('pi_id=' . $province_id . ' AND pi_no=' . $this->_post('city'))
            ->find();
        if ((! $provinceRes) || (! $cityRes)) {
            throw new Exception(L('location_data_error'));
        }
        return true;
    }

    /**
     * 验证工作经历信息
     *
     * @param array $req
     * @throws Exception
     * @return boolean
     */
    protected function _validWorkExp(array $req)
    {
        $PVC = new PVC2();
        $PVC->setStrictMode(true)->setModeArray()->SourceArray = $req;
        // 公司名称验证
        $PVC->Length(1, 50)
            ->Error('no_company_name')
            ->add('company_name');
        // 部门职位验证
        $PVC->Length(1, 20)
            ->Error('no_department_or_position_name')
            ->add('department_position');
        // 工作时间验证
        $PVC->Between(1970, 2014)
            ->Error('start_time_invalid')
            ->add('starttime');
        $PVC->Between(1970, 2014)
            ->Error('start_time_invalid')
            ->add('endtime');
        $PVC->Gteq($req['starttime'])
            ->Error('start_time_must_less_than_end_time')
            ->add('endtime');
        $verifyRes = $PVC->verifyAll();
        if (! $verifyRes) {
            throw new Exception(L($PVC->Error));
        }
        // 公司名称是否含有屏蔽字
        $NK = D('NoKeywords');
        $nkCount = $NK->where("nk_words='" . $req['company_name'] . "'")->count("nk_id");
        if ($nkCount) {
            throw new Exception(L('company_name_includes_keywords'));
        }
        // 部门职位是否含有屏蔽字
        $NK = D('NoKeywords');
        $nkCount = $NK->where("nk_words='" . $req['department_position'] . "'")->count("nk_id");
        if ($nkCount) {
            throw new Exception(L('department_position_includes_keywords'));
        }
        // 所在地验证
        $province_id = $req['province'] + 1;
        $Province = D('Province');
        $provinceRes = $Province->find($province_id);
        $City = D('City');
        $cityRes = $City->where('pi_id=' . $province_id . ' AND pi_no=' . $this->_post('city'))
            ->find();
        if ((! $provinceRes) || (! $cityRes)) {
            throw new Exception(L('location_data_error'));
        }
        return true;
    }

    /**
     * 验证资料信息
     *
     * @param array $req
     * @throws Exception
     * @return boolean
     */
    protected function _validProfile(array $req)
    {
        $PVC = new PVC2();
        $PVC->setStrictMode(true)->setModeArray()->SourceArray = $req;
        // 昵称验证
        $PVC->Length(1, null)
            ->Error('no_nickname')
            ->add('nickname');
        // 昵称是否已被注册
        // $PVC->Length ( 1, 24 )->add ( 'nickname' );
        $Users = D('Users');
        if (! $req['nickname'] || strlen($req['nickname']) > 30) {
            throw new Exception('昵称不符合规范！');
        }
        // exit;
        $res = $Users->getByu_dispname($req['nickname']);
        // if (is_array ( $res ) && $res ['u_id'] != $this->_session (
        // 'f_userid' )) {
        // throw new Exception ( 'u_dispname_already_register' );
        // }
        // 昵称是否含有屏蔽字
        $NK = D('NoKeywords');
        $nkCount = $NK->where("nk_words='" . $req['nickname'] . "'")->count("nk_id");
        if ($nkCount) {
            throw new Exception(L('nickname_nokeywords'));
        }
        // 个人域名验证
        /*
         * $domain = trim ( $req ['domain'] );
         * $pattern = '/^[uU]{1}+\d+$/';
         * $domainRes = preg_match ( $pattern, $domain );
         * if ($domainRes) {
         * // 如果是系统默认的匹配用户ID值
         * $domainId = ( int ) substr ( $domain, 1 );
         * if ($domainId != $this->_session ( 'f_userid' )) {
         * throw new Exception ( L ( 'u_domain_not_valid' ) );
         * }
         * } else {
         * // 如果是用户自定义
         * $pattern = '/^[a-zA-Z][a-zA-Z\d\_]{0,9}$/';
         * $domainRes = preg_match ( $pattern, $domain );
         * if (! $domainRes) {
         * throw new Exception ( L ( 'u_domain_not_valid' ) );
         * }
         *
         * // 判定是否已经被其他用户占用
         * $UP = new UserProfileModel ();
         * $res = $UP->getByu_domain ( $domain );
         * if (is_array ( $res ) && $res ['u_id'] != $this->_session ( 'f_userid' )) {
         * throw new Exception ( 'u_domain_already_register' );
         * }
         * }
         */
        return true;
    }

    /**
     * 添加教育经历
     */
    public function addedu()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }
            // 提交数据
            if ($this->isPost()) {
                // 数据验证
                $this->_validEduExp($this->_post());

                // 准备数据
                $now = get_now();
                $province_id = $this->_post('province') + 1;
                $Province = D('Province');
                $provinceRes = $Province->find($province_id);
                $City = D('City');
                $cityRes = $City->where('pi_id=' . $province_id . ' AND pi_no=' . $this->_post('city'))
                    ->find();

                // 插入数据
                $UED = new UserEducationModel();
                $data = array(
                    $UED->F->UID => $this->_loginUserObj->{$this->_loginUserObj->F->ID},
                    $UED->F->StartYear => $this->_post('starttime'),
                    $UED->F->EndYear => $this->_post('endtime'),
                    $UED->F->SchoolName => $this->_post('school_name'),
                    $UED->F->ProfName => $this->_post('class_name'),
                    $UED->F->ProvinceId => $provinceRes['pi_id'],
                    $UED->F->ProvinceName => $provinceRes['pi_name'],
                    $UED->F->CityId => $cityRes['ci_id'],
                    $UED->F->CityName => $cityRes['ci_name'],
                    $UED->F->IsPublic => $this->_post('privacy'),
                    $UED->F->CityNumber => $this->_post('city'),
                    $UED->F->Type => $this->_post('edu_type'),
                    $UED->F->CTime => $now,
                    $UED->F->UpdateTime => $now
                );
                $UED->create();
                $addRes = $UED->add($data);
                if ($addRes) {
                    $this->success(L('save_success'), HOMEPAGE . '/user.php/userconf/addedu', 3);
                }
            }
            // 准备初始化数据
            $Combo_startYear = get_dropdown_option(genDescYear());
            $Combo_province = get_dropdown_option(genCHNProvince(), 0);
            $Combo_city = get_dropdown_option(genCHNCity(1));
            // 获取教育经历
            $UED = new UserEducationModel();
            $eduexpList = $UED->getUserEdu($this->_loginUserObj->{$this->_loginUserObj->F->ID});
            // 模板赋值
            $this->assign('yearCombo', $Combo_startYear);
            $this->assign('provinceCombo', $Combo_province);
            $this->assign('cityCombo', $Combo_city);
            $this->assign('DBF_UED', $UED->F);
            $this->assign('eduexpList', $eduexpList);
            $this->assign('showtitle', "用户设置-教育经历-3D城");

            // 渲染输出
            $this->_renderPage();
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/userconf/addedu');
        }
    }

    /**
     * 添加工作经历
     */
    public function addwork()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }
            // 提交数据
            if ($this->isPost()) {
                // 数据验证
                $this->_validWorkExp($this->_post());

                // 准备数据
                $now = get_now();
                $province_id = $this->_post('province') + 1;
                $Province = D('Province');
                $provinceRes = $Province->find($province_id);
                $City = D('City');
                $cityRes = $City->where('pi_id=' . $province_id . ' AND pi_no=' . $this->_post('city'))
                    ->find();

                // 插入数据
                $UWE = new UserWorkModel();
                $data = array(
                    $UWE->F->UID => $this->_loginUserObj->{$this->_loginUserObj->F->ID},
                    $UWE->F->StartYear => $this->_post('starttime'),
                    $UWE->F->EndYear => $this->_post('endtime'),
                    $UWE->F->CompanyName => $this->_post('company_name'),
                    $UWE->F->PositionName => $this->_post('department_position'),
                    $UWE->F->ProvinceId => $provinceRes['pi_id'],
                    $UWE->F->ProvinceName => $provinceRes['pi_name'],
                    $UWE->F->CityId => $cityRes['ci_id'],
                    $UWE->F->CityName => $cityRes['ci_name'],
                    $UWE->F->IsPublic => $this->_post('privacy'),
                    $UWE->F->CityNumber => $this->_post('city'),
                    $UWE->F->CTime => $now,
                    $UWE->F->UpdateTime => $now
                );
                $UWE->create();
                $addRes = $UWE->add($data);
                if ($addRes) {
                    $this->success(L('save_success'), HOMEPAGE . '/user.php/userconf/addwork', 3);
                }
            }
            // 准备初始化数据
            $Combo_startYear = get_dropdown_option(genDescYear());
            $Combo_province = get_dropdown_option(genCHNProvince(), 0);
            $Combo_city = get_dropdown_option(genCHNCity(1));
            // 获取工作经历
            $UWE = new UserWorkModel();
            $workexpList = $UWE->getUserWork($this->_loginUserObj->{$this->_loginUserObj->F->ID});
            // 模板赋值
            $this->assign('yearCombo', $Combo_startYear);
            $this->assign('provinceCombo', $Combo_province);
            $this->assign('cityCombo', $Combo_city);
            $this->assign('DBF_UWE', $UWE->F);
            $this->assign('workexpList', $workexpList);
            $this->assign('showtitle', "用户设置-工作经历-3D城");

            // 渲染输出
            $this->_renderPage();
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/userconf/addwork');
        }
    }

    /**
     * 添加培训经历
     */
    public function addtrain()
    {
        /*
         * $UserTrain = new UserTrainingModel (); $now = get_now (); $data =
         * array ( $UserTrain->F->UID => 99, $UserTrain->F->StartYear => 1999,
         * $UserTrain->F->StartMonth => 10, $UserTrain->F->EndYear => 2003,
         * $UserTrain->F->EndMonth => 9, $UserTrain->F->TrainID => 1,
         * $UserTrain->F->TrainName => '达内科技', $UserTrain->F->PositionID => 1,
         * $UserTrain->F->PositionName => 'PHP程序员', $UserTrain->F->Intro =>
         * '无他唯手熟尔', $UserTrain->F->CTime => $now, $UserTrain->F->UpdateTime =>
         * $now ); $res = $UserTrain->add ( $data ); vd ( $res );
         */
        $this->_renderPage();
    }

    /**
     * 编辑教育经历
     */
    public function editedu()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }
            // 提交数据
            if ($this->isPost()) {
                // 数据验证
                $this->_validEduExp($this->_post());
                // 准备数据
                $now = get_now();
                $province_id = $this->_post('province') + 1;
                $Province = D('Province');
                $provinceRes = $Province->find($province_id);
                $City = D('City');
                $cityRes = $City->where('pi_id=' . $province_id . ' AND pi_no=' . $this->_post('city'))
                    ->find();
                // 编辑数据
                $UED = new UserEducationModel();
                $conditon = array(
                    $UED->F->UID => $this->_loginUserObj->{$this->_loginUserObj->F->ID},
                    $UED->F->ID => intval($this->_post('eduexpid'))
                );
                $data = array(
                    $UED->F->UID => $this->_loginUserObj->{$this->_loginUserObj->F->ID},
                    $UED->F->StartYear => $this->_post('starttime'),
                    $UED->F->EndYear => $this->_post('endtime'),
                    $UED->F->SchoolName => $this->_post('school_name'),
                    $UED->F->ProfName => $this->_post('class_name'),
                    $UED->F->ProvinceId => $provinceRes['pi_id'],
                    $UED->F->ProvinceName => $provinceRes['pi_name'],
                    $UED->F->CityId => $cityRes['ci_id'],
                    $UED->F->CityName => $cityRes['ci_name'],
                    $UED->F->IsPublic => $this->_post('privacy'),
                    $UED->F->CityNumber => $this->_post('city'),
                    $UED->F->Type => $this->_post('edu_type'),
                    $UED->F->UpdateTime => $now
                );
                $editRes = $UED->where($conditon)->save($data);
                if ($editRes) {
                    $this->success(L('save_success'), HOMEPAGE . '/user.php/userconf/addedu', 3);
                }
            }
            // 数据验证
            $uedId = intval($this->_get('id'));
            if (! $uedId) {
                throw new Exception(L('edu_exp_id_error'));
            }
            // 获取教育经历详情
            $UED = new UserEducationModel();
            $condition = array(
                $UED->F->UID => $this->_loginUserObj->{$this->_loginUserObj->F->ID},
                $UED->F->ID => $uedId
            );
            $eduexpInfo = $UED->where($condition)->find();
            if (! $eduexpInfo) {
                throw new Exception(L('edu_exp_data_invalid'));
            }
            // 准备初始化数据
            $Combo_educationType = get_dropdown_option(L('EDUCATION_TYPE'), $eduexpInfo[$UED->F->Type]);
            $Combo_allowPublic = get_dropdown_option(L('IS_ALLOW_PUBLIC'), $eduexpInfo[$UED->F->IsPublic]);
            $Combo_startYear = get_dropdown_option(genDescYear(), $eduexpInfo[$UED->F->StartYear]);
            $Combo_endYear = get_dropdown_option(genDescYear(), $eduexpInfo[$UED->F->EndYear]);
            $Combo_province = get_dropdown_option(genCHNProvince(), $eduexpInfo[$UED->F->ProvinceId] - 1);
            $Combo_city = get_dropdown_option(genCHNCity($eduexpInfo[$UED->F->ProvinceId]), $eduexpInfo[$UED->F->CityNumber]);
            // 获取教育经历
            $eduexpList = $UED->getUserEdu($this->_loginUserObj->{$this->_loginUserObj->F->ID});
            // 模板赋值
            $this->assign('allowPublicCombo', $Combo_allowPublic);
            $this->assign('educationTypeCombo', $Combo_educationType);
            $this->assign('startYearCombo', $Combo_startYear);
            $this->assign('endYearCombo', $Combo_endYear);
            $this->assign('provinceCombo', $Combo_province);
            $this->assign('cityCombo', $Combo_city);
            $this->assign('DBF_UED', $UED->F);
            $this->assign('eduexpInfo', $eduexpInfo);
            $this->assign('eduexpList', $eduexpList);
            // 渲染输出
            $this->_renderPage();
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/userconf/addedu');
        }
    }

    /**
     * 编辑工作经历
     */
    public function editwork()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }
            // 提交数据
            if ($this->isPost()) {
                // 数据验证
                $this->_validWorkExp($this->_post());
                // 准备数据
                $now = get_now();
                $province_id = $this->_post('province') + 1;
                $Province = D('Province');
                $provinceRes = $Province->find($province_id);
                $City = D('City');
                $cityRes = $City->where('pi_id=' . $province_id . ' AND pi_no=' . $this->_post('city'))
                    ->find();
                // 编辑数据
                $UWE = new UserWorkModel();
                $conditon = array(
                    $UWE->F->UID => $this->_loginUserObj->{$this->_loginUserObj->F->ID},
                    $UWE->F->ID => intval($this->_post('workexpid'))
                );
                $data = array(
                    $UWE->F->StartYear => $this->_post('starttime'),
                    $UWE->F->EndYear => $this->_post('endtime'),
                    $UWE->F->CompanyName => $this->_post('company_name'),
                    $UWE->F->PositionName => $this->_post('department_position'),
                    $UWE->F->ProvinceId => $provinceRes['pi_id'],
                    $UWE->F->ProvinceName => $provinceRes['pi_name'],
                    $UWE->F->CityId => $cityRes['ci_id'],
                    $UWE->F->CityName => $cityRes['ci_name'],
                    $UWE->F->CityNumber => $this->_post('city'),
                    $UWE->F->IsPublic => $this->_post('privacy'),
                    $UWE->F->UpdateTime => $now
                );
                $editRes = $UWE->where($conditon)->save($data);
                if ($editRes) {
                    $this->success(L('save_success'), HOMEPAGE . '/user.php/userconf/addwork', 3);
                }
            }
            // 数据验证
            $uweId = intval($this->_get('id'));
            if (! $uweId) {
                throw new Exception(L('work_exp_id_error'));
            }
            // 获取工作经历详情
            $UWE = new UserWorkModel();
            $condition = array(
                $UWE->F->UID => $this->_loginUserObj->{$this->_loginUserObj->F->ID},
                $UWE->F->ID => $uweId
            );
            $workexpInfo = $UWE->where($condition)->find();
            if (! $workexpInfo) {
                throw new Exception(L('work_exp_data_invalid'));
            }
            // 准备初始化数据
            $Combo_allowPublic = get_dropdown_option(L('IS_ALLOW_PUBLIC'), $workexpInfo[$UWE->F->IsPublic]);
            $Combo_startYear = get_dropdown_option(genDescYear(), $workexpInfo[$UWE->F->StartYear]);
            $Combo_endYear = get_dropdown_option(genDescYear(), $workexpInfo[$UWE->F->EndYear]);
            $Combo_province = get_dropdown_option(genCHNProvince(), $workexpInfo[$UWE->F->ProvinceId] - 1);
            $Combo_city = get_dropdown_option(genCHNCity($workexpInfo[$UWE->F->ProvinceId]), $workexpInfo[$UWE->F->CityNumber]);
            // 获取工作经历
            $workexpList = $UWE->getUserWork($this->_loginUserObj->{$this->_loginUserObj->F->ID});
            // 模板赋值
            $this->assign('allowPublicCombo', $Combo_allowPublic);
            $this->assign('startYearCombo', $Combo_startYear);
            $this->assign('endYearCombo', $Combo_endYear);
            $this->assign('provinceCombo', $Combo_province);
            $this->assign('cityCombo', $Combo_city);
            $this->assign('DBF_UWE', $UWE->F);
            $this->assign('workexpInfo', $workexpInfo);
            $this->assign('workexpList', $workexpList);
            // 渲染输出
            $this->_renderPage();
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/userconf/addwork');
        }
    }

    /**
     * 编辑培训经历
     */
    public function edittrain()
    {
        $UserTrain = new UserTrainingModel();
        $now = get_now();
        $con = array(
            $UserTrain->F->ID => 1,
            $UserTrain->F->UID => 99
        );
        $res = $UserTrain->where($con)->find();
        $data = array(
            $UserTrain->F->StartYear => 1999,
            $UserTrain->F->StartMonth => 10,
            $UserTrain->F->EndYear => 2003,
            $UserTrain->F->EndMonth => 9,
            $UserTrain->F->TrainID => 2,
            $UserTrain->F->TrainName => '宝信科技',
            $UserTrain->F->PositionID => 1,
            $UserTrain->F->PositionName => 'PHP程序员',
            $UserTrain->F->Intro => '无他唯手熟尔',
            $UserTrain->F->CTime => $now,
            $UserTrain->F->UpdateTime => $now
        );
        $res = $UserTrain->where($con)->save($data);
        vd($res);
    }

    /**
     * 删除教育经历
     */
    public function removeedu()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }
            // 数据验证
            $uedId = intval($this->_get('id'));
            if (! $uedId) {
                throw new Exception(L('edu_exp_id_error'));
            }
            // 获取教育经历详情
            $UED = new UserEducationModel();
            $condition = array(
                $UED->F->UID => $this->_loginUserObj->{$this->_loginUserObj->F->ID},
                $UED->F->ID => $uedId
            );
            $eduexpInfo = $UED->where($condition)->find();
            if (! $eduexpInfo) {
                throw new Exception(L('edu_exp_data_invalid'));
            }
            // 删除数据
            $data = array(
                $UED->F->Status => - 1
            );
            $removeRes = $UED->where($condition)->save($data);
            if (! $removeRes) {
                throw new Exception(L('remove_edu_exp_data_error'));
            }
            redirect(HOMEPAGE . '/user.php/userconf/addedu');
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/userconf/addedu');
        }
    }

    /**
     * 删除工作经历
     */
    public function removework()
    {
        try {
            // 判断登录
            if (! $this->_isLogin()) {
                $this->_needLogin();
            }
            // 数据验证
            $uweId = intval($this->_get('id'));
            if (! $uweId) {
                throw new Exception(L('work_exp_id_error'));
            }
            // 获取工作经历详情
            $UWE = new UserWorkModel();
            $condition = array(
                $UWE->F->UID => $this->_loginUserObj->{$this->_loginUserObj->F->ID},
                $UWE->F->ID => $uweId
            );
            $workexpInfo = $UWE->where($condition)->find();
            if (! $workexpInfo) {
                throw new Exception(L('work_exp_data_invalid'));
            }
            // 删除数据
            $data = array(
                $UWE->F->Status => - 1
            );
            $removeRes = $UWE->where($condition)->save($data);
            if (! $removeRes) {
                throw new Exception(L('remove_work_exp_data_error'));
            }
            redirect(HOMEPAGE . '/user.php/userconf/addwork');
        } catch (Exception $e) {
            $this->error($e->getMessage(), '__APP__/userconf/addwork');
        }
    }

    /**
     * 删除培训经历
     */
    public function removetrain()
    {
        $UserTrain = new UserTrainingModel();
        $con = array(
            $UserTrain->F->ID => 1,
            $UserTrain->F->UID => 99
        );
        $res = $UserTrain->where($con)->find();
        $data = array(
            $UserTrain->F->Status => - 1
        );
        $res = $UserTrain->where($con)->save($data);
        vd($res);
    }

    /**
     * 新增收货地址-地区选择器
     *
     * @param AreaInfoPickerModel $AIPM
     * @param unknown $Province
     * @param unknown $City
     * @param unknown $Region
     * @return unknown
     */
    private function getDispArea(AreaInfoPickerModel $AIPM, $Province, $City, $Region)
    {
        $Result .= $AIPM->getItemNameByID($Province) . ' ';
        $Result .= $AIPM->getItemNameByID($City) . ' ';
        $Result .= $AIPM->getItemNameByID($Region);
        return $Result;
    }

    private function getHtmlCtrl(AreaInfoPickerModel $AIPM)
    {
        $HtmlCtrl = array();
        $HtmlCtrl['AreaInfo'] = $AIPM->getJsonAreaInfo();
        $HtmlCtrl['AreaChildIndex'] = $AIPM->getJsonChildIndex();
        return $HtmlCtrl;
    }

    private function setDispArea(array $AddressList, AreaInfoPickerModel $AIPM)
    {
        $F = new DBF_UserAddress();
        foreach ($AddressList as &$Address) {
            $Province = $Address[$F->Province];
            $City = $Address[$F->City];
            $Region = $Address[$F->Region];
            $Address['area_disp'] = $this->getDispArea($AIPM, $Province, $City, $Region);
        }

        return $AddressList;
    }
}
