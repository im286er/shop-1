<?php

class HelpCateModel extends Model
{
	protected $DBF;
	protected $tableName = 'help_doc_cate';
	protected $_map = array(
		'id'        => 'id',
		'cate_name'  => 'cate_name',
		'pid'       => 'pid',
		'sort'      => 'sort',
        'title'     => 'tdk_title',
        'typeid'    => 'typeid',
        'icon'      => 'icon',
		'ctime'     => 'ctime'
     );
	protected $fields = array(
		'id',
		'cate_name',
		'pid',
		'sort',
		'typeid',
		'icon',
		'ctime',
		'_pk' => 'id',
		'_autoinc' => TRUE);

	public function __construct()
	{
		parent::__construct();
		$this->DBF = new DBF();
	}

	public function getDBF()
	{
		return $this->DBF;
	}

	public function isRootCate()
	{
		if($this->pid == 0)
		{return true;}
		return false;
	}

	public function isNodeCate()
	{
		$num = $this->where('pid=' . $this->id)->count();
		if($num > 0){
            return false;
        }else{
            return true;
        }
	}

    /**
     * 分类下是否存在文章
     */
    public function existArticle(){
        $num = M('help_doc')->where('cate=' . $this->id)->count();
        if($num > 0){
            return false;
        }else{
            return true;
        }
    }




	public function callGetParentByCID($Category)
	{
		if($Category)
		{
			//if(($Category[$this->DBF->ProductCategory->ParentID] > 1000))
			if(($Category[$this->DBF->ProductCategory->ParentID] > 0))
			{
				$Result = $this->getCategoryByCID($Category[$this->DBF->ProductCategory->ParentID]);
				$Result['Child'] = $Category;
				return $this->callGetParentByCID($Result);
			}
			else { return $Category; }
		}
	}

	public function getCategoryByCID($CID)
	{
		$Category = $this->where( "id='" . $CID . "'")->select();
		if($Category) { return $Category[0]; }
		return false;
	}

	/**
	 * 获得指定分类下的子分类的数组
	 *
	 * @access public
	 * @param int $cat_id
	 *        	分类的ID
	 * @param int $selected
	 *        	当前选中分类的ID
	 * @param boolean $re_type
	 *        	返回的类型: 值为真时返回下拉列表,否则返回数组
	 * @param int $level
	 *        	限定返回的级数。为0时返回所有级数
	 * @param int $is_show_all
	 *        	如果为true显示所有分类，如果为false隐藏不可见分类。
	 * @return mix
	 */
	public function getCateList($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true, $where = '1=1 '){
		static $res = NULL;
		if($res === NULL){
			$data = false;
			if($data === false)	{
				$sql = "SELECT c.*, COUNT(s.id) AS has_children FROM  tdf_help_doc_cate AS c " . "LEFT JOIN tdf_help_doc_cate AS s ON s.pid=c.id " . "WHERE " . $where . "GROUP BY c.id " . "ORDER BY c.pid ASC,c.sort ASC,s.sort";
				$res = $this->query($sql);
			}else{
				$res = $data;
			}
		}

      //  exit;
        if(empty($res) == true){
			return $re_type ? '' : array();
		}

       // exit;
        $options = $this->getCateoptions($cat_id, $res); // 获得指定分类下的子分类的数组

       // exit;
        $children_level = 99999; // 大于这个分类的将被删除
        if ($is_show_all == false) {
            foreach ($options as $key => $val) {
                if ($val['level'] > $children_level) {
                    unset($options[$key]);
                } else {
                    if ($val['is_show'] == 0) {
                        unset($options[$key]);
                        if ($children_level > $val['level']) {
                            $children_level = $val['level']; // 标记一下，这样子分类也能删除
                        }
                    } else {
                        $children_level = 99999; // 恢复初始值
                    }
                }
            }
        }

        /* 截取到指定的缩减级别 */
        if ($level > 0) {
            if ($cat_id == 0) {
                $end_level = $level;
            } else {
                $first_item = reset($options); // 获取第一个元素
                $end_level = $first_item['level'] + $level;
            }

            /* 保留level小于end_level的部分 */
            foreach ($options as $key => $val) {
                if ($val['level'] >= $end_level) {
                    unset($options[$key]);
                }
            }
        }

        if ($re_type == true) {

            $select = '';
            foreach ($options as $var) {
                $select .= '<option value="' . $var['pc_id'] . '" ';
                $select .= ($selected == $var['p_id']) ? "selected='ture'" : '';
                $select .= '>';
                if ($var['level'] > 0) {
                    $select .= str_repeat('&nbsp;', $var['level'] * 4);
                }
                $select .= htmlspecialchars(addslashes($var['cate_name']), ENT_QUOTES) . '</option>';
            }
            return $select;
        }else{
            foreach ($options as $key => $value) {
                $options[$key]['url'] = build_uri('cates', array(
                    'cid' => $value['cat_id']), $value['cat_name']);
            }
            return $options;
        }
	}

