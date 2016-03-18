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

	//��ȡ�û��ջ���ַ�б�
	public function getAddressList($userid){
		$result = $this->where(array('userid'=>$userid))->order('receivingid desc')->select();
		return $result;
	}
	
	//����ջ���ַ
	public function Address_add($opt){
		$result = $this->add($opt);
		return $result;
	}
	
	//��ȡ�ջ���ַ������Ϣ
	public function getAddressInfo($receivingid){
		$result = $this->where(array('receivingid'=>$receivingid))->find();
		return $result;
	}
	
	//�޸��ջ���ַ��Ϣ
	public function Address_edit($uid,$receivingid,$opt){
		$result = $this->where(array('receivingid'=>$receivingid,'userid'=>$uid))->save($opt);
		return $result;
	}
	
	//ɾ���ջ���ַ
	public function Address_del($uid,$receivingid){
		$result = $this->where(array('receivingid'=>$receivingid,'userid'=>$uid))->delete();
		return $result;
	}
}

?>