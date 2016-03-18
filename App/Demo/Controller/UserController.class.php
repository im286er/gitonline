<?php
namespace Demo\Controller;

class UserController extends MerchantController {
	//会员管理
	public function index() {
		$where = array('u_jid'=>$this->jid);
		if( $this->type == 2 ) { 
			//$where['usid'] = $this->tsid;
		}

		if( isset($_POST['keywords']) && !empty($_POST['keywords']) ) {
				$where['u_name'] = array('like', "%{$_POST['keywords']}%");
		}

		$page = new \Demo\Org\Page(M('user')->where($where)->count(), 10);
		$this->assign('userlist', D('user')->order('u_id desc')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->display();
	}
	
	//会员详情
	public function infouser() {
		$jid = M('user')->alias('AS u')->where("u.mid=".I('get.id', 0, 'intval'))->join('__SHOP__ AS s ON u.usid=s.sid')->getField('s.jid');
		if( !$jid || $jid !== $this->jid ) E('你无权查看当前页面');
		$userinfo = D('View')->view('user')->where('User.mid='.I('get.id', 0, 'intval'))->find();
		$this->assign('userinfo', $userinfo);
		$this->display();
	}

	public function opinion(){
		$where = array();
		$where = $this->type == 1 ? array('op.op_jid'=>$this->jid) : array('op.op_sid'=>$this->tsid);
		if( isset($_POST['keywords']) && !empty($_POST['keywords']) ) {
			$where['u_name|op_content'] = array('like', "%{$_POST['keywords']}%");	
		}
		$where['op.op_status'] = '1';
		$page = new \Demo\Org\Page(M('Opinion')->alias('AS op')->where($where)->join('__USER__ AS u ON op.op_uid=u.u_id')->count(), 10);
		$datalist = M('Opinion')->alias('AS op')->where($where)->join('__USER__ AS u ON op.op_uid=u.u_id')->order('op.op_id desc')->limit($page->firstRow.','.$page->listRows)->select();
		$sids = array();
		if($datalist)foreach($datalist as $value)$sids[] = $value['op_sid'];
		if($sids){
			$shops = M('shop')->field('sid,sname')->where('sid in('.implode(',',$sids).')')->select();
			$this->assign('shops', array_column($shops,'sname','sid'));
		}
		$this->assign('datalist', $datalist);
		$this->assign('pages', $page->show());
		$this->display();
	}
	
	public function opreply(){
		$action = I('post.action');
		$op_id = I('post.op_id');
		if(!$op_id)exit('0');
		$where = array();
		$where = $this->type == 1 ? array('op_jid'=>$this->jid) : array('op_sid'=>$this->tsid);
		$where['op_id']=$op_id;
		if($action=='reply'){
			$op_replytxt = I('post.op_replytxt');
			$data=array('op_replytxt'=>$op_replytxt,'op_replytime'=>time());
			$result = M('Opinion')->where($where)->setField($data);						
			exit($result?'1':'0');
		}elseif($action=='operation'){
		   $op_status = I('post.op_status');
		   $result = M('Opinion')->where($where)->setField('op_status',$op_status);
		   exit($result?'1':'0');
	   }
	}

}