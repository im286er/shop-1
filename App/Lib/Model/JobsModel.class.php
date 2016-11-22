<?php
/**
 * 任务类
 *
 * @author miaomin 
 * Jun 26, 2013 10:17:22 AM
 */
class JobsModel extends Model {
	protected $tableName = 'jobs';
	protected $fields = array (
			'j_id',
			'j_act',
			'j_res',
			'j_type',
			'j_roll',
			'j_cycle',
			'j_limit',
			'j_val',
			'_pk' => 'j_id',
			'_autoinc' => TRUE 
	);
}
?>