<?php
class PrinterModelAuditAction extends CommonAction
{
	public function index()
	{
		$SI = $this->getSearchInfo();
		$SI = $SI ? $SI : array();
		$PMM = new PrinterModelModel();
		$PMList = $PMM->getPMListByStatus($SI);
		if($PMList === false) { return $this->error('数据库连接失败，请稍后再试'); }
		$TotalCount = $PMM->TotalCount;
		load('@.Paging'); 
		$PagingInfo = getPagingInfoAdmin($TotalCount, $SI['page'], $SI['count'], 10, $SI['BaseUrl']);
		$this->assign('PagingInfo', $PagingInfo);
		$this->assign('PMList', $PMList);
		$this->assign('Status_disp', L('PM_STATUS_DISP'));
		$sbuttclass['status']		=$SI['status']===0?"class=btn_in":"class=btn";
		$sbuttclass['nvreify1']	=$SI['nvreify']===1?"class=btn_in":"class=btn";
		$sbuttclass['nvreify2']	=$SI['nvreify']===2?"class=btn_in":"class=btn";
		$sbuttclass['nfix']			=$SI['nfix']==1?"class=btn_in":"class=btn";
		$this->assign('buttonclass',$sbuttclass);
		
		
	  $this->display();
	}
	
	public function audit()
	{
		$YFID = $this->getID();
		if(!$YFID) { return $this->error('页面传值错误'); }
		
		$PMPM = new PrinterMaterialPickerModel();
		if(!$PMPM->IsLoaded) { return $this->error('数据库连接失败，请稍后再试'); }
		
		$PMM = new PrinterModelModel();
		$PModel = $PMM->find($YFID);
		if($PModel === false) { return $this->error('数据库连接失败，请稍后再试'); }
		if($PModel === null) { return $this->error('当前项不存在或已被删除'); }
		
		$PMMM = new PrinterModelMaterialModel();
		$PModelMaterial = $PMMM->getMaterialByYunFileID($YFID);
		if($PModelMaterial === false) { return $this->error('数据库连接失败，请稍后再试'); }
		$MappedPModel = $PMM->parseFieldsMap($PModel);
		$this->assign('Post', $this->mapPrintModelPost($MappedPModel, $PModelMaterial));
		
		if($this->isPost())
		{
			$this->assign('Post',$_POST);
			$AuditPost = $this->getPModelAuditPost();
			if(!$AuditPost) { return $this->displayError('必要信息没有填写完整'); }
			//上传封面
			$CoverInfo = $this->savePModelCover($YFID);
			if($CoverInfo === false) { return $this->displayError('封面上传失败'); }
			if($CoverInfo !== null)
			{ $PModel[$PMM->F->Cover] = preg_replace('|^./|', '/', $CoverInfo['savepath'], 1) . $CoverInfo['savename']; }
			//保存材质
			$PMM->startTrans();
			$IsSaveMaterial = $this->savePrintModelMaterial($YFID, $AuditPost, $PModelMaterial);
			if($IsSaveMaterial === false)
			{ $PMM->rollback(); return $this->displayError('保存失败，请稍后再试'); }
			//保存审核信息
			$PMM = $this->buildPrintModelData($YFID, $AuditPost, $PModel[$PMM->F->Cover], $IsSaveMaterial);
			if($PMM->save() === false) { $PMM->rollback(); return $this->displayError('保存失败，请稍后再试'); }
			$PMM->commit();
			//var_dump($PModel);exit;
			if(!$PModel[$PMM->F->Cover]) { return $this->success('保存成功<br/>请上传封面'); }
			return $this->success('保存成功', U('admin/printer_model_audit/index'));
		}
		$this->assign('SessionID', session_id());
		$this->assign('U_UploadFile', U('/admin/printer_model_audit/uploadfixprintermodel/id/' . $YFID));
		$this->assign('MaterialList', $PMPM->getChildList(0));
		$this->assign('PModel', $PModel);
		$this->assign('PModelMaterial', array_column($PModelMaterial, $PMMM->F->PMaterialID));
		$this->display();
	}
	
