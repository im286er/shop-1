<?php
/*
 * @thinkphp3.2.2  auth认证   php5.3以上
 * @Created on 2015/08/18
 * @Author  夏日不热(老屁)   757891022@qq.com
 *
 */
namespace Admin\Controller;
use Common\Controller\AuthController;
use Think\Auth;

//后台会员
class UserController extends AuthController {
		
	//用户列表
     public function user_list(){
    	$m = M('user');
    	$nowPage = isset($_GET['p'])?$_GET['p']:1;
    	
    	// page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
    	$data = $m->order('id DESC')->page($nowPage.','.PAGE_SIZE)->select();
    	foreach ($data as $k=>$v){
    		$data[$k]['create_time'] = date('Y-m-d H:i:s',$data[$k]['create_time']);
    	}
    	//分页
    	$count = $m->where($where)->count(id);		// 查询满足要求的总记录数
    	$page = new \Think\Page($count,PAGE_SIZE);		// 实例化分页类 传入总记录数和每页显示的记录数
    	$show = $page->show();		// 分页显示输出
    	$this->assign('page',$show);// 赋值分页输出
    	$this->assign('data',$data);
    	$this->display();
    }

    //删除用户
    public function user_del(){
    	$where['id'] = $_POST['id'];	//活动ID
    	$m = M('user');
    	$result = $m->where($where)->delete();
    	if($result){
    		$this->ajaxReturn(1);
    	}else{
    		$this->ajaxReturn(0);
    	}
    }
    //收藏列表
    public function collect_list(){
    	$m = M('Collect');
    	$nowPage = isset($_GET['p'])?$_GET['p']:1;
    	 
    	// page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
    	$data = $m->order('id DESC')->page($nowPage.','.PAGE_SIZE)->select();
    	//分页
    	$count = $m->where($where)->count(id);		// 查询满足要求的总记录数
    	$page = new \Think\Page($count,PAGE_SIZE);		// 实例化分页类 传入总记录数和每页显示的记录数
    	$show = $page->show();		// 分页显示输出
    	$this->assign('page',$show);// 赋值分页输出
    	$this->assign('data',$data);
    	$this->display();
    }
    
    //发送优惠券
    public function coupons_add(){
    	if(!empty($_POST)){
    		$m = M('user_coupons');
    		$_POST['mobile'] = $_COOKIE['mobile'];
    		$_POST['create_time'] = time();
    		$result = $m->add($_POST);
    		if($result){
    			$this->success('发送成功');
    		}else{
    			$this->error('发送失败');
    		}
    	}else{
    		$result['begin_date'] = date('Y-m-d',time()+86400);
    		$result['end_date'] = date('Y-m-d',time()+86400*7);
    		$this->assign('result',$result);
    		$this->display();
    	}
    } 
    
    //优惠券列表
    public function coupons_list(){
    	if(!empty($_POST)){
    		$m = M('user_coupons');
    		$_POST['mobile'] = $_COOKIE['mobile'];
    		$_POST['create_time'] = time();
    		$result = $m->add($_POST);
    		if($result){
    			$this->success('发送成功');
    		}else{
    			$this->error('发送失败');
    		}
    	}else{
    		$m = M('user_coupons');
    		$nowPage = isset($_GET['p'])?$_GET['p']:1;   		
    		// page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
    		$result = $m->order('id DESC')->page($nowPage.','.PAGE_SIZE)->select();
    		//分页
    		$count = $m->where($where)->count(id);		// 查询满足要求的总记录数
    		$page = new \Think\Page($count,PAGE_SIZE);		// 实例化分页类 传入总记录数和每页显示的记录数
    		$show = $page->show();		// 分页显示输出
    		$this->assign('page',$show);// 赋值分页输出
    		$this->assign('result',$result);
    		$this->display();
    	}
    }  

    //删除优惠券
    public function coupons_del(){
        $where['id'] = $_POST['id'];	//活动ID
    	$m = M('user_coupons');
    	$result = $m->where($where)->delete();
    	if($result){
    		$data['code'] = '1';	//删除成功
    		$this->ajaxReturn($data);
    	}else{
    		$data['code'] = '0';	//删除失败
    		$this->ajaxReturn($data);
    	}
    }
    
    //关于我们
    public function about_us(){
    	if(!empty($_POST)){
    		$m = M('page');
    		$where['columu'] = 'about_us';	//栏目名称
    		$_POST['update_time'] = time();	//更新时间
    		$result = $m->where($where)->save($_POST);	
    		if($result){
    			$this->success('保存成功');
    		}else{
    			$this->error('保存失败');
    		}
    	}else{
			$m = M('page');
			$where['columu'] = 'about_us';	//栏目名称
	    	$result = $m->where($where)->find();
	    	$this->assign('result',$result);
	    	$this->display();
    	}
    }
    
    //我的特权
    public function my_tequan(){
    	if(!empty($_POST)){
    		$m = M('page');
    		$where['columu'] = 'my_tequan';	//栏目名称
    		$_POST['update_time'] = time();	//更新时间
    		$result = $m->where($where)->save($_POST);	
    		if($result){
    			$this->success('保存成功');
    		}else{
    			$this->error('保存失败');
    		}
    	}else{
			$m = M('page');
			$where['columu'] = 'my_tequan';	//栏目名称
	    	$result = $m->where($where)->find();
	    	$this->assign('result',$result);
	    	$this->display();
    	}
    }
    
    //短信管理
    public function user_sms_list(){
    	$m = M('user_sms');
    	if(!empty($_POST['mobile'])){
    		$where['mobile'] = $_POST['mobile'];	//手机号
    	}
    	$nowPage = isset($_GET['p'])?$_GET['p']:1;
    	$result = $m->where($where)->order('id DESC')->page($nowPage.','.PAGE_SIZE)->select();
    	//分页
    	$count = $m->where($where)->count(id);		// 查询满足要求的总记录数
    	$page = new \Think\Page($count,PAGE_SIZE);		// 实例化分页类 传入总记录数和每页显示的记录数
    	$show = $page->show();		// 分页显示输出
    	$this->assign('page',$show);// 赋值分页输出
    	$this->assign('data',$result);
    	$this->display();
    }
    
    //删除短信记录
    public function user_sms_del(){
        $where['id'] = $_POST['id'];	//短信记录ID
    	$m = M('user_sms');
    	$result = $m->where($where)->delete();
    	if($result){
    		$this->ajaxReturn(1);	//删除成功
    	}else{
    		$this->ajaxReturn(0);
    	}
    }
}


