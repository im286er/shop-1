<?php
/**
 * DIY 
 *
 * @author zhangzhibin 
 * 
 */
class DiyAction extends CommonAction {
	
	public function __construct() {
		parent::__construct ();
		$this->header="jewelry";
		// 判断登录
		//if (! $this->_isLogin ()) {
				
			//echo $_GET['id'];
			//var_dump( __SELF__);

			//exit;
		//	$this->_needLogin ();
	//	}
		
		// load ( "@.DBF" );
		
		$this->DBF = new DBF ();
	}
	
	
	public function index(){
		$diylistinfo=M('diy_cate')->where("delsign=0")->limit(0,8)->order("sort")->select();
		$this->assign("diylistinfo",$diylistinfo);
		//var_dump($diylistinfo);
		$this->_renderPage();
	}
	
	public function edit(){
		$DM= new DiyUnitModel();
		$DM->getDiyUnit();
		$this->_renderPage ();
	}
	
	

	
	
	/* public function jewelryedit(){
		$u_id=$_SESSION ['f_userid'];
		$cid=14;//DIY类别
		
		$DC=M('diy_cate')->field('startprice')->where("cid=".$cid)->find();
		$startprice=$DC['startprice'];
		
		$DU=M('diy_unit')->field('id,unit_name,unit_value,unit_default')->where('cid='.$cid)->order('sort')->select();//选择tdf_diy_unit
		foreach($DU as $keyDU => $valueDU){
			if($valueDU['unit_name']=='Size'){$sizeArr=$valueDU['unit_value'];}
			$value_default[$valueDU['unit_name']]=$valueDU['unit_default']; //读取默认值
		}
		$sizeArr=explode(";",$sizeArr);//尺寸大小默认数组
		foreach($sizeArr as $rkey =>$rvalue){
			$tempsize=explode("=",$rvalue);
			$size[$rkey]['key']=$tempsize[0];
			$size[$rkey]['value']=$tempsize[1];
		}
		
		$sql="select TPM.pma_id,TPM.pma_name as TPM_name,TPMP.pma_name as TPMP_name,TPM.pma_unitprice,TPM.pma_density,TPM.pma_startprice from tdf_printer_material as TPM ";
		$sql.="Left Join tdf_printer_material as TPMP ON TPMP.pma_id=TPM.pma_parentid ";
		$sql.="where TPM.pma_type=1 order by TPM.pma_weight ASC ";
		$mcate=M("printer_material")->query($sql);
		
		if($_POST){
			if (!$this->_isLogin()){
				cookie('value',$_POST,array('expire'=>3600,'prefix'=>'DIY'));
				$this->_needLogin();
				exit;
			}
			foreach($DU as $key =>$value){
				$diyinfo[$value['id']]=$_POST[$value['unit_name']];//获取post来的数据
			}
			$data=$_POST;
			$data['u_id']=$u_id;
			$data['diy_unit_info']=serialize($diyinfo);
			$data['price']=$_POST['price'];
			$data['title']=$data['Textvalue'];//名称
			$data['startprice']=$startprice;
			$datasave=$this->into_price($data,$mcate);//加入价格计算
			
			if($_POST['pid']){//如果有id，执行更新
				$sresult=M('user_diy')->where("id=".$data['pid'])->save($data);
				$diy_id=$_POST['pid'];
			}else{//无ID进行数据增加
				$result=M('user_diy')->add($data);
				$diy_id=$result['id'];
			}
			
			if($_POST['stype']==1){
				$p_data['p_name']		= $data['title'];
				$p_data['p_cate_4']		= intval($data['diy_cate']);//产品类别
				$p_data['p_creater']	= $u_id;
				$p_data['p_cover']		= $data['cover'];
				$p_data['p_price']		= $data['price'];
				$p_data['p_createdate']	= date("Y-m-d G:i:s",time());
				$p_data['p_createtime']	= time();
				$p_data['p_lastupdate']	= date("Y-m-d G:i:s",time());
				$p_data['p_lastupdatetime']	= time();
				$p_data['p_producttype']	=4;//产品类型 1为数字模型 2为实物商品(3d打印机) 3为打印件 4为DIY产品
				$p_data['p_diy_id']		=$data['pid'];
				
				$PM=new ProductModel();
				if($PM->getProductByDiyid($p_data['p_diy_id'])){//如果存在有diy_id,修改更新
					if($PM->where("p_diy_id=".$p_data['p_diy_id']."")->save($p_data)){
						$pm_info=$PM->getProductByDiyid($p_data['p_diy_id']);
						$pid=$pm_info['p_id'];
					}
				}else{//如果没有diy_id，则新增
					$pid=$PM->add($p_data);
				}
				$UCM=new UserCartModel();
				$isAdded = $UCM->addProduct_diy ( $pid, $u_id );
				redirect (  WEBROOT_URL."/user.php/cart");
			}else{
				redirect ( __APP__."/diy-jewelrylist");
			}
		
		}else{
			$udid=I("id",0,"intval");//tdf_user_id中的ID
					
			if($udid){
				$udinfo=M('user_diy')->where('id='.$udid)->find();
				//$udinfo=M('user_diy')->where('id='.$udid.' and u_id='.$u_id)->find();
				//if(!$udinfo){
				//	$this->error("很抱歉,参数错误！","",3);
				//}
				$udinfo=$this->get_udinfo($udinfo);
			}else{
				if($_COOKIE['DIYvalue']){//如果存在
					$defaultvalue= explode('think:', $_COOKIE['DIYvalue']);//去除think:
					//cookie(null,'DIYvalue');//清除cookie
					$defaultvalue=json_decode($defaultvalue[1],TRUE);//json格式转换为数组
				}else{
					$defaultvalue=$value_default;
				}
				$udinfo=$defaultvalue;
			}
			//var_dump($udinfo);
			//var_dump($size);
			$udinfo['startprice']=$startprice;//赋值起打价格
			$this->assign('size',$size);
			$this->assign('udinfo',$udinfo);
			$this->assign('mcate',$mcate);
			$this->_renderPage();
		}		
	} */


    /**
     *
     */
    public function jewelry(){//
		$pid=I('pid',0,'intval');
//        $mmode=I('mmode',0,'intval');
//        $mtype=I('type','','string');
		$showtype=I('showtype',0,'intval');
        $TPM= new ProductModel();
        if($pid){
			$udinfo=M("user_diy")->where("id=".$pid)->find();
			$cid=$udinfo['cid'];
		}else{
			$cid=I('cid',0,'intval');
            $productInfo=$TPM->getProductByDiyCateCid($cid);
            //no webgl
            $this->assign("cpid",$productInfo['p_id']);
		}
        //获取TDK信息 start
        $TPCM=new ProductCateModel();
        $TDK=$TPCM->getCateTDKByPcate($productInfo['p_cate'],$productInfo['p_name']);
        $this->assign("TDK",$TDK);
        //获取TDK信息 end

		//产品缩略图
		$cinfo=M('diy_cate')->where("cid=".$cid)->find();
		$iconArr=explode(',',$cinfo['cate_icon']);
		foreach($iconArr as $key => $value){
			$cinfo['icon'][$key]=getimgbyID($value);
		}

		//作者信息(头像、签名)
		$Users = new UsersModel ();
		$Users->find ($cinfo['u_id']);
		$UP = $Users->getUserProfile ();
		$UA = $Users->getUserAcc ();
		$this->assign ( 'userBasic_au', $Users->data () );
		$this->assign('userProf',$UP->data());

		//推荐款式产品
		$Prodlist=$this->getProd(5,$cid);
		$this->assign ( 'DBF_P', $this->DBF->Product );
		$this->assign('Prodlist',$Prodlist);
		//推荐款式产品

		$this->assign('showtype',$showtype);
		$this->assign('pid',$pid);
		$this->assign('cid',$cid);
		//var_dump($cinfo);
		$this->assign('cinfo',$cinfo);

//        $this->assign('mmode',$mmode);
//        $this->assign('mtype',$mtype);

		$this->_renderPage();
	}

    /**
     * 2016/09/01
     * 智绘首饰
     */
    public function smartjewelry(){
        $mmode=I('mmode',0,'intval');
        $pid=I('pid',0,'intval');
        $showtype=I('showtype',0,'intval');
        $TPM= new ProductModel();
        if($pid){
            $udinfo=M("user_diy")->where("id=".$pid)->find();
            $cid=$udinfo['cid'];
        }else{
            $cid=I('cid',0,'intval');
            $productInfo=$TPM->getProductByDiyCateCid($cid);
            //no webgl
            $this->assign("cpid",$productInfo['p_id']);
        }
        //获取TDK信息 start
        $TPCM=new ProductCateModel();
        $TDK=$TPCM->getCateTDKByPcate($productInfo['p_cate'],$productInfo['p_name']);
        $this->assign("TDK",$TDK);
        //获取TDK信息 end

        //产品缩略图
        $cinfo=M('diy_cate')->where("cid=".$cid)->find();
        $iconArr=explode(',',$cinfo['cate_icon']);
        foreach($iconArr as $key => $value){
            $cinfo['icon'][$key]=getimgbyID($value);
        }

        //作者信息(头像、签名)
        $Users = new UsersModel ();
        $Users->find ($cinfo['u_id']);
        $UP = $Users->getUserProfile ();
        $UA = $Users->getUserAcc ();
        $this->assign ( 'userBasic_au', $Users->data () );
        $this->assign('userProf',$UP->data());

        //推荐款式产品
        $Prodlist=$this->getProd(5,$cid);
        $this->assign ( 'DBF_P', $this->DBF->Product );
        $this->assign('Prodlist',$Prodlist);
        //推荐款式产品

        $this->assign('showtype',$showtype);
        $this->assign('pid',$pid);
        $this->assign('cid',$cid);
        //var_dump($cinfo);
        $this->assign('cinfo',$cinfo);
        $this->assign('mmode',$mmode);

        //为Nav显示高亮新增参数,不影响其他操作，勿删-Start
        $this->assign('sjshownav',1);
        //为Nav显示高亮新增参数-End

        $this->_renderPage();
    }


	public function snapjewelry(){//
		$pid=I('pid',0,'intval');
		$showtype=I('showtype',0,'intval');
		$upid=I('upid',0,'intval');
		
		$this->assign('showtype',$showtype);
		$this->assign('pid',$pid);
		$this->assign('upid',$upid);
		$this->_renderPage();
	}
	
	
	//得到产品列表
	public function getProd($count,$cid){
		@load ( '@.SearchParser' );
		$SP = new SearchParser ();
		$SP->parseUrlInfo ( true );
		$SearchInfo = $SP->SearchInfo;
		$SearchInfo ['page'] = $this->_get ( 'page' );
		$SearchInfo ['count'] = $count;
		$SearchInfo ['cateory'] = '1263';
		$PSM = new ProductSearchModel ( $SearchInfo, 'model', true );
		$SearchResult=$PSM->getResult ( $SP->SearchInfo ['page'] );
		$isun=0;
		foreach($SearchResult as $key =>$value){
			if($value['p_diy_cate_cid']==$cid){
				unset($SearchResult[$key]);
				$isun=1;
			}
		}
		if($isun!==1){//如果没有unset数组，就去除最后一个数组元素
			array_pop($SearchResult);
		}
		return $SearchResult;
	}

