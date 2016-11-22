<?php

class MakeTableModel extends Model{
    /**
     * @var data stored here
     */
    private static $_PREFIX = 'm_'; // 控件名前缀
    private static $m_symbol = '|'; // 指定值分隔符
    private static $m_name = null; // 控件名
    private static $m_title = null; // 表头项
    private static $m_PK = 'id'; // 主键
    private static $m_data = null; // 列表数据源
    private static $m_control = null; // 修改/删除等控制参数
    private static $m_instance = null; // 单例对象
    
    private static $m_id=null; //得到的get的selectid ,用于判断是新增数据还是编辑数据
    // 表标记参数 
    private static $m_tags = array('tags'=>array('name'=>'table'), 'tagsrow'=>array('name'=>'tr'), 'tagscol'=>array('name'=>'td'));
    
    function __construct($_tableName='default')
    {
        self::$m_name = $_tableName; 
    }
    
		/**
		 * @desc 创建单例对象
		 */
    static function getInstance()
    {
        if (!is_object(self::$m_instance))
        {
            self::$m_instance = get_instance_of('MakeTable');
        }
        return self::$m_instance;
    }
    
    /**
     * @desc 生成显示列表方法
     */
    function showTableList($_data=null) //列表
    {
        $_rtn = '';
        if ($_data && is_array($_data))
        {
            self::$m_data = $_data;
        }
        // 取表头部分
        $_rtn .= self::tableCaput();
                
        $_titlecss = is_array(self::$m_tags['tagsrow']['rules']['class']) ? self::$m_tags['tagsrow']['rules']['class'][0] : (isset(self::$m_tags['tagsrow']['rules']['class']) ? self::$m_tags['tagsrow']['rules']['class'] : '');  
        
        $_rtn .= "<".self::$m_tags['tagsrow']['name'];
        $_rtn .= $_titlecss ? " class=\"{$_titlecss}\">" : '>';
          
        $_values = array_values(self::$m_title);
          
        foreach($_values as $value)
        {
            is_array($value) ? $value = $value[0] : '';
            $_rtn .= "<".self::$m_tags['tagscol']['name'].">{$value}</".self::$m_tags['tagscol']['name'].">";
        }
        
        if (isset(self::$m_control['control']))
        {
            $_rtn .= "<".self::$m_tags['tagscol']['name'].">操作</".self::$m_tags['tagscol']['name'].">";
        }
        
        $_rtn .= "</".self::$m_tags['tagsrow']['name'].">"; 
        
        // 输出数据列表
        $fileds = array_keys(self::$m_title); 
        foreach(self::$m_data as $key=>$value)
        {
            $_rtn .= "<".self::$m_tags['tagsrow']['name']."";

            $_rtn .= self::classRules($key+1, self::$m_tags['tagsrow']['rules']['rul'], self::$m_tags['tagsrow']['rules']['class']).">";
            
            foreach($fileds as $k=>$v)
            {
                $_cbox = '';
                
                if (is_array(self::$m_title[$v]) && self::$m_title[$v]['1'] == 'checkbox')
                {
                   $_cbox = self::doInput('chck', 'checkbox', $value[self::$m_PK]);
                }
                
                $_rtn .= "<".self::$m_tags['tagscol']['name'].self::classRules($k+1, self::$m_tags['tagscol']['rules']['rul'], self::$m_tags['tagscol']['rules']['class']).">";
                $_rtn .= "{$_cbox} {$value[$v]}</".self::$m_tags['tagscol']['name'].">";
            }
            
            if (isset(self::$m_control['control']))
            {
                if (stripos(self::$m_control['control']['con_parse'][0],'?') === false)
                {
                    $_con = self::$m_control['control']['con_parse'][0] ."?".self::$m_control['control']['con_parse'][1]."="; 
                } else {
                    $_con = self::$m_control['control']['con_parse'][0] ."&".self::$m_control['control']['con_parse'][1]."="; 
                } 
                               
                $_rtnc = '';
                $_rtn .= "<".self::$m_tags['tagscol']['name'].">";
                foreach(self::$m_control['control']['con_list'] as $k=>$v)
                {
                    $_rtnc .= "|<a href=\"{$_con}{$k}&id=".$value[self::$m_PK]."\">{$v}</a>";
                }
                $_rtn .= substr($_rtnc,1)."</".self::$m_tags['tagscol']['name'].">";
            }            
            $_rtn .= "</".self::$m_tags['tagsrow']['name'].">"; 
        }
        
        // 取表尾
        $_rtn .= self::tableTail(); 

        // 按钮
        $_rtn .= self::submitButton(false);

        return $_rtn;
    }
    
    
    /**
     * @desc 生成提交表单方法
     */
    function showTableSubmit($_data=null) 
    {
        $_rtn = '';
        if ($_data && is_array($_data))
        {
            self::$m_data = $_data;
        }
        
        // 取表单头部
        $_rtn .= self::submitCaput();
        
        // 取表头部分
        $_rtn .= self::tableCaput();
        
        // 添加表单标题
        $_rtn .= self::submitTitle();
        $_fileds = array_keys(self::$m_title);
        
        foreach($_fileds as $value)
        {
            $_parse = self::doParse($value, self::$m_title[$value][0], self::$m_title[$value][1]);
            $_method = "do".ucfirst(self::$m_title[$value][0]);
            $_rtn .= "<".self::$m_tags['tagsrow']['name'].">";

            if(self::$m_title[$value][1]=="radio"){
            	$_rtn .= "<".self::$m_tags['tagscol']['name']." colspan=2>";
            }else{
            	$_rtn .= "<".self::$m_tags['tagscol']['name']." width=120 colspan=2>";
            }
            
            if (self::$m_title[$value][2] && is_array(self::$m_title[$value][2]))
            {
                (self::$m_title[$value][0] == 'select') ? $_rtn .= self::$m_title[$value][2]['name'].":".self::doSelectCaput(self::$_PREFIX.$value, self::$m_title[$value][3]) : $_rtn .= self::$m_title[$value][2]['name'].":";
                foreach(self::$m_title[$value][2]['vlist'] as $k=>$v)
                {
                    $_parse = self::doParse($value, self::$m_title[$value][0], self::$m_title[$value][1], $k);
                    $_rtn  .= (self::$m_title[$value][0] == 'select') ? call_user_func_array(array(&$this, $_method), $_parse) : call_user_func_array(array(&$this, $_method), $_parse)."{$v} ";
                }
                (self::$m_title[$value][0] == 'select') ? $_rtn .= self::doSelectTail() : '';
            } else {
                $_rtn .= self::$m_title[$value][2].":".call_user_func_array(array(&$this, $_method), $_parse);
            }
            $_rtn .= "</".self::$m_tags['tagscol']['name'].">";
            $_rtn .= "</".self::$m_tags['tagsrow']['name'].">";            
        }
        
        // 按钮
        $_rtn .= self::submitButton();
        
        // 取表尾
        $_rtn .= self::tableTail();
         
        // 取表单尾
        $_rtn .= self::submitTail(); 
        
        return $_rtn;
    }
    

