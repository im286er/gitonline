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

	//新模板
	'NEW_THEMES'		=> array(
			'new1',
			'new2',
	),

	//洗衣信息模块
	'MESSAGE_B' => array('type'=>0,'code'=>'message','name'=>'信息管理','url'=>'/Message/pushmsg.html?menucode=message','next'=>array(
				    array('type'=>0,'id'=>'Messagepushmsg','name'=>'群发消息','url'=>'/Message/pushmsg.html'),
				    array('type'=>0,'id'=>'Messagecommentmsg','name'=>'留言管理','url'=>'/Message/commentmsg.html'),
				    array('type'=>0,'id'=>'Messageparammsg','name'=>'特权价格','url'=>'/Message/parammsg.html'),
		)),

	


	//模板功能
	'FUNC_MENU'	=> array(
			array('id'=>'1', 'name'=>'测试功能', 'url'=>'/Sales/goods/ctype/1.html?menucode=goods', 'o_url'=>'/Message/hdlist.html'),
			array('id'=>'2', 'name'=>'活动', 'url'=>'/Design/activity.html', 'o_url'=>'/Message/hdlist.html'),
			array('id'=>'3', 'name'=>'评价', 'url'=>'/Design/comment.html', 'o_url'=>'/Message/hdlist.html'),
			// array('id'=>'4', 'name'=>'资讯', 'url'=>'/Sales/goods/ctype/1.html?menucode=goods', 'o_url'=>'/Message/hdlist.html'),
			// array('id'=>'5', 'name'=>'大转盘', 'url'=>'/Sales/goods/ctype/1.html?menucode=goods', 'o_url'=>'/DaZhuanPan/index.html'),
			array('id'=>'6', 'name'=>'商品', 'url'=>'/Design/goods.html', 'o_url'=>'/Message/hdlist.html'),
			array('id'=>'7', 'name'=>'关于我们', 'url'=>'/Design/shop.html', 'o_url'=>'/Message/hdlist.html'),
		),
	
	'TOP_MENU' => array(
			'shop' =>    array('code'=>'shop','name'=>'店铺管理','url'=>'/Index/index.html?menucode=shop','next'=>array(
					array('id'=>'ShopaddShop','name'=>'添加店铺','url'=>'/Shop/addShop.html'),
					array('id'=>'Shopindex','name'=>'管理店铺','url'=>'/Shop/index.html'),
					array('id'=>'PrintprintList','name'=>'打印设置','url'=>'/Print/printList.html'),
					array('id'=>'Functionautoreply','name'=>'消息设置','url'=>'/Function/autoreply/type/2.html'),
			)),
			'renovation' =>    array('code'=>'renovation','name'=>'店铺装修','url'=>'/Manage/template.html?menucode=renovation','next'=>array(
					array('id'=>'mobileTheme','name'=>'手机版装修','url'=>'/Manage/template.html','next'=>array(
						array('id'=>'ManagesysTheme','name'=>'系统模板','url'=>'/Manage/sysTheme.html'),
						array('id'=>'Managetemplate','name'=>'我的模板','url'=>'/Manage/template.html'),
					)),
					array('id'=>'Specialsplist','name'=>'微传单','url'=>'/Special/splist','next'=>array(
							array('id'=>'Specialsplist','name'=>'系统专题','url'=>'/Special/splist'),
							array('id'=>'Specialsplistmy','name'=>'我的传单','url'=>'/Special/splist/my'),
							array('id'=>'Specialsplistmyread','name'=>'推广统计','url'=>'/Special/splist/myread'),
					)),
					array('id'=>'','name'=>'电脑版装修','url'=>'','next'=>array(
							array('id'=>'','name'=>'系统模板','url'=>''),
							array('id'=>'','name'=>'我的模板','url'=>''),
					)),
					array('id'=>'','name'=>'个人名片设计','url'=>'','next'=>array(
							array('id'=>'','name'=>'名片模板','url'=>''),
							array('id'=>'','name'=>'我的名片','url'=>''),
					)),
			)),
			'goods' =>    array('code'=>'goods','name'=>'商品管理','url'=>'/Sales/goods/ctype/1.html?menucode=goods','next'=>array(
					array('id'=>'SalesclassList','name'=>'分类管理','url'=>'/Sales/classList.html?ctype=1'),
					array('id'=>'SalesaddGoods','name'=>'添加商品','url'=>'/Sales/addGoods.html?ctype=1'),
					array('id'=>'Salesgoods','name'=>'商品管理','url'=>'/Sales/goods.html?ctype=1'),
					// array('id'=>'Salesgoods','name'=>'管理店铺','url'=>'/Sales/goods/ctype/1.html'),
					// array('id'=>'PrintprintList','name'=>'打印设置','url'=>'/Print/printList.html'),
			)),
			'order' =>    array('code'=>'order','name'=>'订单管理','url'=>'/Sales/myorder.html?menucode=order','next'=>array(
					array('id'=>'Salesmyorder2','name'=>'全部订单','url'=>'/Sales/myorder.html','next'=>array(
							array('id'=>'Salesmyorder','name'=>'消费订单','url'=>'/Sales/myorder.html'),
					)),
					array('id'=>'Salesmyreserve2','name'=>'预约管理','url'=>'/Sales/myreserve.html','next'=>array(
							array('id'=>'Salesmyreserve','name'=>'预约订单','url'=>'/Sales/myreserve.html'),
					)),
					
					array('id'=>'SalesrecedeOrderGoods2','name'=>'退货管理','url'=>'/Sales/recedeOrderGoods.html','next'=>array(
							array('id'=>'SalesrecedeOrderGoods','name'=>'退货商品','url'=>'/Sales/recedeOrderGoods.html'),
							array('id'=>'SalesrecedeOrderGoods1','name'=>'退货分析','url'=>'/Sales/recedeOrderGoods/type/tab1.html'),
					)),
			)),
			'finance' =>    array('code'=>'finance','name'=>'财务管理','url'=>'/Manage/finance.html?menucode=finance','next'=>array(
					array('id'=>'Managefinance','name'=>'财务明细','url'=>'/Manage/finance.html'),
					array('id'=>'Manageeditifo','name'=>'个人支付设置','url'=>'/Manage/editifo.html'),
					array('id'=>'Pluginsetalipay','name'=>'企业支付设置','url'=>'/Plugin/setalipay.html'),
					array('id'=>'Managecarryapply','name'=>'提现','url'=>'/Manage/carryapply.html','next'=>array(
							array('id'=>'Managecarryapply2','name'=>'申请提现','url'=>'/Manage/carryapply.html'),
							array('id'=>'Managefinance4','name'=>'提现记录','url'=>'/Manage/finance/type/4.html'),
					)),
			)),
			'member' =>    array('code'=>'member','name'=>'会员管理','url'=>'/User/rebate.html?menucode=member','next'=>array(
					array('id'=>'Userrebate','name'=>'会员信息管理','url'=>'/User/rebate.html'),
					array('id'=>'Useropinion','name'=>'会员留言管理','url'=>'/User/opinion.html'),
					array('id'=>'Messagepushmsg','name'=>'会员短信群发','url'=>'/Message/pushmsg.html','next'=>array(
						array('id'=>'Messagepushmsg2','name'=>'新建短信群发','url'=>'/Message/pushmsg.html'),
						array('id'=>'Messagelistmsg','name'=>'已发送管理','url'=>'/Message/listmsg.html'),
					)),
			)),
			'wifi' =>    array('code'=>'wifi','name'=>'硬件wifi','url'=>'/Manage/device.html?menucode=wifi','next'=>array(
					array('id'=>'Managedevice','name'=>'设备信息','url'=>'/Manage/device.html'),
					array('id'=>'Pluginshareset','name'=>'分享设置','url'=>'/Plugin/shareset.html'),
			)),
			'account' =>    array('code'=>'account','name'=>'账户管理','url'=>'/Account/index.html?menucode=account','next'=>array(
					array('id'=>'Accountindex','name'=>'个人信息','url'=>'/Account/index.html','next'=>array(
						array('id'=>'Accountindex2','name'=>'修改资料','url'=>'/Account/index.html'),
						array('id'=>'Indexeditpwd','name'=>'修改密码','url'=>'/Index/editpwd.html'),
					)),
					array('id'=>'Accountadd','name'=>'添加账户','url'=>'/Account/add.html'),
					array('id'=>'AccountaccountList','name'=>'账号管理','url'=>'/Account/accountList.html'),
			)),
			'marketing' =>    array('code'=>'marketing','name'=>'营销管理','url'=>'/Invest/index.html?menucode=marketing','next'=>array(
					array('id'=>'Investindex','name'=>'消费投资','url'=>'/Invest/index.html'),
					array('id'=>'Investupgrade','name'=>'9.9升级','url'=>'/Invest/upgrade.html'),
			)),
	),
);