    /**
     * @return array|mixed
     * param  $cid 首饰类别  如果为1 则是简笔画,只读取银的材质数据
     *
     */
	private function getMcate($cid){//获取打印材料数组，包括材料对应的公式
		if($cid==1){
            $where="and TPM.pma_id=127 ";
        }else{
            $where="";
        }
        $sql="select TPM.pma_id,TPM.pma_name as TPM_name,TPMP.pma_name as TPMP_name,TPM.pma_unitprice,TPM.pma_density,TPM.pma_startprice,TPM.pma_diy_formula_s,TPM.pma_diy_formula_b,TPM.pma_necklace_price,TPM.pma_image,TPM.pma_jbh_formula from tdf_printer_material as TPM ";
		$sql.="Left Join tdf_printer_material as TPMP ON TPMP.pma_id=TPM.pma_parentid ";
		$sql.="where TPM.pma_type=1 and TPM.ishidden=0 ";
		$sql.=$where;
		$sql.="order by TPM.pma_weight ASC ";
		$mcate=M("printer_material")->query($sql);//打印材料数组，必须
		return $mcate;
	}

    private function getMcateIDKey($mArr){//根据材料数组以材料ID为key重组
        foreach($mArr as $key => $value){
            $result[$value['pma_id']]=$value;
        }
        return $result;
    }
	
