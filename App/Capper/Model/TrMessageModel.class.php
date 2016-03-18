<?php
namespace Capper\Model;
use Think\Model;

class TrMessageModel extends Model {
	public function types($type=null){
		$types = array(
			0 => array('name'=>'系统通知','ico'=>U('@www').'/Public/Upload/2015-04-12/5529dbe76bb06.png'),
			1 => array('name'=>'返现通知','ico'=>U('@www').'/Public/Upload/2015-04-12/5529dbe76bb06.png'),
			2 => array('name'=>'邀请通知','ico'=>U('@www').'/Public/Upload/2015-04-12/5529dbe76bb06.png'),
			3 => array('name'=>'活动通知','','ico'=>U('@www').'/Public/Upload/2015-04-12/5529dbe76bb06.png'),
		);
		return $type!=''?$types[$type]:$types;
	}
	
}
?>