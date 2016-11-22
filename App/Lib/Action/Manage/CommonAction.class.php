<?php
class CommonAction extends Action {

    public $loginMarked;
    
    private $_ajaxResponseStruct = array (
    		'status' => null,
    		'info' => null,
    		'url' => null
    );

    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function _initialize() {
    	import ( 'ORG.Util.RBAC' );
    	$tempPnodeid	=I("get.Pnodeid",0,"intval");
    		$tempnodeid		=I("get.nodeid",0,"intval");
    				if($tempPnodeid!=0){$_SESSION['Pnodeid']=$tempPnodeid;}
    				if($tempnodeid!=0){$_SESSION['nodeid']=$tempnodeid;}
    		   				
    	header("Content-Type:text/html; charset=utf-8");
        header('Content-Type:application/json; charset=utf-8');
        $systemConfig = include WEB_ROOT . 'App/Common/Manage/systemConfig.php';
        if (empty($systemConfig['TOKEN']['admin_marked'])) {
            $systemConfig['TOKEN']['admin_marked'] = "3DCity后台管理";
            $systemConfig['TOKEN']['admin_timeout'] = 3600000;
            $systemConfig['TOKEN']['member_marked'] = "http://www.bitmap3d.com";
            $systemConfig['TOKEN']['member_timeout'] = 3600000;
            F("systemConfig", $systemConfig, WEB_ROOT . "App/Common/Manage");
            if (is_dir(WEB_ROOT . "install/")) {
                delDirAndFile(WEB_ROOT . "install/", TRUE);
            }
        }
       
        $this->loginMarked = md5($systemConfig['TOKEN']['admin_marked']);
        $this->checkLogin();
        // 用户权限检查
        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
           // import('ORG.Util.RBAC');
            if (!RBAC::AccessDecision()) {
        	           //检查认证识别号
                if (!$_SESSION [C('USER_AUTH_KEY')]) {
                    //跳转到认证网关
                    redirect(C('USER_AUTH_GATEWAY'));
//                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                }
                
                // 没有权限 抛出错误
                if (C('RBAC_ERROR_PAGE')) {
                    // 定义权限错误页面
                    redirect(C('RBAC_ERROR_PAGE'));
                }else{
                    if (C('GUEST_AUTH_ON')) {
                    	$this->assign('jumpUrl', C('USER_AUTH_GATEWAY'));
               		}
                    // 提示错误信息
                  //echo L('_VALID_ACCESS_');
                  //print_r($_SESSION);
                  //exit;
                  $this->error(L('_VALID_ACCESS_'));
                }
            }
        }
        /***************/
       
        
        /***************/
      
				$nav_model	=$this->getNodeTitleByName(MODULE_NAME);//导航当前模块显示名
				$nav_action	=$this->getNodeTitleByName(ACTION_NAME);//导航当前动作显示名

				if(MODULE_NAME=="Module"){
					$currentNav = "";
				}else{
					$currentNav =$nav_model." > ".$nav_action;
				}
				$this->assign("currentNav",$currentNav);
				$this->assign("menu", $this->show_menu());

        $this->assign("sub_menu", $this->show_sub_menu());
        $this->assign("my_info", $_SESSION['my_info']);
        $this->assign("site", $systemConfig);
       // $this->getQRCode();  //二维码显示
    }
    
    /**
     * AJAX响应结果
     *
     * @param int $status
     * @param string $info
     * @param string $url
     * @return array
     */
    protected function _ajaxResponse($status = 0, $info = '', $url = '', $data = array()) {
    	$response = $this->_ajaxResponseStruct;
    	$response ['status'] = $status;
    	$response ['info'] = $info;
    	$response ['url'] = $url;
    	$response ['data'] = $data;
    	print_r ( json_encode ( $response ) );
    	exit ();
    }

