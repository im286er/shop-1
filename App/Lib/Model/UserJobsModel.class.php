<?php
/**
 * 用户任务类
 *
 * @author miaomin 
 * Jun 26, 2013 10:35:41 AM
 */
class UserJobsModel extends Model {
	protected $tableName = 'user_jobs';
	protected $fields = array (
			'uj_id',
			'u_id',
			'j_id',
			'uj_date',
			'uj_timestamp',
			'uj_award',
			'_pk' => 'uj_id',
			'_autoinc' => TRUE 
	);
}
?>