    /**
     * @desc 生成提交表单头部方法
     * self::$m_tags['tagsform']['attr'] = array() 表单属性
     * self::$m_tags['tagsform']['custom'] = '' 自定义项 exp: <input name='onclick' type='hidden' value='123' /> 
     * self::$m_tags['tagsform']['button'] = array('input','text','名称') 表单按钮 
     * 可以多个 exp: array(array('submit','submit','提交'), array('reset','reset','重置')) 
     */
    private static function submitCaput()
    {
        $_rtn_cap = "<form";
        if (isset(self::$m_tags['tagsform']['attr']))
        {
            foreach(self::$m_tags['tagsform']['attr'] as $key=>$value)
            {
                $_rtn_cap .= " {$key}=\"{$value}\"";
            }
        }
        $_rtn_cap .= '>';
        
        if (isset(self::$m_tags['tagsform']['custom']))
        {
            $_rtn_cap .= self::$m_tags['tagsform']['custom'];
        }
        
        return $_rtn_cap;            
    }
    
    
    /**
     * @desc 生成提交表单按扭方法
     * $_isSide 在表框框里面还是外面
     */
    private function submitButton($_isSide=true)
    {
        $_rtn_tail = "";
        $_parse    = array();
        
        if (isset(self::$m_tags['tagsform']['button']) && is_array(self::$m_tags['tagsform']['button']))
        {
            $_rtn_tail .= $_isSide ? "<".self::$m_tags['tagsrow']['name'].">" : '';
            
            if (is_array(self::$m_tags['tagsform']['button'][0]))
            {
                foreach(self::$m_tags['tagsform']['button'] as $key=>$value)
                {
                    //dump($value);
                    $_parse    = array();
                    foreach($value as $k=>$v)
                    {
                        $v ? array_push($_parse, $v) : array_push($_parse, ''); 
                    }
                    
                   // dump($_parse);
                    
                    $_rtn_tail .= $_isSide ? "<".self::$m_tags['tagscol']['name'].">" : '';
                    $_rtn_tail .= call_user_func_array(array(&$this, 'doInput'), $_parse);
                    $_rtn_tail .= $_isSide ? "</".self::$m_tags['tagscol']['name'].">" : '';
                }
            } else {
                $_rtn_tail .= $_isSide ? "<".self::$m_tags['tagscol']['name'].">" : '';
                foreach(self::$m_tags['tagsform']['button'] as $k=>$v)
                {
                    $v ? array_push($_parse, $v) : array_push($_parse, ''); 
                }
                
                $_rtn_tail .= call_user_func_array(array(&$this, 'doInput'), $_parse);
                $_rtn_tail .= $_isSide ? "</".self::$m_tags['tagscol']['name'].">" : '';
            }
            $_rtn_tail .= $_isSide ? "</".self::$m_tags['tagsrow']['name'].">" : '';
        }
        return $_rtn_tail;
    }
    
        
    /**
     * @desc 生成提交表单尾部方法
     */
    private static function submitTail()
    {
        return "</form>";
    }