	private function getUdinfoById($id){//根据id值获得user_diy中的数据
		$udinfo=M('user_diy')->where('id='.$id)->find();
		return $udinfo;
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
	
	public function jewelryeditall(){//DIY编辑器
        $dotserver=DOTSERVER;
        $this->assign('DOTSERVER',$dotserver);
        //----------------------------------------你画我猜 图片参数-------start-------
        $img    = base64_decode(I("img","0","string"));
        $this->assign("img",$img);
        //----------------------------------------你画我猜 图片参数-------end-------

        $sendmsg    = I("sendmsg",0,'intval');
        $this->assign("sendmsg",$sendmsg);
        $currentUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $this->assign("currentUrl",$currentUrl);//当前完整url
        $agentId    = I('agentid','0','string'); //代理商ID,从url参数获取

        if($agentId){   //如果存在代理商ID,先生成productKey
           $productKey  =getRandOnlyId();
           //$productKey ="abc";
           $this->assign('productKey',$productKey);
        }
        $this->assign('agentId',$agentId);
        //-----------------------根据code记录页面访问日志  start-----
       $code=I('code','0','string');
       if($code){
           if($this->saveLogActive($code)){ $this->assign('code',$data['code']);}
       }
        //-----------------------根据code记录页面访问日志  end-------
		$mmode          = I('mmode',0,'intval');//页面类型 0为pc 1为手机
		$udid           = I("pid",0,"intval");//tdf_user_diy中的ID
		$showtype       = I('showtype',0,'intval');
        $clientStorage  = I("clientStorage",'0',"string");
        $clientUserId   = I("clientUserId",'0',"string");
        $clienttype     = 0;
        $clienttype     = I("clienttype",0,'intval'); //如果clienttype是2则为APP客户端ios版本
        //新增参数
        $this->assign('mtype',$this->_get('type'));
        $PM=new ProductModel();
        if($clientUserId){//第三方商户(麦德龙/柯蓝)内嵌iframe
            $stype=1;
            $clienttype=1;
            //$clientUser_pid=I("pid",0,"intval");
            //$udid=$PM->getUdidByPid($clientUser_pid);
        }
        if($agentId){//触屏售卖机
            $clienttype = 3;
            $enforceWeb = 1;
        }
        $this->assign("enforceWeb",$enforceWeb);
        $this->assign("clienttype",$clienttype);
        $this->assign("stype",$stype);
        $this->assign("clientUserId",$clientUserId);
        $User = new UsersModel ();
		if($agentId){//如果是触摸屏打开浏览器所带的参数
            $userInfoByAgentId=$User->getUserInfoByAgentId($agentId);
            //dump($userInfoByAgentId);
            //exit;
            $u_id=$userInfoByAgentId['u_id'];
            $UserData = $User->getUserByID ( $u_id );
            session ( 'f_userid', $UserData ['u_id'] );
            session ( 'f_nickname', $UserData ['u_dispname'] );
            session ( 'f_logindate', time () );
            //echo $u_id;
            //exit;
        }else{
            if($clientUserId){
                $clientUserId=pub_encode_pass($clientUserId,'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm','decode');
                $u_id=$clientUserId;
                $UserData = $User->getUserByID ( $u_id );
                session ( 'f_userid', $UserData ['u_id'] );
                session ( 'f_nickname', $UserData ['u_dispname'] );
                session ( 'f_logindate', time () );
            }else{
                $u_id=$_SESSION ['f_userid'];
            }
        }

        $cid=I('cid',0,'intval');
		if(!$cid){
			$udinfo=$this->getUdinfoById($udid);
			$cid=$udinfo['cid'];
		}

        $mcate=$this->getMcate($cid);//材质数组(必须)
        $mcateIDKey=$this->getMcateIDKey($mcate);
        $productInfo=$PM->getProductByDiyCateCid($cid);
        $this->assign('productInfo',$productInfo);
        $DC=M('diy_cate')->where("cid=".$cid)->find();//diy产品类型
		$DU=M('diy_unit')->where('cid='.$cid.' and delsign=0')->order('sort')->select();//选择tdf_diy_unit
		$dataArr['diy_cate']=$DC;
        //-----------------------判断是否为镶钻类DIY start
        if($DC['cate_type']==1){//Sweet焕彩耳钉  如果是镶钻DIY，转至镶钻DIY模板
            $templates='diamondeditall';
        }elseif($DC['cate_type']==2){//甜甜圈
            $templates='tianeditall';
        }elseif($DC['cate_type']==3){//大小宝石焕彩戒指
            $templates='bigsmalleditall';
        }elseif($DC['cate_type']==4){
            $templates='bigsmallpendanteditall';
        }elseif($DC['cate_type']==5){ //简笔画模板
            $templates='draweditall';
            $jbh_formula=pub_encode_pass($mcate[0]['pma_jbh_formula'],'1');
            $this->assign('jbh_formula',$jbh_formula);
        }elseif($DC['cate_type']==6){
            //$templates='./App/templates/Front/micky/Help/shopflow.html';
            $templates="jewelryeditnew";
        }
        //$csid=session_id();//客户的session_id 用于不登录保存用户方案

        $price_count=$DC['price_count']?$DC['price_count']:1;//计算价格之单位个数
        $UCM=new UserCartModel();
        //-----------------------判断是否为镶钻类DIY end

		if($_POST) {//如果post数据
            $mtype = I('mtype','','string');//页面访问类型 iwx为新版手机端 空为旧版
            if($clientStorage!=="ClientVisitIdentity") {
                if (!$this->_isLogin()) {
                    if ($_POST['Textvalue']) {
                        $_POST['Textvalue'] = utf8_unicode($_POST['Textvalue']);
                    }
                    if ($_POST['Backtext']) {
                        $_POST['Backtext'] = utf8_unicode($_POST['Backtext']);
                    }
                    cookie('value', $_POST, array('expire' => 3600, 'prefix' => 'DIY'));
                    // 登录后继续跳转到iframe的父框架-----------------start
                    if ($this->_get('reqtype') == 'ajax') {
                        $result ['isSuccess'] = false;
                        $result ['Reason'] = '0001'; // 需要登录
                        echo json_encode($result);
                        exit ();
                    } else {
                        //如果是APP的调用，就继续执行
                        if (is_weixin()) {
                            echo("<script>window.parent.location.href='" . WX_CALLBACK_DOMAIN . "/index/auth-wx_launch?jump_uri=" . WEBROOT_URL . "/index/diy-jewelryeditall-cid-" . $cid . "-mmode-1';</script>");
                        } elseif (is_mobile()) {
                            //第三版手机版
                            if($mtype == 'iwx'){
                                echo("<script>window.parent.location.href='" . WEBROOT_URL . "/user.php/login/?type=iwx"."&"."from_url=" . WEBROOT_URL . "/index/diy-jewelryeditall-cid-" . $cid . "-mmode-1-type-iwx';</script>");
                            }else{
                                //第二版手机版
                                echo("<script>window.parent.location.href='" . WEBROOT_URL . "/user.php/login/?from_url=" . WEBROOT_URL . "/index/diy-jewelryeditall-cid-" . $cid . "-mmode-1';</script>");
                            }
                        } else {
                            echo("<script>window.parent.location.href='" . WEBROOT_URL . "/user.php/login/?from_url=" . WEBROOT_URL . "/index/diy-jewelry-cid-" . $cid . "';</script>");
                        }
                    }
                    // 登录后继续跳转到iframe的父框架------------------end
                    //$this->_needLogin();
                    exit;
                }
            }
            
            foreach($DU as $key =>$value){
                if($value['unit_material']){//如果有tdf_diy_unit中unit_material有值则需要保存部件的材质
                    $diyinfo[$value['id']]['value']=$_POST[$value['unit_name']];//获取post来的数据_部件值
                    $diyinfo[$value['id']]['material']=$_POST[$value['unit_name']."_material"];//获取post来的数据_部件材质值
                    $diyinfo[$value['id']]['visiable']=$_POST[$value['unit_name']."_visiable"];//获取post来的数据_部件材质值
                }else{
                    $diyinfo[$value['id']]=$_POST[$value['unit_name']];//获取post来的数据
                }
			}

			$data=$_POST;
            if(!$data['cover']){$data['cover']=$productInfo['p_cover'];}
			$data['title']			=$_POST['Textvalue']?$_POST['Textvalue']:"首饰定制";
			$data['u_id']			=$u_id;
			$data['diy_unit_info']	=serialize($diyinfo);
			$data['price']			=$_POST['price'];
            $data['cid']			=$_POST['cid'];
            $data['id']			    =$udid;

            if($DC['isdiamond']){
                if(!$this->into_price($data,$DU,$price_count)){
                   $this->error("价格计算有误！");
                }
            }//镶钻DIY加入价格计算验证
			if($_POST['pid']){//如果有id，执行更新
				$result=M('user_diy')->where("id=".$data['pid'])->save($data);
				$diy_id=$_POST['pid'];
			}else{//无ID进行数据增加
				$result=M('user_diy')->add($data);
				$diy_id=$result;
			}
			if($_POST['stype']==1){	//如果stype为1则为购买，加入到购物车
                //exit;
                $p_data['p_name']			= $data['title'];
				$p_data['p_cate_4']			= intval($data['cid']);//产品类别
				$p_data['p_creater']		= $u_id;
                $p_data['p_cover']			= $data['cover'];
         		$p_data['p_price']			= $data['price'];
				$p_data['p_createdate']		= date("Y-m-d G:i:s",time());
				$p_data['p_createtime']		= time();
				$p_data['p_lastupdate']		= date("Y-m-d G:i:s",time());
				$p_data['p_lastupdatetime']	= time();
				$p_data['p_producttype']	= 4;//产品类型 1为数字模型 2为实物商品(3d打印机) 3为打印件 4为DIY产品
				$p_data['p_diy_id']			= $diy_id;
                if($data['productKey']){
                    $p_data['p_key']    	= $data['productKey'];
                }

                $PM=new ProductModel();
				if($PM->getProductByDiyid($p_data['p_diy_id'])){//如果存在有diy_id,修改更新
					if($PM->where("p_diy_id=".$p_data['p_diy_id']."")->save($p_data)){
						$pm_info=$PM->getProductByDiyid($p_data['p_diy_id']);
						$pid=$pm_info['p_id'];
					}
				}else{//如果没有diy_id，则新增
					$pid=$PM->add($p_data);
				}

                //--------------------------加入统计code的日志 start
                $data_c['code']=$_POST['code'];
                if($data_c['code']){
                    $data_c['ip']=get_client_ip();
                    $data_c['url']=__SELF__ ;
                    $data_c['type']=1;
                    $data_c['sessionid']=session_id();
                    M('log_active')->add($data_c);
                }
                //--------------------------加入统计code的日志 end

                //---------------------------APP直接从diy产品生成订单------start
                if($data['productKey']){//判断是否为APP加入购物车
                    $product_c=$this->procuct_cart_order($pid);//APP直接生成订单
                }else{
                    $isAdded = $UCM->addProduct_diy( $pid, $u_id );
                }
                //---------------------------APP直接从diy产品生成订单------end

				$jurl=str_replace("jewelryeditall", "jewelry", get_url()); //替换jewelryeditall为jewelry
				$JUMP_URL=pub_encode_pass($jurl,"3dcity");//加码加密
                if(intval($_POST['mmode'])==1){
					/*直接从购物车中生成订单
					$SA = A ( "User/Sales" ); // 调用user分组下的sales模块
					$temp_orderid = $SA->get_umorderid ();
					$oid = $temp_orderid; // 加密orderid
					$this->WX_cart_order($oid,$_SESSION ['f_userid']);//微信中购物车直接生成订单
					$oid_encode=pub_encode_pass( $oid, $_SESSION ['f_userid']); // 加密orderid
					echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/wxuser/address/oid/".$oid_encode."/jurl/".$JUMP_URL."';</script>");
					*/
                    if($mtype == 'iwx'){
                        echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/cart/index/mmode/1/type/iwx/jurl/".$JUMP_URL."';</script>");
                    }else{
                        echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/cart/index/mmode/1/jurl/".$JUMP_URL."';</script>");
                    }
					exit;
				}else{
                    if($clientUserId==1) {//是否为第三方的转向
                        echo ("<script>window.location.href='".WEBROOT_URL."/index/diy-jewelryeditall-pid-".$diy_id."-showtype-10-clientUserId-1-sendmsg-1.html';</script>");
                    }else{
                        if($clientStorage =="ClientVisitIdentity") {
                            //echo "abc";
                            exit;
                            echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/cart/index/jurl/".$JUMP_URL."';</script>");
                            exit;
                        }else{
                            if($agentId){//如果有agentId,为安卓贩卖机版本,则转向二维码扫码页面
                                $productKey  =I("productKey","0","string");
                                echo ("<script>window.parent.location.href='".WEBROOT_URL."/index/index-showordercode-pkey-".$productKey."';</script>");
                            }else{
                                //echo "abc";
                                echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/cart/index/jurl/".$JUMP_URL."';</script>");
                                exit;
                            }
                            exit;
                        }
                    }
                }
				//echo ("<script>window.open('".WEBROOT_URL."/user.php/cart','_blank')</script>");
				//redirect (  WEBROOT_URL."/user.php/cart");
			}else{
				if(intval($_POST['mmode'])==1){//如果是微信打开，转向分享页面
					echo ("<script>window.parent.top.location.href='".WEBROOT_URL."/index/wx-diysnap-pid-".$diy_id."-mmode-1.html';</script>");
				}else{
                    if($clientUserId==1){//是否为第三方的转向
                        //var_dump($diyinfo);
                       // var_dump($dataArr);
                       // var_dump($DU);
                        exit;
                       // echo ("<script>window.location.href='".WEBROOT_URL."/index/diy-jewelryeditall-pid-".$diy_id."-showtype-10-clientUserId-1-sendmsg-1.html';</script>");
                    }else{
                        echo ("<script>window.parent.top.location.href='".WEBROOT_URL."/user.php/mydiy/jewelrylist.html';</script>");
                    }
                }
			}
		}else{ //显示diy
			if($udid){//如果存在pid（pid通过参数传输过来）
				$udinfo=M('user_diy')->where('id='.$udid)->find();
				$fieldvalue=$this->get_udinfo_all($udinfo,$cid);//用户diy数据
                if($fieldvalue['cid']==1){
                    $projectJsonPath=$fieldvalue['Content'];
                }
                $productInfo=$PM->getProductByDiyid($udid);
                $productId=$productInfo['p_id'];
                //echo "<pre>";var_dump($udinfo);echo "<pre>";
                //echo "<pre>";var_dump($fieldvalue);echo "<pre>";
            }else{
               if($_COOKIE['DIYvalue']){//如果存在
					$defaultvalue= explode('think:', $_COOKIE['DIYvalue']);//去除think:
					cookie(null,'DIYvalue');//清除cookie
					$defaultvalue=json_decode($defaultvalue[1],TRUE);//json格式转换为数组
					if($defaultvalue['Textvalue']){
						$defaultvalue['Textvalue']=str_replace('%5C','\\',$defaultvalue['Textvalue']);//替换斜线
						$defaultvalue['Textvalue']=unicode_decode($defaultvalue['Textvalue']);//解为utf8
					}
					if($defaultvalue['Backtext']){
						$defaultvalue['Backtext']=str_replace('%5C','\\',$defaultvalue['Backtext']);//替换斜线
						$defaultvalue['Backtext']=unicode_decode($defaultvalue['Backtext']);//解为utf8
					}
				}else{
					$defaultvalue=$this->getDefaultValueByDu($DU);//默认值
				}
				$udinfo=$defaultvalue;
                $fieldvalue=$defaultvalue;
			}
			$udinfo['startprice']=$DC['startprice'];//赋值起打价格
		}

        $TDM=new DiyDiamondModel();
        $DPM=new DiyPendantModel();
       /*//-------------------所有宝石读取为一个数组，不随位置变化动态读取宝石------------------start------------
        $diamondAll=$TDM->getDiyDiamondByArrId(str_replace(';',',',$DC['diamond'])); //读取产品配置的可选宝石
        //var_dump($diamondAll);
        $this->assign('diamondAll',$diamondAll);
        //-------------------所有宝石读取为一个数组，不随位置变化动态读取宝石------------------end--------------*/
		foreach($DU as $key =>$value){
			if($key!==count($DU)){
				$DUArray[$key]=$value;
				$DUArray[$key]['next_fieldgroup']=$DU[$key+1]['fieldgroup'];//拼接处理next_fieldgroup
			}
            $DUArray[$key]['fieldvalue'] = $fieldvalue[$value['unit_name']];//赋值
		    //--------------------------------------新增加unit部件的值和材质值的判断-----------------------start---
            /* if($value['unit_material']){
                $DUArray[$key]['fieldvalue']            = $fieldvalue[$value['unit_name']]['value'];
                $DUArray[$key]['fieldvalue_material']   = $fieldvalue[$value['unit_name']]['material'];
                $DUArray[$key]['fieldvalue_visiable']   = $fieldvalue[$value['unit_name']]['visiable'];
                //if(!$DUArray[$key]['ishidden']){$DUArray[$key]['fieldvalue_visiable']   = 1;}
            /* }else{
                $DUArray[$key]['fieldvalue'] = $fieldvalue[$value['unit_name']];
                //if(!$DUArray[$key]['ishidden']){$DUArray[$key]['fieldvalue_visiable']   = 1;}
            }*/
            //--------------------------------------新增加unit部件的值和材质值的判断------------------------end---
			if($value['fieldtype']=="SELECT"){//如果是SELECT,构造selectarr
                if($value['unit_name']=='Size'){
                    $haveSize=1;
                    $this->assign('haveSize',$haveSize);
                }
				$DUArray[$key]['unit_value_arr']=$this->getUnitValueArr($value['unit_value']);
                $DUArray[$key]['fieldvalue_show']=$this->getUnitFieldValueShow($DUArray[$key]['unit_value_arr'],$DUArray[$key]['fieldvalue']);

			}
			if($value['unit_name']=='Chaintype'){//如果是项链样式,构造链子的默认值数组
				$DUArray[$key]['unit_default_arr']=$this->getUnitDefaultArr($value['unit_default']);
				$unit_default_arr=json_encode($DUArray[$key]['unit_default_arr']);//链子默认值数组
				if($udid){
					$chaintypeCurrentV=$DUArray[$key]['fieldvalue'];
				}else{
					$chaintypeCurrentV=0;
				}
			}
			if($value['fieldtype']=='MATERIAL'){//从材质配置中取出材质的默认值
              	$mcateDefault=$value['unit_default'];
                $MaterialDefaultArr=explode(';',$mcateDefault);
                if(count($MaterialDefaultArr)==1){//判断是否使用所有材质，如果此处不为1，则$mcateType为1,需要重新组合mcate
                    $mcateType=0;
                }else{
                    if($udid){
                        $DUArray[$key]['fieldvalue']=$mcateIDKey[$DUArray[$key]['fieldvalue']]['pma_id']; //材料默认值写入fieldvalue
                        $DUArray[$key]['fieldvalue_name']=$mcateIDKey[$DUArray[$key]['fieldvalue']]['TPM_name']; //材料显示名称
                        $DUArray[$key]['fieldvalue_pic']=$mcateIDKey[$DUArray[$key]['fieldvalue']]['pma_image']; //材料显示图片
                    }else{
                        $DUArray[$key]['fieldvalue']=$MaterialDefaultArr[0]; //材料默认值写入fieldvalue
                        $DUArray[$key]['fieldvalue_name']=$mcateIDKey[$MaterialDefaultArr[0]]['TPM_name']; //材料显示名称
                        $DUArray[$key]['fieldvalue_pic']=$mcateIDKey[$MaterialDefaultArr[0]]['pma_image']; //材料显示图片
                    }
                    $mcateType=1;
                }
                $MaterialDefault=$MaterialDefaultArr[0];
			}

            if($value['fieldtype']=='PENDANT'){
                $havePendant=1;//是否有吊坠选择
                $pendantGet=I('pendant','','string');
                if($pendantGet){$DUArray[$key]['fieldvalue']=$pendantGet;}

                $pendantDefault=$value['unit_diamond'];
                $pendantDefault=str_replace(';',',',$pendantDefault);//替换分号为逗号
                $pendantArray[$value['id']]=$DPM->getDiyPendantByArrId($pendantDefault);
                if($udid){
                    $DUArray[$key]['fieldvalue_stylePendantValue'] = $this->getStylePendantValue($fieldvalue[$value['unit_name']],$DPM);
                    $pendantPrice[$value['id']]=$DPM->getPriceArr($fieldvalue[$value['unit_name']], $pendantArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],1,$price_count);//获取吊坠价格
                }else{
                    $pendantPrice[$value['id']]=$DPM->getPriceArr($fieldvalue[$value['unit_name']], $pendantArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],1,$price_count);//获取吊坠价格
                    $DUArray[$key]['fieldvalue_stylePendantValue'] = $this->getStylePendantValue($DUArray[$key]['unit_default'],$DPM);

                }
                //$pendantArray[$value['id']]=$TDM->getDiyDiamondByArrId($fieldvalue[$value['unit_name']]);
                //$diamondArrayJson=json_encode($diamondArray);
                //var_dump($pendantPrice);
            }

            //--------------------------------------------宝石配置读取start
            if($value['fieldtype']=='DIAMOND'){//读取出宝石
                $diamondDefault=$value['unit_diamond'];
                $diamondDefault=str_replace(';',',',$diamondDefault);//替换分号为逗号
                $diamondArray[$value['id']]=$TDM->getDiyDiamondByArrId($diamondDefault);
                $diamondArrayJson=json_encode($diamondArray);

                if($udid){
                    $diamondPosDid[$value['id']]=$TDM->getDiamondInfoByid($fieldvalue[$value['unit_name']]);//方案中的宝石位置和宝石ID
                    $diamondPosDidImg[$value['id']]=$fieldvalue[$value['unit_name']];//方案中的宝石位置和宝石ID
                    if($value['unit_material']) {//如果有tdf_diy_unit中unit_material有值则需要读取部件的材质（数组读取）
                        //--------------------------------------新增加unit部件的值、材质值、是否显示值-----------------------start---
                        $DUArray[$key]['fieldvalue']            = $fieldvalue[$value['unit_name']]['value'];
                        $DUArray[$key]['fieldvalue_styleDiamondValue'] = $this->getStyleDiamondValue($fieldvalue[$value['unit_name']]['value'],$TDM);
                        $DUArray[$key]['fieldvalue_material']   = $fieldvalue[$value['unit_name']]['material'];
                        $DUArray[$key]['fieldvalue_visiable']   = $fieldvalue[$value['unit_name']]['visiable'];
                        //--------------------------------------新增加unit部件的值、材质值、是否显示值-----------------------start---
                        //--------------------------------------获取价格数组(如果unit_material有值则取键为value)---------start-----
                        $visiable=$DUArray[$key]['fieldvalue_visiable']?$DUArray[$key]['fieldvalue_visiable']:1;

                        $diamondPrice[$value['id']]=$TDM->getPriceArr($fieldvalue[$value['unit_name']]['value'], $diamondArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],$visiable,$price_count);//获取宝石价格
                        //--------------------------------------获取价格数组(如果unit_material有值则取键为value)---------end-----
                    }else{
                        //--------------------------------------新增加unit部件的值、材质值、是否显示值-----------------------start---
                        $DUArray[$key]['fieldvalue']            = $fieldvalue[$value['unit_name']];
                        $DUArray[$key]['fieldvalue_styleDiamondValue'] = $this->getStyleDiamondValue($fieldvalue[$value['unit_name']],$TDM);

                        //--------------------------------------新增加unit部件的值、材质值、是否显示值-----------------------start---
                        //--------------------------------------获取价格数组(如果unit_material无值或为0则直接取值)---------start-----
                        $diamondPrice[$value['id']]=$TDM->getPriceArr($fieldvalue[$value['unit_name']], $diamondArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],1,$price_count);//获取宝石价格
                        //--------------------------------------获取价格数组(如果unit_material无值或为0则直接取值)---------end-----
                    }
                    //var_dump($value['id']);
                    //var_dump($fieldvalue);
                    //var_dump($value['unit_name']);
                    //$diamondPrice[$value['id']]=$TDM->getPriceByPosDidArr($fieldvalue[$value['unit_name']]['value'], $diamondArray[$value['id']]);//获取宝石价格
                }else{
                    //如果是镶钻的部件，更新部件的相关值(fieldvalue和fieldvalue_material)用于前台页面显示----start
                    $DUArray[$key]['fieldvalue']= $DUArray[$key]['unit_default']; //部件值为部件的默认值
                    $DUArray[$key]['fieldvalue_styleDiamondValue'] = $this->getStyleDiamondValue($DUArray[$key]['unit_default'],$TDM);

                    $DUArray[$key]['fieldvalue_material']= $DUArray[$key]['unit_material_value'];//部件材质值为部件材质的默认值
                    if(!$DUArray[$key]['ishidden']){$DUArray[$key]['fieldvalue_visiable']   = 1;}//部件默认是否显示
                    //------------------------------------------------------------------------------end
                    $diamondPrice[$value['id']]=$TDM->getPriceArr($DUArray[$key]['unit_default'],$diamondArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],$DUArray[$key]['fieldvalue_visiable'],$price_count);//获取宝石价格
                    //$diamondPrice[$value['id']]=0;
                }
            }
            //--------------------------------------------宝石配置读取end
		}
        if($pendantPrice){
            $diamondPrice=$diamondPrice+$pendantPrice;
        }else{
            $diamondPrice=$diamondPrice;
        }

        $diamondPriceJson=json_encode($diamondPrice);
        $this->assign('diamondPrice',$diamondPrice);
        $this->assign('diamondPriceJson',$diamondPriceJson);
        $this->assign('diamondPosDid',$diamondPosDid);//方案中的宝石位置和宝石ID
        $this->assign('diamondinfo',$diamondArray);
        $this->assign('diamondinfoJson',$diamondArrayJson);
        /*通过$mcateType判断是否使用所有材质，如果不使用全部材质，进行过滤mcate*/
        if($mcateType==1){
            foreach($mcate as $mkey => $mvalue){
               if(!in_array($mvalue['pma_id'],$MaterialDefaultArr)){
                 unset($mcate[$mkey]);
               }
            }
        }

		$dataArr['diy_unit']=$DUArray;
        //var_dump($mcate);
        $dataArr['material']=$mcate;
       /* if($havePendant){
            $pendantAll=$DPM->getDiyPendantAll();
        }*/

        $dataArr['pendant']=$pendantArray;//所有吊坠集合
		//如果是项链，增加链子参数-- start --------------------------------------<
		if($dataArr['diy_cate']['cate_group']==2){
			$DN=new DiyNecklaceModel();
			$necklace=$DN->getDiyNecklace();
			$necklace_json=json_encode($necklace);
			$dataArr['necklace']=$necklace_json;
    			$show_neck="<script>";
				//$show_neck.="var neck=new Array();";
				//foreach($necklace_json as $key1 => $value1){
				$show_neck.="var neck=".$necklace_json.";";
				$show_neck.="var neck_current=".$chaintypeCurrentV.";";
				$show_neck.="var neck_default=".$unit_default_arr.";";
			    //}
			$show_neck.="</script>";


			$necklaceDefaultArr=(json_decode($unit_default_arr));

			$nDefaultID=$necklaceDefaultArr->$MaterialDefault;
			$neckdefaultArr=$DN->getNecklaceByPmaid($MaterialDefault);
            //var_dump($neckdefaultArr);
			$this->assign("show_neck",$show_neck);
		}
		//如果是项链，增加链子参数--- end -------------------------------------->

		//增加附加字符------- start -->
		if($dataArr['diy_cate']['fontext']=='0,0,0' || $dataArr['diy_cate']['fontext']==0){
			$dataArr['diy_cate']['fontext']='';
		}else{
			$fontextArr=explode(",",$dataArr['diy_cate']['fontext']);
			$dataArr['diy_cate']['fontextArr']=$fontextArr;
		}
		//增加附加字符------- end -->
		$fontsArr=explode(',',$dataArr['diy_cate']['fonts']);
		if($fontsArr){
			$dataArr['fontsArr']=$fontsArr;
		}else{
			$dataArr['fontsArr'][0]=$dataArr['fonts'];
		}
		if($this->_isLogin()){
			$islogin=1;
		}else{
			$islogin=0;
		}
        //var_dump($pendantArray);
        //echo "<pre>";print_r($dataArr);echo "<pre>";
        //print_r($dataArr);
        //exit;

        /*echo "<table>";
        foreach($dataArr['diy_unit'] as $t1 => $v1){
            echo "<tr><td>";
            echo $v1;
            echo "</td></tr>";
        }

        echo "</table>";*/

        Vendor('wxshare.jssdk');
        $jssdk = new JSSDK("wxf71f9222d3bc3c2e", "7338ad0158221be35c8572c562a56899");
        $signPackage = $jssdk->GetSignPackage();
        $shareInfo['title']=$productInfo['p_name'];
        $shareInfo['desc']="首饰也可以DIY啦！一起来3DCITY玩转3D打印和DIY首饰的乐趣...";
        $shareInfo['link']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $shareInfo['imgUrl']=WEBROOT_URL.$productInfo['p_cover'];
        $this->assign("shareInfo",$shareInfo);
        $this->assign('signPackage',$signPackage);


        //获取diy后的参数，用于返回给第三方-----------------------start---------------------
        $diyInfoArr=$UCM->getDiyInfoByDataArr($dataArr,$udid);
        $diyInfoJson=json_encode($diyInfoArr);
        $this->assign("diyInfoJson",$diyInfoJson);
        //var_dump($diyInfoArr);
        //exit;
        //获取diy后的参数，用于返回给第三方-----------------------end-----------------------

		$materialId2Name = array(
			127 => "925银",
			128 => "18K玫瑰金",
			129 => "18K白金",
			130 => "18K黄金"
		);
		$this->assign("materialId2Name", $materialId2Name);
        //单个首饰产品的倍率，用于单独调价 2016-2-29 by zhangzhibin
        if($dataArr['diy_cate']['price_per']==0 || !$dataArr['diy_cate']['price_per']){
            $dataArr['diy_cate']['price_per']=1;
        }
        $this->assign("cid",$cid);
		$this->assign('isLogin',$islogin);
		$this->assign('showtype',$showtype);
		$this->assign('dataAll',$dataArr);
		$this->assign('udinfo',$udinfo);
		$this->assign('mcate',$mcate);
        //浏览器判断
        if (is_weixin ()) {
            $mobileAgent = 1;
            $mmode=1;
        } elseif (is_mobile ()) {
            $mobileAgent = 2;
            $mmode=1;
        } else {
            $mobileAgent = 3;
        }
        $u_id=$u_id?$u_id:0;
        $projectJsonPath=$projectJsonPath?$projectJsonPath:'/upload/project/20160426/jsonfile/3d2e88834544ac04.json';
        $udid=$udid?$udid:0;
        $this->assign('type',I('type'));
        $this->assign("mmode",$mmode);
        $this->assign ( 'mobileAgent', $mobileAgent );
        $this->assign('udid',$udid);
        $this->assign('productId',$productId);
        $this->assign('uid',$u_id);
        $this->assign("projectJsonPath",$projectJsonPath);
        $this->display($templates);
		//$this->_renderPage();
	}


