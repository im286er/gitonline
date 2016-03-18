<?php
namespace Common\Controller;
use Think\Controller;

class ManagerController extends Controller {
    public function _initialize() {
		if( checkrobot() ) { //如果是搜索引擎，则屏蔽掉
			exit(header("HTTP/1.1 403 Forbidden"));
		}
		
		$smid = $_SESSION[C('USER_AUTH_KEY')];
		$cmid = \Common\Org\Cookie::get('mid');
		if(!$smid || !$cmid) $this->redirect("Public/login");
		
		if($smid != $cmid) {
			$this->redirect(U('/Public/logout', '', false, true));
		}
		
		//权限判断
		if( C('USER_AUTH_ON') ) {
			//管理员允许访问任何页面
			if(session('administrator') || strtolower(substr(ACTION_NAME, 0, 6)) == 'public') {
				return true;
			}
			$rule = CONTROLLER_NAME.'/'.ACTION_NAME;
			
			static $Auth = null;
			if ( !$Auth ) { $Auth= new \Think\Auth(); }
		/*	 if(!$Auth->check(CONTROLLER_NAME.'/'.ACTION_NAME, $smid)) {
				if( !in_array(CONTROLLER_NAME, array('Index', 'Notice')) ) $this->error('没有权限访问本页面!'); 
			}*/
		}
    }
}
?>