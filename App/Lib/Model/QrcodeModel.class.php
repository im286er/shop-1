<?php
/*
*
*    时间：2013-10-15
*
*    说明：Zip压缩包、压缩操作类
*
*/
class QrcodeModel extends Model{

	/**
	 * 功能：生成二维码
	 * @param string $qr_data   手机扫描后要跳转的网址
	 * @param string $qr_level  默认纠错比例 分为L、M、Q、H四个等级，H代表最高纠错能力
	 * @param string $qr_size   二维码图大小，1－10可选，数字越大图片尺寸越大
	 * @param string $save_path 图片存储路径
	 * @param string $save_prefix 图片名称前缀
	 */
	function createQRcode($save_path,$qr_data='PHP QR Code :)',$qr_level='L',$qr_size=4,$save_prefix='qrcode'){
		if(!isset($save_path)) return '';
		//设置生成png图片的路径
		$PNG_TEMP_DIR = & $save_path;
		//导入二维码核心程序
		Vendor ( 'PHPQRcode.phpqrcode');
		//vendor('PHPQRcode.class#phpqrcode');  //注意这里的大小写哦，不然会出现找不到类，PHPQRcode是文件夹名字，class#phpqrcode就代表class.phpqrcode.php文件名
		//检测并创建生成文件夹
		if (!file_exists($PNG_TEMP_DIR)){
			mkdir($PNG_TEMP_DIR);
		}
		$filename = $PNG_TEMP_DIR.'test.png';
		$errorCorrectionLevel = 'L';
		if (isset($qr_level) && in_array($qr_level, array('L','M','Q','H'))){
			$errorCorrectionLevel = & $qr_level;
		}
		$matrixPointSize = 4;
		if (isset($qr_size)){
			$matrixPointSize = & min(max((int)$qr_size, 1), 10);
		}
		if (isset($qr_data)) {
			if (trim($qr_data) == ''){
				die('data cannot be empty!');
			}
			//生成文件名 文件路径+图片名字前缀+md5(名称)+.png
			$filename = $PNG_TEMP_DIR.$save_prefix.md5($qr_data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
			//开始生成
			QRcode::png($qr_data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
		} else {
			//默认生成
			QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);
		}
		if(file_exists($PNG_TEMP_DIR.basename($filename)))
			return basename($filename);
		else
			return FALSE;
	}
}
?>