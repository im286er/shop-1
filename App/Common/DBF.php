<?php
//@formatter:off
class DBFCreater
{
	private $_dispPrefix = false;
	public function getFields()
	{
		$Define = array('_pk', '_autoinc');
		$ClassVars = get_class_vars(get_class($this));
		$Result = array();
		foreach($ClassVars as $Key => $Val)
		{
			if($Key[0] != '_') { $Result[] = $Val; }
			else { if(in_array($Key, $Define)) { $Result[$Key] = $Val; } }
		}
		return $Result;
	}

	public function getMappedFields()
	{
		$ClassVars = get_class_vars(get_class($this));
		$Result = array();
		foreach($ClassVars as $Key => $Val)
		{ if($Key[0] != '_') { $Result[strtolower($Key)] = $Val; } }
		return $Result;
	}
	
	public function dispPrefix($isDisp = false)
	{
		if($this->_dispPrefix == $isDisp ) { return; }
		$this->_dispPrefix = $isDisp;
	
		$Define = array('_pk');
		$ClassVars = get_class_vars(get_class($this));
		$Result = array();
		foreach($ClassVars as $Key => $Val)
		{
			if($Key[0] == '_' && !in_array($Key, $Define)) { continue; }
			if(!$isDisp)
			{ $Val = preg_replace('/$' . $ClassVars['_Table'] . '[.]/', '', $Val); }
			else { $Val = $ClassVars['_Table'] . '.' . $Val; }
				
			$this->{$Key} = $Val;
		}
	}
}
/*==========================********************************==========================
 * 库表结构定义开始
 * 
 * lastupdate: 2015.1.23
 */

/**
 * 活动主表
 * 
 * @author miaomin
 * Jul 15, 2015 2:54:54 PM
 *
 */
class DBF_SPMain extends DBFCreater
{
    private static $instance = null;
    public static function construct()
    {
        if(!(self::$instance instanceof self)) { self::$instance = new self(); }
        return self::$instance;
    }
    public $_Table 	= 'tdf_spmain';
    public $ID 		= 'spm_id';
    public $TITLE = 'spm_title';
    public $TYPE = 'spm_type';
    public $BEGIN = 'spm_begin';
    public $END = 'spm_end';
    public $CREATEDATE = 'spm_createdate';
    public $ENABLED = 'spm_enabled';
    public $CREATEUID = 'spm_createuid';
    public $PIDS = 'spm_pids';
    public $LASTUPDATE = 'spm_lastupdate';
    public $_pk 	= 'spm_id';
    public $_autoinc = true;
}

/**
 * 活动属性表
 * 
 * @author miaomin
 * Jul 24, 2015 6:06:48 PM
 *
 */
class DBF_SPProp extends DBFCreater
{
    private static $instance = null;
    public static function construct()
    {
        if(!(self::$instance instanceof self)) { self::$instance = new self(); }
        return self::$instance;
    }
    public $_Table 	= 'tdf_spprop';
    public $ID 		= 'spp_id';
    public $SPID = 'spm_id';
    public $SPITEMID = 'ispi_id';
    public $SPITEMNAME = 'ispi_name';
    public $SPPVAL = 'spp_val';
    public $_pk 	= 'spp_id';
    public $_autoinc = true;
}

/**
 * 活动属性配置表
 * 
 * @author miaomin
 * Jul 27, 2015 1:40:58 PM
 *
 */
class DBF_SPConf extends DBFCreater
{
    private static $instance = null;
    public static function construct()
    {
        if(!(self::$instance instanceof self)) { self::$instance = new self(); }
        return self::$instance;
    }
    public $_Table 	= 'tdf_spconfig';
    public $ID 		= 'spc_id';
    public $SPTYPEID = 'ispt_id';
    public $SPITEMID = 'ispi_id';
    public $_pk 	= 'spc_id';
    public $_autoinc = true;
}

/**
 * 活动商品表
 * 
 * @author miaomin
 * Jul 16, 2015 9:45:33 AM
 *
 */
class DBF_SPProduct extends DBFCreater
{
    private static $instance = null;
    public static function construct()
    {
        if(!(self::$instance instanceof self)) { self::$instance = new self(); }
        return self::$instance;
    }
    public $_Table 	= 'tdf_spproduct';
    public $ID 		= 'spp_id';
    public $SPID = 'spm_id';
    public $PID = 'p_id';
    public $_pk 	= 'spp_id';
    public $_autoinc = true;
}

/**
 * 活动类型表
 * 
 * @author miaomin
 * Jul 15, 2015 11:11:17 AM
 *
 */
class DBF_InfoSPType extends DBFCreater
{
    private static $instance = null;
    public static function construct()
    {
        if(!(self::$instance instanceof self)) { self::$instance = new self(); }
        return self::$instance;
    }
    public $_Table 	= 'tdf_info_sptype';
    public $ID 		= 'ispt_id';
    public $NAME = 'ispt_name';
    public $_pk 	= 'ispt_id';
    public $_autoinc = true;
}

/**
 * 销售报表
 *
 * @author miaomin 
 * Feb 5, 2015 7:50:24 PM
 *
 * $Id$
 */
class DBF_SalesReport extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	public $_Table 	= 'tdf_sales_report';
	public $ID 		= 'sr_id';
	public $CREATER = 'p_creater';
	public $PMOCUID = 'p_mocuid';
	public $ORDERID 	= 'up_orderid';
	public $PREPAIDID 	= 'up_id';
	public $PID	= 'p_id';
	public $BELONGPID = 'p_belongid';
	public $PPRICE = 'p_price';
	public $PCOUNT = 'sr_pcount';
	public $AMOUNT = 'sr_amount';
	public $PREPAIDUID 	= 'up_uid';
	public $PREPAIDUGROUP 	= 'up_ugroup';
	public $PREPAIDMOCUID = 'up_mocuid';
	public $CREATEDATE 	= 'sr_createdate';
	public $CDTIME 	= 'sr_cdtime';
	public $PREPAIDUIP = 'up_uip';
	public $PREPAIDUAGENT = 'up_uagent';
	public $DISCOUNTTYPE = 'up_discounttype';
	public $DISCOUNT = 'up_discount';
    public $REALAMOUNT = 'sr_realamount';
    public $WORKORDER = 'work_order';
	public $_pk 	= 'sr_id';
	public $_autoinc = true;
}

/**
 * 专属定制记录表
 *
 * @author miaomin 
 * Jan 23, 2015 5:03:46 PM
 *
 * $Id$
 */
class DBF_UserCustomize extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	public $_Table 	= 'tdf_user_customize';
	public $ID 		= 'ucu_id';
	public $UID 	= 'u_id';
	public $PID 	= 'p_id';
	public $_pk 	= 'ucu_id';
	public $_autoinc = true;
}

/**
 * 商品主分类属性值记录表
 *
 * @author miaomin 
 * Dec 13, 2014 1:13:00 PM
 *
 * $Id$
 */
class DBF_InfoProductPropVal extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_info_propval';
	public $ID = 'ipv_id';
	public $PROPVAL = 'ipv_val';
	public $PROPID = 'ipp_id';
	public $MAINTYPE = 'ipt_id';

	public $_pk = 'ipv_id';
	public $_autoinc = true;
}

/**
 * 商品主分类属性定义表
 *
 * @author miaomin 
 * Dec 12, 2014 2:11:48 PM
 *
 * $Id$
 */
class DBF_InfoProductMainProp extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_info_productprop';
	public $ID = 'ipp_id';
	public $PROPNAME = 'ipp_name';
	public $MAINTYPE = 'ipt_id';
	public $PROPVALS = 'ipp_vals';
	public $ISPRIME = 'ipp_prime';
	public $WEIGHT = 'ipp_weight';

	public $_pk = 'ipp_id';
	public $_autoinc = true;
}

/**
 * 商品主分类表
 *
 * @author miaomin 
 * Dec 12, 2014 11:33:18 AM
 *
 * $Id$
 */
