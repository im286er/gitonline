<?php
function orderNumber(){
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$order_number = '';
	for($i=0;$i<8;$i++){
		$order_number .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}
	$order_number .= date("YmdHi");
	for($i=0;$i<4;$i++){
		$order_number .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}
	return $order_number;
}


//¾àÀë×ª»¯
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
?>