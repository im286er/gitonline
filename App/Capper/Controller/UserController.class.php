<?php
namespace Capper\Controller;
use Think\Controller;
/* * *我的 * * */
class UserController extends CapperappController {
	public $userid;
	public $userinfo;


	/***发送验证码***/
	public function sendsms() {
		$mobile = I('post.mobile','','trim');
		$verify  = I('post.verify','','trim');
		if( !$mobile ) die(JSON(array('errcode'=>81443,'errmsg'=>'请输入发送的手机号码')));
		
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[012356789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $mobile) ) {
			die( JSON( array('errcode'=>81011, 'errmsg'=>'手机号格式不正确') ) );
		}
		
		if($verify=='user_unique'){
			$is_mobile = D('TrUser')->where(array('u_phone'=>$mobile))->find();
			!$is_mobile or die(JSON(array('errcode'=>81013,'errmsg'=>'您要绑定的手机号码已存在！')));
		}elseif($verify=='user_exist'){
			$is_mobile = D('TrUser')->where(array('u_phone'=>$mobile))->find();
			$is_mobile or die(JSON(array('errcode'=>81014,'errmsg'=>'该手机号码未注册用户！')));
		}elseif($verify=='mobile_verify'){
			$this->common();
			//$this->userinfo['u_phone'] or die(JSON(array('errcode'=>81015,'errmsg'=>'请先绑定手机后再操作')));
			//$this->userinfo['u_phone'] == $mobile or die(JSON(array('errcode'=>81016,'errmsg'=>'您输入的手机号码与您绑定的手机号码不一致')));
		}
		$content = \Org\Util\String::randString(4, 1);
		//session('SendSms', $content);
		die(sendmsg( $mobile, $content,'【托儿】') ? JSON(array('errcode'=>'ok','errmsg'=>$content)):JSON(array('errcode'=>81444,'errmsg'=>'短信发送失败')));		
	}



	/**登录授权**/
	public function login(){
		$mobile = I('post.mobile');
		$ucode = I('post.ucode');
		$mobile or die(JSON(array('errcode'=>80101,'errmsg'=>'请填写您的手机号码')));
		$result = D('TrUser')->login($mobile,$code);
		if(is_numeric($result)==false)die(JSON($result));
		$data = D('TrUsertoken')->sign_utoken($this->appid,$result);
		die(JSON($data));
	}
	

	/**续签协议授权**/
	public function renewal(){
		$utoken = I('post.utoken');
		$ucode = I('post.ucode')?I('post.ucode'):randpw(4,'CHAR');
		$utoken or die(JSON(array('errcode'=>80005,'errmsg'=>'utoken未提交！')));
		$ucode or die(JSON(array('errcode'=>80006,'errmsg'=>'ucode未提交！')));
		$data = D('TrUsertoken')->renew_utoken($this->appid,$utoken);
		die(JSON($data));
	}
	
	/****第三方登录的第一步***/
	public function oauthlogin(){
		$openid = trim(I('post.openid'));
		$source = trim(I('post.source'));
		$openid or die(JSON(array('errcode'=>80201,'errmsg'=>'无第三方登录openid')));
		$source or die(JSON(array('errcode'=>80202,'errmsg'=>'无第三方登录来源')));
		$oauth = D('TrUoauth')->check_oauth($source,$openid);
		if($oauth['userid']){
			$data = D('TrUsertoken')->sign_utoken($this->appid,$oauth['userid']);//已经绑定过手机号码可以直接登录
			die(JSON($data));
		}else{
			die(JSON(array('result'=>'ok','dispose'=>'oauthreg')));//说明要进行手机号验证才能登录
		}
	}

	/**第三方快捷登录的第二步**/
	public function oauthreg(){
		$mobile = trim(I('post.mobile'));
		$openid = trim(I('post.openid'));
		$source = trim(I('post.source'));
		$avatar = trim(I('post.avatar'));
		$nickname = trim(I('post.nickname'));
		$mobile or die(JSON(array('errcode'=>80201,'errmsg'=>'手机号不能为空')));
		$openid or die(JSON(array('errcode'=>80201,'errmsg'=>'无第三方登录openid')));
		$source or die(JSON(array('errcode'=>80202,'errmsg'=>'无第三方登录来源')));
		$result = D('TrUser')->oauthlogin($mobile,$openid, $source, $avatar,$nickname,$this->appid);
		if(is_numeric($result)==false)die(JSON($result));
		$data = D('TrUsertoken')->sign_utoken($this->appid,$result);
		die(JSON($data));
	}
	
	/**用户退出登录**/
	public function loginout(){
		$this->common();
		$utoken = I('post.utoken');
		$utoken or die(JSON(array('errcode'=>80005,'errmsg'=>'utoken未提交！')));
		$result = D('TrUser')->login_out($this->appid,I('post.utoken'));
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'用户退出成功'))):die(JSON(array('errcode'=>80144,'errmsg'=>'用户退出失败')));
	}


	/***检测utoken是否通过***/
	public function checkUtoken(){
		$result = D('TrUsertoken')->renew_utoken(I('post.utoken'));
		if($result['errcode']>0)die(JSON($result));
		return $result;
	}

	/***获取用户基本信息**/
	public function common(){
		$result = D('TrUsertoken')->checkUtoken(I('post.utoken'));
		if(is_array($result))die(JSON($result));
		/**验证通过写获取方法**/
		$this->userid = $result;
		$this->userinfo = D('TeUser')->userinfo($result);
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
		$username or exit(JSON(array('errcode'=>81011,'errmsg'=>'姓名不能为空')));
		$result = D('TrUser')->where(array('u_userid'=>$this->userid))->setField('u_username',$username);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'修改姓名成功'))):die(JSON(array('errcode'=>81005,'errmsg'=>'修改姓名失败')));
	}

	/***绑定手机号***/
	public function setMobile(){
		$this->common();
		$mobile = I('post.mobile','','trim');
		$mobile or die( JSON( array('errcode'=>81010, 'errmsg'=>'请输入手机号码') ) );
		$checkmobile = D('TrUser')->check_mobile($mobile);
		$checkmobile or die(JSON(array('errcode'=>81011,'errmsg'=>'手机号码输入错误！')));
		$is_mobile = D('TrUser')->where(array('u_phone'=>$mobile))->find();
		!$is_mobile or die(JSON(array('errcode'=>81013,'errmsg'=>'您要绑定的手机号码已存在！')));
		$data = array();
		$data['u_phone'] = $mobile;
		$result = D('TrUser')->where(array('u_userid'=>$this->userid))->setField($data);
		if(!$this->userinfo['u_phone']){
			//D('TrInvite')->confirmMobileInvite($this->userid,$mobile);
		}
		die(JSON(array('errcode'=>'ok','errmsg'=>'手机号码绑定成功')));
		//$result?die(JSON(array('errcode'=>'ok','errmsg'=>'手机号码绑定成功'))):die(JSON(array('errcode'=>81005,'errmsg'=>'手机号码绑定失败')));
	}



	/***提现帐号设置**/
	public function withdrawSet(){
		$this->common();
		$u_withdrawzh = trim(I('post.withdrawzh'));
		$u_withdrawname = trim(I('post.withdrawname'));
		//$u_withdrawpass =  trim(I('post.withdrawpass'));
		if(!$u_withdrawzh)die(JSON(array('errcode'=>81017,'errmsg'=>'提现帐号不能为空')));
		if(!$u_withdrawname)die(JSON(array('errcode'=>81018,'errmsg'=>'提现账户姓名不能为空')));
		//if(!$u_withdrawpass)die(JSON(array('errcode'=>81019,'errmsg'=>'提现密码不能为空')));
		$result = D('TrUser')->where(array('u_userid'=>$this->userid))->setField(array('u_withdrawzh'=>$u_withdrawzh,'u_withdrawname'=>$u_withdrawname));
		die(JSON(array('errcode'=>'ok','errmsg'=>'设置提现帐号成功')));
		//$result?die(JSON(array('errcode'=>'ok','errmsg'=>'设置提现帐号成功'))):die(JSON(array('errcode'=>81020,'errmsg'=>'设置提现帐号失败')));
	}


	/***修改提现密码或者找回提现密码**/
	public function withdrawPassSet(){
		$this->common();
		$oldwithdrawpass = D('TrUser')->passwordmd5(trim(I('post.oldpassword')));
		$newwithdrawpass = trim(I('post.newpassword'));
		$type = I('post.type','set');
		if($type=='edit'){
			if(!$newwithdrawpass)die(JSON(array('errcode'=>81032,'errmsg'=>'请输入新提现密码')));
			$oldwithdrawpass or die(JSON(array('errcode'=>81031,'errmsg'=>'请输入原提现密码')));
			if($this->userinfo['u_withdrawpass'] != $oldwithdrawpass && $type=='edit')die(JSON(array('errcode'=>81032,'errmsg'=>'原提现密码错误')));
		}
		if(!$newwithdrawpass)die(JSON(array('errcode'=>81032,'errmsg'=>'请输入提现密码')));
		$result = D('TrUser')->where(array('u_userid'=>$this->userid))->setField('u_withdrawpass',D('TrUser')->passwordmd5($newwithdrawpass));
		if($type=='edit')
			die(JSON(array('errcode'=>'ok','errmsg'=>'提现密码修改成功')));
		else
			die(JSON(array('errcode'=>'ok','errmsg'=>'提现密码设置成功')));
		//$result?die(JSON(array('errcode'=>'ok','errmsg'=>'提现密码修改成功'))):die(JSON(array('errcode'=>81034,'errmsg'=>'提现密码修改失败')));
	}

	/*
	/***收货地址列表**/
	public function receivingList(){
		$this->common();
		$list = array();
		$list = D('TrReceiving')->where(array('r_userid'=>$this->userid))->order('r_default desc,r_receivingid desc')->select();
		die(JSON($list));
	}

	/***收货地址添加**/
	public function receivingAdd(){
		$this->common();
		$data = array();
		$data['r_userid'] = $this->userid;
		$data['r_name'] = I('post.name','','trim');
		$data['r_phone'] = I('post.phone','','trim');
		$data['r_address'] = I('post.address','','trim');
		$data['r_maddress'] = I('post.maddress','','trim');
		$result = D('TrReceiving')->add($data);
		if(I('post.default'))D('TrReceiving')->setDefault($this->userid,$result);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'收货地址添加成功'))):die(JSON(array('errcode'=>81030,'errmsg'=>'收货地址添加失败')));
	}

	/***查找收货地址**/
	public function receivingInfo(){
		$receivingid = I('post.receivingid','','trim');
		$receiving = array();
		$receiving = D('TrReceiving')->where(array('r_receivingid'=>$receivingid,'r_userid'=>$this->userid))->find();
		die(JSON($receiving));
	}

	/***收货地址修改**/
	public function receivingEdit(){
		$this->common();
		$data = array();
		$receivingid = I('post.receivingid','','trim');
		$data['r_userid'] = $this->userid;
		$data['r_name'] = I('post.name','','trim');
		$data['r_phone'] = I('post.phone','','trim');
		$data['r_address'] = I('post.address','','trim');
		$data['r_maddress'] = I('post.maddress','','trim');
		$result = D('TrReceiving')->where(array('r_receivingid'=>$receivingid,'r_userid'=>$this->userid))->setField($data);
		if(I('post.default'))D('TrReceiving')->setDefault($this->userid,$receivingid);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'收货地址修改成功'))):die(JSON(array('errcode'=>81030,'errmsg'=>'收货地址修改失败')));
	}

	/***默认收货地址设置**/
	public function receivingDefault(){
		$this->common();
		$receivingid = I('post.receivingid','','trim');
		$result = D('TrReceiving')->setDefault($this->userid,$receivingid);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'默认收货地址设置成功'))):die(JSON(array('errcode'=>81030,'errmsg'=>'默认收货地址设置失败')));
	}
	*/
	/****申请提现设置****/
	public function withdrawApply(){
		$this->common();
		$money = I('post.money');
		$verify  = I('post.verify','','trim');
		
		$this->userinfo['u_withdrawzh'] && $this->userinfo['u_withdrawname'] or die(JSON(array('errcode'=>83001,'errmsg'=>'请先设置提现账户')));
		$money or die(JSON(array('errcode'=>83002,'errmsg'=>'请输入提现金额')));
		$this->userinfo['u_balance'] >= $money or die(JSON(array('errcode'=>83003,'errmsg'=>'账户余额不足')));
		$this->userinfo['u_balance'] >= 20 or die(JSON(array('errcode'=>83004,'errmsg'=>'账户余额必须大于20元才能提现')));
		if($verify=='check'){
			die(JSON(array('errcode'=>'ok','errmsg'=>'提现信息检测通过')));
		}
		$withdrawpass =  D('TrUser')->passwordmd5(trim(I('post.withdrawpass')));
		trim(I('post.withdrawpass')) or die(JSON(array('errcode'=>83005,'errmsg'=>'请输入提现密码')));
		$this->userinfo['u_withdrawpass'] == $withdrawpass or die(JSON(array('errcode'=>83006,'errmsg'=>'您输入的提现密码错误')));

		$Bookkeeping = D('System/Bookkeeping');
		$data = array();
		$data = $Bookkeeping->calculate($money);
		$data['bmention'] = $money;
		$data['bls'] = $bls = date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
		$data['bmid'] = $this->userid;
		$data['bstime'] = date('Y-m-d H:i:s');
		$data['bip'] = get_client_ip();
		$data['bdzh'] = $this->userinfo['u_withdrawzh'];
		$data['bname'] = $this->userinfo['u_withdrawname'];
		$data['bresidue'] = $this->userinfo['u_balance']-$money;
		$data['butype'] = '1';
		$result = $Bookkeeping->data($data)->add();
		if($result){
			D('TrUser')->where(array('u_userid'=>$this->userid))->setDec('u_balance',$money);
		}
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'提现成功'))):die(JSON(array('errcode'=>83010,'errmsg'=>'提现失败')));
	}

	/****提现明细****/
	public function withdrawList(){
		$this->common();
		$Bookkeeping = D('System/Bookkeeping');
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
		$Bookkeeping = D('System/Bookkeeping');
		$where['bmid'] = $this->userid;
		$where['butype'] = '1';
		$where['bls'] = $bls;
		$data = $Bookkeeping->where($where)->find();
		die(JSON($data));
	}


	/****用户消息统计汇总****/
	public function messageCollect(){
		$this->common();
		$TrMessage = D('TrMessage');
		$list = D('TrMessage')->field('m_status,count(m_msid) AS num')->where(array('m_userid'=>$this->userid))->group('m_status')->getField('m_status,num');
		$result?die(JSON($list)):die(JSON(array('errcode'=>84010,'errmsg'=>'操作失败')));
	}

	/****用户消息列表****/
	public function messageList(){
		$this->common();
		$data = array();
		$TrMessage = D('TrMessage');
		$list = $TrMessage->where(array('m_userid'=>$this->userid,array('m_status'=>array('in','0,1'))))->order('m_msid desc')->select();
		if($list)foreach($list as $key => $value){
			$type = $TrMessage->types($value['m_type']);
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
		$TrMessage = D('TrMessage')->where(array('m_userid'=>$this->userid,'m_msid'=>$msid))->setField('m_status',$status);
		$result?die(JSON(array('errcode'=>'ok','errmsg'=>'操作成功'))):die(JSON(array('errcode'=>84020,'errmsg'=>'操作失败')));
	}

	/***消费返现***/
	public function translation(){
		$this->common();
		$data = array();
		$type = I('post.type');
		$TrTranslation = D('TrTranslation');
		$where = array();
		$where['t_uid'] = $this->userid;
		if($type=='1'){
			$where['t_type'] = array('in','0,1,5');
		}elseif($type=='2'){
			$where['t_type'] =2;
		}elseif($type=='3'){
			$where['t_type'] = array('in','3,4');
		}
		$list = $TrTranslation->where($where)->field('t_tid,t_oid,t_balance,t_type,t_addtime,t_notes')->order('t_tid desc')->select();
		if($list)foreach($list as $key => $value){
			$value['typesname'] = $TrTranslation->types($value['t_type']);
			$data[] = $value;
		}
		die(JSON($data));
	}

	/***创建升级订单***/
	public function createVipOrder(){
		$this->common();
		$vipprice = C('REGISTER_VIP');
		 $viporder = D('TrOrder')->where(array('o_uid'=>$this->userid,'o_gtype'=>3))->find();
		 if(!$viporder){
			$data = array();
			$data['o_id'] = orderNumber();
			$data['o_uid'] = $this->userid;
			$data['o_dstime'] = date('Y-m-d H:i:s');
			$data['o_dstatus'] = 1;
			$data['o_price'] = $vipprice;
			$data['o_gtype'] = '3';
			D('TrOrder')->add($data);//创建升级VIP订单
			$viporder = $data;
		 }
		 if($viporder['o_price']!=$vipprice){
			D('TrOrder')->where(array('o_uid'=>$this->userid,'o_gtype'=>3))->setField('o_price',$vipprice);
			$viporder['o_price'] = $vipprice;
		 }
		$viporder['title'] = '升级VIP用户';
		$viporder['success_url'] = U('My/vip@tr',array('action'=>'succeed'));//支付成功返回的url
		$viporder['fail_url'] = U('My/vip@tr',array('action'=>$linkurl));//支付失败返回的url
		$viporder['notify_url'] = U('Token/alipayNotify@tr');//此步后面加判断，是使用什么支付，返回的字段
		die(JSON($viporder));
	}

	/**获取订单信息**/
	public function orderinfo(){
		//$this->common();
		$oid = I('post.oid','','trim');
		if(!$oid)die(JSON(array('errcode'=>81201,'errmsg'=>'订单号不能为空')));
		$orderinfo = D('TrOrder')->field('o_id,o_price,o_sid,o_gtype')->where(array('o_id'=>$oid,'o_pstatus'=>'0'))->find();
		$orderinfo or die(JSON(array('errcode'=>81202,'errmsg'=>'订单不存在')));
		if($orderinfo)$orderinfo['sname'] = D('Shop')->where(array('sid'=>$orderinfo['o_sid']))->getField('sname');
		//unset($orderinfo['o_sid']);
		switch ($orderinfo['o_gtype']){
			case '1':
			 	$orderinfo['title'] = $orderinfo['sname'].'-在线下单';
				$linkurl = url_param_encrypt(U('Shop/shopInfo@tr',array('sid'=>$orderinfo['o_sid'])),'E');
			  break;  
			case '2':
				$linkurl = url_param_encrypt(U('Shop/shopInfo@tr',array('sid'=>$orderinfo['o_sid'])),'E');
				$orderinfo['title'] = $orderinfo['sname'].'-在线预约';
			  break;
			case '3': 
				$linkurl = url_param_encrypt(U('Recharge/calls@tr'),'E');
				$orderinfo['title'] = '升级VIP';
			  break;
			case '4':
				$linkurl = url_param_encrypt(U('Recharge/calls@tr'),'E');
				$orderinfo['title'] = '话费充值';
			  break;
			case '5':
				$linkurl = url_param_encrypt(U('Recharge/calls@tr'),'E');
				$orderinfo['title'] = '流量充值';
			  break;
			default:
				$linkurl = url_param_encrypt(U('Recharge/calls@tr'),'E');
				$orderinfo['title'] = '在线支付';
		}
		if(!$orderinfo['sname'])$orderinfo['sname'] = $orderinfo['title'];
		$orderinfo['notify_url'] = U('Token/alipayNotify@tr');//此步后面加判断，是使用什么支付，返回的字段
		$orderinfo['success_url'] = U('My/order@tr',array('returnurl'=>$linkurl));//支付成功返回的url
		$orderinfo['fail_url'] = U('My/order@tr',array('returnurl'=>$linkurl));//支付失败返回的url
		$orderinfo?die(JSON($orderinfo)):die(JSON(array('errcode'=>81202,'errmsg'=>'订单号不存在订单!')));
	}

	/****支付成功返回的数据****/
	public function paysuccess(){
		$this->common();
		$oid = I('post.oid','','trim');
		$pay_trade_no = I('post.pay_trade_no');
		$pway = I('post.pway');
		if(!$oid)die(JSON(array('errcode'=>81211,'errmsg'=>'订单号不能为空')));
		
		
		$orderinfo = D('TrOrder')->where(array('o_id'=>$oid))->find();
		if($orderinfo['o_pstatus'] < 1){
			$ainfo['o_pway'] = $pway;
			$ainfo['o_pstime'] = date('Y-m-d H:i:s');
			$ainfo['o_pstatus'] = '1';
			$ainfo['o_dstatus'] = '3';
			D('TrOrder')->where(array('o_uid'=>$orderinfo['o_uid'],'o_id'=>$oid,'o_pstatus'=>'0'))->setField($ainfo);
			if($orderinfo['o_gtype']==3){
				D('TrUser')->where(array('u_userid'=>$orderinfo['o_uid']))->setField('u_usertype','1');//升级订单改变会员状态
			}
			if($pay_trade_no){
				/***记录支付日志***/
				$paylog = array();
				$paylog['pay_uid'] = $orderinfo['o_uid'];
				$paylog['pay_type'] = $orderinfo['o_gtype'];//1代表购买支付
				$paylog['pay_price'] = $orderinfo['o_price'];
				$paylog['pay_time'] = date('Y-m-d H:i:s');
				$paylog['pay_oid'] = $oid;
				$paylog['pay_trade_no'] = $pay_trade_no;
				$paylog['pay_way'] = $pway;
				M('Tr_paylog')->data($paylog)->add();
				//$mission = new \Common\Org\Commission;
				//$mission->insertInfo($oid);
			}

			die(JSON(array('errcode'=>'ok', 'errmsg'=>'您的订单支付成功')));
		}elseif($orderinfo['o_pstatus'] == 1){
			die(JSON(array('errcode'=>'ok', 'errmsg'=>'订单支付成功')));
		}
		die(JSON(array('errcode'=>81204,'errmsg'=>'订单支付错误，请联系客服处理!')));
	}



	//添加收货地址
	public function addressAdd(){
		$this->userid();
		$name     = I('post.name');
		$phone    = I('post.phone');
		$address  = I('post.address');
		$maddress = I('post.maddress');
		$zipcode  = I('post.zipcode');
		$defaults  = I('post.defaults',0);
		
		if(empty($name) || empty($phone) || empty($address) || empty($maddress)){
			$result = array("errcode" => '81301',"errmsg"  => '信息不完整',"events"  => array(),);
			die(JSON($result));
		}
		if($defaults == 1){
			M('TrReceiving')->where(array('userid'=>$this->userid))->save(array('defaults'=>0));
		}
		$opt = array('userid' => $this->userid,'name' => $name,'phone' => $phone,'address' => $address,'maddress' => $maddress,'zipcode' =>$zipcode,'defaults' => $defaults);
		$r = D('TrReceiving')->Address_add($opt);
		if($r){
			$result = array("errcode" => 'ok',"errmsg" => '操作成功',"events" => array(),);
			die(JSON($result));
		}else{
			$result = array("errcode" => '81300',"errmsg"  => '操作失败',"events"  => array(),);
			die(JSON($result));
		}
	}
	//编辑收货地址
	public function addressEdit(){
		$this->common();
		$receivingid = I('post.receivingid');
		$name = I('post.name');
		$phone = I('post.phone');
		$address = I('post.address');
		$maddress = I('post.maddress');
		$zipcode  = I('post.zipcode');
		$defaults  = I('post.defaults',0);
		
		if(empty($receivingid) || empty($name) || empty($phone) || empty($address) || empty($maddress)){
			$result = array("errcode" => '81301',"errmsg"  => '信息不完整',"events"  => array(),);
			die(JSON($result));
		}
		if($defaults == 1){
			M('receiving')->where(array('userid'=>$this->userid))->save(array('defaults'=>0));
		}
		
		$opt = array('name' => $name,'phone' => $phone,'address' => $address,'maddress' => $maddress,'zipcode' =>$zipcode,'defaults' => $defaults);
		$r = D('TrReceiving')->Address_edit($this->userid,$receivingid,$opt);
		$r = true;
		if($r){
			$result = array("errcode" => 'ok',"errmsg" => '操作成功',"events" => array(),);
			die(JSON($result));
		}else{
			$result = array("errcode" => '81300',"errmsg"  => '操作失败',"events"  => array(),);
			die(JSON($result));
		}
	}
	//收货地址列表
	public function addressList(){
		$this->common();
		$r = D('TrReceiving')->getAddressList($this->userid);
		if($r){
			$result = array("errcode" => 'ok',"errmsg" => '操作成功',"events" => $r);
			die(JSON($result));
		}else{
			$result = array("errcode" => '81302',"errmsg"  => '没有查询到数据',"events"  => array(),);
			die(JSON($result));
		}
	}
	//收货地址详细信息
	public function addressInfo(){
		$this->common();
		$receivingid     = I('receivingid');		
		$r = D('TrReceiving')->getAddressInfo($receivingid);
		if($r){
			$result = array("errcode" => 'ok',"errmsg" => '操作成功',"events"  => $r);
			die(JSON($result));
		}else{
			$result = array("errcode" => '81302',"errmsg" => '没有查询到数据',"events" => array(),);
			die(JSON($result));
		}
	}
	//删除收货地址
	public function addressDel(){
		$this->common();
		$receivingid     = I('receivingid');
		$r = D('TrReceiving')->Address_del($this->userid,$receivingid);
		if($r){
			$result = array("errcode" => 'ok',"errmsg"  => '操作成功',"events"  => array(),);
			die(JSON($result));
		}else{
			$result = array("errcode" => '81300',"errmsg"  => '操作失败',"events"  => array(),);
			die(JSON($result));
		}
	}

   //上传用户图像
   public function uploadUserImg() {
		$this->common();
		$uploadROOT = realpath(THINK_PATH.'../Public/');//上传地址的根目录
		$uploadSubPath = '/Upload/trapp/'.date('Ym/');//上传地址的子目录
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
			$imgpath = U('@ho').'/Public'.$uploadSubPath.($subName?date('d').'/':'').$attachmentInfo['savename'];
			D('TrUser')->where(array('u_userid'=>$this->userid))->setField('u_avatar',$imgpath);
            die(JSON(array('errcode'=>'ok', 'errmsg'=>$imgpath, 'savename'=>basename($attachmentInfo['savename']))));
        } else {
            die(JSON(array('errcode'=>'90000', 'errmsg'=>$attachment->getError())));
        }
    }

}


