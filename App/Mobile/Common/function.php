<?php
function orderNumber(){
	/*
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$order_number = '';
	for($i=0;$i<8;$i++){
		$order_number .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}
	$order_number .= date("YmdHi");
	for($i=0;$i<4;$i++){
		$order_number .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}*/
	$order_number = date("ymdHis").mt_rand(1000,9999);
	return $order_number;
}

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

//距离转化
function distance_convert($distance=null){
	if(!$distance)return false; 
	if($distance>1){
		return $distance.'km';
	}elseif($distance<=0.1){
		return '<100m';
	}else{
		return ($distance*1000).'m';
	}
}

function userAgent($ua){
## This credit must stay intact (Unless you have a deal with @lukasmig or frimerlukas@gmail.com
## Made by Lukas Frimer Tholander from Made In Osted Webdesign.
## Price will be $2
 
    $iphone = strstr(strtolower($ua), 'mobile'); //Search for 'mobile' in user-agent (iPhone have that)
    $android = strstr(strtolower($ua), 'android'); //Search for 'android' in user-agent
    $windowsPhone = strstr(strtolower($ua), 'phone'); //Search for 'phone' in user-agent (Windows Phone uses that)
      
      
    function androidTablet($ua){ //Find out if it is a tablet
        if(strstr(strtolower($ua), 'android') ){//Search for android in user-agent
            if(!strstr(strtolower($ua), 'mobile')){ //If there is no ''mobile' in user-agent (Android have that on their phones, but not tablets)
                return true;
            }
        }
    }
    $androidTablet = androidTablet($ua); //Do androidTablet function
    $ipad = strstr(strtolower($ua), 'ipad'); //Search for iPad in user-agent
      
    if($androidTablet || $ipad){ //If it's a tablet (iPad / Android)
        return 'tablet';
    }
    elseif($iphone && !$ipad || $android && !$androidTablet || $windowsPhone){ //If it's a phone and NOT a tablet
        return 'mobile';
    }
    else{ //If it's not a mobile device
        return 'desktop';
    }    
} 

function get_brand($ua){
	preg_match('/\([^\(\)]*?\)/', $ua, $ua_main);
	$ua_arr = explode(';', $ua_main[0]);
	$ua_str = $ua_arr[count($ua_arr)-1];
	$brand  = substr($ua_str, 0, strlen($ua_str)-1);
	return $brand;
}

//通过用户认证
function setPortal($rid, $rip, $mac) {
	$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
	$ret = $client->connect("120.26.89.187", "10019", 0.3, 0);
	
	$string  = "sz,".$rid.",".$rip.",".$mac.",1,600";
	return $client->send( $string ) ? true : false;
}

function format_money($money=0.00,$precise=2){
	if($money>=100)return rtrim($money,'0');
	if($money>=1000)$precise = 1;
	if($money>=10000)$precise = 0;
	return round($money, $precise);
}


?>