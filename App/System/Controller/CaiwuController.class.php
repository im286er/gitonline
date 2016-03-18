<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class CaiwuController extends ManagerController{
	public function shop(){
		$agent = I('agent',0);
		$opt = array();
		if($agent > 0){
			$opt['magent'] = $agent;
		}
		
		foreach(M('agent')->alias('AS a')->field('a.id,a.anickname,a.pid')->where(array('m.mtype'=>1))->join('__MEMBER__ AS m ON a.mid=m.mid')->select() as $r) {
			$agentListArray[$r["id"]] = $r;
		}
		$this->assign('agentList', \Common\Org\Tree::ItreeInitialize()->initialize($agentListArray)->treeRule(0, "<option \$selected value=\$id>\$spacer \$anickname</option>",$agent));
		
		$page = new \Think\Page(M('merchant')->where($opt)->count(), 10);
		$mlist = M('merchant')->field('jid,mnickname,magent,mid')->where($opt)->limit($page->firstRow.','.$page->listRows)->order('jid desc')->select();
		foreach($mlist as $k=>$v){
			$tp1 = M('order')->field(' sum(o_price) as tp1 ')->where(array('o_type'=>2,'o_pstatus'=>1,'o_jid'=>$v['jid']))->find();
			$tp2 = M('fl_order')->field(' sum(flo_price-flo_backprice) as tp2 ')->where(array('flo_ptype'=>1,'flo_pstatus'=>1,'flo_jid'=>$v['jid']))->find();
			
			$mlist[$k]['to_price'] = $tp1['tp1']+$tp2['tp2'];
			$agname = M('agent')->where(array('id'=>$v['magent']))->getField('anickname');
			$mlist[$k]['agname'] = $agname;
			$bk = M('bookkeeping')->field(' sum(bmention) as tixian ')->where(array('btype'=>1,'bmid'=>$v['mid']))->find();
			$mlist[$k]['tixian'] = $bk['tixian'] ? $bk['tixian'] : 0;
			$mlist[$k]['yue']    = M('member')->where(array('mid'=>$v['mid']))->getField('money');
		}
		$this->assign('orderlist', $mlist);
		$this->assign('pages', $page->show());
		$this->display();
	}
	
	public function count(){
		//商户总余额收入，总提现额，总余额
		//返利会员总收入,总提现,总余额
		$data1 = M('order')->field(' sum(o_price) as j_t1 ')->where(array('o_type'=>2,'o_pstatus'=>1,'o_jid'=>array('gt',0)))->find();
		$data2 = M('fl_order')->field(' sum(flo_price-flo_backprice) as j_t2 ')->where(array('flo_ptype'=>1,'flo_pstatus'=>1,'flo_jid'=>array('gt',0)))->find();	
		$data3 = M('bookkeeping')->field(' sum(bmention) as tixian ')->where(array('btype'=>1,'butype'=>0))->find();
		$data4 = M('member')->field(' sum(money) as yue ')->where(array('mtype'=>2))->find();
		
		$data5 = M('fl_translation')->field(' sum(flt_balance) as fl ')->find();
		$data6 = M('bookkeeping')->field(' sum(bmention) as tixian ')->where(array('btype'=>1,'butype'=>1))->find();
		$data7 = M('fl_user')->field(' sum(flu_balance) as yue ')->find();
		
		$merchant = array(
			't1' => $data1['j_t1'] + $data2['j_t2'],
			't2' => $data3['tixian'],
			't3' => $data4['yue'],
		);
		$user = array(
				't1' => $data5['fl'],
				't2' => $data6['tixian'],
				't3' => $data7['yue'],
		);
		$this->assign('merchant',$merchant);
		$this->assign('user',$user);
		$this->display();
	}
}