<?php
namespace Capper\Model;
use Think\Model;
class TrUoauthModel extends Model {

	public function source (){
		$source = array(
			'qq' => 'QQ',
			'weixin' => '微信',
			'weibo' => '新浪微博',
			'alipay' => '支付宝',
		);
		return $source;
	}
	
	/***第三方首次登录填写的资料录入***/
	public function oauthlogin($data=array()){
		$oauth = $this->check_oauth($data['source'],$data['openid']);
		if($oauth){  //如果此授权已经存在
			$this->where(array('userid'=>$oauth['userid']))->setField(array('avatar'=>$data['avatar'],'nickname'=>$data['nickname'],'lasttime'=>$data['lasttime']));
			return $oauth;
		}else{     //如果此授权不存在
			$array = array();
			$array['userid'] = $data['userid'];
			$array['source'] = $data['source'];
			$array['openid'] = $data['openid'];
			$array['nickname'] = $data['nickname'];
			$array['avatar'] = $data['avatar'];
			$array['lasttime'] = date('Y-m-d H:i:s');
			$array['regtime'] = date('Y-m-d H:i:s');
			$array['appid'] = $data['appid'];
			$this->add($array);
			return $array;
		}
	}

	/*****检测是否已经做过第三方登录****/
	public function check_oauth($source,$openid){
		$oauth = $this->where(array('source'=>$source,'userid'=>array('gt',0),'openid'=>$openid))->find();
		return $oauth?$oauth:false;
	}
	
}

?>