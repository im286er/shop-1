<?php
class PVC2 {
	private $_ValueMode = 'post';
	private $_DataType = null;
	private $_ExpandMode = array ();
	private $_Validate = null;
	private $_DefaultValue = null;
	private $_Error = '';
	private $_StrictMode = false;
	private $_TempResult = null;
	public $Selector = array ();
	public $SourceArray = array ();
	public $ResultArray = array ();
	public $Error = array ();
	function __construct($ValueMode = 'post') {
		$this->_ValueMode = $ValueMode;
	}
	
	// 取值方式(Value Mode)
	/*
	 * 使用Post方式取值 @return PVC2
	 */
	public function setModePost() {
		$this->_ValueMode = PVC2EnumObj::$VM_Post;
		return $this;
	}
	/**
	 * 使用Get方式取值
	 *
	 * @return PVC2
	 */
	public function setModeGet() {
		$this->_ValueMode = PVC2EnumObj::$VM_Get;
		return $this;
	}
	/**
	 * 在Session中取值
	 *
	 * @return PVC2
	 */
	public function setModeSession() {
		$this->_ValueMode = PVC2EnumObj::$VM_Session;
		return $this;
	}
	/**
	 * 在Cookie中取值
	 *
	 * @return PVC2
	 */
	public function setModeCookie() {
		$this->_ValueMode = PVC2EnumObj::$VM_Cookie;
		return $this;
	}
	/**
	 * 在当前对象的_SourceArray中取值
	 *
	 * @return PVC2
	 */
	public function setModeArray() {
		$this->_ValueMode = PVC2EnumObj::$VM_Array;
		return $this;
	}
	
	// 验证值的类型(Data Type)
	/**
	 * 整数类型
	 *
	 * @return PVC2
	 */
	public function isInt() {
		$this->_DataType = PVC2EnumObj::$DT_Int;
		return $this;
	}
	/**
	 * 数字类型
	 *
	 * @return PVC2
	 */
	public function isNum() {
		$this->_DataType = PVC2EnumObj::$DT_Num;
		return $this;
	}
	/**
	 * 字符串类型
	 *
	 * @return PVC2
	 */
	public function isString() {
		$this->_DataType = PVC2EnumObj::$DT_String;
		return $this;
	}
	/**
	 * 数组类型
	 *
	 * @return PVC2
	 */
	public function isArray() {
		$this->_DataType = PVC2EnumObj::$DT_Array;
		return $this;
	}
	/**
	 * 日期格式字符串类型
	 *
	 * @return PVC2
	 */
	public function isDate() {
		$this->_DataType = PVC2EnumObj::$DT_Date;
		return $this;
	}
	/**
	 * 时间格式字符串类型
	 *
	 * @return PVC2
	 */
	public function isTime() {
		$this->_DataType = PVC2EnumObj::$DT_Time;
		return $this;
	}
	
	/**
	 * 日期时间格式字符串类型
	 *
	 * @return PVC2
	 */
	public function isDateTime() {
		$this->_DataType = PVC2EnumObj::$DT_Datetime;
		return $this;
	}
	/**
	 * Url字符串类型
	 *
	 * @return PVC2
	 */
	public function isUrl() {
		$this->_DataType = PVC2EnumObj::$DT_Url;
		return $this;
	}
	/**
	 * 电子邮件格式字符串类型
	 *
	 * @return PVC2
	 */
	public function isEMail() {
		$this->_DataType = PVC2EnumObj::$DT_EMail;
		return $this;
	}
	/**
	 * MD5格式字符串类型
	 *
	 * @author miaomin
	 * @return PVC2
	 */
	public function isMD5() {
		$this->_DataType = PVC2EnumObj::$DT_MD5;
		return $this;
	}
	/**
	 * 0或1整形
	 *
	 * @return PVC2
	 */
	public function isIntBool() {
		$this->_DataType = PVC2EnumObj::$DT_IntBool;
		return $this;
	}
	/**
	 * Json字符串类型
	 *
	 * @return PVC2
	 */
	public function isJson() {
		$this->_DataType = PVC2EnumObj::$DT_Json;
		return $this;
	}
	/**
	 * JsonArray字符串类型
	 *
	 * @return PVC2
	 */
	public function isJsonArray() {
		$this->_DataType = PVC2EnumObj::$DT_JsonArray;
		return $this;
	}
	
