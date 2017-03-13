<?php
return array(
	'LOAD_EXT_CONFIG'	=> 'db',

    'DEFAULT_CONTROLLER'    => 'Shop',
    'DEFAULT_ACTION'    => 'index',

	'TMPL_ACTION_ERROR'     =>  'Common/jump_error', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  'Common/jump_success', // 默认成功跳转对应的模板文件

    'URL_MODEL'	=> 2,

    'SESSION_TYPE' => 'Db',// 使用Db方式存储session Think\Session\Driver\Db

	//'配置项'=>'配置值'
	'URL_ROUTER_ON' => true,
	'URL_ROUTE_RULES' => [
		'register'	=> 'Member/register',// 注册URL
		'center'	=> 'Member/center', // 用户中心
		'login'		=> 'Member/login', // 登陆
		'verify'	=> 'Member/verify',// 验证码
		'logout'	=> 'Member/logout', // 退出

       
        'index' => 'Shop/index',
        // 带参数的路由
        'goods/:goods_id\d'   => 'Shop/goods',

        'addGoods'  => 'Buy/addGoods',
        'cart'  => 'Buy/cart',
        'remove'    => 'Buy/removeGoods',
        'order' => 'Buy/order',
        'ajax'  => 'Buy/ajax',
        'checkout'  => 'Buy/checkout',
        'orderInfo/:order_sn' => 'Buy/orderInfo',
	],

    //支付宝配置参数
    'alipay_config'=>array(
    'partner' =>'20********50',   //这里是你在成功申请支付宝接口后获取到的PID；
    'key'=>'9t***********ie',//这里是你在成功申请支付宝接口后获取到的Key
    'sign_type'=>strtoupper('MD5'),
    'input_charset'=> strtolower('utf-8'),
    'cacert'=> getcwd().'\\cacert.pem',
    'transport'=> 'http',
    ),
     //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；

    'alipay'   =>array(
         //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email'=>'pay@xxx.com',
        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url'=>'http://www.xxx.com/Pay/notifyurl', 
        //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'return_url'=>'http://www.xxx.com/Pay/returnurl',
        //支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
        'successpage'=>'User/myorder?ordtype=payed',   
        //支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
        'errorpage'=>'User/myorder?ordtype=unpay', 
    ),
);