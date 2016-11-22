<?php
/**
 * diy_unit
 *
 */
class DiyNecklaceModel extends Model {
	protected $tableName = 'diy_necklace';
	
	public function getDiyNecklace(){
		$DN=M("diy_necklace")->where("delsign=0")->select();
		//-------------------------------加入链子样式中文名称 start
		$necklaceStyle=L('necklaceStyle');
		$necklaceLength=L('lengthName');
       // var_dump($DN);
//var_dump($value['style']);
        foreach($DN as $key =>$value){
            $DN[$key]['style']=1; //所有链子都为style为1 只有一种链子 O形链子
			$DN[$key]['style_name']=$necklaceStyle[$value['style']];
			$DN[$key]['length_name']=$necklaceLength[$value['length']];
		}
        //var_dump($necklaceStyle);
		//-------------------------------加入链子样式中文名称 end
		return $DN;
	}
	
	
	
	public function getNecklaceByID($id){
		$NL=M("diy_necklace")->where("id=".$id."")->find();
		return $NL;
	}

	public function getNecklaceByPmaid($pma_id){
		$NL=M("diy_necklace")->where("pma_id=".$pma_id."")->select();
		
		//-------------------------------加入链子样式中文名称 start
		$necklaceStyle=L('necklaceStyle');
		$necklaceLength=L('lengthName');
		foreach($NL as $key =>$value){
			$NL[$key]['style_name']=$necklaceStyle[$value['style']];
			$NL[$key]['length_name']=$necklaceLength[$value['length']];
		}
		//-------------------------------加入链子样式中文名称 end
		//var_dump($NL);
		return $NL;
	}
	
	//根据id获得链子的详细描述(大小、长度、样式)
	/*
	 * 此方法很方便的取出链子描述
	 * param int @id 链子的id
	 */
	public function getNecklaceExplainByID($id){
		$NL=M("diy_necklace")->where("id=".$id."")->find();
		$ChainArr=$this->getChainRadioInfo();
		foreach($ChainArr as $key => $value){
			$result.=$ChainArr[$key][$NL[$key]]." ";
		}
		return $result;
	}
	
	

	
	
	//获取链子配置节点content的radio的选项值
	public function getChainRadioInfo(){
		$ChainRadioInfo=M('node_modulecontent')->where("NodeId=123")->select();//123为固定值 临时方案
		foreach($ChainRadioInfo as $key => $value){
			if($value['FieldName']=='style' || $value['FieldName']=='length' || $value['FieldName']=='thickness'){
				$ChainArr[$value['FieldName']]=$this->getValueArr($value['GetValue']);
			}
		}
		
		return $ChainArr;
	}
	/*
	 * 把value的值转为数组
	* param string @str 带';'和'='号的字符串 (例如:"1=珠子链;2=O形链")
	* return Array @result
	*/
	public function getValueArr($str){
	
		if($str){
			$strArr=explode(";",$str);
			foreach($strArr as $key => $value){
				$valueArr=explode("=",$value);
				$result[$valueArr[0]]=$valueArr[1];
			}
		}
		return $result;
	}
	
	
	//得到select的值
	public function getSelectValue($Gendertype_value,$id){
		$MInfo=M('diy_unit')->where("id=".$id."")->select();
		foreach($MInfo as $key => $value){
			if($value['fieldtype']=='SELECT'){
				$SelectArr[$value['unit_name']]=$this->getValueArr($value['unit_value']);
			}
		}
		$result=$SelectArr['Gendertype'][$Gendertype_value];
		return $result;
	}
	
	

	
	
}
?>