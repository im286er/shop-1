<?php

class NodeModel extends Model {


	public function delSignNode($id) {
    	if($id){
    		$NM = M("node")->where("id=".$id)->setField("DelSign",1);
    		$NMC = M("node_modulecontent")->where("NodeId=".$id)->setField("DelSign",1); //tdf_node_modulecontent表
    		$NML = M("node_modulecontentlist")->where("NodeId=".$id)->setField("DelSign",1); //tdf_node_modulecontentlist表
    		$NMF = M("node_modulefunction")->where("NodeId=".$id)->setField("DelSign",1); //tdf_node_modulefunction表
    		$result=$NM?array('status'=>2,'info'=>'已删除'):array('status'=>0,'info'=>'删除失败');
    	}
    	return $result;
    }

   

}

?>