	/**
	 * 过滤和排序所有分类，返回一个带有缩进级别的数组
	 *
	 * @access private
	 * @param int $cat_id
	 *        	上级分类ID
	 * @param array $arr
	 *        	含有所有分类的数组
	 * @param int $level
	 *        	级别
	 * @return void
	 */
    private function getCateoptions($spec_cat_id, $arr)
    {
        static $cat_options = array();
        if (isset($cat_options[$spec_cat_id])) {
            return $cat_options[$spec_cat_id];
        }
       // var_dump($spec_cat_id[0]);
        //exit;

       // exit;
        if (!isset($cat_options[0])) {
            $level = $last_cat_id = 0;
            $options = $cat_id_array = $level_array = array();
            // $data = read_static_cache('cat_option_static');
            $data = false;
            if ($data === false) {
                while (!empty($arr)) {
                    foreach ($arr as $key => $value) {
                        $cat_id = $value['id'];
                        if ($level == 0 && $last_cat_id == 0) {
                            if ($value['pid'] > 0) {
                                break;
                            }
                            $options[$cat_id] = $value;
                            $options[$cat_id]['level'] = $level;
                            $options[$cat_id]['id'] = $cat_id;
                            $options[$cat_id]['cate_name'] = $value['cate_name'];

                            unset($arr[$key]);
                            if ($value['has_children'] == 0) {
                                continue;
                            }
                            $last_cat_id = $cat_id;
                            $cat_id_array = array($cat_id);
                            $level_array[$last_cat_id] = ++$level;
                            continue;
                        }

                        if ($value['pid'] == $last_cat_id) {
                            $options[$cat_id] = $value;
                            $options[$cat_id]['level'] = $level;
                            $options[$cat_id]['id'] = $cat_id;
                            $options[$cat_id]['cate_name'] = $value['cate_name'];
                            unset($arr[$key]);

                            if ($value['has_children'] > 0) {
                                if (end($cat_id_array) != $last_cat_id) {
                                    $cat_id_array[] = $last_cat_id;
                                }
                                $last_cat_id = $cat_id;
                                $cat_id_array[] = $cat_id;
                                $level_array[$last_cat_id] = ++$level;
                            }
                        } elseif ($value['pid'] > $last_cat_id) {
                            break;
                        }
                    }

                    $count = count($cat_id_array);
                    if ($count > 1) {
                        $last_cat_id = array_pop($cat_id_array);
                    } elseif ($count == 1) {
                        if ($last_cat_id != end($cat_id_array)) {
                            $last_cat_id = end($cat_id_array);
                        } else {
                            $level = 0;
                            $last_cat_id = 0;
                            $cat_id_array = array();
                            continue;
                        }
                    }

                    if ($last_cat_id && isset($level_array[$last_cat_id])) {
                        $level = $level_array[$last_cat_id];
                    } else {
                        $level = 0;
                    }
                }
                // 如果数组过大，不采用静态缓存方式
                /*
                 * if (count ( $options ) <= 2000) { write_static_cache (
                 * 'cat_option_static', $options ); }
                 */
            } else {
                $options = $data;
            }
            $cat_options[0] = $options;
        } else {
            $options = $cat_options[0];
        }

        if (!$spec_cat_id) {
            return $options;
        } else {
            if (empty($options[$spec_cat_id])) {
                return array();
            }

            $spec_cat_id_level = $options[$spec_cat_id]['level'];

            foreach ($options as $key => $value) {
                if ($key != $spec_cat_id) {
                    unset($options[$key]);
                } else {
                    break;
                }
            }

            $spec_cat_id_array = array();
            foreach ($options as $key => $value) {
                if (($spec_cat_id_level == $value['level'] && $value['pid'] != $spec_cat_id) || ($spec_cat_id_level > $value['level'])) {
                    break;
                } else {
                    $spec_cat_id_array[$key] = $value;
                }
            }
            $cat_options[$spec_cat_id] = $spec_cat_id_array;

            return $spec_cat_id_array;
        }
    }

