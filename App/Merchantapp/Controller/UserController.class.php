<?php
namespace Merchantapp\Controller;

class UserController extends MerchantappController {

	public function index() {
		$where = array();
		//如果品牌登录并且品牌下的分店总数超过1个，则显示列表
		//if( $this->type==1 ) {
			//$where['u_jid'] = $this->jid;	
		//} else {
			//$sid = $this->sid ? $this->sid : $this->sidlist[0]['sid'];
			//$where['u_sid'] = $sid;
		//}
		!$_GET['type'] && $_GET['type']=1;

		$type = I('get.type', 1, 'intval');
		switch( $type ) {
			case 1:
				$userlist = M('fl_user')->where($where)->select();
			break;
			case 2:
				$userlist = M('fl_user')->where(array_merge($where, array('flu_source'=>'3')))->select();
			break;
			case 3:
				$userlist = M('fl_user')->where(array_merge($where, array('flu_source'=>'1')))->select();
			break;
			case 4:
				$userlist = M('fl_user')->where(array_merge($where, array('flu_source'=>'2')))->select();
			break;	
		}

		

		$jianame = M('merchant')->where('jid='.$this->jid)->getField('mnickname');

		foreach($userlist as $k=>$u) {

			$userlist[$k]['u_msg'] = M('opinion')->where("op_uid=".$u['flu_userid']." and op_status='0'")->count();

			

			//if( $u['u_sid'] ) {

				//$userlist[$k]['u_sname'] = M('shop')->where('sid='.$u['u_sid'])->getField('sname');

			//} else {

				$userlist[$k]['u_sname'] = $jianame;

			//}

		}



		$this->assign("count_a", M('fl_user')->where($where)->count());//总数

		$this->assign("count_w", M('fl_user')->where(array_merge($where, array('flu_source'=>'3')))->count());//微信

		$this->assign("count_x", M('fl_user')->where(array_merge($where, array('flu_source'=>'1')))->count());//QQ

		$this->assign("count_b", M('fl_user')->where(array_merge($where, array('flu_source'=>'2')))->count());//微博

		

		$this->assign('type', $type);

		$this->assign('userlist', $userlist);

		$this->display();

	}





	//查看会员的留言

	public function opinion() {

		$where = array('op_uid'=>I('get.uid'), 'op_status'=>"1");

		if( $this->type==1 ) {

			$where['op_jid'] = $this->jid;	

		} else {

			$sid = $this->sid ? $this->sid : $this->sidlist[0]['sid'];

			$where['op_sid'] = $sid;

		}

		

		//这个要修改的，现在不能一一回复，要做到可以连续回复

		$opinion = M('Opinion')->where( $where )->order('op_addtime desc')->limit(20)->select();

		$this->assign('opinion', $opinion);

		

		$this->assign("useravatar", M('fl_user')->where("flu_userid=".I('get.uid'))->getField('flu_avatar'));
		$this->assign("username", M('fl_user')->where("flu_userid=".I('get.uid'))->getField('flu_nickname'));
		$this->assign("merchantlo", M("merchantApp")->where("jid=".$this->jid)->getField("applogo"));		



		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';

		file_exists($path.'AutoReply2.php') && $AutoReply=file_get_contents($path.'AutoReply2.php');

		$this->assign('AutoReply', $AutoReply);

		

		$this->display();

	}
}