	// 扩展验证模式(Expand Mode)
	/**
	 * 使用正则验证目标值
	 *
	 * @param string $RegexString        	
	 * @return PVC2
	 */
	public function Regex($RegexString) {
		$this->_ExpandMode [PVC2EnumObj::$EM_Regex] = $RegexString;
		return $this;
	}
	/**
	 * 相等
	 *
	 * @param string $eqStr        	
	 * @return PVC2
	 */
	public function Eq($eqStr) {
		$this->_ExpandMode [PVC2EnumObj::$EM_Eq] = $eqStr;
		return $this;
	}
	/**
	 * 大等于
	 *
	 * @param string $gtStr        	
	 * @return PVC2
	 */
	public function Gteq($gtStr) {
		$this->_ExpandMode [PVC2EnumObj::$EM_Gteq] = $gtStr;
		return $this;
	}
	/**
	 * 目标值是否符合规定的字符长度
	 *
	 * @param Int $Min        	
	 * @param Int $Max        	
	 * @return PVC2
	 */
	public function Length($Min, $Max) {
		$this->_ExpandMode [PVC2EnumObj::$EM_Length] = array (
				'min' => $Min,
				'max' => $Max 
		);
		return $this;
	}
	/**
	 * 目标值是否符合规定的数值范围
	 *
	 * @param Num $Min        	
	 * @param Num $Max        	
	 * @return PVC2
	 */
	public function Between($Min, $Max) {
		$this->_ExpandMode [PVC2EnumObj::$EM_Between] = array (
				'min' => $Min,
				'max' => $Max 
		);
		return $this;
	}
	/**
	 * 目标值是否在一个序列内
	 *
	 * @param array $Array        	
	 * @return PVC2
	 */
	public function In($Array) {
		$this->_ExpandMode [PVC2EnumObj::$EM_In] = $Array;
		return $this;
	}
	/**
	 * 目标值是否和另一个SourceArray中的Key所对应的值
	 *
	 * @param string $Key        	
	 * @return PVC2
	 */
	public function Confirm($Key) {
		$this->_ExpandMode [PVC2EnumObj::$EM_Confirm] = $Key;
		return $this;
	}
	/**
	 * 由字符串和指定分割符返回一个数组
	 *
	 * @param string $Delimiter        	
	 */
	public function Split($Delimiter) {
		$this->_ExpandMode [PVC2EnumObj::$EM_Split] = $Delimiter;
		return $this;
	}
	/**
	 * 由字符串和指定分割符返回一个数组
	 *
	 * @param function $Function        	
	 */
	public function UseFunction($Function) {
		$this->_ExpandMode [PVC2EnumObj::$EM_Function] = $Function;
		return $this;
	}
	
	// 空验证(Empty Validate)
	/**
	 * 目标存在则验证
	 *
	 * @return PVC2
	 */
	public function validateExists() {
		$this->_Validate = PVC2EnumObj::$EV_Exists;
		return $this;
	}
	/**
	 * 必须验证
	 *
	 * @return PVC2
	 */
	public function validateMust() {
		$this->_Validate = PVC2EnumObj::$EV_Must;
		return $this;
	}
	/**
	 * 目标值不为空或''时验证
	 *
	 * @return PVC2
	 */
	public function validateNotNull() {
		$this->_Validate = PVC2EnumObj::$EV_NotNull;
		return $this;
	}
	
	// 辅助功能
	/**
	 *
	 * @param any $Val        	
	 * @return PVC2
	 */
	public function DefVal($Val) {
		$this->_DefaultValue = $Val;
		return $this;
	}
	/**
	 * 当前验证规则不通过时产生的错误消息
	 *
	 * @param string $ErrorMessage        	
	 * @return PVC2
	 */
	public function Error($ErrorMessage) {
		$this->_Error = $ErrorMessage;
		return $this;
	}
	/**
	 * 设置严格验证模式
	 *
	 * @param bool $Mode        	
	 * @return PVC2
	 */
	public function setStrictMode($Mode) {
		$this->_StrictMode = $Mode;
		return $this;
	}
	
