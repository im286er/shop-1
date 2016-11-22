<?php
/**
 * 首页类
 *
 * @author zhangzhibin 
 * Jul 8, 2013 1:34:10 PM  
 */
class HelpAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct ();
		$this->_assignLoginInfo();
	}
	
	/**
	 * 首页
	 */
	public function index() {
		$this->assign("currenturl",pub_encode_pass(urlencode(get_url()),"3dcity","encode")); //赋值当前url到模板
		$ID=$this->_get("id");
		if(!$this->isGet()){
			$condition="1=1";
		}else{
			$condition="id=".$this->_get("id")."";
		}
		$DC=M();
		$sql="select title,content from tdf_help_doc where ".$condition."";
		$content = $DC->query($sql);
	
		//----------------构造菜单数组
		$AM= M('help_doc');
		$helpdoc = $AM->field('id,title,cate')->where('isdel=0 and status=0')->select();
		$CM = M('help_doc_cate');
		$helpcate= $CM->field('id,cate_name')->where('typeid=0')->select();// 查询满足要求的总记录数
		//var_dump($helpcate);
		foreach($helpcate as $m){
			$i=0;
			foreach($helpdoc as $nkey){
				if($m['id']==$nkey['cate']){
					$i++;
					$arr1[$m['cate_name']][$nkey['id']]=$nkey['title'];
				}
				$arr_temp[$m]=$arr1;
			}
		}
		
// 		/var_dump($arr1);
		$this->assign('id',$ID);
		$this->assign('showtitle',"3DCITY_".$content[0]['title']);
		$this->assign('keywords','模型下载,游戏模型,3D模型,室内模型,MAYA模型,MAX模型,建筑模型,动画模型,CG模型,效果图模型,3D模型下载,3D打印模型,3D打印');
		$this->assign('description','3DCity是专业的3D模型展示,分享,交流平台.我们提供超炫的Web3D引擎,为广大CG用户提供专业的3D模型作品展示,3D模型免费下载平台,也是国内唯一一家提供专业3D打印模型下载平台_'.$content[0]['title']);
		
		//----------------构造菜单数组
		$this->assign('showdoc',$content);
		$this->assign('menus',$arr1);
		$this->assign('header','jewelry');
		$this->_renderPage ();
	}
	
	
	public function dvsdown(){
		
		
		echo "<a href=''>下载dvs2.1版本</a>";
		
			
	}
	public function sitemap(){
		$this->assign("currenturl",pub_encode_pass(urlencode(get_url()),"3dcity","encode")); //赋值当前url到模板
		$this->display();
	}

	/**品牌故事*/
	public function brandstory(){
		//为Nav显示高亮新增参数,不影响其他操作，勿删-Start
		$this->assign('brandshownav',1);
		//为Nav显示高亮新增参数-End
		
		$this->display();
	}

	/**首饰定制*/
	public function jewelrymade(){
		$this->display();
	}

	/**配送说明*/
	public function express(){
		$this->display();
	}

	/**制作工艺*/
	public function craft(){
		$this->display();
	}

	/**购物流程*/
	public function shopflow(){
		$this->display();
	}

	/**在线支付*/
	public function payonline(){
		$this->display();
	}

	/**联系我们*/
	public function contactus(){
		$this->display();
	}

	/**简易测量手围*/
	public function celiangsw(){
		$this->display();
	}

	/**简易测量链长*/
	public function celiangll(){
		$this->display();
	}

	/**简易测量指围*/
	public function celiangzw(){
		$this->display();
	}

	/**饰品保养*/
	public function shipinbaoyang(){
		$this->display();
	}

	/**退货*/
	public function tuihuo(){
		$this->display();
	}

	/**weixiu*/
	public function weixiu(){
		$this->display();
	}

	/* IWX
	 * 第三版手机版品牌故事
	 */
	public function iwxbrandstory(){
		$this->_renderPage();
	}
	/* IWX
	 * 第三版手机版购物指南
	 */
	public function iwxshopguide(){
		$this->_renderPage();
	}
	/* IWX
	 * 第三版手机版售后服务
	 */
	public function iwxafterservice(){
		$this->_renderPage();
	}
	/* IWX
	 * 第三版手机版帮助支持
	 */
	public function iwxsupport(){
		$this->_renderPage();
	}
	
	
	
}