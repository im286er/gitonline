<?php
namespace Rebateapp\Controller;
use Think\Controller;

class RebateviewController extends Controller {
	public $userid;//当前用户的id
	public $msystem;
	public function _initialize() {

		$user_agent = I('server.HTTP_USER_AGENT');
		//echo I('utoken');
		if(I('post.coordinate'))cookie('coordinate',I('post.coordinate'));
		if(stristr($user_agent,'iPhone') || stristr($user_agent, 'ipa')){
			$this->msystem = 'ios';
			$this->assign('msystem','ios');
		}else{
			$this->msystem = 'android';
			$this->assign('msystem','android');
		}
		if(I('utoken')){
			$uinfo = D('FlUsertoken')->appToView(I('utoken'));
			$this->userid = $uinfo['userid'];
			$this->appid = $uinfo['appid'];
			cookie('userid',$this->userid);
			cookie('appid',$this->appid);
		}elseif(cookie('userid')>0){
			if(cookie('appid')){
				$result = D('FlUsertoken')->checkLoginOut(cookie('appid'),cookie('userid'));
				$result or cookie('userid',null);
				$this->userid = cookie('userid');
			}else{
				$this->userid = cookie('userid');
			}
		}
		//$this->userid = 1;
		$this->assign('userid',$this->userid);
	}

}