class DBF_InfoProductMainType extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_info_producttype';
	public $ID = 'ipt_id';
	public $TYPENAME = 'ipt_name';
	public $TYPEINTRO = 'ipt_intro';
	public $TYPEPROPS = 'ipt_props';
	public $ISENABLE = 'ipt_isenable';

	public $_pk = 'ipt_id';
	public $_autoinc = true;
}

/**
 * 打印模型材质过滤表
 *
 * @author miaomin 
 * Nov 27, 2014 1:07:45 PM
 *
 * $Id$
 */
class DBF_ProductPMMaterialFilter extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_product_pm_material_filter';
	public $ID = 'ppmf_id';
	public $PMAID = 'pma_id';

	public $_pk = 'ppmf_id';
	public $_autoinc = true;
}

/**
 * 打印模型计算公式表
 *
 * @author miaomin 
 * Nov 17, 2014 11:20:09 AM
 *
 * $Id$
 */
class DBF_ProductPMFormula extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_product_pm_formula';
	public $ID = 'pmf_id';
	public $FORMULA = 'pmf_formula';
	public $ISDEFAULT = 'pmf_isdefault';
	
	public $_pk = 'pmf_id';
	public $_autoinc = true;
}

/**
 * 商品促销水印表
 * 
 * @author miaomin
 * Jun 25, 2015 10:02:12 AM
 *
 */
class DBF_ProductWaterProof extends DBFCreater{
    private static $instance = null;
    public static function construct()
    {
        if(!(self::$instance instanceof self)) { self::$instance = new self(); }
        return self::$instance;
    }

    public $_Table = 'tdf_product_waterproof';
    public $ID = 'pwp_id';
    public $TITLE = 'pwp_title';
    public $CREATEDATE = 'pwp_createdate';
    public $ISENABLED = 'pwp_isenabled';
    public $IMGURL = 'pwp_imgurl';

    public $_pk = 'pwp_id';
    public $_autoinc = true;
}

/**
 * 用户拥有的实体打印模型表
 *
 * @author miaomin 
 * Nov 5, 2014 1:17:58 PM
 *
 * $Id$
 */
class DBF_UserPrintModel extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_printmodel';
	public $ID = 'upm_id';
	public $PID = 'p_id';
	public $UID = 'u_id';
	public $PMAID = 'pma_id';
	public $PMANAME = 'pma_name';
	public $LENGTH_PRINT = 'upm_length';
	public $WIDTH_PRINT = 'upm_width';
	public $HEIGHT_PRINT = 'upm_height';
	public $VOLUME_PRINT = 'upm_volume';
	public $CONVEX_PRINT = 'upm_convex';
	public $UNITPRICE = 'upm_unitprice';
	public $PMDID = 'pmd_id';
	public $PMDTITLE = 'pmd_title';
	public $INCART = 'upm_incart';
	public $TYPE = 'upm_type';
	public $UCID = 'upm_cartid';
	public $ORDERID = 'upm_orderid';

	public $_pk = 'upm_id';
	public $_autoinc = true;
}

/**
 * 可打印模型成品表
 *
 * @author miaomin 
 * Oct 20, 2014 2:30:47 PM
 *
 * $Id$
 */
class DBF_ProductPrintModel extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_product_printmodel';
	public $ID = 'ppr_id';
	public $PID = 'p_id';
	public $VOLUME = 'ppr_volume';
	public $LENGTH = 'ppr_length';
	public $WIDTH = 'ppr_width';
	public $HEIGHT = 'ppr_height';
	public $SURFACE = 'ppr_surface';
	public $REPAIRLV = 'ppr_repairlv';
	public $CONVEX = 'ppr_convex';
	public $CREATEDATE = 'ppr_createdate';
	public $LASTUPDATE = 'ppr_lastupdate';
	public $VERIFY ='ppr_verify';
	public $VFYUID ='ppr_vfy_uid';
	public $VFYDATE ='ppr_vfy_date';
	public $VFYREASON = 'ppr_vfy_reason';
	
	public $_pk = 'ppr_id';
	public $_autoinc = true;
}

/**
 * 可打印模型支持材料表
 *
 * @author miaomin 
 * Oct 20, 2014 2:43:36 PM
 *
 * $Id$
 */
class DBF_ProductPMMaterial extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_product_pm_material';
	public $ID = 'ppm_id';
	public $PID = 'p_id';
	public $MATERIALID = 'pma_id';
	public $PRECISIONID = 'pmd_id';
	public $PRICE = 'pmm_price';
	public $ENABLED = 'ppm_enabled';

	public $_pk = 'ppm_id';
	public $_autoinc = true;
}

/**
 * 模型webgl_capture表
 *
 * @author miaomin 
 * Jul 8, 2014 7:15:32 PM
 *
 * $Id$
 */
class DBF_ProductWebglCapture extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_product_webgl_capture';
	public $ID = 'pwc_id';
	public $PMWID = 'pmw_id';
	public $PID = 'p_id';
	public $FILEPATH = 'pwc_filepath';
	public $CREATEDATE = 'pwc_createdate';
	public $ORDER = 'pwc_order';
	public $_pk = 'pwc_id';
	public $_autoinc = true;
}

/**
 * 模型webgl表
 *
 * @author miaomin 
 * Jul 8, 2014 5:56:31 PM
 *
 * $Id$
 */
class DBF_ProductWebgl extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_product_webgl';
	public $ID = 'pmw_id';
	public $PID = 'p_id';
	public $PFID = 'pf_id';
	public $IDCODE = 'pmw_idcode';
	public $CREATEDATE = 'pmw_createdate';
	public $CDTIME = 'pmw_cdtime';
	public $LASTUPDATE = 'pmw_lastupdate';
	public $LDTIME = 'pmw_ldtime';
	public $STAT = 'pmw_stat';
	public $UID= 'pmw_uid';
	public $TOKEN = 'pmw_token';
	public $TOKENID = 'pmw_tokenid';
	public $FROM = 'pmw_from';
	public $CURRENTCAPTURE = 'pmw_current_capture';
	public $CURRENTCAPTUREID = 'pmw_current_captureid';
	public $ORIGINALFILE = 'pmw_original_file';
	public $LASTUPDATEFILE = 'pmw_lastupdate_file';
	public $_pk = 'pmw_id';
	public $_autoinc = true;
}

/**
 * 任务队列表
 *
 * @author miaomin 
 * Jul 4, 2014 11:08:13 AM
 *
 * $Id$
 */
class DBF_JobQueue extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_job_queue';
	public $ID = 'jq_id';
	public $JOBCODE = 'jq_code';
	public $CREATEDATE = 'jq_createdate';
	public $CDTIME = 'jq_cdtime';
	public $STAT = 'jq_stat';
	public $TYPE= 'jq_type';
	public $TOKEN = 'jq_token';
	public $UID = 'jq_uid';
	public $IP = 'jq_ip';
	public $REID = 'jq_reid';
	public $_pk = 'jq_id';
	public $_autoinc = true;
}

/**
 * 用户粉丝表
 *
 * @author miaomin 
 * May 6, 2014 9:47:12 AM
 *
 * $Id$
 */
class DBF_UserFollower extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_follower';
	public $ID = 'ufr_id';
	public $UID = 'u_id';
	public $FollowerID = 'ufr_fromuid';
	public $CreateDate = 'ufr_createdate';
	public $CreateDateTS = 'ufr_createdate_ts';
	public $_pk = 'ufr_id';
	public $_autoinc = true;
}

/**
 * 用户关注表
 *
 * @author miaomin 
 * May 5, 2014 5:59:05 PM
 *
 * $Id$
 */
