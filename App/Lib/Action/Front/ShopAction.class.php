<?php
class ShopAction extends CommonAction { //打印机商店主页
	
		
	
	public function index(){
		$this->assign("currenturl",pub_encode_pass(urlencode(get_url()),"3dcity","encode")); //赋值当前url到模板
		
		//商城首页幻灯片————————start————————
		$HM=new HelpModel();
		$ShopIndexPic=$this->getShopIndexPic();
		$this->assign('ShopIndexPic',$ShopIndexPic);
		//商城首页幻灯片————————end——————————
		
		$TP=new ProductModel();
		$ProductList=$TP->getPrinterIndex(1259); //3D打印机
		$this->assign('ProductList',$ProductList);
		//var_dump($ProductList);
		
		$ProductListHC=$TP->getPrinterIndex(1260);//打印耗材
		$this->assign('ProductListHC',$ProductListHC);
		$this->_renderPage ();
		
		//$this->display();
	}
	
	public function detail(){//商品详情
		$IsLogined = isset ( $_SESSION ['f_userid'] );
		if ($this->_isLogin ()) {
			$loginUser = D ( 'Users' );
			$loginUser->find ( $this->_session ( 'f_userid' ) );
			$this->assign ( 'LoginUser', $loginUser->data () );
		}
	
		$pid=I('id',0,'intval');
		
		$TP=new ProductModel();
		$Product=$TP->getShoppingInfoByID($pid);
		
		$Product[0]['p_cate_3_name']=$this->getCateName($Product[0]['p_cate_3']);
//var_dump($Product);
		$AboutProduct=$this->getProductByCateId($Product[0]['p_cate_3'],$Product[0]['p_id']);//相关产品
		
		$showtitle=cutproductname($Product[0]['p_name'])." 3D打印机-3D城";
		$this->assign('showtitle',$showtitle);
		
		
		$this->assign ( 'IsLogined', $IsLogined );
		$this->assign('AboutProduct',$AboutProduct);
		$this->assign('Product',$Product[0]);
		$this->_renderPage ();
	}
		
	/*
	 * 获得商城首页幻灯片信息
	*/
	private function getShopIndexPic(){
		$IP=new HelpModel();
		$result=$IP->getindexpic(10);
		return $result;
	}
	
	private function getCateName($cid){
		$CM= new CatesModel();
		$Cateinfo=$CM->getCategoryByCID($cid);
		return $Cateinfo['pc_name'];
	}
	
	private function getProductByCateId($cateid,$pid){//根据cateid获得相关产品
		$TP=new ProductModel();
		$result=$TP->getPrinterIndex($cateid,5);
		foreach($result as $key=>$value){
			if($pid==$value['p_id']){
				unset($result[$key]);
			}
		}
		return $result;
	}
	
	
}
?>