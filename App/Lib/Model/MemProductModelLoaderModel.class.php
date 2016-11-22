<?php

class MemProductModelLoaderModel extends Model
{

	/**
	 * @var DBF
	 */
	protected $DBF;

	/**
	 * @var DBF_ProductModel
	 */
	public $F;

	function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->ProductModel;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		// $this->_map = $this->F->getMappedFields();
		parent::__construct();
	}

	public function getFilters()
	{
		return $this->field(
							$this->F->ProductID . ',' . $this->F->IsTexture . ',' .
							$this->F->IsMaterials . ',' . $this->F->IsAnimation . ',' .
							$this->F->IsRigged . ',' . $this->F->IsUVLayout)->select();
	}
}
?>