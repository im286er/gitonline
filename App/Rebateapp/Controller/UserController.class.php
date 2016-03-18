<?php
namespace Rebateapp\Controller;
use Think\Controller;
/* * *我的 * * */
class UserController extends Controller {
	public $userid;
	public $userinfo;

	/**登录授权**/
	public function login(){
		$username = I('post.username');
		$password = I('post.password');
		$username or die(JSON(array('errcode'=>80101,'errmsg'=>'请填写用户名')));
		$password or die(JSON(array('errcode'=>80102,'errmsg'=>'请填写密码')));
		$result = D('FlUser')->login($username,$password);
		if($result['errcode']>0)die(JSON($result));
		$ucode = I('post.ucode')?I('post.ucode'):randpw(4,'CHAR');
		$data = D('FlUsertoken')->sign_utoken($this->appid,$result['flu_userid'],$ucode);
		die(JSON($data));
	}

	/**续签协议授权**/
	public function renewal(){
		$utoken = I('post.utoken');
		$ucode = I('post.ucode')?I('post.ucode'):randpw(4,'CHAR');
		$utoken or die(JSON(array('errcode'=>80005,'errmsg'=>'utoken未提交！')));
		$ucode or die(JSON(array('errcode'=>80006,'errmsg'=>'ucode未提交！')));
		$data = D('FlUsertoken')->renew_utoken($this->appid,$utoken,$ucode);
		die(JSON($data));
	}
	
	/**注册**/
	public function register(){
		$username = trim(I('post.username'));
		$password = trim(I('post.password'));
		$ismobile = trim(I('post.ismobile'));
		$username or die(JSON(array('errcode'=>80101,'errmsg'=>'请填写用户名')));
		$password or die(JSON(array('errcode'=>80102,'errmsg'=>'请填写密码')));
		$result = D('FlUser')->register($this->appid,$username,$password,$ismobile);
		die(JSON($result));
	}

	/**第三方快捷登录**/
	public function sdklogin(){
		$openid = trim(I('post.openid'));
		$source = trim(I('post.source'));
		$avatar = trim(I('post.avatar'));
		$nickname = trim(I('post.nickname'));
		$openid or die(JSON(array('errcode'=>80201,'errmsg'=>'无第三方登录openid')));
		$source or die(JSON(array('errcode'=>80202,'errmsg'=>'无第三方登录来源')));
		$result = D('FlUser')->sdklogin($this->appid,$openid, $source, $avatar,$nickname);
		die(JSON($result));
	}
	
