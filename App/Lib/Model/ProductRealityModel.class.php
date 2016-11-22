<?php
class ProductRealityModel extends Model {
	protected $tableName = 'product_reality';
	protected $fields = array (
			'r_id',
			'p_id',
			'r_filename',
			'r_mdname',
			'r_type',
			'r_path',
			'r_createdate',
			'r_lastupdate',
			'r_enable',
			'_pk' => 'r_id',
			'_autoinc' => true 
	);
	public function getRealityList($pid) {
		return $this->where ( 'p_id=' . $pid )->select ();
	}
}
?>