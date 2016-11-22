<?php
//@formatter:off
/**
 * @name: PageValueCollector(PVC)(頁面值採集器)
 * @version: 1.0.0.7
 * @author: 冷夜草
 * @create: 2012-12-04
 * @update: 2012-02-25
 */
class PageValueCollector
{
	// 接受值模式
	public $ValueMode = 'post';
	public static $CVM_Post = 'post';
	public static $CVM_Get = 'get';
	public static $CVM_Session = 'session';
	public static $CVM_Cookie = 'cookie';
	public static $CVM_Array = 'array';
	// 驗證模式
	public static $VM_NoValidation = 0;
	public static $VM_DataType = 1;
	public static $VM_Regex = 2;
	public static $VM_Length = 3;
	public static $VM_Between = 4;
	public static $VM_In = 5;
	public static $VM_Confirm = 6;
	public static $VM_Split = 7;
	public static $VM_Function = 8;
	// 值類型(只用於M_DataType)
	public static $DT_Int = 0;
	public static $DT_Decimal = 1;
	public static $DT_String = 2;
	public static $DT_Array = 3;
	public static $DT_Date = 4;
	public static $DT_Time = 5;
	public static $DT_Datetime = 6;
	public static $DT_Url = 7;
	public static $DT_EMail = 8;
	public static $DT_IntBool = 9;
	public static $DT_Json = 10;
	public static $DT_JsonArray = 11;
	// 空值檢測方式
	public static $EV_NoValidation = 0;
	public static $EV_Default = 1;
	public static $EV_NotNull = 2;
	//用于偷懒的数组初始变量们
	// @formatter:on
	/**
	 * 目標數組中要檢測的Key
	 */
	public static $INIT_Key = 'Key';
	/**
	 * 對目標數據的驗證模式，使用[$VM_Enum]賦值
	 */
	public static $INIT_ValidationMode = 'ValidationMode';
	/**
	 * 僅在[$INIT_ValidationMode = $VM_DataType]模式下有效 
	 * 對目標數據的數據類型進行驗證，使用[$DT_Enum]賦值 
	 * $DT_Int //類型是整形的數據，小數是不被允許的
	 * $DT_Decimal //數值類型，包含整數和小數
	 * $DT_String //字符串類型數據
	 * $DT_Array //數組類型數據
	 * $DT_Date //日期類型數據，正則驗證，帶閏年的驗證
	 * $DT_Time //時間類型數據，正則驗證，精確到秒（必須包含）
	 * $DT_Datetime //日期和時間的驗證，正則驗證，現在可能有問題
	 * $DT_Url //Url的驗證，正則驗證
	 * $DT_EMail //E-Mail驗證，正則驗證
	 * $DT_IntBool //對0或1的int類型驗證，返回數字
	 */
	public static $INIT_DataType = 'DataType';
	/**
	 * 對目標數據驗證失敗后記錄的錯誤信息
	 */
	public static $INIT_ErrorMessage = 'ErrorMessage';
	/**
	 * 目標為空值時的判斷方式，使用 [$EV_Enum]賦值
	 * $EV_NoValidation不對數據是否為空進行驗證
	 * $EV_Default中允許null值存在的
	 * $EV_NotNull中不允許null值存在的
	 */
	public static $INIT_EmptyValidate = 'EmptyValidate';
	/**
	 * 在[$INIT_EmptyValidate=$EV_NoValidation]時無效
	 * 在[$INIT_EmptyValidate=$EV_Default]時有效
	 * 在[$INIT_EmptyValidate=$EV_NotNull]時有效
	 * 值为空时如果设置了该值则填充变量
	 */
	public static $INIT_DefaultValue = 'DefaultValue';
	/**
	 * 僅在[$INIT_ValidationMode = $VM_Regex]模式下有效 
	 * 使用一個正則表達式賦值
	 * 參數：[$INIT_Regex =>'/^[a-zA-Z]{1,5}$/']
	 * 使用preg_match進行匹配
	 */
	public static $INIT_Regex = 'Regex';
	/**
	 * 在[$INIT_ValidationMode = $M_Length]模式下有效
	 * 在[$INIT_ValidationMode = $M_Between]模式下有效
	 * 目標值驗證範圍中允許的最小值，參數：[$INIT_Min => 2]
	 * 必須包含$INIT_Min或$INIT_Max中的一項，否則驗證失敗
	 */
	public static $INIT_Min = 'Min';
	/**
	 * 在[$INIT_ValidationMode = $M_Length]模式下有效
	 * 在[$INIT_ValidationMode = $M_Between]模式下有效
	 * 目標值驗證範圍中允許的最大值，參數：[$INIT_Max => 2]
	 * 必須包含$INIT_Min或$INIT_Max中的一項，否則驗證失敗
	 */
	public static $INIT_Max = 'Min';
	/**
	 * 僅在[$INIT_ValidationMode = $VM_In]模式下有效
	 * 驗證目標值是否在一個序列中
	 * 使用一個数组賦值
	 * 參數：[$INIT_CompareArray => array(1,2,3,4,5)]
	 */
	public static $INIT_CompareArray = 'CompareArray';
	/**
	 * 僅在[$INIT_ValidationMode = $VM_Confirm]模式下有效
	 * 驗證兩個Key所指向的值是否相同
	 * 使用字符串賦值，字符串必須是另一個Key
	 * 參數：[$INIT_Confirm => 'pwdconfirm']
	 */
	public static $INIT_Confirm = 'Confirm';
	/**
	 * 僅在[$INIT_ValidationMode = $VM_Split]模式下有效
	 * 由字符串和指定分割符返回一個數組
	 * 使用字符串賦值，參數：[$INIT_Split => ',']
	 * 該方式不帶驗證
	 */
	public static $INIT_Split = 'Delimiter';
	/**
	 * 僅在[$INIT_ValidationMode = $VM_Function]模式下有效
	 * 由自定義函數對值進行驗證
	 * 使用一個function對其賦值，參數：[$INIT_Function => 'avg']
	 * 返回結果為傳入函數的返回值
	 */
	public static $INIT_Function = 'Function';
	
