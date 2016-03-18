<?php
namespace Rebateapp\Controller;
use Think\Controller;

class AddressController extends RebateappController {
	public $userid;
	/***获取用户基本信息**/
	public function usercommon(){
		$result = D('FlUsertoken')->checkUtoken(I('utoken'));
		if(!is_array($result)){
			$this->userid = $result;
		}else{
			$result = array(
					"errcode" => '81300',
					"errmsg"  => '没有查到对应用户',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
	
	//添加收货地址
	public function addressAdd(){
		$this->usercommon();
	
		$name     = I('name');
		$phone    = I('phone');
		$address  = I('address');
		$maddress = I('maddress');
		$zipcode  = I('zipcode');
		$defaults  = I('defaults',0);
	
		if(empty($name) || empty($phone) || empty($address) || empty($maddress)){
			$result = array(
					"errcode" => '81301',
					"errmsg"  => '信息不完整',
					"events"  => array(),
			);
			die(JSON($result));
		}
		if($defaults == 1){
			M('fl_receiving')->where(array('flr_userid'=>$this->userid))->save(array('flr_default'=>0));
		}
		$opt = array(
				'flr_userid' => $this->userid,
				'flr_name'   => $name,
				'flr_phone'  => $phone,
				'flr_address' => $address,
				'flr_maddress' => $maddress,
				'flr_zipcode'  =>$zipcode,
				'flr_default'  => $defaults
		);
		$r = D('Receiving')->Address_add($opt);
		if($r){
			$result = array(
					"errcode" => 'ok',
					"errmsg"  => '操作成功',
					"events"  => array(),
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81300',
					"errmsg"  => '操作失败',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
	//编辑收货地址
	public function addressEdit(){
		$this->usercommon();
	
		$receivingid     = I('receivingid');
		$name     = I('name');
		$phone    = I('phone');
		$address  = I('address');
		$maddress = I('maddress');
		$zipcode  = I('zipcode');
		$defaults  = I('defaults',0);
	
		if(empty($receivingid) || empty($name) || empty($phone) || empty($address) || empty($maddress)){
			$result = array(
					"errcode" => '81301',
					"errmsg"  => '信息不完整',
					"events"  => array(),
			);
			die(JSON($result));
		}
		if($defaults == 1){
			M('fl_receiving')->where(array('flr_userid'=>$this->userid))->save(array('flr_default'=>0));
		}
	
		$opt = array(
				'flr_name'   => $name,
				'flr_phone'  => $phone,
				'flr_address' => $address,
				'flr_maddress' => $maddress,
				'flr_zipcode'  =>$zipcode,
				'flr_default'  => $defaults
		);
		$r = D('Receiving')->Address_edit($this->userid,$receivingid,$opt);
		$r = true;
		if($r){
			$result = array(
					"errcode" => 'ok',
					"errmsg"  => '操作成功',
					"events"  => array(),
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81300',
					"errmsg"  => '操作失败',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
	//收货地址列表
	public function addressList(){
		$this->usercommon();
		$r = D('Receiving')->getAddressList($this->userid);
		if($r){
			$result = array(
					"errcode" => 'ok',
					"errmsg"  => '操作成功',
					"events"  => $r
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81302',
					"errmsg"  => '没有查询到数据',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
	//收货地址详细信息
	public function addressInfo(){
		$this->usercommon();
	
		$receivingid     = I('receivingid');
		$r = D('Receiving')->getAddressInfo($receivingid);
		if($r){
			$result = array(
					"errcode" => 'ok',
					"errmsg"  => '操作成功',
					"events"  => $r
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81302',
					"errmsg"  => '没有查询到数据',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
	//删除收货地址
	public function addressDel(){
		$this->usercommon();
	
		$receivingid     = I('receivingid');
		$r = D('Receiving')->Address_del($this->userid,$receivingid);
		if($r){
			$result = array(
					"errcode" => 'ok',
					"errmsg"  => '操作成功',
					"events"  => array(),
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81300',
					"errmsg"  => '操作失败',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
}