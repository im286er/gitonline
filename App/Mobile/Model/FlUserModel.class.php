<?php
namespace Mobile\Model;
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

	public function is_mobile($mobile){
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[012356789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $mobile) ) {
			die( JSON( array('errcode'=>81011, 'errmsg'=>'手机号格式不正确') ) );
		}
		return true;
	}



	/***Md5密码加密***/
	public function register($username,$password,$jid,$ismobile=1){
		$user = $this->where(array('flu_username'=>$username))->find();
		if($user)return array('errcode'=>80103,'errmsg'=>'该用户名已经被注册！');
		!$ismobile or $this->is_mobile($username);
		$data = array();
		$data['flu_username'] = $username;
		$data['flu_password'] = $this->passwordmd5($password);
		$data['flu_usertype'] = '0';
		$data['flu_sjid'] = $jid;
		$data['flu_sagentid'] = M('merchant')->where(array('jid'=>$jid))->getField('magent');
		if($ismobile==1)$data['flu_phone'] = $username;
		if($ismobile==1)$data['flu_nickname'] = $username;
		$data['flu_regtime'] = date('Y-m-d H:i:s');
		$data['flu_lastip'] = get_client_ip();
		$data['flu_puserid'] = session('smid')>0 ? session('smid') : 0;
		$userid = $this->add($data);
		return $userid;
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

	
    /****退出登录****/
	public function login_out($appid,$utoken){
		$result = D('FlUsertoken')->where(array('appid'=>$appid,'utoken'=>$utoken))->setField('utoken','');
		return $result;
	}



}

?>