<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class AccountingController extends ManagerController {
	public $agentid, $tjids=array();
	
	public function _initialize() {
		parent::_initialize();	
		$this->agentid = $agentid = \Common\Org\Cookie::get('agentid');	
		foreach(M('merchant')->where("magent=".$agentid)->field('jid')->select() as $merchant) $this->tjids[] = $merchant['jid'];
	}
	
    //收入明细(我的)
    public function incomeInfo() {
		$userid = M('flUser')->where('flu_gagentid='.$this->agentid)->getField('flu_userid');
		if( !$userid ) $this->error('你还没有注册或绑定返利会员');
		
		$ystime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
		$yetime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d')-1, date('Y')));
		$wstime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-date('w')+1, date('Y')));
		$wetime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d')-date('w')+7, date('Y')));
		
		//总收入
		$countIncomes = M('flTranslation')->where('`flt_uid`='.$userid.'')->sum('flt_balance');
		//昨日总收入
		$countDincome = M('flTranslation')->where("`flt_uid`=".$userid." and `flt_addtime`>='{$ystime}' and `flt_addtime`<='{$yetime}'")->sum('flt_balance');
		//本周总收入
		$countWincome = M('flTranslation')->where("`flt_uid`=".$userid." and `flt_addtime`>='{$wstime}' and `flt_addtime`<='{$wetime}'")->sum('flt_balance');
		//总支出
		$countSexpend = M('bookkeeping')->where("`bmid`={$userid} and `butype`=1")->sum('bmention');
		//昨日总支出
		$countDexpend = M('bookkeeping')->where("`bmid`={$userid} and `butype`=1 and `betime`>='{$ystime}' and `betime`<='{$yetime}'")->sum('bmention');
		//本周总支出
		$countWexpend = M('bookkeeping')->where("`bmid`={$userid} and `butype`=1 and `betime`>='{$wstime}' and `betime`<='{$wetime}'")->sum('bmention');
		
		$this->assign('countIncomes', $countIncomes);
		$this->assign('countDincome', $countDincome);
		$this->assign('countWincome', $countWincome);
		$this->assign('countSexpend', $countSexpend);
		$this->assign('countDexpend', $countDexpend);
		$this->assign('countWexpend', $countWexpend);
		
		$where['t.flt_uid'] = $userid;
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$statime=str_replace('+', '', I('get.statime'));
			$endtime=str_replace('+', '', I('get.endtime'));
			$where['t.flt_addtime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) { 
			$statime=str_replace('+', '', I('get.statime'));
			$where['t.flt_addtime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( I('get.endtime', '') ) {
			$endtime=str_replace('+', '', I('get.endtime'));
			$where['t.flt_addtime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		   
		if( isset($_GET['keyword']) && !empty($_GET['keyword'])  ) {
			$where['u.flu_nickname'] = array("like", "%{$_GET[keyword]}%"); 
		}
		
		//收入列表（也就是订单列表）
		$page = new \Think\Page(M('flTranslation')->where($where)->count(), 11);
		$accountlist = M('flTranslation')->alias('AS t')->join("__FL_USER__ AS u ON u.flu_userid=f.flt_tuserid")->field("t.*,u.flu_username")->where($where)->order('flt_addtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('accountlist', $accountlist);
		$this->assign('pages', $page->show());
		$this->display();	
    }
	
	//提现明细（我的）
	public function mentionInfo() {
		$userid = M('flUser')->where('flu_gagentid='.$this->agentid)->getField('flu_userid');
		if( !$userid ) $this->error('你还没有注册或绑定返利会员');
		$where['bmid'] = $userid;
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$statime=str_replace('+', '', I('get.statime'));
			$endtime=str_replace('+', '', I('get.endtime'));
			$where['b.bstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) { 
			$statime=str_replace('+', '', I('get.statime'));
			$where['b.bstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( I('get.endtime', '') ) {
			$endtime=str_replace('+', '', I('get.endtime'));
			$where['b.bstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		
		$page = new \Think\Page(M('bookkeeping')->where($where)->count(), 8);
		$bookkeepinglist = array();
		$this->assign('bookkeepinglist', M('bookkeeping')->alias('AS b')->field('b.*,u.flu_nickname,u.flu_username')->where($where)->join('__FL_USER__ AS u ON b.bmid=u.flu_userid', 'left')->order('b.bstime DESC')->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->display();
	}
	
	//查看明细
	public function accountingInfo() {
		$this->assign('account', M('bookkeeping')->alias('AS b')->field('b.*,u.flu_nickname,u.flu_username')->where('b.bls='.I('get.bls'))->join('__FL_USER__ AS u ON b.bmid=u.flu_userid', 'left')->order('b.bstime DESC')->find());				
		$this->display( 'Accounting_mention' );
	}
	
	//发送验证码 
	public function sendsms() {
		$tpl = I('get.val', ''); if( !$tpl ) exit("0");
		$content = \Org\Util\String::randString(4, 1);
		session('SendSms', $content);
		session('SendSmsTel', $tpl);
		exit(sendmsg( $tpl, $content) ? "1" : "0");		
	}
	
	//申请提现
	public function addMention() {
		if( IS_POST ) {
			$Pcode 		= I('post.Pcode', 0, 'floatval');
			$Pbannce 	= I('post.Pbannce');
			$Password 	= I('post.Password');
			$pcontent 	= I('post.pcontent');
			
			if( session('SendSms') != $Pcode ) $this->error('验证码输入错误');
			$userinfo = M('flUser')->where('flu_gagentid='.$this->agentid)->find();
			if( !$userinfo ) E('你还没有注册或绑定返利会员');
			
			if( md5(md5($Password)) != $userinfo['flu_withdrawpass'] ) {
				$this->error('你的提现密码出现错误');	
			}
			if( floatval($Pbannce) > $userinfo['flu_balance'] ) {
				$this->error('你提现的是非法数据');	
			}
			
			$userModel = M('flUser');
			$userModel->startTrans();
			
			$bresidue = $userinfo['flu_balance'] - $Pbannce;
			$result_s = $userModel->where("flu_userid=".$userinfo['flu_userid'])->setField('flu_balance', $bresidue);
			
			$data = array();
			$data['bmention'] = $Pbannce;
			$data['bls'] = $bls = date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
			$data['bmid'] = $userinfo['flu_userid'];
			$data['bstime'] = date('Y-m-d H:i:s');
			$data['bip'] = get_client_ip();
			$data['bdzh'] = $userinfo['flu_withdrawzh'];
			$data['bname'] = $userinfo['flu_username'];
			$data['bresidue'] = $bresidue;
			$data['bmarker'] = $pcontent;
			$data['butype'] = 2;
			$result_t = M('bookkeeping')->add($data);
			
			$error = 0;
			if( $result_s && $result_t ) {
				$userModel->commit();
			} else {
				$error = 1; $userModel->rollback();	
			}
			if( $error ) {
				$this->error('申请失败');	
			} else {
				$this->success('提现成功，请在记录里查看进度', U('Accounting/mentionInfo'));
			}
		} else {
			$userinfo = M('flUser')->where('flu_gagentid='.$this->agentid)->find();
			$userid = $userinfo['flu_userid'] ? $userinfo['flu_userid'] : 0;
			if( !$userid ) $this->error('你还没有注册或绑定返利会员');
			if( $userinfo['flu_balance'] <= 0  || !$userinfo['flu_withdrawzh'] || !$userinfo['flu_withdrawpass'] ) {
				$this->assign('error', 1);
			}
			$this->assign('countbance', $userinfo['flu_balance']);
			$this->assign('userphone', $userinfo['flu_phone']);
			$this->assign('flu_withdrawzh', $userinfo['flu_withdrawzh']);
			$this->assign('userid', $userid);
			$this->display();
		}
	}
}