<?php
class UserActionModel extends Model {
	protected $tableName = 'user_action';
	protected $fields = array (
			'ua_id',
			'ua_parent',
			'ua_code',
			'ua_relevance',
			'_pk' => 'ua_id',
			'_autoinc' => TRUE 
	);
	public function getPrivilegeArr() {
		/* 获取权限的分组数据 */
		$res = $this->where ( 'ua_parent=0' )->select ();
		foreach ( $res as $key => $val ) {
			$priv_arr [$val ['ua_id']] = $val;
		}
		
		/* 按权限组查询底级的权限名称 */
		$res = $this->where ( 'ua_parent' . db_create_in ( array_keys ( $priv_arr ) ) )->select ();
		foreach ( $res as $key => $val ) {
			$priv_arr [$val ['ua_parent']] ['priv'] [$val ["ua_code"]] = $val;
		}
		
		// 将同一组的权限使用 "," 连接起来，供JS全选
		foreach ( $priv_arr as $action_id => $action_group ) {
			$priv_arr [$action_id] ['priv_list'] = join ( ',', @array_keys ( $action_group ['priv'] ) );
		}
		return $priv_arr;
	}
}
?>