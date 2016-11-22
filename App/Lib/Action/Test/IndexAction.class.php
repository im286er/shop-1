<?php
class IndexAction extends Action
{
	public function index()
	{
		echo 'Test Index';
		echo '<br/>';
		$arr = array(
				'name' => 'Zerock', 
				'age' => '21', 
				'createdate' => '2012\12\06', 
				'isadmin' => 1, 
				'website' => 'http://zzexy.elacg.com', 
				'email' => 'no.zerock@hotmail.com', 
				'cash' => '12.45', 
				'shit' => '');
		
		$pvc = new PVC2();
		$pvc->setModeArray()->SourceArray = $arr;
		echo '$pvc->isString()->validateExists()->Length(6, 20)->verifyKey(\'name\'): ';
		var_dump($pvc->isString()->validateExists()->Length(6, 20)->verifyKey('name'));
		echo '<br/>';
		echo '$pvc->isInt()->validateExists()->Between(18, null)->verifyKey(\'age\'): ';
		var_dump($pvc->isInt()->validateExists()->Between(18, null)->verifyKey('age'));
		echo '<br/>';
		
		echo '$pvc->isInt()->validateExists()->Between(10, 200)->verifyValue(\'age\')): ';
		var_dump($pvc->isInt()->validateExists()->Between(10, 200)->verifyValue(182));
		echo '<br/>';
		
		$pvc->validateMust();
		$pvc->isInt()->Error('shit')->add('cash');
		$pvc->isDate()->add('createdate');
		$pvc->isIntBool()->add('isadmin');
		$pvc->isIntBool()->add('age');
		$pvc->isEMail()->add('email');
		$pvc->isString()->add('shit');
		$pvc->isString()->add('shit1');
		$pvc->verifyAll();
		
		var_dump($pvc->Error);
		echo '<br/>';
		var_dump($pvc->ResultArray);
		
		/*
		$val1 = $_GET['sb'];
		echo 'val1 =  $_GET[\'sb\'];<br/>';
		echo 'isset: ';
		var_dump(isset($val1));
		echo '<br/>';
		echo 'is_null: ' ;
		var_dump(is_null($val1));
		echo '<br/>';
		echo 'empty: ';
		var_dump(empty($val1)) ;
		echo '<br/>';
		echo 'array_key_exists: ';
		var_dump(array_key_exists('val1', get_defined_vars()));
		echo '<br/>';
		echo '<br/>';
		
		$val2 = $_GET['val'];
		echo 'val2 =  $_GET[\'val\'];<br/>';
		echo 'isset: ';
		var_dump(isset($val2));
		echo '<br/>';
		echo 'is_null: ' ;
		var_dump(is_null($val2));
		echo '<br/>';
		echo 'empty: ';
		var_dump(empty($val2)) ;
		echo '<br/>';
		echo 'array_key_exists: ';
		var_dump(array_key_exists('val2', get_defined_vars()));
		echo '<br/>';
		echo '<br/>';
		
		$val3 = null;
		echo 'val3 = null;<br/>';
		echo 'isset: ';
		var_dump(isset($val3));
		echo '<br/>';
		echo 'is_null: ' ;
		var_dump(is_null($val3));
		echo '<br/>';
		echo 'empty: ';
		var_dump(empty($val3)) ;
		echo '<br/>';
		echo 'array_key_exists: ';
		var_dump(array_key_exists('val3', get_defined_vars()));
		echo '<br/>';
		echo '<br/>';
		
		$val4 = '';
		echo 'val4 = \'\';<br/>';
		echo 'isset: ';
		var_dump(isset($val4));
		echo '<br/>';
		echo 'is_null: ' ;
		var_dump(is_null($val4));
		echo '<br/>';
		echo 'empty: ';
		var_dump(empty($val4)) ;
		echo '<br/>';
		echo 'array_key_exists: ';
		var_dump(array_key_exists('val4', get_defined_vars()));
		echo '<br/>';
		echo '<br/>';
		
		echo 'val5 undefine;<br/>';
		echo 'isset: ';
		var_dump(isset($val5));
		echo '<br/>';
		echo 'is_null: ' ;
		var_dump(is_null($val5));
		echo '<br/>';
		echo 'empty: ';
		var_dump(empty($val5)) ;
		echo '<br/>';
		echo 'array_key_exists: ';
		var_dump(array_key_exists('val5', get_defined_vars()));
		echo '<br/>';
		echo '<br/>';
		
		var_dump(get_defined_vars());
		*/
	}
	
	function cate()
	{
		$CM = new CategoryPickerModel();
		$this->display();
	}
}