<?php
class UserInviteModel extends Model {
	protected $tableName = 'user_invite';
	protected $fields = array (
			'ui_id',
			'ui_mail',
			'ui_url',
			'ui_code',
			'ui_status',
			'ui_senddate',
			'ui_allowdate',
			'_pk' => 'ui_id',
			'_autoinc' => TRUE 
	);
	public function getUserInviteList($req) {
		import ( 'ORG.Util.Page' );
		$res = array (
				'arr' => array (),
				'page' 
		);
		$count = $this->where ( '1=1' )->count ();
		$Page = new Page ( $count, 20 );
		$show = $Page->show ();
		$list = $this->where ( '1=1' )->order ( 'ui_id' )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		$res ['arr'] = $list;
		$res ['page'] = $show;
		return $res;
	}
}
?>