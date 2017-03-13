<?php

namespace Home\Controller;
use Home\Cart\Cart;

class BuyController extends CommonController
{

    public function addGoodsAction()
    {
        // 得到购物车对象
        $cart = new Cart;
        $goods_id = I('post.goods_id');
        $buy_quantity = I('post.buy_quantity');
        $goods_product_id = I('post.goods_product_id');
        // 利用购物车对象, 完成购物车商品添加
        $cart->addGoods($goods_id, $buy_quantity, $goods_product_id);

        // 跳转到购物车页面
        $this->redirect('/cart', [], 0);
    }

    public function cartAction()
    {

        $cart = new Cart;
        // 通过购物车, 获取商品信息列表
        $goods_list = $cart->getGoodsList();
        $this->assign('goods_list', $goods_list);
        // 获取购物车信息
        $this->assign('cart_info', $cart->getCartInfo());


        $this->display();
    }

    public function removeGoodsAction()
    {
         $cart = new Cart;
         $goods_id = I('request.goods_id');
         $goods_product_id = I('request.goods_product_id');

         $result = $cart->removeGoods($goods_id, $goods_product_id);

         if ($result) {
            $this->ajaxReturn(['error'=>0]);
         } else {
            $this->ajaxReturn(['error'=>1, 'errorInfo'=>$cart->getError()]);
         }
    }

    /**
     * 订单确认
     * @return [type] [description]
     */
    public function orderAction()
    {
        // 校验是否登录
        $this->checkLogin('/order');

        $this->display();
    }

    /**
     * 订单生成
     * @return [type] [description]
     */
    public function checkoutAction()
    {
        if (! $this->checkLogin('', false)) {
            // 会员未登录
            $this->ajaxReturn(['error'=>1, 'errorInfo'=>'会员未登录']);
        }
        // 1：先将订单生成(订单属于未确定), 加入订单处理队列
        $order = I('post.');// 地址, 货运, 支付方式
        // 购物车商品信息
        $cart = new Cart;
        $order['goods_list'] = $cart->getGoodsListRaw();// raw获取原始的购物车商品列表(不包含商品的额外信息部分)
        $order['member_id'] = session('member.member_id');
        // 形成唯一的订单ID
        // 时间+额外标志(随机数, 递增数)
        // microtime() . mt_rand(0-10000)
        // microtime() . $redis->incr('order_sn');//0.87614400 1478849437
        $redis = new \Redis;
        $redis->connect('127.0.0.1', '6379');
        

        $time_arr = explode(' ', microtime()); 
        $order_sn = $time_arr[1] . substr($time_arr[0], 2, 6);
        $order_sn .= $redis->incr('order_sn');
        $order['order_sn'] = $order_sn;
         
        // 将订单信息加入队列
        // 将数组序列化之后存储到订单队列
        $result = $redis->lpush('order_list', serialize($order));

        // 返回结果
        if ($result) {
            $this->ajaxReturn(['error'=>0, 'order_sn'=>$order_sn, 'order_url'=>U('/orderInfo/'.$order_sn)]);
        } else {
            $this->ajaxReturn(['error'=>1, 'errorInfo'=>'订单未被添加']);
        }

        // 另外的进程处理队列, 更新处理结果,  订单生成成功， 将订单进入order表！ 

    }

    public function orderInfoAction()
    {
        $this->assign('order_sn', I('get.order_sn'));
        $this->display();
    }

