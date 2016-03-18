<?php
return array(
	'TMPL_FILE_DEPR'		=> '_' ,
	
	'USER_AUTH_KEY'			=> 'aASDKatKAS',
	
	
	//后台菜单
	'ADMIN_MENU_LIST'		=> array(
		1 => array(
				'name'	=> '代理商管理',
				'icon'	=> '19',
				'list'	=> array(
						array(
							'name'	=> '代理商管理',
							'icon'	=> '20',
							'list'	=> array(
									'代理商列表|Agen/index',				
							)
						),
				)		
		),
		2 => array(
				'name'	=> '商户管理',
				'icon'	=> '28',
				'list'	=> array(
						array(
							'name'	=> '商户管理',
							'icon'	=> 29,
							'list'	=> array(
									'商户列表|Merchant/Index',	 			
							)
						),
						array(
							
							'name'	=> '商户app',
							'icon'	=> 29,
							'list'	=> array(
									'应用列表|Merchant/appList',	 			
							)
						),	
				)		
		),
		3 => array(
				'name'	=> '会员管理',
				'icon'	=> '43',
				'list'	=> array(
						array(
							'name'	=> '会员管理',
							'icon'	=> '44',
							'list'	=> array(
									'会员列表|User/vusersList', 				
							)
						),
				)		
		),
		4 => array(
				'name'	=>	'运营管理',
				'icon'	=>	'47',
				'list'	=>array(
 						/*
						array(
							'name'	=>'运营管理',
							'icon'	=>	'49',
							'list'	=>array(
									'广告管理|Advertisement/index',
								)
						),
						*/
						array(
							'name'	=> '推送管理',
							'icon'	=>	'48',
							'list'	=> array(
									'推送列表|Message/messagesList',				
							)
						),
						array(
							'name'	=> '通知管理',
							'icon'	=>	'133',
							'list'	=> array(
									'通知列表|Notice/noticesList', 				
							)
						),
				),
		),
		
		5 => array(
				'name'	=> '财务管理',
				'icon'	=> '63',
				'list'	=> array(
						array(
							'name'	=> '订单管理',
							'icon'	=> '64',
							'list'	=> array(
									'订单列表|Order/ordersList',				
							)
						),
						array(
							'name'	=> '财务明细',
							'icon'	=> '67',
							'list'	=> array(
									'收入明细|Accounting/incomeInfo', 
									'提现明细|Accounting/mentionInfo',
							//		'申请提现|Accounting/addMention',
									
							)
						)
				)		
		),
		6 => array(
				'name'	=> '设备管理',
				'icon'	=> '176',
				'list'	=> array(
						array(
							'name'	=> '设备列表',
							'icon'	=> '177',
							'list'	=> array(
									'设备列表|Device/deviceList',				
							)
						)
				)		
		)
	),
);