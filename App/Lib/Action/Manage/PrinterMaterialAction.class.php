<?php
class PrinterMaterialAction extends CommonAction
{
	private $UrlHome = 'admin'; //! ?
	private $UrlIndex = 'admin/printer_material/index';
	private $UrlAdd = 'admin/printer_material/add';
	private $UrlEdit = 'admin/printer_material/edit';
	
	function __construct()
	{
		parent::__construct();
		$this->UrlHome = WEBROOT_PATH . $this->UrlHome;
		$this->UrlIndex = U($this->UrlIndex);
		$this->UrlAdd = U($this->UrlAdd);
		$this->UrlEdit = U($this->UrlEdit);
		$this->assign('U_Home', $this->UrlHome);
		$this->assign('U_Index', $this->UrlIndex);
		$this->assign('U_Add', $this->UrlAdd);
		$this->assign('U_Edit', $this->UrlEdit);
		parent::__construct();
	}
	
	public function index()
	{ 
		$PMPM = new PrinterMaterialPickerModel();
		if(!$PMPM->IsLoaded) { return $this->error('数据库连接失败，请稍后再试', $this->UrlHome); }
		$PrintMaterialList = $PMPM->getChildList(0);
		$this->assign('PMList', $PrintMaterialList);
		$this->display();
	}
	
	public function add()
	{
		$PMPM = new PrinterMaterialPickerModel();
		if(!$PMPM->IsLoaded) { return $this->error('数据库连接失败，请稍后再试', $this->UrlIndex); }
		$this->assign('PMOption', $PMPM->getChildList(0, 1));
		if($this->isPost())
		{
			$this->assign('Post', $_POST);
			$PMPost = $this->getPMPost();
			if(!$PMPost) { return $this->displayError('有必要项没有填写'); }
			$PMPM = $this->buildPMData(); $PMPM->startTrans();
			if($PMPM->add() === false) { $PMPM->rollback(); return $this->displayError('数据库连接失败，请稍后再试'); }
			$PMPM->commit(); return $this->success('添加成功', $this->UrlIndex);
		}
		$this->display();
	}
	
	public function edit()
	{
		$PMID = $this->getMPID();
		if(!$PMID) { return $this->error('当前项不存在或已被删除', $this->UrlIndex); }
		
		$PMPM = new PrinterMaterialPickerModel();
		if(!$PMPM->IsLoaded) { return $this->error('数据库连接失败，请稍后再试', $this->UrlIndex); }
		$this->assign('PMOption', $PMPM->getChildList(0, 1));
		
		$PMPM = new PrinterMaterialPickerModel();
		$PM = $PMPM->getItemByID($PMID);
		if($PM === false) { return $this->error('数据库连接失败，请稍后再试', $this->UrlIndex); }
		if($PM === null) { return $this->error('当前项不存在或已被删除', $this->UrlIndex);}
		
		$this->assign('Post', $PMPM->parseFieldsMap($PM)); //!
		if($this->isPost())
		{
			$Post = $_POST; $Post['id'] = $PMID;
			$this->assign('Post', $Post);
			$PMPost = $this->getPMPost();
			if(!$PMPost) { return $this->displayError('有必要项没有填写'); }
			
			if($PMID == $PMPost['parentid']) { return $this->displayError('不能设置自己为父级'); }
			if($PMPM->hasChind($PMID) && $PMPost['parentid'] != 0)
			{ return $this->displayError('当前项存在子项，不能被设置为子项'); }
			$ParentPM = $PMPM->getItemByID($PMID);
			if(!$ParentPM) { return $this->displayError('当前选择的父级不存在，请重新选择'); }
			if($PMPost['parentid'] != 0 && $ParentPM[$PMPM->F->ParentID] == 0)
			{ return $this->displayError('不能设置一个子项作为父项'); }
			
			$PMPM = $this->buildPMData($PMID, $PMID); $PMPM->startTrans();
			if($PMPM->save() === false) { $PMPM->rollback(); return $this->displayError('数据库连接失败，请稍后再试'); }
			$PMPM->commit(); return $this->success('保存成功', $this->UrlIndex);
		}
		$this->display();
	}
	
	public function delete()
	{
		$PMID = $this->getMPID();
		if(!$PMID) { return $this->error('打开方式错误', $this->UrlIndex); }
		$PMPM = new PrinterMaterialPickerModel();
		if(!$PMPM->IsLoaded) { return $this->error('数据库连接失败，请稍后再试', $this->UrlIndex); }
		$PM = $PMPM->getItemByID($PMID);
		if($PM === false) { return $this->error('连接失败，请稍后再试', $this->UrlIndex); }
		if($PM === null) { return $this->error('当前项不存在或已被删除', $this->UrlIndex); }
		
		$this->assign('PM', $PM);
		if($this->isPost())
		{
			if($PMPM->hasChind($PMID))
			{ return $this->error('当前项存在子项，请先删除所有子项', $this->UrlIndex); }
			
			$PMPM->startTrans();
			if($PMPM->where($PMPM->F->ID . "='" . $PMID . "'")->delete() === false)
			{ $PMPM->rollback(); return $this->displayError('连接失败，请稍后再试'); }
			$PMPM->commit(); return $this->success('删除成功', $this->UrlIndex);
		}
		$this->display();
	}
	
	private function getPMPost()
	{
		$PVC = new PVC2();
		$PVC->setModePost();
		$PVC->isString()->validateMust()->add('name');
		$PVC->isInt()->validateMust()->add('parentid');
		$PVC->isNum()->validateMust()->add('startprice');
		$PVC->isNum()->validateMust()->add('unitprice');
		$PVC->isNum()->validateMust()->add('density');
		$PVC->isNum()->validateMust()->add('factor');
		$PVC->isNum()->validateMust()->add('maxlength');
		$PVC->isNum()->validateMust()->add('maxwidth');
		$PVC->isNum()->validateMust()->add('maxheight');

		$PVC->isNum()->validateMust()->add('rationref');
		$PVC->isNum()->validateMust()->add('singlepartfee');
		$PVC->isNum()->validateMust()->add('pricehour');
		$PVC->isNum()->validateMust()->add('fabspeed');
		if(!$PVC->verifyAll()) { return false; }
		return $PVC->ResultArray;
	}
	
	private function getMPID()
	{
		$PVC = new PVC2();
		$PVC->setModeGet();
		$PVC->isInt()->validateMust()->add('id');
		if(!$PVC->verifyAll()) { return false; }
		return $PVC->ResultArray['id'];
	}
	
	private function buildPMData($PMID = null)
	{
		$PMPM = new PrinterMaterialPickerModel();
		$PMPM->create();
		if(isset($PMID)) { $PMPM->{$PMPM->F->ID} = $PMID; }
		return $PMPM;
	}
}
?>