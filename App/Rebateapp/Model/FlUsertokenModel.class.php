<?php
namespace Rebateapp\Model;
use Think\Model;

class FlUsertokenModel extends Model {
	public $expirestime = 604800;
	protected $_validate = array(

    );
	
	//首次签署utoken
	public function sign_utoken($appid,$userid,$ucode,$type='inside'){
		$result = $this->where(array('userid'=>$userid,'appid'=>$appid))->find();
		$data = array();
		if($result){
			$data['utoken'] = $this->create_utoken($appid,$userid,$ucode);
			$data['ucode'] = $ucode;
			$data['unum'] = $result['unum']+1;
			$data['oldutoken'] = $result['utoken'];
			$data['expirestime'] = time()+$this->expirestime;
			$this->where(array('userid'=>$userid,'appid'=>$appid))->setField($data);
		}else{
			$data['userid'] = $userid;
			$data['appid'] = $appid;
			$data['utoken'] = $this->create_utoken($appid,$userid,$ucode);
			$data['ucode'] = $ucode;
			$data['unum'] = $result['unum']+1;
			$data['expirestime'] = time()+$this->expirestime;
			$this->add($data);
		}
		return array('utoken'=>$data['utoken'],'type'=>$type,'expirestime'=>$this->expirestime);

	}

	/**续签协议授权**/
	public function renew_utoken($appid,$utoken,$ucode,$type='inside'){
		$result = $this->where(array('utoken'=>$utoken))->find();
		if($result){
			$data['utoken'] = $this->create_utoken($appid,$result['userid'],$ucode);
			$data['ucode'] = $ucode;
			$data['oldutoken'] = $result['utoken'];
			$data['unum'] = $result['unum']+1;
			$data['expirestime'] = time()+$this->expirestime;
			$this->where(array('utoken'=>$utoken))->setField($data);
			return array('utoken'=>$data['utoken'],'type'=>$type,'expirestime'=>$this->expirestime);
		}else return array('errcode'=>80011,'errmsg'=>'utoken已经失效！');	
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

	//判断用户是否退出登录
	public function checkLoginOut($appid,$userid){
		$usertoken = $this->where(array('appid'=>$appid,'userid'=>$userid))->find();
		if(!$usertoken || !$usertoken['utoken'])return false;
		return true;
	}

	//生成token
	public function  create_utoken($appid,$userid,$ucode){
		$utoken = md5($appid.$userid.randpw(16).$ucode);
		return $utoken;
	}
	
}

?>