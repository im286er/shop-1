<?php
/**
 * 用户头像类
 *
 * @author miaomin 
 * Jun 28, 2013 1:53:41 PM
 */
class AvatarAction extends CommonAction {
	
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct ();
		
		// 判断登录
		if (! $this->_isLogin ()) {
			$this->_needLogin ();
		}
	}
	
	/**
	 * 上传头像
	 */
	public function upload() {
		/*
		 * IMG RESIZE SELECT $upload =
		 * 'D:\Zend\WorkSpace\city\tmp_upload\large'; if (move_uploaded_file (
		 * $_FILES ['upl'] ['tmp_name'], $upload . '\\' . $_FILES ['upl']
		 * ['name'] )) { $large_img = '/city/tmp_upload/large/' . $_FILES
		 * ['upl'] ['name']; list ( $width, $height ) = getimagesize ( $upload .
		 * '\\' . $_FILES ['upl'] ['name'] ); echo '{"status":"success","url":"'
		 * . $large_img . '","width":"' . $width . '","height":"' . $height .
		 * '"}'; exit (); }
		 */
		$MD5File16Name = getMD5File16 ( $_FILES ['upl'] [tmp_name] );
		import ( "ORG.Net.UploadFile" );
		$upload = new UploadFile ();
		$upload->uploadReplace = true;
		$upload->maxSize = 3145728; // 头像文件大小限制3M
		$upload->allowExts = array (
				'png',
				'jpg',
				'jpeg',
				'gif'
		); // 头像文件仅支持jpg格式
		$upload->saveRule = $MD5File16Name . '';
		$upload->thumb = true;
		$upload->thumbMaxWidth = '180,96,24';
		$upload->thumbMaxHeight = '180,96,24';
		$genAvatarPath = getSavePathByID ( $this->_session ( 'f_userid' ) );
		// 上传路径
		$upload->savePath = './upload/avatar/' . $genAvatarPath . 'o/';
		// 缩略图上传路径
		$upload->thumbPath = './upload/avatar/' . $genAvatarPath . 's/';
		$upload->thumbPrefix = '180_180_,96_96_,24_24_';
		$upload->thumbSuffix = '';
		// miaomin added@2014.3.18
		$upload->thumbType = 1;
		if (! $upload->upload ()) {
			// TODO
			// AJAX的响应会有一个专门的方法来处理
			echo json_encode ( $upload->getErrorMsg () );
		} else {
			$info = $upload->getUploadFileInfo ();
			$savename = $info [0] ['savename'];
			$savename_arr = explode ( '.', $savename );
			// $info [0] ['thumbname'] = $savename_arr [0] . '_200.' . $savename_arr [1];
			$info [0] ['thumbname'] = '96_96_' . $savename_arr [0] . '.' . $savename_arr [1];
			// $info [0] ['thumbsrc'] = TMP_UPLOAD_PATH . '/avatar/' . $genAvatarPath . 's/' . $info [0] ['thumbname'];
			$info [0] ['thumbsrc'] = TMP_UPLOAD_PATH . '/avatar/' . $genAvatarPath . 's/' . $info [0] ['thumbname'];
			// 保存图片
			$Users = D ( 'Users' );
			$Users->find ( $this->_session ( 'f_userid' ) );
			// $Users->u_avatar = $genAvatarPath . 's/' . $info [0] ['thumbname'];
			$Users->u_avatar = $genAvatarPath . 'o/' . $savename_arr [0] . '.' . $savename_arr [1];
			$Users->save ();
			
			//任务系统 上传头像增加积分
			//$HJ = new HookJobsModel ();
			//$HJ->run ( $this->_session ( 'f_userid' ), __METHOD__ );
				
			echo json_encode ( $info );
		}
	}
	
	/**
	 * 上传头像
	 */
	public function index() {
		$upload = 'D:\Zend\WorkSpace\city\upload\large';
		if (move_uploaded_file ( $_FILES ['upl'] ['tmp_name'], $upload . '\\' . $_FILES ['upl'] ['name'] )) {
			$large_img = '/city/upload/large/' . $_FILES ['upl'] ['name'];
			list ( $width, $height ) = getimagesize ( $upload . '\\' . $_FILES ['upl'] ['name'] );
			echo '{"status":"success","url":"' . $large_img . '","width":"' . $width . '","height":"' . $height . '"}';
			exit ();
		}
	}
	
	/**
	 * 剪切头像
	 */
	public function crop() {
		// Original image
		$filename = '492f2b1d710eLarge.jpg';
		// die(print_r($_POST));
		$new_filename = 'D:\Zend\WorkSpace\city\upload\thumbnail\492f2b1d710eLarge.jpg';
		
		// Get dimensions of the original image
		list ( $current_width, $current_height ) = getimagesize ( $filename );
		$tmp_img = array (
				'width' => $current_width,
				'height' => $current_height 
		);
		// The x and y coordinates on the original image where we
		// will begin cropping the image, taken from the form
		$x1 = $_POST ['x1'];
		$y1 = $_POST ['y1'];
		$x2 = $_POST ['x2'];
		$y2 = $_POST ['y2'];
		$w = $_POST ['w'];
		$h = $_POST ['h'];
		// die(print_r($_POST));
		
		// This will be the final size of the image
		$crop_width = 100;
		$crop_height = 100;
		
		// Create our small image
		$new = imagecreatetruecolor ( $crop_width, $crop_height );
		// Create original image
		$current_image = imagecreatefromjpeg ( $filename );
		// resamling (actual cropping)
		imagecopyresampled ( $new, $current_image, 0, 0, $x1, $y1, $crop_width, $crop_height, $w, $h );
		// creating our new image
		imagejpeg ( $new, $new_filename, 95 );
	}
}