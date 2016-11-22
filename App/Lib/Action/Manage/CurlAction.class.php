<?php

/**
 * Curl模块
 *
 * @author miaomin
 * Oct 15, 2013 11:49:30 AM
 *
 * $Id: CurlAction.class.php 1093 2013-12-09 03:22:41Z miaomiao $
 */
class CurlAction extends CommonAction
{

    // 公钥
    private $_publicKey = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';

    // 远程主机地址
    // private $_remoteHost = 'http://localhost/3DPrinter/';
    // private $_remoteHost = 'http://sh1dt110/3DPrinter/';
    // private $_remoteHost = 'http://192.168.40.21/ignite';
    private $_remoteHost = WEBROOT_URL;

    // REST服务地址
    private $_serviceUrl = '/api.php/services/rest';

    // REST服务调用地址
    private $_restUrl;

    // User-Agent
    private $_ua = 'phpCurl-agent/1.0';

    /**
     * Curl模块
     */
    public function __construct()
    {
        parent::__construct();

        $this->_restUrl = $this->_remoteHost . $this->_serviceUrl;
    }

    /**
     * 首页
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 暗示
     */
    public function hint()
    {
        $str = new stdClass ();
        echo $this->_hint($str);
    }

    /**
     * 暗示
     *
     * @param int $str
     * @return int
     */
    private function _hint(int $str)
    {
        return $str;
    }

    // 新计价测试
    public function calctest()
    {
        import('App.Model.CalcPrinterObject');

        // 模型信息
        $boundBox = 984165;
        $volume = 35;
        $ratio = round(($volume / $boundBox), 2);
        // $ratio = 50;
        $surfaceArea = 44199;
        $repairLevel = 1;
        $repairFee = $repairLevel * 50;
        $postFee = 0;
        $pmArr = array(
            'boundBox' => $boundBox,
            'volume' => $volume,
            'ratio' => $ratio,
            'surfaceArea' => $surfaceArea,
            'repairLevel' => $repairLevel,
            'repairFee' => $repairFee,
            'postFee' => $postFee
        );

        // 材质信息
        $pmaId = 100;
        $PMA = new PrinterMaterialModel ();
        $PMA->find($pmaId);

        // 计价模型初始化
        $CPO = new CalcPrinterObject ();
        $CPO->transMap($PMA);
        $CPO->transMap($pmArr);
        $paraArr = $CPO->getPara();
        pr($paraArr);

        // 计价模型判断加工
        $isMod = false;
        $PPF = new PrinterPriceFormulaModel ();
        foreach ($paraArr as $key => $val) {
            if ($val == -1) {
                $isMod = true;
                $modCon [] = $PPF->getTypeVal($key);
                // 需做判断加工
            }
        }

        if ($isMod) {
            $ppfWhere = db_create_in($modCon, 'ppf_type');
            $ppfRes = $PPF->where($ppfWhere)->select();
            if ($ppfRes) {
                foreach ($ppfRes as $key => $val) {
                    $condition = replace_string_vars($val ['ppf_condition'], $paraArr);
                    if (eval ("return $condition;")) {
                        $formula = replace_string_vars($val ['ppf_formula'], $paraArr);
                        $paraArr [$PPF->getTypeKey($val ['ppf_type'])] = eval ("return $formula;");
                    }
                }
            }
        }

        pr($paraArr);

        // 开始计价
        $ppfCon = array(
            'pma_id' => $pmaId,
            'ppf_type' => 1
        );
        $ppfRes = $PPF->where($ppfCon)->select();

        if (count($ppfRes) == 1) {
            $formula = replace_string_vars($ppfRes [0] ['ppf_formula'], $paraArr);
            echo $formula;
            $calcRes = eval ("return $formula;");
            if ($ppfRes [0] ['ppf_condition']) {
                $calcArr = array(
                    'quote' => $calcRes
                );
                $CPO->transMap($calcArr);
                $paraArr = $CPO->getPara();

                $condition = replace_string_vars($ppfRes [0] ['ppf_condition'], $paraArr);
                $calcRes = eval ("return $condition;");
            }
            echo 'Result: $' . $calcRes;
            exit ();
        } else {
            foreach ($ppfRes as $key => $val) {
                $condition = replace_string_vars($val ['ppf_condition'], $paraArr);
                if (eval ("return $condition;")) {
                    vd($val ['ppf_formula']);
                    $formula = replace_string_vars($val ['ppf_formula'], $paraArr);
                    echo $formula;
                    echo 'Result: $' . eval ("return $formula;");
                    exit ();
                }
            }
        }
    }

    /**
     * Blocklist
     */
    public function blocklist()
    {
        $md5 = '6cdf6d923a9404d010c14595c30dc368';
        $sha1 = '5b57b632ec30df8e2bf75f597edad2ecfd5d24cb';
        $BFM = new BlockFilesModel ();
        $res = $BFM->getBlockList($md5, $sha1);
        pr($res);
    }

