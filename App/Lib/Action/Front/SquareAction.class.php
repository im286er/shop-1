<?php
class SquareAction extends CommonAction {

	
	
	public function __construct() {
		parent::__construct ();
		$this->DBF = new DBF ();
		$this->header="square";
	}
	
	/*达人广场首页
	 * 
	 */
	public function index(){
		$dsql="select TUP.u_name,TUP.u_firstname,TUP.u_bir,TUP.u_sex,TUP.u_newprof,TU.u_avatar,TU.u_id from tdf_users as TU ";
		$dsql.="Left Join tdf_user_profile as TUP On TUP.u_id=TU.u_id ";
		$dsql.="where TU.u_group=1 order by TUP.u_sort asc limit 0,9";
		$designList=M()->query($dsql);
		$sexArr=L('sex');
		foreach($designList as $key => $value){
			$designList[$key]['u_sex_show']=$sexArr[$value['u_sex']];
			$designList[$key]['u_newprof_show']=$this->show_newprof($value['u_newprof']);
		}

		$jsql="select TUP.u_name,TUP.u_firstname,TUP.u_bir,TUP.u_sex,TUP.u_newprof,TU.u_avatar,TU.u_id from tdf_users as TU ";
		$jsql.="Left Join tdf_user_profile as TUP On TUP.u_id=TU.u_id ";
		$jsql.="where TU.u_group=3 limit 0,9";
		$jkList=M()->query($jsql);
		$sexArr=L('sex');
		foreach($jkList as $key => $value){
			$jkList[$key]['u_sex_show']=$sexArr[$value['u_sex']];
			$jkList[$key]['u_newprof_show']=$this->show_newprof($value['u_newprof']);
		}
		
		$this->assign('jklist',$jkList);
		$this->assign('designlist',$designList);
		$this->assign('header',$this->header);
		$this->_renderPage();
	}
	
	/*
	 * 根据newprofStr字符串返回专注领域数组
	 * @newprofStr 专注领域字符串
	 */
	public function show_newprof($newprofStr){
		$newprofArr=explode(",",$newprofStr);
		$UNPM=new UserNewProfModel();
		$newprofConfArr=$UNPM->getAllProfArr();
		foreach($newprofArr as $k => $v){
			$result[$k]=$newprofConfArr[$v];
		}
		return $result;
	}
	
	public function test(){
		echo "dcd";
	}
	
	
	
}
?>