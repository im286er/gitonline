<?php
namespace Rebateapp\Model;
use Think\Model;

class FlQuestModel extends Model {


	/****任务的键值不能重复*/
	public function inviteRewards(){   //邀请奖励
		$data = array(
			1 => array('questname'=>'invite1','money'=>'0.9','maxman'=>1,'text'=>'推荐用户注册并成功加入9.9会员，推荐成功1位，额外奖励0.9元现金'),
			2 => array('questname'=>'invite10','money'=>'12','maxman'=>10,'text'=>'推荐用户注册并成功加入9.9会员，推荐成功10位，额外奖励12元现金'),
			3 => array('questname'=>'invite30','money'=>'42','maxman'=>30,'text'=>'推荐用户注册并成功加入9.9会员，推荐成功30位，额外奖励42元现金'),
			4 => array('questname'=>'invite50','money'=>'78','maxman'=>50,'text'=>'推荐用户注册并成功加入9.9会员，推荐成功50位，额外奖励78元现金'),
			5 => array('questname'=>'invite100','money'=>'175','maxman'=>100,'text'=>'推荐用户注册并成功加入9.9会员，推荐成功100位，额外奖励175元现金'),
		);
		return $data;
	}
	/****任务的键值不能重复***/
	public function consumptionRewards(){   //消费奖励
		$data = array(
			6 => array('questname'=>'consumption1','rate'=>'0.1','text'=>'首次消费返现到账后，可额外领取商家返利部分10%的现金奖励'),
			7 => array('questname'=>'consumption2','rate'=>'0.08','text'=>'第二次消费返现到账后，可额外领取8%的现金奖励'),
			8 => array('questname'=>'consumption3','rate'=>'0.06','text'=>'第三次及以后每次消费返现到账后，可额外领取6%的现金奖励'),
		);
		return $data;
	}
	
	/*****获取所有任务****/
	public function configQuest($key){
		$inviteRewards = $this->inviteRewards();
		if($inviteRewards[$key])return $inviteRewards[$key];
		$consumptionRewards = $this->consumptionRewards();
		if($consumptionRewards[$key])return $consumptionRewards[$key];
		return false;
	}
	
	/****处理邀请任务处理*****/
	public function inviteQuest($userid,$questid){
		$configQuest = $this->configQuest($questid);
		$array = array();
		$array['fl_uid'] = $userid;
		$array['questname'] = $configQuest['questname'];
		$array['finishtime'] = time();
		$array['awardmoney'] = $configQuest['money'];
		$result = $this->add($array);
		/***更新账户金额和收入增加日志***/
		$translation = array();
		$translation['flt_uid'] = $userid;
		$translation['flt_oid'] = 'quest'.str_pad($result,'11',"0",STR_PAD_LEFT);
		$translation['flt_balance'] = $configQuest['money'];
		$translation['flt_addtime'] = date('Y-m-d H:i:s');
		$translation['flt_type'] = 5;
		$translation['flt_notes'] = '领取邀请任务奖励';
		$update = D('FlUser')->where(array('flu_userid'=>$userid))->setInc('flu_balance',$configQuest['money']);
		if($update){//账户增加之后处理
			D('FlTranslation')->add($translation);
			$this->where(array('id'=>$result))->setField('status','1');
		}
		return array('msg'=>'领取任务成功，奖励'.$configQuest['money'].'元已发放到账户','status'=>1);
	}


	/***处理购买返利任务的请求***/
	public function consumptionQuest($userid,$questid,$corder=0){
		$configQuest = $this->configQuest($questid);
		if($configQuest['questname']=='consumption3' && $corder > 3){
			$verifyAward = $this->verifyAward($userid);
			if($verifyAward==false)return array('msg'=>'任务未完成','status'=>0);
		}
		if(!$verifyAward['flt_tid']){
			$verifyAward = $this->getOrder($userid,$configQuest['questname']);
		}
		if(!$verifyAward[0]['flo_backprice'])return array('msg'=>'任务未完成','status'=>0);
		$money = round($verifyAward[0]['flo_backprice']*$configQuest['rate'],2);
		$array = array();
		$array['fl_uid'] = $userid;
		$array['questname'] = $configQuest['questname'];
		$array['finishtime'] = time();
		$array['awardmoney'] = $money;
		$array['assist'] = $verifyAward[0]['flt_tid'];
		$result = $this->add($array);
		/***更新账户金额和收入增加日志***/
		$translation = array();
		$translation['flt_uid'] = $userid;
		$translation['flt_oid'] = 'quest'.str_pad($result,'11',"0",STR_PAD_LEFT);
		$translation['flt_balance'] = $money;
		$translation['flt_addtime'] = date('Y-m-d H:i:s');
		$translation['flt_type'] = 5;
		$translation['flt_notes'] = '领取订单返利任务奖励';
		$update = D('FlUser')->where(array('flu_userid'=>$userid))->setInc('flu_balance',$money);
		if($update){//账户增加之后处理
			D('FlTranslation')->add($translation);
			$this->where(array('id'=>$result))->setField('status','1');
		}
		return array('msg'=>'领取任务成功，奖励'.$money.'元已发放到账户','status'=>1);
	}



	/***** 获取订单数据信息 *****/
	public function getOrder($userid,$questname){
		$limit = (ltrim($questname,'consumption')-1).',1';
		$translation = M('FlTranslation')->alias('ft')->join('azd_fl_order fo on ft.flt_oid=fo.flo_id')->where(array('ft.flt_type'=>'0','ft.flt_uid'=>$userid))->order('ft.flt_tid asc')->field('ft.flt_tid,fo.flo_gtype,fo.flo_backprice')->limit($limit)->select();
		return $translation && $translation[0]['flo_gtype']?$translation:false;
	}



	/***** consumption3 验证这个任务订单*****/
	public function verifyAward($userid){
		$questinfo = $this->where(array('questname'=>'consumption3','fl_uid'=>$userid))->order('id desc')->find();
		if(!$questinfo)return true;
		$translation = M('FlTranslation')->alias('ft')->join('azd_fl_order fo on ft.flt_oid=fo.flo_id')->where(array('ft.flt_type'=>'0','ft.flt_uid'=>$userid,'ft.flt_tid'=>array('egt',$questinfo['assist'])))->order('ft.flt_tid desc')->field('ft.flt_tid,fo.flo_gtype,fo.flo_backprice')->select();
		return $translation && $translation[0]['flo_gtype']?$translation:false;
	}

}
?>