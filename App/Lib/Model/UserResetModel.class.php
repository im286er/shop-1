<?php
class UserResetModel extends Model {
	protected $tableName = 'user_reset';
	protected $fields = array (
			'u_id',
			'ur_code',
			'ur_active',
			'ur_createdate',
			'ur_activedate',
			'ur_ip',
			'_pk' => 'u_id' 
	);
	public function validtime($createdate) {
		$createdate = is_string ( $createdate ) ? strtotime ( $createdate ) : strtotime ( $this->ur_createdate );
		$now = time ();
		$result = $now - $createdate;
		if ($result <= C ( 'USER_RESET_VALID_TIME' )) {
			return true;
		}
		return false;
	}
}
?>