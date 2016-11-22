<?php
/**
 * 屏蔽关键字类
 *
 * @author miaomin 
 * Jun 28, 2013 11:38:42 AM
 */
class NoKeywordsModel extends Model {
	protected $tableName = 'nokeywords';
	protected $fields = array (
			'nk_id',
			'nk_words',
			'nk_nid',
			'nk_createdate',
			'_pk' => 'nk_id',
			'_autoinc' => TRUE 
	);
	
	
	/**
	 * 校验云目录名称是否有违禁词汇
	 *
	 * @param string $foldername
	 * @return boolean
	 */
	public function verifyNameNokey(string $foldername) {
		// 昵称是否含有屏蔽字
		$NK = D ( 'NoKeywords' );
		$nkCount = $NK->where ( 'nk_words="' . $foldername . '"' )->count ( 'nk_id' );
	
		if ($nkCount) {
			return false;
		}
		return true;
	}
	
	//判断字符长度是否小于3
	public function verifyNameStringLen(string $foldername){
		if(!isset($foldername{2})){
			return false;
		}
		return true;
	}
	
	
}
?>