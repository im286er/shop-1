<?php

class AccessAction extends CommonAction {

	/*
	 * 服务器信息
	 */
	public function serverinfo(){
		if (function_exists('gd_info')) {
			$gd = gd_info();
			$gd = $gd['GD Version'];
		} else {
			$gd = "不支持";
		}
		$info = array(
				'操作系统' => PHP_OS,
				'主机名IP端口' => $_SERVER['SERVER_NAME'] . ' (' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'] . ')',
				'运行环境' => $_SERVER["SERVER_SOFTWARE"],
				'PHP运行方式' => php_sapi_name(),
				'程序目录' => WEB_ROOT,
				'MYSQL版本' => function_exists("mysql_close") ? mysql_get_client_info() : '不支持',
				'GD库版本' => $gd,
				//	'MYSQL版本' => mysql_get_server_info(),
				'上传附件限制' => ini_get('upload_max_filesize'),
				'执行时间限制' => ini_get('max_execution_time') . "秒",
				'剩余空间' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
				'服务器时间' => date("Y年n月j日 H:i:s"),
				'北京时间' => gmdate("Y年n月j日 H:i:s", time() + 8 * 3600),
				'采集函数检测' => ini_get('allow_url_fopen') ? '支持' : '不支持',
				'register_globals' => get_cfg_var("register_globals") == "1" ? "ON" : "OFF",
				'magic_quotes_gpc' => (1 === get_magic_quotes_gpc()) ? 'YES' : 'NO',
				'magic_quotes_runtime' => (1 === get_magic_quotes_runtime()) ? 'YES' : 'NO',
		);
		$this->assign('server_info', $info);
		$this->display();
	}
    
    /**
      +----------------------------------------------------------
     * 管理员列表
      +----------------------------------------------------------
     */
	public function admList(){
		$this->assign("list", D("Access")->adminList());
		$this->display();
		
	}
	
	
    public function index() {//默认权限管理首页为节点列表
    	/* $this->assign("list", D("Access")->adminList());
    	$this->display();
    	 */
    	$mbnodeid=15;
    	
      $this->assign("list", D("Access")->nodeList($mbnodeid));
      $this->display("nodelist"); 
     
    }

    
    public function nodeList() {
    	//var_dump(D("Access")->nodeList());

    	$mbnodeid=I('mbnodeid',0,'intval');
        //var_dump(D("Access")->nodeList($mbnodeid));
      	$this->assign("list", D("Access")->nodeList($mbnodeid));
    	$this->display();
    }

    public function roleList() {
        $this->assign("list", D("Access")->roleList());
        $this->display();
    }

    
    public function contentList(){
    	$mbnodeid=I('mbnodeid',0,'intval');
    	$this->assign("list", D("Access")->contentList($mbnodeid));
    	$this->display();    	
    }
    
    public function editContentlist() {
    	//echo $_SESSION['nodeid'];
    	
    	$searchtype	=I("searchtype",0,"intval");
    	$keywords	=I("keywords",0,"intval");
    	//echo $searchtype;
    	
    	//exit;
    	if (IS_POST) {
    		$this->checkToken();
    		
    		header('Content-Type:application/json; charset=utf-8');
    		echo json_encode(D("Access")->editContentlist($searchtype,$keywords));
    	} else {
    		$M = M("node_modulecontentlist");
    		$info = $M->where("id=" . (int) $_GET['id'])->find();
    		if (empty($info['id'])) {
    			$this->error("不存在contentlist的id", U('Access/contentlist'));
    		}
    		$info=$this->getnodeid($info);	//
    		$info=$this->getfieldtype($info);
    		$this->assign("searchtype", $searchtype);
    		$this->assign("keywords", $keywords);
    		$this->assign("info", $info);
    		$this->display();
    	}
    }
    
    public function addContentlist(){
    	if (IS_POST) {
    		$this->checkToken();
    		header('Content-Type:application/json; charset=utf-8');
    		echo json_encode(D("Access")->addContentlist());
    	} else {
    		//var_dump(array('level' => 0));
    		$info=$this->getnodeid(array('level' => 0));
    		$info=$this->getfieldtype($info);
    		$this->assign("info", $info);
    	
    		//var_dump($this->getnodeid(array('level' => 0)));
    		$this->display("editcontentlist");
    	}
    }
    
    
    public function addRole() {
        if (IS_POST) {
            $this->checkToken();
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->addRole());
        } else {
            $this->assign("info", $this->getRole());
            $this->display("editrole");
        }
    }

    public function editRole() {
        if (IS_POST) {
            $this->checkToken();
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->editRole());
        } else {
            $M = M("Role");
            $info = $M->where("id=" . (int) $_GET['id'])->find();
            if (empty($info['id'])) {
                $this->error("不存在该角色", U('Access/roleList'));
            }
 // var_dump($this->getRole($info));
            $this->assign("info", $this->getRole($info));
            $this->display();
        }
    }

    public function opNodeStatus() {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode(D("Access")->opStatus("Node"));
    }

    public function opRoleStatus() {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode(D("Access")->opStatus("Role"));
    }
    
    public function opIsmenuStatus() {
    	header('Content-Type:application/json; charset=utf-8');
    	echo json_encode(D("Access")->opIsmenu("Node"));
    }

    public function opSort() {
        $M = M("Node");
        $datas['id'] = (int) $this->_post("id");
        $datas['sort'] = (int) $this->_post("sort");
        header('Content-Type:application/json; charset=utf-8');
        if ($M->save($datas)) {
            echo json_encode(array('status' => 1, 'info' => "处理成功"));
        } else {
            echo json_encode(array('status' => 0, 'info' => "处理失败"));
        }
    }

    public function editNode() {
        if (IS_POST) {
            $this->checkToken();
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->editNode());
            //exit;
        } else {
            $M = M("Node");
            $info = $M->where("id=" . (int) $_GET['id'])->find();
            if (empty($info['id'])) {
                $this->error("不存在该节点", U('Access/nodeList'));
            }
            $this->assign("info", $this->getPid($info));
            $this->display();
        }
    }

    public function addNode() {
        if (IS_POST) {
            $this->checkToken();
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->addNode());
        } else {
            $this->assign("info", $this->getPid(array('level' => 1)));
            $this->display("editnode");
        }
    }
    
    
    /*
     * 删除节点
     */
    public function delNode(){
    	
    	header('Content-Type:application/json; charset=utf-8');
    	$NM=new NodeModel();
    	$delresult=$NM->delSignNode((int) $_GET['id']);
    	echo json_encode($delresult);
     }

    /**
      +----------------------------------------------------------
     * 添加管理员
      +----------------------------------------------------------
     */
    public function addAdmin() {
        if (IS_POST) {
						
        		$this->checkToken();
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->addAdmin());
        } else {
            $this->assign("info", $this->getRoleListOption(array('role_id' => 0)));
            $this->display();
        }
    }

    public function changeRole() {
        header('Content-Type:application/json; charset=utf-8');
      
        if (IS_POST) {
        	  $this->checkToken();
            echo json_encode(D("Access")->changeRole());
        	  
        } else {
            $M = M("Node");
            $info = M("Role")->where("id=" . (int) $_GET['id'])->find();
         
            if (empty($info['id'])) {
                $this->error("不存在该用户组", U('Access/roleList'));
            }
           $access = M("Access")->field("CONCAT(`node_id`,':',`level`,':',`pid`) as val")->where("`role_id`=" . $info['id'])->select();
            $info['access'] = count($access) > 0 ? json_encode($access) : json_encode(array());
            $this->assign("info", $info);
            $datas = $M->where("level=1")->select();
            foreach ($datas as $k => $v) {
                $map['level'] = 2;
                $map['pid'] = $v['id'];
                $datas[$k]['data'] = $M->where($map)->select();
                foreach ($datas[$k]['data'] as $k1 => $v1) {
                    $map['level'] = 3;
                    $map['pid'] = $v1['id'];
                    $datas[$k]['data'][$k1]['data'] = $M->where($map)->select();
                }
            }
          //  print_r($datas);
           $this->assign("nodeList", $datas);
            $this->display();
        }
    }

    /**
      +----------------------------------------------------------
     * 添加管理员
      +----------------------------------------------------------
     */
    public function editAdmin() {
        if (IS_POST) {
            $this->checkToken();
        header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->editAdmin());
            //$this->error("超级管理员信ddd息不允许操作", U("Access/index")); 
        } else {
            $M = M("Admin");
            $aid = (int) $_GET['aid'];
            $pre = C("DB_PREFIX");
    
            $info = $M->where("`aid`=" . $aid)->join($pre . "role_user ON " . $pre . "admin.aid = " . $pre . "role_user.user_id")->find();
            if (empty($info['aid'])) {
                $this->error("不存在该管理员ID", U('Access/index'));
            }
            if ($info['email'] == C('ADMIN_AUTH_KEY')) {
                $this->error("超级管理员信息不允许操作", U("Access/index"));
                exit;
            }

            //var_dump($this->getRoleListOption($info));
            $this->assign("info", $this->getRoleListOption($info));
            $this->display("addadmin");
        }
    }

    private function getRole($info = array()) {
        import("Category");
        $cat = new Category('Role', array('id', 'pid', 'name', 'fullname'));
        $list = $cat->getList();   
       //获取分类结构
        foreach ($list as $k => $v) {
            $disabled = $v['id'] == $info['id'] ? ' disabled="disabled"' : "";
            $selected = $v['id'] == $info['pid'] ? ' selected="selected"' : "";
            $info['pidOption'].='<option value="' . $v['id'] . '"' . $selected . $disabled . '>' . $v['fullname'] . '</option>';
        }
        return $info;
    }

    private function getRoleListOption($info = array()) {
        import("Category");
        $cat = new Category('Role', array('id', 'pid', 'name', 'fullname'));
        $list = $cat->getList();               //获取分类结构
       
        
        $info['roleOption'] = "";
        foreach ($list as $v) {
            $disabled = $v['id'] == 1 ? ' disabled="disabled"' : "";
            $selected = $v['id'] == $info['role_id'] ? ' selected="selected"' : "";
            $info['roleOption'].='<option value="' . $v['id'] . '"' . $selected . $disabled . '>' . $v['fullname'] . '</option>';
        }
        return $info;
    }

    private function getPid($info) {
        $arr = array("请选择", "项目", "模块", "操作");
        for ($i = 1; $i < 4; $i++) {
            $selected = $info['level'] == $i ? " selected='selected'" : "";
            $info['levelOption'].='<option value="' . $i . '" ' . $selected . '>' . $arr[$i] . '</option>';
        }
        $level = $info['level'] - 1;
        import("Category");
        $cat = new Category('Node', array('id', 'pid', 'title', 'fullname'));
        $list = $cat->getList();               //获取分类结构
        $option = $level == 0 ? '<option value="0" level="-1">根节点</option>' : '<option value="0" disabled="disabled">根节点</option>';
        foreach ($list as $k => $v) {
            $disabled = $v['level'] == $level ? "" : ' disabled="disabled"';
            $selected = $v['id'] != $info['pid'] ? "" : ' selected="selected"';
            $option.='<option value="' . $v['id'] . '"' . $disabled . $selected . '  level="' . $v['level'] . '">' . $v['fullname'] . '</option>';
        }
        $info['pidOption'] = $option;
        return $info;
    }
    
    private function getnodeid($info) {
    	$arr = array("请选择", "项目", "模块", "操作");
    	for ($i = 1; $i < 4; $i++) {
    		$selected = $info['level'] == $i ? " selected='selected'" : "";
    		$info['levelOption'].='<option value="' . $i . '" ' . $selected . '>' . $arr[$i] . '</option>';
    	}
    	$level = $info['level'] - 1;
    	import("Category");
    	$cat = new Category('Node', array('id', 'pid', 'title', 'fullname'));
    	$list = $cat->getList("level=1 or level=2 or (level=3 and MainTable<>'0')");//获取分类结构
    	foreach($list as $key =>$value){
    		$MainTable=$value['MainTable']?" 主表 ".$value['MainTable']:'';
    		$AsField=$value['AsField']?" 别名 ".$value['AsField']:'';
    		$MainRelationFieldName=$value['MainRelationFieldName']?" 主键 ".$value['MainRelationFieldName']:'';
    		$MainRelationFieldNameAsField=$value['MainRelationFieldNameAsField']?" 主键别名 ".$value['MainRelationFieldNameAsField']:'';
    		$InnerJoinSql=$value['InnerJoinSql']?" 关联".$value['InnerJoinSql']:'';
    		$info['data'][$value['id']]=$MainTable.$AsField.$MainRelationFieldName.$MainRelationFieldNameAsField.$InnerJoinSql;			    	
    	}
    	foreach($list as $key =>$value){
    		//var_dump($value);
    		if($value['MainTable']<>'0'){
    			$TM=M($value['MainTable']);
    			$info['table_feild'][$key]=$TM->getDbFields();
    		}
    	}
    	
    	
    	$option = $level == 0 ? '<option value="0" level="-1">根节点</option>' : '<option value="0" disabled="disabled">根节点</option>';
    	foreach ($list as $k => $v) {
    
    		$selected = $v['id'] != $info['NodeId'] ? "" : ' selected="selected"';
    		$option.='<option value="' . $v['id'] . '"' .  $selected . '  level="' . $v['level'] . '">' . $v['fullname'] . '</option>';
    	}
    	$info['NodeIdOption'] = $option;
    	return $info;
    }
    
    private function getfieldtype($info) {
    	$FT=M("node_modulecontentlist");
    	$t=$FT->Distinct(true)->field('FieldType')->select();
    	$result="<option>请选择</option>";
     	foreach($t as $k=>$v){
    		if(!empty($v['FieldType'])){$result.="<option value='".$v['FieldType']."'>".$v['FieldType']."</option>";}
    	}
    	$info['FTypeOption']=$result;
     	return $info;
    }

    //同步到外网服务器（同步增加节点配置）
    public function synNode(){
        $id=I("id");
        $NM=M('node');
        $NCLM=M('node_modulecontentlist');
        $NCM=M('node_modulecontent');
        $nodeInfo=$NM->where("id=".$id."")->select();
        $nodeContentlist=$NCLM->where("NodeId=".$id."")->select();
        $nodeContent=$NCM->where("NodeId=".$id."")->select();

       // print_r($nodeInfo[0]);
       // print_r($nodeContentlist);
       // print_r($nodeContent);

        $result=$this->saveNodeAllDb2($nodeInfo[0],$nodeContentlist,$nodeContent);
        if(!$result){
            echo "服务器上存在同名节点，同步增加未成功！";
        }else{
            echo "同步增加节点成功！";
        }
       // var_dump($nodeInfo_db2);
        // $this->display();
    }

    //数据写入外网服务器(同步增加节点配置数据)
    private function saveNodeAllDb2($nodeInfo,$nodeContentlist,$nodeContent){
        $NM_db2 = M ( "node", "tdf_", "DB_CONFIG2" );
        $NCLM_db2 = M ( "node_modulecontentlist", "tdf_", "DB_CONFIG2" );
        $NCM_db2 = M ( "node_modulecontent", "tdf_", "DB_CONFIG2" );
        $nodeInfo_db2=$NM_db2->where("title='".$nodeInfo['title']."'")->select();
        if($nodeInfo_db2){
            $result=0;
        }else{
            unset($nodeInfo['id']);
            $result=$NM_db2->add($nodeInfo);
            foreach($nodeContentlist as $key => $value){
                $value['NodeId']=$result;
                unset($value['id']);
                $NCLM_db2->add($value);
            }
            foreach($nodeContent as $key1 => $value1){
                unset($value1['id']);
                $value1['NodeId']=$result;
                $NCM_db2->add($value1);
            }
        }
        return $result;
    }









}