	public function uploadFixPrinterModel()
	{
		$YFID = $this->getID();
		if(!$YFID) { return $this->error('页面传值错误'); }
		
		$PMM = new PrinterModelModel();
		$PModel = $PMM->find($YFID);
		if($PModel === false)
		{ echo '{"isSuccess":false, "Message":"数据库连接失败，请稍后再试"}'; return false; }
		if($PModel === null)
		{ echo '{"isSuccess":false, "Message":"当前项不存在或已被删除"}'; return false; }
		
		load('@.YunUploader'); $YU = new YunUploader();
		$YunFile = $YU->uploadOne ( $_FILES['Filedata'] );
		$FileID = $YunFile['yf_id'];
		if(!$FileID)
		{ echo '{"isSuccess":false, "Message":"' . $YU->getLastError() . '"}'; return false; }
		
		$FileData = $YU->getLastFileData();
		$PMM = $this->buildFixPrintModelData($YFID, $FileData);
		if($PMM->save() === false)
		{ echo '{"isSuccess":false, "Message":"数据库连接失败，请售后再试"}'; return false; }
		echo '{"isSuccess":true}';
	}
	
	private function savePModelCover($PMID)
	{
		$CoverFile = $_FILES['modelcover'];
		if(!isset($CoverFile)) { return null; }
		if($CoverFile['size'] == 0) { return null; }
		$SavePath = C('UPLOAD_PAHT.PRINTER_MODEL_COVER') . getSavePathByID($PMID);
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();
		$upload->uploadReplace = true;
		$upload->maxSize = 4194304;
		$upload->allowExts = array('jpg', 'png', 'jpeg');
		$upload->savePath = $SavePath . 'o/';
		$upload->saveRule = $PMID . '';
		//$upload->autoSub = true; $upload->subType = 'custom'; $upload->subDir = getSavePathByID($PID) . 'o/';
		$upload->thumb = true;
		$upload->thumbPath = $SavePath . 's/';
		$upload->thumbPrefix = '640_480_,180_135_,90_67_';
		$upload->thumbMaxWidth = '640,180,90';
		$upload->thumbMaxHeight = '480,135,67';
		$upload->thumbRemoveOrigin = false;
		$CoverInfo = $upload->uploadOne($CoverFile);
		if(!$CoverInfo) { return false; }
		return $CoverInfo[0];
	}
	
	private function getPModelAuditPost()
	{
		$PVC = new PVC2(); $PVC->setModePost();
		$PVC->isNum()->validateMust()->add('originallength');
		$PVC->isNum()->validateMust()->add('originalwidth');
		$PVC->isNum()->validateMust()->add('originalheight');
		$PVC->isNum()->validateMust()->add('length');
		$PVC->isNum()->validateMust()->add('width');
		$PVC->isNum()->validateMust()->add('height');
		$PVC->isNum()->validateMust()->add('volume');
		$PVC->isNum()->validateMust()->add('ratio');
		$PVC->isArray()->validateNotNull()->add('materials');
		$PVC->isint()->validateMust()->add('needverify');
		$PVC->isString()->validateNotNull()->add('needfix');
		$PVC->isString()->validateNotNull()->add('needmaterial');
		$PVC->isInt()->validateMust()->In(array(1, 3))->add('status');
		if(!$PVC->verifyAll()) { return false; }
		return $PVC->ResultArray;
	}
	
	private function getID()
	{
		$PVC = new PVC2(); $PVC->setModeGet();
		$PVC->isInt()->validateMust()->add('id');
		if(!$PVC->verifyAll()) { return false; }
		return $PVC->ResultArray['id'];
	}
	
