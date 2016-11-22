<?php
class LitterAction extends CommonAction {
	public function inbox() {
		$SI = $this->getPagingInfo ();
		$SI ['uid'] = $this->_session ( 'f_userid' );
		$LUM = new LitterUserModel ();
		$List = $LUM->getInBox ( $SI );
		load ( '@.Paging' );
		$PI = getPagingInfo ( $LUM->TotalCount, $SI ['page'], $SI ['count'] );
		
		$this->assign ( 'List', $List );
		$this->assign ( 'PI', $PI );
		var_dump ( $List );
		// $this->display();
	}
	public function outbox() {
		$SI = $this->getPagingInfo ();
		$SI ['uid'] = $this->_session ( 'f_userid' );
		$LUM = new LitterUserModel ();
		$List = $LUM->getOutBox ( $SI );
		load ( '@.Paging' );
		$PI = getPagingInfo ( $LUM->TotalCount, $SI ['page'], $SI ['count'] );
		
		$this->assign ( 'List', $List );
		$this->assign ( 'PI', $PI );
		var_dump ( $List );
		// $this->display();
	}
	public function sys() {
		$SI = $this->getPagingInfo ();
		$LSM = new LitterSysModel ();
		$List = $LSM->getListByUser ( $SI, $this->_session ( 'f_userid' ) );
		load ( '@.Paging' );
		$PI = getPagingInfo ( $LSM->TotalCount, $SI ['page'], $SI ['count'] );
		
		$this->assign ( 'List', $List );
		$this->assign ( 'PI', $PI );
		var_dump ( $List );
		// $this->display();
	}
	public function send() {
		if ($this->isPost ()) {
			$this->assign ( 'Post', $_POST );
			$Post = $this->getLitterPost ();
			if (! $Post) {
				return $this->displayError ( '发送信息错误' );
			}
			$LUM = $this->buildLitterData ( $Post ['sendto'] );
			if ($LUM === false) {
				return $this->displayError ( '数据库连接失败' );
			}
			if ($LUM === null) {
				return $this->displayError ( '找不到对应用户' );
			}
			// 自己不能给自己写信 - miaomin added@2014.5.13
			if ($LUM->{$LUM->F->From} == $LUM->{$LUM->F->To}) {
				echo 'Cann\'t send message to youself.';
				exit ();
			}
			if ($LUM->add () === false) {
				return $this->displayError ( '数据库连接失败' );
			}
			return $this->success ( '发送成功', U ( 'user/litter/outbox' ) );
		}
		$this->display ();
	}
	public function detail() {
		$ID = $this->getID ();
		if (! $ID) {
			return $this->error ( U ( 'user/litter/inbox' ), '页面传值错误' );
		}
		
		$LUM = new LitterUserModel ();
		$Detail = $LUM->getDetail ( $ID, $this->_session ( 'f_userid' ) );
		if ($Detail === false) {
			return $this->error ( '数据库连接失败', U ( 'user/litter/inbox' ) );
		}
		if ($Detail === null) {
			return $this->error ( '找不到对应信息', U ( 'user/litter/inbox' ) );
		}
		
		$this->assign ( 'Detail', $Detail );
		var_dump ( $Detail );
		// $this->display();
	}
	public function sysdetail() {
		$ID = $this->getID ();
		if (! $ID) {
			return $this->error ( '页面传值错误', U ( 'user/litter/sys' ) );
		}
		
		$LSM = new LitterSysModel ();
		$F_I = DBF_LitterSys_Index::construct ();
		$Detail = $LSM->getDetail ( $ID, $this->_session ( 'f_userid' ) );
		if ($Detail === false) {
			return $this->error ( '数据库连接失败', U ( 'user/litter/inbox' ) );
		}
		if ($Detail === null) {
			return $this->error ( '找不到对应信息', U ( 'user/litter/inbox' ) );
		}
		
		$this->assign ( 'Detail', $Detail );
		var_dump ( $Detail );
		// $this->display();
	}
	private function getPagingInfo() {
		$PVC = new PVC2 ();
		$PVC->isInt ()->validateExists ()->DefVal ( 1 )->add ( 'page' );
		$PVC->isInt ()->validateExists ()->DefVal ( 20 )->add ( 'count' );
		if (! $PVC->verifyAll ()) {
			return false;
		}
		return $PVC->ResultArray;
	}
	private function getID() {
		$PVC = new PVC2 ();
		$PVC->setModeGet ();
		$PVC->isInt ()->validateMust ()->add ( 'id' );
		if (! $PVC->verifyAll ()) {
			return false;
		}
		return $PVC->ResultArray ['id'];
	}
	private function getLitterPost() {
		$PVC = new PVC2 ();
		$PVC->setModePost ()->setStrictMode ( true );
		$PVC->isString ()->validateMust ()->add ( 'sendto' );
		$PVC->isString ()->validateMust ()->add ( 'title' );
		$PVC->isString ()->validateMust ()->add ( 'contents' );
		if (! $PVC->verifyAll ()) {
			return false;
		}
		return $PVC->ResultArray;
	}
	private function buildLitterData($NickName) {
		$UM = new UsersModel ();
		$User = $UM->getUserByNickName ( $NickName );
		if (! $User) {
			return $User;
		}
		$LUM = new LitterUserModel ();
		$LUM->create ();
		$LUM->{$LUM->F->Date} = get_now ();
		$LUM->{$LUM->F->Time} = time ();
		$LUM->{$LUM->F->From} = $this->_session ( 'f_userid' );
		$LUM->{$LUM->F->To} = $User [$UM->F->ID];
		return $LUM;
	}
}
?>