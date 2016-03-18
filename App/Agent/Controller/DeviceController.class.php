<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class DeviceController extends ManagerController{
	private $agentid;
	
	public function _initialize() {
		parent::_initialize();
		$this->agentid = \Common\Org\Cookie::get('agentid');	
	}
	
    //设备列表
    public function deviceList() {
		!$_GET['type'] && $_GET['type']=1;
		
		//获取所有的子代理
		$child_list = $child_id_list = array();
		$child_list = M('agent')->alias('AS a')->join('__MEMBER__ AS m ON a.mid=m.mid')->where(" m.mstatus=1 and a.pid=".$this->agentid)->select();
		if(is_array($child_list) && !empty($child_list)) foreach($child_list as $c) $child_id_list[] = $c['id']; 
		
		
		if( isset($_GET['type']) && intval($_GET['type'])==2 ) { //未分配
			$where = array('rmerchant' => 0, 'ragent'=>$this->agentid);
		} else if( isset($_GET['type']) && intval($_GET['type'])==3 ) { //已分配
			if( !empty($child_id_list) ) {
				$where = array("_string" => '( ragent='.$this->agentid.' and rmerchant != 0 ) or ragent in('.implode(',', $child_id_list).')');
			} else {
				$where = array("_string" => 'rmerchant != 0 and ragent='.$this->agentid);	
			}
		} else {//所有设备
			$child_id_list[] = $this->agentid;
			$where['ragent'] = array("in", $child_id_list);
		}
		
		if( isset($_GET['status']) && $_GET['status'] != '' ) {
			$where['rstatus'] = I('get.status', 0, 'intval');	
		}

		if( I('get.sq', '') ) { 
			$rcode = trim(I('get.sq', '')); 
			
			switch( I('get.search_type', 0, 'intval') )
			{
				case 1:
					$where['boxnum'] = I('get.sq', "", 'intval');  
				break;
				
				case 2:
					$where['rid'] = array('like', "%{$rcode}%");  
				break;
				
				case 3:
					$where['rcode'] = array('like', "%{$rcode}%");  
				break;
			}
		}

		if( I('get.mc', '') ) { 
			$rname = trim(I('get.mc', '')); $where['rname'] = array('like', "%{$rname}%");  
		}

        $page = new \Think\Page(M('router')->where($where)->count(), 10);
		$deviceList = M('router')->where( $where )->limit($page->firstRow.','.$page->listRows)->order('rid DESC')->select();
		$agentid = $merchantid = $shopid = array();
		if( is_array($deviceList) && !empty($deviceList) ) foreach( $deviceList as $d ) { 
			if( !empty($d['ragent']) ) $agentid[ $d['ragent'] ] = $d['ragent'];
			if( !empty($d['rmerchant']) ) $merchantid[ $d['rmerchant'] ] = $d['rmerchant'];
			if( !empty($d['rshop']) ) $shopid[ $d['rshop'] ] = $d['rshop'];
		}
		
		if( !empty($agentid) ) {//获取代理商
			$agentlist = M('agent')->where( array("id"=>array("in", $agentid)) )->field("id,anickname")->select();
			foreach( $agentlist as $a ) $agentlistarr[ $a['id'] ] = $a['anickname'];
		}
		
		if( !empty($merchantid) ) {//获取商家
			$merchantlist = M('merchant')->where( array("jid"=>array("in", $merchantid)) )->field("jid,mnickname")->select();
			foreach( $merchantlist as $a ) $merchantlistarr[ $a['jid'] ] = $a['mnickname'];
		}
		
		if( !empty($shopid) ) {//获取门店
			$shoplist = M('shop')->where( array("sid"=>array("in", $shopid)) )->field("sid,sname")->select();
			foreach( $shoplist as $a ) $shoplistarr[ $a['sid'] ] = $a['sname'];
		}
		
		$this->assign('agentlistarr', $agentlistarr ? $agentlistarr : array());
		$this->assign('merchantlistarr', $merchantlistarr ? $merchantlistarr : array());
		$this->assign('shoplistarr', $shoplistarr ? $shoplistarr : array());
        $this->assign('deviceList', $deviceList ? $deviceList : array());
        $this->assign('pages', $page->show());
		
		$count_child = M('agent')->alias('AS a')->join('__MEMBER__ AS m ON a.mid=m.mid')->where(" m.mstatus=1 and a.pid=".$this->agentid)->count();
		$this->assign('ischild', $count_child ? "1" : "0");
		
        $this->display();
    }

	//分配设备到商家
	public function setMerchant() {
		if( IS_POST ) {
			$rid = I('post.rid');
			//先把这些设备的绑定取消
			M('router')->where(array("rid"=>array("in", "{$rid}")))->save( array('rmerchant'=>0, 'rshop'=>0) );
			
			if( M('router')->where(array("rid"=>array("in", "{$rid}")))->save( $_POST['info'] ) !== false ) {
				$this->display('Jump:success'); 
			} else {
				$this->display('Jump:error');
			}
		} else {
			$merchant = M('merchant')->alias('AS j')->join('__MEMBER__ AS m ON j.mid=m.mid')->where("m.mstatus=1 and j.magent=".$this->agentid)->field('j.jid,j.mnickname')->select();	
			$this->assign('merchant', $merchant);
				
			if( intval($_GET['type'])==1 ) {
				$regent=M('router')->where(array('rid'=>I('get.rid')))->find();
				if( !$regent ) goto noinfo;
				$this->assign('regent', $regent['ragent']);

				if( $regent['rmerchant'] ) { $this->assign('jid', $regent['rmerchant']); }
				
				if( $regent['rmerchant'] ) {
					$shoplist = M('shop')->where("status='1' and jid=".$regent['rmerchant'])->field('sid,sname')->select();
					$this->assign('sid', $regent['rshop']);
					$this->assign('shoplist', $shoplist);
				}
			}
			noinfo: 
			$this->assign('rid', I('get.rid'));
			$this->display();		
		}
	}
	
	//分配设备到子代理
	public function setAgent() {
		if( IS_POST ) {
			$rid = I('post.rid');
			//先把这些设备的绑定取消
			M('router')->where(array("rid"=>array("in", "{$rid}")))->save( array('ragent'=>0, 'rmerchant'=>0, 'rshop'=>0, "rstatus"=>0) );
			
			if( M('router')->where(array("rid"=>array("in", "{$rid}")))->save( array("ragent"=>I('post.ragent', 0, 'intval')) ) !== false ) {
				$this->display('Jump:success'); 
			} else {
				$this->display('Jump:error');
			}
		} else {
			$agent_list = M('agent')->alias('AS a')->join('__MEMBER__ AS m ON a.mid=m.mid')->where(" m.mstatus=1 and a.pid=".$this->agentid)->select();	
			$this->assign('agent_list', $agent_list);
				
			if( intval($_GET['type'])==1 ) {
				$regent=M('router')->where(array('rid'=>I('get.rid')))->getField('ragent');
				$this->assign('regent', $regent);
			}
			$this->assign('rid', I('get.rid'));
			$this->display('setAgentid');		
		}
	}
	
	
	//连接情况
	public function linksInfos() {
		$where = array('rcode'=>I('get.rid'));
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['rlast'] = array(array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime')))), array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime')))), 'and');					
		} elseif( I('get.statime', '') ) {
			$where['rlast'] = array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['rlast'] = array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime'))));	
		}
		$page = new \Think\Page(M('routerUser')->where( $where )->count('distinct(rusermac)'), 15);
        $this->assign('userList', M('routerUser')->where( $where )->limit($page->firstRow.','.$page->listRows)->order('rlast DESC')->group('rusermac')->select());
        $this->assign('pages', $page->show());
		$this->display();
	}

	//登录记录
	public function linksLists() {
		$where = array('rusermac'=>I('get.mac'));
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['rlast'] = array(array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime')))), array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime')))), 'and');					
		} elseif( I('get.statime', '') ) {
			$where['rlast'] = array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['rlast'] = array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime'))));	
		}
		$page = new \Think\Page(M('routerUser')->where( $where )->count(), 15);

        $this->assign('userList', M('routerUser')->where( $where )->limit($page->firstRow.','.$page->listRows)->order('rlast DESC')->select());
        $this->assign('pages', $page->show());
		$this->display();
	}
	
	//获取分店
	public function getShop() {
		$jid = I('get.id', 0, 'intval');
		$shoplist = M('shop')->where("status='1' and jid=".$jid)->field('sid,sname')->select();	
		$html  = $shoplist ? '<option value="0">选择所属分店</option>' : ''; 
		if( is_array($shoplist) && !empty($shoplist) ) {
			foreach($shoplist as $m) {
				$html .= '<option value="'.$m['sid'].'">'.$m['sname'].'</option>';
			}	
		}
		echo $html;
	}

}