    /**
     * 后台记录日志
     * @param string $data(类型,类型名称,节点id,用户ID,目标id)
     * @return int
     */
    protected function addLog($logtype,$logtype_name,$nodeid,$uid,$target) {
    	$data=array(
	    	'logtype'			=>$logtype,
	    	'logip'				=>$_SERVER["REMOTE_ADDR"],
    		'logtype_name'		=>$logtype_name,
	    	'nodeid'			=>$nodeid,
	    	'uid'				=>$uid,
	    	'target'			=>$target,
    	);
    	$log = LogFactoryModel::init ( 'admin' );
    	$res = $log->addLog ( $data );
    	return $res;
    }
    
    
    protected function getQRCode($url = NULL) {
        if (IS_POST) {
            $this->assign("QRcodeUrl", "");
        } else {
//            $url = empty($url) ? C('WEB_ROOT') . $_SERVER['REQUEST_URI'] : $url;
            $url = empty($url) ? C('WEB_ROOT') . U(MODULE_NAME . '/' . ACTION_NAME) : $url;
            import('QRCode');
            $QRCode = new QRCode('', 80);
            $QRCodeUrl = $QRCode->getUrl($url);
						$this->assign("QRcodeUrl", $QRCodeUrl);
        }
    }
		
    public function getNodeTitleByName($cname){//由节点名称得到中文名称
    	//echo $cname;
    	$TM=M(node);
    	$title =$TM->field("title")->where("name like '%".$cname."%'")->find();
     	return $title['title'];
    }

	public function checkLogin()
	{
		$LoginMarked = isset($_COOKIE[$this->loginMarked]) ? $_COOKIE[$this->loginMarked] : $_POST[$this->loginMarked];
		if(isset($LoginMarked))
		{
			$cookie = explode("_", $LoginMarked);
			$timeout = C("TOKEN");
			if(time() > (end($cookie) + $timeout['admin_timeout']))
			{
				setcookie("$this->loginMarked", NULL, -3600, "/");
				unset($_SESSION[$this->loginMarked], $LoginMarked);
				$this->error("登录超时，请重新登录", U("Public/index"));
			}
			else
			{
				if($cookie[0] == $_SESSION[$this->loginMarked])
				{
					setcookie("$this->loginMarked", $cookie[0] . "_" . time(), 0, "/");
				}
				else
				{
					setcookie("$this->loginMarked", NULL, -3600, "/");
					unset($_SESSION[$this->loginMarked], $LoginMarked);
					$this->error("帐号异常，请重新登录", U("Public/index"));
				}
			}
		}
		else
		{
			$this->redirect("Public/index");
		}
		return TRUE;
	}

    /**
      +----------------------------------------------------------
     * 验证token信息
      +----------------------------------------------------------
     */
    protected function checkToken() {
        if (IS_POST) {
            if (!M("Admin")->autoCheckToken($_POST)) {
                die(json_encode(array('status' => 0, 'info' => '令牌验证失败')));
            }
            unset($_POST[C("TOKEN_NAME")]);
        }
    }

    /**
      +----------------------------------------------------------
     * 显示一级菜单
      +----------------------------------------------------------
     */
    private function show_menu() {
    	$authId=$_SESSION['my_info'];
    	$RBACID_ARR=RBAC::getAccessListID($authId['aid']);
    	
    	foreach($RBACID_ARR[1] as $key => $value){
    		$menu_nodeid.=$key.",";
    	}
    	//var_dump($menu_nodeid);
    	
   	$menu_nodeid=substr($menu_nodeid,0,strlen($menu_nodeid)-1);//根据权限获取模块ID用于显示菜单
   	if($_SESSION[C('ADMIN_AUTH_KEY')]){
   		$sqlwhere="";
   	}else{
   		$sqlwhere="and id in (".$menu_nodeid.")";
   	}
   	$Pnodeid=I("Pnodeid");
		$onemenus = M()->query('select name,title,id from tdf_node where level=2 and ismenu=1 and DelSign=0 '.$sqlwhere.' order by sort');
		$arr = array();
        foreach ($onemenus as $mkey) {
            $arr[] = $mkey['name'].";".$mkey['title'].";".$mkey['id'];
		}
		$cache=$arr;
		
		$count = count($cache);
        $i = 1;
        $menu = "";
        foreach ($cache as $url => $name) {
        	$namearr=explode(";",$name);//以;数组化
        	$name_url=$namearr[0];
        	$name_menu=$namearr[1];
        	$name_id=$namearr[2];
        	//echo "count".$count."<br>";
        	
        	
        	if ($i == 1) {
                $css = $name_id == $Pnodeid || !$name_id ? "fisrt_current" : "fisrt";
                $menu.='<li class="' . $css . '"><span><a href=__APP__/'.$name_url .'/index/Pnodeid/'.$name_id.'>' . $name_menu . '</a></span></li>';
            } else if ($i == $count) {
                $css = $name_id == $Pnodeid ? "end_current" : "end";
                $menu.='<li class="' . $css . '"><span><a href=__APP__/'.$name_url .'/index/Pnodeid/'.$name_id.'>' . $name_menu . '</a></span></li>';
            } else{ 
            	  $css = $name_id == $Pnodeid ? "current" : "";
                $menu.='<li class="' . $css . '"><span><a href=__APP__/'.$name_url .'/index/Pnodeid/'.$name_id.'>' . $name_menu . '</a></span></li>';
            }
            $i++;
        }
        return $menu;
    }

