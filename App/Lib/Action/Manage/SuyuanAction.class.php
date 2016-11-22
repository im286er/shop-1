<?php
/**
 * 素原销售管理
 *
 * @author miaomin 
 * Dec 12, 2014 11:12:32 AM
 *
 * $Id$
 */
class SuyuanAction extends CommonAction {
	
	/**
	 * 首页
	 */
	public function index() {
		$this->display ();
	}
	
	/**
	 * 产品销售记录明细
	 */
	public function salesdetail() {
		try {
			$this->display ();
		} catch ( Exception $e ) {
		}
	}
}
?>