	//@formatter:off
	// 前方高能注意!
	/**
	 * 用於定義PVC中數據選擇器的收集內容與驗證規則
	 * 選擇器是由一個數組定義的，這個數組的組成部份也是數組
	 * 在子數組中，'鍵'=>'值'的對應關係定義了單個key的驗證規則
	 * 以下內容是子數組內公用的
	 * <code>
	 * $pvc->Selector = array(
	 * 	array(
	 * 		$pvc::$INIT_Key => 'name', //要驗證的key
	 * 		$pvc::$INIT_DataType => $pvc::$VM_DataType, //驗證類型 [self::M_Enum] PVC中枚舉的M_類型
	 * 		$pvc::$INIT_ErrorMessage => 'name Error', //錯誤消息 | 驗證失敗時會被填充到$Error數組中
	 * 		$pvc::$INIT_EmptyValidate => $pvc::$EV_Default, //空值的檢查方式 [self::EV_Enum] PVC中枚舉的EV_類型，$EV_Default中允許null值存在的，$EV_NotNull則不允許
	 * 		$pvc::$INIT_DefaultValue => '' //值為空情況下的默認值，在'EmptyValidate'=>$EV_NoValidation模式下無效
	 * 		)
	 * );
	 * </code>
	 * 
	 * 分別說明每個ValidationMode
	 * $VM_NoValidation //不進行驗證，ErrorMessage、EmptyValidate和DefaultValue在這種模式下是無效的
	 * $VM_DataType //對數據類型進行驗證，詳見對DataType的說明
	 * $VM_Regex //驗證一個正則表達式 [參數：'Regex'=>'/^[a-zA-Z]{1,5}$/']
	 * $VM_Length //驗證一個字符串的長度是否在一個範圍內 [參數：'Min' => 2, 'Max' => 55(兩個參數必填一個)]
	 * $VM_Between //驗證一個數值的大小是否在一個範圍內 [參數：'Min' => 2, 'Max' => 55(兩個參數必填一個)]
	 * $VM_In //驗證一個對象是否存在于一個對照數組內 [參數：'CompareArray' => array(1,2,3,4,5)]
	 * $VM_Confirm //驗證兩個key指向的值是否相同  [參數：'Confirm' => 'pwdconfirm']
	 * $VM_Split //由字符串和指定分割符返回一個數組，不帶驗證[參數：'Delimiter' => ',']
	 * $VM_Function //由自定義函數對值進行驗證[參數：'Function' => 'avg']
	 * 
	 * 分別說明每個DataType
	 * $DT_Int //類型是整形的數據，小數是不被允許的
	 * $DT_Decimal //數值類型，包含整數和小數
	 * $DT_String //字符串類型數據
	 * $DT_Array //數組類型數據
	 * $DT_Date //日期類型數據，正則驗證，帶閏年的驗證
	 * $DT_Time //時間類型數據，正則驗證，精確到秒（必須包含）
	 * $DT_Datetime //日期和時間的驗證，正則驗證，現在可能有問題
	 * $DT_Url //Url的驗證，正則驗證
	 * $DT_EMail //E-Mail驗證，正則驗證
	 * $DT_IntBool //對0或1的int類型驗證，返回數字
	 * @var array(array(),array(),array()...)
	 */
	public $Selector = array();
	/**
	 * 用於存放要接受的數據數據，支持自定義
	 * <code>
	 * $pvc->SourceArray = array(
	 * 'name' => 'Zerock', 
	 * 'age' => '21', 
	 * 'createdate' => '2012\12\06', 
	 * 'isadmin' => 1, 
	 * 'website' => 'http://zzexy.elacg.com', 
	 * 'email' => 'no.zerock@hotmail.com', 
	 * 'cash' => '12.45', 
	 * 'shit' => '');
	 * </code>
	 * @var array
	 */
	public $SourceArray = null;
	/**
	 * 用於存放驗證后的錯誤信息的數組
	 * @var array
	 */
	public $Error = array();
	/**
	 * 經過驗證后的值將會被存儲在這個變量中
	 * @var array
	 */
	public $ResultArray = array();

