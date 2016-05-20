<?php
namespace Merchant\Controller;

class AjaxController extends MerchantController {
	//系统功能->基础设置
	public function shopSet(){
		$sid = I('sid');
		$sitename = I('sitename');
		$bgm = I('bgm');
		$video = I('video');
		$foot = I('foot');
		
		$info = array(
			'sitename' => $sitename,
			'bgmon' => $bgm,
			'videoon' => $video,
			'footon' => $foot
		);
		M('shop')->where(array('jid'=>$this->jid,'sid'=>$sid))->save($info);
		die('1');
	}


	//复制分类
	public function copyClass(){
		$cid  = I('post.cid');
		$info = M('category')->where(array('id'=>$cid))->find();
		if ($info) {
			$re = M("category")->add(array('cimg'=>$info['cimg'],'model'=>$info['model'],'print_id'=>$info['print_id'],'jid'=>$info['jid'], 'corder'=>$info['corder'], 'sid'=>$info['sid'], 'cname'=>$info['cname']));
			exit( "$re" );
		}else{
			exit(0);
		}
	}





}