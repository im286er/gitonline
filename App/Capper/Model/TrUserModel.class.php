<?php
namespace Capper\Model;
use Think\Model;

class TrUserModel extends Model {
	public $expiration = 7200;

	protected $_validate = array(

    );
	
	/**检测手机号码**/
	public function check_mobile($mobile){
		if(!$mobile)return false;
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[012356789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $mobile) ) {
			return false;
		}
		return true;
	}

	/***使用手机号码登录***/
	public function login($mobile=null){
		if($this->check_mobile($mobile)==false)return array('errcode'=>81011, 'errmsg'=>'手机号格式不正确');
		$user = $this->where(array('u_phone'=>$mobile))->find();
		if(!$user)return $this->register($mobile);
		if($user && $user['u_status'] <= 0)return array('errcode'=>80001,'errmsg'=>'您的帐号已经被禁用。');
		$this->where(array('u_userid'=>$user['u_userid']))->setField(array('u_lastip'=>get_client_ip(),'u_lasttime'=>date('Y-m-d H:i:s')));
		return $user['u_userid'];
	}

	/***根据手机注册用户***/
	public function register($mobile){
		$data = array();
		$data['u_phone'] = $mobile;
		$data['u_regtime'] = date('Y-m-d H:i:s');
		$data['u_lastip'] = get_client_ip();
		$userid = $this->add($data);
		return $userid;
	}

	/***获取用户数据***/
	public function userinfo($userid=null){
		$user = $this->where(array('u_userid'=>$userid))->find();
		return $user;
	}

	/***第三方快速登录***/
	public function oauthlogin($mobile,$openid,$source,$avatar=null,$nickname=null,$appid=1){
		$userid = $this->login($mobile);
		$data=array();
		$data['userid'] = $userid;
		$data['openid'] = $openid;
		$data['source'] = $source;
		$data['avatar'] = $avatar;
		$data['nickname'] = $nickname;
		$data['appid'] = $appid;
		D('TrUoauth')->oauthlogin($data);
		return $userid;
	}

    /****退出登录****/
	public function login_out($appid,$utoken){
		$result = D('TrUsertoken')->where(array('appid'=>$appid,'utoken'=>$utoken))->setField('utoken','');
		return $result;
	}

}

?>