	public function getCateCombo($location = 0, $pc_type = 0)
	{
		$re = '';
		$num = 0;
		$num = $this->count();
		//echo $num;
		if($num){
			$catinfobit_ = $this->where("pc_type=".$pc_type."")->select();	
			$re = "<option value='0'>" . L('root_cate') . "</option>";
			$re .= $this->genCateOption($catinfobit_, 0, '', 0, $location);
		}else{
			$re = "<option value='0'>" . L('root_cate') . "</option>";
		}
		return $re;
	}
	
	
	public function getCateCombo_2($location = 0, $pc_type = 0)
	{
		$re = '';
		$num = 0;
		$num = $this->count();
		$catinfobit_ = $this->where("pc_type=".$pc_type."")->select();
		//var_dump($catinfobit_);
		$re="<option value='0'>" . L('root_cate') . "</option>";
		foreach($catinfobit_ as $key => $value){
			
			if(intval($value['pc_id'])==$location){
				$re.="<option selected=selected value=".$value['pc_id'].">".$value['pc_name']."</option>";
			}else{
				$re.="<option value=".$value['pc_id'].">" .$value['pc_name'] . "</option>";
			}
			//var_dump($value);
		}
		
		
		//$re .= $this->genCateOption($catinfobit_, 0, '', 0, $location);	
		//var_dump($re);
		return $re;
	}

