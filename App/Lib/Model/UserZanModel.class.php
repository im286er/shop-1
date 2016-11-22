<?php
/**
 * 用户点赞行为类
 *
 * @author miaomin 
 * Mar 10, 2014 10:12:08 AM
 *
 * $Id$
 */
class UserZanModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_UserZan
	 */
	public $F;
	
	/**
	 * 用户点赞行为类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->UserZan;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 根据获赞对象ID获取点赞用户列表
	 *
	 * 返回结果：
	 * NULL表示没有结果
	 * False表示查询错误
	 * 二维数组表示查询结果
	 *
	 * @param int $pId        	
	 * @param int $pType        	
	 * @return multitype
	 */
	public function getZanUserList($pId, $pType = 1) {
		$res = NULL;
		$con = array (
				$this->F->ZanID => $pId,
				$this->F->ZanType => $pType 
		);
		$res = $this->where ( $con )->select ();
		return $res;
	}
	
	/**
	 * 根据获赞对象ID获取点赞用户列表
	 *
	 * 返回结果：
	 * NULL表示没有结果
	 * False表示查询错误
	 * 二维数组表示查询结果
	 *
	 * @param int $uId        	
	 * @return multitype
	 */
	public function getZanPList($uId) {
		$res = NULL;
		$con = array (
				$this->F->UID => $uId 
		);
		$res = $this->where ( $con )->select ();
		return $res;
	}
	
	/**
	 * 加赞
	 *
	 * 返回结果：
	 * 如果数据非法或者查询错误则返回false
	 * 如果是自增主键 则返回主键值，否则返回1
	 * 如果已经点过赞则返回NULL
	 *
	 * @param int $uId        	
	 * @param int $pId        	
	 * @param int $pType        	
	 * @return multitype
	 */
	public function addZan($uId, $pId, $pType = 1) {
		// 重复的就别加了
		$res = NULL;
		$con = array (
				$this->F->UID => $uId,
				$this->F->ZanID => $pId,
				$this->F->ZanType => $pType 
		);
		$allowZan = $this->where ( $con )->find ();
		if ($allowZan === null) {
			$data = $con;
			$data [$this->F->CTime] = get_now ();
			$this->create ( $data );
			$res = $this->add ();
		}
		// 更新计数
		if ($res) {
			$PM = new ProductModel ();
			$pmRes = $PM->find ( $pId );
			if (! $pmRes) {
				return false;
			}
			$PM->{$PM->F->Zans} = $PM->{$PM->F->Zans} + 1;
			$PM->save ();
		}
		
		return $res;
	}

/*
 * 判断增加点赞记录
 */
    public function addZanNew($uId, $pId, $ip,$pType = 1){
        $now = time ();
        $UZAN = new UserZanModel ();
        $condition ['u_id']     = $uId;
        $condition ['uz_pid']   = $pId;
        $condition ['uz_ip']    = $ip;
        $uzanRes = $UZAN->where ( $condition )->order ( 'uz_id DESC' )->limit ( 1 )->find ();
        if (($uzanRes) && ($now - $uzanRes ['uz_ts']) <= 1800000) {
            $result=0;
        }else{
            $UZAN->create ();
            $UZAN->u_id = $uId;
            $UZAN->uz_pid = $pId;
            $UZAN->uz_date = get_now ();
            $UZAN->uz_ip = $ip;
            $UZAN->uz_ts = time ();
            if (! $UZAN->add ()) {
                $result=0;
            }else{
                $result=1;
            }
        }
        return $result;
    }
}