<?php
/**
 * 用户标签类
 *
 * @author miaomin 
 * Mar 13, 2014 10:15:19 AM
 *
 * $Id$
 */
class UserTagsModel extends Model {
	
	/**
	 *
	 * @var DBF
	 */
	protected $DBF;
	
	/**
	 *
	 * @var DBF_UserTags
	 */
	public $F;
	
	/**
	 * 自动完成
	 */
	protected $_auto = array (
			array (
					'ut_count',
					'1' 
			) 
	);
	
	/**
	 * 用户标签类
	 */
	public function __construct() {
		$this->DBF = new DBF ();
		$this->F = $this->DBF->UserTags;
		$this->trueTableName = $this->F->_Table;
		$this->fields = $this->F->getFields ();
		$this->_map = $this->F->getMappedFields ();
		
		parent::__construct ();
	}
	
	/**
	 * 重要！ 以下内容可以考虑同产品标签表重构！！！
	 */
	
	/**
	 * 用户自定义标签
	 *
	 * @param int $uid        	
	 * @param array $tagsArray        	
	 * @param int $type        	
	 * @return mixed
	 */
	public function addUserTagsArray($uid, $tagsArray, $type = null) {
		if (! $type) {
			return false;
		}
		
		// 处理用户标签表
		$OldTags = $this->getTagsByUser ( $uid, $type );
		
		if ($OldTags === false) {
			return false;
		}
		
		$OldTags = $OldTags ? $OldTags : array ();
		$ExistTags = array ();
		
		foreach ( $OldTags as $OldTag ) {
			if (in_array ( $OldTag [$this->F->TagName], $tagsArray )) {
				$ExistTags [] = $OldTag [$this->F->TagName];
			}
		}
		
		if (! array_diff ( $tagsArray, $OldTags )) {
			return true;
		}
		
		if ($this->changTagsCount ( $ExistTags, - 1 ) === false) {
			return false;
		}
		
		$TagsIDArray = $this->addTagsArray ( $tagsArray );
		if ($TagsIDArray === false) {
			return false;
		}
		
		// 处理用户标签索引表
		$UTIM = new UserTagsIndexModel ();
		if ($UTIM->where ( $UTIM->F->UID . "='" . $uid . "' AND " . $UTIM->F->TagType . "='" . $type . "'" )->delete () === false) {
			return false;
		}
		
		foreach ( $TagsIDArray as $TagID ) {
			$UTIM->{$UTIM->F->UID} = $uid;
			$UTIM->{$UTIM->F->TagID} = $TagID;
			$UTIM->{$UTIM->F->TagType} = $type;
			if ($UTIM->add () === false) {
				return false;
			}
		}
		
		// 处理用户个人信息表
		$UPM = new UserProfileModel ();
		$upmRes = $UPM->find ( $uid );
		if (! $upmRes) {
			return false;
		}
		$userProfStr = $this->tagsArrTotagsStr ( $tagsArray );
		$UPM->u_prof = $userProfStr;
		$UPM->save();
		
		return true;
	}
	
	/**
	 * 添加标签
	 *
	 * @param string $tag        	
	 * @return Ambigous <mixed, boolean, unknown, string>
	 */
	public function addTag($tag) {
		$res = $this->getByut_name ( $tag );
		if (is_array ( $res )) { // 有返回结果
			$this->where ( 'ut_id=' . $this->ut_id )->setInc ( 'ut_count', 1 );
			return $this->ut_id;
		} else {
			$this->create ();
			$this->ut_name = $tag;
			return $this->add ();
		}
	}
	
