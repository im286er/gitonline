<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class UserController extends ManagerController {
    //会员列表
    public function usersList() {
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$stime=I('get.statime'); $etime=I('get.endtime');
			$where['u_regtime'] = array(array('egt', date('Y-m-d 00:00:00', strtotime($stime))), array('elt', date('Y-m-d 23:59:59', strtotime($etime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$stime=I('get.statime');
			$where['u_regtime'] = array('egt', date('Y-m-d 00:00:00', strtotime($stime)));	
		} elseif( I('get.endtime', '') ) {
			$etime=I('get.endtime');
			$where['u_regtime'] = array('elt', date('Y-m-d 23:59:59', strtotime($etime)));          	
		}

		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['u_name|u_source|mnickname'] = array('like', "%{$keyword}%", 'or'); 
		}
		$page = new \Think\Page(D('View')->view('user')->where($where)->count(), 10);
        $this->assign('usersList',D('View')->view('user')->where($where)->order('u_regtime DESC')->limit($page->firstRow.','.$page->listRows)->select());
	    $this->assign('pages',$page->show()); 
		$this->display();
    }
	
	//返利会员
	public function vusersList() {
		//统计
		$count = M('flUser')->count();
		
		$stime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
		$etime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d')-1, date('Y')));

		$ycount = M('flUser')->where("`flu_regtime`>='{$stime}' and `flu_regtime`<='{$etime}'")->count();
		
		$stime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
		$tcount = M('flUser')->where("`flu_regtime`>='{$stime}'")->count();
		
		$stime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-date('w'), date('Y')));
		$wcount = M('flUser')->where("`flu_regtime`>='{$stime}'")->count();

		$this->assign('count', $count);
		$this->assign('ycount', $ycount);
		$this->assign('tcount', $tcount);
		$this->assign('wcount', $wcount);
		$where = array();
		if(I('get.puserid', '', 'intval'))$where['flu_puserid'] = I('get.puserid');
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$stime=I('get.statime'); $etime=I('get.endtime');
			$where['flu_regtime'] = array(array('egt', date('Y-m-d 00:00:00', strtotime($stime))), array('elt', date('Y-m-d 23:59:59', strtotime($etime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$stime=I('get.statime');
			$where['flu_regtime'] = array('egt', date('Y-m-d 00:00:00', strtotime($stime)));	
		} elseif( I('get.endtime', '') ) {
			$etime=I('get.endtime');
			$where['flu_regtime'] = array('elt', date('Y-m-d 23:59:59', strtotime($etime)));          	
		}

		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['flu_nickname'] = array('like', "%{$keyword}%", 'or'); 
		}
		
		$page = new \Think\Page(D('View')->view('vuser')->where($where)->count(), 10);
        $this->assign('usersList',D('View')->view('vuser')->where($where)->order('flu_regtime DESC')->limit($page->firstRow.','.$page->listRows)->select());
	    $this->assign('pages',$page->show()); 
		$this->display();
	}
	
	//绑定会员为商家或代理商
	public function setUserInfo() {
		if( IS_POST ) {
			$userid = I('post.uid', 0, "intval");
			//如果没有，表示删除
			$data['flu_gagentid']	= intval($_POST['agentid']);
			$data['flu_gjid']	= intval($_POST['jid']);
			 
			if( M('flUser')->where("flu_userid=".$userid)->save( $data ) !== false ) {
                 $this->display('Jump:success');
            } else { 
				$this->display('Jump:error');
			}
		} else {
			$userInfo = M('flUser')->where("flu_userid=".I('get.uid'))->find();
			if( isset($userInfo['flu_gagentid']) && $userInfo['flu_gagentid'] ) {
				$this->assign("agentinfo", M('agent')->where("id=".$userInfo['flu_gagentid'])->find());				
			}
			
			if( isset($userInfo['flu_gjid']) && $userInfo['flu_gjid'] ) {
				$merchantinfo = M('merchant')->where("jid=".$userInfo['flu_gjid'])->find();
				$this->assign("merchantinfo", $merchantinfo);
				$this->assign("agentinfos", M('agent')->where("id=".$merchantinfo['magent'])->find());	
			}
			
			$this->assign('userinfo', $userInfo);	
			$this->display();	
		}
	}
	
	//绑定账号时，选择代理商
	public function ajaxAgent() {
		$string = '<option>所属代理商</option>';
		foreach( M('agent')->alias('AS a')->join("__MEMBER__ AS m ON a.mid=m.mid")->where("m.mstatus >= 0 ")->field('a.id,a.anickname')->select() as $agent) {
			$string .= '<option value="'.$agent['id'].'">'.$agent['anickname'].'</option>';
		}
		exit( $string );
	}
	
	//绑定账号时，选择商家
	public function ajaxMerchant() {
		$string = '<option>所属商家</option>';
		foreach( M('merchant')->alias('AS j')->join("__MEMBER__ AS m ON j.mid=m.mid")->where('m.mstatus >= 0  AND j.magent='.I('post.agentid', "", 'intval'))->field('j.jid,j.mnickname')->select() as $agent) {
			$string .= '<option value="'.$agent['jid'].'">'.$agent['mnickname'].'</option>';
		}
		exit( $string );
	}
	
	//查看会员信息
	public function usersInfo() {
		$userInfo = D('View')->view('user')->where(array('u_id'=>I('get.uid', 0, 'intval')))->find(); 
		if( !is_array($userInfo) && empty($userInfo) ) $this->display('Jump:error');
		$this->assign('user', $userInfo);
		$this->display();	
	}
	
	//查看V会员的信息
	public function vusersInfo() {
		$userInfo = D('View')->view('vuser')->where(array('flu_userid'=>I('get.uid', 0, 'intval')))->find(); 
		if( !is_array($userInfo) && empty($userInfo) ) $this->display('Jump:error');
		$this->assign('count', M('flUser')->where("flu_puserid=".$userInfo['flu_userid'])->count());		
		$this->assign('user', $userInfo);
		$this->display();
	}

	//查看会员消费记录
	public function consumption() {
		$orderInfo = M('order')->where(array('o_uid'=>I('get.uid', 0, 'intval')))->select();
		$countNum = 0;
		foreach($orderInfo as $key=>$value) {
			//此处的效率极低，先这样，以后好好优化
			$orderInfo[$key]['ogoods'] = M($value['o_table'])->where('sp_oid='.$value['o_id'])->select();
			$countNum += $value['o_price'];	
		}
		$this->assign('countNum', number_format($countNum, 2));
		$this->assign('orderInfo', $orderInfo);
		$this->display();
	}
	
	//查看V会员消息记录
	public function vconsumption() {
		$orderInfo = M('flOrder')->alias('AS o')->join("__MERCHANT__ AS m ON o.flo_jid=m.jid", "left")->field("o.*,m.mnickname")->where(array('flo_uid'=>I('get.uid', 0, 'intval')))->select();
		$countNumo = $countNumt = 0;
		foreach($orderInfo as $key=>$value) { 
			$countNumo += $value['flo_price']; 
			if( $value['flo_pstatus']==1 ) $countNumt += $value['flo_price'];
		}
		$this->assign('countNumo', number_format($countNumo, 2));
		$this->assign('countNumt', number_format($countNumt, 2));
		$this->assign('orderInfo', $orderInfo);
		$this->display();	
	}
}