	// 正式开始
	public function reset() {
		$this->_DataType = null;
		$this->_ExpandMode = array ();
		$this->_EmptyVerification = null;
		$this->_DefaultValue = null;
		$this->_Error = '';
	}
	/**
	 * 添加一条验证规则
	 */
	public function add($Key) {
		$Rule = array (
				'Key' => $Key,
				'VM' => $this->_ValueMode,
				'DT' => $this->_DataType,
				'EM' => $this->_ExpandMode,
				'EV' => $this->_Validate,
				'DV' => $this->_DefaultValue,
				'ERROR' => $this->_Error 
		);
		$this->Selector [] = $Rule;
		$this->reset ();
	}
	public function addArray($Arr) {
		foreach ( $Arr as $Key ) {
			$Rule = array (
					'Key' => $Key,
					'VM' => $this->_ValueMode,
					'DT' => $this->_DataType,
					'EM' => $this->_ExpandMode,
					'EV' => $this->_Validate,
					'DV' => $this->_DefaultValue,
					'ERROR' => $this->_Error 
			);
			$this->Selector [] = $Rule;
		}
		$this->reset ();
	}
	public function verifyValue($Value) {
		$Rule = array (
				'VM' => $this->_ValueMode,
				'DT' => $this->_DataType,
				'EM' => $this->_ExpandMode,
				'EV' => $this->_Validate,
				'DV' => $this->_DefaultValue 
		);
		$this->reset ();
		return $this->verify ( $Value, $Rule );
	}
	public function verifyKey($Key) {
		$Value = $this->getValue ( $Key );
		return $this->verifyValue ( $Value );
	}
	public function verifyAll() {
		$this->ResultArray = array ();
		$this->Error = array ();
		foreach ( $this->Selector as $Rule ) {
			$Key = $Rule ['Key'];
			$VM = isset ( $Rule ['VM'] ) ? $Rule ['VM'] : $this->_ValueMode;
			$Value = $this->getValue ( $Key, $VM );
			$this->ResultArray [$Key] = $Value;
			if (count ( $this->Error ) > 0 && $this->_StrictMode) {
				continue;
			}
			if ($this->verify ( $Value, $Rule )) {
				$this->addReuult ( $Rule ['Key'], $this->_TempResult );
				$this->_TempResult = null;
			} else {
				$this->addError ( $Rule ['Key'], $Rule ['ERROR'] );
			}
		}
		return count ( $this->Error ) == 0;
	}
	private function verify($Value, $Rule) {
		$IsVerify = false;
		$Result = false;
		
		switch ($Rule ['EV']) {
			case PVC2EnumObj::$EV_Must :
				{
					$IsVerify = true;
					break;
				}
			case PVC2EnumObj::$EV_NotNull :
				{
					$IsVerify = ! ($Value === null || $Value === '');
					break;
				}
			default : // PVC2EnumObj::$EV_Exists
				{
					$IsVerify = isset ( $Value );
					break;
				}
		}
		
		if ($IsVerify) {
			if (isset ( $Rule ['DT'] )) {
				if ($this->validationDataType ( $Value, $Rule ['DT'] )) {
					$Result = true;
				}
			}
			if (isset ( $Rule ['EM'] )) {
				foreach ( $Rule ['EM'] as $EM => $EMRule ) {
					$fun = 'validation' . $EM;
					if (method_exists ( $this, $fun )) {
						switch ($EM) {
							case PVC2EnumObj::$EM_Between :
								{
									$Result = $this->{$fun} ( $Value, $EMRule ['min'], $EMRule ['max'] );
									break;
								}
							case PVC2EnumObj::$EM_Length :
								{
									$Result = $this->{$fun} ( $Value, $EMRule ['min'], $EMRule ['max'] );
									break;
								}
							case PVC2EnumObj::$EM_Split :
								{
									$Result = $this->{$fun} ( $Value, $EMRule );
									$this->_TempResult = $Result;
									break;
								}
							default :
								{
									$Result = $this->{$fun} ( $Value, $EMRule );
									break;
								}
						}
					}
				}
			}
		}
		
		if ($Result === false) {
			if (! $this->setDefaultValue ( $Value, $Rule ['DV'] ) && $IsVerify) {
				return false;
			}
		}
		return true;
	}
	private function setDefaultValue($Value, $Default) {
		if ($Default === null) {
			return false;
		}
		$this->_TempResult = null;
		if (($Value === null || $Value === '') && $Default !== null) {
			$this->_TempResult = $Default;
		}
		return true;
	}
	private function getValue($Key, $ValueMode = null) {
		$ValueMode = $ValueMode ? $ValueMode : $this->_ValueMode;
		switch ($ValueMode) {
			case PVC2EnumObj::$VM_Post :
				{
					return $_POST [$Key];
					break;
				}
			case PVC2EnumObj::$VM_Get :
				{
					return isset ( $_GET [$Key] ) ? urldecode ( $_GET [$Key] ) : null;
					break;
				}
			case PVC2EnumObj::$VM_Session :
				{
					return $_SESSION [$Key];
					break;
				}
			case PVC2EnumObj::$VM_Cookie :
				{
					return $_COOKIE [$Key];
					break;
				}
			case PVC2EnumObj::$VM_Array :
				{
					return $this->SourceArray [$Key];
					break;
				}
		}
	}
	private function validationDataType($Value, $DataType) {
		switch ($DataType) {
			case PVC2EnumObj::$DT_Int :
				{
					if (! (is_numeric ( $Value ) && is_int ( $Value * 1 ))) {
						return false;
					}
					$this->_TempResult = intval ( $Value );
					break;
				}
			case PVC2EnumObj::$DT_Num :
				{
					if (! (is_numeric ( $Value ))) {
						return false;
					}
					$this->_TempResult = floatval ( $Value );
					break;
				}
			case PVC2EnumObj::$DT_String :
				{
					if (! is_string ( $Value )) {
						return false;
					}
					$this->_TempResult = strval ( $Value );
					break;
				}
			case PVC2EnumObj::$DT_Array :
				{
					if (! (is_array ( $Value ))) {
						return false;
					}
					$this->_TempResult = $Value;
					break;
				}
			case PVC2EnumObj::$DT_Date :
				{
					$Pattern = '@^(([\d]{1,4}[-.\\\\]((0?[13578]|1[02])[-.\\\\](0?[1-9]|[12][\d]|3[01])|(0?[469]|11)[-.\\\\](0?[1-9]|[12][\d]|30)|(0?[2])[-.\\\\](0?[1-9]|[1][\d]|2[0-8])))|(((([\d]{0,2})(0[48]|[2468][048]|[13579][26]))|((0?[48]|1[26]|[2468][048]|[3579][26])00))[-.\\\\]0?2[-.\\\\]29))$@';
					if (! $this->validationRegex ( $Value, $Pattern )) {
						return false;
					}
					$this->_TempResult = strval ( str_replace ( '\\', '-', $Value ) );
					break;
				}
			case PVC2EnumObj::$DT_Time :
				{
					$Pattern = '@^([2][0-3])|[0-1]?\d:[0-5]?\d:[0-5]?\d$@';
					return $this->validationRegex ( $Value, $Pattern );
				}
			case PVC2EnumObj::$DT_Datetime :
				{
					// !!!这个正则不专业..到时候改改..
					$Pattern = '@^$@';
					if (! $this->validationRegex ( $Value, $Pattern )) {
						return false;
					}
					$this->_TempResult = strval ( str_replace ( '\\', '-', $Value ) );
					break;
				}
			case PVC2EnumObj::$DT_Url :
				{
					// !这个正则不专业..到时候改改..
					$Pattern = '/(https?|ftp|mms):\/\/([A-z0-9]+[_\-]?[A-z0-9]+\.)*[A-z0-9]+\-?[A-z0-9]+\.[A-z]{2,}(\/.*)*\/?/';
					return $this->validationRegex ( $Value, $Pattern );
				}
			case PVC2EnumObj::$DT_EMail :
				{
					$Pattern = '/^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/';
					return $this->validationRegex ( $Value, $Pattern );
				}
			case PVC2EnumObj::$DT_IntBool :
				{
					if (! (is_numeric ( $Value ) && is_int ( $Value * 1 )) || ! ($Value == '0' || $Value == '1')) {
						return false;
					}
					$this->_TempResult = intval ( $Value );
					break;
				}
			case PVC2EnumObj::$DT_Json :
				{
					$Json = json_decode ( $Value );
					if ($Json === false) {
						return false;
					}
					$this->_TempResult = $Json;
					break;
				}
			case PVC2EnumObj::$DT_JsonArray :
				{
					$Json = json_decode ( $Value, true );
					if ($Json === false) {
						return false;
					}
					$this->_TempResult = $Json;
					break;
				}
			// default : { return false; }
		}
		return true;
	}
	