/*
 * 产品直接生成订单
 */
    public function procuct_cart_order($pid){
        $temp_orderid = $this->get_umorderid ();
        $oid = $temp_orderid; // 加密orderid
        $TPM=new ProductModel();
        $Product=$TPM->getProductByID($pid);
        //$product_cart=$UCM->getProductByPidUid($pid,$uid);
        $ProductList [0]=$Product;
        $up_express = 1;	// 是否需要快递
        $tempcount = 1;// 产品数量为1
        $TotalPrice=$Product['p_price']*$tempcount;
        $pid_array [] = $Product [$this->DBF->Product->ID] . "," . $tempcount; // product的id数组
        if ($Product ['p_producttype'] == 4) { // 如果是DIY件，需保存DIY的快照参数(增加tdf_user_diy中的diy_unit_info和cover、price字段)
            $udinfo = M ( 'user_diy' )->where ( 'id=' . $ProductList [0] ['p_diy_id'] )->find ();
            $ProductList [0] ['diy_unit_info'] = $udinfo ['diy_unit_info'];
            $ProductList [0] ['cover'] = $udinfo ['cover'];
            $ProductList [0] ['price'] = $udinfo ['price'];
            $ProductList [0] ['uc_producttype'] = 4;
            $ProductList [0] ['uc_count'] = 1; //数量为1
            $ProductList [0] ['uc_id'] = 1;//模拟购物车ID为1
            $ProductList [0] ['uc_isreal'] = 0;
            $ProductList [0] ['uc_lastupdate'] = time();
            $ProductList [0] ['uc_ctime'] = 0;
            $ProductList [0] ['uc_isbind'] = 0;
            $ProductList [0] ['uc_bindids'] = 0;
            $ProductList [0] ['uc_masterid'] = 0;
            $ProductList [0] ['uc_handleuc'] = 0;
            $ProductList [0] ['p_count'] = 1;
        }
        $up_product_info = serialize ( $ProductList ); // 存储到订单商品快照中的商品信息
        $up_amount_save = $TotalPrice;
        $IP = get_client_ip ();

        $UPM = new UserPrepaidModel ();
        $UPD = new UserPrepaidDetailModel ();
        $UPM->startTrans (); // 在d模型中启动事务
        $uid=$_SESSION ['f_userid'];
        $up_type=4;
        $upid = $UPM->addRecord ( $uid, $up_amount_save, $IP, 0, $oid, 0, serialize ( $pid_array ), $up_type, $up_express );
        $upd_id = $UPD->addRecord ( $upid, $up_product_info );
        $pidUpdate=$UPM->where("up_id=".$upid."")->setField('p_id',$pid);
        if ($upid && $upd_id && $pidUpdate) {
           $UPM->commit (); // 提交事务
        } else {
           $UPM->rollback (); // 事务回滚
        }


    }

    //获取宝石的webGL显示CSS对应ID
    function getStylePendantValue($pendantId,$PDM){
        $pendantInfo=$PDM->getPendantInfoByid($pendantId);
        $pendantStyleArr=L('pendant_style');
        $result=$pendantStyleArr[$pendantInfo['style']];
        return $result;
    }

    //获取宝石的webGL显示CSS对应ID
	function getStyleDiamondValue($diamondId,$TDM){
        $diamondInfo=$TDM->getDiamondInfoByid($diamondId);
        $diamondStyleArr=L('diamond_style');
        $result=$diamondStyleArr[$diamondInfo['style']];
        return $result;
    }

	function into_price($ud,$d_unit_array,$price_count=1){//根据模型信息获取材料单价和比重，并计算出价格带入数组

        $TDM=new DiyDiamondModel();
        $dimondpriceArr=$TDM->getDiyDiamondAll(1);
        $TDM=new DiyPendantModel();
        $pendantpriceArr=$TDM->getDiypendantAll(1);
        //var_dump($ud);
        //var_dump($d_unit_array);
        foreach($d_unit_array as $key => $value){
            $pricePart=0;
            if($value['fieldtype']=='DIAMOND'){ //如果是宝石
                $diamond_num=$value['unit_diamond_count'];
                $diamond_price=$dimondpriceArr[$ud[$value['unit_name']]];
                $price=$value['unit_price'];
                if($value['unit_ischoose']){
                    $isvisiable=$ud[$value['unit_name']."_visiable"];
                }else{
                    $isvisiable=1;
                }
                $pricePart=($price+($diamond_num*$diamond_price)) * $isvisiable;
            }else if($value['fieldtype']=='PENDANT'){
                $diamond_num=$value['unit_diamond_count'];
                $diamond_price=$pendantpriceArr[$ud[$value['unit_name']]];
                $price=$value['unit_price'];
                $isvisiable=1;
                $pricePart=($price+($diamond_num*$diamond_price)) * $isvisiable;
            }
            $totle_price=$totle_price+$pricePart;
        }
        $totle_price=$totle_price*$price_count;
        if($totle_price == $ud['price']){
            return true;
        }else{
            return false;
        }
	}
	
