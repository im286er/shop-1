<?php
/**
 * 奖项信息表
 *
 * @author miaomin 
 * Mar 13, 2014 5:20:32 PM
 *
 * $Id$
 */
class AwardInfoModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_AwardInfo
	 */
	public $F;
	
	/**
	 * 奖项信息表
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->AwardInfo;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 添加奖项
	 *
	 * @param array $AwardsArray
	 * @return boolean multitype:NULL <mixed, boolean, unknown, string>
	 */
	public function addAwardsArray($AwardsArray) {
		$INStr = "'" . implode ( "','", $AwardsArray ) . "'";
		$DBTags = $this->where ( $this->F->Name . ' IN (' . $INStr . ')' )->select ();
		if ($DBTags === false) {
			return false;
		}
		$DBTags = $this->conventPKtoArrayKey ( $DBTags, $this->F->Name );
		$Result = array ();
		foreach ( $AwardsArray as $Tags ) {
			if (trim ( $Tags ) == '') {
				continue;
			}
			if (array_key_exists ( $Tags, $DBTags )) {
				$Result [$Tags] = $DBTags [$Tags] [$this->F->ID];
			} else {
				$this->{$this->F->Name} = $Tags;
				$TID = $this->add ();
				if (! $TID) {
					return false;
				}
				$Result [$Tags] = $TID;
			}
		}
		return $Result;
	}
	
	/**
	 * 主键转换
	 *
	 * @param array $Array
	 * @param string $PK
	 * @return multitype:multitype: unknown
	 */
	private function conventPKtoArrayKey($Array, $PK) {
		$Result = array ();
		foreach ( $Array as $Key => $Val ) {
			if (! isset ( $Result [$Val [$PK]] )) {
				$Result [$Val [$PK]] = array ();
			}
			$Result [$Val [$PK]] = $Val;
		}
		return $Result;
	}
}