	private function getSearchInfo($Mode = 'get')
	{
		$PVC = new PVC2($Mode);
		$PVC->isInt()->validateNotNull()->add('status');
		$PVC->isInt()->validateNotNull()->add('nvreify');
		$PVC->isInt()->validateNotNull()->add('nfix');
		$PVC->isInt()->validateNotNull()->add('nmaterial');
		$PVC->isInt()->validateNotNull()->DefVal(1)->add('page');
		$PVC->isInt()->validateNotNull()->DefVal(20)->add('count');
		if(!$PVC->verifyAll()) { return false; }
		$ArrBase = array('status', 'nvreify', 'nfix', 'nmaterial');
		$BaseUrl = '';
		foreach ($ArrBase as $Base)
		{
			if(isset($PVC->ResultArray[$Base]))
			{
				//var_dump($PVC->ResultArray[$Base]);
				$BaseUrl .= !$BaseUrl ? '?' : '&';
				$BaseUrl .= $Base . '=' . $PVC->ResultArray[$Base];
			}
		}
		$PVC->ResultArray['BaseUrl'] = U('admin/printer_model_audit/index' . $BaseUrl);
		return $PVC->ResultArray;
	}
	
	private function mapPrintModelPost($MappedPModel, $PModelMaterial)
	{
		$MappedPModel['materials'] = array_column($PModelMaterial, 'pma_id');
		return $MappedPModel;
	}
	
	private function buildPrintModelData($YFID, $AuditPost, $Cover, $IsSaveMaterial)
	{
		$PMM = new PrinterModelModel();
		$PMM->create();
		$PMM->{$PMM->F->ID} = $YFID;
		$PMM->{$PMM->F->LastUpdate} = get_now();
		$PMM->{$PMM->F->LastUpdateTime} = time();
		if($IsSaveMaterial === true) { $PMM->{$PMM->F->LastMaterial} = time(); }
		if($PMM->{$PMM->F->Status} == 3) { return $PMM; }
		
		if(isset($AuditPost['needverify']))
		{ $PMM->{$PMM->F->NeedVerify} = $AuditPost['needverify']; }
		$PMM->{$PMM->F->NeedFix} = isset($AuditPost['needfix']) ? 1 : 0;
		$PMM->{$PMM->F->NeedMaterial} = isset($AuditPost['needmaterial']) ? 1 : 0;
		
		if($Cover) { $PMM->{$PMM->F->Cover} = $Cover; }
		else
		{
			$PMM->{$PMM->F->Status} = 0;
			$PMM->{$PMM->F->NeedVerify} = 1;
		}
		if($PMM->{$PMM->F->NeedVerify} != 0 || $PMM->{$PMM->F->NeedFix} != 0)
		{ $PMM->{$PMM->F->Status} = 2; }
		
		return $PMM;
	}
	
	private function buildFixPrintModelData($YFID, $FileData)
	{
		$YFM_F = new DBF_YunFiles();
		$PMM = new PrinterModelModel();
		$PMM->{$PMM->F->ID} = $YFID;
		$PMM->{$PMM->F->LastUpdate} = get_now();
		$PMM->{$PMM->F->LastUpdateTime} = time();
		$PMM->{$PMM->F->FilePath} = $FileData[$YFM_F->Path];
		$PMM->{$PMM->F->FileName} = $FileData[$YFM_F->Name];
		$PMM->{$PMM->F->FileExt} = $FileData[$YFM_F->Ext];
		$PMM->{$PMM->F->FileMD5} = $FileData[$YFM_F->MD5hex];
		$PMM->{$PMM->F->FileSHA1} = $FileData[$YFM_F->SHA1hex];
		return $PMM;
	}
	
	private function savePrintModelMaterial($YFID, $AuditPost, $PModelMaterial)
	{
		$MaterialList = $AuditPost['materials'];
		if(isset($MaterialList))
		{ if(array_diff($MaterialList, $PModelMaterial) === array()) { return true; } }
		$PMPM = new PrinterModelMaterialModel();
		if($PMPM->where($PMPM->F->YunFileID . "='" . $YFID . "'")->delete() === false)
		{ return false; }
		foreach($MaterialList as $Material)
		{
			$PMPM->{$PMPM->F->YunFileID} = $YFID;
			$PMPM->{$PMPM->F->PMaterialID} = $Material;
			if($PMPM->add() === false) { return false; }
		}
		return true;
	}
}
?>