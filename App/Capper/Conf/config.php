<?php
return array(
	'DEFAULT_THEME'			=> 'Default',
	'TMPL_FILE_DEPR'		=> '_' ,
	'USER_AUTH_ON'			=> false,
	'WECHAT'				=> array(
				'appid' => 'wxced84b44ef1a07bd',
				'appsecret' => '5e7acbb329e9320ecd3dba07d53f7e23',
				'apptoken' => 'tuoer',
	),
	

	//流量 (流量-价格-折扣)
	'flow'		=> 		array(
		'title'		=> '流量',
		'data1'		=> array(//移动
			2	=> array(2, 1, 0.96),
			10	=> array(10, 3, 0.96),
			30	=> array(30, 5, 0.96),
			70	=> array(70, 10, 0.96),
			500	=> array(500, 30, 0.96),
			700	=> array(700, 40, 0.96),
			1024=> array(1024, 50, 0.96),
		),
		'data2'		=> array(//联通
			20	=> array(20, 3, 1),
			50	=> array(50, 6, 1),
			100	=> array(100, 10, 1),
			200	=> array(200, 15, 1),
			500	=> array(500, 30, 1),
			1024=> array(1024, 100, 1)			
		),
		'data3'		=> array(//电信
			5	=> array(5, 1, 0.98),
			10	=> array(10, 2, 0.98),
			30	=> array(30, 5, 0.98),
			50	=> array(50, 7, 0.98),
			100	=> array(100, 10, 0.98),
			200	=> array(200, 15, 0.98),
			500	=> array(500, 30, 0.98),
			1024=> array(1024, 50, 0.98)
		)
		
		
		
		
	),






	
);