class DBF_UserFollowing extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_following';
	public $ID = 'uf_id';
	public $UID = 'u_id';
	public $FollowingUID = 'uf_touid';
	public $CreateDate = 'uf_createdate';
	public $CreateDateTS = 'uf_createdate_ts';
	public $IsFriend = 'uf_isfriend';
	public $FriendDate = 'uf_frienddate';
	public $FriendDateTS = 'uf_frienddate_ts';
	public $_pk = 'uf_id';
	public $_autoinc = true;
}

/**
 * 用户关系表
 *
 * @author miaomin 
 * May 5, 2014 5:52:40 PM
 *
 * $Id$
 */
class DBF_UserRelation extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_relation';
	public $UID = 'u_id';
	public $List = 'ur_list';
	public $CountList = 'ur_list_num';
	public $Ver = 'ur_ver';
	public $LastUpdate = 'ur_lastupdate';
	public $LastUpdateTS = 'ur_lastupdate_ts';
	public $_pk = 'u_id';
	public $_autoinc = false;
}

/**
 * 产品奖项表
 *
 * @author miaomin 
 * Mar 14, 2014 10:17:24 AM
 *
 * $Id$
 */
class DBF_ProductAward extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_product_award';
	public $AWID = 'aw_id';
	public $PID = 'p_id';
}

/**
 * 奖项信息表
 *
 * @author miaomin 
 * Mar 13, 2014 5:12:00 PM
 *
 * $Id$
 */
class DBF_AwardInfo extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_awardinfo';
	public $ID = 'aw_id';
	public $Name = 'aw_name';
	public $Type = 'aw_type';
	public $Status = 'aw_status';
	public $_pk = 'aw_id';
	public $_autoinc = true;
	
}

/**
 * 用户标签索引表结构映射
 *
 * @author miaomin 
 * Mar 13, 2014 10:11:49 AM
 *
 * $Id$
 */
class DBF_UserTagsIndex extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_tags_index';
	public $TagID = 'ut_id';
	public $UID = 'u_id';
	public $TagType = 'uti_type';
}

/**
 * 用户标签表结构映射
 *
 * @author miaomin 
 * Mar 13, 2014 9:53:54 AM
 *
 * $Id$
 */
class DBF_UserTags extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_tags';
	public $ID = 'ut_id';
	public $TagName = 'ut_name';
	public $Count = 'ut_count';
	public $IsHot = 'ut_ishot';
	public $CTime = 'ctime';
	public $_pk = 'ut_id';
	public $_autoinc = true;
}

/**
 * 用户点赞表结构映射
 *
 * @author miaomin 
 * Mar 10, 2014 9:59:51 AM
 *
 * $Id$
 */
class DBF_UserZan extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_zan';
	public $ID = 'uz_id';
	public $UID = 'u_id';
	public $ZanType = 'uz_type';
	public $ZanID = 'uz_pid';
	public $CTime = 'uz_date';
	public $IP = 'uz_ip';
	public $TS = 'uz_ts';
	public $_pk = 'uz_id';
	public $_autoinc = true;
}

/**
 * 教育经历库表结构映射
 *
 * @author miaomin 
 * Mar 7, 2014 1:23:13 PM
 *
 * $Id$
 */
class DBF_UserEdu extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_edu';
	public $ID = 'ued_id';
	public $UID = 'u_id';
	public $StartYear = 'ued_start_year';
	public $StartMonth = 'ued_start_month';
	public $EndYear = 'ued_end_year';
	public $EndMonth = 'ued_end_month';
	public $SchoolID = 'ued_schoolid';
	public $SchoolName = 'ued_schoolname';
	public $ProfID = 'ued_profid';
	public $ProfName = 'ued_profname';
	public $SchoolFormal = 'ued_schoolformal';
	public $CTime = 'ued_createdate';
	public $UpdateTime = 'ued_lastupdate';
	public $Status = 'ued_status';
	public $IsCert = 'ued_iscert';
	public $CertUpload = 'ued_certupload';
	public $IsPublic = 'ued_ispublic';
	public $ProvinceId = 'ued_provinceid';
	public $ProvinceName = 'ued_provincename';
	public $CityId = 'ued_cityid';
	public $CityName = 'ued_cityname';
	public $CityNumber = 'ued_cityno';
	public $Type = 'ued_type';
	public $_pk = 'ued_id';
	public $_autoinc = true;
}

/**
 * 工作经历库表结构映射
 *
 * @author miaomin 
 * Mar 7, 2014 1:23:44 PM
 *
 * $Id$
 */
class DBF_UserWork extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_work';
	public $ID = 'uwe_id';
	public $UID = 'u_id';
	public $StartYear = 'uwe_start_year';
	public $StartMonth = 'uwe_start_month';
	public $EndYear = 'uwe_end_year';
	public $EndMonth = 'uwe_end_month';
	public $CompanyID = 'uwe_coid';
	public $CompanyName = 'uwe_coname';
	public $PositionID = 'uwe_poid';
	public $PositionName = 'uwe_poname';
	public $Intro = 'uwe_intro';
	public $CTime = 'uwe_createdate';
	public $UpdateTime = 'uwe_lastupdate';
	public $Status = 'uwe_status';
	public $IsCert = 'uwe_iscert';
	public $IsPublic = 'uwe_ispublic';
	public $ProvinceId = 'uwe_provinceid';
	public $ProvinceName = 'uwe_provincename';
	public $CityId = 'uwe_cityid';
	public $CityName = 'uwe_cityname';
	public $CityNumber = 'uwe_cityno';
	public $_pk = 'uwe_id';
	public $_autoinc = true;
}

/**
 * 培训经历库表结构映射
 *
 * @author miaomin 
 * Mar 7, 2014 1:24:00 PM
 *
 * $Id$
 */
class DBF_UserTrain extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}

	public $_Table = 'tdf_user_train';
	public $ID = 'utr_id';
	public $UID = 'u_id';
	public $StartYear = 'utr_start_year';
	public $StartMonth = 'utr_start_month';
	public $EndYear = 'utr_end_year';
	public $EndMonth = 'utr_end_month';
	public $TrainID = 'utr_trid';
	public $TrainName = 'utr_trname';
	public $PositionID = 'utr_posid';
	public $PositionName = 'utr_posname';
	public $Intro = 'utr_intro';
	public $CTime = 'utr_createdate';
	public $UpdateTime = 'utr_lastupdate';
	public $Status = 'utr_status';
	public $IsCert = 'utr_iscert';
	public $CertUpload = 'utr_certupload';
	public $_pk = 'utr_id';
	public $_autoinc = true;
}

class DBF_InviteCode extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_invite_code';
	public $ID = 'ic_id';
	public $Code = 'ic_code';
	public $Active = 'ic_active';
	public $CreateDate = 'ic_createdate';
	public $ActiveDate = 'ic_activedate';
	public $CTime = 'ctime';
	public $_pk = 'ic_id';
	public $_autoinc = true;
}

class DBF_ProductUse extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_use';
	public $PID = 'p_id';
	public $PUID = 'pu_id';
}

class DBF_ClientLog extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_log_client';
	public $ID = 'log_id';
	public $UID = 'u_id';
	public $CreateDate = 'log_createdate';
	public $Request = 'log_request';
	public $Files = 'log_files';
	public $Response = 'log_response';
	public $UserAgent = 'log_useragent';
	public $Method = 'log_method';
	public $IP = 'log_ip';
	public $_pk = 'log_id';
	public $_autoinc = true;
}

class DBF_LitterSys extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_litter_sys';
	public $ID = 'ls_id';
	public $Title = 'ls_title';
	public $Contents = 'ls_contents';
	public $Date = 'ls_date';
	public $Time = 'ls_time';
	public $From = 'ls_from';
	public $_pk = 'ls_id';
	public $_autoinc = true;
}

class DBF_LitterSys_Index extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_litter_sys_index';
	public $LitterID = 'ls_id';
	public $UserID = 'u_id';
	public $isRead = 'lsi_isread';
}

