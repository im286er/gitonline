<?php
namespace Rebateapp\Model;
use Think\Model;

class FlInviteModel extends Model {

	public $coefficient = '12333';

	/***记录唯一标识，如MAC地址，或者邀请码***/
	public function recordMarking($marking=null){
		if(!$marking)return false;
		 $ip = get_client_ip();
		 $is_invite = $this->where(array('marking'=>array('eq',$marking)))->find();
		 if($is_invite)return false;
		 $invite = $this->where(array('ip'=>$ip,'marking'=>array('eq',''),'status'=>'0','type'=>array('in','0,1')))->order('addtime desc')->find();
		 if($invite){
			$data = array();
			$data['marking'] =  $marking;
			$result = $this->where(array('id'=>$invite['id']))->setField($data);
			return $result;
		 }
		 return false;
	}
	/***注册的时候根据回传的mac地址，找出邀请人***/
	/**$userid 为注册人id,$marking 为注册人唯一标识**/
	public function confirmRelation($userid,$marking=null){
		if(!$userid || !$marking)return false;
		$invite = $this->where(array('marking'=>array('eq',$marking),'status'=>'0'))->find();
		
		if($invite){ //如果找到唯一编码
			$data = array();
			$data['inviteeuid'] =  $userid;
			$data['status'] =  '1';
			$this->where(array('id'=>$invite['id']))->setField($data);//让状态变成邀请成功，但未支付升级为vip
			$result = D('FlUser')->where(array('flu_userid'=>$userid))->setField('flu_puserid',$invite['inviter']);//关联邀请会员
			if($invite['jid'])$this->confirmBelong($userid,$invite['jid']);
			return $result;
		}
		return false;
	}

	/***找出所属代理商***/
	/**$userid 为注册人id,$jid 为商家Id**/
	public function confirmBelong($userid,$jid){
		if(!$userid && !$jid)return false;
		$magent = M('merchant')->where(array('jid'=>$jid))->getField('magent');
		$data = array();
		$data['flu_sjid'] = $jid;
		if($magent)$data['flu_sagentid'] = $magent;
		$result = D('FlUser')->where(array('flu_userid'=>$userid))->setField($data);
		return $result;
	}

	/*****确认邀请码邀请****/
	public function confirmCodeInvite($userid,$invitecode=null){
		if(!$userid || !$invitecode)return false;
		$inviter = $this->inviteCode($invitecode,'inviter');
		$inviteuser = D('FlUser')->find($inviter);
		if(!$inviteuser)return false;
		$data = array();
		$data['inviter'] =  $inviter;
		$data['ip'] = get_client_ip();
		$data['marking'] =  $invitecode;
		$data['addtime'] = time();
		$data['type'] = 2;//邀请类型
		$data['status'] = 1;
		$data['inviteeuid'] = $userid;
		$this->data($data)->add();
		$result = D('FlUser')->where(array('flu_userid'=>$userid))->setField('flu_puserid',$inviter);//关联邀请会员
		return true;
	}

	//type为create 创建邀请吗，否则就是获取用户uid
	public function inviteCode($code=false,$type='create'){
		if(!$code)return false;
		return $type=='create'?$code+$this->coefficient:$code-$this->coefficient;
	}


	/******输入手机号邀请******/
	public function confirmMobileInvite($userid,$mobile=null){
		if(!$userid || !$mobile)return false;
		$invite = $this->where(array('marking'=>$mobile,'status'=>'0','inviter'=>array('gt',0)))->order('addtime desc')->find();
		if(!$invite)return false;
		$data = array();
		$data['type'] = 2;//邀请类型
		$data['status'] = 1;
		$data['inviteeuid'] = $userid;
		$this->where(array('id'=>$invite['id']))->setField($data);//让状态变成邀请成功
		$result = D('FlUser')->where(array('flu_userid'=>$userid))->setField('flu_puserid',$invite['inviter']);//关联邀请会员
		if($invite['jid'])$this->confirmBelong($userid,$invite['jid']);
		return true;
	}

}
?>