	/**用户退出登录**/
	public function loginout(){
		$this->common();
		$utoken = I('post.utoken');
		$utoken or die(JSON(array('errcode'=>80005,'errmsg'=>'utoken未提交！')));
		$result = D('FlUser')->login_out($this->appid,I('post.utoken'));
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'用户退出成功'))):die(JSON(array('errcode'=>80144,'errmsg'=>'用户退出失败')));
	}


	/***检测utoken是否通过***/
	public function checkUtoken(){
		$result = D('FlUsertoken')->checkUtoken(I('post.utoken'));
		if(is_array($result))die(JSON($result));
		die(JSON(array('errcode'=>'ok','errmsg'=>'utoken正确')));
	}

	/***获取用户基本信息**/
	public function common(){
		$result = D('FlUsertoken')->checkUtoken(I('post.utoken'));
		if(is_array($result))die(JSON($result));
		/**验证通过写获取方法**/
		$this->userid = $result;
		$this->userinfo = D('FlUser')->userinfo($result);
		/**验证通过写获取方法**/
	}
	/***获取用户的公共信息**/
	public function userinfo(){
		$this->common();
		die(JSON($this->userinfo));
	}

	/***修改用户名**/
	public function editUsername(){
		$this->common();
		$username = trim(I('post.username'));
		$username or exit(JSON(array('errcode'=>81011,'errmsg'=>'用户名不能为空')));
		$is_username = D('FlUser')->where(array('flu_username'=>$username))->find();
		!$is_username or exit(JSON(array('errcode'=>81012,'errmsg'=>'用户名已经存在')));
		$result = D('FlUser')->where(array('flu_userid'=>$this->userid))->setField('flu_username',$username);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'修改用户名成功'))):die(JSON(array('errcode'=>81005,'errmsg'=>'修改用户名失败')));
	}

	/***修改昵称**/
	public function editNickname(){
		$this->common();
		$nickname = trim(I('post.nickname'));
		$nickname or die(JSON(array('errcode'=>81031,'errmsg'=>'昵称不能为空')));
		(strlen($nickname) >= 3 && strlen($nickname) <= 21 ) or die(JSON(array('errcode'=>81032,'errmsg'=>'昵称设置须2-7个汉字')));
		$result = D('FlUser')->where(array('flu_userid'=>$this->userid))->setField('flu_nickname',$nickname);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'昵称设置成功'))):die(JSON(array('errcode'=>81033,'errmsg'=>'昵称设置失败')));
	}


	/***找回密码**/
	public function findPassword(){
		$mobile = trim(I('post.mobile'));
		$mobile or die( JSON( array('errcode'=>81010, 'errmsg'=>'请输入手机号码') ) );
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[012356789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $mobile) ) {
			die( JSON( array('errcode'=>81011, 'errmsg'=>'手机号格式不正确') ) );
		}
		$newpassword = D('FlUser')->passwordmd5(trim(I('post.newpassword')));
		trim(I('post.newpassword')) or die(JSON(array('errcode'=>81014,'errmsg'=>'请输入新密码')));
		$result = D('FlUser')->where(array('flu_phone'=>$mobile))->setField('flu_password',$newpassword);
		die(JSON(array('errcode'=>'ok','errmsg'=>'找回密码成功')));
		//$result?die(JSON(array('errcode'=>'ok','errmsg'=>'找回密码成功'))):die(JSON(array('errcode'=>81006,'errmsg'=>'找回密码失败')));
	}


	/***修改登录密码**/
	public function editPassword(){
		$this->common();
		$oldpassword = D('FlUser')->passwordmd5(trim(I('post.oldpassword')));
		$newpassword = trim(I('post.newpassword'));
		if($this->userinfo['flu_password'] && !$oldpassword)die(JSON(array('errcode'=>81013,'errmsg'=>'请输入原密码')));
		if(!$newpassword)die(JSON(array('errcode'=>81014,'errmsg'=>'请输入新密码')));
		if($this->userinfo['flu_password'] != $oldpassword)die(JSON(array('errcode'=>81015,'errmsg'=>'原密码错误')));
		$result = D('FlUser')->where(array('flu_userid'=>$this->userid))->setField('flu_password',D('FlUser')->passwordmd5($newpassword));
		die(JSON(array('errcode'=>'ok','errmsg'=>'登录密码修改成功')));
		//$result?die(JSON(array('errcode'=>'ok','errmsg'=>'登录密码修改成功'))):die(JSON(array('errcode'=>81005,'errmsg'=>'登录密码修改失败')));
	}	
	/***绑定手机号***/
	public function setMobile(){
		$this->common();
		$mobile = I('post.mobile','','trim');
		$mobile or die( JSON( array('errcode'=>81010, 'errmsg'=>'请输入手机号码') ) );
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[012356789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $mobile) ) {
			die( JSON( array('errcode'=>81011, 'errmsg'=>'手机号格式不正确') ) );
		}
		$is_mobile = D('FlUser')->where(array('flu_phone'=>$mobile))->find();
		!$is_mobile or die(JSON(array('errcode'=>81013,'errmsg'=>'您要绑定的手机号码已存在！')));
		$data = array();
		$data['flu_phone'] = $mobile;
		//if($this->userinfo['flu_username'] && $this->userinfo['flu_phone'] && $this->userinfo['flu_username']==$this->userinfo['flu_phone']){
			$data['flu_username'] = $mobile;
		//}
		$result = D('FlUser')->where(array('flu_userid'=>$this->userid))->setField($data);
		if(!$this->userinfo['flu_phone']){
			D('FlInvite')->confirmMobileInvite($this->userid,$mobile);
		}
		die(JSON(array('errcode'=>'ok','errmsg'=>'手机号码绑定成功')));
		//$result?die(JSON(array('errcode'=>'ok','errmsg'=>'手机号码绑定成功'))):die(JSON(array('errcode'=>81005,'errmsg'=>'手机号码绑定失败')));
	}

	/***发送验证码***/
	public function sendsms() {
		$mobile = I('post.mobile','','trim');
		$verify  = I('post.verify','','trim');
		if( !$mobile ) die(JSON(array('errcode'=>81443,'errmsg'=>'请输入发送的手机号码')));
		
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[012356789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $mobile) ) {
			die( JSON( array('errcode'=>81011, 'errmsg'=>'手机号格式不正确') ) );
		}
		
		if($verify=='user_unique'){
			$is_mobile = D('FlUser')->where(array('flu_phone'=>$mobile))->find();
			!$is_mobile or die(JSON(array('errcode'=>81013,'errmsg'=>'您要绑定的手机号码已存在！')));
		}elseif($verify=='user_exist'){
			$is_mobile = D('FlUser')->where(array('flu_phone'=>$mobile))->find();
			$is_mobile or die(JSON(array('errcode'=>81014,'errmsg'=>'该手机号码未注册用户！')));
		}elseif($verify=='mobile_verify'){
			$this->common();
			$this->userinfo['flu_phone'] or die(JSON(array('errcode'=>81015,'errmsg'=>'请先绑定手机后再操作')));
			$this->userinfo['flu_phone'] == $mobile or die(JSON(array('errcode'=>81016,'errmsg'=>'您输入的手机号码与您绑定的手机号码不一致')));
		}
		$content = \Org\Util\String::randString(4, 1);
		//session('SendSms', $content);
		$result = iconv('GB2312', 'UTF-8', flsendmsg( $mobile, $content));
		//if($result == 'often'){ die(JSON(array('errcode'=>81444,'errmsg'=>'发送频繁，请5分钟后再操作'))); } 
		die($result ? JSON(array('errcode'=>'ok','errmsg'=>$content)):JSON(array('errcode'=>81444,'errmsg'=>'发送失败，短信发送过于频繁')));		
	}

	/***提现帐号设置**/
	public function withdrawSet(){
		$this->common();
		$flu_withdrawzh = trim(I('post.withdrawzh'));
		$flu_withdrawname = trim(I('post.withdrawname'));
		//$flu_withdrawpass =  trim(I('post.withdrawpass'));
		if(!$flu_withdrawzh)die(JSON(array('errcode'=>81017,'errmsg'=>'提现帐号不能为空')));
		if(!$flu_withdrawname)die(JSON(array('errcode'=>81018,'errmsg'=>'提现账户姓名不能为空')));
		//if(!$flu_withdrawpass)die(JSON(array('errcode'=>81019,'errmsg'=>'提现密码不能为空')));
		$result = D('FlUser')->where(array('flu_userid'=>$this->userid))->setField(array('flu_withdrawzh'=>$flu_withdrawzh,'flu_withdrawname'=>$flu_withdrawname));
		die(JSON(array('errcode'=>'ok','errmsg'=>'设置提现帐号成功')));
		//$result?die(JSON(array('errcode'=>'ok','errmsg'=>'设置提现帐号成功'))):die(JSON(array('errcode'=>81020,'errmsg'=>'设置提现帐号失败')));
	}


	/***修改提现密码或者找回提现密码**/
	public function withdrawPassSet(){
		$this->common();
		$oldwithdrawpass = D('FlUser')->passwordmd5(trim(I('post.oldpassword')));
		$newwithdrawpass = trim(I('post.newpassword'));
		$type = I('post.type','set');
		if($type=='edit'){
			if(!$newwithdrawpass)die(JSON(array('errcode'=>81032,'errmsg'=>'请输入新提现密码')));
			$oldwithdrawpass or die(JSON(array('errcode'=>81031,'errmsg'=>'请输入原提现密码')));
			if($this->userinfo['flu_withdrawpass'] != $oldwithdrawpass && $type=='edit')die(JSON(array('errcode'=>81032,'errmsg'=>'原提现密码错误')));
		}
		if(!$newwithdrawpass)die(JSON(array('errcode'=>81032,'errmsg'=>'请输入提现密码')));
		$result = D('FlUser')->where(array('flu_userid'=>$this->userid))->setField('flu_withdrawpass',D('FlUser')->passwordmd5($newwithdrawpass));
		if($type=='edit')
			die(JSON(array('errcode'=>'ok','errmsg'=>'提现密码修改成功')));
		else
			die(JSON(array('errcode'=>'ok','errmsg'=>'提现密码设置成功')));
		//$result?die(JSON(array('errcode'=>'ok','errmsg'=>'提现密码修改成功'))):die(JSON(array('errcode'=>81034,'errmsg'=>'提现密码修改失败')));
	}


	/***收货地址列表**/
	public function receivingList(){
		$this->common();
		$list = array();
		$list = D('FlReceiving')->where(array('flr_userid'=>$this->userid))->order('flr_default desc,flr_receivingid desc')->select();
		die(JSON($list));
	}

	/***收货地址添加**/
	public function receivingAdd(){
		$this->common();
		$data = array();
		$data['flr_userid'] = $this->userid;
		$data['flr_name'] = I('post.name','','trim');
		$data['flr_phone'] = I('post.phone','','trim');
		$data['flr_address'] = I('post.address','','trim');
		$data['flr_maddress'] = I('post.maddress','','trim');
		$result = D('FlReceiving')->add($data);
		if(I('post.default'))D('FlReceiving')->setDefault($this->userid,$result);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'收货地址添加成功'))):die(JSON(array('errcode'=>81030,'errmsg'=>'收货地址添加失败')));
	}

	/***查找收货地址**/
	public function receivingInfo(){
		$receivingid = I('post.receivingid','','trim');
		$receiving = array();
		$receiving = D('FlReceiving')->where(array('flr_receivingid'=>$receivingid,'flr_userid'=>$this->userid))->find();
		die(JSON($receiving));
	}

	/***收货地址修改**/
	public function receivingEdit(){
		$this->common();
		$data = array();
		$receivingid = I('post.receivingid','','trim');
		$data['flr_userid'] = $this->userid;
		$data['flr_name'] = I('post.name','','trim');
		$data['flr_phone'] = I('post.phone','','trim');
		$data['flr_address'] = I('post.address','','trim');
		$data['flr_maddress'] = I('post.maddress','','trim');
		$result = D('FlReceiving')->where(array('flr_receivingid'=>$receivingid,'flr_userid'=>$this->userid))->setField($data);
		if(I('post.default'))D('FlReceiving')->setDefault($this->userid,$receivingid);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'收货地址修改成功'))):die(JSON(array('errcode'=>81030,'errmsg'=>'收货地址修改失败')));
	}

	/***默认收货地址设置**/
	public function receivingDefault(){
		$this->common();
		$receivingid = I('post.receivingid','','trim');
		$result = D('FlReceiving')->setDefault($this->userid,$receivingid);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'默认收货地址设置成功'))):die(JSON(array('errcode'=>81030,'errmsg'=>'默认收货地址设置失败')));
	}
	
	/****申请提现设置****/
	public function withdrawApply(){
		$this->common();
		$money = I('post.money');
		$verify  = I('post.verify','','trim');
		
		$this->userinfo['flu_withdrawzh'] && $this->userinfo['flu_withdrawname'] or die(JSON(array('errcode'=>83001,'errmsg'=>'请先设置提现账户')));
		$money or die(JSON(array('errcode'=>83002,'errmsg'=>'请输入提现金额')));
		$this->userinfo['flu_balance'] >= $money or die(JSON(array('errcode'=>83003,'errmsg'=>'账户余额不足')));
		$this->userinfo['flu_balance'] >= 20 or die(JSON(array('errcode'=>83004,'errmsg'=>'账户余额必须大于20元才能提现')));
		if($verify=='check'){
			die(JSON(array('errcode'=>'ok','errmsg'=>'提现信息检测通过')));
		}
		$withdrawpass =  D('FlUser')->passwordmd5(trim(I('post.withdrawpass')));
		trim(I('post.withdrawpass')) or die(JSON(array('errcode'=>83005,'errmsg'=>'请输入提现密码')));
		$this->userinfo['flu_withdrawpass'] == $withdrawpass or die(JSON(array('errcode'=>83006,'errmsg'=>'您输入的提现密码错误')));

		$Bookkeeping = D('Common/Bookkeeping');
		$data = array();
		$data = $Bookkeeping->calculate($money);
		$data['bmention'] = $money;
		$data['bls'] = $bls = date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
		$data['bmid'] = $this->userid;
		$data['bstime'] = date('Y-m-d H:i:s');
		$data['bip'] = get_client_ip();
		$data['bdzh'] = $this->userinfo['flu_withdrawzh'];
		$data['bname'] = $this->userinfo['flu_withdrawname'];
		$data['bresidue'] = $this->userinfo['flu_balance']-$money;
		$data['butype'] = '1';
		$result = $Bookkeeping->data($data)->add();
		if($result){
			D('FlUser')->where(array('flu_userid'=>$this->userid))->setDec('flu_balance',$money);
		}
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'提现成功'))):die(JSON(array('errcode'=>83010,'errmsg'=>'提现失败')));
	}

	/****提现明细****/
	public function withdrawList(){
		$this->common();
		$Bookkeeping = D('Common/Bookkeeping');
		$pages = I('post.page',1);
		$where = $data = array();
		$where['bmid'] = $this->userid;
		$where['butype'] ='1';
		$page = new \Common\Org\Page($Bookkeeping->where($where)->count(), 100,'','',$pages);
		$list = $Bookkeeping->where($where)->field('')->limit($page->firstRow.','.$page->listRows)->order('bstime desc')->select();
		if($list)foreach($list as $key => $value){
			$value['btypename'] = $Bookkeeping->btypes($value['btype']);
			$data[] = $value;
		}
		die(JSON($data));
	}

	/****提现详情****/	
	public function withdrawInfo(){
		$this->common();
		$bls = I('post.bls');
		$Bookkeeping = D('Common/Bookkeeping');
		$where['bmid'] = $this->userid;
		$where['butype'] = '1';
		$where['bls'] = $bls;
		$data = $Bookkeeping->where($where)->find();
		die(JSON($data));
	}


	/****用户消息统计汇总****/
	public function messageCollect(){
		$this->common();
		$FlMessage = D('FlMessage');
		$list = D('FlMessage')->field('flm_status,count(flm_msid) AS num')->where(array('flm_userid'=>$this->userid))->group('flm_status')->getField('flm_status,num');
		$result?die(JSON($list)):die(JSON(array('errcode'=>84010,'errmsg'=>'操作失败')));
	}

	/****用户消息列表****/
	public function messageList(){
		$this->common();
		$data = array();
		$FlMessage = D('FlMessage');
		$list = $FlMessage->where(array('flm_userid'=>$this->userid,array('flm_status'=>array('in','0,1'))))->order('flm_msid desc')->select();
		if($list)foreach($list as $key => $value){
			$type = $FlMessage->types($value['flm_type']);
			$value['ico'] = $type['ico'];
			$value['typenames'] = $type['name'];
			$data[] = $value;
		}
		die(JSON($data));
	}

	/***用户消息操作***/
	public function messageAct(){
		$this->common();
		$msid = I('post.msid');
		$status = I('post.status','1','intval');
		$FlMessage = D('FlMessage')->where(array('flm_userid'=>$this->userid,'flm_msid'=>$msid))->setField('flm_status',$status);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'操作成功'))):die(JSON(array('errcode'=>84020,'errmsg'=>'操作失败')));
	}

	/***消费返现***/
	public function translation(){
		$this->common();
		$data = array();
		$type = I('post.type');
		$FlTranslation = D('FlTranslation');
		$where = array();
		$where['flt_uid'] = $this->userid;
		if($type=='1'){
			$where['flt_type'] = array('in','0,1,5');
		}elseif($type=='2'){
			$where['flt_type'] =2;
		}elseif($type=='3'){
			$where['flt_type'] = array('in','3,4');
		}
		$list = $FlTranslation->where($where)->field('flt_tid,flt_oid,flt_balance,flt_type,flt_addtime,flt_notes')->order('flt_tid desc')->select();
		if($list)foreach($list as $key => $value){
			$value['typesname'] = $FlTranslation->types($value['flt_type']);
			$data[] = $value;
		}
		die(JSON($data));
	}

	/***创建升级订单***/
	public function createVipOrder(){
		$this->common();
		$vipprice = C('REGISTER_VIP');
		 $viporder = D('FlOrder')->where(array('flo_uid'=>$this->userid,'flo_gtype'=>3))->find();
		 if(!$viporder){
			$data = array();
			$data['flo_id'] = orderNumber();
			$data['flo_uid'] = $this->userid;
			$data['flo_dstime'] = date('Y-m-d H:i:s');
			$data['flo_dstatus'] = 1;
			$data['flo_price'] = $vipprice;
			$data['flo_gtype'] = '3';
			D('FlOrder')->add($data);//创建升级VIP订单
			$viporder = $data;
		 }
		 if($viporder['flo_price']!=$vipprice){
			D('FlOrder')->where(array('flo_uid'=>$this->userid,'flo_gtype'=>3))->setField('flo_price',$vipprice);
			$viporder['flo_price'] = $vipprice;
		 }
		$viporder['title'] = '升级VIP用户';
		$viporder['success_url'] = U('My/vip@flapp',array('action'=>'succeed'));//支付成功返回的url
		$viporder['fail_url'] = U('My/vip@flapp',array('action'=>$linkurl));//支付失败返回的url
		$viporder['notify_url'] = U('Token/alipayNotify@flapp');//此步后面加判断，是使用什么支付，返回的字段
		die(JSON($viporder));
	}

	/**获取订单信息**/
	public function orderinfo(){
		//$this->common();
		$oid = I('post.oid','','trim');
		$pay_type = I('post.pay_type','aliapywap','trim');
		
		if(!$oid)die(JSON(array('errcode'=>81201,'errmsg'=>'订单号不能为空')));
		$orderinfo = D('FlOrder')->field('flo_id,flo_price,flo_sid,flo_gtype')->where(array('flo_id'=>$oid,'flo_pstatus'=>'0'))->find();
		$orderinfo or die(JSON(array('errcode'=>81202,'errmsg'=>'订单不存在')));
		if($orderinfo)$orderinfo['sname'] = D('Shop')->where(array('sid'=>$orderinfo['flo_sid']))->getField('sname');
		//unset($orderinfo['flo_sid']);
		switch ($orderinfo['flo_gtype']){
			case '1':
			 	$orderinfo['title'] = $orderinfo['sname'].'-在线下单';
				$linkurl = url_param_encrypt(U('Shop/shopInfo@flapp',array('sid'=>$orderinfo['flo_sid'])),'E');
			  break;  
			case '2':
				$linkurl = url_param_encrypt(U('Shop/shopInfo@flapp',array('sid'=>$orderinfo['flo_sid'])),'E');
				$orderinfo['title'] = $orderinfo['sname'].'-在线预约';
			  break;
			case '3': 
				$linkurl = url_param_encrypt(U('Recharge/calls@flapp'),'E');
				$orderinfo['title'] = '升级VIP';
			  break;
			case '4':
				$linkurl = url_param_encrypt(U('Recharge/calls@flapp'),'E');
				$orderinfo['title'] = '话费充值';
			  break;
			case '5':
				$linkurl = url_param_encrypt(U('Recharge/calls@flapp'),'E');
				$orderinfo['title'] = '流量充值';
			  break;
			default:
				$linkurl = url_param_encrypt(U('Recharge/calls@flapp'),'E');
				$orderinfo['title'] = '在线支付';
		}
		if(!$orderinfo['sname'])$orderinfo['sname'] = $orderinfo['title'];
		if($pay_type == 'weixin')
		$orderinfo['notify_url'] = U('Token/weixinNotify@flapp');//此步后面加判断，是使用什么支付，返回的字段
		else
		$orderinfo['notify_url'] = U('Token/alipayNotify@flapp');//此步后面加判断，是使用什么支付，返回的字段

		$orderinfo['success_url'] = U('My/order@flapp',array('returnurl'=>$linkurl));//支付成功返回的url
		$orderinfo['fail_url'] = U('My/order@flapp',array('returnurl'=>$linkurl));//支付失败返回的url
		$orderinfo?die(JSON($orderinfo)):die(JSON(array('errcode'=>81202,'errmsg'=>'订单号不存在订单!')));
	}

	/****支付成功返回的数据****/
	public function paysuccess(){
		$this->common();
		$oid = I('post.oid','','trim');
		$pay_trade_no = I('post.pay_trade_no');
		$pway = I('post.pway');
		if(!$oid)die(JSON(array('errcode'=>81211,'errmsg'=>'订单号不能为空')));
		
		
		$orderinfo = D('FlOrder')->where(array('flo_id'=>$oid))->find();
		if($orderinfo['flo_pstatus'] < 1){
			$ainfo['flo_pway'] = $pway;
			$ainfo['flo_pstime'] = date('Y-m-d H:i:s');
			$ainfo['flo_pstatus'] = '1';
			$ainfo['flo_dstatus'] = '3';
			D('FlOrder')->where(array('flo_uid'=>$orderinfo['flo_uid'],'flo_id'=>$oid,'flo_pstatus'=>'0'))->setField($ainfo);
			if($orderinfo['flo_gtype']==3){
				D('FlUser')->where(array('flu_userid'=>$orderinfo['flo_uid']))->setField('flu_usertype','1');//升级订单改变会员状态
			}
			if($pay_trade_no){
				/***记录支付日志***/
				$paylog = array();
				$paylog['pay_uid'] = $orderinfo['flo_uid'];
				$paylog['pay_type'] = $orderinfo['flo_gtype'];//1代表购买支付
				$paylog['pay_price'] = $orderinfo['flo_price'];
				$paylog['pay_time'] = date('Y-m-d H:i:s');
				$paylog['pay_oid'] = $oid;
				$paylog['pay_trade_no'] = $pay_trade_no;
				$paylog['pay_way'] = $pway;
				M('fl_paylog')->data($paylog)->add();
				//$mission = new \Common\Org\Commission;
				//$mission->insertInfo($oid);
			}

			die(JSON(array('errcode'=>'ok', 'errmsg'=>'您的订单支付成功')));
		}elseif($orderinfo['flo_pstatus'] == 1){
			die(JSON(array('errcode'=>'ok', 'errmsg'=>'订单支付成功')));
		}
		die(JSON(array('errcode'=>81204,'errmsg'=>'订单支付错误，请联系客服处理!')));
	}



   //上传用户图像
   public function uploadUserImg() {
		$this->common();
		$uploadROOT = realpath(THINK_PATH.'../Public/');//上传地址的根目录
		$uploadSubPath = '/Upload/flapp/'.date('Ym/');//上传地址的子目录
		$subName = array('date','d');
		$uploadPath =$uploadROOT.$uploadSubPath;
        if(!file_exists($uploadPath)) mkdirs($uploadPath, 0775);
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'subName'	=> $subName,
			'exts'		=> 'jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF',
			'maxSize'	=> 2048000
		);
		$attachment = new \Think\Upload( $uploadConfig );
		if(!$_FILES['imgFile'])die(JSON(array('errcode'=>'90000', 'errmsg'=>'图片流未提交')));
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        if($attachmentInfo && is_array($attachmentInfo)) {
			$imgpath = U('@www').'/Public'.$uploadSubPath.($subName?date('d').'/':'').$attachmentInfo['savename'];
			D('FlUser')->where(array('flu_userid'=>$this->userid))->setField('flu_avatar',$imgpath);
            die(JSON(array('errcode'=>'ok', 'errmsg'=>$imgpath, 'savename'=>basename($attachmentInfo['savename']))));
        } else {
            die(JSON(array('errcode'=>'90000', 'errmsg'=>$attachment->getError())));
        }
    }

}


