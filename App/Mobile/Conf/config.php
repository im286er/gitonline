<?php
return array(
	'DEFAULT_THEME'			=> 'Default',
	'TMPL_FILE_DEPR'		=> '_' ,
	'USER_AUTH_ON'			=> false,
	'NEW_ACTIVE_NUMBER'     => 2,  //首页展示的活动数量
	'NEW_COUPON_NUMBER'     => 2,  //首页展示的优惠券数量
	'WXCONFIGPATH' => 'tuoer', //微信公众号的配置，包含微信支付的配置目录
	//腾讯QQ登录配置
	'THINK_SDK_QQ' => array(
		'APP_KEY'    => '101201335', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '4f62e7aa12f1ebc0b1168b478034748d', //应用注册成功后分配的KEY
		'CALLBACK'   => 'qq',
	),
	
	//新浪微博配置
	'THINK_SDK_SINA' => array(
		'APP_KEY'    => '4176726483', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'd9e0d10f6ca88f8cecab580343df5a1b', //应用注册成功后分配的KEY
		'CALLBACK'   =>'sina',
	),
	'TMPL_LOAD_DEFAULTTHEME'=>true,
	'PAY_TYPE' => array(
		'alipaywap',
		'alipaypc',
	),
);