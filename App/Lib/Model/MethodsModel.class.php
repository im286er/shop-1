<?php
/**
 * 行为类
 *
 * @author miaomin 
 * Jun 24, 2013 10:21:23 AM
 */
class MethodsModel extends Model {
	protected $tableName = 'methods';
	protected $fields = array (
			'm_id',
			'm_name',
			'_pk' => 'm_id',
			'_autoinc' => TRUE 
	);
}
?>