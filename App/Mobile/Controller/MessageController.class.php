<?php
namespace Mobile\Controller;

class MessageController extends MobileController {
	
	public function index() {
		$pid = I('get.pid') or E('你无权查看当前页面');
		$message = M('pushContent')->where('pid='.$pid)->find();

		$this->assign('message', $message);
		$this->assign('isheader', true);
		$this->mydisplay($this->action_name);
	}
}