class DBF_LitterUser extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_litter_user';
	public $ID = 'lu_id';
	public $Title = 'lu_title';
	public $Contents = 'lu_conntents';
	public $Date = 'lu_date';
	public $Time = 'lu_time';
	public $From = 'lu_from';
	public $To = 'lu_to';
	public $DelFrom = 'lu_from_del';
	public $DelTo = 'lu_to_del';
	public $IsRead = 'lu_isread';
	public $_pk = 'lu_id';
	public $_autoinc = true;
}

class DBF_LogRTrans extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_log_rtrans';
	public $ID = 'rl_id';
	public $UserID = 'u_id';
	public $Bef_RC = 'rl_bef_rc';
	public $Aft_RC = 'rl_aft_rc';
	public $Bef_RCav = 'rl_bef_rcav';
	public $Aft_RCav = 'rl_aft_rcav';
	public $RCoin = 'rl_rc';
	public $Type = 'rl_type';
	public $DealType = 'rl_dealtype';
	public $DealID = 'rl_dealid';
	public $AddDate = 'rl_adddate';
	public $CTime = 'ctime';
	public $_pk = 'rl_id';
	public $_autoinc = true;
}

class DBF_LogVTrans extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_log_vtrans';
	public $Id = 'vl_id';
	public $UserID = 'u_id';
	public $Bef_VC = 'vl_bef_vc';
	public $Aft_VC = 'vl_aft_vc';
	public $Bef_VCav = 'vl_bef_vcav';
	public $Aft_VCav = 'vl_aft_vcav';
	public $VCoin = 'vl_vc';
	public $Type = 'vl_type';
	public $DealType = 'vl_dealtype';
	public $DealID = 'vl_dealid';
	public $AddDate = 'vl_adddate';
	public $CTime = 'ctime';
	public $_pk = 'vl_id';
	public $_autoinc = true;
}

class DBF_LogUserDownload extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_log_userdownload';
	public $ID = 'lud_id';
	public $ProductID = 'p_id';
	public $UserID = 'u_id';
	public $LogDate = 'lud_date';
	public $LogTime = 'lud_time';
	public $IP = 'lud_ip';
	public $_pk = 'lud_id';
	public $_autoinc = true;
}

class DBF_LogUserLogin extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_log_userlogin';
	public $ID 		= 'lul_id';
	public $UserID 	= 'u_id';
	public $LogDate = 'lul_date';
	public $LogTime = 'lul_time';
	public $IP 		= 'lul_ip';
	public $Type 	= 'lul_type';
	public $_pk 	= 'lul_id';
	public $_autoinc = true;
}

class DBF_Product extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product';
	public $ID = 'p_id';
	public $Name = 'p_name';
	public $Creater = 'p_creater';
	public $Cate_1 = 'p_cate_1';
	public $Cate_2 = 'p_cate_2';
	public $Cate_3 = 'p_cate_3';
	public $Cate_4 = 'p_cate_4';
	public $Cover = 'p_cover';
	public $Cover_ID = 'p_cover_id';
	public $Price = 'p_price';
	public $VPrice = 'p_vprice';
	public $Tags = 'p_tags';
	public $Intro = 'p_intro';
	public $Author = 'p_author';
	public $CreateDate = 'p_createdate';
	public $CreateTime = 'p_createtime';
	public $LastUpdate = 'p_lastupdate';
	public $LastUpdateTime = 'p_lastupdatetime';
	public $Downs = 'p_downs';
	public $Downs_disp = 'p_downs_disp';
	public $Views = 'p_views';
	public $Views_disp = 'p_views_disp';
	public $Score = 'p_score';
	public $Comments = 'p_comments';
	public $Photos = 'p_photos';
	public $Dispweight = 'p_dispweight';
	public $Slabel = 'p_slabel';
	public $ProductType = 'p_producttype';
	public $LicType = 'p_lictype';
	public $License = 'p_license';
	public $DownloadLimit = 'p_downloadlimit';
	public $IsFormal = 'p_formal';
	public $IsChoice = 'p_choice';
	public $Source = 'p_source';
	public $Purchase = 'p_purchase';
	public $Purchase_disp = 'p_purchase_disp';
	public $Ctprime = 'p_ctprime';
	public $Verify = 'p_verify';
	public $VerifyUid = 'p_vfy_uid';
	public $VerifyUname = 'p_vfy_uname';
	public $VerifyDate = 'p_vfy_date';
	public $Favors = 'p_favors';
	public $Ctime = 'ctime';
	public $MainFile = 'p_mainfile';
	public $MainFile_disp = 'p_mainfile_disp';
	public $Dvs='p_dvs';
	public $Dvs_createtool='p_dvs_createtool';
	public $Dvs_pfid		='p_dvs_pfid';
	public $Zans = 'p_zans';
	public $Awards = 'p_awards';
	public $Oss = 'p_oss';
	public $Count = 'p_count_kc';
	public $DesignPrice = 'p_price_design';
	public $Diy_ID = 'p_diy_id';
	public $MainType = 'p_maintype';
	public $Image = 'p_image';
	public $BelongPid = 'p_belongpid';
	public $PropIdSpec = 'p_propid_spec';
	public $PropNameSpec = 'p_propname_spec';
	public $DiyCateCid = 'p_diy_cate_cid';
	public $Cate = 'p_cate';
	public $Relation = 'p_relation';
	public $Mini = 'p_mini';
	public $UnitName = 'p_unitname';
	public $WaterProofId = 'p_wpid';
    public $OnSaleIntro = 'p_onsaleintro';
    public $p_key = 'p_key';
	public $_pk = 'p_id';
    public $_autoinc = true;
	public $Mintro = 'p_introm';
}

class DBF_ProductCategory extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_cate';
	public $ID = 'pc_id';
	public $Name = 'pc_name';
	public $Remark = 'pc_remark';
	public $Count = 'pc_count';
	public $DispWeight = 'pc_dispweight';
	public $Slabel = 'pc_slabel';
	public $Ptype='pc_type';
	public $ParentID = 'pc_parentid';
	public $CT_SubTypeName = 'pc_ct_subtypename';
    public $tdk_title ='tdk_title';
    public $tdk_keywords ='tdk_keywords';
    public $tdk_description ='tdk_description';
    public $icon ='pc_icon';
	public $CTime = 'ctime';
	public $_pk = 'pc_id';
	public $_autoinc = true;
}

class DBF_ProductCreateTool extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_createtool';
	public $ID = 'pct_id';
	public $Name = 'pct_name';
	public $Remark = 'pct_remark';
	public $Ext = 'pct_ext';
	public $DispWeight = 'pct_dispweight';
	public $PCateID = 'pc_id';
	public $SubType = 'pct_subtype';
	public $HasSubType = 'pct_hassubtype';
	public $Prime = 'pct_prime';
	public $CTime = 'ctime';
	public $_pk = 'pct_id';
	public $_autoinc = true;
}

class DBF_ProductCreateToolIndex extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_createtool_index';
	public $PCTID = 'pct_id';
	public $PCTSubID = 'pct_subid';
}

class DBF_ProductFile extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_file';
	public $ID = 'pf_id';
	public $FileName = 'pf_filename';
	public $OriginalName = 'pf_originalname';
	public $FileSize = 'pf_filesize';
	public $FileSize_disp = 'pf_filesize_disp';
	public $Uploader = 'pf_uploader';
	public $CreateDate = 'pf_createdate';
	public $LastUpdate = 'pf_lastupdate';
	public $CreateTime = 'pf_createtime';
	public $LastUpdateTime = 'pf_lastupdatetime';
	public $CreateTool = 'pf_createtool';
	public $CTVersion = 'pf_ctversion';
	public $SubCreateTool = 'pf_subcreatetool';
	public $SubCTVersion = 'pf_subctversion';
	public $Isfree = 'pf_isfree';
	public $Remark = 'pf_remark';
	public $Server = 'pf_server';
	public $Path = 'pf_path';
	public $Ext = 'pf_ext';
	public $Downloads = 'pf_downloads';
	public $ProductID = 'p_id';
	public $CTime = 'ctime';
	public $_pk = 'pf_id';
	public $_autoinc = true;
}

