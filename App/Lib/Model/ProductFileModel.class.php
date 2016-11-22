<?php
class ProductFileModel extends Model {
	protected $ct_name_mapper = array ();
	protected $_map = array (
			'extension' => 'pf_ext',
			'savepath' => 'pf_path',
			'savename' => 'pf_filename',
			'filesize' => 'pf_filesize',
			'filesize_disp' => 'pf_filesize_disp',
			'createdate' => 'pf_createdate',
			'createtool' => 'pf_createtool',
			'ctver' => 'pf_csversion',
			'remark' => 'pf_remark',
			'subtool' => 'pf_subcreatetool',
			'subctver' => 'pf_subcsversion',
			'uploader' => 'pf_uploader',
			'isfree' => 'pf_isfree',
			'pid' => 'p_id' 
	);
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	/**
	 *
	 * @var DBF_ProductFile
	 */
	public $F;
	function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->ProductFile;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		// $this->_map = $this->F->getMappedFields();
		parent::__construct ();
		$PCT = D ( 'ProductCreateTool' );
		$this->ct_name_mapper = $PCT->getMapper ();
	}
	public function getFileByProduct($ProductID) {
		return $this->where ( $this->F->ProductID . "='" . $ProductID . "'" )->select ();
	}
	public function getFbxFileIDByProduct($ProductID) {
		return $this->where ( $this->F->CreateTool . "=5 and " . $this->F->ProductID . "='" . $ProductID . "'" )->find ();
	}
	public function getFileByProductjSON($ProductID) {
		$Files = $this->getFileByProduct ( $ProductID );
		if ($Files === false) {
			return false;
		}
		return $this->conventFileToJson ( $Files );
	}
	public function getTempFileByUser($UserID) {
		return $this->where ( $this->F->ProductID . "='0' AND " . $this->F->Uploader . "='" . $UserID . "'" )->select ();
	}
	public function getFileByFileID($FileID) {
		return $this->where ( $this->F->ID . "='" . $FileID . "'" )->select ();
	}
	public function getTempFileJsonByUser($UserID) {
		$Files = $this->getTempFileByUser ( $UserID );
		if ($Files === false) {
			return false;
		}
		return $this->conventFileToJson ( $Files );
	}
	public function getFileByIDArray($IDArray, $UID = 0) {
		$where = $this->F->ID . ' IN(' . implode ( ',', $IDArray ) . ')';
		if ($UID) {
			$where = $this->F->Uploader . "='" . $UID . "' AND " . $where;
		}
		return $this->where ( $where )->select ();
	}
	public function deleteFileByID($FileID) {
		if (! is_array ( $FileID )) {
			$FileID = array (
					$FileID 
			);
		}
		foreach ( $FileID as $ID ) {
			if ($this->where ( $this->F->ID . "='" . $ID . "'" )->delete () === false) {
				return false;
			}
		}
		return true;
	}
	private function conventFileToJson($Files) {
		$JsonStr = '';
		foreach ( $Files as $File ) {
			$FileID = $File [$this->F->ID];
			$FileName = $File [$this->F->OriginalName] ? $File [$this->F->OriginalName] : $File [$this->F->FileName];
			$FileSize = $File [$this->F->FileSize];
			$CreateDate = $File [$this->F->CreateDate];
			$FileCT = $File [$this->F->CreateTool];
			$FileCTV = $File [$this->F->CTVersion];
			$FileSCT = $File [$this->F->SubCreateTool];
			$FileSCTV = $File [$this->F->SubCTVersion];
			$JsonStr .= '{"FileID":' . $FileID . ',"FileName":"' . $FileName . '","FileSize":' . $FileSize . ',"CreateDate":"' . $CreateDate . '","CT":' . $FileCT . ',"CTV":"' . $FileCTV . '","SCT":' . $FileSCT . ',"SCTV":"' . $FileSCTV . '"},';
		}
		if (strlen ( $JsonStr ) > 0) {
			$JsonStr = substr ( $JsonStr, 0, strlen ( $JsonStr ) - 1 );
		}
		return '[' . $JsonStr . ']';
	}
	// old
	public function getCtNameMapper() {
		return $this->ct_name_mapper;
	}
	
	/*
	 * 根据文件名、路径、p_id判断文件数据是否已经存在 @$file_arr:数组,包括 文件名、文件路径、p_id @返回:0不存在 1已经存在
	 * zhangzhibin
	 */
	public function getHaveByFileArr($farr) {
		// $t=$this->where("pf_filename='" . $farr['pf_filename'] . "' and
		// pf_path='".$farr['pf_path']."' and p_id=".$farr['p_id'])->find();
		// $result=$this->getLastSql();
		$RES = $this->where ( "pf_filename='" . $farr ['pf_filename'] . "' and pf_path='" . $farr ['pf_path'] . "' and p_id=" . $farr ['p_id'] )->find ();
		if ($RES) {
			$result = $RES ['pf_id'];
		} else {
			$result = 0;
		}
		return $result;
	}
	
	/**
	 * 根据文件ID判断当前用户是否为文件上传者
	 *
	 * @aurthor miaomin
	 *
	 * @param int $fid        	
	 * @param int $uid        	
	 * @return boolean
	 */
	public function isUploader($fid, $uid) {
		$res = false;
		$findRes = $this->find ( $fid );
		if ($findRes) {
			$res = ($findRes [$this->F->Uploader] == $uid) ? true : false;
		}
		return $res;
	}
	
	public function getFileByFilename($pfname) {
		return $this->where ( $this->F->FileName . "='" . $pfname . "'" )->select();
	}
}
?>