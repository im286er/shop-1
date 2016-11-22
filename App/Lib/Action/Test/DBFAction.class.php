<?php

class DBFAction extends Action
{

	function createDBF()
	{
		$M = new Model();
		$Tables = $M->query('show tables');
		
		
		$ClassList = array();
		
		foreach($Tables as $Table)
		{
			$TableName = array_values($Table);
			$TableName = $TableName[0];
			$Fields = $M->query('SHOW COLUMNS FROM ' . $TableName);
			if($Fields === false) { echo '//Can not read Table "' . $TableName . '"'; continue; }
			
			//echo '//Table : ' . $TableName . '<br/>';
			
			$ClassName = 'DBF_' . $this->getNoPreExtName($TableName);
			$ClassList[] = $this->getNoPreExtName($TableName);
			$Expand = '';
			echo 'class ' . $ClassName . ' extends DBFCreater<br />';
			echo '{<br />';
			echo 'public $_Table' . " = '" . $TableName . "';<br/>";
print <<<singleton
			private static \$instance = null;
			public static function construct()
			{
				if(!(self::\$instance instanceof self)) { self::\$instance = new self(); }
				return self::\$instance;
			}
singleton;

			foreach ($Fields as $Field)
			{
				echo 'public $' . $this->getNoPreExtName($Field['Field']) . " = '" . $Field['Field'] . "';";
				echo '<br />';
				if($Field['Key'] == 'PRI') { $Expand .= "public \$_pk = '" . $Field['Field'] . "';<br/>"; }
				if(stristr($Field['Extra'],'auto_increment')) { $Expand .= "public \$_autoinc = true;<br/>"; }
			}
			echo $Expand;
			
			echo '}<br />';
			echo '<br/>';
		}
		
		if(!$ClassList) { return; }
		
		echo '<br/>//------------------------------<br/>';
		echo 'class DBF<br />';
		echo '{<br />';
		foreach ($ClassList as $Class)
		{
			echo '/**<br/>';
			echo ' * @var DBF_' . $Class . '<br/>';
			echo '*/<br/>';
			echo 'public $' . $Class . ';<br/>';
			echo '<br/>';
		}
		
		echo 'function __construct()<br/>';
		echo '{<br />';
		foreach ($ClassList as $Class)
		{
			echo '$this->' . $Class . ' = new DBF_' . $Class . ';<br/>';
		}
		
		echo '}<br />';
		echo '}<br />';
		echo '<br/>';
	}

	public function singleton()
	{
		load('@.TestDBF');
		$U1 = DBF_Users1::construct();
		$U2 = DBF_Users2::construct();
		echo 'U1: ';
		var_dump($U1->getName());
		echo '<br/>';
		echo 'U2: ';
		var_dump($U2->getName());
		echo '<br/><br/>';
		
		$U1::$aaa = 111;
		$U2::$aaa = 222;
		
		echo 'U1: ';
		var_dump($U1->getName());
		echo '<br/>';
		echo 'U2: ';
		var_dump($U2->getName());
		echo '<br/><br/>';
		
		$U1 = DBF_Users1::construct();
		$U2 = DBF_Users2::construct();
		echo 'U1: ';
		var_dump($U1->getName());
		echo '<br/>';
		echo 'U2: ';
		var_dump($U2->getName());
		echo '<br/><br/>';
	}
	
	public function dispPrefix()
	{
		load('@.TestDBF');
		$U1 = DBF_Users1::construct();
		var_dump($U1);
		echo '<br/><br/>';
		$U1->dispPrefix(true);
		var_dump($U1);
		echo '<br/><br/>';
		$U1->dispPrefix(false);
		var_dump($U1);
		echo '<br/><br/>';
	}
	
	private function getNoPreExtName($Name)
	{
		return ucfirst(strtolower(preg_replace('|^[0-9a-zA-Z]+_|', '', $Name)));
	}
}