<?php

class MemProductTagsIndexLoaderModel extends Model
{

	/**
	 * @var DBF
	 */
	protected $DBF;

	/**
	 * @var DBF_ProductTagsIndex
	 */
	public $F;

	function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->ProductTagsIndex;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		// $this->_map = $this->F->getMappedFields();
		parent::__construct();
	}

	public function getTagsIndex() { return $this->select(); }
}
?>