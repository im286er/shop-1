<?php

class IndexAction extends CommonAction {

    public function index() {

    	//$adminlog = $this->addLog(1,"登录",1,$_SESSION['my_info']['aid'],0);//记录后台日志
        if($_SESSION['my_info']['nickname']!=="管理员"){
            echo ("<script>window.parent.location.href='".WEBROOT_URL."/manage.php/empty/index/Pnodeid/2';</script>");
        }
        $todayOrder=$this->getOrderInfo(0,1);
        $fiveOrder=$this->getOrderInfo(1,5);
        $tenOrder=$this->getOrderInfo(5,10);
        $fifteenOrder=$this->getOrderInfo(10,15);
        $upfifteenOrder=$this->getOrderInfo(15,1000);
        $this->assign("todayOrderCount",$todayOrder['count']);
        $this->assign("todayOrderInfo",$todayOrder['info']);
        $this->assign("fiveOrderCount",$fiveOrder['count']);
        $this->assign("fiveOrderInfo",$fiveOrder['info']);
        $this->assign("tenOrderCount",$tenOrder['count']);
        $this->assign("tenOrderInfo",$tenOrder['info']);
        $this->assign("fifteenOrderCount",$fifteenOrder['count']);
        $this->assign("fifteenOrderInfo",$fifteenOrder['info']);
        $this->assign("upfifteenOrderCount",$upfifteenOrder['count']);
        $this->assign("upfifteenOrderInfo",$upfifteenOrder['info']);

        //var_dump($upfifteenOrder['info']);

    	$this->display();
    }

    public function testS(){
        $scode="zhangzhibin";
       // $result=pub_encode_pass($scode,"O4rDRqwshSBojonvTt4mar21Yv1Ehmqm","decode"); //解密
       // $result=pub_encode_pass($scode,"O4rDRqwshSBojonvTt4mar21Yv1Ehmqm","encode"); //解密
     //  $t="70827609aypwsrLDJeG38mdVV8Z18JK3dzXnxXZghWazMNOFhwEFdgTQM=";
        $t="9f62ad38fKTOFSXUMTAQ9SUwo=";
        $de_result=pub_encode_pass($t,"O4rDRqwshSBojonvTt4mar21Yv1Ehmqm","decode");
        //echo "原始:".$scode;
        //echo " 加密后:".$result;
        echo "解密后:".$de_result;
        //echo " 解密后:".$result;
    }

    public function test_curl_get(){
      // echo "this is test_curl_get";
        //http://140.207.154.14/xfx/index.php/index/getdatas/openid/2/avatar/cc/dispname/aaaa
        $ch = curl_init("http://140.207.154.14/xfx/index.php/index/getdatas/openid/233/avatar/abc/dispname/abc") ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_exec($ch) ;
        /* 写入文件 */
        //$fh = fopen("out.html", 'w') ;
        //fwrite($fh, $output) ;
        //fclose($fh) ;

    }


    public function myInfo() {
        if (IS_POST) {
            $this->checkToken();
            echo json_encode(D("Index")->my_info($_POST));
        } else {
            $this->display();
        }
    }

    public function cache() {
//        $caches = array(
//            "allCache" => WEB_CACHE_PATH,
//            "allRunCache" => WEB_CACHE_PATH . "Runtime/",
//            "allAdminRunCache" => WEB_CACHE_PATH . "Runtime/Admin/",
//            "allHomeRunCache" => WEB_CACHE_PATH . "Runtime/Home/",
//            "allHomeRunCache" => WEB_CACHE_PATH . "Runtime/Home/",
//        );
        $caches = array(
            "HomeCache" => array("name" => "网站前台缓存文件", "path" => WEB_CACHE_PATH . "Home/Cache/"),
            "AdminCache" => array("name" => "网站后台缓存文件", "path" => WEB_CACHE_PATH . "Runtime/Admin/Cache/"),
            "HomeData" => array("name" => "网站前台数据库字段缓存文件", "path" => WEB_CACHE_PATH . "Runtime/Home/Data/"),
            "AdminData" => array("name" => "网站后台数据库字段缓存文件", "path" => WEB_CACHE_PATH . "Runtime/Admin/Data/"),
            "HomeLog" => array("name" => "网站前台日志缓存文件", "path" => WEB_CACHE_PATH . "Runtime/Home/Logs/"),
            "AdminLog" => array("name" => "网站后台日志缓存文件", "path" => WEB_CACHE_PATH . "Runtime/Admin/Logs/"),
            "HomeTemp" => array("name" => "网站前台临时缓存文件", "path" => WEB_CACHE_PATH . "Runtime/Home/Temp"),
            "AdminTemp" => array("name" => "网站后台临时缓存文件", "path" => WEB_CACHE_PATH . "Runtime/Admin/Temp"),
            "Homeruntime" => array("name" => "网站前台runtime.php缓存文件", "path" => WEB_CACHE_PATH . "Runtime/Home/~runtime.php"),
            "Adminruntime" => array("name" => "网站后台runtime.php缓存文件", "path" => WEB_CACHE_PATH . "Runtime/Admin/~runtime.php"),
            "MinFiles" => array("name" => "JS\CSS压缩缓存文件", "path" => WEB_CACHE_PATH . "MinFiles/")
        );
        if (IS_POST) {
            foreach ($_POST['cache'] as $path) {
                if (isset($caches[$path]))
                    delDirAndFile($caches[$path]['path']);
            }
//            pre($_POST);
//            $this->checkToken();
            echo json_encode(array("status"=>1,"info"=>"缓存文件已清除"));
        } else {
            $this->assign("caches", $caches);
            $this->display();
        }
    }

    /*
     * 通过天数获取订单ID
     * @param $beginday起始天数  ,$endday结尾天数
     */
    private function getOrderInfo($beginday,$endday){
        $condition_beginday=" (".time()." - unix_timestamp(up_orderbacktime))/(24*3600) > ".$beginday;
        $condition_endday=" (".time()." - unix_timestamp(up_orderbacktime))/(24*3600) <= ".$endday;
        $todayOrderInfo=M("user_prepaid")->field("up_id,up_amount,up_orderbacktime,up_done_status,ceil((".time()." - unix_timestamp(up_orderbacktime))/(24*3600)) as day")->where("up_status=1 and up_done_status<>'订单交易完成' and (".$condition_beginday.") and (".$condition_endday.")")->order("up_id desc")->select();
        $todayOrder['count']=count($todayOrderInfo);
        $todayOrder['info']=$todayOrderInfo;
        return $todayOrder;
    }

}