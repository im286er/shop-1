<?php

/**
 * 非DIY类商品购物条目类
 *
 * @author miaomin 
 * Dec 23, 2014 5:56:14 PM
 *
 * $Id$
 */
class CartItemNoneDiyModel extends AbstractCartItem
{

    /**
     * 购物车条目参数
     *
     * @var unknown_type
     */
    protected $_args = array(
        
        // ==============必要参数==============
        
        // UCID
        'ucid' => null,
        
        // PID
        'pid' => null,
        
        // Product Type
        'producttype' => null,
        
        // UID
        'uid' => null,
        
        // Is Real Object
        'realobject' => null,
        
        // 单价
        'unitprice' => null,
        
        // 数量
        'count' => null,
        
        // 折扣
        'discount' => null,
        
        // 总价
        'amount' => null,
        
        // 实际支付
        'realamount' => null,
        
        // Need Shipping Address
        'needaddress' => null,
        
        // 捆绑商品
        'bindids' => null,
        
        // 是否捆绑商品
        'isbind' => null,
        
        // 捆绑主商品
        'masterid' => null,
        
        // 捆绑主商品ucid
        'handleuc' => null
    );

    /**
     * 映射关系
     *
     * @var unknown_type
     */
    protected $_map = array(
        
        // VAL值为$_REQUEST
        'pid' => 'pid',
        'ucid' => 'itemid',
        'count' => 'count',
        'realobject' => 'isreal',
        'bindids' => 'bindids',
        'masterid' => 'masterid',
        'handleuc' => 'handleuc'
    );

    /**
     * 非DIY类商品购物条目类
     */
    public function __construct()
    {}

    /**
     * 价格计算
     *
     * @see AbstractCartItem::calcprice()
     */
    public function calcprice()
    {
        $defaultPrice = '98';
        
        $PM = new ProductModel();
        
        $pmRes = $PM->find($this->_args['pid']);
        
        if ($pmRes) {
            // 计算促销活动后的单价
            $SPProductM = new SPProductModel();
            $pmRes[$PM->F->Price] = $SPProductM->calcSPPriceByPid($this->_args['pid']);
            return $pmRes[$PM->F->Price];
        }
        
        return $defaultPrice;
    }

    /**
     * 增加一件到购物车
     *
     * @see AbstractCartItem::add()
     */
    public function add()
    {
        // var_dump ( $this->_args );
        
        // 添加user_cart
        $UCM = new UserCartModel();
        $UCM->create($this->_args);
        $addRes = $UCM->add();
        
        return $addRes;
    }

    /**
     * 移除一件到购物车
     *
     * @see AbstractCartItem::remove()
     */
    public function remove()
    {
        $removeRes = null;
        if ($this->_args['ucid'] && $this->_args['uid']) {
            
            // 删除user_cart
            $UCM = new UserCartModel();
            $condition = array(
                $UCM->F->ID => $this->_args['ucid'],
                $UCM->F->UserID => $this->_args['uid']
            );
            $ucRes = $UCM->where($condition)->find();
            if ($ucRes) {
                $removeRes = $UCM->where($condition)->delete();
                if ($ucRes[$UCM->F->IsBind] == 1) {
                    $condition = array(
                        $UCM->F->HandleUc => $this->_args['ucid'],
                        $UCM->F->UserID => $this->_args['uid']
                    );
                    $removeRes = $UCM->where($condition)->delete();
                }
            }
        }
        
        return $removeRes;
    }

    /**
     * 是否已经添加
     *
     * @see AbstractCartItem::isAlreadyAdd()
     */
    public function isAlreadyAdd()
    {
        $res = null;
        
        if ($this->_args['bindids'] == '') {
            // 无捆绑判断
            $res = $this->_noneBindIsAlreadyAdd();
        } else {
            // 捆绑判断
            $res = $this->_bindIsAlreadyAdd();
        }
        
        return $res;
    }
    
    /*
     * 无捆绑判断添加
     */
    private function _noneBindIsAlreadyAdd()
    {
        $res = null;
        
        $UCM = new UserCartModel();
        
        $condition = array(
            $UCM->F->UserID => $this->_args['uid'],
            $UCM->F->ProductID => $this->_args['pid'],
            $UCM->F->IsReal => $this->_args['realobject'],
            $UCM->F->IsBind => 0
        );
        
        $res = $UCM->where($condition)->select();
        
        return $res;
    }
    
    /*
     * 捆绑判断添加
     */
    private function _bindIsAlreadyAdd()
    {
        $res = null;
        
        $UCM = new UserCartModel();
        
        $condition = array(
            $UCM->F->UserID => $this->_args['uid'],
            $UCM->F->ProductID => $this->_args['pid'],
            $UCM->F->IsReal => $this->_args['realobject'],
            $UCM->F->IsBind => 1,
            $UCM->F->BindsIds => $this->_args['bindids']
        );
        
        $res = $UCM->where($condition)->select();
        
        return $res;
    }

    /**
     * 生成一段购物车界面
     */
    public function renderIndex($specDeli = '<br>')
    {
        $templatesArr = array();
        
        $UCM = new UserCartModel();
        $condition = array(
            $UCM->F->ID => $this->_args['ucid'],
            $UCM->F->UserID => $this->_args['uid']
        );
        $ucmRes = $UCM->where($condition)->find();
        
        $PM = new ProductModel();
        $pmRes = $PM->find($this->_args['pid']);
        
        // 参加促销活动信息
        $SPProductM = new SPProductModel();
        $SPProductRes = $SPProductM->getSPDetailRenderByPid($this->_args['pid']);
        if ($SPProductRes) {
            $templatesArr['spenable'] = 1;
            $templatesArr['splist'] = $SPProductRes;
        }
        
        $templatesArr['count'] = $ucmRes[$UCM->F->Count];
        $templatesArr['unitprice'] = $pmRes[$PM->F->Price];
        $templatesArr['removelink'] = 'cart/removeitem/itemid/' . $this->_args['ucid'];
        $templatesArr['propspec'] = ProductPropValModel::parseCombinePropVals($pmRes[$PM->F->PropIdSpec], $specDeli);
        
        return $templatesArr;
    }

    /**
     * 价格小计
     */
    public function amount()
    {
        $amount = 0;
        
        $UCM = new UserCartModel();
        $condition = array(
            $UCM->F->ID => $this->_args['ucid'],
            $UCM->F->UserID => $this->_args['uid']
        );
        $ucmRes = $UCM->where($condition)->find();
        
        // 获取单价
        /*
        $PM = new ProductModel();
        $pmRes = $PM->find($this->_args['pid']);
        */
        
        // $amount += $ucmRes[$UCM->F->Count] * $pmRes[$PM->F->Price];
        $amount += $ucmRes[$UCM->F->Count] * $this->calcprice();
        
        return $amount;
    }
}
?>