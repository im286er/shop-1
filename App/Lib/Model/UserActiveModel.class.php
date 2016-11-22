<?php
class UserActiveModel extends Model {
	protected $tableName = 'user_active';
	protected $fields = array (
			'u_id',
			'uc_code',
			'uc_active',
			'uc_createdate',
			'uc_activedate',
			'_pk' => 'u_id' 
	);
	public function validtime($createdate) {
		$createdate = is_string ( $createdate ) ? strtotime ( $createdate ) : strtotime ( $this->uc_createdate );
		$now = time ();
		$result = $now - $createdate;
		if ($result <= C ( 'USER_ACTIVE_VALID_TIME' )) {
			return true;
		}
		return false;
	}
}
?>