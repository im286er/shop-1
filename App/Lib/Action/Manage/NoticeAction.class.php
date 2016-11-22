<?php
/**
 * 通知模块
 *
 * @author miaomin 
 * Sep 12, 2013 1:16:02 PM
 */
class NoticeAction extends CommonAction {
	/**
	 * 首页
	 */
	function index() {
		$this->display ();
	}
	
	/**
	 * 发送通知
	 */
	function sendmessage() {
		if ($this->isPost ()) {
			// print_r ( $this->_post () );
			$sendType = $this->_post ( 'sendType' );
			if ($sendType) {
				vendor ( 'ELetter.Letter' );
				$to = 'wow730@gmail.com';
				
				switch ($sendType) {
					case 1 :
						// 后台处理订单完毕后邮件通知
						$args = array ('order_number'=>'123456');
						$res = Letter::sendOrderCompleteMail ( $to, $args );
						break;
					case 2 :
						// 后台处理模型验证后邮件通知
						$args = array ('product_prop_url'=>'http://www.baidu.com/');
						$res = Letter::sendVerifyProductMail ( $to, $args );
						break;
				}
				
				print_r ( $res );
			}
		}
		$this->display ();
	}


	/**
	 * 手机短信发送
	 */
	function sendmobile(){
		if($_POST){
			$data['mailto']         =   I('mob_no','0','string');
			$data['mailsubject']    =   I('begintime','0','string');
			$data['mailcontent']    =   I('mailcontent','0','string');
			$data['mid']            =   I('tempid',0,'intval');
			$data['sendtype']       =   I('sendtype',0,'intval');
			$data['status']         =   0;
			if(!$data['mailto']){$this->error('手机号码未输入');}
			if(!$data['mailsubject']){$this->error('观影时间未输入');}
			if(!$data['mailcontent']){$this->error('观影位置未输入');}
			$TMM=M('mail');
			$mailInfo=$TMM->where("mailto='".$data['mailto']."' and mid=".$data['mid']."")->find();
			if($mailInfo){
				$TMM->where("id=".intval($mailInfo['id'])."")->save($data);
			}else{
				$TMM->add($data);
			}

		}

		$this->display();
	}

	/**
	 * 手机短信发送列表
	 */
	function sendmobilelist(){
		$TMM=M('mail');

		if($_POST){
			$mailid=I("mailid");
			foreach($mailid as $key=>$value){
				$mailInfo=$TMM->where("id=".intval($value)." and sendtype=1 and status=0")->find();
				$datas[0] = $mailInfo['mailsubject'];//时间
				$datas[1] = $mailInfo['mailcontent'];//座位
				if($mailInfo){
					$smsResult=smssent($mailInfo['mailto'],$datas, $mailInfo['mid']);
					if($smsResult){
						$setField['status'] = 1;
						$setField['sendtime'] = date('y-m-d h:i:s',time());
						$result=$TMM->where("id=".intval($value)."")->setField($setField);
						$log.="ID:".$value."手机号:".$mailInfo['mailto']."发送成功 \n";
					}else{
						$log.="ID:".$value."手机号:".$mailInfo['mailto']."发送失败 \n";
					}
				}else{
					$log.="ID:".$value."未发送,此条短信已经发送过. \n";
				}
			}
			writelog("sms",$log);
			//$rurl=__ROOT__."/notice/sendmobilelist/Pnodeid/118/nodeid/195";
			redirect (__SELF__);

		}else{
			$mailList=$TMM->where("sendtype=1 and mid=104670")->select();
		}

		$this->assign('mailList',$mailList);
		$this->display();
	}

}
?>