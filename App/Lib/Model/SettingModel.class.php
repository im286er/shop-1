<?php

class SettingModel extends Model
{
	/**
	 * @var DBF
	 */
	protected $DBF;
	protected $trueTableName = 'tdf_setting';
	protected $fields = array('id','attribute', 'value','comment','ctime');

	function getAllSetting()
	{
		$Settings = $this->select();
		if($Settings)
		{return trans_pk_to_key($Settings, 'attribute');}
		return $Settings;
	}

	function setAttr($Attrs)
	{
		$this->startTrans();
		foreach($Attrs as $key => $val)
		{
			$data = array('value' => $val);
			if($this->where("attribute='" . $key . "'")->data($data)->save() === false)
			{
				$this->rollback();
				return false;
			}
		}
		$this->commit();
		return true;
	}
	
	function getIdIndex($attribute='idindex'){
		$IND=M('setting');
		$result=$IND->where("attribute='".$attribute."'")->find();
        //var_dump($result);
		return $result;
	}
	
	function getIndexuid(){
		$IND=M('setting');
		$result=$IND->where('attribute="idindex"')->find();
		return $result['index_uid'];
	}

    public function getProductsByID($WhereIn){
        $PM = new ProductModel();
        $sql="select p_id,p_producttype,p_price,p_name,p_cover,p_views_disp,p_zans,p_diy_cate_cid from tdf_product where p_id in (" . $WhereIn . ")  order by instr('".$WhereIn."',p_id ) ";
        $Result=$PM->query($sql);
        //var_dump($PM->getlastsql());
        return $Result;
    }
	
	
	
	
	
}