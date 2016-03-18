<?php
function getList($path='') {
	if( !file_exists($path) ) return false;	
	$dir_resource = opendir($path) or die('');
	
	$file_list = array();
	while($file=readdir($dir_resource)) {
		if($file=='.' || $file=='..') continue;
		
		$file_list[] = $file;	
	}
	fclose($dir_resource);
	
	return $file_list;	
}


//获取主行业图标
function get_main_vocation($v_list,$vid){
	foreach($v_list as $vv){
		if($vid == $vv['v_id']){
			if($vv['v_pid'] == 0){
				return  $vv['v_id'];
			}else{
				return get_main_vocation($v_list,$vv['v_pid']);
			}
		}
	}
}