	// miaomin
	private function validationGteq($Value, $gtStr) {
		if ($Value >= $gtStr) {
			return true;
		}
		return false;
	}
	
	// miaomin
	private function validationEq($Value, $eqStr) {
		if ($Value === $eqStr) {
			return true;
		}
		return false;
	}
	private function validationRegex($Value, $Regex) {
		if (is_string ( $Value )) {
			if (preg_match ( $Regex, $Value ) > 0) {
				$this->_TempResult = strval ( $Value );
				return true;
			}
		}
		return false;
	}
	private function validationLength($Value, $Min, $Max) {
		$StrLength = $this->utf8_StrLen ( $Value );
		if (isset ( $Min )) {
			if (! (is_int ( $Min ) && $StrLength >= $Min)) {
				return false;
			}
		}
		if (isset ( $Max )) {
			if (! (is_int ( $Max ) && $StrLength <= $Max)) {
				return false;
			}
		}
		return true;
	}
	private function validationBetween($Value, $Min, $Max) {
		if (! is_numeric ( $Value )) {
			return false;
		}
		if (isset ( $Min )) {
			if (! (is_numeric ( $Min ) && $Value >= $Min)) {
				return false;
			}
		}
		if (isset ( $Max )) {
			if (! (is_numeric ( $Max ) && $Value <= $Max)) {
				return false;
			}
		}
		return true;
	}
	private function validationIn($Value, $CompareArray) {
		if (! is_array ( $CompareArray )) {
			return false;
		}
		return in_array ( $Value, $CompareArray );
	}
	private function validationSplit($Value, $Delimiter) {
		return split ( $Delimiter, $Value );
	}
	// !
	private function validationConfirm($Value, $Confirm) {
		
		// miaomin debug@2014.4.1
		/*
		 * $ConfirmValue = $this->SourceArray [$Confirm]; return $Value ==
		 * $ConfirmValue ? true : false;
		 */
		$ConfirmValue = $this->getValue ( $Confirm );
		return $Value == $ConfirmValue ? true : false;
	}
	private function validationFunction($Value, $Function) {
		return $Function ( $Value );
	}
	private function utf8_StrLen($string = null) {
		preg_match_all ( "/./us", $string, $match );
		return count ( $match [0] );
	}
	private function addReuult($Key, $Result) {
		$this->ResultArray [$Key] = $Result;
	}
	private function addError($Key, $ErrorMessage) {
		if ($this->_StrictMode) {
			$this->Error = $ErrorMessage;
		} else {
			if (! isset ( $this->Error [$Key] )) {
				$this->Error [$Key] = $ErrorMessage;
			}
		}
	}
}
class PVC2EnumObj {
	// 接受值模式
	public static $VM_Post = 'post';
	public static $VM_Get = 'get';
	public static $VM_Session = 'session';
	public static $VM_Cookie = 'cookie';
	public static $VM_Array = 'array';
	// 扩展驗證模式
	public static $EM_Regex = 'Regex';
	public static $EM_Length = 'Length';
	public static $EM_Between = 'Between';
	public static $EM_In = 'In';
	public static $EM_Confirm = 'Confirm';
	public static $EM_Split = 'Split';
	public static $EM_Function = 'Function';
	public static $EM_Eq = 'Eq'; // miaomin add
	                             // 值類型
	public static $EM_Gteq = 'Gteq'; // miaomin add
	public static $DT_Int = 'Int';
	public static $DT_Num = 'Num';
	public static $DT_String = 'String';
	public static $DT_Array = 'Array';
	public static $DT_Date = 'Date';
	public static $DT_Time = 'Time';
	public static $DT_Datetime = 'Datetime';
	public static $DT_Url = 'Url';
	public static $DT_EMail = 'EMail';
	public static $DT_MD5 = 'MD5';
	public static $DT_IntBool = 'IntBool';
	public static $DT_Json = 'Json';
	public static $DT_JsonArray = 'JsonArray';
	// 空值验证方式
	public static $EV_Exists = 'Exists';
	public static $EV_Must = 'Must';
	public static $EV_NotNull = 'NotNull';
}