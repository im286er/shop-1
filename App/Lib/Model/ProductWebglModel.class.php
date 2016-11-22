<?php
/**
 * 模型Webgl Model类
 *
 * @author miaomin 
 * Jul 8, 2014 7:28:30 PM
 *
 * $Id$
 */
class ProductWebglModel extends Model {
	
	/**
	 *
	 * @var DBF_ProductWebgl
	 */
	public $F;
	
	/**
	 * 模型Webgl Model类
	 */
	public function __construct() {
		$DBF = new DBF ();
		$this->F = $DBF->ProductWebgl;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 判断文件是否被允许转换WEBGL
	 *
	 * @param array $fileinfo        	
	 * @return boolean
	 */
	static public function isAllowWebglConvert($fileinfo) {
		if (C ( 'WEB3D_ENABLED' )) {
			// 如果是WEB3D需要对文件的格式和大小做判断
			return ((in_array ( $fileinfo ['pct_ext'], C ( 'WEB3D.ALLOW_CONVERT_TYPE' ) )) && ($fileinfo ['pf_filesize'] <= C ( 'WEB3D.ALLOW_CONVERT_SIZE' )));
		} else {
			// 如果是LAO3D都可以转
			return true;
		}
	}
}
?>