class DBF_ProductPermit extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_permit';
	public $ID = 'pp_id';
	public $Name = 'pp_name';
	public $Type = 'pp_type';
	public $Url = 'pp_url';
	public $LegalCodeUrl = 'pp_legalcodeurl';
	public $DispWeight = 'pp_dispweight';
	public $CTime = 'ctime';
	public $_pk = 'pp_id';
	public $_autoinc = true;
}

class DBF_ProductModel extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_model';
	public $ProductID = 'p_id';
	public $Geometry = 'pm_geometry';
	public $Mesh = 'pm_mash';
	public $Vertices = 'pm_vertices';
	public $IsTexture = 'pm_istexture';
	public $IsMaterials = 'pm_ismaterials';
	public $IsAnimation = 'pm_isanimation';
	public $IsRigged = 'pm_isrigged';
	public $IsUVLayout = 'pm_isuvlayout';
	public $IsRendered = 'pm_isrendered';
	public $ModelFormats = 'pm_modelformats';
	public $IsOverView = 'pm_isoverview';
	public $IsVR = 'pm_isvr';
	public $IsAR = 'pm_isar';
	public $UnWrappedUVs = 'pm_unwrappeduvs';
	public $CTime = 'ctime';
	public $IsMorenode = 'pm_ismorenode';
	public $IsPrint = 'pm_isprint';
	public $WebPF = 'pm_webpf';
	public $IsGM = 'pm_isgm';
	public $IsPrintReady = 'pm_isprready';
	public $IsPrintModel = 'pm_isprmodel';
	public $_pk = 'p_id';
	public $_autoinc = true;
}

class DBF_ProductPhoto extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_photo';
	public $ID = 'pp_id';
	public $OriginalName = 'pp_originalname';
	public $FileName = 'pp_filename';
	public $Path = 'pp_path';
	public $CreateDate = 'pp_createdate';
	public $Title = 'pp_title';
	public $Remark = 'pp_remark';
	public $DispWeight = 'pp_dispweight';
	public $Group = 'pp_group';
	public $Sequence = 'pp_sequence';
	public $ProductID = 'p_id';
	public $CTime = 'ctime';
	public $_pk = 'pp_id';
	public $_autoinc = true;
}

class DBF_ProductReality extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_reality';
	public $ID = 'r_id';
	public $ProductID = 'p_id';
	public $FileName = 'r_filename';
	public $Type = 'r_type';
	public $Path = 'r_path';
	public $Mdname = 'r_mdname';
	public $CreateDate = 'r_createdate';
	public $LastUpdate = 'r_lastupdate';
	public $Enable = 'r_enable';
	public $Ctime = 'ctime';
	public $_pk = 'r_id';
	public $_autoinc = true;
}

class DBF_ProductSource extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_source';
	public $ID = 'ps_id';
	public $Name = 'ps_name';
	public $Url = 'ps_url';
	public $_pk = 'ps_id';
	public $_autoinc = true;
}

class DBF_ProductTags extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_tags';
	public $ID = 'pt_id';
	public $Name = 'pt_name';
	public $Count = 'pt_count';
	public $IsHot = 'pt_ishot';
	public $CTime = 'ctime';
	public $_pk = 'pt_id';
	public $_autoinc = true;
}

class DBF_ProductTagsIndex extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_product_tags_index';
	public $TagsID = 'pt_id';
	public $ProductID = 'p_id';
}

class DBF_Setting extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_setting';
	public $Attribute = 'attribute';
	public $Value = 'value';
	public $CTime = 'ctime';
}

class DBF_UserAction extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_action';
	public $ID = 'ua_id';
	public $Parent = 'ua_parent';
	public $Code = 'ua_code';
	public $Relevance = 'ua_relevance';
	public $_pk = 'ua_id';
	public $_autoinc = true;
}

class DBF_UserActive extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_active';
	public $UserID = 'u_id';
	public $Code = 'uc_code';
	public $Active = 'uc_active';
	public $CreateDate = 'uc_createdate';
	public $ActiveDate = 'uc_activedate';
	public $CTime = 'ctime';
	public $_pk = 'u_id';
}

class DBF_UserDeals extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table 			= 'tdf_user_deals';
	public $ID 					= 'ud_id';
	public $Buyer 			= 'ud_buyer';
	public $Seller 			= 'ud_seller';
	public $PID 				= 'ud_pid';
	public $ProductPrice= 'ud_pprice';
	public $DealDate 		= 'ud_dealdate';
	public $IPAddress 	= 'ud_ipaddress';
	public $Status 			= 'ud_status';
	public $CTime 			= 'ctime';
	public $_pk 				= 'ud_id';
	public $_autoinc = true;
}

class DBF_UserDealsVcoin extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_deals_vcoin';
	public $ID = 'ud_id';
	public $Buyer = 'ud_buyer';
	public $Seller = 'ud_seller';
	public $PID = 'ud_pid';
	public $ProductPrice = 'ud_pprice';
	public $DealDate = 'ud_dealdate';
	public $IPAddress = 'ud_ipaddress';
	public $Status = 'ud_status';
	public $CTime = 'ctime';
	public $_pk = 'ud_id';
	public $_autoinc = true;
}

class DBF_UserDownloads extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_downloads';
	public $ID = 'udl_id';
	public $UserID = 'u_id';
	public $ProductID = 'p_id';
	public $Author = 'p_author';
	public $VCoinPaid = 'p_vp';
	public $RCoinPaid = 'p_rp';
	public $DownDate = 'udl_downdate';
	public $IP = 'udl_ipaddress';
	public $Status = 'udl_status';
	public $_pk = 'ud_id';
	public $_autoinc = true;
}

class DBF_UserGetCash extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_getcash';
	public $ID = 'ug_id';
	public $UserID = 'u_id';
	public $Amount = 'ug_amount';
	public $AppDate = 'ug_appdate';
	public $ApplyDate = 'ug_applydate';
	public $Admin = 'ug_admin';
	public $Remark = 'ug_remark';
	public $Status = 'ug_status';
	public $CTime = 'ctime';
	public $_pk = 'ug_id';
	public $_autoinc = true;
}

class DBF_UserInvite extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_invite';
	public $ID = 'ui_id';
	public $Mail = 'ui_mail';
	public $Url = 'ui_url';
	public $Code = 'ui_code';
	public $Status = 'ui_status';
	public $SendDate = 'ui_senddate';
	public $AllowDate = 'ui_allowdate';
	public $CTime = 'ctime';
	public $_pk = 'ui_id';
	public $_autoinc = true;
}

class DBF_UserOwnProduct extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_own_product';
	public $UserID = 'u_id';
	public $ProductID = 'p_id';
	public $Creater = 'p_creater';
	public $CTime = 'ctime';
	public $Type = 'uop_type';
}

