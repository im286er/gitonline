<?php
namespace Mobile\Model;
use Think\Model;

class UsertokenModel extends Model {
	public $expirestime = 63072000;
	protected $_validate = array(

    );
	
	//首次签署utoken
	public function signUtoken($user){
		$data = array();
		$data['userid'] = $user['u_id'];
		$data['utoken'] = $this->create_utoken($user['u_id'],$user['u_source']);
		$data['source'] = $user['u_source'];
		$data['addtime'] = time();
		$data['expirestime'] = time()+$this->expirestime;
		$this->add($data);
		return array('utoken'=>$data['utoken'],'expirestime'=>$this->expirestime);
	}

	//检测Token是否正确
	public function checkUtoken($utoken){
		if(!$utoken)return array('errcode'=>80010,'errmsg'=>'utoken未提交');
		$usertoken = $this->where(array('utoken'=>$utoken))->find();
		if(!$usertoken)return array('errcode'=>80011,'errmsg'=>'utoken已经失效，请重新登录获取！');
		return $usertoken['userid'];
	}

	//根据token，找出用户信息
	public function appToView($utoken){
		if(!$utoken)return false;
		$usertoken = $this->where(array('utoken'=>$utoken))->find();
		if(!$usertoken)return false;
		return $usertoken;
	}

	//根据token，退出登录
	public function logoutUtoken($utoken){
		if(!$utoken)return false;
		$usertoken = $this->where(array('utoken'=>$utoken))->delete();
		if(!$usertoken)return false;
		return $usertoken;
	}

	//生成token
	protected function create_utoken($userid,$source){
		$utoken = md5($userid.$source.randpw(16,'NUMBER').microtime());
		return $utoken;
	}
	
}

?>