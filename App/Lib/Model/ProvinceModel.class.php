<?php
/**
 * 地区 - 省类
 *
 * @author miaomin 
 * Jul 4, 2013 9:29:29 AM
 */
class ProvinceModel extends Model {
	protected $tableName = 'province';
	protected $fields = array (
			'pi_id',
			'pi_name',
			'pi_fid',
			'_pk' => 'pi_id',
			'_autoinc' => TRUE 
	);
}
?>