    public function getCateCombo_alltype($location = 0)
    {
        $re = '';
        $num = 0;
        $num = $this->count();
        if ($num) {
            $catinfobit_ = $this->where("typeid=0")->select();
            //$re = "<option value='0'>" . L('root_cate') . "</option>";
            $re .= $this->genCateOption($catinfobit_, 0, '', 0, $location);
        } else {
            //$re = "<option value='0'>" . L('root_cate') . "</option>";
        }
        return $re;
    }
	
	
	private function genCateOption($catinfobit_, $pid = 0, $re = '', $level = 1, $location = 0)
	{
		$newlevel = 1;
		$newre = '';
		$space = '';
		//var_dump($catinfobit_);
		//echo $level."aa";
		//exit;
		$space = get_spaces($level);
        foreach ($catinfobit_ as $key => $val) {
            if ($val['pid'] == $pid) {
                $newlevel = $level + 1;
                if ($val['id'] == $location) {
                    $newre = "<option value='" . $val['id'] . "' selected>" . $space . "--" . $val['cate_name'] . "</option>";
                } else {
                    $newre = "<option value='" . $val['id'] . "'>" . $space . "--" . $val['cate_name'] . "</option>";
                }
                unset($catinfobit_[$key]);
                $re .= $this->genCateOption($catinfobit_, $val['id'], $newre, $newlevel, $location);
            }
        }
		return $re;
	}
	

	
	/**
	 * 获得指定分类下的子分类的数组
	 *
	 * @access public
	 * @param int $cat_id
	 *        	分类的ID字符串，带逗号的
	 * @param varchar $selected 当前选中分类的数组
	 * 
	 * @param boolean $re_type
	 *        	返回的类型: 值为真时返回checkbox列表,否则返回数组
	 * @param int $level
	 *        	限定返回的级数。为0时返回所有级数
	 * @param int $is_show_all
	 *        	如果为true显示所有分类，如果为false隐藏不可见分类。
	 * @return mix
	 */
	public function getCateCheckbox($cat_id = 0, $selected = 0, $re_type = true, $checkbox_name='cate', $level = 0, $is_show_all = true, $where = '1=1 '){
		$checkedArr=$this->getCheckedArr($selected);
		static $res = NULL;
		if($res === NULL){
			// $data = read_static_cache('cat_pid_releate');
			$data = false;
			if($data === false){
				$sql = "SELECT c.*, COUNT(s.pc_id) AS has_children " . 'FROM ' . $this->DBF->ProductCategory->_Table . " AS c " . "LEFT JOIN " . $this->DBF->ProductCategory->_Table . " AS s ON s.pc_parentid=c.pc_id " . "WHERE " . $where . "GROUP BY c.pc_id " . "ORDER BY c.pc_parentid ASC,c.pc_dispweight ASC,c.pc_id";
				$res = $this->query($sql);
				/*
				 * $newres = array(); foreach($res as $k=>$v) {
				* $res[$k]['models_num'] = !empty($newres[$v['cateId']]) ?
				* $newres[$v['cateId']] : 0; }
				*/
				// 如果数组过大，不采用静态缓存方式
				/*
				 * if (count ( $res ) <= 1000) { write_static_cache (
				 		* 'cat_pid_releate', $res ); }
				*/
			}else{
				$res = $data;
			}
		}
		if(empty($res) == true){return $re_type ? '' : array();}
		$options = $this->getCateoptions($cat_id, $res); // 获得指定分类下的子分类的数组
		$children_level = 9999; // 大于这个分类的将被删除
		if($is_show_all == false){
			foreach($options as $key => $val){
				if($val['level'] > $children_level)	{
					unset($options[$key]);
				}elseif($val['level']==1){
					unset($options[$key]);
				}else{
					if($val['is_show'] == 0){
						unset($options[$key]);
						if($children_level > $val['level']){
							$children_level = $val['level']; // 标记一下，这样子分类也能删除
						}
					}else{
						$children_level = 99999; // 恢复初始值
					}
				}
			}
		}
		//----------------------删除主大类，大类不显示 start
		//foreach($options as $k =>$v){
		//	if($v['level']==1){unset($options[$k]);}
		//}
		//----------------------删除主大类，大类不显示 end
		/* 截取到指定的缩减级别 */
		if($level > 0){
			if($cat_id == 0){
				$end_level = $level;
			}else{
				$first_item = reset($options); // 获取第一个元素
				$end_level = $first_item['level'] + $level;
			}
			/* 保留level小于end_level的部分 */
			foreach($options as $key => $val){
				if($val['level'] >= $end_level){
					unset($options[$key]);
				}
				//print_r($options[$key]);
				
				if($val['level']==1){
					print_r($options[$key]);
					unset($options[$key]);
				}
			}
		}
		if($re_type == true){
			$checkbox = '';
			foreach($options as $var){
				if($var['level']==1){ //大类不构造checkbox
					$checkbox.="&nbsp;&nbsp;<font color=blue>".$var['pc_name']."</font><br>";
				}elseif($var['level']==0){
					$checkbox.="<font color=blue>".$var['pc_name']."</font><br>";
				}else{
					$checkbox.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='".$checkbox_name."[]' value=".$var['pc_id']." ";
					if(in_array($var['pc_id'], $checkedArr)){$checkbox.="checked=checked ";}
					$checkbox.=">".$var['pc_name']."<br>";
				}
			}
			return $checkbox;
		}else{
			return $options;
		}
	}
	
	private function getCheckedArr($checkedCate){
		if(!$checkedCate){return 0;}
		if(strstr($checkedCate,',')){
			$result=explode(',',$checkedCate);
		}else{
			$result[0]=$checkedCate;
		}
		return $result;
	}


    public function updateCate(){


        $id=I('id',0,'intval');
        $data['cate_name']=I('cate_name','0','string');
        $data['sort']=I('sort',0,'intval');
        $data['pid']=I('pid',0,'intval');
       // exit;
        if($this->where("id=".$id."")->save($data)){
            //$this->save();
            return true;
        }else{
            var_dump($data);
            echo $this->getLastSql();
            exit;
            echo "aabb";
            throw new Exception($this->getError());
        }
    }

    public function deleteCate()
    {
        // 判断一，根分类不可移除
        if ($this->isRootCate()) {
            throw new Exception('根分类不能删除');
        }
        // 判断二，分类下仍有子分类不可移除
        $isNode = $this->isNodeCate();
        if (!$isNode) {
            throw new Exception('分类下有子分类不能删除');
        }
        //判断三,分类下仍有文章不可以删除
        $isArticle = $this->existArticle();
        if (!$isArticle) {
            throw new Exception(L('分类下有文章内容'));
        }
        // 删除
        if ($this->delete()) {
            return true;
        } else {
            exit($this->getError());
        }
    }


	
	
}
?>