<?php

class UserAddressModel extends Model
{

    /**
     *
     * @var DBF
     */
    protected $DBF;

    /**
     *
     * @var DBF_UserAddress
     */
    public $F;

    /**
     * 最多增加收货地址数量
     */
    const MAXADDLIMIT = 20;

    public function __construct()
    {
        $this->DBF = new DBF();
        $this->F = $this->DBF->UserAddress;
        $this->trueTableName = $this->F->_Table;
        
        $this->fields = $this->F->getFields();
        $this->_map = $this->F->getMappedFields();
        parent::__construct();
    }

    /**
     * 添加收货地址的验证条件
     *
     * @author miaomin@2015.6.6
     * @lastedited miaomin@2016.1.19
     * @param int $UID            
     * @param array $postArr            
     * @return boolean
     */
    public function verifyAddAddress($UID, $postArr)
    {
        $condition = array(
            $this->F->UserID => $UID,
            $this->F->IsRemove => 0
        );
        $addressCnt = $this->where($condition)->count();
        if ($addressCnt >= self::MAXADDLIMIT) {
            return false;
        }
        return true;
    }

    /**
     * 删除收货地址
     *
     * @author miaomin@2015.6.6
     * @param int $UID            
     * @param int $addressId            
     * @return boolean
     */
    public function removeAddressByUserID($UID, $addressId)
    {
        $addressInfo = $this->getAddressByID($addressId);
        
        $condition = array(
            $this->F->UserID => $UID,
            $this->F->ID => $addressId
        );
        $data = array(
            $this->F->IsRemove => 1
        );
        
        $removeRes = $this->where($condition)->save($data);
        
        if (! $removeRes) {
            return $removeRes;
        }
        
        if ($addressInfo[$this->F->IsDefault]) {
            $condition = array(
                $this->F->UserID => $UID,
                $this->F->IsRemove => 0,
                $this->F->IsDefault => 0
            );
            $selRes = $this->where($condition)
                ->order($this->F->ID . ' desc')
                ->limit(1)
                ->select();
            if ($selRes) {
                $condition = array(
                    $this->F->UserID => $UID,
                    $this->F->ID => $selRes[0][$this->F->ID]
                );
                $data = array(
                    $this->F->IsDefault => 1
                );
                $this->where($condition)->save($data);
            }
        }
        
        return true;
    }

    /**
     * 根据用户ID添加(保存)收货地址
     *
     * @author miaomin@2015.6.4
     * @param int $UID            
     * @param array $postArr            
     * @return boolean
     */
    public function addAddressByUserID($UID, $postArr)
    {
        $UAM = $this->bulidAddressData($UID, $postArr);
        $UAM->startTrans();
        
        if ($postArr['id']) {
            // 判断条件
            $condition = array(
                $UAM->F->ID => $postArr['id'],
                $this->F->IsRemove => 0,
                $UAM->F->UserID => $UID
            );
            $oldAddressInfo = $UAM->where($condition)->select();
            if (($oldAddressInfo) and ($oldAddressInfo[0][$UAM->F->IsDefault] == 1) and ($UAM->{$UAM->F->IsDefault} === 0)) {
                $selectDefaultCondition = array(
                    $this->F->UserID => $UID,
                    $this->F->IsRemove => 0,
                    $this->F->IsDefault => 0
                );
                $selRes = $this->where($selectDefaultCondition)
                    ->order($this->F->ID . ' desc')
                    ->limit(1)
                    ->select();
                if ($selRes) {
                    $setDefaultcondition = array(
                        $this->F->UserID => $UID,
                        $this->F->ID => $selRes[0][$this->F->ID]
                    );
                    $data = array(
                        $this->F->IsDefault => 1
                    );
                    $this->where($setDefaultcondition)->save($data);
                }
            }
            $Result = $UAM->where($condition)->save();
            if ($Result !== false) {
                $Result = $postArr['id'];
            }
        } else {
            $Result = $UAM->add();
        }
        // 保存失败
        if ($Result === false) {
            $UAM->rollback();
            return false;
        } else {
            $AddressID = $Result;
        }
        // 设置默认地址
        if (! $AddressID || (isset($postArr['defaultaddress']))) {
            $Result = $Result == 0 ? $AddressID : $Result;
            if ($UAM->setDefaultAddress($UID, $Result) === false) {
                $UAM->rollback();
                return false;
            }
        }
        
        $UAM->commit();
        return $AddressID;
    }