class DBF_UserPrepaid extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_prepaid';
	public $ID = 'up_id';
	public $UserID = 'up_uid';
	public $Amount = 'up_amount';
	public $Efee   = 'up_efee';
	public $PayType = 'up_paytype';
	public $DealDate = 'up_dealdate';
	public $IPAddress = 'up_ipaddress';
	public $Status = 'up_status';
	public $OrderID = 'up_orderid';
	public $OrderBackID = 'up_orderbackid';
	public $OrderBackTime = 'up_orderbacktime';
	public $ProductID = 'up_productid';
	public $Type = 'up_type';
	public $Express 	= 'up_express';
	public $Uaid 		= 'up_uaid';
	public $Address 	= 'up_address';
	public $Zipcode 	= 'up_zipcode';
	public $Mobile 		= 'up_mobile';
	public $Phone		= 'up_phone';
	public $Addressee 	= 'up_addressee';
	public $DoneStatus	= 'up_done_status';
	public $DoneUser	= 'up_done_user';
	public $DoneTime	= 'up_done_time';
	public $ExpressName = 'up_expressname';
	public $Expressid	= 'up_expressid';
	public $CTime 		= 'ctime';
	public $Delsign 	= 'delsign';
	public $AdminAid 	= 'admin_aid';
	public $AmountAccount 	='up_amount_account';
	public $AmountCoupon	='up_amount_coupon';
	public $AmountTotal 	='up_amount_total';
	public $OrderidSuffix = 'up_orderid_suffix';
	public $OrderidNew	='up_orderid_new';
	public $OrderBz		='up_order_bz';
	public $Cmbpaydate = 'up_cmbpay_date';
    public $Cmbpayorderid   = 'up_cmbpay_orderid';
	public $Agentuserid     = 'up_agent_userid';
	public $Thirdorderid    = 'third_orderid';
	public $Vrsign        = 'vr_sign';
    public $Pid = 'p_id';
	public $_pk 		='up_id';
	public $_autoinc = true;
}

class DBF_UserPrepaidDetail extends DBFCreater //保存订单商品快照
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	public $_Table 	= 'tdf_user_prepaid_detail';
	public $ID 		= 'id';
	public $UpID 	= 'up_id';
	public $ProductInfo= 'up_product_info';
	public $Isdel 	= 'isdel';
	public $CTime 	= 'ctime';
	public $_pk 	= 'id';
	public $_autoinc = true;
}


class DBF_UserReset extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_reset';
	public $UserID = 'u_id';
	public $Code = 'ur_code';
	public $Active = 'ur_active';
	public $CreateDate = 'ur_createdate';
	public $ActiveDate = 'ur_activedate';
	public $IP = 'ur_ip';
	public $_pk = 'u_id';
}

class DBF_Users extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_users';
	public $ID = 'u_id';
	public $Pass = 'u_pass';
	public $Salt = 'u_salt';
	public $EMail = 'u_email';
	public $Avatar = 'u_avatar';
	public $DispName = 'u_dispname';
	public $RealName = 'u_dispname';
	public $Type = 'u_type';
	public $Level = 'u_level';
	public $Title = 'u_title';
	public $CreateDate = 'u_createdate';
	public $LastLogin = 'u_lastlogin';
	public $Status = 'u_status';
	public $Permission = 'u_permission';
	public $LastIP = 'u_lastip';
	//经验值
	public $Exp = 'u_exp';
	//可用经验值
	public $ExpAV = 'u_exp_av';
	//手机区号
	public $MobPre = 'u_mob_pre';
	//手机号
	public $MobNo = 'u_mob_no';
	//邮箱验证用户
	public $MailVerify = 'u_mail_verify';
	//身份证验证用户
	public $IddVerify = 'u_idd_verify';
	//删除标记
	public $Del = 'u_del';
	//注销标记
	public $Logout = 'u_logout';
	//用户注册来源
	public $From = 'u_from';
	public $LastLoginTime = 'u_lastlogintime';
	//标识用户ID的唯一字串
	public $Identifier = 'u_identifier';
	//用户永久登录标识
	public $Token = 'u_token';
	//用户永久登录的过期时间
	public $Timeout = 'u_timeout';
	//认证设计师
	public $Group = 'u_group';
	//商户成员
	public $MocUID = 'u_mocuid';
	public $_pk = 'u_id';
	public $_autoinc = true;
}

class DBF_UserMailVaildate extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_mailvaildate';
	public $ID = 'umv_id';
	public $UserID = 'u_id';
	public $Code = 'umv_code';
	public $Type = 'umv_type';
	public $CrteateTime = 'umv_createtime';
	public $Expired = 'umv_expired';
	public $_pk = 'umv_id';
	public $_autoinc = true;
}


class DBF_User_Account extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_account';
	public $ID = 'u_id';
	public $Vcoin = 'u_vcoin';
	public $Vcoin_av = 'u_vcoin_av';
	public $Rcoin = 'u_rcoin';
	public $Rcoin_av = 'u_rcoin_av';
	public $_pk = 'u_id';
	public $_autoinc = true;
}

//2014-08-25 用户地址
class DBF_UserAddress extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	public $_Table = 'tdf_user_address';
	public $ID = 'ua_id';
	public $UserID = 'u_id';
	public $Addressee = 'ua_addressee';
	public $Province = 'ua_province';
	public $City = 'ua_city';
	public $Region = 'ua_region';
	public $Address = 'ua_address';
	public $ZipCode = 'ua_zipcode';
	public $Mobile = 'ua_mobile';
	public $PhonePre = 'ua_phonepre';
	public $Phone = 'ua_phone';
	public $PhoneExt = 'ua_phoneext';
	public $IsDefault = 'ua_isdefault';
	public $IsRemove = 'ua_isremove';
	public $_pk = 'ua_id';
	public $_autoinc = true;
}


class DBF_UserCart extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_cart';
	public $ID = 'uc_id';
	public $ProductID = 'p_id';
	public $UserID = 'u_id';
	public $Count = 'uc_count';
	public $ProductType = 'uc_producttype';
	public $IsReal ='uc_isreal';
	public $Lastupdate = 'uc_lastupdate';
	public $Ctime = 'uc_ctime';
	public $IsBind = 'uc_isbind';
	public $BindsIds = 'uc_bindids';
	public $MasterId = 'uc_masterid';
	public $HandleUc = 'uc_handleuc';
	
	public $_pk = 'uc_id';
	public $_autoinc = true;
	
}

class DBF_PayType extends DBFCreater{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table			='tdf_paytype';
	public $PtID				='pt_id';
	public $PaytypeID		='paytypeid';
	public $PayName			='payname';
	public $PayGateway	='paygateway';
	public $PayGroup		='paygroup';
	public $PayMethcode	='paymethodcode';
	public $BankCode 		='bankcode';
	public $PayRemark 	='payremark';
	public $IsUsed			='isused';
	public $Icon 				='icon';
	public $Sort				='sort';
	public $Ctime				='ctime';
}

class DBF_UserFavor extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table = 'tdf_user_favor';
	public $UID = 'u_id';
	public $UFID = 'uf_id';
	public $UFTYPE = 'uf_type';
	public $CreateDate = 'uf_createdate';
}

class DBF_Article extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table 	= 'tdf_article';
	public $Title 	= 'title';
	public $Summary = 'summary';
	public $Content = 'content';
	public $Showtime= 'showtime';
	public $U_id 		= 'u_id';
	public $Isdel 	= 'isdel';
	public $Status 	= 'status';
	public $_pk			= 'id';
	public $Ctime		= 'ctime';
}


class DBF_Book extends DBFCreater
{
	private static $instance = null;
	public static function construct()
	{
		if(!(self::$instance instanceof self)) { self::$instance = new self(); }
		return self::$instance;
	}
	
	public $_Table 			= 'tdf_book';
	public $Title 			= 'title';
	public $Bookpic  		= 'bookpic';
	public $Author  		= 'author';
	public $Publisher 	= 'publisher';
	public $Publishtime	= 'publishtime';
	public $Price				= 'price';
	public $Summary 		= 'summary';
	public $Content 		= 'content';
	public $U_id 				= 'u_id';
	public $Isdel 			= 'isdel';
	public $Status 			= 'status';
	public $sort 				= 'sort';
	public $_pk					= 'id';
	public $Ctime				= 'ctime';
}

class DBF_ClientVersion extends DBFCreater
{
	public $_Table 		= 'tdf_client_version';
	public $ID 			= 'cv_id';
	public $VERSION 	= 'cv_version';
	public $LASTUPDATE 	= 'cv_lastupdate';
	public $DOWNURL 	= 'cv_downurl';
	public $LEVEL 		= 'cv_level';
	public $TYPE		='cv_type';
	public $INFO		='cv_info';
	public $_pk 		= 'cv_id';
	public $_autoinc 	= true;
}