    /**
     * Blockmerge
     */
    public function blockmerge()
    {
        $md5 = '6cdf6d923a9404d010c14595c30dc368';
        $sha1 = '5b57b632ec30df8e2bf75f597edad2ecfd5d24cb';
        $BFM = new BlockFilesModel ();
        $res = $BFM->mergeBlockList($md5, $sha1, 8);
        pr($res);
    }

    /**
     * 日志用
     */
    public function log()
    {
        try {
            $log = LogFactoryModel::init('client');
            $data = array(
                'u_id' => 1
            );
            $res = $log->insertLog($data);
            if ($res) {
                // 记录日志成功
            } else {
                // 失败
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            exit ();
        }
    }

    /**
     * 测试用
     */
    public function visa()
    {
        $username = 'joer@163.com';
        $password = strtoupper(md5('joer'));
        $ciphertxt = base64_encode($username . ' ' . $password);
        echo $ciphertxt;
    }

    public function unvisa()
    {
        $visa = 'dGhvdWdodC14QDE2My5jb20gZTEwYWRjMzk0OWJhNTlhYmJlNTZlMDU3ZjIwZjg4M2U=';
        $plaintext = base64_decode($visa);
        pr($plaintext);
    }

    /**
     * 解压缩ZIP
     */
    public function zip()
    {
        $filename = './524fac306d6d4.zip';
        $res = extractZip($filename);
        var_dump($res);
    }

    /**
     * 解压缩RAR
     */
    public function rar()
    {
        // php5.4无法完成
    }

    /**
     * 正则解析
     */
    public function pospos()
    {
        $subject = '1-20';
        $pattern = '/^[1-9]{1}[0-9]*-[1-9]{1}[0-9]*$/';
        $res2 = preg_match($pattern, $subject);
        dump($res2);
    }

    /**
     * hash计算
     */
    public function hash()
    {
        $filename = 'D:\西方建筑.stl';
        $filename = iconv('UTF-8', 'GB2312', $filename);
        $res = file_exists($filename);

        // $filename = 'D:\xfjz.stl';
        echo md5_file($filename);
        echo '<br><br>';
        echo sha1_file($filename);
    }

    /**
     * 云已使用容量计算
     */
    public function refresh()
    {
        $UF = new UserFilesModel ();

        $ufRes = $UF->helper->listUserFile(1);
        $totalsize = 0;
        foreach ($ufRes as $key => $val) {
            $totalsize += $val ['yf_size'];
        }

        if ($totalsize) {
            $res = $UF->helper->updateUsedCapacity(1, $totalsize, 'UPDATE');
        }

        vd($res);
    }


    /**
     * 新计算单个模型价格
     *
     * lastcheck: 2013/11/8
     */
    public function singlecalc()
    {
        try {
            if ($this->isPost()) {
                $method = 'orders.checkprice';
                $format = 'json';
                $debug = 0;
                $user = 'wow730@gmail.com';
                $pass = md5('123456');
                $visa = base64_encode($user . ' ' . $pass);

                /* 计价参数 */
                // 材质ID
                $cartitem_material = ( int )$this->_post('pmaid');
                // 模型体积
                $cartitem_volume = ( float )$this->_post('volume');
                // 最小包围盒长
                $cartitem_minBoundBox_L = ( float )$this->_post('minBoundBoxL');
                // 最小包围盒宽
                $cartitem_minBoundBox_W = ( float )$this->_post('minBoundBoxW');
                // 最小包围盒高
                $cartitem_minBoundBox_H = ( float )$this->_post('minBoundBoxH');
                // 模型表面积
                $cartitem_surfaceArea = ( float )$this->_post('surfaceArea');
                // 模型待修理级别
                $cartitem_repairLevel = ( int )$this->_post('repairLevel');
                // Debug
                $cartitem_calcdebug = ( int )$this->_post('cdebug');

                // 生成签名
                $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
                $vcode = $this->_genVcode();
                $sign = gensign($curlPost, $vcode, $this->_publicKey);

                // Curl
                $curlPost = array(
                    'method' => $method,
                    'visa' => $visa,
                    'format' => $format,
                    'vcode' => $vcode,
                    'sign' => $sign,
                    'debug' => $debug,
                    'cartitem_material' => $cartitem_material,
                    'cartitem_volume' => $cartitem_volume,
                    'cartitem_minBoundBox_L' => $cartitem_minBoundBox_L,
                    'cartitem_minBoundBox_W' => $cartitem_minBoundBox_W,
                    'cartitem_minBoundBox_H' => $cartitem_minBoundBox_H,
                    'cartitem_surfaceArea' => $cartitem_surfaceArea,
                    'cartitem_repairLevel' => $cartitem_repairLevel,
                    'cartitem_calcdebug' => $cartitem_calcdebug
                );

                $curlRes = $this->_curlPost($curlPost, 1);
                $curlRes = json_decode($curlRes);

                // 赋值
                $this->assign('calcResult', $curlRes [1]);
                $this->assign('volume', $cartitem_volume);
                $this->assign('minBoundBox_L', $cartitem_minBoundBox_L);
                $this->assign('minBoundBox_W', $cartitem_minBoundBox_W);
                $this->assign('minBoundBox_H', $cartitem_minBoundBox_H);
                $this->assign('surfaceArea', $cartitem_surfaceArea);
                $this->assign('repairLevel', $cartitem_repairLevel);
            }
            $PMA = new PrinterMaterialModel ();
            if ($cartitem_material) {
                $pmaOpt = $PMA->getPMAOption("pma_parentid <> '0'", $cartitem_material);
            } else {
                $pmaOpt = $PMA->getPMAOption("pma_parentid <> '0'");
            }
            // 赋值
            $this->assign('pmaOption', $pmaOpt);
            $this->display();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 新计算单个模型价格
     *
     * lastcheck: 2013/11/8
     */
    public function singlecalc2()
    {
        $method = 'orders.checkprice';
        $format = 'xml';
        $debug = 0;
        $user = 'wow730@gmail.com';
        $pass = md5('123456');
        $visa = base64_encode($user . ' ' . $pass);

        /* 计价参数 */
        // 材质ID
        $cartitem_material = ( int )$this->_request('pmaid');
        // 模型体积
        $cartitem_volume = ( float )$this->_request('volume');
        // 最小包围盒长
        $cartitem_minBoundBox_L = ( float )$this->_request('minBoundBoxL');
        // 最小包围盒宽
        $cartitem_minBoundBox_W = ( float )$this->_request('minBoundBoxW');
        // 最小包围盒高
        $cartitem_minBoundBox_H = ( float )$this->_request('minBoundBoxH');
        // 模型表面积
        $cartitem_surfaceArea = ( float )$this->_request('surfaceArea');
        // 模型待修理级别
        $cartitem_repairLevel = ( int )$this->_request('repairLevel');
        // Debug
        $cartitem_calcdebug = ( int )$this->_request('cdebug');

        // 生成签名
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        // Curl
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'cartitem_material' => $cartitem_material,
            'cartitem_volume' => $cartitem_volume,
            'cartitem_minBoundBox_L' => $cartitem_minBoundBox_L,
            'cartitem_minBoundBox_W' => $cartitem_minBoundBox_W,
            'cartitem_minBoundBox_H' => $cartitem_minBoundBox_H,
            'cartitem_surfaceArea' => $cartitem_surfaceArea,
            'cartitem_repairLevel' => $cartitem_repairLevel,
            'cartitem_calcdebug' => $cartitem_calcdebug
        );

        $this->_curlPost($curlPost);
    }

    /**
     * 文件是否存在判断
     */
    public function fileexist()
    {
        // TODO
        // $method不能写在这里
        $method = 'models.isfileexist';
        $format = 'xml';
        $debug = 0;
        $user = 'wow730@gmail.com';
        $pass = md5('123456');
        $visa = base64_encode($user . ' ' . $pass);
        $md5 = '0e853548a92b4ed3f7e4bc443ecc93f3';
        $sha1 = 'df5af2bb6e0a905c48fe3cdaa06178d4a8724eb1';

        // 生成签名
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        $curlPost = array(
            'method' => $method,
            // 'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'md5' => $md5,
            'sha1' => $sha1,
            'debug' => $debug
        );

        $this->_curlPost($curlPost);
    }

    /**
     * 获取Plugin列表
     */
    public function getpluginlist()
    {
        // TODO
        // $method不能写在这里
        $method = 'models.getpluginlist';
        $format = 'xml';
        $debug = 0;
        $user = 'joer@163.com';
        $pass = md5('joer');
        $visa = base64_encode($user . ' ' . $pass);

        // 生成签名
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );

        $this->_curlPost($curlPost);
    }


    /**
     * 是否注册
     * 只判断邮箱是否被注册掉
     */
    public function isregistermobile()
    {
        $method = 'users.isregistermobile';
        $format = 'json';
        $debug = 0;
        $user = '13611999605';
        $pass = md5('123422256');
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        // 生成签名
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 是否注册
     *
     * 只判断邮箱是否被注册掉
     */
    public function isregister()
    {
        $method = 'users.isregister';
        $format = 'json';
        $debug = 0;
        $user = 'wow730@gmail.com';
        $pass = md5('123456');
        $visa = base64_encode($user . ' ' . $pass);

        // 生成签名
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 找回密码
     */
    public function findpwd()
    {
        try {
            $method = 'users.findpwd';
            $format = 'json';
            $debug = 0;
            $user = 'miaomin@bitmap3d.com.cn';
            $pass = md5('111111');
            $visa = base64_encode($user . ' ' . $pass);

            // 生成签名
            $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
            $vcode = $this->_genVcode();
            $sign = gensign($curlPost, $vcode, $this->_publicKey);

            $curlPost = array(
                'method' => $method,
                'visa' => $visa,
                'format' => $format,
                'vcode' => $vcode,
                'sign' => $sign,
                'debug' => $debug
            );

            $this->_curlPost($curlPost);
        } catch (Exception $e) {

            echo $e->getMessage();
        }
    }

    /**
     * 用户登录
     */
    public function login()
    {
        $method = 'users.login';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        //$user = '18621118091';18201798541 3dcityfamily
        //$pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['apptype'] = "1";
        $datas = base64_encode(json_encode($datas));
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas
        );
        $this->_curlPost($curlPost);
    }

    /*
     * 发送手机验证码_注册
     */
    public function mobsendcode()
    {
        $method = 'users.mobsendcode';
        $format = 'json';
        $debug = 0;
        $user = '18201798541';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['codetype'] = 1;
        $datas = base64_encode(json_encode($datas));
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas
        );
        $this->_curlPost($curlPost);
    }

    /*
     * 发送手机验证码_找回密码
     */
    public function mobsendcode_findpass()
    {
        $method = 'users.mobsendcode';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = md5('123456');
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['codetype'] = 2;
        $datas = base64_encode(json_encode($datas));
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas
        );
        $this->_curlPost($curlPost);
    }

