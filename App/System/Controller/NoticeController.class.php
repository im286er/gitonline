<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class NoticeController extends ManagerController {
	private $mid;
	
	public function _initialize() {
		parent::_initialize();
		$this->mid = (int)\Common\Org\Cookie::get('mid');
	}
	
	//消息列表
	public function noticesList() {
		
		$where = array('n.fmid'=>$this->mid, 'n.tmid'=>array('in', array(-4, -3, $this->mid)), '_logic'=>'or');
		$page = new \Think\Page(M('notice')->alias('AS n')->where($where)->count(), 10);
		$tname = array(-1=>'所有商家', -2=>'所有代理商', -3=>'所有系统人员', -4=>'所有人员');
		$noticelist = M('notice')->alias('AS n')->where("( n.fmid={$this->mid} OR n.tmid IN (-4, -3, {$this->mid}) )")->field('n.*,d.cid,m1.mname as fname,m2.mname AS tname')->join("__NOTICE_DATA__ AS d ON n.nid=d.nid AND d.mid={$this->mid}", 'left')->join('__MEMBER__ as m1 ON n.fmid=m1.mid', 'left')->join('__MEMBER__ as m2 ON n.tmid=m2.mid', 'left')->order('n.nid DESC')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($noticelist as $k=>$notice) {
			array_key_exists($notice['tmid'], $tname) && $noticelist[$k]['tname']=$tname[$notice['tmid']];
		}
		$this->assign('noticelist', $noticelist);
		$this->assign('mid', $this->mid);
		$this->assign('pages', $page->show());
		$this->display();
	}

	//发送消息
	public function noticeAdd() {
		if( IS_POST ) {
			$data['fmid'] = $this->mid;
			if( I('post.p_type', 0, 'intval') !== 5 ) {
				$data['tmid'] = intval($_POST['p_type']);	
			} else {
				$data['tmid'] = M('member')->where(array('mname'=>I('post.muserid')))->getField('mid');
			}
			$data['ntis'] = I('post.ptitle');
			$data['ncon'] = I('post.pcontent');
			if( M('notice')->add($data) ) {
				
				//如果发送成功，把消息推送到商家APP里
				if( I('post.p_type', 0, 'intval') !== 5 ) {
					$appmsg['jid'] = $appmsg['sid'] = 0;
				} else {
					$appmsg['jid'] = M('merchant')->where(array('mid'=>$data['tmid']))->getField('jid'); 
					$appmsg['sid'] = 0;
				}
				$appmsg['title'] = $data['ntis'];
				$appmsg['content'] = $data['ncon'];
				$appmsg['type'] = 3;
				$appmsg['addtime'] = date('Y-m-d H:i:s');
				M('appmsg')->add($appmsg);
				
				
				$this->display('Jump:success');	
			} else { $this->display('Jump:error'); }
		} else {
			$this->display();			
		}
	}

	//删除消息
	public function noticeDel() {
		$nid = I('get.nid', ''); if( !$nid ) exit('0');
		exit( M('notice')->where(array('nid'=>array('in', "$nid"), 'fmid'=>$this->mid))->delete() !== false && M('noticeData')->where(array('nid'=>array('in', "$nid"), 'mid'=>$this->mid))->delete() !== false ? "1" : "0");
	}
	
	//查看消息
	public function noticeSet() {
		$m=M('noticeData')->add(array('mid'=>$this->mid, 'nid'=>I('get.nid', 0, 'intval')));
		if($m){$data=1;}
		
		echo $data;
	}
	
	//发送消息时，用于获取用户名
	public function publicGetName() {
		$keywords = I('get.q', ''); if( !$keywords ) exit('');
		foreach(M('Member')->field('mname,mtype')->where(array('mname'=>array('like', "{$keywords}%")))->select() as $m) {
			echo $m['mname']."|".$m['mtype']."\n";
		}
	}
}
