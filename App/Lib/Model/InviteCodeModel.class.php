<?php
class InviteCodeModel extends Model {
	protected $tableName = 'invite_code';
	protected $fields = array (
			'ic_id',
			'ic_code',
			'ic_active',
			'ic_createdate',
			'ic_activedate',
			'_pk' => 'ic_id',
			'_autoinc' => TRUE 
	);
	public function getInviteCodeList($req) {
		import ( 'ORG.Util.Page' );
		$res = array (
				'arr' => array (),
				'page' 
		);
		$count = $this->where ( '1=1' )->count ();
		$Page = new Page ( $count, 20 );
		$show = $Page->show ();
		$list = $this->where ( '1=1' )->order ( 'ic_id' )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		$res ['arr'] = $list;
		$res ['page'] = $show;
		return $res;
	}
}
?>