class DBF_UserToken extends DBFCreater
{
	public $_Table = 'tdf_user_token';
	public $ID = 'u_id';
	public $TOKEN = 'ut_token';
	public $EXPIRE = 'ut_expire';
	public $LASTUPDATE = 'ut_lastupdate';
	public $IPADDRESS = 'ut_ipaddress';
	public $_pk = 'u_id';
}

class DBF_UserAuth extends DBFCreater
{
	public $_Table 		= 'tdf_user_auth';
	public $ID 			= 'id';
	public $UID 		= 'u_id';
	public $AUTHTYPE 	= 'AuthType';
	public $NickName 	= 'NickName';
	public $Headimgurl = 'Headimgurl';
	public $OPENID 		= 'OpenId';
	public $ACCESSTOKEN	= 'Access_Token';
	public $CREATETIME	= 'CreateTime';
	public $DELSIGN		= 'DelSign';
	public $_pk = 'id';
}

class DBF_AreaInfo extends DBFCreater
{
	public $_Table = 'tdf_areainfo';
	public $ID = 'ai_id';
	public $Name = 'ai_name';
	public $ParentID = 'ai_parentid';
	public $_pk = 'ai_id';
	public $_autoinc = true;
}

class DBF_DiyCate extends DBFCreater{
	public $_Table 	= 'tdf_diy_cate';
	public $ID 		= 'cid';
	public $Cname 	= 'cate_name';
	public $Cicon	= 'cate_icon';
	public $Cgroup	= 'cate_group';
	public $Sort	= 'sort';
	public $UID		='u_id';
	public $INTRO1	='intro1';
	public $INTRO2	='intro2';
	public $TAGS	='tags';
	public $Delsign	= 'delsign';
	public $Ctime	= 'ctime';
	public $P_slabel= 'p_slabel';
	public $_pk 	= 'cid';	
	public $_autoinc = true;
}

class DBF_DiyUnit extends DBFCreater{
	public $_Table 		= 'tdf_diy_unit';
	public $ID 			= 'id';
	public $Uname 		= 'unit_name';
	public $Ushowname	= 'unit_showname';
	public $Ucover		= 'unit_cover';
	public $Uvalue		= 'unit_value';
	public $Uprice		= 'unit_price';
	public $Sort		= 'sort';
	public $Delsign		= 'delsign';
	public $Ctime		= 'ctime';
	public $_pk 		= 'id';
	public $_autoinc 	= true;
}


class DBF_UserDiy extends DBFCreater{
	public $_Table 		= 'tdf_user_diy';
	public $ID 			= 'id';
	public $Uid 		= 'u_id';
	public $Did    		= 'd_id';
	public $Delsign		= 'delsign';
	public $Ctime		= 'ctime';
	public $_pk 		= 'id';
	public $_autoinc = true;
}

class DBF_PrinterMaterial extends DBFCreater
{
	public $_Table = 'tdf_printer_material';
	public $ID = 'pma_id';
	public $Name = 'pma_name';
	public $ParentID = 'pma_parentid';
	public $ParentName = 'pma_parentname';
	public $StartPrice = 'pma_startprice';
	public $UnitPrice = 'pma_unitprice';
	public $Density = 'pma_density';
	public $Factor = 'pma_factor';
	public $MaxLength = 'pma_maxlength';
	public $MaxWidth = 'pma_maxwidth';
	public $MaxHeight = 'pma_maxheight';
	public $Image = 'pma_image';
	public $CreateTime = 'pma_createtime';
	public $LastUptime = 'pma_lastuptime';
	public $RationRef = 'pma_rationref';
	public $SinglePartFee = 'pma_singlepartfee';
	public $PriceHour = 'pma_pricehour';
	public $FabSpeed = 'pma_fabspeed';
	public $Weight = 'pma_weight';
	public $Color = 'pma_color';
	public $MinLength = 'pma_minlength';
	public $MinWidth = 'pma_minwidth';
	public $MinHeight = 'pma_minheight';
	public $Feature = 'pma_feature';
	public $Application = 'pma_application';
	public $Convex = 'pma_convex_factor';
	public $Wastage = 'pma_wastage';
	public $Cover = 'pma_cover';
	public $CoverId = 'pma_cover_id';
	public $_pk = 'pma_id';
	public $_autoinc = true;
}

class DBF_PrinterMaterialPhoto extends DBFCreater
{
	public $_Table = 'tdf_printer_material_photo';
	public $ID = 'pmp_id';
	public $PMID = 'pm_id';
	public $ORIGINALNAME = 'pmp_originalname';
	public $FILENAME = 'pmp_filename';
	public $PATH = 'pmp_path';
	public $CREATEDATE = 'pmp_createdate';
	public $TITLE = 'pmp_title';
	public $REMARK = 'pmp_remark';
	public $DISPWEIGHT = 'pmp_dispweight';
	public $GROUP = 'pmp_group';
	public $SEQUENCE = 'pmp_sequence';
	public $CTIME = 'ctime';
	public $_pk = 'pmp_id';
	public $_autoinc = true;
}


//------------------------------
class DBF
{
	/**
	 * @var DBF_InviteCode
	 */
	public $InviteCode;

	/**
	 * @var DBF_LogRTrans
	 */
	public $LogRTrans;

	/**
	 * @var DBF_LogVTrans
	 */
	public $LogVTrans;

	/**
	 * @var DBF_LogUserDownload
	 */
	public $LogUserDownload;
	
	/**
	 * @var DBF_LogUserLogin
	 */
	public $LogUserLogin;
	
	/**
	 * @var DBF_Product
	 */
	public $Product;

	/**
	 * @var DBF_ProductCategory
	 */
	public $ProductCategory;

	/**
	 * @var DBF_ProductCreateTool
	 */
	public $ProductCreateTool;

	/**
	 * @var DBF_ProductCreateToolIndex
	 */
	public $ProductCreateToolIndex;

	/**
	 * @var DBF_ProductFile
	 */
	public $ProductFile;
	
	/**
	 * @var DBF_ProductPermit
	 */
	public $ProductPermit;

	/**
	 * @var DBF_ProductModel
	 */
	public $ProductModel;

	/**
	 * @var DBF_ProductPhoto
	 */
	public $ProductPhoto;

	/**
	 * @var DBF_ProductReality
	 */
	public $ProductReality;

	/**
	 * @var DBF_ProductSource
	 */
	public $ProductSource;

	/**
	 * @var DBF_ProductTags
	 */
	public $ProductTags;

	/**
	 * @var DBF_ProductTagsIndex
	 */
	public $ProductTagsIndex;

	/**
	 * @var DBF_Setting
	 */
	public $Setting;

	/**
	 * @var DBF_UserAction
	 */
	public $UserAction;

	/**
	 * @var DBF_UserActive
	 */
	public $UserActive;

	/**
	 * @var DBF_UserCart
	 */
	public $UserCart;
	
	
	/**
	 * @var DBF_PayType
	 * 
	 **/
	public $PayType;
	
	
	/**
	 * @var DBF_UserDeals
	 */
	public $UserDeals;
	
	/**
	 * @var DBF_UserDealsVcoin
	 */
	public $UserDealsVcoin;

	/**
	 * @var DBF_UserDownloads
	 */
	public $UserDownloads;

	/**
	 * @var DBF_UserGetCash
	 */
	public $UserGetCash;

	/**
	 * @var DBF_UserInvite
	 */
	public $UserInvite;

	/**
	 * @var DBF_UserOwnProduct
	 */
	public $UserOwnProduct;

	/**
	 * @var DBF_UserPrepaid
	 */
	public $UserPrepaid;
	
	/**
	 * @var DBF_UserPrepaidDetail
	 */
	public $UserPrepaidDetail;
	
	/**
	 * @var DBF_UserReset
	 */
	public $UserReset;

	/**
	 * @var DBF_Users
	 */
	public $Users;
	
	/**
	 * @var DBF_User_Account
	 */
	public $UserAccount;
	