/* 	function viewdetail(){//预览时的显示
		$pid=I("id",0,"intval");
		$PM=new ProductModel();
		$product_info=$PM->getProductByID($pid);
		$udid=$product_info['p_diy_id'];
		$udinfo=M('user_diy')->where('id='.$udid)->find();
		$udinfo=$this->get_udinfo($udinfo);
		$this->assign('udinfo',$udinfo);
		$this->_renderPage();
	} */
	
	/* function snapdetail(){//预览时的显示
		$pid=I("pid",0,"intval");
		$upid=I("upid",0,"intval");
		$PFM=new ProductFileModel();
		$product_file=$PFM->getFileByProduct($pid);
		if($product_file){
			$pfile=$product_file[0]['pf_path'].$product_file[0]['pf_filename'];
			echo "模型已经生成,可直接 <a href='".WEBROOT_URL.$pfile."'>下载</a><br><br>";
		}
		$udinfo=$this->getUdinfoByUpid($upid,$pid);
		$this->assign('pfile',$pfile);
		$this->assign('pid',$pid);
		$this->assign('udinfo',$udinfo);
		$this->_renderPage();
	}
	 */

    public function snapdetailall(){//DIY编辑器
        $mmode      =I('mmode',0,'intval'); //页面类型 0为pc 1为手机
        $product_id       =I("pid",0,"intval");
        $PFM=new ProductFileModel();
        $product_file=$PFM->getFileByProduct($product_id);
        $this->assign("product_id",$product_id);
        if($product_file){
            $pfile=$product_file[0]['pf_path'].$product_file[0]['pf_filename'];
        }
        $this->assign('pfile',$pfile);
        $PM         =new ProductModel();
        $udid       =$PM->getUdidByPid($product_id);   //tdf_user_diy中的ID
        $showtype   =I('showtype',0,'intval');
        $clientUserId =I("clientUserId",'0',"string");
        if($clientUserId){
            $clientUserId=pub_encode_pass($clientUserId,'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm','decode');
            $u_id=$clientUserId;
            $User = new UsersModel ();
            $UserData = $User->getUserByID ( $u_id );
            session ( 'f_userid', $UserData ['u_id'] );
            session ( 'f_nickname', $UserData ['u_dispname'] );
            session ( 'f_logindate', time () );
        }else{
            $u_id=$_SESSION ['f_userid'];
        }

        $mcate=$this->getMcate();//材质数组(必须)
        $mcateIDKey=$this->getMcateIDKey($mcate);

        $cid=I('cid',0,'intval');
        if(!$cid){
            $udinfo=$this->getUdinfoById($udid);
            $cid=$udinfo['cid'];
        }

        $productInfo=$PM->getProductByDiyCateCid($cid);
        $this->assign('productInfo',$productInfo);

        $DC=M('diy_cate')->where("cid=".$cid)->find();//diy产品类型
        $DU=M('diy_unit')->where('cid='.$cid.' and delsign=0')->order('sort')->select();//选择tdf_diy_unit
        $dataArr['diy_cate']=$DC;
        //-----------------------判断是否为镶钻类DIY start
        if($DC['cate_type']==1){//Sweet焕彩耳钉  如果是镶钻DIY，转至镶钻DIY模板
            $templates='diamondeditall';
        }elseif($DC['cate_type']==2){//甜甜圈
            $templates='tianeditall';
        }elseif($DC['cate_type']==3){//大小宝石焕彩戒指
            $templates='bigsmalleditall';
        }elseif($DC['cate_type']==4){
            $templates='bigsmallpendanteditall';
        }
        $price_count=$DC['price_count']?$DC['price_count']:1;//计算价格之单位个数
        //echo $templates;
        //var_dump($DC);
        //-----------------------判断是否为镶钻类DIY end
        if($_POST){//如果post数据
            if (!$this->_isLogin()){
                if($_POST['Textvalue']){$_POST['Textvalue']=utf8_unicode($_POST['Textvalue']);}
                if($_POST['Backtext']){$_POST['Backtext']=utf8_unicode($_POST['Backtext']);}
                cookie('value',$_POST,array('expire'=>3600,'prefix'=>'DIY'));
                // 登录后继续跳转到iframe的父框架-----------------start
                if ($this->_get ( 'reqtype' ) == 'ajax') {
                    $result ['isSuccess'] = false;
                    $result ['Reason'] = '0001'; // 需要登录
                    echo json_encode ( $result );
                    exit ();
                } else {
                    if (is_weixin()){
                        echo ("<script>window.parent.location.href='" . WX_CALLBACK_DOMAIN . "/index/auth-wx_launch?jump_uri=".WEBROOT_URL."/index/diy-jewelryeditall-cid-".$cid."-mmode-1';</script>");
                    }elseif (is_mobile()){
                        echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/login/?from_url=".WEBROOT_URL."/index/diy-jewelryeditall-cid-".$cid."-mmode-1';</script>");
                    }else{
                        echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/login/?from_url=".WEBROOT_URL."/index/diy-jewelry-cid-".$cid."';</script>");
                    }
                }
                // 登录后继续跳转到iframe的父框架------------------end
                //$this->_needLogin();
                exit;
            }

            foreach($DU as $key =>$value){
                if($value['unit_material']){//如果有tdf_diy_unit中unit_material有值则需要保存部件的材质
                    $diyinfo[$value['id']]['value']=$_POST[$value['unit_name']];//获取post来的数据_部件值
                    $diyinfo[$value['id']]['material']=$_POST[$value['unit_name']."_material"];//获取post来的数据_部件材质值
                    $diyinfo[$value['id']]['visiable']=$_POST[$value['unit_name']."_visiable"];//获取post来的数据_部件材质值
                }else{
                    $diyinfo[$value['id']]=$_POST[$value['unit_name']];//获取post来的数据
                }
            }

            $data=$_POST;
            if(!$data['cover']){$data['cover']=$productInfo['p_cover'];}
            $data['title']			=$_POST['Textvalue']?$_POST['Textvalue']:"首饰定制";
            $data['u_id']			=$u_id;
            $data['diy_unit_info']	=serialize($diyinfo);
            $data['price']			=$_POST['price'];
            $data['cid']			=$_POST['cid'];
            $data['id']			    =$udid;

            if($DC['isdiamond']){
                if(!$this->into_price($data,$DU,$price_count)){
                    $this->error("价格计算有误！");
                }
            }//镶钻DIY加入价格计算验证
            if($_POST['pid']){//如果有id，执行更新
                $sresult=M('user_diy')->where("id=".$data['pid'])->save($data);
                $diy_id=$_POST['pid'];
            }else{//无ID进行数据增加
                $result=M('user_diy')->add($data);
                $diy_id=$result;
            }

            if($_POST['stype']==1){	//如果stype为1则为购买，加入到购物车
                $p_data['p_name']			= $data['title'];
                $p_data['p_cate_4']			= intval($data['cid']);//产品类别
                $p_data['p_creater']		= $u_id;
                $p_data['p_cover']			= $data['cover'];
                $p_data['p_price']			= $data['price'];
                $p_data['p_createdate']		= date("Y-m-d G:i:s",time());
                $p_data['p_createtime']		= time();
                $p_data['p_lastupdate']		= date("Y-m-d G:i:s",time());
                $p_data['p_lastupdatetime']	= time();
                $p_data['p_producttype']	= 4;//产品类型 1为数字模型 2为实物商品(3d打印机) 3为打印件 4为DIY产品
                $p_data['p_diy_id']			=$diy_id;
                $PM=new ProductModel();
                if($PM->getProductByDiyid($p_data['p_diy_id'])){//如果存在有diy_id,修改更新
                    if($PM->where("p_diy_id=".$p_data['p_diy_id']."")->save($p_data)){
                        $pm_info=$PM->getProductByDiyid($p_data['p_diy_id']);
                        $pid=$pm_info['p_id'];
                    }
                }else{//如果没有diy_id，则新增
                    $pid=$PM->add($p_data);
                }
                //--------------------------加入统计code的日志 start
                $data_c['code']=$_POST['code'];
                if($data_c['code']){
                    $data_c['ip']=get_client_ip();
                    $data_c['url']=__SELF__ ;
                    $data_c['type']=1;
                    $data_c['sessionid']=session_id();
                    M('log_active')->add($data_c);
                }
                //--------------------------加入统计code的日志 end

                $UCM=new UserCartModel();
                $isAdded = $UCM->addProduct_diy( $pid, $u_id );
                $jurl=str_replace("jewelryeditall", "jewelry", get_url()); //替换jewelryeditall为jewelry
                $JUMP_URL=pub_encode_pass($jurl,"3dcity");//加码加密
                if(intval($_POST['mmode'])==1){
                    /*直接从购物车中生成订单
                    $SA = A ( "User/Sales" ); // 调用user分组下的sales模块
                    $temp_orderid = $SA->get_umorderid ();
                    $oid = $temp_orderid; // 加密orderid
                    $this->WX_cart_order($oid,$_SESSION ['f_userid']);//微信中购物车直接生成订单
                    $oid_encode=pub_encode_pass( $oid, $_SESSION ['f_userid']); // 加密orderid
                    echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/wxuser/address/oid/".$oid_encode."/jurl/".$JUMP_URL."';</script>");
                    */
                    echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/cart/index/mmode/1/jurl/".$JUMP_URL."';</script>");
                    exit;
                }else{
                    echo ("<script>window.parent.location.href='".WEBROOT_URL."/user.php/cart/index/jurl/".$JUMP_URL."';</script>");
                    exit;
                }
                //echo ("<script>window.open('".WEBROOT_URL."/user.php/cart','_blank')</script>");
                //redirect (  WEBROOT_URL."/user.php/cart");
            }else{
                if(intval($_POST['mmode'])==1){//如果是微信打开，转向分享页面
                    echo ("<script>window.parent.top.location.href='".WEBROOT_URL."/index/wx-diysnap-pid-".$diy_id."-mmode-1.html';</script>");
                }else{
                    echo ("<script>window.parent.top.location.href='".WEBROOT_URL."/user.php/mydiy/jewelrylist.html';</script>");
                }
            }
        }else{ //显示diy
            if($udid){//如果存在pid（pid通过参数传输过来）
                $udinfo=M('user_diy')->where('id='.$udid)->find();
                $fieldvalue=$this->get_udinfo_all($udinfo,$cid);//用户diy数据
                //var_dump($fieldvalue);
            }else{
                if($_COOKIE['DIYvalue']){//如果存在
                    $defaultvalue= explode('think:', $_COOKIE['DIYvalue']);//去除think:
                    cookie(null,'DIYvalue');//清除cookie
                    $defaultvalue=json_decode($defaultvalue[1],TRUE);//json格式转换为数组
                    if($defaultvalue['Textvalue']){
                        $defaultvalue['Textvalue']=str_replace('%5C','\\',$defaultvalue['Textvalue']);//替换斜线
                        $defaultvalue['Textvalue']=unicode_decode($defaultvalue['Textvalue']);//解为utf8
                    }
                    if($defaultvalue['Backtext']){
                        $defaultvalue['Backtext']=str_replace('%5C','\\',$defaultvalue['Backtext']);//替换斜线
                        $defaultvalue['Backtext']=unicode_decode($defaultvalue['Backtext']);//解为utf8
                    }
                }else{
                    $defaultvalue=$this->getDefaultValueByDu($DU);//默认值
                }
                $udinfo=$defaultvalue;
                $fieldvalue=$defaultvalue;
            }
            $udinfo['startprice']=$DC['startprice'];//赋值起打价格
        }

        $TDM=new DiyDiamondModel();
        $DPM=new DiyPendantModel();
        /* //-------------------所有宝石读取为一个数组，不随位置变化动态读取宝石------------------start------------
         $diamondAll=$TDM->getDiyDiamondByArrId(str_replace(';',',',$DC['diamond'])); //读取产品配置的可选宝石
         //var_dump($diamondAll);
         $this->assign('diamondAll',$diamondAll);
         //-------------------所有宝石读取为一个数组，不随位置变化动态读取宝石------------------end--------------*/
        foreach($DU as $key =>$value){
            if($key!==count($DU)){
                $DUArray[$key]=$value;
                $DUArray[$key]['next_fieldgroup']=$DU[$key+1]['fieldgroup'];//拼接处理next_fieldgroup
            }
            $DUArray[$key]['fieldvalue'] = $fieldvalue[$value['unit_name']];//赋值
            //--------------------------------------新增加unit部件的值和材质值的判断-----------------------start---
            /* if($value['unit_material']){
                $DUArray[$key]['fieldvalue']            = $fieldvalue[$value['unit_name']]['value'];
                $DUArray[$key]['fieldvalue_material']   = $fieldvalue[$value['unit_name']]['material'];
                $DUArray[$key]['fieldvalue_visiable']   = $fieldvalue[$value['unit_name']]['visiable'];
                //if(!$DUArray[$key]['ishidden']){$DUArray[$key]['fieldvalue_visiable']   = 1;}
            /* }else{
                $DUArray[$key]['fieldvalue'] = $fieldvalue[$value['unit_name']];
                //if(!$DUArray[$key]['ishidden']){$DUArray[$key]['fieldvalue_visiable']   = 1;}
            }*/
            //--------------------------------------新增加unit部件的值和材质值的判断------------------------end---
            if($value['fieldtype']=="SELECT"){//如果是SELECT,构造selectarr
                if($value['unit_name']=='Size'){
                    $haveSize=1;
                    $this->assign('haveSize',$haveSize);
                }
                $DUArray[$key]['unit_value_arr']=$this->getUnitValueArr($value['unit_value']);
                $DUArray[$key]['fieldvalue_show']=$this->getUnitFieldValueShow($DUArray[$key]['unit_value_arr'],$DUArray[$key]['fieldvalue']);

            }
            if($value['unit_name']=='Chaintype'){//如果是项链样式,构造链子的默认值数组
                $DUArray[$key]['unit_default_arr']=$this->getUnitDefaultArr($value['unit_default']);
                $unit_default_arr=json_encode($DUArray[$key]['unit_default_arr']);//链子默认值数组
                if($udid){
                    $chaintypeCurrentV=$DUArray[$key]['fieldvalue'];
                }else{
                    $chaintypeCurrentV=0;
                }
            }
            if($value['fieldtype']=='MATERIAL'){//从材质配置中取出材质的默认值
                $mcateDefault=$value['unit_default'];
                $MaterialDefaultArr=explode(';',$mcateDefault);
                if(count($MaterialDefaultArr)==1){//判断是否使用所有材质，如果此处不为1，则$mcateType为1,需要重新组合mcate
                    $mcateType=0;
                }else{
                    if($udid){
                        $DUArray[$key]['fieldvalue']=$mcateIDKey[$DUArray[$key]['fieldvalue']]['pma_id']; //材料默认值写入fieldvalue
                        $DUArray[$key]['fieldvalue_name']=$mcateIDKey[$DUArray[$key]['fieldvalue']]['TPM_name']; //材料显示名称
                        $DUArray[$key]['fieldvalue_pic']=$mcateIDKey[$DUArray[$key]['fieldvalue']]['pma_image']; //材料显示图片
                    }else{
                        $DUArray[$key]['fieldvalue']=$MaterialDefaultArr[0]; //材料默认值写入fieldvalue
                        $DUArray[$key]['fieldvalue_name']=$mcateIDKey[$MaterialDefaultArr[0]]['TPM_name']; //材料显示名称
                        $DUArray[$key]['fieldvalue_pic']=$mcateIDKey[$MaterialDefaultArr[0]]['pma_image']; //材料显示图片
                    }
                    $mcateType=1;
                }
                $MaterialDefault=$MaterialDefaultArr[0];
            }

            if($value['fieldtype']=='PENDANT'){
                $havePendant=1;//是否有吊坠选择
                $pendantGet=I('pendant','','string');
                if($pendantGet){$DUArray[$key]['fieldvalue']=$pendantGet;}

                $pendantDefault=$value['unit_diamond'];
                $pendantDefault=str_replace(';',',',$pendantDefault);//替换分号为逗号
                $pendantArray[$value['id']]=$DPM->getDiyPendantByArrId($pendantDefault);
                if($udid){
                    $DUArray[$key]['fieldvalue_stylePendantValue'] = $this->getStylePendantValue($fieldvalue[$value['unit_name']],$DPM);
                    $pendantPrice[$value['id']]=$DPM->getPriceArr($fieldvalue[$value['unit_name']], $pendantArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],1,$price_count);//获取吊坠价格
                }else{
                    $pendantPrice[$value['id']]=$DPM->getPriceArr($fieldvalue[$value['unit_name']], $pendantArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],1,$price_count);//获取吊坠价格
                    $DUArray[$key]['fieldvalue_stylePendantValue'] = $this->getStylePendantValue($DUArray[$key]['unit_default'],$DPM);

                }
                //$pendantArray[$value['id']]=$TDM->getDiyDiamondByArrId($fieldvalue[$value['unit_name']]);
                //$diamondArrayJson=json_encode($diamondArray);
                //var_dump($pendantPrice);
            }

            //--------------------------------------------宝石配置读取start
            if($value['fieldtype']=='DIAMOND'){//读取出宝石
                $diamondDefault=$value['unit_diamond'];
                $diamondDefault=str_replace(';',',',$diamondDefault);//替换分号为逗号
                $diamondArray[$value['id']]=$TDM->getDiyDiamondByArrId($diamondDefault);
                $diamondArrayJson=json_encode($diamondArray);

                if($udid){
                    $diamondPosDid[$value['id']]=$TDM->getDiamondInfoByid($fieldvalue[$value['unit_name']]);//方案中的宝石位置和宝石ID
                    $diamondPosDidImg[$value['id']]=$fieldvalue[$value['unit_name']];//方案中的宝石位置和宝石ID
                    if($value['unit_material']) {//如果有tdf_diy_unit中unit_material有值则需要读取部件的材质（数组读取）
                        //--------------------------------------新增加unit部件的值、材质值、是否显示值-----------------------start---
                        $DUArray[$key]['fieldvalue']            = $fieldvalue[$value['unit_name']]['value'];
                        $DUArray[$key]['fieldvalue_styleDiamondValue'] = $this->getStyleDiamondValue($fieldvalue[$value['unit_name']]['value'],$TDM);
                        $DUArray[$key]['fieldvalue_material']   = $fieldvalue[$value['unit_name']]['material'];
                        $DUArray[$key]['fieldvalue_visiable']   = $fieldvalue[$value['unit_name']]['visiable'];
                        //--------------------------------------新增加unit部件的值、材质值、是否显示值-----------------------start---
                        //--------------------------------------获取价格数组(如果unit_material有值则取键为value)---------start-----
                        $visiable=$DUArray[$key]['fieldvalue_visiable']?$DUArray[$key]['fieldvalue_visiable']:1;

                        $diamondPrice[$value['id']]=$TDM->getPriceArr($fieldvalue[$value['unit_name']]['value'], $diamondArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],$visiable,$price_count);//获取宝石价格
                        //--------------------------------------获取价格数组(如果unit_material有值则取键为value)---------end-----
                    }else{
                        //--------------------------------------新增加unit部件的值、材质值、是否显示值-----------------------start---
                        $DUArray[$key]['fieldvalue']            = $fieldvalue[$value['unit_name']];
                        $DUArray[$key]['fieldvalue_styleDiamondValue'] = $this->getStyleDiamondValue($fieldvalue[$value['unit_name']],$TDM);

                        //--------------------------------------新增加unit部件的值、材质值、是否显示值-----------------------start---
                        //--------------------------------------获取价格数组(如果unit_material无值或为0则直接取值)---------start-----
                        $diamondPrice[$value['id']]=$TDM->getPriceArr($fieldvalue[$value['unit_name']], $diamondArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],1,$price_count);//获取宝石价格
                        //--------------------------------------获取价格数组(如果unit_material无值或为0则直接取值)---------end-----
                    }
                    //var_dump($value['id']);
                    //var_dump($fieldvalue);
                    //var_dump($value['unit_name']);
                    //$diamondPrice[$value['id']]=$TDM->getPriceByPosDidArr($fieldvalue[$value['unit_name']]['value'], $diamondArray[$value['id']]);//获取宝石价格
                }else{
                    //如果是镶钻的部件，更新部件的相关值(fieldvalue和fieldvalue_material)用于前台页面显示----start
                    $DUArray[$key]['fieldvalue']= $DUArray[$key]['unit_default']; //部件值为部件的默认值
                    $DUArray[$key]['fieldvalue_styleDiamondValue'] = $this->getStyleDiamondValue($DUArray[$key]['unit_default'],$TDM);

                    $DUArray[$key]['fieldvalue_material']= $DUArray[$key]['unit_material_value'];//部件材质值为部件材质的默认值
                    if(!$DUArray[$key]['ishidden']){$DUArray[$key]['fieldvalue_visiable']   = 1;}//部件默认是否显示
                    //------------------------------------------------------------------------------end
                    $diamondPrice[$value['id']]=$TDM->getPriceArr($DUArray[$key]['unit_default'],$diamondArray[$value['id']],$value['unit_diamond_count'],$value['unit_price'],$DUArray[$key]['fieldvalue_visiable'],$price_count);//获取宝石价格
                    //$diamondPrice[$value['id']]=0;
                }
            }
            //--------------------------------------------宝石配置读取end
        }
        if($pendantPrice){
            $diamondPrice=$diamondPrice+$pendantPrice;
        }else{
            $diamondPrice=$diamondPrice;
        }

        $diamondPriceJson=json_encode($diamondPrice);

        $this->assign('diamondPrice',$diamondPrice);
        $this->assign('diamondPriceJson',$diamondPriceJson);
        $this->assign('diamondPosDid',$diamondPosDid);//方案中的宝石位置和宝石ID
        $this->assign('diamondinfo',$diamondArray);
        $this->assign('diamondinfoJson',$diamondArrayJson);
        /*通过$mcateType判断是否使用所有材质，如果不使用全部材质，进行过滤mcate*/
        if($mcateType==1){
            foreach($mcate as $mkey => $mvalue){
                if(!in_array($mvalue['pma_id'],$MaterialDefaultArr)){
                    unset($mcate[$mkey]);
                }
            }
        }

        $dataArr['diy_unit']=$DUArray;
        $dataArr['material']=$mcate;
        /* if($havePendant){
             $pendantAll=$DPM->getDiyPendantAll();
         }*/


        $dataArr['pendant']=$pendantArray;//所有吊坠集合
        //var_dump( $pendantArray);
        //	echo "编辑时链子当前值:".$chaintypeCurrentV;
        //如果是项链，增加链子参数-- start -<
        if($dataArr['diy_cate']['cate_group']==2){
            $DN=new DiyNecklaceModel();
            $necklace=$DN->getDiyNecklace();
            $necklace_json=json_encode($necklace);
            $dataArr['necklace']=$necklace_json;
            $show_neck="<script>";
            //$show_neck.="var neck=new Array();";
            //foreach($necklace_json as $key1 => $value1){
            $show_neck.="var neck=".$necklace_json.";";
            $show_neck.="var neck_current=".$chaintypeCurrentV.";";
            $show_neck.="var neck_default=".$unit_default_arr.";";
            //}
            $show_neck.="</script>";


            $necklaceDefaultArr=(json_decode($unit_default_arr));

            $nDefaultID=$necklaceDefaultArr->$MaterialDefault;
            $neckdefaultArr=$DN->getNecklaceByPmaid($MaterialDefault);
            //var_dump($neckdefaultArr);
            $this->assign("show_neck",$show_neck);
        }
        //如果是项链，增加链子参数--- end ->

        //增加附加字符------- start -->
        if($dataArr['diy_cate']['fontext']=='0,0,0' || $dataArr['diy_cate']['fontext']==0){
            $dataArr['diy_cate']['fontext']='';
        }else{
            $fontextArr=explode(",",$dataArr['diy_cate']['fontext']);
            $dataArr['diy_cate']['fontextArr']=$fontextArr;
        }
        //增加附加字符------- end -->
        $fontsArr=explode(',',$dataArr['diy_cate']['fonts']);
        if($fontsArr){
            $dataArr['fontsArr']=$fontsArr;
        }else{
            $dataArr['fontsArr'][0]=$dataArr['fonts'];
        }
        if($this->_isLogin()){
            $islogin=1;
        }else{
            $islogin=0;
        }
        //var_dump($pendantArray);
        //var_dump($dataArr['diy_cate']);
        /*echo "<table>";
        foreach($dataArr['diy_unit'] as $t1 => $v1){
            echo "<tr><td>";
            echo $v1;
            echo "</td></tr>";
        }

        echo "</table>";*/

        Vendor('wxshare.jssdk');
        $jssdk = new JSSDK("wxf71f9222d3bc3c2e", "7338ad0158221be35c8572c562a56899");
        $signPackage = $jssdk->GetSignPackage();


        $shareInfo['title']=$productInfo['p_name'];
        $shareInfo['desc']="首饰也可以DIY啦！一起来3DCITY玩转3D打印和DIY首饰的乐趣...";
        $shareInfo['link']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $shareInfo['imgUrl']=WEBROOT_URL.$productInfo['p_cover'];
        $this->assign("shareInfo",$shareInfo);
        $this->assign('signPackage',$signPackage);

        //zhengweifu [2016-01-20, start]
