<?php
namespace Merchant\Controller;

class UserController extends MerchantController {
	//会员管理
	public function index() {
		$where = array('u_jid'=>$this->jid);
		if( $this->type == 2 ) { 
			//$where['usid'] = $this->tsid;
		}

		if( I('get.keywords') ) {
				$where['u_name'] = array('like', "%".I('get.keywords')."%");
		}
		if( I('get.u_source') ) {
				$where['u_source'] = I('get.u_source');
		}
		$page = new \Common\Org\Page(M('user')->where($where)->count(), 10);
		$this->assign('userlist', D('user')->order('u_id desc')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->assign('type',$this->type);
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
			$where['flu_nickname|op_content'] = array('like', "%{$_POST['keywords']}%");	
		}
		$where['op.op_status'] = '1';
		$page = new \Common\Org\Page(M('Opinion')->alias('AS op')->where($where)->join('azd_fl_user AS u ON op.op_uid=u.flu_userid')->count(), 10);
		$datalist = M('Opinion')->alias('AS op')->where($where)->join('azd_fl_user AS u ON op.op_uid=u.flu_userid')->order('op.op_id desc')->limit($page->firstRow.','.$page->listRows)->select();
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
			$uid = M('Opinion')->where(array('op_id'=>$op_id))->getField('op_uid');
			$clientid = M('User')->where(array('u_id'=>$uid))->getField('u_clientid');
			if($clientid){
				$app = M('merchantApp')->where( array('jid'=>$this->jid) )->field('gt_appid,gt_appkey,gt_appsecret,gt_mastersecret')->find();
				$this->_IGtPushMessageToCidTransmission($app,$clientid);
			}
			exit($result?'1':'0');
		}elseif($action=='operation'){
		   $op_status = I('post.op_status');
		   $result = M('Opinion')->where($where)->setField('op_status',$op_status);
		   exit($result?'1':'0');
	   }
	}
	
	//特定用户－透传
	private function _IGtPushMessageToCidTransmission($app,$clientid) {
		$info['title'] = '您的意见反馈商家已回复';
		$info['time'] = date('Y-m-d H:i:s');
		$info['imageUrl'] = '';
		$info['content'] = '您的意见反馈商家已回复，详情请到“关于我们”->“意见反馈中查看”';
		$args = array( 'transmissionContent' => JSON($info) );
		$mesg = array( 'offlineExpireTime'=>7200, 'netWorkType'=>0 );
		$res = \Common\Org\IGPushMsg::getIGPushMsg(true, $app)->pushMessageToCid($clientid, 4, json_encode($args), json_encode($mesg));
	}


	//全民返利会员管理
	public function rebate(){
		$where = array('flu_sjid'=>$this->jid);
		
		$keywords = trim(I('keywords'));
		
		if( !empty($keywords) ) {			
			$where['flu_phone'] = array('like', "%{$keywords}%");
		}
		
		$page = new \Common\Org\Page(M('fl_user')->where($where)->count(), 10);
		$this->assign('userlist', D('fl_user')->order('flu_userid desc')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->assign('type',$this->type);
		$this->assign('jid',$this->jid);
		$this->assign('MEMBER_B',C('MEMBER_B'));
		$this->display();
	}


	/**
	 * 更改特权会员状态
	 */
	public function changePrivilege(){
		$flu_userid = I('post.flu_userid');
		$privilege  = I('post.privilege');
		
		$result = M('FlUser')->where(array('flu_userid'=>$flu_userid))->setField('flu_privilege',$privilege);
		exit($result?'1':'0');
	}

}