	/**
	 * @var DBF_UserAddress
	 */
	public $UserAddress;
	
	/**
	 * @var DBF_UserFavor
	 */
	public $UserFavor;

	/**
	 * @var DBF_Article
	 */
	public $Article;
	
	/**
	 * @var DBF_Book
	 */
	public $Book;
	
	/**
	 * @var DBF_ClientLog
	 */
	public $LogClient;
	
	/**
	 * @var DBF_ProductUse
	 */
	public $ProductUse;
	
	/**
	 * @var DBF_UserEdu
	 */
	public $UserEdu;
	
	/**
	 * @var DBF_UserWork
	 */
	public $UserWork;
	
	/**
	 * @var DBF_UserTrain
	 */
	public $UserTrain;
	
	/**
	 * @var DBF_UserZan
	 */
	public $UserZan;
	
	/**
	 * @var DBF_UserTags
	 */
	public $UserTags;
	
	/**
	 * @var DBF_UserTagsIndex
	 */
	public $UserTagsIndex;
	
	/**
	 * @var DBF_AwardInfo
	 */
	public $AwardInfo;
	
	/**
	 * @var DBF_ProductAward
	 */
	public $ProductAward;
	
	/**
	 * @var DBF_UserRelation
	 */
	public $UserRelation;
	
	/**
	 * @var DBF_UserFollowing
	 */
	public $UserFollowing;
	
	/**
	 * @var DBF_UserFollower
	 */
	public $UserFollower;
	
	/**
	 * @var DBF_ClientVersion
	 */
	public $ClientVersion;
	
	/**
	 * @var DBF_JobQueue
	 */
	public $JobQueue;
	
	/**
	 * @var DBF_ProductWebgl
	 */
	public $ProductWebgl;
	
	/**
	 * @var DBF_UserToken
	 */
	public $UserToken;
	
	/**
	 * @var DBF_UserAuth
	 */
	public $UserAuth;
	
	/**
	 * @var DBF_ProductWebglCapture
	 */
	public $ProductWebglCapture;
	
	/**
	 * @var DBF_AreaInfo
	 */
	public $AreaInfo;
	
	/**
	 * @var DBF_DiyCate
	 */
	public $DiyCate;
	
	/**
	 * @var DBF_DiyUnit
	 */
	public $DiyUnit;
	
	/**
	 * @var DBF_UserDiy
	 */
	public $UserDiy;
	
	/**
	 * @var DBF_PrinterMaterial
	 */
	public $PrinterMaterial;
	/**
	 * @var DBF_PrinterMaterialPhoto
	 */
	public $PrinterMaterialPhoto;
	
	public $ProductPrintModel;
	
	public $ProductPMMaterial;
	
	public $UserPrintModel;
	
	public $ProductPMFormula;
	
	public $ProductPMMaterialFilter;
	
	public $InfoProductType;
	
	public $InfoProductProp;
	
	public $InfoPropVal;
	
	public $UserCustomize;
	
	public $SalesReport;
	
	public $ProductWaterProof;
	
	public $InfoSPType;
	
	public $SPMain;
	
	public $SPProduct;
	
	public $SPProp;
	
	public $SPConfig;
	
	function __construct()
	{
		$this->AreaInfo 		= new DBF_AreaInfo();
		$this->InviteCode = new DBF_InviteCode();
		$this->LogRTrans = new DBF_LogRTrans();
		$this->LogVTrans = new DBF_LogVTrans();
		$this->LogUserDownload = new DBF_LogUserDownload();
		$this->LogUserLogin = new DBF_LogUserLogin();
		$this->Product = new DBF_Product();
		$this->ProductCategory = new DBF_ProductCategory();
		$this->ProductCreateTool = new DBF_ProductCreateTool();
		$this->ProductCreateToolIndex = new DBF_ProductCreateToolIndex();
		$this->ProductFile = new DBF_ProductFile();
		$this->ProductPermit = new DBF_ProductPermit();
		$this->ProductModel = new DBF_ProductModel();
		$this->ProductPhoto = new DBF_ProductPhoto();
		$this->ProductReality = new DBF_ProductReality();
		$this->ProductSource = new DBF_ProductSource();
		$this->ProductTags = new DBF_ProductTags();
		$this->ProductTagsIndex = new DBF_ProductTagsIndex();
		$this->Setting = new DBF_Setting();
		$this->UserAction = new DBF_UserAction();
		$this->UserActive = new DBF_UserActive();
		$this->UserCart = new DBF_UserCart();
		$this->PayType = new DBF_PayType();
		$this->UserDeals = new DBF_UserDeals();
		$this->UserDealsVcoin = new DBF_UserDealsVcoin();
		$this->UserDownloads = new DBF_UserDownloads();
		$this->UserGetCash = new DBF_UserGetCash();
		$this->UserInvite = new DBF_UserInvite();
		$this->UserOwnProduct = new DBF_UserOwnProduct();
		$this->UserPrepaid = new DBF_UserPrepaid();
		$this->UserReset = new DBF_UserReset();
		$this->Users = new DBF_Users;
		$this->UserMailVaildate = new DBF_UserMailVaildate();
		$this->UserFavor = new DBF_UserFavor();
		$this->Article = new DBF_Article;
		$this->Book = new DBF_Book;
		$this->UserAccount =new DBF_User_Account();
		$this->LogClient = new DBF_ClientLog();
		$this->ProductUse = new DBF_ProductUse();
		$this->UserEdu = new DBF_UserEdu();
		$this->UserWork = new DBF_UserWork();
		$this->UserTrain = new DBF_UserTrain();
		$this->UserZan = new DBF_UserZan();
		$this->UserTags = new DBF_UserTags();
		$this->UserTagsIndex = new DBF_UserTagsIndex();
		$this->AwardInfo = new DBF_AwardInfo();
		$this->ProductAward = new DBF_ProductAward();
		$this->ClientVersion = new DBF_ClientVersion();
		$this->UserRelation = new DBF_UserRelation();
		$this->UserFollowing = new DBF_UserFollowing();
		$this->UserFollower = new DBF_UserFollower();
		$this->JobQueue = new DBF_JobQueue();
		$this->ProductWebgl = new DBF_ProductWebgl();
		$this->ProductWebglCapture = new DBF_ProductWebglCapture();
		$this->UserToken = new DBF_UserToken();
		$this->UserAuth = new DBF_UserAuth();
		$this->UserAddress = new DBF_UserAddress();
		$this->UserPrepaidDetail = new DBF_UserPrepaidDetail();
		$this->DiyCate = new DBF_DiyCate();
		$this->DiyUnit = new DBF_DiyUnit();
		$this->UserDiy = new DBF_UserDiy();
		$this->PrinterMaterial = new DBF_PrinterMaterial();
		$this->PrinterMaterialPhoto = new DBF_PrinterMaterialPhoto();
		$this->ProductPrintModel = new DBF_ProductPrintModel();
		$this->ProductPMMaterial = new DBF_ProductPMMaterial(); 
		$this->UserPrintModel = new DBF_UserPrintModel();
		$this->ProductPMFormula = new DBF_ProductPMFormula();
		$this->ProductPMMaterialFilter = new DBF_ProductPMMaterialFilter();
		$this->InfoProductType = new DBF_InfoProductMainType();
		$this->InfoProductProp = new DBF_InfoProductMainProp();
		$this->InfoPropVal = new DBF_InfoProductPropVal();
		$this->UserCustomize = new DBF_UserCustomize();
		$this->SalesReport = new DBF_SalesReport();
		$this->ProductWaterProof = new DBF_ProductWaterProof();
		$this->InfoSPType = new DBF_InfoSPType();
		$this->SPMain = new DBF_SPMain();
		$this->SPProduct = new DBF_SPProduct();
		$this->SPProp = new DBF_SPProp();
		$this->SPConfig = new DBF_SPConf();
	}  	
}
//@formatter: on
?>