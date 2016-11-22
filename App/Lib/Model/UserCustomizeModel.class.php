<?php

/**
 * 商品专属定制基本类
 *
 * @author miaomin 
 * Jan 23, 2015 4:55:47 PM
 *
 * $Id$
 */
class UserCustomizeModel extends Model
{

    /**
     *
     * @var DBF
     */
    protected $DBF;

    /**
     *
     * @var DBF_UserCustomize
     */
    public $F;

    /**
     * 商品专属定制基本类
     */
    public function __construct()
    {
        $this->DBF = new DBF();
        $this->F = $this->DBF->UserCustomize;
        
        $this->trueTableName = $this->F->_Table;
        $this->fields = $this->F->getFields();
        
        if (! $this->_map) {
            $this->_map = $this->F->getMappedFields();
        }
        
        parent::__construct();
    }

    /**
     * 获取专属定制列表
     */
    public function getCustomizeList($where = '1=1')
    {
        $res = array();
        
        $cList = $this->where($where)
            ->join(' tdf_users ON tdf_users.u_id = tdf_user_customize.u_id')
            ->field('tdf_user_customize.*,tdf_users.u_email,tdf_users.u_mob_no')
            ->select();
        
        foreach ($cList as $key => $val) {
            if (! array_key_exists($val[$this->F->UID], $res)) {
                if ($val['u_email'] == ''){
                    $uname = $val['u_mob_no'];
                }else{
                    $uname = $val['u_email'];
                }
                $res[$val[$this->F->UID]] = array(
                    'u_email' => $uname,
                    'pids' => array(
                        $val[$this->F->PID]
                    )
                );
            } else {
                $res[$val[$this->F->UID]]['pids'][] = $val[$this->F->PID];
            }
        }
        
        return $res;
    }

    /**
     * 根据用户名获取专属定制商品ID
     *
     * @param string $username            
     */
    public function getCustomizePids($username)
    {
        // 查找
        $UM = new UsersModel();
        $umRes = $UM->getUserByEMail($username);
        if (! $umRes) {
            return $umRes;
        }
        // 条件
        $condition = array(
            $this->F->UID => $umRes[$UM->F->ID]
        );
        return $this->where($condition)->select();
    }

    /**
     * 移除专属定制
     *
     * @param int $uid            
     */
    public function removeCustomizeList($uid)
    {
        // 条件
        if ($uid) {
            
            $condition = array(
                $this->F->UID => $uid
            );
            
            return $this->where($condition)->delete();
        }
        
        return false;
    }

    /**
     * 根据用户名设置专属定制商品ID
     *
     * @param string $username            
     * @param string $pidsStr            
     */
    public function setCustomizePids($username, $pidsStr)
    {
        $addRes = null;
        
        // 查找
        $UM = new UsersModel();
        $umRes = $UM->getUserByMobnoEmail($username);
        if (! $umRes) {
            return $umRes;
        }
        
        // 条件
        $condition = array(
            $this->F->UID => $umRes[$UM->F->ID]
        );
        
        // 删除
        $this->where($condition)->delete();
        
        // 插入
        $pidsArr = explode(',', $pidsStr);
        foreach ($pidsArr as $key => $val) {
            $insertData = array(
                $this->F->UID => $umRes[$UM->F->ID],
                $this->F->PID => $val
            );
            
            $addRes = $this->add($insertData);
        }
        
        return $addRes;
    }
}