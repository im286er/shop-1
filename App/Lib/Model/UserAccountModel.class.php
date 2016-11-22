<?php
/**
 * 用户账户类
 *
 * @author miaomin 
 * Jul 11, 2013 11:58:38 AM
 */
class UserAccountModel extends Model {
	protected $tableName = 'user_account';
	protected $fields = array (
			'u_id',
			'u_vcoin',
			'u_vcoin_av',
			'u_rcoin',
			'u_rcoin_av',
			'_pk' => 'u_id' 
	);
	public function changeVCoin($User, $VCoin, $VCoin_av) { // 用户积分
		$DBF_U = new DBF_User_Account ();
		$data = array (
				$DBF_U->ID => $User [$DBF_U->ID],
				$DBF_U->Vcoin => $User [$DBF_U->Vcoin] + $VCoin,
				$DBF_U->Vcoin_av => $User [$DBF_U->Vcoin_av] + $VCoin_av 
		);
		return $this->save ( $data ) ? true : false;
	}
	public function changeRCoin($User, $RCoin, $RCoin_av) { // 用户RMB
		$DBF_U = new DBF_User_Account ();
		if ($User) {
			$data = array (
					$DBF_U->Rcoin => $User [$DBF_U->Rcoin] + $RCoin,
					$DBF_U->Rcoin_av => $User [$DBF_U->Rcoin_av] + $RCoin_av 
			);
			$result = $this->where ( $DBF_U->ID . "=" . $User [$DBF_U->ID] . " and " . $DBF_U->Rcoin . "=" . $User [$DBF_U->Rcoin] . " and " . $DBF_U->Rcoin_av . "=" . $User [$DBF_U->Rcoin_av] )->save ( $data );
		} else {
			$result = 0;
		}
		return $result;
	}
	public function addVcoin($val) {
		$UC = D ( 'UserAccount' );
		$UC->find ( $this->u_id );
		$UC->u_vcoin += $val;
		$UC->u_vcoin_av += $val;
		$UC->save ();
		// 日志
		/*
		 * $vt = D ( 'LogVtrans' ); $vt->addLog ($this,$val,1,3,0);
		 */
	}
	public function getUserAccountByUid($Uid) { // 根据 u_id 查询useraccount表 0621 by
	                                            // zhangzhibin
		$DBF_U = new DBF_User_Account ();
		$User = $this->where ( "u_id=" . $Uid . "" )->select ();
		if (! $User) {
			return $User;
		}
		return $User [0];
	}
	public function addLogAndRcoin_order($out_trade_no, $total_fee) { // 扣除用户账户金额，更改订单状态为成功，减去账户、日志和用户金额数和已购买的模型
	                                                                  // out_trade_no：订单号
	                                                                  // total_fee：费用
		$UP = D ( 'User_prepaid' );
		$UPorder = $UP->where ( "up_orderid='" . $out_trade_no . "'" )->find (); // 查询对应order_id的记录
		$up_productid = $UPorder ['up_productid'];
		$u_id = $UPorder ['up_uid'];
		$PerpaidID = $UPorder ['up_id'];
		if ($UPorder ['up_status'] == 1) {
			$result = 2; // 已经支付过
		} else {
			$TU = D ( "Users" );
			$UserAccountInfo = $this->getUserAccountByUid ( $u_id );
			if ($UserAccountInfo ['u_rcoin_av'] < $total_fee) {
				$result = 3; // 账户余额不足
			} else {
				$LRTM = new LogRTransModel ();
				$UP->startTrans (); // 在d模型中启动事务
				$UP->up_status = 1;
				$UP->up_orderbackid = time ();
				$UP->up_orderbacktime = date ( 'Y-m-d H:i:s', NOW_TIME );
				$UP->up_paytype = 153;
				$UP->up_amount = 0;
				$UP->up_amount_account = $UPorder ['up_amount'];
				$UP->up_amount_total = $UPorder ['up_amount'];
				$step1 = $UP->where ( "up_orderid='" . $out_trade_no . "'" )->save ();
				// var_dump($up_productid);
				$step2 = $this->add_UserDeals ( unserialize ( $up_productid ), $u_id, $UPorder ); // 添加到user_deals用户交易表中
				$step3 = $this->changeRCoin ( $UserAccountInfo, - $total_fee, - $total_fee ); // 2减少用户账户金额
				                                                                              // miaomin
				                                                                              // edited@2014.11.12
				                                                                              // $step4
				                                                                              // =$this->delcart(unserialize($up_productid),$u_id);
				$step5 = $LRTM->addLog ( $UserAccountInfo, $total_fee, 0, 4, $PerpaidID ); // 3增加日志
				                                                                           // uid用户id，$RCoin增加值,类型(0减少
				                                                                           // 1增加
				                                                                           // 2购物车购买)，交易类型，交易id
				
				if ($step1 && $step2 && $step3) {
					$UP->commit (); // 提交事务
					                
					// 发mail----start
					$orderid_encode = pub_encode_pass ( $out_trade_no, $u_id, 'encode' );
					$MailMode = new MailModel ();
					$UM = new UsersModel ();
					$userinfo = $UM->getUserByID ( $u_id );
					$mailinfo = array (
							'up_id' => $PerpaidID,
							'up_orderid' => $out_trade_no,
							'mailto' => $userinfo ['u_email'],
							'dispname' => $userinfo ['u_dispname'],
							'orderid' => $orderid_encode 
					);
					$MailMode->addMailSend ( 3, $mailinfo );
					// 发mail-----end
					$result = 1;
				} else {
					$UP->rollback (); // 事务回滚
					$result = 5;
				}
				
				//后台件表加记录  edited@2015/2/10 lifangyuan<-----------
				//R('Manage/Billmanage/addproduct',array($out_trade_no));
				//<------------------------------------------------------------------
			}
		}
		return $result;
	}
	