	/**
	 * 添加标签组
	 *
	 * @param array $TagsArray        	
	 * @return boolean multitype:NULL <mixed, boolean, unknown, string>
	 */
	public function addTagsArray($TagsArray) {
		$INStr = "'" . implode ( "','", $TagsArray ) . "'";
		$DBTags = $this->where ( $this->F->TagName . ' IN (' . $INStr . ')' )->select ();
		if ($DBTags === false) {
			return false;
		}
		$DBTags = $this->conventPKtoArrayKey ( $DBTags, $this->F->TagName );
		$Result = array ();
		foreach ( $TagsArray as $Tags ) {
			if (trim ( $Tags ) == '') {
				continue;
			}
			if (array_key_exists ( $Tags, $DBTags )) {
				$this->where ( 'ut_id=' . $DBTags [$Tags] [$this->F->ID] )->setInc ( $this->F->Count, 1 );
				$Result [$Tags] = $DBTags [$Tags] [$this->F->ID];
			} else {
				$this->{$this->F->TagName} = $Tags;
				$this->{$this->F->Count} = 1;
				$TID = $this->add ();
				if (! $TID) {
					return false;
				}
				$Result [$Tags] = $TID;
			}
		}
		return $Result;
	}
	
	/**
	 * 改变标签引用计数
	 *
	 * @param array $TagsArray        	
	 * @param int $Inc        	
	 * @return boolean Ambigous unknown>
	 */
	public function changTagsCount($TagsArray, $Inc) {
		if (! $TagsArray) {
			return true;
		}
		$INStr = "'" . implode ( "','", $TagsArray ) . "'";
		$DBTags = $this->where ( $this->F->TagName . ' IN (' . $INStr . ')' )->select ();
		if ($DBTags === false) {
			return false;
		}
		$TagsID = array ();
		foreach ( $DBTags as $Tag ) {
			$TagsID [] = $Tag [$this->F->ID];
		}
		$this->where ( $this->F->ID . ' IN (' . implode ( ',', $TagsID ) . ')' );
		return $this->setInc ( $this->F->Count, $Inc );
	}
	
	/**
	 * 获取标签
	 *
	 * @param int $Top        	
	 * @return mixed
	 */
	public function getTages($Top = 100) {
		$DBF = new DBF ();
		return $this->limit ( $Top )->order ( $DBF->UserTags->Count . ' DESC' )->select ();
	}
	
	/**
	 * 根据用户ID获取标签
	 *
	 * @param int $UID        	
	 * @param int $Type        	
	 * @return Ambigous <mixed, string, boolean, NULL, unknown>
	 */
	public function getTagsByUser($UID, $Type = 0) {
		$UTI = $this->DBF->UserTagsIndex;
		$UTI_UID = $UTI->_Table . '.' . $UTI->UID;
		$UTI_TID = $UTI->_Table . '.' . $UTI->TagID;
		$UTI_TYPE = $UTI->_Table . '.' . $UTI->TagType;
		
		$UT_ID = $this->F->_Table . '.' . $this->F->ID;
		
		if ($Type) {
			return $this->join ( $UTI->_Table . " ON " . $UTI_TID . '=' . $UT_ID )->where ( $UTI_UID . "='" . $UID . "' AND " . $UTI_TYPE . "='" . $Type . "'" )->select ();
		} else {
			return $this->join ( $UTI->_Table . " ON " . $UTI_TID . '=' . $UT_ID )->where ( $UTI_UID . "='" . $UID . "'" )->select ();
		}
	}
	
	/**
	 * 主键转换
	 *
	 * @param array $Array        	
	 * @param string $PK        	
	 * @return multitype:multitype: unknown
	 */
	private function conventPKtoArrayKey($Array, $PK) {
		$Result = array ();
		foreach ( $Array as $Key => $Val ) {
			if (! isset ( $Result [$Val [$PK]] )) {
				$Result [$Val [$PK]] = array ();
			}
			$Result [$Val [$PK]] = $Val;
		}
		return $Result;
	}
	
	/**
	 * 将标签数组拼接成标签字符串
	 *
	 * @param array $tagsArray        	
	 * @param string $separator        	
	 * @return string
	 */
	private function tagsArrTotagsStr($tagsArray, $separator = ' ') {
		$TagsStr = '';
		foreach ( $tagsArray as $Tags ) {
			$TagsStr .= $Tags . $separator;
		}
		if (strlen ( $TagsStr ) > 0) {
			$TagsStr = substr ( $TagsStr, 0, strlen ( $TagsStr ) - strlen ( $separator ) );
		}
		return $TagsStr;
	}
}