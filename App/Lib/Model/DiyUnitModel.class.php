<?php
/**
 * diy_unit
 *
 */
class DiyUnitModel extends Model {
	protected $tableName = 'diy_unit';
	protected $fields = array (
			'id',
			'cid',
			'cate',
			'unit_name',
			'unit_showname',
			'unit_value',
			'unit_price',
			'delsign',
			'ctime',
			'_pk' => 'id',
			'_autoinc' => TRUE
	);
	
	public function getDiyUnit(){
		$sql="select TDC.cate_name,TDC.cid from tdf_diy_unit as TDU ";
		$sql.="Left Join tdf_diy_cate as TDC On TDU.cid=TDC.cid ";
		$sql.="where TDU.delsign<>1 order by TDU.sort,TDC.cate_group ";
		$DU=M("diy_unit");
		$duinfo=$DU->query($sql);
		//var_dump($duinfo);
	}
	
	
	public function getUnitByUserDiy($cid,$udinfo){
		$UD=M("diy_unit")->where("cid=".$cid)->order("sort")->select();
		$sql = "select TPM.pma_id,TPM.pma_name as TPM_name,TPMP.pma_name as TPMP_name,TPM.pma_unitprice,TPM.pma_density,TPM.pma_startprice,TPM.pma_diy_formula_s,TPM.pma_diy_formula_b from tdf_printer_material as TPM ";
		$sql .= "Left Join tdf_printer_material as TPMP ON TPMP.pma_id=TPM.pma_parentid ";
		$sql .= "where TPM.pma_type=1 order by TPM.pma_weight ASC ";
		$mcate = M ( "printer_material" )->query ( $sql ); // 打印材料数组，必须
		// ----------------------------------打印材料数组V
		$DNM=new DiyNecklaceModel();
		foreach ( $mcate as $keyM => $valueM ){
			$materialArr [$valueM ['pma_id']] = $valueM ['TPM_name'];
		}//材料数组
		foreach($UD as $key =>$value){
			//var_dump($value);
			//$udinfo[$value['unit_name']]=$value['unit_showname'].":".$diyinfo[$value['id']];
			if($value ['unit_name'] == "Textvalue"){//输入的主体字符
				$productLog .= $value ['unit_showname'] . ":" . $udinfo [$value ['id']] . " ";
			}elseif($value ['unit_name'] == "Material") {
				$productLog .= "材料:" . $materialArr [$udinfo [$value ['id']]] . " ";
			}elseif($value ['unit_name'] == "Chaintype"){//链子样式
				//$productLog .= $value ['unit_showname'] . ":" .$DNM->getNecklaceExplainByID($udinfo [$value ['id']]) . " ";
			}elseif($value ['unit_name'] == "Gendertype"){//链子男女款式
				//$productLog .= $value ['unit_showname'] . ":" .$DNM->getSelectValue($udinfo [$value ['id']],$value ['id']) . " ";
			}else{
				//$productLog .= $value ['unit_showname'] . ":" . $udinfo [$value ['id']] . " ";
			}
		}
		return $productLog;
	}

    /*
 * 根据cid获取diyunit控件数据
 */
    public function getDiyUnitByCid($cid){
        $sql="select fieldtype,unit_name,unit_showname,unit_default,fieldtitle,fieldmaxlength,unit_value from tdf_diy_unit where (delsign=0 and ishidden=0 and cid=$cid) or (delsign=0 and fieldtype='NECKLACE' and cid=$cid) order by sort ASC ";
        $DU=M("diy_unit");
        $diyunit=$DU->query($sql);
        foreach($diyunit as $key=>$value){
            if($value['fieldtype']=='NECKLACE'){
                print_r($neckInfo);
                $diyunit[$key]['unit_default']=$this->getNecklacePrice($value['unit_default']);
            }
        }
        return $diyunit;
    }

    /**
     * APP中的链子根据链子默认值，用价格替换链子ID并返回
     */
    public function getNecklacePrice($unit_default){
        $necklaceArr=M('diy_necklace')->field('id,price')->where("delsign=0")->select();
        foreach($necklaceArr as $k => $v){
            $neckInfo[$v['id']]=$v['price'];
        }
        $unitDefaultArr=explode(';',$unit_default);
        foreach($unitDefaultArr as $key => $value){
            $defaultArr=explode(':',$value);
                $result.=$defaultArr[0].":".$neckInfo[$defaultArr[1]].";";
        }
        return $result;
    }

    /**根据udinfo数组来返回整个表单数据值，返回用户diy方案数据
     * @param $udid user_di
     * @return mixed
     */
    function get_udinfo_all($udid){//根据用户diy方案id获取整个表单数据值
        $udinfo=M("user_diy")->where("id=".$udid)->find();
        $cid=$udinfo['cid'];
        $diy_unit_info=unserialize($udinfo['diy_unit_info']);
        $UD=M("diy_unit")->where("cid=".$cid)->order("sort")->select();
        foreach($UD as $key =>$value){
            $udinfo[$value['unit_name']]=$diy_unit_info[$value['id']];
        }
        return $udinfo;
    }


 /*
* 根据cid获取diyunit所有(包括隐藏)控件数据 ,不包含用户diy方案数据
*/
    public function getDiyUnitAllByCid($cid){
        $sql="select fieldtype,unit_name,unit_showname,unit_default,fieldtitle,fieldmaxlength,unit_value,id from tdf_diy_unit where delsign=0 and cid=$cid order by sort ASC ";
        $DU=M("diy_unit");
        $diyunit=$DU->query($sql);
        return $diyunit;
    }







}
?>