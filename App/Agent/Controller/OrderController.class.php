<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class OrderController extends ManagerController{
	public $agentid, $tjids=array();
	
	public function _initialize() {
		parent::_initialize();	
		$this->agentid = $agentid = \Common\Org\Cookie::get('agentid');	
		foreach(M('merchant')->where("magent=".$agentid)->field('jid')->select() as $merchant) $this->tjids[] = $merchant['jid'];
	}
	
    //订单列表
    public function ordersList() {
		$where = array('Orders.o_jid'=>array('in', $this->tjids));

		if( I('get.statime', '') && I('get.endtime', '') ) { 
			$statime = str_replace('+', '', I('get.statime'));
			$endtime = str_replace('+', '', I('get.endtime'));
			$where['o_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$statime = str_replace('+', '', I('get.statime'));
			$where['o_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( I('get.endtime', '') ) {
			$endtime = str_replace('+', '', I('get.endtime'));
			$where['o_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}

		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['sname|o_id'] = array('like', "%{$keyword}%", 'or'); 
		}
	
		$page = new \Think\Page(D('View')->view('order')->where($where)->count(), 15);     
		$this->assign('orderlist', D('View')->where($where)->order('o_dstime desc')->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->display();
    }


	//返利订单列表
	public function vordersList() {
		$where = array('flo_jid'=>array('in', $this->tjids));

		if( I('get.statime', '') && I('get.endtime', '') ) { 
			$statime = str_replace('+', '', I('get.statime'));
			$endtime = str_replace('+', '', I('get.endtime'));
			$where['flo_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$statime = str_replace('+', '', I('get.statime'));
			$where['flo_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( I('get.endtime', '') ) {
			$endtime = str_replace('+', '', I('get.endtime'));
			$where['flo_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}

		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['flo_id'] = array('like', "%{$keyword}%", 'or'); 
		}
	
		$page = new \Think\Page(D('View')->view('vorder')->where($where)->count(), 15);     
		$this->assign('orderlist', D('View')->where($where)->order('flo_dstime desc')->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->display();	
	}
}