	// 支付后成功后流程
	public function addLogAndRCoin($out_trade_no, $trade_no, $total_fee) { // 增加用户RMB、日志和用户金额数和已购买的模型
	                                                                       // out_trade_no：订单号
	                                                                       // trade_no:支付宝交易号(回单号)
	                                                                       // total_fee：费用
	                                                                       
		// ---更改订单状态 start--^
		$d = D ( 'user_prepaid' );
		$result = $d->where ( "up_orderid='" . $out_trade_no . "'" )->select (); // 查询对应order_id的记录
		$d->startTrans (); // 在d模型中启动事务
		$PerpaidID  = $result [0] ['up_id'];
		$u_id       = $result [0] ['up_uid'];
		$u_type     = $result [0] ['up_type'];
		$up_productid = $result [0] ['up_productid'];
		$d->up_status = 1;
		$d->up_orderbackid      = $trade_no;
		$d->up_orderbacktime    = date ( 'Y-m-d H:i:s', NOW_TIME );
		$step1 = $d->where ( "up_orderid='" . $out_trade_no . "'" )->save ();
		$UM = new UserAccountModel ();
		$User = $UM->getUserAccountByUid ( $u_id );
		$RCoin = $total_fee;
		if ($u_type == 1) { // 走购物车流程
			$step2 = $this->add_UserDeals ( unserialize ( $up_productid ), $u_id, $result [0] ); // 添加到user_deals用户交易表中
			$step3 = true;
			$step4 = true;
		} elseif ($u_type == 3) { // 积分
			$Result = false;
			$LVTM = new LogVTransModel ();
			$VCoin = $RCoin * 4;
			$step2 = $UM->changeVCoin ( $User, $VCoin, $VCoin ); // 2增加RMB
			$step3 = $LVTM->addLog ( $User, $VCoin, 1, 2, $PerpaidID ); // 3增加日志
			                                                            // uid用户id，$RCoin增加值,类型(0减少
			                                                            // 1增加
			                                                            // 2购物车购买)，交易类型，交易id
			$step4 = true;
		} elseif ($u_type == 0) { // 充值
			$Result = false;
			$LRTM = new LogRTransModel ();
			$step2 = $UM->changeRCoin ( $User, $RCoin, $RCoin ); // 2增加RMB
			$step3 = $LRTM->addLog ( $User, $RCoin, 1, 2, $PerpaidID ); // 3增加日志
			                                                            // uid用户id，$RCoin增加值,类型(0减少
			                                                            // 1增加
			                                                            // 2购物车购买)，交易类型，交易id
			$step4 = true;
		} elseif ($u_type == 4) { // DIY
			// $step2 = true;
			$step2 = $this->add_UserDeals ( unserialize ( $up_productid ), $u_id, $result [0] ); // 添加到user_deals用户交易表中
			$step3 = true;
			// miaomin edited @2014.11.12
			// $step4 = $this->delcart(unserialize($up_productid),$u_id);
			// $step4 = $this->delcart ( unserialize ( $up_productid ), $u_id );
			$step4 = true;
		}
		
		if ($step1 && $step2 && $step3) {
			$d->commit (); // 提交事务
			               // 发mail----start
			$orderid_encode = pub_encode_pass ( $out_trade_no, $u_id, 'encode' );
			$MailMode = new MailModel ();
			$UM = new UsersModel ();
			$userinfo = $UM->getUserByID ( $u_id );
			$mailinfo = array (
					'up_id' => $PerpaidID,
					'up_orderid' => $out_trade_no,
					'mailto' => $userinfo ['u_email'],
					'dispname' => $userinfo ['u_dispname'],
					'orderid' => $orderid_encode 
			);
			$MailMode->addMailSend ( 3, $mailinfo );
			// 发mail-----end
			
			$result_back = 1;
		} else {
			$d->rollback (); // 事务回滚
			$result_back = 0;
		}
		
		//logResult ( date ( 'Y-m-d H:i:s', NOW_TIME ) . " 写入用户积分、日志、积分数成功、已购买的模型<br>" );
		return $result_back;
	}
	
