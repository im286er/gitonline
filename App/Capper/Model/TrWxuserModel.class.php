<?php
namespace Capper\Model;
use Think\Model;

class TrWxuserModel extends Model {
	
	/***微信普通关注事件**/
	public function subscribe($openid=null){
		if(!$openid)return false;
		$wxuser = $this->where(array('openid'=>$openid))->find();
		if(!$wxuser){
			$data = array();
			$data['openid'] = $openid;
			$data['regtime'] = $data['lasttime'] = time();
			$data['subscribe'] = 1;
			$this->add($data);
		}
		if($wxuser['subscribe']<1)
		$this->where(array('openid'=>$openid))->setField(array('lasttime'=>time(),'subscribe'=>'1'));
		return true;
	}
	/***获取用户信息***/
	public function getInfo($uinfo){
		if(count($uinfo) && $uinfo['openid']){
			$data = array();
			$data['ename'] = $uinfo['nickname'];
			$data['sex'] = $uinfo['sex'];
			$data['country'] = $uinfo['country'];
			$data['province'] = $uinfo['province'];
			$data['city'] = $uinfo['city'];
			$data['headimgurl'] = $uinfo['headimgurl'];
			$data['wxgroupid'] = $uinfo['groupid'];
			$data['subscribe'] = $uinfo['subscribe'];
			$this->where(array('openid'=>$uinfo['openid']))->setField($data);
			return true;
		}
		return false;
	}
	/***上报地理位置***/
	public function revGeo($openid,$revGeo){
		if(!$openid)return false;
		$this->where(array('openid'=>$openid))->setField(array('latitude'=>$revGeo['x'],'longitude'=>$revGeo['y'],'accuracy'=>$revGeo['precision']));
	}
	/***取消关注***/
	public function unsubscribe($openid){
		$this->where(array('openid'=>$openid))->setField(array('subscribe'=>'0'));
	}

}

?>