<?php
/**
 * 日志工厂类
 *
 * @author miaomin 
 * Oct 8, 2013 4:38:35 PM
 */
class LogFactoryModel extends Model {
	
	/**
	 * 日志工厂类
	 *
	 * @param string $log        	
	 * @return Model
	 */
	public static function init($log) {
		switch (strtolower ( $log )) {
			case 'ecoupon' :
				$res = new LogEcouponModel ();
				break;
			case 'client' :
				$res = new LogClientModel ();
				break;
			case 'admin':
				$res= new LogAdminModel();
				break;
			default :
				// 其他状况
				$res = new stdClass ();
				break;
		}
		
		//
		if ($res instanceof Model) {
			// 返回
			return $res;
		} else {
			// 抛出异常
			throw new Exception ( 'log_type_undefined' );
		}
	}
}
?>