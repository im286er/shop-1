<?php
/**
 * 客户端版本信息Model类
 *
 * @author miaomin 
 * Dec 19, 2013 10:59:12 AM
 *
 * $Id: ClientVersionModel.class.php 1156 2013-12-24 03:19:58Z miaomiao $
 */
class ClientVersionModel extends Model {
	
	/**
	 *
	 * @var DBF_ClientVersion
	 */
	public $F;
	
	/**
	 * 客户端版本信息Model类
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->ClientVersion;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 根据当前版本信息获取最新的更新版本
	 */
	public function needUpdate($version) {
		$res = array (
				'current_version' => '',
				'needupdate' => 0 
		);
		
		$newVer = $this->_findByNewestVersion ();
		if ($newVer) {
			$res ['current_version']= $newVer ['cv_version'];
			$res ['needupdate'] 	= 1;
			$res ['cvtype']			= $newVer['cv_type'];
			$res ['downurl']		= $newVer['cv_downurl'];
		}
		
		$version = isset ( $version ) ? $version : 0;
		if ($version) {
			$verRes = $this->_findByVersion ( $version );
			if ($verRes) {
				$updateRes = $this->_findByUpdate ( $verRes ['cv_id'] );
				if ($updateRes) {
					$updateLv = 2;
					foreach ( $updateRes as $key => $val ) {
						if ($val ['cv_level'] == 1) {
							$updateLv = 1;
						}
					}
					$res ['current_version'] = $updateRes [0]['cv_version'];
					$res ['downurl']		= $updateRes [0]['cv_downurl'];
					$res ['cvtype']			= $updateRes [0]['cv_type'];
					$res ['needupdate'] 	= $updateLv;
					return $res;
				} else {
					$res ['current_version'] = $version;
					$res ['needupdate'] 	 = 0;
					$res ['downurl']		 = "0";
					$res ['cvtype']			 = "0";
						
					return $res;
				}
			}
		}
		
		return $res;
	}
	
	/**
	 * 根据版本号查找记录
	 *
	 * @param string $version        	
	 */
	private function _findByVersion($version) {
		return $this->where ( "cv_version='" . $version . "'" )->find ();
	}
	
	/**
	 * 查找最新版本
	 */
	private function _findByNewestVersion() {
		return $this->order ( 'cv_id desc' )->limit ( 1 )->find ();
	}
	
	/**
	 * 根据版本ID查找更新信息
	 *
	 * @param int $cvid        	
	 */
	private function _findByUpdate($cvid) {
		return $this->where ( "cv_id > '" . $cvid . "'" )->order ( 'cv_id desc' )->select ();
	}
	
	function getclientvsersion_all(){
		return $this->order ( 'cv_lastupdate desc' )->select ();		
	}
	function getclientversionBycvid($cvid){
		return $this->where ( "cv_id = '" . $cvid . "'" )->order ( 'cv_id desc' )->select ();
	}
	
}
?>