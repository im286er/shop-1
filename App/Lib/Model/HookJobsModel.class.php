<?php
class HookJobsModel extends Model {
	/**
	 * 任务系统
	 *
	 * 将会对传入系统中的用户id和操作行为进行处理
	 * 具体的处理逻辑如下:
	 * 1.根据操作行为获取到操作id
	 * 2.从tdf_jobs表中获取此次操作行为带出的结果行为id
	 * 3.循环执行结果行为
	 *
	 * @param int $uid        	
	 * @param string $mname        	
	 *
	 */
	public function run(int $uid, string $mname) {
		$Method = D ( 'Methods' );
		$Method->getBym_name ( $mname );
		
		$Job = D ( 'Jobs' );
		$list = $Job->where ( 'j_act=' . $Method->m_id )->select ();
		
		foreach ( $list as $k => $v ) {
			switch ($v ['j_cycle']) {
				case 1 : // 单次
					$UJ = D ( 'UserJobs' );
					$count = $UJ->where ( 'u_id=' . $uid . ' and j_id=' . $v ['j_id'] )->count ();
					if (! $count) {
						// 执行
						
						$Method->find ( $v ['j_res'] );
						// pr($Method->data());
						$preExcute = explode ( '::', $Method->m_name );
						// pr($preExcute);
						$EXEOBJ = D ( $preExcute [0] );
						$EXEOBJ->find ( $uid );
						
						call_user_func_array ( 
							array (
								$EXEOBJ,
								$preExcute [1] 
							), 
							array (
								$v ['j_val'] 
							) 
						);
						// vd($EXEOBJ);
						$UJ->u_id = $uid;
						$UJ->j_id = $v ['j_id'];
						$UJ->uj_date = get_now ();
						$UJ->uj_timestamp = time ();
						$UJ->uj_award = $v ['j_val'];
						$r = $UJ->add ();
						if($r){
							$result[$k]=array(
								'status'=>1,
								'val'		=>$v['j_val']
							);
						}
					}
					break;
				case 2 : // 日
					$ts = getTodayTS ();
					$UJ = D ( 'UserJobs' );
					$count = $UJ->where ( 'u_id=' . $uid . ' and j_id=' . $v ['j_id'] . ' and uj_timestamp >= ' . $ts ['s'] . ' and uj_timestamp <= ' . $ts ['e'] )->count ();
					if (! $count) {
						// 执行
						$Method->find ( $v ['j_res'] );
						// pr($Method->data());
						$preExcute = explode ( '::', $Method->m_name );
						// pr($preExcute);
						$EXEOBJ = D ( $preExcute [0] );
						$EXEOBJ->find ( $uid );
						
						call_user_func_array ( array (
								$EXEOBJ,
								$preExcute [1] 
						), array (
								$v ['j_val'] 
						) );
						// vd($EXEOBJ);
						// 删除
						$UJ->where ( 'u_id=' . $uid . ' and j_id=' . $v ['j_id'] )->delete ();
						// 增加
						$UJ->u_id = $uid;
						$UJ->j_id = $v ['j_id'];
						$UJ->uj_date = get_now ();
						$UJ->uj_timestamp = time ();
						$UJ->uj_award = $v ['j_val'];
						$r=$UJ->add ();
					} else {
						$UJ->where ( 'u_id=' . $uid . ' and j_id=' . $v ['j_id'] . ' and uj_timestamp >= ' . $ts ['s'] . ' and uj_timestamp <= ' . $ts ['e'] )->find ();
						if ($UJ->uj_award < $v ['j_limit']) {
							// 执行
							$Method->find ( $v ['j_res'] );
							// pr($Method->data());
							$preExcute = explode ( '::', $Method->m_name );
							// pr($preExcute);
							$EXEOBJ = D ( $preExcute [0] );
							$EXEOBJ->find ( $uid );
							call_user_func_array ( array (
									$EXEOBJ,
									$preExcute [1] 
							), array (
									$v ['j_val'] 
							) );
							// 更新UJ
							$UJ->uj_date = get_now ();
							$UJ->uj_timestamp = time ();
							$UJ->uj_award += $v ['j_val'];
							$r=$UJ->save ();
							if($r){
								$result[$k]=array(
										'status'=>1,
										'val'		=>$v['j_val']
								);
							}
						}
					}
					break;
				case 3 :
					break;
					
				case 4 :
				
					break;
				case 5 : //不限次数
					$UJ = D ( 'UserJobs' );
					//$count = $UJ->where ( 'u_id=' . $uid . ' and j_id=' . $v ['j_id'] )->count ();
					//if (! $count) {
						// 执行
						$Method->find ( $v ['j_res'] );
						// pr($Method->data());
						$preExcute = explode ( '::', $Method->m_name );
						// pr($preExcute);
						$EXEOBJ = D ( $preExcute [0] );
						$EXEOBJ->find ( $uid );
					
						call_user_func_array (
						array (
						$EXEOBJ,
						$preExcute [1]
						),
						array (
						$v ['j_val']
						)
						);
						// vd($EXEOBJ);
						$UJ->u_id = $uid;
						$UJ->j_id = $v ['j_id'];
						$UJ->uj_date = get_now ();
						$UJ->uj_timestamp = time ();
						$UJ->uj_award = $v ['j_val'];
						$r = $UJ->add ();
						if($r){
							$result[$k]=array(
									'status'=>1,
									'val'		=>$v['j_val']
							);
						}
					//}
					break;
			}
			
		}
		return $result;
	}
}
?>