    /*
    * 手机验证码验证
    */
    public function verifymobcode()
    {
        $method = 'users.verifymobcode';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = md5('123456');
        $visa = base64_encode($user . ' ' . $pass);
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );
        $this->_curlPost($curlPost);
    }


    /**
     * 用户注册
     */
    public function register()
    {
        $method = 'users.register';
        $format = 'json';
        $debug = 0;
        $user = '18201798541';
        $pass = '123456';
        // $verifycode="784024";
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        //$datas['codetype']="";
        $datas['verifycode'] = "167406";
        $datas['apptype'] = "1";
        $datas = base64_encode(json_encode($datas));

        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 用户注册
     */
    public function resetpass()
    {
        $method = 'users.resetpass';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '3dcity';
        // $verifycode="326291";
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $datas['verifycode'] = "220647";//验证码
        $datas = base64_encode(json_encode($datas));
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas
        );
        $this->_curlPost($curlPost);
    }

    /**
     * TEST
     */
    public function test()
    {
        $method = 'users.test';
        $format = 'xml';
        $debug = 0;
        $user = 'wow730@gmail.com';
        $pass = md5('123456');
        $visa = base64_encode($user . ' ' . $pass);

        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';

        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            // 'xxx' => iconv ( 'UTF-8', 'GB2312', '中文字符.zip' )
            'xxx' => iconv('UTF-8', 'UTF-8', '中文字符.zip')
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
        // CURL
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->_restUrl,
            CURLOPT_POSTFIELDS => $curlReq,
            CURLOPT_USERAGENT => $this->_ua
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        // 这句话不能拿掉啊拿掉就返回不到结果啦！！！
        if ($return) {
            return $response;
        } else {
            print_r($response);
        }
    }

    /**
     * 获取一个Vcode
     */
    private function _genVcode()
    {
        $min = 1;
        $max = 28;
        return genvcode($min, $max);
    }

    /**
     * 模拟提交
     *
     * //TODO
     *
     * 如果传送参数有问题需要考虑URL_ENCODE
     */
    public function cpost()
    {
        $method = 'users.getuserinfo';
        // $visa = 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==';
        $format = 'xml';
        $debug = 0;
        $user = 'wow730@gmail.com';
        $pass = md5('wow730');
        $visa = base64_encode($user . ' ' . $pass);

        // @formatter:off
		// $curlPost = 'format=' . urlencode ( 'json' ) . '&method=' . urlencode ( 'users.getuserinfo' ) . '&visa=' . urlencode ( 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==' ) . '';
		$curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
		// @formatter:on

        // 生成签名
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        // 待POST数据
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );

        $this->_curlPost($curlPost);
    }

    /**
     * 快速上传块文件
     */
    public function qcbpost()
    {
        for ($i = 0; $i < 7; $i++) {

            $method = 'models.upfile';
            $visa = 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==';
            $format = 'xml';
            $debug = 0;
            $md5 = '6cdf6d923a9404d010c14595c30dc368';
            $sha1 = '5b57b632ec30df8e2bf75f597edad2ecfd5d24cb';
            $uptype = 2;
            $targetname = 'Div_123.rar';
            $targetext = 'rar';
            $blockpos = $i + 1 . '-8';
            $filename1 = 'D:\Zend\WorkSpace\3DPrinter\cut\123.00' . $i;

            $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';

            $vcode = $this->_genVcode();
            $sign = gensign($curlPost, $vcode, $this->_publicKey);

            // 必须是以数组的方式才能提交文件哦
            $curlPost = array(
                'method' => $method,
                'visa' => $visa,
                'format' => $format,
                'filename1' => '@' . $filename1,
                // 'filename2' => '@' . $filename2,
                'vcode' => $vcode,
                'sign' => $sign,
                'md5' => $md5,
                'sha1' => $sha1,
                'uptype' => $uptype,
                'blockpos' => $blockpos,
                'targetname' => $targetname,
                'targetext' => $targetext,
                'debug' => $debug
            );

            $this->_curlPost($curlPost);
        }
    }

    /**
     * Block模拟提交
     */
    public function cbpost()
    {
        $method = 'models.upfile';
        $user = 'wow730@gmail.com';
        $pass = md5('123456');
        $visa = base64_encode($user . ' ' . $pass);
        // $visa = 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==';
        $format = 'xml';
        $debug = 0;
        $filename1 = 'D:\isutf.rar';
        $md5 = '6cdf6d923a9404d010c14595c30dc368';
        $sha1 = '5b57b632ec30df8e2bf75f597edad2ecfd5d24cb';
        $uptype = 2;
        $blockpos = '1-8';
        $targetname = 'Div_123.rar';
        $targetext = 'rar';

        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';

        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        // 必须是以数组的方式才能提交文件哦
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'filename1' => '@' . $filename1,
            // 'filename2' => '@' . $filename2,
            'vcode' => $vcode,
            'sign' => $sign,
            'md5' => $md5,
            'sha1' => $sha1,
            'uptype' => $uptype,
            'blockpos' => $blockpos,
            'targetname' => $targetname,
            'targetext' => $targetext,
            'debug' => $debug
        );

        $this->_curlPost($curlPost);
    }

    /**
     * 渲染图提交
     */
    public function rfpost()
    {
        $method = 'models.upfile';
        $user = 'wow730@gmail.com';
        $pass = md5('123456');
        $visa = base64_encode($user . ' ' . $pass);
        $format = 'xml';
        $debug = 0;
        $filename1 = 'D:\ws2.jpg';
        $uptype = '3';
        $yfid = '1020134';
        $md5 = '59c5924b78645c57ba998f1b7a32de11';
        $sha1 = 'd3d6afa3907d17df14a36f0548d8d82145d33ece';

        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        //
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        // 必须是以数组的方式才能提交文件哦
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'filename1' => '@' . $filename1,
            // 'filename2' => '@' . $filename2,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'uptype' => $uptype,
            'yfid' => $yfid,
            'md5' => $md5,
            'sha1' => $sha1
        );

        $this->_curlPost($curlPost);
    }

    /**
     * 已存在文件提交
     */
    public function efpost()
    {
        $method = 'models.upfile';
        $user = 'miaomin@bitmap3d.com.cn';
        $pass = md5('123456');
        $visa = base64_encode($user . ' ' . $pass);
        $format = 'xml';
        $debug = '0';
        $md5 = 'd68f7c80467fc28abcff754fcca005d3';
        $sha1 = '4bad96d2964952c93228cd2ad51d8302fa3e1c66';
        $uptype = '4';
        $yfid = '1020109';
        $folderid = '0';
        $filename = '111.zip';

        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        // 必须是以数组的方式才能提交文件哦
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'yfid' => $yfid,
            'folderid' => $folderid,
            'filename' => $filename,
            'uptype' => $uptype,
            'md5' => $md5,
            'sha1' => $sha1
        );

        $this->_curlPost($curlPost);
    }

    /**
     * 文件模拟提交
     */
    public function cfpost()
    {
        $method = 'models.upfile';
        $visa = 'bWlhb21pbiAyMTg0YzFlYzRkNmM3NWMyM2M3ZTI2OTY1NGUzMWI1Zg==';
        $format = 'xml';
        $debug = 0;
        $filename1 = 'D:\12.zip';

        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        //
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        // 必须是以数组的方式才能提交文件哦
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'filename1' => '@' . $filename1,
            // 'filename2' => '@' . $filename2,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );

        $this->_curlPost($curlPost);
    }

    /**
     * 公钥私钥
     */
    public function rsa()
    {
        try {
            $ming = 'Miss';

            Vendor('Rsa.Rsa');
            $RSA = new Rsa ();

            // $RSA->genKeyFile ();

            $res = $RSA->encoding($ming);
            echo $res;

            $res = $RSA->decoding($res);
            echo $res;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /*
     * 上传头像文件
     */
    public function uploadavartar()
    {
        $method = 'users.uploadavartar';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $filename = 'D:\a.jpg';

        // 必须是以数组的方式才能提交文件哦
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'filename' => '@' . $filename,
            // 'filename2' => '@' . $filename2,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 获取所有省市县
     * by zhangzhibin
     */
    public function getarea()
    {
        $method = 'users.getarea';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 新增地址
     *
     * by zhangzhibin
     */
    public function adduseraddress()
    {
        $method = 'users.adduseraddress';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        // 参数
        $datas = array('ua_addressee' => "姓名", 'ua_province' => '310000', 'ua_city' => '310100', 'ua_region' => '310105', 'ua_address' => '地址', 'ua_mobile' => '13800138000', 'isdefault' => '1');
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        //$address="8789be61uSnqWMQm9EEBQRQQdSUkIEEUQbCEQbBwcARxtrQhZFFBRTBVVLABdDUVRBXBYUSBQSUldVAlxfFBxvF0FCFkdXX0YYEwNFVgIGHWlGFhYQQwtEXVcCWENaREcNQUAHRxg8EkERGUcJX1ZYDwMUDBBDUwUKBlEPAQ4JRxtrQhZFFBRCCV5XAEYKFBNUVgQDCVhQFRU4RBkWFhIVXw4MUwBMQhBbERtVVAAWHWlGFhYQQxJfVlwBSURTEl8XQ1AHVRYaOEERGUVGQEZeFQ9YVVVDWBcIA1UVPBYQRRdDEFMCXVlcQwsZVlcDGDtDRhYWEhsLR1pdAFwUDBBHA1dRBFUEFDgcOw";
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas
        );
        $this->_curlPost($curlPost);
    }

    /*
	 *获取用户地址列表
	 */
    public function getuseraddress()
    {
        $method = 'users.getuseraddress';
        $format = 'json';
        $debug = 0;
        $user = 'wx_eaad420c3a54863a@3dcity.com';
        $pass = '3dcity2014';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 新增地址
     *
     * by zhangzhibin
     */
    public function updateuseraddress()
    {
        $method = 'users.updateuseraddress';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        // 参数
        $arr = array('ua_id' => '832', 'ua_addressee' => "姓名", 'ua_province' => '310000', 'ua_city' => '310100', 'ua_region' => '310105', 'ua_address' => '地址', 'ua_mobile' => '13800138000', 'isdefault' => '1');
        $datas = base64_encode(json_encode($arr));  //加密地址参数
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 删除收货地址
     * by zhangzhibin
     */
    public function deladdress()
    {
        $method = 'users.deladdress';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        // 参数
        $datas['ua_id'] = '768';
        $datas = base64_encode(json_encode($datas));  //base64地址参数
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas
        );
        $this->_curlPost($curlPost);
    }

    /*
     * APP首页图片
     */
    public function getbanner()
    {
        $method = 'front.getbanner';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
        );
        $this->_curlPost($curlPost);
    }

    /*
    * APP首页推荐图片
    */
    public function getapptj()
    {
        $method = 'front.getapptj';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
        );
        $this->_curlPost($curlPost);
    }

    /*
     * 获取搜索接口,只用于标签搜索
     */
    public function getsearch()
    {
        $method = 'front.getsearch';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['keywords'] = '项链';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /*
 * 获取搜索接口,只用于标签搜索
 */
    public function gethottags()
    {
        $method = 'front.gethottags';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['keywords'] = '戒指';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
        );
        $this->_curlPost($curlPost);
    }

    /*
    * 根据DIY商品的cid获取首饰产品基本资料
    */
    public function getdiydetail()
    {
        $method = 'front.getdiydetail';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['cid'] = 47;
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /*
   * 根据DIY商品的cid获取首饰产品基本资料
   */
    public function getdiybase()
    {
        $method = 'front.getdiybase';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['cid'] = '14';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /*
* 根据DIY商品的cid获取首饰产品基本资料
*/
    public function getdiyunit()
    {
        $method = 'front.getdiyunit';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['cid'] = '14';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /*
    * 根据DIY商品的cid获取首饰产品基本资料
    */
    public function getmaterial()
    {
        $method = 'front.getmaterial';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
        );
        $this->_curlPost($curlPost);
    }

    /*
    * 保存diy数据
    */
    public function savediyvalue()
    {
        $method = 'users.savediyvalue';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $datas['cid'] = '14';
        $datas['price'] = '2150';
        $datas['stype'] = 1;
        $datas['udid'] = '';
        $temp_datas['Textvalue'] = "3dcity";
        $temp_datas['Size'] = "15";
        $temp_datas['Textfont'] = "aldrich";
        $temp_datas['Material'] = "127";

        $datas['attribute'] = $temp_datas;
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /*
   * 获取用户购物车
   */
    public function getusercart()
    {
        $method = 'users.getusercart';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
        );
        $this->_curlPost($curlPost);
    }


    /*
   *删除购物车中的商品
   */
    public function delusercart()
    {
        $method = 'users.delusercart';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['pid'] = '32015,32023';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /*
  *更新购物车（点击结算）
  */
    public function updateusercart()
    {
        $method = 'users.updateusercart';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $ucinfo = array(
            '922' => 2,
            '2183' => 2,
        );
        $datas = $ucinfo;
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }


    /*
   *创建订单
   */
    public function buildorder()
    {
        $method = 'users.buildorder';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['ua_id'] = 760;
        $datas['uc_id'] = "2180,2181";
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /*
      *获取微信预支付ID
      */
    public function getwxrepayid()
    {
        $method = 'weixin.getwxrepayid';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['orderid'] = '143832772843430367';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }


    /*
	 *获取用户订单列表
	 */
    public function getuserorder()
    {
        $method = 'users.getuserorder';
        $format = 'json';
        $debug = 0;
        $user = '20000000001';
        $pass = '123456';
        $visa = base64_encode ( $user . ' ' . $pass );
        $visa = pub_encode_pass($visa,$this->_publicKey,"encode");

        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug
        );
        $this->_curlPost($curlPost);
    }

    /*
* 根据订单ID获取订单详情
*/
    public function getorderdetail()
    {
        $method = 'users.getorderdetail';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['upid'] = '2207';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }


    /**
     * 获取分类
     */
    public function getcates()
    {
        $method = 'front.getcates';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
       // $visa ="6b3cb2bcCK4UN6eTIFAywMdh54NVVCfmN8AnplKk4rIG0L";
        $visa_decode=base64_decode(pub_encode_pass($visa, $this->_publicKey, "decode"));
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';

        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 根据分类获取产品列表
     */
    public function getlistbycate()
    {
        $method = 'front.getlistbycate';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['pc_id'] = '1265';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }


    /**
     * 获取APP精选商品列表
     */
    public function getapptoplist()
    {
        $method = 'front.getapptoplist';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
        );
        $this->_curlPost($curlPost);
    }


    public function productdetail()
    {
        $method = 'front.productdetail';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['pid'] = '28203';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 产品加入购物车
     */
    public function addusercart()
    {
        $method = 'users.addusercart';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $datas['pid'] = '28381';
        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 获取app微信配置参数
     */
    public function getappwxconf()
    {
        $method = 'front.getappwxconf';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
        );
        $this->_curlPost($curlPost);
    }

    /**
     * 通用方法，测试api接口
     */
    public function apigo()
    {
        $ac = I("ac", '0', 'string');
        $method = I("method", '0', 'string');
        //$u      =I("u",'0','string');
        //$p      =I("p",'0','string');
        $dk1 = I("dk1", '0', 'string');
        $dv1 = I("dv1", '0', 'string');
        $dk2 = I("dk2", '0', 'string');
        $dv2 = I("dv2", '0', 'string');

        $method = "" . $ac . "." . $method . "";
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        //$visa='bWlhb21pbkBiaXRtYXAzZC5jb20uY24gMTIzNDU2';
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);

        $datas[$dk1] = $dv1;
        $datas[$dk2] = $dv2;

        //-----临时测试-----
        $datas['uid']=25;
        $datas['proj_json']="[{
	\"visible\": true,
	\"leftText\": \"kiss\",
	\"font\":	\"segoescript\",
	\"material\": \"18k 玫瑰金\",
}, {
	\"visible\": true,
	\"rightText\": \"me\",
	\"font\":	\"segoescript\",
	\"material\": \"18k 玫瑰金\",
}]";
        $datas['price']=2000;
        $datas['stype']=1;
        $datas['diyid']=1743;

        $datas = base64_encode(json_encode($datas));  //加密地址参数
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'datas' => $datas,
        );
        $this->_curlPost($curlPost);
    }

    /**
     * miaomin add@2016.3.23
     *
     * 测试根据pid获取轮播图信息
     */
    public function getproductimg()
    {
//		echo 'Run.';
//      echo $this->_restUrl;
        $pid = $_GET['pid'];
        $method = 'front.getproductimages';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $reqdata = base64_encode(json_encode(array('pid' => $pid)));  //加密地址参数;
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'reqdata' => $reqdata
        );
        $this->_curlPost($curlPost);
    }

    /**
     * guolixun add@2016.06.12
     *
     * 根据产品ID获取分享url
     */
    public function getshareurl()
    {
        $p_key = $_GET['pkey'];
        if(!$p_key){
            $p_key='ea7c162e9aea4c3e8a981eef8f5b0b35';
        }
        $method = 'front.getshareurl';
        $format = 'json';
        $debug = 0;
        $user = '18621118091';
        $pass = '123456';
        $visa = base64_encode($user . ' ' . $pass);
        $visa = pub_encode_pass($visa, $this->_publicKey, "encode");
        $curlPost = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode = $this->_genVcode();
        $sign = gensign($curlPost, $vcode, $this->_publicKey);
        $reqdata = base64_encode(json_encode(array('p_key' => $p_key)));  //加密地址参数;
        $curlPost = array(
            'method' => $method,
            'visa' => $visa,
            'format' => $format,
            'vcode' => $vcode,
            'sign' => $sign,
            'debug' => $debug,
            'reqdata' => $reqdata
        );
        $this->_curlPost($curlPost);
    }


    /**
     * zhangzhibin add@2016.07.18
     *
     * 柯蓝订单通知接口
     */
    public function curlOrdernotify()
    {
        $pubKey         = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
        $method         = 'front.ordernotify';
        $format         = 'json';
        $debug          = 0;
        $user           = 'kl@ignjewelry.com';
        $pass           = '123456';
        $visa           = base64_encode($user . ' ' . $pass);
        $curlPost       = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode          = $this->_genVcode();
        $sign           = gensign($curlPost, $vcode, $pubKey);
        $dataArr['pid'] = 1850;
        $dataArr['count']= 1;

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
     * zhangzhibin add@2016.07.19
     *
     * 柯蓝产品价格查询
     */
    public function curlProductPrice()
    {
        $pubKey         = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';       //32位公钥
        $method         = 'front.productprice';
        $format         = 'json';
        $debug          = 0;
        $user           = 'kl@ignjewelry.com';
        $pass           = '123456';
        $visa           = base64_encode($user . ' ' . $pass);
        $curlPost       = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode          = $this->_genVcode();                       //1-28的随机数
        $sign           = gensign($curlPost, $vcode, $pubKey);
        $dataArr['pid'] = 1850;
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
     * zhangzhibin add
     * 麦德龙生成订单
     */
    public function metro_orderconfirm(){
        $pubKey         = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';       //32位公钥
        $method         = 'front.orderconfirm';
        $format         = 'json';
        $debug          = 0;
        $user           = 'mdl@ignite.com';
        $pass           = 'mdl2016';
        $visa           = base64_encode($user . ' ' . $pass);
        $curlPost       = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode          = $this->_genVcode();                       //1-28的随机数
        $sign           = gensign($curlPost, $vcode, $pubKey);
        $dataArr['orderid']     = 'test006';
        $dataArr['clientuserid']= '7527e753haDVW6AAQBVA';
        $dataArr['detail']      = '2135:2:499';
        $dataArr['addressee']   = '小麦';
        $dataArr['address']     = '虹桥商务广场3A';
        $dataArr['mobile']      = '11011011011';
        $sendData   = base64_encode(json_encode($dataArr));  //参数base64
        $curlPost   = array(
            'method' => $method,
            'visa'   => $visa,
            'format' => $format,
            'vcode'  => $vcode,
            'sign'   => $sign,
            'debug'  => $debug,
            'datas'  => $sendData
        );
        $this->_curlPost($curlPost);
    }

    /**
     * zhangzhibin add
     * 麦德龙生成订单
     */
    public function metro_orderstatus(){
        $pubKey         = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';       //32位公钥
        $method         = 'front.orderstatus';
        $format         = 'json';
        $debug          = 0;
        $user           = 'mdl@ignite.com';
        $pass           = 'mdl2016';
        $visa           = base64_encode($user . ' ' . $pass);
        $curlPost       = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode          = $this->_genVcode();                       //1-28的随机数
        $sign           = gensign($curlPost, $vcode, $pubKey);
        $dataArr['orderid']     = 'mdl1005';
        $dataArr['clientuserid']= '7527e753haDVW6AAQBVA';
        $sendData   = base64_encode(json_encode($dataArr));  //参数base64
        $curlPost   = array(
            'method' => $method,
            'visa'   => $visa,
            'format' => $format,
            'vcode'  => $vcode,
            'sign'   => $sign,
            'debug'  => $debug,
            'datas'  => $sendData
        );
        $this->_curlPost($curlPost);
    }

    /**
     * zhangzhibin add@2016.07.19
     *
     * 麦德龙订单查询快递状态
     */
    public function curlorderexpress(){
        $pubKey         = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';       //32位公钥
        $method         = 'front.orderexpress';
        $format         = 'json';
        $debug          = 0;
        $user           = 'mdl@ignite.com';
        $pass           = 'mdl2016';
        $visa           = base64_encode($user . ' ' . $pass);
        $curlPost       = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode          = $this->_genVcode();                       //1-28的随机数
        $sign           = gensign($curlPost, $vcode, $pubKey);
        $dataArr['orderid']     = '20160918888';
        $dataArr['clientuserid']= '7527e753haDVW6AAQBVA';
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


    public function curlgetthirdinfo()
    {
        $pubKey         = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';
        $method         = 'front.getthirdinfo';
        $format         = 'json';
        $debug          = 0;
        $user           = 'agent1@ignjewelry.com';
        $pass           = '123456';
        $visa           = base64_encode($user . ' ' . $pass);
        $curlPost       = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        $vcode          = $this->_genVcode();
        $sign           = gensign($curlPost, $vcode, $pubKey);
        /*echo $curlPost;
        echo "_____________";
        echo md5($curlPost);
        echo  "sub:";
        echo substr($pubKey, 9, 4);
        echo "___两次md5:";
        echo md5(md5($curlPost) . substr($pubKey, 9, 4));
        echo "_________";
        echo $sign;
        exit;*/
        $dataArr['userCode']    = '67W1G6324';   //定制数量,不写默认为1
        $sendData               = base64_encode(json_encode($dataArr));  //参数base64
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
     * zhangzhibin add
     * VR演示——购物车生成订单
     */
    public function curlvrcartorder(){
        $pubKey         = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';       //32位公钥
        $method         = 'front.vrcartorder';
        $format         = 'json';
        $debug          = 0;
        $user           = 'vr@ign.vip';
        $pass           = '123456';
        $visa           = base64_encode($user . ' ' . $pass);
        $curlPost       = 'method=' . $method . '&visa=' . $visa . '&format=' . $format . '';
        //$vcode          = $this->_genVcode();                       //1-28的随机数
        $vcode          = 10;
        $sign           = gensign($curlPost, $vcode, $pubKey);

        //echo $sign;
        $dataArr['detail']      = '39242:1,28066:1,37365:1,39243:1';
       // echo json_encode($dataArr);
        $sendData   = base64_encode(json_encode($dataArr));  //参数base64
        $curlPost   = array(
            'method' => $method,
            'visa'   => $visa,
            'format' => $format,
            'vcode'  => $vcode,
            'sign'   => $sign,
            'debug'  => $debug,
            'datas'  => $sendData
        );
        $this->_curlPost($curlPost);
    }



}
?>