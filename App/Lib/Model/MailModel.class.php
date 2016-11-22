<?php
/**
 * Mail发送系统
 *
 * @author zhangzhibin 
 * 2014-11-17
 */
class MailModel extends Model {
	protected $tableName = 'mail';
	protected $fields = array (
			'id',
			'mailto',
			'mailcontent',
			'status',
			'sendtime',
			'delsign',
			'ctime',
			'_pk' => 'id',
			'_autoinc' => TRUE 
	);
	
	
	/*增加记录到tdf_mail表中
	 * param int @mid mail模板ID
	 * param array @mailinfo 用户ID
	 */
	public function addMailSend($mid,$mailinfo){
		$MailMode=M('mail');
		$MailInfo=$MailMode->where("up_orderid='".$mailinfo['up_orderid']."' and mid=".$mid."")->find();
		if($MailInfo){//如果存在up_order和mid都相等的记录,不新增
			$mailID=$MailInfo['id'];
		}else{//新增
			$UM=new UsersModel();
			$mailTemplate=$this->getMailTemplate($mid);
			$subject=$mailinfo['subject']?$mailinfo['subject']:$mailTemplate['subject'];
			$content = replace_string_vars($mailTemplate['content'], $mailinfo );
			$data['mid']			=$mid; //收件人mail
			$data['mailto']			=$mailinfo['mailto']; //收件人mail
			$data['up_orderid']		=$mailinfo['up_orderid'];	//邮件主题
			$data['dispname']		=$mailinfo['dispname'];	//邮件主题
			$data['mailsubject']	=$subject;	//邮件主题
			$data['mailcontent']	=$content; //邮件内容
			$mailID=M('mail')->add($data);
		}
		$this->sendMail($mailID);
	}
	
	public function getMailByID($id){
		return M('mail')->where("id=".$id."")->find();
	}
	
	/*
	 * 发送tdf_mail中的邮件
	 * param int @id tdf_mail 表中的id
	 */
	private function sendMail($id){
		$MailInfo=$this->getMailByID($id);
		if(!$MailInfo['status']){
			$to		=$MailInfo['mailto'];
			$toname	=$MailInfo['dispname'];
			$title	=$MailInfo['mailsubject'];
			$content=$MailInfo['mailcontent'];
			//exit;
			vendor ( 'ELetter.Letter' );
			$result_sendmail=Letter::_sendMail($to, $toname, $title, $content);
			if($result_sendmail){
				$this->saveSendMailSuccess($id);
			}
		}
	}
	
	
	/*获取mail模板信息
	 * param int @mid mail模板ID
	 * 
	 */
	public function getMailTemplate($mid){
		$mailTemp=M('mail_template')->where("id=".$mid."")->find();
		return $mailTemp;
	}
	
	/*
	 * 保存发送邮件成功的状态
	 * param int @id tdf_mail中id
	 */
	public function saveSendMailSuccess($id){
		if($id){
			$data['status']=1;
			$data['sendtime']=date ( 'Y-m-d H:i:s', NOW_TIME );
			$result=M('mail')->where("id=".$id)->save($data);
		}
		return $result;
	}
	
	
	
	
}
?>