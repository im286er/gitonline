<?php
namespace Rebateapp\Model;
use Think\Model;

/*收货地址*/
class ReceivingModel extends Model {
	
	//获取用户收货地址列表
	public function getAddressList($userid){
		$result = M('fl_receiving')->where(array('flr_userid'=>$userid))->order('flr_receivingid desc')->select();
		return $result;
	}
	
	//添加收货地址
	public function Address_add($opt){
		$result = M('fl_receiving')->add($opt);
		return $result;
	}
	
	//获取收货地址详情信息
	public function getAddressInfo($receivingid){
		$result = M('fl_receiving')->where(array('flr_receivingid'=>$receivingid))->find();
		return $result;
	}
	
	//修改收货地址信息
	public function Address_edit($uid,$receivingid,$opt){
		$result = M('fl_receiving')->where(array('flr_receivingid'=>$receivingid,'flr_userid'=>$uid))->save($opt);
		return $result;
	}
	
	//删除收货地址
	public function Address_del($uid,$receivingid){
		$result = M('fl_receiving')->where(array('flr_receivingid'=>$receivingid,'flr_userid'=>$uid))->delete();
		return $result;
	}
}

?>