    /**
     * 组织用户收货地址数据
     *
     * @author miaomin@2015.6.4
     * @param int $uid            
     * @param array $Post            
     * @return UserAddressModel
     */
    private function bulidAddressData($uid, $Post)
    {
        $this->create();
        
        if ($Post['id']) {
            $this->{$this->F->ID} = $Post['id'];
        } else {
            $this->{$this->F->UserID} = $uid;
        }
        
        if (! $Post['defaultaddress']) {
            $this->{$this->F->IsDefault} = 0;
        }
        return $this;
    }

    public function getAddressByID($ID)
    {
        return $this->find($ID);
    }

    /**
     * miaomin edited@2015.6.6
     *
     * @param int $UID            
     * @param Object $Page
     * @param string $type
     */
    public function getAddressByUserID($UID,$type = '1', $Page = NULL)
    {
        if($type == 'address'){
            $condition = array(
                $this->F->UserID => $UID,
                $this->F->IsRemove => 0,
                $this->F->IsDefault=>0
            );
        }else if($type =='addresslist'){
            $condition = array(
                $this->F->UserID => $UID,
                $this->F->IsRemove => 0,

            );
        }else{
            $condition = array(
                $this->F->UserID => $UID,
                $this->F->IsRemove => 0,
                $this->F->IsDefault=>1
            );
        }
        if ($Page === null) {
            return $this->where($condition)
                ->order($this->F->ID . ' desc')
                ->select();
        } else {
            return $this->where($condition)
                ->order($this->F->ID . ' desc')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        }
    }

    /**
     * miaomin edited@2015.6.9
     *
     * @param int $UID            
     */
    public function getAddressCntByUserID($UID)
    {
        $condition = array(
            $this->F->UserID => $UID,
            $this->F->IsRemove => 0
        );
        return $this->where($condition)->count();
    }

    /**
     * miaomin added@2015.6.6
     *
     * @param int $UID            
     * @param int $addressId            
     */
    public function getAddressInfoById($UID, $addressId)
    {
        $condition = array(
            $this->F->UserID => $UID,
            $this->F->ID => $addressId
        );
        return $this->where($condition)->select();
    }

    public function getDefaultAddressByUserID($UID)
    { // 获得用户默认地址
        $addressInfo = $this->where($this->F->UserID . "='" . $UID . "' and ua_isdefault=1 ")->find();
        $AIPM = new AreaInfoPickerModel();
        $addressInfo['area'] = $this->getDispArea($AIPM, $addressInfo['ua_province'], $addressInfo['ua_city'], $addressInfo['ua_region']);
        return $addressInfo;
    }

    public function setDefaultAddress($UID, $UAID)
    {
        $data = array(
            $this->F->IsDefault => 0
        );
        if ($this->where($this->F->UserID . "='" . $UID . "'")->save($data) === false) {
            return false;
        }
        $data = array(
            $this->F->IsDefault => 1
        );
        if ($this->where($this->F->ID . "='" . $UAID . "'")->save($data) === false) {
            return false;
        }
        return true;
    }

    public function getAddressAreaByUserID($UID){
        $useraddress = $this->field('ua_id,ua_addressee,ua_province,ua_city,ua_region,ua_address,ua_mobile,ua_isdefault as isdefault')->where($this->F->UserID . "='" . $UID . "'")->order('ua_id desc')->select();
        $AIPM = new AreaInfoPickerModel();
        foreach ($useraddress as $key => $value) {
            $area[$key]['Province'] = $value['ua_province'];
            $area[$key]['City'] = $value['ua_city'];
            $area[$key]['Region'] = $value['ua_region'];
            $useraddress[$key]['area'] = $this->getDispArea($AIPM, $area[$key]['Province'], $area[$key]['City'], $area[$key]['Region']);
        }
        return $useraddress;
    }

    public function getDispArea(AreaInfoPickerModel $AIPM, $Province, $City, $Region){
        $Result .= $AIPM->getItemNameByID($Province) . ' ';
        $Result .= $AIPM->getItemNameByID($City) . ' ';
        $Result .= $AIPM->getItemNameByID($Region);
        return $Result;
    }

    private function getHtmlCtrl(AreaInfoPickerModel $AIPM)
    {
        $HtmlCtrl = array();
        $HtmlCtrl['AreaInfo'] = $AIPM->getJsonAreaInfo();
        $HtmlCtrl['AreaChildIndex'] = $AIPM->getJsonChildIndex();
        return $HtmlCtrl;
    }



    /*
     * 根据用户ID和订单地址判断订单的地址状态
     * param @uid 用户id
     * param @up_address 订单上的地址显示，订单无地址传0
     * 返回：int 1-用户有地址，订单有地址 2-用户有地址订单无地址 3-用户和订单都无地址
     */
    public function AddressStatus($uid, $up_address){

    }
}