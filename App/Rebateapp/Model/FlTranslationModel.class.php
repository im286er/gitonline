<?php
namespace Rebateapp\Model;
use Think\Model;

class FlTranslationModel extends Model {

	public function types($type=null){
		$types = array(
			0 => '订单返现',
			1 => '订单返现',
			2 => '邀请通知',
			3 => '其他返现',
			4 => '其他返现',
		);
		return $type!=''?$types[$type]:$types;
	}
}

?>