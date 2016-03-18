<?php
namespace Capper\Model;
use Think\Model;

class TrReceivingModel extends Model {
	public $expiration = 7200;

	protected $_validate = array(

    );
	public function setDefault($userid=null,$receivingid=null){
		if(!$userid && !$receivingid)return false;
		$this->where(array('r_userid'=>$userid))->setField('r_default','0');
		$this->where(array('r_userid'=>$userid,'r_default'=>$receivingid))->setField('r_default','0');
		return true;
	}

	//获取用户收货地址列表
	public function getAddressList($userid){
		$result = $this->where(array('userid'=>$userid))->order('receivingid desc')->select();
		return $result;
	}
	
	//添加收货地址
	public function Address_add($opt){
		$result = $this->add($opt);
		return $result;
	}
	
	//获取收货地址详情信息
	public function getAddressInfo($receivingid){
		$result = $this->where(array('receivingid'=>$receivingid))->find();
		return $result;
	}
	
	//修改收货地址信息
	public function Address_edit($uid,$receivingid,$opt){
		$result = $this->where(array('receivingid'=>$receivingid,'userid'=>$uid))->save($opt);
		return $result;
	}
	
	//删除收货地址
	public function Address_del($uid,$receivingid){
		$result = $this->where(array('receivingid'=>$receivingid,'userid'=>$uid))->delete();
		return $result;
	}
}

?>