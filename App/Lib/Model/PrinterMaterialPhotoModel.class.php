<?php
/**
 * 材质图片类
 *
 * @author miaomin 
 * Aug 26, 2014 4:40:13 PM
 *
 * $Id$
 */
class PrinterMaterialPhotoModel extends Model {
	
	/**
	 *
	 * @var DBF_PrinterMaterialPhoto
	 */
	public $F;
	
	/**
	 * 材质图片类
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->PrinterMaterialPhoto;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 根据材料ID获取相片
	 *
	 * @param int $PMID
	 *        	材料ID
	 * @return array
	 */
	public function getPhotosByPMID($PMID) {
		return $this->where ( $this->F->PMID . "='" . $PMID . "'" )->order ( $this->F->DISPWEIGHT . ' DESC' )->select ();
	}
	
	/**
	 * 插入一条相片数据
	 *
	 * @param string $OriginalName
	 *        	原文件名
	 * @param string $FileName
	 *        	保存的文件名
	 * @param string $Path
	 *        	保存路径
	 * @param int $PID
	 *        	产品ID
	 * @return false/插入数据ID
	 */
	public function insertPhoto($OriginalName, $FileName, $Path, $PID) {
		// @formatter:off
		$PhotoData = array(
				$this->F->ORIGINALNAME => $OriginalName,
				$this->F->FILENAME => $FileName,
				$this->F->PATH => $Path,
				$this->F->CREATEDATE => get_now(),
				$this->F->TITLE => substr($OriginalName,0 ,50),
				$this->F->PMID => $PID);
		$Result = $this->add($PhotoData);
		return $Result;
		// @formatter:on
	}
}
?>