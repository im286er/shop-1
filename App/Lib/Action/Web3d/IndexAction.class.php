<?php
class IndexAction extends CommonAction {
	public function index() {
		$this->display ();
	}
	public function web3dshow() {
		$modelid = ( int ) $this->_get ( 'modelid' );
		if ($modelid) {
			$PFM = new ProductFileModel ();
			$pfmSelectRes = $PFM->find ( $modelid );
			if ($pfmSelectRes) {
				$PID = $pfmSelectRes [$PFM->F->ProductID];
				$PWEB = new ProductWebglModel ();
				$pwebCondition = array (
						$PWEB->F->PID => $PID,
						$PWEB->F->PFID => $modelid 
				);
				$pwebSelectRes = $PWEB->where ( $pwebCondition )->find ();
				if ($pwebSelectRes) {
					if ($pwebSelectRes [$PWEB->F->STAT] == 1) {
						$pwebSelectRes [$PWEB->F->ORIGINALFILE] = preg_replace ( '/' . C ( 'WEB3D.SAVE_FILEPATH_PREFIX' ) . '/', '/', $pwebSelectRes [$PWEB->F->ORIGINALFILE] );
					} else {
						$pwebSelectRes [$PWEB->F->ORIGINALFILE] = preg_replace ( '/' . C ( 'WEB3D.SAVE_FILEPATH_PREFIX' ) . '/', '/', $pwebSelectRes [$PWEB->F->LASTUPDATEFILE] );
					}
					$this->assign ( 'pwebgl', json_encode ( $pwebSelectRes ) );
				}
			}
		}
		$this->display ();
	}
	
	// 编辑WEB3D
	public function web3dedit() {
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		} // 判断登录
		$modelid = ( int ) $this->_post ( 'modelid' );
		if ($modelid) {
			$uid = $this->_session ( 'f_userid' );
			
			$PFM = new ProductFileModel ();
			$pfmSelectRes = $PFM->find ( $modelid );
			if ($pfmSelectRes) {
				if ($pfmSelectRes[$PFM->F->Uploader] !== $uid){
					$this->error ( "抱歉，没有权限操作！", __APP__ );
				}
				$PID = $pfmSelectRes [$PFM->F->ProductID];
				$PWEB = new ProductWebglModel ();
				$pwebCondition = array (
						$PWEB->F->PID => $PID,
						$PWEB->F->PFID => $modelid 
				);
				$pwebSelectRes = $PWEB->where ( $pwebCondition )->find ();
				if ($pwebSelectRes) {
					if ($pwebSelectRes [$PWEB->F->STAT] == 1) {
						$pwebSelectRes [$PWEB->F->ORIGINALFILE] = preg_replace ( '/' . C ( 'WEB3D.SAVE_FILEPATH_PREFIX' ) . '/', '/', $pwebSelectRes [$PWEB->F->ORIGINALFILE] );
					} else {
						$pwebSelectRes [$PWEB->F->ORIGINALFILE] = preg_replace ( '/' . C ( 'WEB3D.SAVE_FILEPATH_PREFIX' ) . '/', '/', $pwebSelectRes [$PWEB->F->LASTUPDATEFILE] );
					}
					$this->assign ( 'pwebgl', json_encode ( $pwebSelectRes ) );
				}
			}
		}
		$this->display ();
	}
}
?>