<?php
/**
 * 素原销售报表类
 *
 * @author miaomin 
 * Mar 11, 2015 10:21:34 AM
 *
 * $Id$
 */
class SysalesAction extends CommonAction {
	
	/**
	 * 首页
	 */
	public function index() {
		$this->display ();
	}
	
	/**
	 * 按日期查看销售报表
	 */
	public function reportbydate() {
		try {
			$storeId = 1458;
			$startDate = date ( 'Y-m-d');
			$endDate = date ( 'Y-m-d');
			
			if (IS_POST) {
				$startDate = $_POST ['from'];
				$endDate = $_POST ['to'];
			}
			
			$SRM = new SalesReportModel ();
			$srmRes = $SRM->getReportByStore ( $storeId, $startDate.' 00:00:00', $endDate.' 23:59:59' );
			
			$this->assign ( 'listTable', $srmRes );
			$this->assign ( 'from', $startDate );
			$this->assign ( 'to', $endDate );
			$this->display ();
		} catch ( Exception $e ) {
		}
	}
	
	/**
	 * 按销售人员查看销售报表
	 */
	public function reportbysales() {
		try {
			$salesArr = array (
					array (
							'key' => 'RasmusLerdorf',
							'value' => 25 
					),
					array (
							'key' => 'zxb',
							'value' => 293 
					) 
			);
			$salesId = 25;
			$storeId = 1458;
			$startDate = date ( 'Y-m-d' );
			$endDate = date ( 'Y-m-d' );
			
			if (IS_POST) {
				$salesId = $_POST ['salesid'];
				$startDate = $_POST ['from'];
				$endDate = $_POST ['to'];
			}
			
			$SRM = new SalesReportModel ();
			$srmRes = $SRM->getReportBySalesman ( $salesId, $storeId, $startDate.' 00:00:00', $endDate.' 23:59:59' );
			
			$this->assign ( 'salesOption', get_dropdown_option ( $salesArr, $salesId ) );
			$this->assign ( 'listTable', $srmRes );
			$this->assign ( 'from', $startDate );
			$this->assign ( 'to', $endDate );
			$this->display ();
		} catch ( Exception $e ) {
		}
	}
}
?>