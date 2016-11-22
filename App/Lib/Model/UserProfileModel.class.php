<?php
class UserProfileModel extends Model {
	protected $tableName = 'user_profile';
	protected $fields = array (
			'u_id',
			'u_name',
			'u_firstname',
			'u_lastname',
			'u_url',
			'u_sig',
			'u_intro',
			'u_bir_y',
			'u_bir_m',
			'u_bir_d',
			'u_country',
			'u_country_name',
			'u_province',
			'u_province_name',
			'u_province_fid',
			'u_city',
			'u_city_name',
			'u_city_no',
			'u_prof',
			'u_idd_type',
			'u_idd_no',
			'u_wx_no',
			'u_qq_no',
			'u_gtalk_no',
			'u_msn_no',
			'u_wb_no',
			'u_disp_location',
			'u_disp_birth',
			'u_position',
			'u_newprof',
			'u_domain',
			'u_visitnum',
			'_pk' => 'u_id' 
	);
}
?>