    public function ajaxAction()
    {
        $operate = I('request.operate', '', 'trim');
        if ($operate === '') {
            $this->ajaxReturn(['error'=>1, 'errorInfo'=>'没有指定操作']);
        }

        switch ($operate) {
            case 'memberAddress':
                if (! $this->checkLogin('', false)) {
                    // 会员未登录
                    $this->ajaxReturn(['error'=>1, 'errorInfo'=>'会员未登录']);
                }

                // // 获取会员的货运地址
                $rows = M('Address')->alias('a')
                    ->field('a.*, rc.title country_title, rz.title zone_title, rcc.title city_title')
                    ->where(['member_id'=>session('member.member_id')])
                    ->join('left join __REGION__ rc On rc.region_id=a.country_id')// 省级地区表
                    ->join('left join __REGION__ rz On rz.region_id=a.zone_id')// 市级地区表
                    ->join('left join __REGION__ rcc On rcc.region_id=a.city_id')// 县级地区表
                    ->select();

                if ($rows) {
                    $this->ajaxReturn(['error'=>0, 'rows'=>$rows]);
                } else {
                    $this->ajaxReturn(['error'=>1, 'errorInfo'=>'会员还没有货运地址']);
                }
                break;
            
            case 'getRegion':
                $level = I('request.level', '0');
                $region_id = I('request.region_id', '0');

                // 获取(1, 初始)省级初始数据
                if ($level == '0') {
                    $cond = ['parent_id'=>1];
                } else {
                    $cond = ['parent_id'=>$region_id];
                }

                $rows = M('Region')->where($cond)->select();

                if ($rows) {
                    $this->ajaxReturn(['error'=>0, 'rows'=>$rows]);
                } else {
                    $this->ajaxReturn(['error'=>1, 'errorInfo'=>'没有地区']);
                }
                break;

            // 添加地址
            case 'addAddress':
                if (! $this->checkLogin('', false)) {
                    // 会员未登录
                    $this->ajaxReturn(['error'=>1, 'errorInfo'=>'会员未登录']);
                }

                $member_id = session('member.member_id');

                // 添加新的地址, 设置为默认
                // 设置会员ID, 与默认地址
                M('Address')->auto([['is_default', '1'], ['member_id', $member_id]])->create();
                $address_id = M('Address')->add();

                // 将其他的地址设置为非默认
                M('Address')->where(['member_id'=> $member_id, 'address_id'=>['neq', $address_id]])->save(['is_default'=>'0']);

                $this->ajaxReturn(['error'=>0]);
                break;
            case 'getPayment':
                $rows = M('Payment')->where(['enabled'=>1])->order('sort_number')->select();
                if ($rows) {
                    $this->ajaxReturn(['error'=>0, 'rows'=>$rows]);
                } else {
                    $this->ajaxReturn(['error'=>1, 'errorInfo'=>'没有支付方式']);
                }
                break;
            case 'getShipping':
                $rows = M('Shipping')->where(['enabled'=>1])->order('sort_number')->select();
                    
                if ($rows) {
                    // 遍历配送插件 获取运费
                    foreach($rows as & $row) {
                        $class = 'Common\Shipping\\' . $row['key'];
                        $shipping = new $class;
                        // $rows[$k]['price'] = 
                        $row['price'] = $shipping->price();
                    }
                    $this->ajaxReturn(['error'=>0, 'rows'=>$rows]);
                } else {
                    $this->ajaxReturn(['error'=>1, 'errorInfo'=>'没有配送方式']);
                }
                break;
            case 'getGoods':
                if (! $this->checkLogin('', false)) {
                    // 会员未登录
                    $this->ajaxReturn(['error'=>1, 'errorInfo'=>'会员未登录']);
                }

                $cart = new Cart;
                // 通过购物车, 获取商品信息列表
                $goods_list = $cart->getGoodsList();
                // 获取购物车信息
                $cart_info = $cart->getCartInfo();

                $this->ajaxReturn(['error'=>0, 'rows'=>$goods_list, 'cartInfo'=>$cart_info]);
                break;


            case 'getOrderStatus':
                $order_sn = I('request.order_sn');
                // 获取订单状态
                $redis = new \Redis;
                $redis->connect('127.0.0.1', '6379');

                $status = $redis->hget('order', $order_sn);
                if ($status) {
                    // 订单处理完毕
                    if($status == 'yes') {
                        $result = '订单生成成功';
                    } else {
                        $result = '订单失败';
                    }
                } else {
                    $result = '处理中';
                }   

                $this->ajaxReturn(['error'=>0, 'status'=>$result]);
                break;
            case 'getOrderStatusLong':
                # code...
                # 设置最大的执行时间
                ini_set('max_execution_time', '0');// 服务器一直执行

                $order_sn = I('request.order_sn');
                // 获取订单状态
                $redis = new \Redis;
                $redis->connect('127.0.0.1', '6379');

                while (true) {
                    $status = $redis->hget('order', $order_sn);
                    if ($status) {
                        break;
                    }
                }
                if ($status) {
                    // 订单处理完毕
                    if($status == 'yes') {
                        $result = '订单生成成功';
                    } else {
                        $result = '订单失败';
                    }
                } else {
                    $result = '处理中';
                }   

                $this->ajaxReturn(['error'=>0, 'status'=>$result]);
                break;

        }
    }


    public function _before_repeatAction() {
        C('TOKEN_ON', true); // 是否开启令牌验证 默认关闭
        C('TOKEN_NAME', '__hash__'); // 令牌验证的表单隐藏字段名称，默认为__hash__
        C('TOKEN_TYPE', 'md5'); //令牌哈希验证规则 默认为MD5
        C('TOKEN_RESET', true); //令牌验证出错后是否重置令牌 默认为true
    }
    public function repeatAction()
    {

        if(IS_POST) {
            // 下订单
            $model = M('Order');
            if ($model->create()) {
                // $model->add();
            } else {
                echo $model->getError();
            }
            
        } else {
            // 展示订单表单
            $this->display();
        }
    }
}