    /**
     * @desc 生成表头方法
     */
    private static function tableCaput()
    {
        $_rtn_cap = "<".self::$m_tags['tags']['name']." id=\"".self::$m_name."\"";
        if (isset(self::$m_tags['tags']['attr']))
        {
            foreach(self::$m_tags['tags']['attr'] as $key=>$value)
            {
                $_rtn_cap .= " {$key}=\"{$value}\"";
            }
        }
        $_rtn_cap .= '>';
        return $_rtn_cap;            
    }
    
    
    /**
     * @desc 生成表尾部方法
     */
    private static function tableTail()
    {
        return "</".self::$m_tags['tags']['name'].">";
    }
    

    /**
     * @desc 生成提交表单标题方法
     */
    private static function submitTitle()
    {
        return isset(self::$m_tags['tagstitle']['name']) ? "<".self::$m_tags['tagstitle']['name'].">".self::$m_tags['tagstitle']['title']."</".self::$m_tags['tagstitle']['name'].">" : '';
    } 
       

    /**
     * @desc 分析加CLASS规则方法
     */
    private static function classRules($num, $_rules=null, $_rulstr)
    {
        $_return = '';
        
        if (is_array($_rulstr))
        {
            $_rulstr = isset($_rulstr[1]) ? $_rulstr[1] : '';    
        }
        
        if (!$num || !$_rulstr)
        {
            return '';    
        }
        
        if ($_rules == 0 && $num%2 == 0) 
        {
            $_return = " class=\"{$_rulstr}\"";
        } else if ($_rules == 1 && $num%2 != 0) {
            $_return = " class=\"{$_rulstr}\"";
        } else if (in_array($num, explode('|', $_rules))) {
            $_return = " class=\"{$_rulstr}\"";
        }
        return $_return;  
    }
    
    
    /**
     * @desc set 方法集
     */
    function setName($_name)
    {
        self::$m_name = $_name;
        return $this;
    }    
    
    function setPREFIX($_name)
    {
        self::$_PREFIX = $_name;
        return $this;
    }    

    function setTitle($_title)
    {
        self::$m_title = $_title;
        return $this;
    }    
    
    function setData($_data)
    {
        self::$m_data = $_data;
        return $this;
    }    

    function setPK($_str)
    {
        self::$m_PK = $_str;
        return $this;
    }    

    function setControl($_control)
    {
        self::$m_control = $_control;
        return $this;
    }  
      
    function setTags($_tags)
    {
        self::$m_tags = $_tags;
        return $this;
    } 
    
    function setSymbol($_symbol)
    {
        self::$m_symbol = $_symbol;
        return $this;
    }
    
