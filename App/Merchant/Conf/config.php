<?php
return array(
	'DEFAULT_THEME'			=> 'Default',
	'TMPL_FILE_DEPR'		=> '_' ,
	'USER_AUTH_ON'			=> false,
	
	'USER_COOKIE_JID'		=> 'ZLKDFIWLS',
	'USER_COOKIE_SID'		=> 'SIISLKNSO',
	'USER_COOKIE_TPE'		=> 'TISLNSOWK',
	
	
	//上传文件的时候配置
	'UPLOAD_F'				=> array(
			'exts'		=> 'mp4',
			'maxSize'	=> '31457280',			
	),
	
	
	
	'UPLOAD_APS'			=> array(
			'exts'		=> 'jpg,png',
			'maxSize'	=> '204800',			
	),

	//商家评论
	'COMMENT_M'			=> array(438),

	//特权会员
	'MEMBER_B'			=> array(438),


	//洗衣信息模块
	'MESSAGE_B' => array('type'=>0,'code'=>'message','name'=>'信息管理','url'=>'/Message/pushmsg.html?menucode=message','next'=>array(
				    array('type'=>0,'id'=>'Messagepushmsg','name'=>'群发消息','url'=>'/Message/pushmsg.html'),
				    array('type'=>0,'id'=>'Messagecommentmsg','name'=>'留言管理','url'=>'/Message/commentmsg.html'),
				    array('type'=>0,'id'=>'Messageparammsg','name'=>'特权价格','url'=>'/Message/parammsg.html'),
		)),
	
	'TOP_MENU' => array(
		'setting' =>    array('type'=>0,'code'=>'setting','name'=>'店铺设置','url'=>'/Index/index.html?menucode=setting','next'=>array(
					array('type'=>0,'id'=>'Indexindex','name'=>'店铺详情','url'=>'/Index/index.html'),
					array('type'=>1,'id'=>'Indexeditpwd','name'=>'账户设置','url'=>'/Index/editpwd.html'),
				    array('type'=>1,'id'=>'Shopindex','name'=>'添加分店','url'=>'/Shop/index.html'),
					array('type'=>1,'id'=>'Managetemplate','name'=>'店铺装修','url'=>'/Manage/template.html'),
					array('type'=>0,'id'=>'Settingapp','name'=>'模块选择','url'=>'/Setting/app.html'),
					array('type'=>0,'id'=>'Manageadvert','name'=>'首页顶栏广告','url'=>'/Manage/advert.html'),
					array('type'=>0,'id'=>'Salesgoods','name'=>'首页中间模块','url'=>'/Sales/goods/ctype/1.html'),
					array('type'=>0,'id'=>'Messagehdlist','name'=>'首页下方模块','url'=>'/Message/hdlist.html'),
					array('type'=>0,'id'=>'ManagesetFigure','name'=>'APP设置','url'=>'/Manage/setFigure.html'),
					array('type'=>1,'id'=>'Functionautoreply','name'=>'消息设置','url'=>'/Function/autoreply/type/1.html'),
					array('type'=>0,'id'=>'PrintprintList','name'=>'打印设置','url'=>'/Print/printList.html'),
		)),
		'myorder' =>	array('type'=>0,'code'=>'myorder','name'=>'订单管理','url'=>'/Sales/myorder.html?menucode=myorder','next'=>array(
					array('type'=>0,'id'=>'Salesmyorder','name'=>'消费订单','url'=>'/Sales/myorder.html'),
					array('type'=>0,'id'=>'Salesmyreserve','name'=>'预约订单','url'=>'/Sales/myreserve.html'),
					array('type'=>0,'id'=>'SalesrecedeOrderGoods','name'=>'退货管理','url'=>'/Sales/recedeOrderGoods.html'),
		)),
		'message'=>	array('type'=>0,'code'=>'message','name'=>'信息管理','url'=>'/Message/pushmsg.html?menucode=message','next'=>array(
				    array('type'=>0,'id'=>'Messagepushmsg','name'=>'群发消息','url'=>'/Message/pushmsg.html'),
				    // array('type'=>0,'id'=>'Messagecommentmsg','name'=>'留言管理','url'=>'/Message/commentmsg.html'),
				    // array('type'=>0,'id'=>'Messageparammsg','name'=>'特权价格','url'=>'/Message/parammsg.html'),
		)),
		'finance' =>	array('type'=>1,'code'=>'finance','name'=>'财务管理','url'=>'/Manage/finance/type/0.html?menucode=finance','next'=>array(
				    array('type'=>0,'id'=>'Managefinance','name'=>'财务明细','url'=>'/Manage/finance/type/0.html'),
				    array('type'=>0,'id'=>'Pluginsetalipay','name'=>'支付账户设置','url'=>'/Plugin/setalipay.html'),
		)),
		'member' =>	array('type'=>0,'code'=>'member','name'=>'会员管理','url'=>'/User/rebate.html?menucode=member','next'=>array(
					array('type'=>0,'id'=>'Userrebate','name'=>'会员信息','url'=>'/User/rebate.html'),
					array('type'=>0,'id'=>'Useropinion','name'=>'会员反馈','url'=>'/User/opinion.html'),
		)),
		'wifi'=>	array('type'=>0,'code'=>'wifi','name'=>'硬件WIFI','url'=>'/Manage/device.html?menucode=wifi','next'=>array(
					array('type'=>0,'id'=>'Managedevice','name'=>'设备信息','url'=>'/Manage/device.html'),
					array('type'=>0,'id'=>'Pluginshareset','name'=>'分享设置','url'=>'/Plugin/shareset.html'),
		)),
		'special' =>	array('type'=>0,'code'=>'special','name'=>'专题推广','url'=>'/Special/splist?menucode=special','next'=>array(
					array('type'=>0,'id'=>'Specialsplist','name'=>'系统专题','url'=>'/Special/splist'),
					array('type'=>0,'id'=>'Specialsplist2','name'=>'我的专题','url'=>'/Special/splist/my'),
					array('type'=>0,'id'=>'Specialsplist3','name'=>'推广统计','url'=>'/Special/splist/myread'),
		)),
	),
);