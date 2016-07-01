<?php
return array(
	'DEFAULT_THEME'			=> 'Default',
	'TMPL_FILE_DEPR'		=> '_' ,
	'USER_AUTH_ON'			=> false,
	'NEW_ACTIVE_NUMBER'     => 3,  //首页展示的活动数量
	'NEW_COUPON_NUMBER'     => 3,  //首页展示的优惠券数量
	'WXCONFIGPATH' => 'xiyiapp', //微信公众号的配置，包含微信支付的配置目录
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

	// 微信
	'WECHAT'				=> array(
				'appid' => 'wx78d413885fae28e4',
				'appsecret' => '4faea72dd3685e06ba94aac2f9fdfe0e',
				'apptoken' => 'LUYixUo6NewPWjOnDRjmgmDQllGGhYAilTX-PgqKQWRGUU7M0vg940M-zIULmWj0yvOR0DuOp0oJJ5P7ZAzh0ZhR8G3W9Pzn1XvBGVuIECILQ2fqGCzXSP-zA9Xh4lCpYTNfAGAEZT',
	),


	'EXPRESS_JID'			=> array(
		'438'	=> array(
				'd_company'  => '杭州天湖洗衣',
				'd_contact'  => '李平',
				'd_telphone' => '13958173174',
				'd_address'  => '杭州市江干区丁桥镇天鹤路318号',
		),
	),


	/* 快递参数设置 */
    'EXPRESS_CHECKHEADER'   =>  array(      //客户卡号,校验码
        'SF'    => '5713519013',    //AZS //5713519013
    ),

    'EXPRESS_CHECKWORD'     =>  array(      //checkword 秘钥
        'SF'    => 'mv30QsuGvxWigY9Fdv6TN4aMdIi95xE9',      //mv30QsuGvxWigY9Fdv6TN4aMdIi95xE9 ////qYiwqrF1nvPc
    ),

    'EXPRESS_URL'           =>  array(      //快递类服务接口url
        'SF'    => 'http://bsp-ois.sf-express.com/bsp-ois/sfexpressService',    //http://218.17.248.244:11080/bsp-oisp/sfexpressService//http://bsp-ois.sf-express.com/bsp-ois/sfexpressService
    ),

    'MONTHLY_NUM'           => '5713519013',


    //模板功能
	'FUNC_MENU'	=> array(
		array('id'=>'1', 'name'=>'测试功能', 'url'=>'/Index/new2.html'),
		array('id'=>'2', 'name'=>'活动', 'url'=>'/Index/new2Activity.html'),
		array('id'=>'3', 'name'=>'评价', 'url'=>'/Comments/index.html'),
		// array('id'=>'4', 'name'=>'资讯', 'url'=>'/Sales/goods/ctype/1.html?menucode=goods'),
		// array('id'=>'5', 'name'=>'大转盘', 'url'=>'/Sales/goods/ctype/1.html?menucode=goods'),
		array('id'=>'6', 'name'=>'商品', 'url'=>'/Index/new2.html'),
		array('id'=>'7', 'name'=>'关于我们', 'url'=>'/User/aboutus.html'),
	),


	//新模板
	'NEW_THEMES'		=> array(
			'new1',
			'new2',
	),


);