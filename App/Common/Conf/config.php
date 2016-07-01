<?php
return array(
	// 服务器上 数据库常用配置
	'DB_TYPE'			=>	'mysql',			    // 数据库类型
	'DB_HOST'			=>	'localhost',			// 数据库服务器地址
	'DB_NAME'			=>	'azd',					// 数据库名
	'DB_USER'			=>	'root',					// 数据库用户名
	
	'DB_PWD'			=>	'azdingdb123',						// 数据库密码 	

	'DB_PORT'			=>	3306,				    // 数据库端口
	'DB_PREFIX'			=>	'azd_',		            // 数据库表前缀
	'DB_CHARSET'		=>	'utf8',				    // 数据库编码
	'DB_FIELDS_CACHE'	=>	true,					// 启用字段缓存	
	'URL_MODEL'			=>  2,
	
	//Memcache 配置
	'MEMCACHE_HOST'		=> 'localhost',
	'MEMCACHE_PORT'		=> 11321,
	
	
	//默认执行的模块
	'DEFAULT_MODULE'    => 'Home',
	
	'USER_COOKIE_MID'	=> 'KJOKNXSID',
	'USER_COOKIE_JID'	=> 'ZLKDFIWLS',
	'USER_COOKIE_SID'	=> 'SIISLKNSO',
	'USER_COOKIE_TPE'	=> 'TISLNSOWK',	
	
	//显示错误信息
	'SHOW_ERROR_MSG'	=> true,
	
	//分组模块
	'APP_GROUP_LIST' 	=> 'System,Agent,Merchant,Mobile,Ap,Demo,Merchantapp,Rebateapp,Capper,Home',
	'DEFAULT_GROUP'     => 'Mobile',
	
	// 开启子域名配置
	'APP_SUB_DOMAIN_DEPLOY'	=> 1, 
	'APP_SUB_DOMAIN_RULES'	=> array(
			'xt'		=> array('System'),
			'dl'		=> array('Agent'),  
			'sj'		=> array('Merchant'),
			'yd'		=> array('Mobile'),
			'ap'		=> array('Ap'),
			'app'		=> array('Merchantapp'),
			'ce'		=> array('Demo'),
			'flapp'		=> array('Rebateapp'),
			'tr'		=> array('Capper'),
			'ho'        => array('Home'),
	),
	
	//邮件配置
	"MAIL"=>array(
			"mail_host"	=> 'smtp.ym.163.com',
			"mail_port"	=> "25",
			"mail_auth" => false,
			"mail_user"	=> "service@azding.com",
			"mail_pwd"	=> "azdingservice88^"
	),


	'URL_ROUTER_ON'   	=> true,// 开启路由
	'URL_ROUTE_RULES'	=>array(
        'v/:id' => 'Specialview/index',
		'scene/view/id/:id' => 'Specialview/view',
		'scene/addpv/id/:id' => 'Specialview/addpv'
    ),


	//普通会员是VIP会员的返现比例
	'USER_RATION_VIP'		=> 0.1,
	
	//注册VIP会员的价格
	'REGISTER_VIP'			=> 9.9,
	'REGISTER_VIP_GRADE'	=> array(5, 3, 1),//其中前面的是父级邀请人，最后一位是所属代理商提成
	
	//下订单返现比例
	'ORDER_RETURN_GRADE' 	=> array(60, 7, 7, 7, 7),//按百分比, 自己/邀请人/父邀请人/业务员/商家所在的代理商， 其余是OS

	//发送快递用户
	'EXPRESS_JID'			=> array(
		'438'	=> array(
				'd_company'  => '杭州天湖洗衣',
				'd_contact'  => '李平',
				'd_telphone' => '13958173174',
				'd_address'  => '杭州市江干区丁桥镇天鹤路318号',
		),
	),
	'CODEKEY' => 'yhujikuytgfrtyhu',
);