//		var_dump($dataArr['diy_unit']);
//		exit;
        $materialId2Name = array(
            127 => "925银",
            128 => "18K玫瑰金",
            129 => "18K白金",
            130 => "18K黄金"
        );
        $this->assign("materialId2Name", $materialId2Name);
        //zhengweifu [2016-01-20, end]
        //var_dump($mcate);
        //var_dump($dataArr['diy_unit']);
        //exit;
        $this->assign("cid",$cid);
        $this->assign("mmode",$mmode);
        $this->assign('islogin',$islogin);

        $this->assign('showtype',$showtype);
        $this->assign('dataAll',$dataArr);

        $this->assign('udinfo',$udinfo);
        $this->assign('mcate',$mcate);


        // 浏览器判断
        if (is_weixin ()) {
            $mobileAgent = 1;
        } elseif (is_mobile ()) {
            $mobileAgent = 2;
        } else {
            $mobileAgent = 3;
        }

        $this->assign ( 'mobileAgent', $mobileAgent );
        $this->display($templates);
        //$this->_renderPage();
    }
	
	
	function snapdetailall_bak(){//预览时的显示
		$showtype=I('showtype',0,"intval");
		$this->assign("showtype",$showtype);
		$pid=I("pid",0,"intval");
		$upid=I("upid",0,"intval");
		$PFM=new ProductFileModel();
		$product_file=$PFM->getFileByProduct($pid);
		if($product_file){
			$pfile=$product_file[0]['pf_path'].$product_file[0]['pf_filename'];
		}
		$udinfo=$this->getUdinfoByUpid($upid,$pid);
		$cid=I('cid',0,'intval');//DIY种类(tdf_diy_cate中的cid)
		if(!$cid){$cid=$udinfo['p_cate_4']; }//如果没有得到cid的参数，需要从tdf_product表中的 p_cate_4中获得
		if(!$cid){$this->error("参数错误",'diy-jewelrylist');}
		$DC=M('diy_cate')->where("cid=".$cid)->find();//diy产品类型
		$DU=M('diy_unit')->where('cid='.$cid)->order('fieldgroup,sort')->select();//选择tdf_diy_unit
		$sql="select TPM.pma_id,TPM.pma_name as TPM_name,TPMP.pma_name as TPMP_name,TPM.pma_unitprice,TPM.pma_density,TPM.pma_startprice,TPM.pma_diy_formula_s,TPM.pma_diy_formula_b,TPM.pma_jbh_formula from tdf_printer_material as TPM ";
		$sql.="Left Join tdf_printer_material as TPMP ON TPMP.pma_id=TPM.pma_parentid ";
		$sql.="where TPM.pma_type=1 order by TPM.pma_weight ASC ";
		$mcate=M("printer_material")->query($sql);//打印材料数组，必须
		$dataArr['diy_cate']=$DC;
		foreach($DU as $key =>$value){
			if($key!==count($DU)){
				$DUArray[$key]=$value;
				$DUArray[$key]['next_fieldgroup']=$DU[$key+1]['fieldgroup'];//拼接处理next_fieldgroup
			}
			$DUArray[$key]['fieldvalue']=$udinfo[$value['unit_name']];
			if($value['fieldtype']=="SELECT"){//如果是SELECT,构造selectarr
				$DUArray[$key]['unit_value_arr']=$this->getUnitValueArr($value['unit_value']);
			}
		}
		$dataArr['diy_unit']=$DUArray;
		$dataArr['material']=$mcate;
		$fontsArr=explode(',',$dataArr['diy_cate']['fonts']);
		if($fontsArr){
			$dataArr['fontsArr']=$fontsArr;
		}else{
			$dataArr['fontsArr'][0]=$dataArr['fonts'];
		}
		if($this->_isLogin()){
			$islogin=1;
		}else{
			$islogin=0;
		}
		$this->assign('islogin',$islogin);
		$this->assign('dataAll',$dataArr);
		$this->assign('pfile',$pfile);
		$this->assign('pid',$pid);
		$this->assign('mcate',$mcate);
		$this->assign('udinfo',$udinfo);
        $this->assign('mmode',0);
        $this->_renderPage();
	}
	
	
	function snapdetailapi(){//预览时的显示
		
		$up_id=I('upid',0,'intval');
		$p_id=I('pid',0,'intval');
		
		//-------------------向www.3dcity.com发送订单请求,获得订单数据
		$apicurl=new ApicurlModel();
		$apicurlresult=$apicurl->getdiyapi($up_id,$p_id);
		$object=json_decode($apicurlresult);
		//-------------------向www.3dcity.com发送订单请求,获得订单数据
		$upresult =  json_decode( json_encode( $object),true);
		
		$dataArr=$upresult['dataArr'];
		$mcate	=$upresult['mcate'];
		$udinfo	=$upresult['udinfo'];
		$pid	=$upresult['pid'];
		
		$PFM=new ProductFileModel();
		$product_file=$PFM->getFileByProduct($pid);
		if($product_file){
			$pfile=$product_file[0]['pf_path'].$product_file[0]['pf_filename'];
			echo "模型已经生成,可直接 <a href='".WEBROOT_URL.$pfile."'>下载</a><br><br>";
		}
		
		if($this->_isLogin()){
			$islogin=1;
		}else{
			$islogin=0;
		}
		$this->assign('islogin',$islogin);
		$this->assign('pfile',$pfile);
		$this->assign('dataAll',$dataArr);
		$this->assign('pid',$pid);
		$this->assign('mcate',$mcate);
		$this->assign('udinfo',$udinfo);
		$this->_renderPage();
	}
	
	private function getUdinfoByUpid($upid,$pid){
		$UPD=M("user_prepaid_detail")->field("up_product_info")->where("up_id=".$upid)->find();
		$upd_arr=unserialize($UPD['up_product_info']);
		//var_dump($upd_arr);
		foreach($upd_arr as $key => $value){
			if($value['p_id']==$pid){
				$productArr=$value;/*获得product的详细信息数组*/
			}
		}
		$udinfo=$this->get_udinfo($productArr);
		return $udinfo;		
	}
	private function get_udinfo($udinfo){//根据udinfo数组来返回整个表单数据
		$diy_unit_info=unserialize($udinfo['diy_unit_info']);
		$UD=M("diy_unit")->where("cid=".$udinfo['p_cate_4']." and ishidden=0")->order("sort")->select();
		foreach($UD as $key =>$value){
			//echo "ID:".$value['id'];
			$udinfo[$value['unit_name']]=$diy_unit_info[$value['id']];
		}
		return $udinfo;
	}
	
	
