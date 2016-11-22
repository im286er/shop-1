<?php
/*
* WhereBuilder v1.1
* Zerock 2014-02
*
* --------------------------------------------------
* Log
* --------------------------------------------------
* v1.0.0 | 2014-01-14
* - Job done
*
* v1.0.1 | 2014-01-26
* - 添加了5个层级连接函数（__AND，__OR，_AND_，_OR_，End）
* - 支持了查询语句的层级
*
* v1.0.2 | 2014-01-27
* - Bug fix
*
* v1.1.0 | 2014-02-17
* - 添加了一个用于日期区间查询的函数addRangeDate
* - 去除了In语句中可能重复的值
* 
* v1.1.1 | 2014-02-18
* - 添加了1个层级函数，用于标识非条件的层级开始（Start）
*/

class WhereBuilder
{
	private $Eq			= array();
	private $NotEq	= array();
	private $In			= array();
	private $NotIn	= array();
	private $Range	= array();
	private $JoinKey	= array();
	
	private $WhereStr = '';
	private $LvCount = 0;
	
	public function clean()
	{
		$this->Eq			= array();
		$this->NotEq	= array();
		$this->In			= array();
		$this->NotIn	= array();
		$this->Range	= array();
		$this->JoinKey	= array();
		return $this;
	}
	
	public function reset()
	{ $this->clean()->WhereStr = ''; $this->LvCount = 0; return $this; }
	
	public function addEq($Key, $Value)
	{ $this->Eq = $this->addFilter($this->Eq, $Key, $Value); return $this; }
	public function addNotEq($Key, $Value)
	{ $this->NotEq = $this->addFilter($this->NotEq, $Key, $Value); return $this; }
	public function addIn($Key, $Value)
	{
		$Value = array_unique($Value);
		$this->In = $this->addFilter($this->In, $Key, $Value); return $this; 
	}
	public function addNotIn($Key, $Value)
	{
		$Value = array_unique($Value);
		$this->NotIn = $this->addFilter($this->NotIn, $Key, $Value); return $this;
	}
	
	public function addRange($Key, $Min, $Max, $LEq = false, $REq = false)
	{
		if(!isset($Key)) { return $this; }
		if(!isset($Min) && !isset($Max)) { return $this; }
		$Range = array();
		if(isset($Min)) { $Range['min'] = $Min; }
		if(isset($Max)) { $Range['max'] = $Max; }
		$Range['leq'] = (isset($LEq) && $LEq == true);
		$Range['req'] = (isset($REq) && $REq == true);
		if(count($Range) == 0) { return $this; }
		$this->Range[$Key][] = $Range;
		return $this;
	}
	
	public function addRangeDate($Key, $Min, $Max, $LEq = false, $REq = false)
	{
		if(isset($Min)) { $Date1 = strtotime($Min); }
		if(isset($Max)) { $Date2 = strtotime($Max);}
		if(isset($Date1, $Date2))
		{
			if($Date1 > $Date2)
			{ $Temp = $Date1; $Date1 = $Date2; $Date2 = $Temp; }
		}
		else { return $this; }
		
		if(isset($Date1)) { $Date1 = date('Y-m-d H:i:s', $Date1); }
		if(isset($Date2))
		{
			$Date2 = mktime(0, 0, date('s', $Date2) - 1, date('m', $Date2), 
							date('d', $Date2) + 1, date('Y', $Date2));
			$Date2 = date('Y-m-d H:i:s', $Date2);
		}
		return $this->addRange($Key, $Date1, $Date2, $LEq, $REq);
	}
	
	public function addJoinKey($Key1, $Key2)
	{
		if(!isset($Key1, $Key2)) { return $this; }
		$this->JoinKey[$Key1] = $Key2;
		return $this;
	}
	
	public function _AND_()
	{
		$this->WhereStr .= $this->buildWhere() . ' AND ';
		return $this->clean();
	}
	
	public function _OR_()
	{
		$this->WhereStr .= $this->buildWhere() . ' OR ';
		return $this->clean();
	}
	
	public function __Start()
	{
		$this->WhereStr .= $this->buildWhere() . ' (';
		$this->LvCount++;
		return $this;
	}
	
