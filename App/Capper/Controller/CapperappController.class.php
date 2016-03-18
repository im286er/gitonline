<?php
namespace Capper\Controller;
use Think\Controller;

class CapperappController extends Controller {
		
	public $access_token,$appid;
	
	//验证查询
	public function _initialize() {
		header('Content-type: application/json');
		header("Content-type: text/html; charset=utf-8");
		
		$access_token = I('post.access_token');
		if(strtolower(CONTROLLER_NAME) != 'token'){
			$result = D('TrApptoken')->checkToken($access_token);
			if($result['errcode']>0)die(JSON($result));
			$this->access_token = $result['access_token'];
			$this->appid = $result['appid'];
			$this->tokenid = $result['id'];
		}
	}

}