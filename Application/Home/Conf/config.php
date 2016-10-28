<?php
return array(
	'LOAD_EXT_CONFIG'	=> 'db',

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
	],
);