	/**
	 * 用於初始化PVC值的接受模式，默认是post模式
	 * @param self::VM_Enum $valuemodel 
	 * PVC中枚舉的VM_類型
	 */
	public function __construct($valuemodel = 'post')
	{
		$this->ValueMode = $valuemodel;
		$this->setSourceArray();
	}

	/**
	 * 對數據進行驗證
	 */
	function validationData()
	{
		foreach($this->Selector as $Rule)
		{
			if($this->ResultArray[$Rule['Key']] === null)
			{ $this->ResultArray[$Rule['Key']] = $this->SourceArray[$Rule['Key']]; }
			if($Rule['ValidationMode'] !== self::$VM_NoValidation)
			{
				if($this->Error[$Rule['Key']] === null && ($this->SourceArray[$Rule['Key']] !== null || $Rule['EmptyValidate'] != self::$EV_NotNull))
				{
					if($this->validationEmpty($Rule['Key'], $Rule['EmptyValidate'], $Rule['DefaultValue'], $Rule['ErrorMessage']))
					{
						if(empty($this->SourceArray[$Rule['Key']]) && $this->SourceArray[$Rule['Key']] == $Rule['DefaultValue'])
						{ continue; }
						$Result = true;
						switch($Rule['ValidationMode'])
						{
							case self::$VM_DataType :
								{ $Result = $this->validationDataType($Rule['Key'], $Rule['DataType']); break; }
							case self::$VM_Regex :
								{ $Result = $this->validationRegex($Rule['Key'], $Rule['Regex']); break; }
							case self::$VM_Length :
								{ $Result = $this->validationLength($Rule['Key'], $Rule['Min'], $Rule['Max']); break; }
							case self::$VM_Between :
								{ $Result = $this->validationBetween($Rule['Key'], $Rule['Min'], $Rule['Max']); break; }
							case self::$VM_In :
								{ $Result = $this->validationIn($Rule['Key'], $Rule['CompareArray']); break; }
							case self::$VM_Confirm :
								{ $Result = $this->validationConfirm($Rule['Key'], $Rule['Confirm']); break; }
							case self::$VM_Split :
								{ $this->getSplitSplit($Rule['Key'], $Rule['Delimiter']); break; }
							case self::$VM_Function :
								{ $Result = $this->validationFunction($Rule['Key'], $Rule['Function']); break; }
						}
						if(!$Result)
						{ $this->addError($Rule['Key'], $Rule['ErrorMessage'] ? $Rule['ErrorMessage'] : 'Undefined error'); }
					}
				}
				else { $this->addError($Rule['Key'], 'NotFind'); }
			}
		}
	}

