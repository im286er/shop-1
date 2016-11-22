<?php
class WhereBuilderAction extends Action
{
	function index()
	{
		load('@.WhereBuilder');
		$WB = new WhereBuilder();
		$PM = new ProductModel();

		$Where = $WB->addEq($PM->F->ProductType, '1')
								->addRange($PM->F->CreateDate, '2013-1-1', null)
								->__AND()
									->addRange($PM->F->Cate_1, 1, 1050)
									->__AND()
										->addIn($PM->F->Creater, array(400))
										->_OR_()
										->addIn($PM->F->Creater, array(444))
									->__End()
								->__End()
								->getWhere();
		var_dump($Where);
		echo '<br/><br/>';
		var_dump($PM->where($Where)->limit(200)->select());
		echo '<br/><br/>';
		var_dump($PM->getLastSql());
		echo '<br/><br/>';
		
		/*
		$WB->reset();
		$Where = $WB->getWhere();
		var_dump($Where);
		echo '<br/><br/>';
		var_dump($PM->where($Where)->limit(200)->select());
		echo '<br/><br/>';
		var_dump($PM->getLastSql());
		*/
	}
}