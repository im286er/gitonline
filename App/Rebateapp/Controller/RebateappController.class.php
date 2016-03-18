<?php
namespace Rebateapp\Controller;
use Think\Controller;

class RebateappController extends Controller {
		
	public $access_token,$appid;
	
	//验证查询
	public function _initialize() {
		header('Content-type: application/json');
		header("Content-type: text/html; charset=utf-8");
		
		$access_token = I('get.access_token');
		if(strtolower(CONTROLLER_NAME) != 'token'){
			$result = D('FlApptoken')->checkToken($access_token);
			if($result['errcode']>0)die(JSON($result));
			$this->access_token = $result['access_token'];
			$this->appid = $result['appid'];
			$this->tokenid = $result['id'];
		}
	}

}