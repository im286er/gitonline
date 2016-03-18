<?php
return array(
	// 服务器上 数据库常用配置
	'DB_TYPE'			=>	'mysql',			    // 数据库类型
	'DB_HOST'			=>	'localhost',			// 数据库服务器地址
	'DB_NAME'			=>	'azd_demo',					// 数据库名
	'DB_USER'			=>	'root',					// 数据库用户名
	'DB_PWD'			=>	'azding123',						// 数据库密码 	
	'DB_PORT'			=>	3306,				    // 数据库端口
	'DB_PREFIX'			=>	'azd_',		            // 数据库表前缀
	'DB_CHARSET'		=>	'utf8',				    // 数据库编码
	'DB_FIELDS_CACHE'	=>	true,					// 启用字段缓存	
	'URL_MODEL'			=>  2,

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
);