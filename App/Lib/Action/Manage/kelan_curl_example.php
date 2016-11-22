<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 19/7/16
 * Time: 11:08
 */




/**
 * curl请求ordernotify接口
 * 请求地址: http://www.ignjewelry.com/api.php/services/rest
 */
public function curlOrdernotify(){
    $pubKey         = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
    $method         = 'front.ordernotify';
    $format         = 'json';
    $debug          = 0;
    $user           = 'kl@ignjewelry.com';
    $pass           = '123456';
    $visa           = base64_encode($user . ' ' . $pass);
    $curlPost       = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
    $vcode          = $this->_genVcode();
    $sign           = $this->gensign($curlPost, $vcode, $pubKey);
    $dataArr['pid'] = 1850; //pid是定制购买后,返回的pid
    $dataArr['count']= 1;   //定制数量,不写默认为1

    $sendData   = base64_encode(json_encode($dataArr));  //参数base64
    $curlPost   = array(
        'method' => $method,
        'visa'   => $visa,
        'format' => $format,
        'vcode'  => $vcode,
        'sign'   => $sign,
        'debug'  => $debug,
        'datas'   => $sendData
    );
    $this->_curlPost($curlPost);
}

/**
 * CurlPost
 *
 * @param array $curlReq
 * @param int $return
 * @return mixed
 */
private function _curlPost($curlReq, $return = 0)
{
    $_ua = 'phpCurl-agent/1.0';
    $_restUrl = "http://www.ignjewelry.com/api.php/services/rest";

    // CURL
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $_restUrl,
        CURLOPT_POSTFIELDS => $curlReq,
        CURLOPT_USERAGENT => $_ua
    ));
    $response = curl_exec($ch);
    curl_close($ch);

    if ($return) {
        return $response;
    } else {
        print_r($response);
    }
}




    /**
     * 生成一个签名
     *
     * @param string $parameter
     * @param $vcode 必须是1-28
     * @param string $pubkey
     *        	必须是32位
     */
    function gensign($parameter, $vcode, $pubkey)
    {
        $cutstart = $vcode - 1;
       
        //exit;
        return md5(md5($parameter) . substr($pubkey, $cutstart, 4));

    }
    /**
     * 获取一个Vcode
     */
    function _genVcode()
    {
        $min = 1;
        $max = 28;
        return genvcode($min, $max);
    }
}



