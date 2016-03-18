<?php
namespace Rebateapp\Model;
use Think\Model;

class FlApptokenModel extends Model {
	public $expiration = 518400;
	public $datetime;
	protected $_validate = array(
    );
	
	public function datetime(){
			$this->datetime = time();
	}

	//获取access_token
	public function getToken($appid,$secret){
		$this->datetime();
		if(!$appid || !$secret)return array('errcode'=>40001,'errmsg'=>'appid或secret错误！');
		$apptoken = $this->where(array('appid'=>$appid,'secret'=>$secret,'status'=>'1'))->find();
		if($apptoken['expires_in']>$this->datetime){
			return array('access_token'=>$apptoken['access_token'],'expires_in'=>$apptoken['expires_in']-$this->datetime);
		}elseif($apptoken){
			$data = array(); 
			$data['requestnum']=$apptoken['requestnum']+1;
			$data['expires_in'] = $this->datetime+$this->expiration;//当前时间加上过期时间
			if($apptoken['access_token'])$data['former_token'] = $apptoken['access_token'];//存储就的凭证
			$data['access_token'] = randpw(40,'CHAR');
			$this->where(array('appid'=>$appid,'secret'=>$secret,'status'=>'1'))->setField($data);
			return array('access_token'=>$data['access_token'],'expires_in'=>$this->expiration);
		}else return array('errcode'=>40002,'errmsg'=>'appid或secret未找到！');

	}

	//检测Token是否正确
	public function checkToken($token){
		$this->datetime();
		if(!$token)return array('errcode'=>40004,'errmsg'=>'token未提交');
		$apptoken = $this->where(array('access_token'=>$token,'status'=>'1'))->find();
		if(!$apptoken){
			$apptoken = $this->where(array('former_token'=>$token,'status'=>'1'))->find();
			if($apptoken && ($apptoken['expires_in']-$this->datetime) >= ($this->expiration-86400) ){
				return $apptoken;
			}
			return array('errcode'=>40005,'errmsg'=>'token错误，请检查');
		}
		if($apptoken['expires_in']<=$this->datetime)return array('errcode'=>40006,'errmsg'=>'token错误，已过期，请重新获取！');
		return $apptoken;
	}


}

?>