<?php
class UserCommentModel extends Model {
	protected $tableName = 'user_comment';
	protected $fields = array (
			'uc_id',
			'u_id',
			'uc_content',
			'uc_pid',
			'uc_type',
			'uc_createdate',
			'uc_slabel',
			'uc_replyid',
			'_pk' => 'uc_id',
			'_autoinc' => TRUE 
	);
}
?>