<?php
/**
 * 我的赞类
 *
 * @author miaomin 
 * Apr 2, 2014 3:11:28 PM
 *
 * $Id$
 */
class MyzanAction extends CommonAction {
	/**
	 * 构造函数
	 */
	public function __construct() {
		//
		parent::__construct ();
		
		// 判断登录
		// 允许用户不登陆点赞
		/*
		 * if (! $this->_isLogin ()) { $this->_needLogin (); }
		 */
		
		// load ( "@.DBF" );
		$this->DBF = new DBF ();
	}
	
	/**
	 * 添加赞
	 *
	 * @access public
	 * @return mixed
	 */
	public function add() {
		header('Access-Control-Allow-Origin: *');//跨域提交
		header('Access-Control-Allow-Headers: Content-Type');
		header('Access-Control-Allow-Methods: *');
		try {

			// 如果允许用户不登录也可以点赞有必要根据IP做出限制
			if ($this->isGet ()) {
				$pid = $this->_get ( 'id' );
				$Product = D ( 'Product' );
                $productInfo=$Product->find ( $pid );
				if (! $Product->find ( $pid )) {
					$this->error ( L ( 'no_pid' ), WEBROOT_PATH . '/index.php' );
				}

				// 登录判断
				if (! $this->_isLogin ()) {

					// IP限制
					$now = time ();
					$client_ip = get_client_ip ();
					$UZAN = new UserZanModel ();
					$condition ['u_id'] = 0;
					$condition ['uz_pid'] = $Product->p_id;
					$condition ['uz_type'] = $Product->p_producttype;
					$condition ['uz_ip'] = $client_ip;
					$uzanRes = $UZAN->where ( $condition )->order ( 'uz_id DESC' )->limit ( 1 )->find ();
					//if (($uzanRes) && ($now - $uzanRes ['uz_ts']) <= 300) {//加时间和IP判断能否点赞
						//redirect ( WEBROOT_PATH . '/index/models-detail-id-' . $pid );
                      //  echo $uzanRes[$Product->p_zans];
                       // echo $productInfo['p_zans'];
                      //  exit;
					//}

					// 记表
					$UZAN = new UserZanModel ();
					$UZAN->create ();
					$UZAN->u_id = 0;
					$UZAN->uz_pid = $Product->p_id;
					$UZAN->uz_type = $Product->p_producttype;
					$UZAN->uz_date = get_now ();
					$UZAN->uz_ip = get_client_ip ();
					$UZAN->uz_ts = time ();
					if (! $UZAN->add ()) {
						//echo $UZAN->getLastSql ();
                        echo $uzanRes[$Product->p_zans];
						die ();
						// throw new Exception ( 'add_fail' );
					}
					// 赞次数+1
					$Product->p_zans += 1;
                    $echo_zans=$Product->p_zans;
					if (! $Product->save ()) {
						throw new Exception ( 'zans_fail' );
					}
					//redirect ( WEBROOT_PATH . '/index/models-detail-id-' . $pid );
                    echo $echo_zans;
                    exit;
				} else {
					// 如果赞过了，就别再操作了
					$UZAN = new UserZanModel ();
					$condition ['u_id'] = $this->_session ( 'f_userid' );
					$condition ['uz_pid'] = $Product->p_id;
					$condition ['uz_type'] = $Product->p_producttype;
					//if ($UZAN->where ( $condition )->find ()) {

						//redirect ( WEBROOT_PATH . '/index/models-detail-id-' . $pid );
                       // echo "<script language=JavaScript>parent.mainFrame.location.reload();</script>";
                    //}
					
					// 记表
					$UZAN = new UserZanModel ();
					$UZAN->create ();
					$UZAN->u_id = $this->_session ( 'f_userid' );
					$UZAN->uz_pid = $Product->p_id;
					$UZAN->uz_type = $Product->p_producttype;
					$UZAN->uz_date = get_now ();
					$UZAN->uz_ip = get_client_ip ();
					$UZAN->uz_ts = time ();
					if (! $UZAN->add ()) {
						throw new Exception ( 'add_fail' );
					}
					
					// 赞次数+1
					$Product->p_zans += 1;
                    $echo_zans=$Product->p_zans;
                    if (! $Product->save ()) {
						throw new Exception ( 'zans_fail' );
                        echo $echo_zans;
					}
					echo $echo_zans;
					//redirect ( WEBROOT_PATH . '/index/models-detail-id-' . $pid );
                    //echo "<script language=JavaScript> location.replace(location.href);</script>";
                    exit;
				}
			}
		} catch ( Exception $e ) {
			//$this->error ( L ( $e->getMessage () ), WEBROOT_PATH . '/index/models-detail-id-' . $pid );
            echo "error";
            //echo "<script language=JavaScript> location.replace(location.href);</script>";
            exit;

            // echo $e->getMessage ();
		}
	}
}
?>