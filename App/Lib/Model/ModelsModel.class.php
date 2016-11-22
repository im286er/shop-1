<?php
class ModelsModel extends Model {
	protected $_map = array (
			'geometry'		 	=> 'pm_geometry',
			'mash' 					=> 'pm_mash',
			'vertices' 			=> 'pm_vertices',
			'is_texture' 		=> 'pm_istexture',
			'is_materials' 	=> 'pm_ismaterials',
			'is_animation' 	=> 'pm_isanimation',
			'is_rigged' 		=> 'pm_isrigged',
			'is_uvlayout' 	=> 'pm_isuvlayout',
			'is_rendered' 	=> 'pm_isrendered',
			'modelformats' 	=> 'pm_modelformats',
			'isoverview' 		=> 'pm_isoverview',
			'isvr' 					=> 'pm_isvr',
			'isar' 					=> 'pm_isar',
			'arcode' 				=> 'pm_arcode',
			'unwrappeduvs' 	=> 'pm_unwrappeduvs',
			'rvfy' 					=> 'pm_rvfy',
			'arcode' 				=> 'pm_arcode',
			'pm_shapewaysid'=> 'pm_shapewaysid',
			'is_morenode'		=> 'pm_ismorenode',
			'time' 					=> 'pm_ctime'				
	);
	
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
		//$this->_map = $this->F->getMappedFields();
		parent::__construct();
	}
	
	// Zerock @2013/03/04
	public function getProductModelByID($PID)
	{
		$ProductM = $this->where("p_id='" . $PID . "'")->select();
		return $ProductM !== false ? $ProductM ? $ProductM[0] : null : false;
	}
	

	
	
	
	
	
	
}
?>