<?php
namespace Ap\Controller;
use Think\Controller;

class AutoController extends Controller {
	
	//获取指定商家的信息
	public function getInfo() {
		$jid = I('get.jid', 0, 'intval');
		$appInfo = M('merchantApp')->where(array('jid'=>$jid))->find();
		$string = $appInfo ? $appInfo['appname'].',http://www.dishuos.com'.$appInfo['applogo'] : ',';
		exit( $string );
	}

}