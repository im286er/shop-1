<?php

class MemberAction extends CommonAction {
    public function index($firstRow = 0, $listRows = 20) {
    	$M = M("Users");
    	$list = $M->field("`u_id`,`u_email`")->order("`u_createdate` DESC")->limit("$firstRow , $listRows")->select();
    	//var_dump($list);
    
      $this->display();
    }
    
    public function userlist(){
    	$nodeid=I('nodeid',0,'');
    	$NodeMain=M("node")->field("MainTable,AsFeild,InnerJoinSql,QueryFiterSql,OrderBySql")->where("id=".$nodeid)->select();
			$NodeContentList=M("node_contentlist")->field("EchoName")->where("NodeId=".$nodeid)->select();
    	
			//print_r($NodeContentList);
    	//exit;
    	
    	if(!$this->isPost()){
    		$conditon="u_del=0";
    	}else{
    		$conditon="u_email like '%".$_POST['keyword']."%' and u_del=0";
    		$this->assign('keyword',$_POST['keyword']);// 赋值分页输出
    	}
    	
    	$CM = M('Users');
    	import('ORG.Util.Page');// 导入分页类
    	$AM= M();
    	$count= $CM->where($conditon)->count();// 查询满足要求的总记录数
    	$Page = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
    	$show = $Page->show();// 分页显示输出
    	$sql ="select TA.id,TA.title,TA.author,TAC.cate_name,TA.showtime,TU.u_dispname,TA.sort,";
    	$sql.="(case when TA.status=1 then '已发布' else '未发布' end) as status ";
    	$sql.="from tdf_article as TA ";
    	$sql.="left join tdf_article_cate as TAC On TAC.id=TA.cate ";
    	$sql.="left join tdf_users as TU On TU.u_id=TA.u_id ";
    	$sql.="where ".$conditon." ";
    	$sql.="order by TA.ctime ";
    	$sql.="limit ".$Page->firstRow.",".$Page->listRows."";
    	$list = $AM->query($sql);
    	//$list 	= $CM->where($conditon)->order('ctime')->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('article_array',$list);// 赋值数据集
    	$this->assign('page',$show);// 赋值分页输出
    	$this->display(); // 输出模板
    	
    	
    	
    	$M = M("Users");
    	$list = $M->field("`u_id`,`u_email`")->order("`u_createdate` DESC")->select();
    	$this->assign("contentlist", $list);
    	$TableHead=array("Id","email");//显示表格头部
    	
    	echo "<br><br><br><br><br>";
    	var_dump($TableHead);
    	    	
    	
    	
   
    	$this->display();
   
    }
}