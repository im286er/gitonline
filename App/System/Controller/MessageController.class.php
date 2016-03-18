<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class MessageController extends ManagerController{

    //消息列表
    public function messagesList() {
		$page = new \Think\Page(D('View')->view('push')->count(), 18);
		$sj_list = $dl_list = $xt_list = array();
		
		foreach(D('View')->order('ptime DESC')->limit($page->firstRow.','.$page->listRows)->select() as $msg) {
			if( $msg['putype']==1 ) {
				$msg['ptuser'] = '全部用户';	
			} elseif($msg['putype']==2) {
				$msg['ptuser'] = '标签用户';
				$putag = unserialize($msg['putag']);
				if(isset($putag['pro']) && !empty($putag['pro'])) {
					$msg['ptuser'] .= "&nbsp;&nbsp;省份：".$putag['pro'];	
				}
				if(isset($putag['tag']) && !empty($putag['tag'])) {
					$msg['ptuser'] .= "&nbsp;&nbsp;标签：".$putag['tag'];	
				}
			} elseif($msg['putype']==3) {
				$msg['ptuser'] = '指定用户';
				$pcid = unserialize($msg['pucid']);
				if(isset($pcid['type']) && $pcid['type']==1) {
					$msg['ptuser'] .= "&nbsp;&nbsp;CID：".$pcid['list'];	
				}
				if(isset($pcid['type']) && $pcid['type']==2) {
					$msg['ptuser'] .= "&nbsp;&nbsp;别名：".$pcid['list'];	
				}
			}
			if( $msg['mtype']==1 ) {
				$dl_list[] = $msg['pmid'];
			} elseif( $msg['mtype']==2 ) {
				$sj_list[] = $msg['pmid'];
			}
			$msglist[] = $msg;	
		}
		$dl_list = array_unique($dl_list);
		$sj_list = array_unique($sj_list);
		
		$user_nickname = array();
		//获取所有的商家名，代理商名
		if($dl_list) foreach( M('agent')->where(array("mid"=>array("in", $dl_list)))->field('mid,anickname')->select() as $agent )
		{
			$user_nickname[$agent['mid']] = $agent['anickname'];
		}
		
		//获取所有的商家名
		if($sj_list) foreach( M('merchantUser u')->where(array("u.tmid"=>array("in", $sj_list)))->join("__MERCHANT__ AS m ON u.tjid=m.jid", "left")->join("__SHOP__ AS s ON u.tsid=s.sid", "left")->field("u.tmid,u.type,m.mnickname,s.sname")->select() as $jid )
		{
			if( $jid['type']==1 ) {
				$user_nickname[$jid['tmid']] = $jid['mnickname'];
			} else {
				$user_nickname[$jid['tmid']] = $jid['sname'];
			}
		}		
		
		$this->assign('user_nickname', $user_nickname);
		$this->assign('msglist', $msglist);
		$this->assign('pages', $page->show());
	   	$this->display();
    }

	 //添加推送消息
	public function _before_messageAddtc() {
		if( IS_POST ):
			$data['pmid'] = \Common\Org\Cookie::get('mid');
			$data['ptitle'] = I('post.ptitle', '');
			$data['pcontent'] = I('post.pcontent', '');
			$data['putype'] = I('post.type', 1, 'intval');
			switch($data['putype']) {
                case 1:
					$data['putag'] = "";
					$data['pucid'] = ""; 
				break;
				case 2:
					$data['putag'] = serialize(array('pro'=>I('post.p1', '')));
					$data['pucid'] = serialize(array());
				break;
				case 3:
					$data['putag'] = serialize(array());
					$data['pucid'] = serialize( array('type'=>1, 'list'=>I('post.c_t_b')) ); 
				break;	
			}
			$data['pline'] = I('post.c_s1_v', 2, 'intval');
			$this->data = $data;
		endif;
	}
	
	//添加推送消息（透传）
	public function messageAddtc() {
		if( IS_POST ) {	
			if( M('pushContent')->add($this->data) ) {
				switch( $this->data['putype'] ) {
					case 1: $this->_IGtPushMessageToAllTransmission(); break;
					case 2: $this->_IGtPushMessageToTagTransmission(); break;
					case 3: $this->_IGtPushMessageToCidTransmission(); break;	
				}
				$this->display('Jump:success');
			} else { $this->display('Jump:error'); }
		} else {				
			$this->display();
		}
	}
	
	//按会员ID，推送消息
	public function _before_messageAddByMid() {
		if( IS_POST ):
			$cidlist_string = implode(',', $_POST['mid']);
			$data['pmid'] = \Common\Org\Cookie::get('mid');
			$data['ptitle'] = I('post.ptitle', '');
			$data['pcontent'] = I('post.pcontent', '');
			$data['putype'] = 3;
			$data['putag'] = serialize( array() );
			$data['pucid'] = serialize( array('type'=>1, 'list'=>$cidlist_string) );
			$data['psucc'] = 1;
			$data['pline'] = I('post.c_s1_v', 2, 'intval');
			$this->data = $data;
		endif;	
	}

	public function messageAddByMid() {
		if( IS_POST ) {
			if( M('pushContent')->add($this->data) ) {
				$this->_IGtPushMessageToCidTransmission();
				$this->display('Jump:success');
			} else { $this->display('Jump:error'); }
		} else {
			$userlist = M('user')->where(array('u_id'=>array('in', I('get.uid'))))->select();
			if(!is_array($userlist) || empty($userlist)) $this->display('Jump:error');
			$this->assign('userName', $userlist);
			$this->display();
		}
	}
	
	//查看消息
	public function messageInfo() {
		$messageInfo = D('View')->view('push')->where(array('pid'=>I('get.pid', 0, 'intval')))->find();	
		if( !is_array($messageInfo) || empty($messageInfo) ) {
			$this->assign('msg', '消息不存在'); $this->display('Jump:error');	
		}
		$this->assign('msg', $messageInfo);
		$this->display();
	}
	
	
	//全部用户－透传
	private function _IGtPushMessageToAllTransmission() {
		$info['title'] = $this->data['ptitle'];
		$info['time'] = date('Y-m-d H:i:s');
		$info['imageUrl'] = '';
		$info['content'] = $this->data['pcontent'];
		
		$args = array( 'transmissionContent' => JSON($info) );
		$mesg = array( 'offlineExpireTime'=>7200, 'netWorkType'=>0 );
		
		$where['_string'] = "`gt_appid`<>'' and `gt_appkey`<>'' and `gt_appsecret`<>'' and `gt_mastersecret`<>''";
		$merchant_list = M('merchantApp')->where( $where )->field('gt_appid,gt_appkey,gt_appsecret,gt_mastersecret')->select();
		
		foreach($merchant_list as $app) {
			\Common\Org\IGPushMsg::getIGPushMsg(true, $app)->pushMessageToApp(4, json_encode($args), json_encode($mesg), array(), array());
		}
	}
	
	//标签用户－透传
	private function _IGtPushMessageToTagTransmission() {
		$info['title'] = $this->data['ptitle'];
		$info['time'] = date('Y-m-d H:i:s');
		$info['imageUrl'] = '';
		$info['content'] = $this->data['pcontent'];
		
		$args = array( 'transmissionContent' => JSON($info) );
		$mesg = array( 'offlineExpireTime'=>7200, 'netWorkType'=>0 );
		$taglist = unserialize($this->data['putag']);
		
		$where['_string'] = "`gt_appid`<>'' and `gt_appkey`<>'' and `gt_appsecret`<>'' and `gt_mastersecret`<>''";
		$merchant_list = M('merchantApp')->where( $where )->field('gt_appid,gt_appkey,gt_appsecret,gt_mastersecret')->select();
		
		foreach($merchant_list as $app) {
			\Common\Org\IGPushMsg::getIGPushMsg(true, $app)->pushMessageToApp(4, json_encode($args), json_encode($mesg), explode(',', $taglist['pro']), array());
		}
	}
	
	//特定用户－透传
	private function _IGtPushMessageToCidTransmission() {
		$info['title'] = $this->data['ptitle'];
		$info['time'] = date('Y-m-d H:i:s');
		$info['imageUrl'] = '';
		$info['content'] = $this->data['pcontent'];
		$args = array( 'transmissionContent' => JSON($info) );
		$mesg = array( 'offlineExpireTime'=>7200, 'netWorkType'=>0 );
		$pucid = unserialize($this->data['pucid']);
		$cidlist = implode("','", explode(',', $pucid['list']));

		//foreach(M('user')->alias('AS u')->join('__MERCHANT_APP__ AS p ON u.u_jid=p.jid')->where("u.u_clientid in ('{$cidlist}') and p.`gt_appid`<>'' and p.`gt_appkey`<>'' and p.`gt_appsecret`<>'' and p.`gt_mastersecret`<>''")->group('u.u_clientid')->field('u.u_clientid,p.gt_appid,p.gt_appkey,p.gt_appsecret,p.gt_mastersecret')->select() as $app) {
			//$res = \Common\Org\IGPushMsg::getIGPushMsg(true, $app)->pushMessageToCid($app['u_clientid'], 4, json_encode($args), json_encode($mesg));
		//}
	}

}