	public function __AND()
	{
		$this->WhereStr .= $this->_AND_() . '(';
		$this->LvCount++; 
		return $this;
	}
	
	public function __OR()
	{
		$this->WhereStr .= $this->_OR_() . '(';
		$this->LvCount++;
		return $this;
	}
	
	public function __End()
	{
		$this->WhereStr .= $this->buildWhere() . ')';
		$this->clean()->LvCount--;
		return $this;
	}
	
	public function getWhere()
	{
		if($this->LvCount != 0)	{ throw new Exception('WhereBuild error, Lv Stack not empty.'); }
		$this->WhereStr .= $this->buildWhere();
		$this->clean();
		return $this->WhereStr;
	}
	
	private function addFilter($Filter, $Key, $Value)
	{
		if(!isset($Key, $Value)) { return $Filter; }
		$NewFilter = is_array($Value) ? $Value : array($Value);
		$Filter[$Key] = isset($Filter[$Key]) ? array_merge($Filter[$Key], $NewFilter) : $NewFilter;
		return $Filter;
	}
	
	private function buildEq()
	{
		$Eq = array();
		foreach($this->Eq as $Key => $Values)
		{
			foreach($Values as $Value)
			{ $Eq[] = $Key . "='" . $Value . "'"; }
		}
		return $Eq ? implode(' AND ', $Eq) : null;
	}
	
	private function buildNotEq()
	{
		$NotEq = array();
		foreach($this->NotEq as $Key => $Values)
		{
			foreach($Values as $Value)
			{ $NotEq[] = $Key . "<>'" . $Value . "'"; }
		}
		return $NotEq ? implode(' AND ', $NotEq) : null;
	}
	
	private function buildIn()
	{
		$In = array();
		foreach($this->In as $Key => $Values)
		{
			$Temp = array();
			foreach($Values as $Value)
			{ $Temp[] = "'" . $Value . "'"; }
			$In[] = $Key . ' IN(' . implode(',', $Temp) . ')';
		}
		return $In ? implode(' AND ', $In) : null;
	}
	
	private function buildNotIn()
	{
		$NotIn = array();
		foreach($this->NotIn as $Key => $Values)
		{
			$Temp = array();
			foreach($Values as $Value)
			{ $Temp[] = "'" . $Value . "'"; }
			$NotIn[] = $Key . ' NOT IN(' . implode(',', $Temp) . ')';
		}
		return $NotIn ? implode(' AND ', $NotIn) : null;
	}
	
	private function buildRange()
	{
		$Range = array();
		foreach($this->Range as $Key=>$Ranges)
		{
			$Temp = array();
			foreach($Ranges as $R)
			{
				$LS = $R['leq'] ? ' >=' : ' >';
				$RS = $R['req'] ? ' <=' : ' <';
				if(isset($R['min'], $R['max']))
				{
					$Temp[] = $Key . $LS . "'" . $R['min'] . "' AND " . $Key . $RS . "'" . $R['max'] . "'";
					continue;
				}
				if(isset($R['min']))
				{ $Temp[] = $Key . $LS . "'" . $R['min'] . "'"; }
				else
				{ $Temp[] = $Key . $RS . "'" . $R['max'] . "'"; }
			}
			$Range[] = implode(' OR ', $Temp);
		}
		return $Range ? '(' . implode(' AND ', $Range) . ')' : null;
	}
	
	private function buildJoinKey()
	{
		$JoinKey = array();
		foreach($this->JoinKey as $Key1 => $Key2)
		{
			$JoinKey[] = $Key1 . '=' . $Key2;
		}
		return $JoinKey ? implode(' AND ', $JoinKey) : null;
	}

	private function buildWhere()
	{
		$Where = array(
				$this->buildJoinKey(),
				$this->buildEq(),
				$this->buildNotEq(),
				$this->buildIn(),
				$this->buildNotIn(),
				$this->buildRange()
		);
		$i = 0;
		foreach($Where as $W)
		{ if(!isset($W)) { unset($Where[$i]); } $i++; }
		return implode(' AND ', $Where);
	}
}