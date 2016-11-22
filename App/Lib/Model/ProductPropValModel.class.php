<?php
/**
 * 商品主分类属性值基本类
 * 
 * @author miaomin
 * Jun 24, 2015 6:44:38 PM
 * 
 * $Id: ProductPropValModel.class.php 6746 2015-08-10 02:04:03Z miaomiao $
 */
class ProductPropValModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_InfoProductPropVal
	 */
	public $F;
	
	/**
	 * 商品主分类基本类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->InfoPropVal;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
	
	/**
	 * INSERT
	 *
	 * @param string $delistr        	
	 * @param int $maintypeid        	
	 * @param int $propid        	
	 * @param string $delimiter        	
	 * @return mixed
	 */
	public function insertPropVals($delistr, $maintypeid, $propid, $delimiter = ',') {
		$res = false;
		$insertQuery = '';
		$valsArr = explode ( $delimiter, $delistr );
		if (count ( $valsArr )) {
			foreach ( $valsArr as $key => $val ) {
				$insertQuery .= "INSERT INTO " . $this->F->_Table . "(" . $this->F->PROPID . "," . $this->F->PROPVAL . "," . $this->F->MAINTYPE . ") VALUES ('" . $propid . "','" . $val . "','" . $maintypeid . "');";
			}
			
			$res = $this->execute ( $insertQuery );
		}
		return $res;
	}
	
	/**
	 * UPDATE
	 *
	 * @param string $delistr        	
	 * @param int $propid        	
	 * @param string $delimiter        	
	 * @return mixed
	 */
	public function updatePropVals($delistr, $propid, $delimiter = ',') {
		$res = false;
		$updateQuery = '';
		$valsArr = explode ( $delimiter, $delistr );
		
		$condition = array (
				$this->F->PROPID => $propid 
		);
		$findRes = $this->where ( $condition )->select ();
		
		if (count ( $findRes ) == count ( $valsArr )) {
			foreach ( $findRes as $key => $val ) {
				$updateQuery .= "UPDATE " . $this->F->_Table . " SET " . $this->F->PROPVAL . "='" . $valsArr [$key] . "' WHERE " . $this->F->ID . "='" . $val [$this->F->ID] . "';";
			}
			
			$res = $this->execute ( $updateQuery );
		}
		
		return $res;
	}
	
	/**
	 * REMOVE
	 *
	 * @param int $propid        	
	 * @return mixed
	 */
	public function removePropVals($propid) {
		$res = false;
		
		if ($propid) {
			
			$condition = array (
					$this->F->PROPID => $propid 
			);
			
			$this->where ( $condition )->delete ();
		}
		
		return $res;
	}
	
	/**
	 * 解析属性器
	 *
	 * @param string $propIdStr        	
	 * @return string $propNameStr
	 */
	static public function parseCombinePropVals($propIdStr, $dispSeparator = '#', $dispSeparator2 = ':') {
		$propNameStr = '';
		
		$propArr = explode ( '#', $propIdStr );
		
		foreach ( $propArr as $key => $val ) {
			$propNameStr .= self::parsePropVals ( $val, $dispSeparator2 ) . $dispSeparator;
		}
		
		if (substr ( $propNameStr, - strlen ( $dispSeparator ) ) == $dispSeparator) {
			$propNameStr = substr ( $propNameStr, 0, - strlen ( $dispSeparator ) );
		}
		
		return $propNameStr;
	}
	
	/**
	 * 解析属性器
	 *
	 * @param string $propIdStr        	
	 */
	public function parsePropVals($propIdStr, $dispSeparator = ':') {
		$res = '';
		
		$propIdStr = explode ( ':', $propIdStr );
		
		$PMPM = new ProductMainPropModel ();
		$propRes = $PMPM->find ( $propIdStr [0] );
		
		$PVM = new ProductPropValModel ();
		$valRes = $PVM->find ( $propIdStr [1] );
		
		return $propRes [$PMPM->F->PROPNAME] . $dispSeparator . $valRes [$PVM->F->PROPVAL];
	}
}