/* 	function diyview(){//预览时的显示
		$pid=I("id",0,"intval");
		$PM=new ProductModel();
		$product_info=$PM->getProductByID($pid);
		$udid=$product_info['p_diy_id'];
		$udinfo=M('user_diy')->where('id='.$udid)->find();
		$udinfo=$this->get_udinfo($udinfo);
		$this->assign('udinfo',$udinfo);
		
		$this->_renderPage();
	} */
	

	function get_udinfo_all($udinfo,$cid=14){//根据udinfo数组来返回整个表单数据值
		$diy_unit_info=unserialize($udinfo['diy_unit_info']);
		$UD=M("diy_unit")->where("cid=".$cid)->order("sort")->select();
		foreach($UD as $key =>$value){
			$udinfo[$value['unit_name']]=$diy_unit_info[$value['id']];
		}
		return $udinfo;
	}
	
	function getDefaultValueByDu($DU){
		foreach($DU as $keyDU => $valueDU){
			if($valueDU['unit_name']=='size'){$sizeArr=$valueDU['unit_value'];}
			$value_default[$valueDU['unit_name']]=$valueDU['unit_default']; //读取默认值
		}
		return $value_default;
	}
	
	function getUnitDefaultArr($default_value){//输出链子默认数组
		$sizeArr=explode(";",$default_value);//链子默认数组(材质ID=>链子ID)
		foreach($sizeArr as $rkey =>$rvalue){
			$tempsize=explode(":",$rvalue);
			$size[$tempsize[0]]=intval($tempsize[1]);
		}
		return $size;
	}
	
	function getUnitValueArr($unit_value){//根据传入的$unit_value值返回select的值数组
		$sizeArr=explode(";",$unit_value);//尺寸大小默认数组
		foreach($sizeArr as $rkey =>$rvalue){
			$tempsize=explode("=",$rvalue);
			$size[$tempsize[0]]['key']=$tempsize[0];
			$size[$tempsize[0]]['value']=$tempsize[1];
		}
		return $size;
	}

    function getUnitFieldValueShow($sizeArr,$fieldvalue){//根据传入的sizeArr和$unit_value值返回size对应的显示名称
        foreach($sizeArr as $key => $value){
            if($value['key']==$fieldvalue){
                $result=$value['value'];
            }
        }
        return $result;
    }

	
	function getDiyUnitInfo($cid){//由cid获得diy_unit基本配置
		$DU=M('diy_unit')->field('id,unit_name,unit_value,unit_default')->where('cid='.$cid)->order('sort')->select();//选择tdf_diy_unit
		return $DU;		
	}
	
	
	/**
	 * 读取
	 */
	public function diyCurl() {
		$url="http://localhost/city/api/";
		$cu = curl_init();
		curl_setopt($cu, CURLOPT_URL, $url);
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($cu);
		curl_close($cu);
	}




