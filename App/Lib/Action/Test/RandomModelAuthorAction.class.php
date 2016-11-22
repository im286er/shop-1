<?php
class RandomModelAuthorAction extends Action
{
	
	public function doRandom()
	{
		$MinUserID = $_GET['uidmin'];
		$MaxUserID = $_GET['uidmax'];
		$MinProductID = $_GET['pidmin'];
		$MaxProductID = $_GET['pidmax'];
		
		if(!$MinUserID || !$MinUserID || !$MinProductID || !$MaxProductID)
		{ echo '没有设置有效参数'; return; }
			
		$UsersID = $this->getUserIDList($MinUserID, $MaxUserID);
		if($UsersID === false) { echo '连接失败'; return; }
		if($UsersID === null) { echo '当前用户ID范围内找不到任何用户'; return; }
		
		$ProductsID = $this->getProductIDList($MinProductID, $MaxProductID);
		var_dump($ProductsID);
		if($ProductsID === false) { echo '连接失败'; return; }
		if($ProductsID === null) { echo '当前用户ID范围内找不到任何产品'; return; }
		
		$Counter = 0;
		$PMT = new ProductModel(); $PMT->startTrans();
		foreach($ProductsID as $PID)
		{
			$PM = new ProductModel();
			$PM->p_id = $PID;
			$PM->p_creater = $UsersID[array_rand($UsersID, 1)];
			if($PM->save() === false) { $PMT->rollback(); echo '更新意外终止: ' . $Counter; return; }
			$Counter++;
		}
		$PMT->commit(); 
		echo '完成: ' . $Counter; return;
	}
	
	private function getUserIDList($Min, $Max)
	{
		$UM = new UsersModel();
		$UserID = $UM->where("u_id>=" . $Min . " AND u_id<=" . $Max)->field('u_id')->select();
		if($UserID === false) { return false; }
		if($UserID === null) { return null; }
		$UserID = array_column($UserID, 'u_id');
		return $UserID;
	}
	
	private function getProductIDList($Min, $Max)
	{
		$PM = new ProductModel();
		$Where = "p_id>=" . $Min . " AND p_id<=" . $Max . ' AND p_lictype=0';
		$ProductID = $PM->where($Where)->field('p_id')->select();
		var_dump($PM->getLastSql());
		if($ProductID === false) { return false; }
		if($ProductID === null) { return null; }
		$ProductID = array_column($ProductID, 'p_id');
		var_dump($PM->getLastSql());
		return $ProductID;
	}
}
?>