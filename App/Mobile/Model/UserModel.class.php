<?php
namespace Mobile\Model;
use Think\Model;

class UserModel extends Model {
	public $expiration = 7200;

	protected $_validate = array(

    );
	


	/***获取用户数据***/
	public function userinfo($userid=null){
		$user = $this->where(array('u_id'=>$userid))->find();
		return $user;
	}

	/***第三方快速登录***/
	public function sdklogin($jid,$openid,$source,$avatar=null,$nickname=null,$clientid=null){
		$user = $this->where(array('u_jid'=>$jid,'u_source'=>$source,'u_openid'=>$openid))->find();
		$data = array();
		if($nickname)$data['u_name'] = $nickname;
		if($avatar)$data['u_avatar'] = $avatar;
		if($clientid)$data['u_clientid'] = $clientid;
		if($user){
			$data['u_lasttime'] = date('Y-m-d H:i:s');
			$this->where(array('u_id'=>$user['u_id']))->setField($data);
			$userid = $user['u_id'];
		}else{
			$data['u_openid'] = $openid;
			$data['u_source'] = $source;
			$data['u_jid'] = $jid;
			$data['u_regtime'] = date('Y-m-d H:i:s');
			$data['u_id'] = $this->add($data);
			$user = $data;
		}
		$result = D('Usertoken')->signUtoken($user);
		return $result;
	}
    /****退出登录****/
	public function loginOut($utoken){
		$result = D('Usertoken')->where(array('utoken'=>$utoken))->delete();
		return $result;
	}



}

?>