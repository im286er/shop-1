<?php 
class FangAction extends CommonAction{
    
	public function index() {
	    //echo strtoupper(substr( md5(generate_password().microtime()),mt_rand(1,8),12));
	    //echo strtoupper ( substr ( md5 ( generate_password () . microtime () ), mt_rand ( 1, 8 ), 12 ) );
	    //echo generate_ecoupon();
	    //echo generate_ecoupon()."######";
	    //echo generate_ecoupon();
	    /*
	    $Users = M('users');
	    import('ORG.Util.Page');
	    $count =  $Users->where('u_id>4500')->count();
	    
	    $Page = new Page($count,20);
	    $show = $Page->show(1);
	    $list = $Users->where('u_id>4500')->order(u_id)->limit($Page->firstRow.','.$Page->listRows)->select();
	    $this->assign('page',$show);
	    $this->assign('list',$list);
	    $this->display();
	    
	    
	    $User = M('User'); // 实例化User对象
	    import('ORG.Util.Page');// 导入分页类
	    $count      = $User->where('status=1')->count();// 查询满足要求的总记录数
	    $Page       = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数
	    $show       = $Page->show();// 分页显示输出
	    // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
	    $list = $User->where('status=1')->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
	    $this->assign('list',$list);// 赋值数据集
	    $this->assign('page',$show);// 赋值分页输出
	    $this->display(); // 输出模板
	    	  */
	    echo __APP__;
	    
	    
	}
	
	
	
} 
?>
