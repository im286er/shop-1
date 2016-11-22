<?php

class AvartarModel extends Model {

    /*头像上传
     * @param Int $uid
     * @param Array $files 图片数据(有name,type,tmp_name,error,size等)
     */
    public function uploadAvartar($uid,$files){
        $MD5File16Name = getMD5File16 ($files ['tmp_name'] );
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
        $genAvatarPath = getSavePathByID ( $uid );
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
            $result=0;
            //echo json_encode ( $upload->getErrorMsg () );
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
            $Users->find ($uid);
            // $Users->u_avatar = $genAvatarPath . 's/' . $info [0] ['thumbname'];
            $Users->u_avatar = $genAvatarPath . 'o/' . $savename_arr [0] . '.' . $savename_arr [1];
            $Users->save();
            $result['u_avartar']=WEBROOT_URL.getfilepath($info[0]['savepath'].$info[0]['savename']);
            //echo json_encode ( $info );
        }
        return $result;
    }


}

?>
