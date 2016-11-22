<?php
/**
 * 后台 文案管理
 * @author zhangzhibin
 * 2015-07-22
 */
class ArticleAction extends CommonAction {
	
	/*
	 * 文案分类
	 */
	public function cate() {

        $helpCate =new HelpCateModel();
        $where="c.typeid=0 ";
        $cat_list = $helpCate->getCateList ( 0, 0, false,0,true,$where);

        $this->assign ( 'cat_list', $cat_list );
       // exit;
        $this->display();
	}

    /**
     * 文案分类新增
     * @access public
     * @return mix
     */
   public function cateadd(){
       if ($this->isPost ()) {
           $Cates = new HelpCateModel();
           $data['cate_name']=I('cate_name','0','string');
           $data['sort']=I('sort',0,'intval');
           $data['pid']=I('pid',0,'intval');
           if ($Cates->add($data)) {
               $this->success ( L ( 'success_tips' ), '__APP__/Article/cate' );
           } else {
               $this->error ( 'error!', '__APP__/Article/cate' );
           }
       } else {
           $Cates =new HelpCateModel();
           $cateCombo = $Cates->getCateCombo_alltype();
           //var_dump($cateCombo);
           $this->assign('form_combo', $cateCombo);
           $this->assign('form_act', 'cateadd');
           $this->display();
       }
   }

    /**
     * 文案分类编辑
     * @access public
     * @return mix
     */
    public function cateedit() {
         if ($this->isPost ()) {
            try {
                $id = intval ( $this->_post ( 'id' ) );
                $Cates = new HelpCateModel();
                $Cates->find ( $id );
                if ($Cates->updateCate ()) {
                    $this->success ( L ( 'success_tips' ), '__APP__/Article/cate' );
                }
            } catch ( Exception $e ) {

                $this->assign ( 'alert_label', 1 );
                $this->assign ( 'alert_info', $e->getMessage () );
                $id = $this->_get ( 'id' );
                $Cates =new HelpCateModel();
                $Cates->find ( $id );
                $cateCombo = $Cates->getCateCombo_alltype ( $Cates->pid );
                // 模版赋值
                $this->assign ( 'form_combo', $cateCombo );
                $this->display ();
            }
        } else {
            $id = $this->_get ( 'id' );
            $Cates = new HelpCateModel();
            $CatesInfo=$Cates->find ( $id );
            $cateCombo = $Cates->getCateCombo_alltype ($CatesInfo['pid']);
           //var_dump($cateCombo);
            // 模版赋值
            $this->assign ( 'form_combo', $cateCombo );
            $this->assign ( 'front', L ( 'front_label' ) );
            $this->assign ( 'cat_info', $Cates->data () );
            $this->assign ( 'formlabel', L ( 'form_label' ) );
            $this->assign ( 'catelabel', L ( 'cate_add_label' ) );
            $this->assign ( 'cp_home', L ( 'cp_home' ) );
            $this->assign ( 'ur_here', L ( '20_cates_manage' ) );

            $this->assign ( 'copyright', L ( 'copyright' ) );
            $this->assign ( 'js_languages', L ( 'js_languages' ) );
            $this->assign ( 'form_act', 'cateedit' );

            $this->display ();
        }
    }

    /**
     * 分类删除
     *
     * @access private
     * @return mix
     */
    public function catedel()
    {
        try {
            $id = intval($this->_get('id'));
            $Cates = new HelpCateModel();
            $Cates->find($id);
            if($Cates->deleteCate()){
                $this->success ( 'success', '__APP__/Article/cate' );
            }
        } catch (Exception $e) {
            $this->error (  $e->getMessage () , '__APP__/Article/cate' );
        }
        //make_json_result ( $this->fetch ( 'index' ), '', array () );
    }
	


}
?>