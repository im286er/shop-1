<?php
/**
 * 商品销售报表基本类
 *
 * @author miaomin 
 * Feb 5, 2015 7:59:33 PM
 *
 * $Id$
 */
class SalesReportModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_SalesReport
	 */
	public $F;
	
	/**
	 * 商品销售报表基本类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->SalesReport;
		
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		
		if (! $this->_map) {
			$this->_map = $this->F->getMappedFields ();
		}
		
		parent::__construct ();
	}
	
	/**
	 * 付款成功后需要新增一条銷售记录
	 */
	public function addRecord($Product, $Prepaid, $PrepaidDetail) {
		$DBF_P = $this->DBF->Product;
		$DBF_PREPAID = $this->DBF->UserPrepaid;
		
		// 根据PCREATER获取MOCUID
		$UM = new UsersModel ();
		$umRes = $UM->find ( $Product [$DBF_P->Creater] );
		$prepaidUmRes = $UM->find ( $Prepaid [$DBF_PREPAID->UserID] );
		
		// 获取订单详情
		$pdArr = unserialize ( $PrepaidDetail );
		$itemDetailArr = array (
				'p_price' => 0,
				'uc_count' => 0 
		);
		foreach ( $pdArr as $key => $val ) {
			if ($val [$DBF_P->ID] == $Product [$DBF_P->ID]) {
				$itemDetailArr = $val;
				break;
			}
		}
		
		// 
		$belongPid = $Product [$DBF_P->BelongPid] ? $Product [$DBF_P->BelongPid] : $Product [$DBF_P->ID];
		
		$data = array (
				$this->F->CREATER => $Product [$DBF_P->Creater],
				$this->F->PMOCUID => $umRes [$UM->F->MocUID],
				$this->F->ORDERID => $Prepaid [$DBF_PREPAID->OrderID],
				$this->F->PREPAIDID => $Prepaid [$DBF_PREPAID->ID],
				$this->F->PID => $Product [$DBF_P->ID],
				$this->F->BELONGPID => $belongPid,
				$this->F->PPRICE => $itemDetailArr ['p_price'],
				$this->F->PCOUNT => $itemDetailArr ['uc_count'],
				$this->F->AMOUNT => $itemDetailArr ['p_price'] * $itemDetailArr ['uc_count'],
				$this->F->PREPAIDUID => $Prepaid [$DBF_PREPAID->UserID],
				$this->F->PREPAIDUGROUP => $prepaidUmRes [$UM->F->Group],
				$this->F->PREPAIDMOCUID => $prepaidUmRes [$UM->F->MocUID],
				$this->F->CREATEDATE => get_now (),
				$this->F->CDTIME => time (),
				$this->F->PREPAIDUIP => get_client_ip (),
				$this->F->PREPAIDUAGENT => get_client_agent(),
				$this->F->DISCOUNTTYPE => 0,
				$this->F->DISCOUNT => 0,
				$this->F->REALAMOUNT => 0 
		);
		
		return $this->add ( $data );
	}
	
	/**
	 * 根据销售员ID查询销售报表
	 *
	 * @param int $salesid
	 *        	销售人员用户ID
	 * @param int $createrid
	 *        	商品发布者ID
	 * @param string $startdate        	
	 * @param string $enddate        	
	 * @return array
	 */
	public function getReportBySalesman($salesid, $createrid = 0, $startdate = '', $enddate = '') {
		$condition = array(
				'tdf_sales_report.up_uid' => $salesid,
				'tdf_sales_report.p_creater' => $createrid,
				'tdf_sales_report.sr_createdate' => array(
						array('egt',$startdate),
						array('elt',$enddate)
				)
		);
		$getFields = array(
				'tdf_product.p_id',
				'tdf_product.p_cover',
				'tdf_product.p_producttype',
				'tdf_product.p_name',
				'tdf_sales_report.*'
		);
		return $this->join(' tdf_product ON tdf_sales_report.p_belongid = tdf_product.p_id')->where($condition)->field($getFields)->select();
	}
	
	/**
	 * 根据销售员ID查询销售报表
	 *
	 * @param int $createrid        	
	 * @param string $startdate        	
	 * @param string $enddate        	
	 * @return array
	 */
	public function getReportByStore($createrid, $startdate = '', $enddate = '') {
		$condition = array(
			'tdf_sales_report.p_creater' => $createrid,
			'tdf_sales_report.sr_createdate' => array(
				array('egt',$startdate),
				array('elt',$enddate)		
			)
		);
		$getFields = array(
			'tdf_product.p_id',
			'tdf_product.p_cover',
			'tdf_product.p_producttype',
			'tdf_product.p_name',
			'tdf_sales_report.*'
		);
		return $this->join(' tdf_product ON tdf_sales_report.p_belongid = tdf_product.p_id')->where($condition)->field($getFields)->select();
	}

    /**
     * @param $up_id
     * @param $p_id
     * @return mixed
     */
    public function getWorkOrder($up_id,$p_id){
        $result=$this->field("work_order")->where("up_id=".$up_id." and p_id=".$p_id."")->find();
        return $result['work_order'];
    }

    /**
     * @param $up_id 订单id
     * @param $p_id 产品id
     * @param $work_order 工厂单号
     * @return mixed
     */
    public function updateWorkOrder($up_id,$p_id,$work_order){
        $data['work_order']=$work_order;
        $result=M('sales_report')->where ( "up_id=" . $up_id . " and p_id=".$p_id."" )->setField ($data);
        return $result;
    }

}