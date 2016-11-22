<?php

class ProductSourceModel extends Model
{

	protected $tableName = 'product_source';

	protected $fields = array('ps_id', 'ps_name', 'ps_url', '_pk' => 'ps_id');

	public function getSourceList()
	{
		return $this->select();
	}

	public function getSourceOptions($SelectedOption)
	{
		$SelectedOption = $SelectedOption ? $SelectedOption : 0;
		$DBF = new DBF();
		$Options = '<option value="0">请选择来源...</option>';
		$List = $this->getSourceList();
		if(!$List)
		{return $Options;}
		
		foreach($List as $Item)
		{
			$Options .= '<option value="' . $Item[$DBF->ProductSource->ID];
			if($SelectedOption != $Item[$DBF->ProductSource->ID])
			{ $Options .= '">'; }
			else { $Options .= '" selected="selected">'; }
			$Options .= $Item[$DBF->ProductSource->Name] . '</option>';
		}
		return $Options;
	}
}
?>