    function setId($id){
    	self::$m_id=$id;
    	return $this;
    }
    
     
    /**
     * @desc 生成select控件头
     */
    static private function doSelectCaput($_name='default', $_css=null)
    {
        return $_css ? "</td><td><select name=\"{$_name}\" id=\"{$_name}\" class=\"{$_css}\">" : "<select name=\"{$_name}\" id=\"{$_name}\">";
    }


    /**
     * @desc 生成select控件尾
     */
    static private function doSelectTail()
    {
        return "</select>";
    }
    
    
    /**
     * @desc 生成select option选项
     */
    static private function doSelect($_option=null, $_val='0')
    {
        $_str = "";
        if (!$_option)
        {
            $_option = array('请选择');
        }
        
        foreach($_option as $key=>$value)
        {
            $_str .= $_val == $key ? "<option value=\"{$key}\" selected=\"selected\">{$value}</option>" : "<option value=\"{$key}\">{$value}</option>";
        }
        return $_str;
    }
    
    /**
     * @desc 生成input控件
     */
    static private function doInput($_name='default', $_type='text', $_val='', $_css=null, $_check=false)
    {
    	
    	$_str = '';
    	if($_type=="IMAGE"){//如果是图片上传的input
    		$_str .="</td><td><input type='text' name='{$_name}' id='{$_name}' value=\"{$_val}\"  class=\"{$_css}\"> <input type='button' id='{$_name}_img' value='选择图片' />";
    		    $_str	.="<script>";
				$_str	.="KindEditor.ready(function(K) {";
				$_str	.="var editor = K.editor({";
				$_str	.="	allowFileManager : true";
				$_str	.="});";
				$_str	.="K('#{$_name}_img').click(function() {";
				$_str	.="editor.loadPlugin('image', function() {";
				$_str	.="editor.plugin.imageDialog({";
				$_str	.="imageUrl : K('#{$_name}').val(),";
				$_str	.="clickFn : function(url, title, width, height, border, align) {";
				$_str	.="K('#{$_name}').val(url);";
				$_str	.="editor.hideDialog();";
				$_str	.="}";
				$_str	.="});";
				$_str	.="});";
				$_str	.="});";	
				$_str	.="});";
				$_str	.="</script>";
    	}elseif($_type=="MIMG"){
    		$_str .="</td><td>";
    		$_str .="<div style='width: 820px; height: 140px; border: 1px solid #e1e1e1; font-size: 12px; padding: 10px;'>";
            $_str .="<input type='hidden' id='{$_name}' name='{$_name}'  value=\"{$_val}\"  ><span id=spanButtonPlaceholder></span>";
            $_str .="<div id='divFileProgressContainer'></div>";
            $_str .="<div id='thumbnails'>";           
            $_str .="<ul id='pic_list' style='margin: 5px;'>";
            $_str .="</ul>";
            $_str .="<div style='clear: both;'></div>";
            $_str .="</div></div>";
            $_str .="<script>";
			$_str .="function imgId(){";
            $_str .="$('#{$_name}').val($('#imgid').val());";
            $_str .="}";
            $_str .="</script>";
    		//$_str .="<input type='text' name='{$_name}' id='{$_name}' value=\"{$_val}\"  class=\"{$_css}\"><input type='button' id='{$_name}_img' value='选择图片' /><div id='J_imageView'></div>";
    	}elseif($_type=="HTML"){
    			$_str .="</td><td><textarea name='{$_name}' style='width:700px;height:280px; visibility:hidden' id='{$_name}';>".$_val."</textarea>";
    	}else{
	    	if($_css=="DISPLAY"){
	    			$_str .='</td><td colspan=3>'.$_val;
	    		}else{
	    			if($_val<>""){
	    				$_str .= (in_array($_type, array('radio','checkbox'))) ?'':'</td><td>';
	    			}else{
	 					if(self::$m_id){//如果有m_id说明是编辑已有记录。空值的input需要加</td><td>，用以对齐
	 						$_str .= (in_array($_type, array('radio','checkbox'))) ?'':'</td><td>';
	 					}else{
	 						$_str .= (in_array($_type, array('radio','checkbox'))) ?'':'';
	 					}
	    			}
	    			//var_dump($_type);
	    			//$_str .= (in_array($_type, array('radio','checkbox'))) ?'':'</td><td>';
	    			if($_css=="ReadOnly"){
	    				$_readonly .= " readonly='readonly' ";
	    			}
	    			if($_css=="css_radio"){
	    				if($_val==0){
	    					$_str .= $_css ? "</td><td><input name=\"{$_name}\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" class=\"{$_css}\"  {$_readonly}" : "<input name=\"{$_name}\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" ";
	    				}else{
	    					$_str .= $_css ? "<input name=\"{$_name}\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" class=\"{$_css}\"  {$_readonly}" : "<input name=\"{$_name}\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" ";
	    				}
	    			}elseif($_css=="css_checkbox"){
						if($_val==1){
							$_str .= $_css ? "</td><td><input name=\"{$_name}[]\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" class=\"{$_css}\"  {$_readonly}" : "<input name=\"{$_name}[]\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" ";
						}else{
							$_str .= $_css ? "<input name=\"{$_name}[]\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" class=\"{$_css}\"  {$_readonly}" : "<input name=\"{$_name}[]\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" ";
						}
	    			}elseif($_css=="css_imagecover"){
	    				$_str .= $_css ? "<img src=__IMG__".$_val." width=40px height=30px> <a href='aa'>上传</a>" : "";
	    			}else{
	    				$_str .= $_css ? "<input name=\"{$_name}\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" class=\"{$_css}\"  {$_readonly}" : "</td><td><input name=\"{$_name}\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" ";
	    			}
	    			
	    			//$_str .= $_css ? "<input name=\"{$_name}\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" class=\"{$_css}\"  {$_readonly}" : "</td><td><input name=\"{$_name}\" id=\"{$_name}\" type=\"{$_type}\" value=\"{$_val}\" ";
	    			if($_css=="css_checkbox"){
	    				$_str .= (in_array($_type, array('radio','checkbox')) && $_check) ? 'checked="checked" />' : '/>';
	    			}elseif($_css=="css_imagecover"){
	    				$_str .=  '';
	    			}else{
	    				$_str .= (in_array($_type, array('radio','checkbox')) && $_check) ? 'checked="checked" />' : '/>';
	    			}
	    		}
	    	if($_css=="PASSWORDSALT"){
	    		$_str .=" (为空则不修改密码)";
	    	}
    	}
      return $_str;
    }
    
