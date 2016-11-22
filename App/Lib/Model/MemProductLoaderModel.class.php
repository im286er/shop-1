<?php

class MemProductLoaderModel extends Model
{

	/**
	 * @var DBF
	 */
	protected $DBF;

	/**
	 * @var DBF_Product
	 */
	public $F;

	function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->Product;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		// $this->_map = $this->F->getMappedFields();
		parent::__construct();
	}

	public function getCate()
	{ return $this->field($this->F->ID . ',' . $this->F->Cate_1 . ',' . $this->F->Cate_2)->select(); }
	
	public function getCreater()
	{ return $this->field($this->F->ID . ',' . $this->F->Creater)->select(); }
}
?>