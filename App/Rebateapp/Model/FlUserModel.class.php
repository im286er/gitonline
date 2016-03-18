<?php
namespace Rebateapp\Model;
use Think\Model;

class FlUserModel extends Model {
	public $expiration = 7200;

	protected $_validate = array(

    );
	
	public function login($username=null,$password=null){
		$user = $this->where("flu_phone=%s and flu_password='%s' and flu_status='%s'",array($username,$this->passwordmd5($password),0))->find();
		if(!$user){
			return array('errcode'=>80001,'errmsg'=>'用户名或密码错误！');
		}else{
			$this->where(array('flu_userid'=>$user['flu_userid']))->setField(array('flu_lastip'=>get_client_ip(),'flu_lasttime'=>date('Y-m-d H:i:s')));
			return $user;
		}
	}

	/***Md5密码加密***/
	public function register($appid,$username,$password,$ismobile=null){
		$user = $this->where(array('flu_username'=>$username))->find();
		if($user)return array('errcode'=>80103,'errmsg'=>'该用户名已经被注册！');
		$data = array();
		$data['flu_username'] = $username;
		$data['flu_password'] = $this->passwordmd5($password);
		$data['flu_usertype'] = 0;
		if($ismobile==1)$data['flu_phone'] = $username;
		if($ismobile==1)$data['flu_nickname'] = $username;
		$data['flu_regtime'] = date('Y-m-d H:i:s');
		$data['flu_lastip'] = get_client_ip();
		$userid = $this->add($data);
		if($ismobile==1){
			D('FlInvite')->confirmMobileInvite($userid,$username);
		}
		/*
		if(is_numeric(I('post.invitecode')) && I('post.invitecode') > 0){
			D('FlInvite')->confirmCodeInvite($userid,I('post.invitecode'));
		}else{
			D('FlInvite')->confirmRelation($userid,I('post.marking'));//根据唯一标识找出上级邀请
		}
		*/
		/**注册成功后授权一次**/
		$ucode = I('post.ucode')?I('post.ucode'):randpw(4,'CHAR');
		$result = D('FlUsertoken')->sign_utoken($appid,$userid,$ucode);
		return $result;
	}

	/***Md5密码加密***/
	public function passwordmd5($password){
		return md5(md5($password));
	}

	/***获取用户数据***/
	public function userinfo($userid=null){
		$user = $this->where(array('flu_userid'=>$userid))->find();
		return $user;
	}

	/***第三方快速登录***/
	public function sdklogin($appid,$openid,$source,$avatar=null,$nickname=null){
		$user = $this->where(array('flu_openid'=>$openid,'flu_source'=>$source))->find();
		if($user){
			$this->where(array('flu_userid'=>$user['flu_userid']))->setField(array('flu_lastip'=>get_client_ip(),'flu_lasttime'=>date('Y-m-d H:i:s')));
			$userid = $user['flu_userid'];
		}else{
			$data = array();
			$data['flu_openid'] = $openid;
			$data['flu_source'] = $source;
			$data['flu_avatar'] = $avatar;
			$data['flu_nickname'] = $nickname;
			$data['flu_usertype'] = 0;
			$data['flu_regtime'] = date('Y-m-d H:i:s');
			$data['flu_lastip'] = get_client_ip();
			$userid = $this->add($data);
			//D('FlInvite')->confirmRelation($userid,I('post.marking'));//根据唯一标识找出上级邀请
		}
		$ucode = randpw(4,'CHAR');
		$result = D('FlUsertoken')->sign_utoken($appid,$userid,$ucode,'outer');
		return $result;
	}
    /****退出登录****/
	public function login_out($appid,$utoken){
		$result = D('FlUsertoken')->where(array('appid'=>$appid,'utoken'=>$utoken))->setField('utoken','');
		return $result;
	}



}

?>