	/**
	 * 添加用户交易记录
	 *
	 * 说明: 对用户交易表进行修改原先的deal表不做扩展新建了salesreport表用以统计销售数据
	 * 说明人员: miaomin@2015/2/9
	 *
	 * @param array $pid_arr        	
	 * @param int $uid        	
	 * @param array $orderinfo        	
	 * @return number
	 */
	public function add_UserDeals($pid_arr, $uid, $orderinfo = array()) { // 账户扣除
	                                                                      // 由产品ID数组增加到用户交易表中
	                                                                      // by
	                                                                      // zhangzhibin
		$PR = new ProductModel ();
		$UD = new UserDealsModel ();
		$UOPM = new UserOwnProductModel ();
		// miaomin added@2015/2/9
		$SRM = new SalesReportModel ();
		$UPDM = new UserPrepaidDetailModel ();
		$orderDetail = $UPDM->where ( 'up_id="' . $orderinfo ['up_id'] . '"' )->find ();
		
		$result = 0;
		
		if (is_array ( $pid_arr )) {
			foreach ( $pid_arr as $v ) {
				$v_arr = explode ( ",", $v );
				$v_pid = $v_arr [0];
				$product = $PR->getProductByID ( $v_pid );
				if ($SRM->addRecord ( $product, $orderinfo, $orderDetail ['up_product_info'] ) && $UD->addRecord ( $uid, 0, $product, get_client_ip (), 1 ) && $UOPM->addRecord ( $uid, $v_pid, $product ['p_creater'], 1 )) {
					$result = 1;
				}
			}
		} else {
			$v_arr = explode ( ",", $pid_arr );
			$v_pid = $v_arr [0];
			$product = $PR->getProductByID ( $v_pid );
			if ($SRM->addRecord ( $product, $orderinfo, $orderDetail [' '] ) && $UD->addRecord ( $uid, 0, $product, get_client_ip (), 1 ) && $UOPM->addRecord ( $uid, $v_pid, $product ['p_creater'], 1 )) {
				$result = 1;
			}
		}
		return $result;
	}
	public function add_UserDealsVcoin($pid_arr, $uid) { // 账户扣除 由产品ID数组增加到用户交易表中
	                                                     // by zhangzhibin
		$PR = new ProductModel ();
		$UD = new UserDealsVcoinModel ();
		$UOPM = new UserOwnProductModel ();
		$result = false;
		if (is_array ( $pid_arr )) {
			foreach ( $pid_arr as $v ) {
				$v_arr = explode ( ",", $v );
				$v_pid = $v_arr [0];
				$product = $PR->getProductByID ( $v );
				if ($UD->addRecord ( $uid, 0, $product, get_client_ip (), 1 ) && $UOPM->addRecord ( $uid, $v_pid, $product ['p_creater'], 1 )) {
					$result = true;
				}
			}
		} else {
			$v_arr = explode ( ",", $pid_arr );
			$v_pid = $v_arr [0];
			$product = $PR->getProductByID ( $pid_arr );
			if ($UD->addRecord ( $uid, 0, $product, get_client_ip (), 1 ) && $UOPM->addRecord ( $uid, $v, $product ['p_creater'], 1 )) {
				$result = true;
			}
		}
		return $result;
	}
	public function delcart($pid_arr, $uid) { // 由产品ID数组删除用户购物车中信息 by zhangzhibin
		$i = 0;
		$res = true;
		$UCM = new UserCartModel ();
		foreach ( $pid_arr as $v ) {
			if ($UCM->deleteProduct ( $v, $uid )) {
				$result [$i] = true;
			} else {
				$result [$i] = false;
			}
			$i ++;
		}
		for($k = 0; $k <= $i; $k ++) {
			if (! $result [$k]) {
				$res = false;
			}
		}
		return $res;
	}
}
?>