    /*
     * @desc 生成textarea控件
     */
    static private function doTextarea($_name='default', $_val='', $_css=null)  
    {
    	
        $_str = $_css ? "</td><td><textarea name=\"{$_name}\" id=\"{$_name}\" class=\"{$_css}\">{$_val}</textarea>" : "<textarea name=\"{$_name}\" id=\"{$_name}\">{$_val}</textarea>";
        return $_str;
    }
    
    
    /**
     * @desc 控件参数拆分
     */
    static private function doParse($fileds, $type='input', $attr='text', $value=null)
    {
        $_parse = array('0'=>self::$_PREFIX.$fileds);
        switch($type)
        {
            case 'input':
                foreach(array(1,3) as $e=>$u)
                {
                    isset(self::$m_title[$fileds][$u]) 
                    ? $_parse[$u] = self::$m_title[$fileds][$u] 
                    : $_parse[$u] = ''; 
                }
                            
                if (in_array($attr, array('radio','checkbox'))){
                    $_parse[2] = $value;
                   //echo $attr; 
                    if($attr=='checkbox'){
                    	if (in_array($value, explode(',',self::$m_data[$fileds]))){
                    		$_parse[4] = true;
                    	}else{
                    		$_parse[4] = false;
                    	}
                    }else{
                    	if (in_array($value, explode(self::$m_symbol,self::$m_data[$fileds]))){
                    		$_parse[4] = true;
                    	} else {
                    		$_parse[4] = false;
                    	}
                    }
	                   
	                    
                    
                } else {
                    $_parse[2] = self::$m_data[$fileds];
                }
            break;
            
            case 'textarea':
                foreach(array(3) as $e=>$u)
                {
                    isset(self::$m_title[$fileds][$u]) 
                    ? $_parse[$u] = self::$m_title[$fileds][$u] 
                    : $_parse[$u] = ''; 
                }
                $_parse[1] = self::$m_data[$fileds];
            break;

            case 'select':
                $_parse = array();
                $_parse[2] = array("{$value}"=>self::$m_title[$fileds][2]['vlist'][$value]);
                $_parse[3] = self::$m_data[$fileds];
            break;
        }
        ksort($_parse);
        return $_parse;           
    }  
}
?>