/*
 * 微信中购物车直接生成订单
 */
 public function WX_cart_order($oid,$uid){
 	$UPM = new UserPrepaidModel ();
 	$UCM = new UserCartModel ();
 	$up_type=4;
 	$ProductList = $UCM->getProduct ( $uid ); // 购买的模型集合
 	foreach ( $ProductList as $k => $Product ) {
 		if ($Product ['p_producttype'] == 2 || $Product ['p_producttype'] == 4) {
 			$up_express = 1;	// 是否需要快递
 			$tempcount = 1;// 产品数量为1 
 			$UCM->updateCartCount ( $Product ['p_id'], $tempcount );	// 数量同步
 			$ProductList [$k] [$UCM->F->Count] = $tempcount; 			// 价格小计
 			$TotalPrice += $Product [$this->DBF->Product->Price] * $tempcount;
 		} else {
 			$tempcount = 1;// 数量统计
 			$UCM->updateCartCount ( $Product ['p_id'], $tempcount );// 购物车更新数量
 			$ProductList [$k] [$UCM->F->Count] = $tempcount;// 数量同步
 			$TotalPrice += $Product [$this->DBF->Product->Price] * $tempcount;// 价格小计
 		}
 		$pid_array [] = $Product [$this->DBF->Product->ID] . "," . $tempcount; // product的id数组
 		$ProductList [$k] ['p_count'] = $tempcount; // 单个产品数量

 		if ($Product ['p_producttype'] == 4) { // 如果是DIY件，需保存DIY的快照参数(增加tdf_user_diy中的diy_unit_info和cover、price字段)
 			$udinfo = M ( 'user_diy' )->where ( 'id=' . $ProductList [$k] ['p_diy_id'] )->find ();
 			$ProductList [$k] ['diy_unit_info'] = $udinfo ['diy_unit_info'];
 			$ProductList [$k] ['cover'] = $udinfo ['cover'];
 			$ProductList [$k] ['price'] = $udinfo ['price'];
 		}
 	}
 	$up_product_info = serialize ( $ProductList ); // 存储到订单商品快照中的商品信息
 	$up_amount_save = $TotalPrice;
 	$IP = get_client_ip ();
 	
 	$UPM_info = $UPM->getPrepaidListByOrderid ( $oid );
 	$UPD = new UserPrepaidDetailModel ();
 	if (! $UPM_info) {
 		$UPM->startTrans (); // 在d模型中启动事务
 		$upid = $UPM->addRecord ( $uid, $up_amount_save, $IP, 0, $oid, 0, serialize ( $pid_array ), $up_type, $up_express );
 		$upd_id = $UPD->addRecord ( $upid, $up_product_info );
 		if ($upid && $upd_id) {
 			$UPM->commit (); // 提交事务
 		} else {
 			$UPM->rollback (); // 事务回滚
 		}
 	} else {
 		$upid = $UPM_info [0] ['up_id'];
 	}
 	
 	//var_dump($upid);
 	
 }


    public function get_umorderid() 	// 产生orderid
    {
        $tempid = time () . $this->generate_rand ( 8 );
        return $tempid;
    }

    public function generate_rand($l) { // 产生随机数$l为多少位
        $c = "0123456789";
        // $c= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        srand ( ( double ) microtime () * 1000000 );
        for($i = 0; $i < $l; $i ++) {
            $rand .= $c [rand () % strlen ( $c )];
        }
        return $rand;
    }


}