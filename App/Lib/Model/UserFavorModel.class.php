<?php
class UserFavorModel extends Model {
	protected $tableName = 'user_favor';
	protected $fields = array (
			'u_id',
			'uf_id',
			'uf_type',
			'uf_createdate'
	);
}
?>