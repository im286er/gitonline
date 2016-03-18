<?php
function randpw($len=8,$format='ALL'){
	$is_abc = $is_numer = 0;
	$password = $tmp ='';  
	switch($format){
		case 'ALL':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		break;
		case 'CHAR':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		break;
		case 'NUMBER':
			$chars='0123456789';
		break;
		default :
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			break;
	}
	mt_srand((double)microtime()*1000000*getmypid());
	while(strlen($password)<$len){
		$tmp =substr($chars,(mt_rand()%strlen($chars)),1);
		if(($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR'){
			$is_numer = 1;
		}
		if(($is_abc <> 1 && preg_match('/[a-zA-Z]/',$tmp)) || $format == 'NUMBER'){
			$is_abc = 1;
		}
		$password.= $tmp;
	}
	if($is_numer <> 1 || $is_abc <> 1 || empty($password) ){
		$password = randpw($len,$format);
	}
	return $password;
}

function getPhoneAddress( $phone='' ) {
	if( preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[01256789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $phone) ) {
		$file_content = file_get_contents( "https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=".$phone );
		
		$file_content = iconv('gb2312', 'UTF-8', $file_content);
		preg_match("/carrier\:\'([^']*)\'/is", $file_content, $match);
		
		if( !isset($match[1]) || empty($match[1]) ) {
			return JSON( array('error'=>'获取失败', 'errno'=>'10002') );	
		}
		return JSON( array('errno'=>0, 'data'=>$match[1]) );
	}
	return JSON( array('error'=>'手机号输入不正确', 'errno'=>'10001') );
}


function format_bytes($size, $i=0, $delimiter='') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for (; $size >= 1024 && $i < 6; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}
//精确的位数
function format_money($money=0.00,$precise=2){
	if($money>=1000)$precise = 1;
	if($money>=10000)$precise = 0;
	return round($money, $precise);
}


//生成订单号
function orderNumber(){
	
	$order_number = date("ymdHis").mt_rand(1000,9999);
	return $order_number;
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