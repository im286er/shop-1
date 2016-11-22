<?php
/**
 * 地区 - 城市类
 *
 * @author miaomin 
 * Jul 4, 2013 9:30:37 AM
 */
class CityModel extends Model {
	protected $tableName = 'city';
	protected $fields = array (
			'ci_id',
			'ci_name',
			'pi_id',
			'pi_no',
			'_pk' => 'ci_id',
			'_autoinc' => TRUE 
	);
}
?>