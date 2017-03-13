<?php

namespace Home\Cart;

/**
 * 购物车类
 */
class Cart
{
    private $goods_list = [];// 存储购物车内商品的属性

    public function __destruct()
    {
        $this->saveGoods();// 存储商品数据
    }
    /**
     * 持久化存储购物车中商品的信息
     * @return [type] [description]
     */
    private function saveGoods()
    {
        if ($member = session('member')) {
            $cart_goods = M('CartGoods');
            // 用户处于登录状态
            // 遍历所有的购物车中的商品, 
            $cart_goods_list = [];// 操作过主键
            foreach($this->goods_list as $key=>$goods) {
                // 判断该商品是否存在于数据表中
                $cond['member_id'] = $member['member_id'];
                $cond['goods_id'] = $goods['goods_id'];
                $cond['goods_product_id'] = $goods['goods_product_id'];
                // 查询判断
                // 记录当前处理过的所购的商品的ID
                if ($cart_goods->where($cond)->find()) {
                    $cart_goods_list[] = $cart_goods->cart_goods_id;
                    // 数据存在
                    // (ORM) AR模式语法更新
                    $cart_goods->buy_quantity = $goods['buy_quantity'];
                    $cart_goods->save();
                } else {
                    // 没有找到
                    $data = $cond;
                    $data['buy_quantity'] = $goods['buy_quantity'];
                    $cart_goods_list[] = $cart_goods->add($data);
                }
            }

            // 删除没有操作过的
            $cond = [
                'member_id'=>$member['member_id'], 
                'cart_goods_id'=>['not in', $cart_goods_list], 
                ];
            $cart_goods->where($cond)->delete();

        } else {
            // 存储到cookie中
            cookie('cart_goods_list', serialize($this->goods_list), ['expire'=>30*24*3600]);
        }
    }

    public function __construct()
    {
        $this->initGoods();// 初始商品数据
    }
    /**
     * 初始化购物车商品
     * @return [type] [description]
     */
    private function initGoods()
    {
        // 从cookie或者数据表中读取数据
        // 根据用户的登录状态判断
        if ($member = session('member')) {
            // 从数据表获取
            $cond['member_id'] = $member['member_id'];
            $rows = M('CartGoods')->field('goods_id, goods_product_id, buy_quantity')->where($cond)->select();
            // 拼凑成 goods_id:goods_product_id这种下标
            $goods_list = [];
            foreach($rows as $goods) {
                // 拼凑key
                $goods_key = $goods['goods_id'] . ':' . $goods['goods_product_id'];
                $goods_list[$goods_key] = $goods;
            }

        } else {
            // 从cookie中获取
            $goods_list = unserialize(cookie('cart_goods_list'));
        }

        // 存储到$this->goods_list中
        $this->goods_list = $goods_list ? $goods_list : [];  
    }

    /**
     * 合并cookie中的商品到, 数据表中.
     * @return [type] [description]
     */
    public function mergeCookieGoods()
    {
        // 从cookie中获取
        $goods_list = unserialize(cookie('cart_goods_list'));
        // 合并到goods_list属性中:
        $this->goods_list = array_merge($this->goods_list, $goods_list ? $goods_list : []); 
    }




    /**
     * 添加商品到购物车
     */
    public function addGoods($goods_id, $buy_quantity=1, $goods_product_id=0)
    {
        $goods_key = $goods_id . ':' . $goods_product_id;

        // 判断该商品(货品)是否已经购买
        if (isset($this->goods_list[$goods_key])) {
            // 购买过
            // 仅仅需要调整数量
            $this->goods_list[$goods_key]['buy_quantity'] += $buy_quantity;
        } else {
            // 未购买过, 加入商品信息
            $this->goods_list[$goods_key] = [
                'goods_id'  => $goods_id, 
                'buy_quantity'  => $buy_quantity,
                'goods_product_id'  => $goods_product_id,
            ];
        }

    }

    public function getGoodsList()
    {
        $m_goods = D('Goods');
        $m_product_option = M('ProductOption');
        
        $return_goods_list = [];
        foreach($this->goods_list as $key=>$goods)
        {
            // 获取每个商品的详细信息
            $goods_info = $m_goods->field('goods_id', 'image', 'name', 'price')->find($goods['goods_id']);
            // 获取型号(属性组合)
            $option_list = $m_product_option->alias('po')->field('ga.title ga_title, ao.title ao_title')->join('left join __ATTRIBUTE_OPTION__ ao using(attribute_option_id)')->join('left join __GOODS_ATTRIBUTE__ ga using(goods_attribute_id)')->where(['goods_product_id'=>$goods['goods_product_id']])->select();
            $goods_info['option_list'] = $option_list;

            // 合并全部的商品信息
            $goods_info = array_merge($goods_info, $goods);
            // 获取当前商品的真是价格
            // 商品, 货品, 会员决定
            $member = session('member');
            $goods_info['real_price'] = $m_goods->getPrice($goods['goods_id'], $goods['goods_product_id'], $member?$member['member_id']:0);

            $return_goods_list[$key] = $goods_info;
        }

        return $return_goods_list;
    }

    /**
     * [removeGoods description]
     * @param  [type]  $goods_id         [description]
     * @param  integer $goods_product_id [description]
     * @return [type]                    [description]
     */
    public function removeGoods($goods_id, $goods_product_id=0)
    {
        $goods_key = $goods_id . ':' . $goods_product_id;
        if (isset($this->goods_list[$goods_key])) {
            // 删除即可
            unset($this->goods_list[$goods_key]);
            return true;
        } else {
            $this->error = '商品(货品)不存在';
            return false;
        }

    }

    public function getCartInfo()
    {
        $total_price = 0;
        $total_weight = 0;// 以g计算单位
        $m_goods = D('Goods');

        foreach($this->goods_list as $key=>$goods) {
            $row = $m_goods->field('weight, wu.title weight_title')->join('left join __WEIGHT_UNIT__ wu using(weight_unit_id)')->find($goods['goods_id']);
            switch ($row['weight_title']) {
                case '克':
                    $total_weight += $row['weight']*$goods['buy_quantity'];
                    break;
                case '千克':
                    $total_weight += $row['weight']*1000*$goods['buy_quantity'];
                    break;
                case '500克(斤)':
                    $total_weight += $row['weight']*500*$goods['buy_quantity'];
                    break;
            }
            $member = session('member');
            $total_price += $m_goods->getPrice($goods['goods_id'], $goods['goods_product_id'], $member?$member['member_id']:0)*$goods['buy_quantity'];
        }

        return ['total_price'=>$total_price, 'total_weight'=>$total_weight];
    }

    public function getGoodsListRaw()
    {
        return $this->goods_list;
    }

    private $error;
    public function getError()
    {
        return $this->error;
    }

    public function clearGoods()
    {

    }


}