	private function setSourceArray()
	{
		switch($this->ValueMode)
		{
			case self::$CVM_Post : { $this->SourceArray = $_POST; break; }
			case self::$CVM_Get : {
				$UrlDecodeedGet = array();
				foreach ($_GET as $GetKey=>$GetValue)
				{ $UrlDecodeedGet[$GetKey] = urldecode($GetValue); }
				$this->SourceArray = $UrlDecodeedGet; 
				break; }
			case self::$CVM_Session : { $this->SourceArray = $_SESSION; break; }
			case self::$CVM_Cookie : { $this->SourceArray = $_COOKIE; break; }
			case self::$CVM_Array : { break; }
		}
	}

	private function validationEmpty($Key, $EmptyValidate, $DefaultValue, $ErrorMessage)
	{
		$PostValue = $this->SourceArray[$Key];
		switch($EmptyValidate)
		{
			case self::$EV_NoValidation : { return true; }
			case self::$EV_Default :
				{
					if(!empty($PostValue)) { return true; }
					$this->SourceArray[$Key] = $DefaultValue;
					$this->ResultArray[$Key] = $DefaultValue;
					return true;
					break;
				}
			case self::$EV_NotNull :
				{
					if(!empty($PostValue)) { return true; }
					if(isset($DefaultValue) && $DefaultValue !== null)
					{ 
						$this->SourceArray[$Key] = $DefaultValue;
						$this->ResultArray[$Key] = $DefaultValue;
						return true;
					}
					$this->addError($Key, $ErrorMessage ? $ErrorMessage : 'Empty');	
					break;
				}
		}
		return false;
	}

	private function validationDataType($Key, $DataType)
	{
		$PostValue = $this->SourceArray[$Key];
		switch($DataType)
		{
			case self::$DT_Int :
				{
					if(!(is_numeric($PostValue) && is_int($PostValue * 1))) { return false; }
					$this->ResultArray[$Key] = intval($PostValue);
					break;
				}
			case self::$DT_Decimal :
				{
					if(!(is_numeric($PostValue))) { return false; }
					$this->ResultArray[$Key] = floatval($PostValue);
					break;
				}
			case self::$DT_String :
				{
					if(!is_string($PostValue)) { return false; }
					$this->ResultArray[$Key] = strval($PostValue);
					break;
				}
			case self::$DT_Array :
				{
					if(!(is_array($PostValue))) { return false; }
					$this->ResultArray[$Key] = $PostValue;
					break;
				}
			case self::$DT_Date :
				{
					$Pattern = '@^(([\d]{1,4}[-.\\\\]((0?[13578]|1[02])[-.\\\\](0?[1-9]|[12][\d]|3[01])|(0?[469]|11)[-.\\\\](0?[1-9]|[12][\d]|30)|(0?[2])[-.\\\\](0?[1-9]|[1][\d]|2[0-8])))|(((([\d]{0,2})(0[48]|[2468][048]|[13579][26]))|((0?[48]|1[26]|[2468][048]|[3579][26])00))[-.\\\\]0?2[-.\\\\]29))$@';
					if(!$this->validationRegex($Key, $Pattern)) { return false; }
					$this->ResultArray[$Key] = strval(str_replace('\\', '-', $this->ResultArray[$Key]));
					$this->SourceArray[$Key] = $this->ResultArray[$Key];
					break;
				}
			case self::$DT_Time :
				{
					$Pattern = '@^([2][0-3])|[0-1]?\d:[0-5]?\d:[0-5]?\d$@';
					return $this->validationRegex($Key, $Pattern);
				}
			case self::$DT_Datetime :
				{
					// !!!这个正则不专业..到时候改改..
					$Pattern = '@^$@';
					if(!$this->validationRegex($Key, $Pattern)) { return false; }
					$this->ResultArray[$Key] = strval(str_replace('\\', '-', $this->ResultArray[$Key]));
					$this->SourceArray[$Key] = $this->ResultArray[$Key];
					break;
				}
			case self::$DT_Url :
				{
					// !这个正则不专业..到时候改改..
					$Pattern = '/^https?:\/\/(\w+\.)?[\w\-\.]+(\.\w+)+$/';
					return $this->validationRegex($Key, $Pattern);
				}
			case self::$DT_EMail :
				{
					$Pattern = '/^[a-zA-Z0-9_\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/';
					return $this->validationRegex($Key, $Pattern);
				}
			case self::$DT_IntBool :
				{
					if(!(is_numeric($PostValue) && is_int($PostValue * 1)) && ($PostValue = '0' || $PostValue = '1')) { return false; }
					$this->ResultArray[$Key] = intval($PostValue);
					break;
				}
			case self::$DT_Json :
				{
					$Json = json_decode($PostValue);
					if(!$Json) { return false; }
					$this->ResultArray[$Key] = $Json;
					break;
				}
			case self::$DT_JsonArray :
				{
					$Json = json_decode($PostValue, true);
					if(!$Json) { return false; }
					$this->ResultArray[$Key] = $Json;
					break;
				}
			//default : { return false; }
		}
		return true;
	}