    /**
      +----------------------------------------------------------
     * 显示二级菜单
      +----------------------------------------------------------
     */
    private function show_sub_menu() {
    	$authId=$_SESSION['my_info'];
    	$RBACID_ARR=RBAC::getAccessListID($authId['aid']);
    	$nodeid		=$_SESSION['nodeid'];
    	$Pnodeid	=$_SESSION['Pnodeid'];
    	$big=0 ? "Common" : $Pnodeid;
		$twomenus = M()->query('select pn.id,pn.pid as pid,pn.name as name,pn.title as title,ppn.name as pname,pn.url as url from tdf_node as pn Left Join tdf_node as ppn On pn.pid=ppn.id where pn.level=3 and pn.ismenu=1 and pn.DelSign=0 order by ppn.sort,pn.sort');
        $arr = array();
		$subMemuRBAC=$RBACID_ARR[1][$Pnodeid];
		foreach ($twomenus as $i =>$mkey) {
			$arr[$mkey['pid']]=$mkey['pname'];
	  	}

	   	foreach($arr as $key=>$m){
			unset($arr1);
			foreach($twomenus as $nkey=>$val){
               // var_dump($val);
				if($key==$val['pid']){
					$arr1[$val['id']]=$val['title'];
				}
				if($key ==2){
					$arr_temp["0"]=$arr1;
				}else{
					$arr_temp[$key]=$arr1;
				}
			}		
		} 
		
		
		$cache=$arr_temp;
		$sub_menu = array();
		if($cache[$Pnodeid]) {
			$cache = $cache[$Pnodeid];
    	   	if(!$_SESSION[C('ADMIN_AUTH_KEY')]){ //如果是管理员，显示所有子菜单
	    		foreach($subMemuRBAC as $keyRBAC =>$valueRBAC){
	    	   		if(array_key_exists($keyRBAC,$cache)){
	    	   			$newcache[$keyRBAC]=$cache[$keyRBAC];
	    	   		}
	    	   	}
	    	$cache=$newcache;
    	   }
        // var_dump($cache);

            foreach ($cache as $ukey => $title) {
        	$big_nodename=$this->getNodenameByNodeid($Pnodeid);
            $url_nodename=$this->getNodenameByNodeid($ukey);
            $url=$this->getNodenameByNodeid($ukey,1);

            if($Pnodeid == 0){
              	$use_url="index";
            }else{
                if($url){
                    $use_url = __APP__."/".$url;
                }else{
                    $use_url = __APP__."/".$big_nodename."/".$url_nodename."/Pnodeid/".$Pnodeid."/nodeid/".$ukey;
                }
            }
                if($nodeid==$ukey){
                    $temp_title="<font color='#7fff00'>".$title.'  ▶</font>';
                }else{
                    $temp_title=$title;
                }

                $sub_menu[] = array('url' => $use_url, 'title' => $temp_title);
         }
            return $sub_menu;
        } else {
            return $sub_menu[] = array('url' => '#', 'title' => "该菜单组不存在");
        }
    }
    
    
    
    function getNodenameByNodeid($id,$backtype=0){
    	$NM=M('node');
    	$nm_arr=$NM->field("name,url")->where("id=".$id)->find();
    	if($backtype){
            return $nm_arr['url'];
        }else{
            return $nm_arr['name'];
        }
    }
    
    protected function displayError($Error, $Key = 'ErrInfo')
    { $this->assign($Key, $Error); $this->display(); }
}