<?php

class MemProductTagsLoaderModel extends Model
{

	/**
	 * @var DBF
	 */
	protected $DBF;

	/**
	 * @var DBF_ProductTags
	 */
	public $F;

	function __construct()
	{
		$this->DBF = new DBF();
		$this->F = $this->DBF->ProductTags;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields();
		// $this->_map = $this->F->getMappedFields();
		parent::__construct();
	}

	public function getTags() { return $this->field($this->F->ID . ',' . $this->F->Name)->select(); }
}
?>