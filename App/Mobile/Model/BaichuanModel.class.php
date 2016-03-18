<?php
namespace Mobile\Model;
use Think\Model;

class BaichuanModel extends Model {
	public $appkey = '23219426';
	public $secretKey = 'f0916d1d6d8fe710543cbba6f5d010f3';
	protected $_validate = array(
    );
	public function __construct(){
		vendor("AliBaichuan.TopSdk");
	}
	/*增加用户，$data 可以是一个数组，也可以是一个多维数组*/
	public function usersAdd($data=array()){
		$c = new \TopClient;
		$c->appkey = $this->appkey;
		$c->secretKey = $this->secretKey;
		$req = new \OpenimUsersAddRequest;
		$users = $data['userid']?$data:array_values($data);
		$req->setUserinfos(json_encode($users));
		$resp = $c->execute($req);
		return $this->objectToArray($resp);
	}

	/*获取用户信息，$data 可以是一个字符或者是一个数组*/
	public function usersGet($data=null){
		$c = new \TopClient;
		$c->appkey = $this->appkey;
		$c->secretKey = $this->secretKey;
		$req = new \OpenimUsersGetRequest;
		$users = is_array($data)?implode(",",$data):$data;
		$req->setUserids($users);
		$resp = $c->execute($req);
		return $this->objectToArray($resp);
	}

	/*更新用户信息，$data 可以是一个数组，也可以是一个多维数组*/
	public function usersUpdate($data=array()){
		$c = new \TopClient;
		$c->appkey = $this->appkey;
		$c->secretKey = $this->secretKey;
		$req = new \OpenimUsersUpdateRequest;
		$users = $data['userid']?$data:array_values($data);
		$req->setUserinfos(JSON($users));
		$resp = $c->execute($req);
		return $this->objectToArray($resp);
	
	}
	/*删除用户信息，$data 可以是一个字符或者是一个数组*/
	public function usersDelete($data){
		$c = new \TopClient;
		$c->appkey = $this->appkey;
		$c->secretKey = $this->secretKey;
		$req = new \OpenimUsersDeleteRequest;
		$users = is_array($data)?implode(",",$data):$data;
		$req->setUserids($users);
		$resp = $c->execute($req);
		return $this->objectToArray($resp);
	}
	/*发送自定义消息*/
	public function custmsgPush($data){
		$c = new \TopClient;
		$c->appkey = $this->appkey;
		$c->secretKey = $this->secretKey;
		$req = new \OpenimCustmsgPushRequest;
		$custmsg = array();
		$custmsg['from_user'] = $data['from_user'];
		$to_users = is_array($data['to_users'])?implode('","',$data['to_users']):$data['to_users'];
		$custmsg['to_users'] = '["'.$to_users.'"]'; 
		$custmsg['to_appkey'] = $this->appkey;
		$custmsg['summary '] = $data['summary'];
		$custmsg['apns_param '] = $data['apns_param'];
		if($data['aps'])$custmsg['aps']['alert'] = $data['aps'];
		$custmsg['data '] = $data['data'];
		$req->setCustmsg(JSON($custmsg));
		$resp = $c->execute($req);
		return $this->objectToArray($resp);
	}

	/*聊天对象获取*/
	public function relationsGet($data=array()){
		$c = new \TopClient;
		$c->appkey = $this->appkey;
		$c->secretKey = $this->secretKey;
		$req = new \OpenimRelationsGetRequest;
		$req->setBegDate($data['bendata']);
		$req->setEndDate($data['enddata']);
		$user = array();
		$user['uid'] = $data['uid'];
		$user['app_key'] = $data['uid'];
		$user['taobao_account '] = $this->appkey;
		$req->setUser($user);
		$resp = $c->execute($req);
		return $this->objectToArray($resp);
	}

	/*聊天记录查询*/
	public function chatlogsGet($data=array()){
		$c = new \TopClient;
		$c->appkey = $this->appkey;
		$c->secretKey = $this->secretKey;
		$req = new \OpenimChatlogsGetRequest;
		$req->setUser1($data['user1']);
		$req->setUser2($data['user2']);
		$req->setBegin($data['begin']);
		$req->setEnd($data['end']);
		$req->setCount("100");
		$resp = $c->execute($req);
	}

	/*对象转化为数组*/
	public function objectToArray($e){
		$e=(array)$e;
		foreach($e as $k=>$v){
			if( gettype($v)=='resource' ) return;
			if( gettype($v)=='object' || gettype($v)=='array' )
				$e[$k]=(array)$this->objectToArray($v);
		}
		return $e;
	}


	/*账户密码*/
	public function password($userid){
		return md5($userid.'baichuan'.$this->appkey);
	}
	
	/***userkey验证**/
	public function userkeyVerify($userkey,$userid,$type){
		$key = md5($this->appkey.$userid.$type.'dishuos');
		return $userkey==$key?true:false;
	}

	public function merchant($jid=null){
		if(!$jid)return false;
		$data = $shops = array();
		$merchant = M('merchant')->where(array('jid'=>$jid))->field('mabbreviation,mlptel')->find();
		if(!$merchant)return false;
		$data['name'] = $merchant['mabbreviation'];
		$data['userid'] = 'dishusj'.$jid;
		//$adduser['password'] = $this->password($jid);
		$data['mobile'] = $merchant['mlptel'];
		$shop = M('shop')->where(array('jid'=>$jid,'status'=>'1','is_show'=>1))->field('sid,sname,mservetel,servernum')->select();
		if($shop)foreach($shop as $key => $val){
			$array = array();
			$array['name'] = trim($val['sname']);
			$array['userid'] = 'dishumd'.$val['sid'];
		//  $array['password'] = $this->password($val['sid']);
			$array['mobile'] = $val['mservetel'];
			$shops[] = $array;
			if($val['servernum'] >= 1)for($i=1;$i<=$val['servernum'];$i++){ //构架多客服
				$array['name'] = trim($val['sname']).'客服'.$i;
				$array['userid'] = 'dishumd'.$val['sid'].'-'.$i;
				$shops[] = $array;
			}
		}
		$data['shops'] =$shops;
		return $data;
	}



	/***找出用户的昵称等信息**/
	public function userInfo($userid,$type){
		if(!$userid && !$type)return false;
		$data = array();
		if($type=='dishuuser'){
			$user = M('user')->where(array('u_id'=>$userid))->field('u_phone,u_name,u_ename')->find();
			$data['email'] = null;
			$data['mobile'] = $user['u_phone'];
		}elseif($type=='dishusj'){
			$user = M('merchant')->where(array('jid'=>$userid))->field('mlptel')->find();
			$data['email'] = null;
			$data['mobile'] = $user['mlptel'];
		}elseif($type=='dishumd'){
			$user = M('shop')->where(array('sid'=>$userid))->field('sid,sname,mservetel')->find();
			$data['email'] = null;
			$data['mobile'] = $user['mservetel'];
		}
		return $data;
	}


	/*账户分类*/
	public function userType(){
		return array(
			'dishuuser',//帝鼠用户
			'dishusj',//商家
			'dishumd',//门店
			'dishudl',//代理
			'dishuxt',//系统
			'fanliuser',//返利用户
		);
	}

}
?>