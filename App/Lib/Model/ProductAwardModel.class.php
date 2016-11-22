<?php
/**
 * 产品奖项表
 *
 * @author miaomin 
 * Mar 14, 2014 10:24:34 AM
 *
 * $Id$
 */
class ProductAwardModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_ProductAward
	 */
	public $F;
	
	/**
	 * 产品奖项表
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->ProductAward;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 根据产品ID获取奖项
	 *
	 * @param int $PID        	
	 * @param int $Type        	
	 * @return Ambigous <mixed, string, boolean, NULL, unknown>
	 */
	public function getAwardsByProduct($PID) {
		$con = array (
				$this->F->_Table . '.' . $this->F->PID => $PID 
		);
		$res = $this->join('tdf_awardinfo ON tdf_awardinfo.aw_id =  tdf_product_award.aw_id')->where ( $con )->select ();
		return $res;
	}
	
	/**
	 * 自定义奖项
	 *
	 * @param int $pid        	
	 * @param array $tagsArray        	
	 * @param int $type        	
	 * @return mixed
	 */
	public function addAwardArray($pid, $tagsArray) {
		
		$AwardInfo = new AwardInfoModel();
		
		// 处理产品奖项信息
		$OldTags = $this->getAwardsByProduct ( $pid );
		
		if ($OldTags === false) {
			return false;
		}
		
		$OldTags = $OldTags ? $OldTags : array ();
		$ExistTags = array ();
		
		foreach ( $OldTags as $OldTag ) {
			if (in_array ( $OldTag [$AwardInfo->F->Name], $tagsArray )) {
				$ExistTags [] = $OldTag [$AwardInfo->F->Name];
			}
		}
		
		if (! array_diff ( $tagsArray, $OldTags )) {
			return true;
		}
		
		$TagsIDArray = $AwardInfo->addAwardsArray ( $tagsArray );
		if ($TagsIDArray === false) {
			return false;
		}
		
		// 处理产品奖项信息
		if ($this->where ( $this->F->PID . "='" . $pid . "'" )->delete () === false) {
			return false;
		}
		
		foreach ( $TagsIDArray as $TagID ) {
			$this->{$this->F->PID} = $pid;
			$this->{$this->F->AWID} = $TagID;
			if ($this->add () === false) {
				return false;
			}
		}
		
		// 处理
		$PM = new ProductModel ();
		$pmRes = $PM->find ( $pid );
		if (! $pmRes) {
			return false;
		}
		$pAwardStr = $this->tagsArrTotagsStr ( $TagsIDArray, '#' );
		$PM->{$PM->F->Awards} = $pAwardStr;
		$PM->save ();
		
		return true;
	}
	
	/**
	 * 将标签数组拼接成标签字符串
	 *
	 * @param array $tagsArray
	 * @param string $separator
	 * @return string
	 */
	private function tagsArrTotagsStr($tagsArray, $separator = ' ') {
		$TagsStr = '';
		foreach ( $tagsArray as $Tags ) {
			$TagsStr .= $Tags . $separator;
		}
		if (strlen ( $TagsStr ) > 0) {
			$TagsStr = substr ( $TagsStr, 0, strlen ( $TagsStr ) - strlen ( $separator ) );
		}
		return $TagsStr;
	}
}