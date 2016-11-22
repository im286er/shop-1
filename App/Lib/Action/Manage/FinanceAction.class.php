<?php
class FinanceAction extends CommonAction {
	public function index() { //财务管理首页
  	$this->display();
	}
  
	public function paybilllist(){ //索取发票列表
		$PB=new UserPayBillModel();
		$result=$PB->getall();
		$this->display();
	}
}