	private function validationRegex($Key, $Regex)
	{
		$PostValue = $this->SourceArray[$Key];
		if(is_string($PostValue))
		{
			if(preg_match($Regex, $PostValue) > 0)
			{ $this->ResultArray[$Key] = strval($PostValue); return true; }
		}
		return false;
	}

	private function validationLength($Key, $Min, $Max)
	{
		$PostValue = $this->SourceArray[$Key];
		$StrLength = $this->utf8_StrLen($PostValue);
		if(isset($Min))
		{ if(!(is_int($Min) && $StrLength >= $Min)) { return false; } }
		if(isset($Max))
		{ if(!(is_int($Max) && $StrLength <= $Max)) { return false; } }
		return true;
	}

	private function validationBetween($Key, $Min, $Max)
	{
		$PostValue = $this->SourceArray[$Key];
		if(!is_numeric($PostValue)) { return false; }
		if(isset($Min))
		{ if(!(is_numeric($Min) && $PostValue >= $Min)) { return false; } }
		if(isset($Max))
		{ if(!(is_numeric($Max) && $PostValue <= $Max)) { return false; } }
		return true;
	}

	private function validationIn($Key, $CompareArray)
	{
		$PostValue = $this->SourceArray[$Key];
		if(!is_array($CompareArray)) {return false;}
		return in_array($PostValue, $CompareArray);
	}

	private function validationConfirm($Key, $Confirm)
	{
		$PostValue = $this->SourceArray[$Key];
		$ConfirmValue = $this->SourceArray[$Confirm];
		return $PostValue == $ConfirmValue ? true : false;
	}

	private function validationFunction($Key, $Function) { return $Function($Key); }

	private function getSplit($Key, $Delimiter)
	{
		$PostValue = $this->SourceArray[$Key];
		$this->ResultArray[$Key] = explode($Delimiter, $PostValue);
		return true;
	}

	private function utf8_StrLen($string = null)
	{
		preg_match_all("/./us", $string, $match);
		return count($match[0]);
	}

	private function addError($Key, $Error)
	{ if($this->Error[$Key] === null) { $this->Error[$Key] = $Error; } }
}
/**
* @changelog
* # v1.0.0.0(2012-12-04)
* # 所有收集及验证功能基本完成
*
* # v1.0.0.1(2012-12-10)
* # 修改了M_DataType中Date和Time的正則表達式
* # 增加註釋
* # DateTime的验证正则依然没= =
*
* # v1.0.0.2(2012-01-31)
* # 增加了$INIT_Enum系列值，用於初始化$Selector
* # 將$M_Enum修改為$VM_Enum
* # 將$T_Enum修改為$DT_Enum
* # 將$VM_Enum修改為$CVM_Enum
* 
* # v1.0.0.3(2012-02-11)
* # 修復了無法自動獲取值的Bug
* 
* # v1.0.0.4(2012-02-17)
* # 增加了對json的支持
* 
* # v1.0.0.6(2012-02-21)
* # 对空值的验证方式做了修改
* 
* # v1.0.0.7(2012-02-25)
* # 添加了Get模式下的UrlDecode
*/
?>