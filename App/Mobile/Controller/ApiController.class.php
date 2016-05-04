<?php
namespace Mobile\Controller;
use Think\Controller;
/***活动控制器***/
class ApiController extends Controller {

	public $userid,$userinfo,$appid,$access_token,$tokenid;

	/*** 获取token ***/
	public function get_token(){
		$appid = I('get.appid');
		$secret = I('get.secret');
		$result = D('Rebateapp/FlApptoken')->getToken($appid,$secret);
		die(JSON($result));
	}
	public function DeleteHtml($str) { 
		$str = trim($str); //清除字符串两边的空格
		$str = preg_replace("/\t/","",$str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
		$str = preg_replace("/\r\n/","",$str); 
		$str = preg_replace("/\r/","",$str); 
		$str = preg_replace("/\n/","",$str); 
		$str = preg_replace("/ /","",$str);
		$str = preg_replace("/  /","",$str);  //匹配html中的空格
		return trim($str); //返回字符串
	}

	public function wxShare($jid=71){
		$merchant = M("merchant")->field('jid,mnickname,mabbreviation')->where(array('jid'=>$jid))->find();
		$merchantapp = M("merchantApp")->field('jid,applogo,appjs')->where(array('jid'=>$jid))->find();
		$wxShare = array(
					'imgUrl' => U('@www').$merchantapp['applogo'],
					'link' =>  U('Index/index@yd',array('jid'=>$jid,'v'=>time())),
					'desc' => $this->DeleteHtml(strip_tags($merchantapp['appjs'])),
					'title' => $merchant['mabbreviation'],
					);
		return $wxShare;
	}

	/*添加百川用户*/
	public function bcUserAdd(){
		$data = array();
		$data[8]['userid']='dishuuser17';
		$data[8]['password']='123456';
		$baichuan = D('Baichuan')->usersAdd($data);
		if($baichuan['uid_succ']['string']){
			print_r($baichuan['uid_succ']['string']);
			return $baichuan['uid_succ']['string'];
		}elseif($baichuan['code']){
			die('code:'.$baichuan['code']."\t\t\t msg:".$baichuan['msg']."\t\t\t sub_msg:".$baichuan['sub_msg']);
		}
		return false;
	}


	/*修改百川用户*/
	public function bcUsersGet(){
		$baichuan = D('Baichuan')->usersGet(array('baichuan1'));
		print_r($baichuan);
		if($baichuan['userinfos']['userinfos']){
			return $baichuan['userinfos']['userinfos'];
		}elseif($baichuan['code']){
			die('code:'.$baichuan['code']."\t\t\t msg:".$baichuan['msg']."\t\t\t sub_msg:".$baichuan['sub_msg']);
		}
		return false;
	}
	/*更新百川用户*/
	public function bcUserUpdate(){
		$data = array();
		$data[8]['userid']='taobao28';
		$data[8]['password']='123456';
		$data[8]['email']='taobao28@fl0.cn';
		$data[8]['mobile']='15868476075';
		$data[8]['taobaoid']='45456ll';
		$data[10]['email']='taobao28@fl.cn';
		$data[10]['userid']='taobao29';
		$data[10]['password']='123456';
		$baichuan = D('Baichuan')->usersUpdate($data);
		if($baichuan['uid_succ']['string']){
			print_r($baichuan['uid_succ']['string']);
			return $baichuan['uid_succ']['string'];
		}elseif($baichuan['code']){
			die('code:'.$baichuan['code']."\t\t\t msg:".$baichuan['msg']."\t\t\t sub_msg:".$baichuan['sub_msg']);
		}
		return false;
	}

	/*删除百川用户*/
	public function bcUsersDelete(){
		$baichuan = D('Baichuan')->usersDelete('taobao27');
		if($baichuan['result']['string']){
			print_r($baichuan['result']['string']);
			return $baichuan['result']['string'];
		}elseif($baichuan['code']){
			die('code:'.$baichuan['code']."\t\t\t msg:".$baichuan['msg']."\t\t\t sub_msg:".$baichuan['sub_msg']);
		}
		return false;
	}

	/*** 百川消息推送 ***/
	public function bcMsgPush(){
		$data = array();
		$data['from_user'] = I('post.from_user');
		$data['to_users'] = I('post.to_users');//可以是数组或者单个用户名
		$data['summary'] = I('post.summary');
		$data['from_user'] or die(JSON(array('errcode'=>80751,'errmsg'=>'无发送方userid')));
		$data['to_users'] or die(JSON(array('errcode'=>80752,'errmsg'=>'无接受方userid')));
		$data['summary'] or die(JSON(array('errcode'=>80753,'errmsg'=>'无推送内容')));
		$data['apns_param'] = I('post.apns_param');
		$data['aps'] = I('post.aps');
		$data['data'] = I('post.data');
		$baichuan = D('Baichuan')->custmsgPush($data);
		if($baichuan['msgid']){
			die(JSON(array('errcode'=>'ok','errmsg'=>$baichuan['msgid'])));
		}elseif($baichuan['code']){
			die(JSON(array('errcode'=>$baichuan['code'],'errmsg'=>$baichuan['msg'],'sub_msg'=>$baichuan['sub_msg'])));
		}
		return false;
	}


	/*** 查询聊天记录 ***/
	public function chatLogs(){
		$data = array();
		I('post.user1') or die(JSON(array('errcode'=>80761,'errmsg'=>'请输入用户1')));
		I('post.user2') or die(JSON(array('errcode'=>80762,'errmsg'=>'请输入用户2')));
		I('post.begin') or die(JSON(array('errcode'=>80763,'errmsg'=>'请选择起始时间')));
		I('post.end') or die(JSON(array('errcode'=>80764,'errmsg'=>'请选择终止时间')));
		$data['user1'] = array('uid' => I('post.user1'),'taobao_account'=>false);
		$data['user2'] = array('uid' => I('post.user2'),'taobao_account'=>false);
		$data['begin'] = is_numeric(I('post.begin'))?I('post.begin'):strtotime(I('post.begin'));
		$data['end'] = is_numeric(I('post.end'))?I('post.end'):strtotime(I('post.end'));
		$data['count'] = 100;
		$baichuan = D('Baichuan')->chatlogsGet($data);
		if($baichuan['messages']){
			return $baichuan['messages'];
		}elseif($baichuan['code']){
			die(JSON(array('errcode'=>$baichuan['code'],'errmsg'=>$baichuan['msg'],'sub_msg'=>$baichuan['sub_msg'])));
		}
		return false;
	}
	/*** 从H5页面到app的地址 ***/
	public function viewToApp(){
		$linkurl = I('get.linkurl');//返回html的地址
		$direction = I('get.direction');//去app的地方
	}

	/*** 从app到H5页面的地址 ***/
	public function appToView(){
		$get = I('get.');//返回html的地址
		$skipurl = url_param_encrypt($get['linkurl'],'D');
		unset($get['linkurl']);
		if(count($get))$urlstr = http_build_query($get);
		if($skipurl){
			$tourl = $skipurl.'?'.$urlstr;
		}elseif($linkurl){
			$tourl =urldecode($get['linkurl']) . '?'.$urlstr;
		}
		if($tourl)header("location:".$tourl);
	}


	//验证查询
	public function common() {
		header('Content-type: application/json');
		header("Content-type: text/html; charset=utf-8");
		$access_token = I('get.access_token');
		if(strtolower(CONTROLLER_NAME) != 'token'){
			$result = D('Rebateapp/FlApptoken')->checkToken($access_token);
			if($result['errcode']>0)die(JSON($result));
			$this->access_token = $result['access_token'];
			$this->appid = $result['appid'];
			$this->tokenid = $result['id'];
		}
	}


	/**第三方快捷登录**/
	public function sdklogin(){
		$this->common();
		header('Content-type: application/json');
		header("Content-type: text/html; charset=utf-8");
		$jid = trim(I('post.jid'));
		$openid = trim(I('post.openid'));
		$source = trim(I('post.source'));
		$avatar = trim(I('post.avatar'));
		$nickname = trim(I('post.nickname'));
		$clientid = trim(I('post.clientid'));
		$jid or die(JSON(array('errcode'=>80201,'errmsg'=>'无商家ID（jid）,请检查')));
		$openid or die(JSON(array('errcode'=>80202,'errmsg'=>'无第三方登录openid')));
		$source or die(JSON(array('errcode'=>80203,'errmsg'=>'无第三方登录来源')));
		$result = D('User')->sdklogin($jid,$openid,$source,$avatar,$nickname,$clientid);
		die(JSON($result));
	}
	
	
	/****获取当前版本号****/
	public function checkUpdate(){
		//$this->common();
		$jid = I('post.jid');
		$jid or die(JSON(array('errcode'=>80301,'errmsg'=>'无商家ID（jid）,请检查')));
		$merchantapp = M('merchant_app')->where(array('jid'=>$jid))->field('appversions,iosversions,appurl,up_explain')->find();
		$merchantapp or die(JSON(array('errcode'=>80302,'errmsg'=>'无此商家,请检查')));
		if($merchantapp['up_explain']){
			$merchantapp['up_explain'] = $this->escapeJsonString($merchantapp['up_explain']);
		}
		$merchantapp['downurl'] = U('Index/appdown@yd',array('jid'=>$jid,'type'=>'must'));
		$merchantapp['appurl'] = rtrim(U('@yd',null,null,false),'/').$merchantapp['appurl'];
		die(JSON($merchantapp));
	}

	/****处理换行符等特殊字符****/
    public function escapeJsonString($value) { 
    	$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
    	$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
    	$result = str_replace($escapers, $replacements, $value);
    	return $result;
    }


	/***检测utoken是否通过***/
	public function checkUtoken(){
		$this->common();
		$result = D('Usertoken')->checkUtoken(I('post.utoken'));
		if(is_array($result))die(JSON($result));
		return $result;
	}
	    
	public function logout(){
		$this->common();
		$result = D('Usertoken')->logoutUtoken(I('post.utoken'));
		if($result)
			die(JSON(array('errcode'=>'ok', 'errmsg'=>'退出成功')));
		else
			die(JSON(array('errcode'=>81444,'errmsg'=>'退出失败!')));
	}


	/***获取百川用户测试***/
	public function baichuanAccount(){
		//$this->usercommon();
		$this->userid = I('post.userid',1);
		$data = array();
		$data['userid'] = 'dishuos'.$this->userid;
		$data['password'] = D('Baichuan')->password($this->userid);
		$baichuan = D('Baichuan')->usersAdd($data);
		if($baichuan['uid_succ']['string']){
			return $baichuan['uid_succ']['string'];
		}elseif($baichuan['code']){
			$baichuan = D('Baichuan')->usersUpdate($data);
		}
		die(JSON($data));
	}

	/***批量获取聊天用户头像***/
	public function bcUsersInfo(){
		$datalist = array();
		$users = explode(',',I('post.users'));
		$type = I('post.type','dishuuser');
		if($users)foreach($users as $value){
			$userids[] = ltrim($value,$type);
		}
		if(!$userids)die(JSON($datalist));
		
		if($type=='dishuuser'){
			$userdata = D('User')->where(array('u_id'=>array('in',$userids)))->getField('u_id,u_name,u_ename,u_phone,u_avatar');
			if($userdata)foreach($userdata as $key => $value){
				$uinfo = array();
				$uinfo['nickname'] = $value['u_ename']?$value['u_ename']:$value['u_name'];
				$uinfo['mobile'] = $value['u_phone'];
				$uinfo['avatar'] = $value['u_avatar'];
				$datalist[$type.$key] = $uinfo;
			}
		}
		die(JSON($datalist));
	}


	/***获取百川用户的登录信息***/
	public function baichuanUser(){
		$userid = I('post.userid');
		$type = I('post.type');
		$userkey = I('post.userkey');
		if($type=='dishumd' && stristr($userid,'-')){
			$user = explode('-',$userid);
			$userid = $user[0];
			$serverid = intval($user[1]);
		}
		$bcutypes = D('Baichuan')->userType();
		intval($userid) or die(JSON(array('errcode'=>80401,'errmsg'=>'请提交正确的用户UserId')));
		($type && in_array($type,$bcutypes)) or die(JSON(array('errcode'=>80402,'errmsg'=>'请提交正确的用户类型')));
		//($userkey && D('Baichuan')->userkeyVerify($userkey,$userid,$type)) or die(JSON(array('errcode'=>80403,'errmsg'=>'请提交正确的密匙')));
		$data = array();
		$data = D('Baichuan')->userInfo($userid,$type);
		$data['userid'] = $type.$userid.($serverid?'-'.$serverid:'');//补齐门店多客服
		$data['password'] = D('Baichuan')->password($userid);
		$baichuan = D('Baichuan')->usersAdd($data);
		if($baichuan['code'])$baichuan = D('Baichuan')->usersUpdate($data);
		die(JSON($data));
	}



	/***用户和商家建立聊天***/
	public function buildChat(){
		$userid = I('post.userid');
		$jid = I('post.jid');
		$userkey = I('post.userkey');
		$type = I('post.type','dishuuser');
		$bcutypes = D('Baichuan')->userType();
		intval($userid) or die(JSON(array('errcode'=>80401,'errmsg'=>'请提交正确的用户UserId')));
		intval($jid) or die(JSON(array('errcode'=>80402,'errmsg'=>'请提交正确的用户JID')));
		($type && in_array($type,$bcutypes)) or die(JSON(array('errcode'=>80403,'errmsg'=>'请提交正确的用户类型')));
		//($userkey && D('Baichuan')->userkeyVerify($userkey,$userid,$type)) or die(JSON(array('errcode'=>80404,'errmsg'=>'请提交正确的密匙')));
		$data = array();
		$data['userid'] = 'dishuuser'.$userid;
		$data['password'] = D('Baichuan')->password($userid);
		$baichuan = D('Baichuan')->usersAdd($data);
		if($baichuan['code'])$baichuan = D('Baichuan')->usersUpdate($data);
		$data['merchant'] = D('Baichuan')->merchant($jid);
		die(JSON($data));
	}

	/***获取用户基本信息**/
	public function usercommon(){
		$result = $this->checkUtoken();
		$this->userid = $result;
		/**验证通过写获取方法**/
	}


	/***获取用户信息***/
	public function userinfo(){
		$this->usercommon();
		$userinfo = D('User')->userinfo($this->userid);
		if($userinfo['u_openid']=='(null)'){
			$userinfo['u_name']='用户';
			$userinfo['u_avatar']='';
		}
		$this->userinfo = $userinfo;
		die(JSON($this->userinfo));
	}
	
	/***获取订单信息***/
	public function orderinfo(){
		$oid = I('post.oid','','trim');
		if(!$oid)die(JSON(array('errcode'=>81201,'errmsg'=>'订单号不能为空')));
		$orderinfo = D('Order')->field('o_id,o_price,o_jid,o_sid,o_type,o_pway,o_name,o_phone,o_address')->where(array('o_id'=>$oid,'o_pstatus'=>'0'))->find();
		$orderinfo or die(JSON(array('errcode'=>81202,'errmsg'=>'订单不存在')));
		if($orderinfo)$orderinfo['sname'] = D('Shop')->where(array('sid'=>$orderinfo['o_sid']))->getField('sname');
		if(!$orderinfo['sname'])$orderinfo['sname'] = $orderinfo['title'];
		switch ($orderinfo['o_gtype']){
			case 'Choose':
			 	$orderinfo['title'] = $orderinfo['sname'].'-在线下单';
				$linkurl = url_param_encrypt(U('Choose/index@yd',array('jid'=>$orderinfo['o_jid'],'sid'=>$orderinfo['o_sid'])),'E');
			  break;  
			default:
				$orderinfo['title'] = $orderinfo['sname'].'-在线下单';
				$linkurl = url_param_encrypt(U('Choose/index@yd',array('jid'=>$orderinfo['o_jid'],'sid'=>$orderinfo['o_sid'])),'E');
		}
		$orderinfo['notify_url'] = U('Api/alipayNotify@yd');//此步后面加判断，是使用什么支付，返回的字段
		$orderinfo['success_url'] = U('User/myorder@yd',array('returnurl'=>$linkurl));//支付成功返回的url
		$orderinfo['fail_url'] = U('User/myorder@yd',array('returnurl'=>$linkurl));//支付失败返回的url
		$orderinfo?die(JSON($orderinfo)):die(JSON(array('errcode'=>81202,'errmsg'=>'订单号不存在订单!')));
	}
	


	/****支付成功返回的数据****/
	public function paysuccess(){
		//$this->common();
		$oid = I('post.oid','','trim');
		$pay_trade_no = I('post.pay_trade_no');
		$pway = I('post.pway');
		if(!$oid)die(JSON(array('errcode'=>81211,'errmsg'=>'订单号不能为空')));
		$orderinfo = D('Order')->where(array('o_id'=>$oid))->find();
		if($orderinfo['o_pstatus'] < 1){ 
			$ainfo['o_pway'] = $pway;
			$ainfo['o_pstime'] = date('Y-m-d H:i:s');
			$ainfo['o_pstatus'] = '1';
			$ainfo['o_dstatus'] = '3';
			$ainfo['o_type'] = '2';
			D('Order')->where(array('o_uid'=>$orderinfo['o_uid'],'o_id'=>$oid,'o_pstatus'=>'0'))->setField($ainfo);
			if($pay_trade_no){
				/***记录支付日志***/
				$paylog = array();
				$paylog['oid'] = $oid;
				$paylog['uid'] = $orderinfo['o_uid'];
				$paylog['gtype'] = $orderinfo['o_gtype'];//
				$paylog['pay_price'] = $orderinfo['o_price'];
				$paylog['pay_time'] = date('Y-m-d H:i:s');
				$paylog['pay_trade_no'] = $pay_trade_no;
				$paylog['pay_way'] = $pway;
				$paylog['pay_type'] = '2';
				M('pay_log')->data($paylog)->add();
			}

			die(JSON(array('errcode'=>'ok', 'errmsg'=>'您的订单支付成功')));
		}elseif($orderinfo['o_pstatus'] == 1){
			die(JSON(array('errcode'=>'ok', 'errmsg'=>'订单支付成功')));
		}
		die(JSON(array('errcode'=>81204,'errmsg'=>'订单支付错误，请联系客服处理!')));
	}




	/***客户端【安卓与苹果】支付宝支付的异步通知***/
	public function alipayNotify(){
		$alipay_config = array();
		/*验证支付方式有效性*/
		$alipayclientnotify = new \Org\Util\pay\alipayclientnotify($alipay_config);
		$verify_result = $alipayclientnotify->verifyNotify();
		if($verify_result) {//验证成功
			$trade_status = I('post.trade_status');//交易状态
			$oid = I('post.out_trade_no');//支付宝订单号
			$pay_trade_no = I('post.trade_no');//支付宝交易号
			($trade_status=='TRADE_SUCCESS' || $trade_status=='TRADE_FINISHED') or die('success');//交易成功，否则直接退回，返回接受数据成功
			$pway = 'alipaywap';
			$orderinfo = D('Order')->where(array('o_id'=>$oid))->find();
			if(!$orderinfo)die('fail');
			if($orderinfo['o_pstatus']<1){
				$ainfo['o_pway'] = $pway;
				$ainfo['o_pstime'] = date('Y-m-d H:i:s');
				$ainfo['o_pstatus'] = '1';
				$ainfo['o_dstatus'] = '3';
				$ainfo['o_type'] = '2';
				
			}
			D('Order')->where(array('o_id'=>$oid))->setField($ainfo);
			$paylog = M('pay_log')->where(array('pay_trade_no'=>$pay_trade_no))->find();
			if(!$paylog){
				/***记录支付日志***/
				$paylog = array();
				$paylog['oid'] = $oid;
				$paylog['jid'] = $orderinfo['o_jid'];
				$paylog['uid'] = $orderinfo['o_uid'];
				$paylog['gtype'] = $orderinfo['o_gtype'];//
				$paylog['pay_price'] = $orderinfo['o_price'];
				$paylog['pay_time'] = date('Y-m-d H:i:s');
				$paylog['pay_trade_no'] = $pay_trade_no;
				$paylog['pay_way'] = $pway;
				$paylog['pay_type'] = '2';
				$paylog['pay_status'] = '1';
				$r = M('pay_log')->data($paylog)->add();
				if($r){
					$merchant = M('merchant')->where(array('jid'=>$orderinfo['o_jid']))->find();
					M('member')->where(array('mid'=>$merchant['mid']))->setInc('money',$orderinfo['o_price']);
				}

				//判断是否发快递
				if (in_array($orderinfo['o_jid'], array_keys(C('EXPRESS_JID')))){
				 	$data  = C('EXPRESS_JID');
				 	$data  = $data[$orderinfo['o_jid']];
					$re    = D('Order')->xmlservice($oid , $orderinfo['o_name'] , $orderinfo['o_phone'] , $orderinfo['o_address'] , $data['d_company'] , $data['d_contact'] , $data['d_telphone'] , $data['d_address']);
					D('Order')->where(array('o_id'=>$orderinfo['o_id']))->setField(array('o_close_reason'=>$re));
				}
				die("success");
			}
			die("fail");
		}else {
			die("fail");
		}
	}
	
	//添加收货地址
	public function addressAdd(){
		$this->usercommon();
		
		$name     = I('name');
		$phone    = I('phone');
		$address  = I('address');
		$maddress = I('maddress');
		$zipcode  = I('zipcode');
		$defaults  = I('defaults',0);
		
		if(empty($name) || empty($phone) || empty($address) || empty($maddress)){
			$result = array(
				"errcode" => '81301',
				"errmsg"  => '信息不完整',
				"events"  => array(),
			);
			die(JSON($result));
		}
		if($defaults == 1){
			M('receiving')->where(array('userid'=>$this->userid))->save(array('defaults'=>0));
		}
		$opt = array(
				'userid' => $this->userid,
				'name'   => $name,
				'phone'  => $phone,
				'address' => $address,
				'maddress' => $maddress,
				'zipcode'  =>$zipcode,
				'defaults'  => $defaults
		);
		$r = D('Address')->Address_add($opt);
		if($r){
			$result = array(
				"errcode" => 'ok',
				"errmsg"  => '操作成功',
				"events"  => array(),
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81300',
					"errmsg"  => '操作失败',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
	//编辑收货地址
	public function addressEdit(){
		$this->usercommon();
		
		$receivingid     = I('receivingid');
		$name     = I('name');
		$phone    = I('phone');
		$address  = I('address');
		$maddress = I('maddress');
		$zipcode  = I('zipcode');
		$defaults  = I('defaults',0);
		
		if(empty($receivingid) || empty($name) || empty($phone) || empty($address) || empty($maddress)){
			$result = array(
				"errcode" => '81301',
				"errmsg"  => '信息不完整',
				"events"  => array(),
			);
			die(JSON($result));
		}
		if($defaults == 1){
			M('receiving')->where(array('userid'=>$this->userid))->save(array('defaults'=>0));
		}
		
		$opt = array(
				'name'   => $name,
				'phone'  => $phone,
				'address' => $address,
				'maddress' => $maddress,
				'zipcode'  =>$zipcode,
				'defaults'  => $defaults
		);
		$r = D('Address')->Address_edit($this->userid,$receivingid,$opt);
		$r = true;
		if($r){
			$result = array(
				"errcode" => 'ok',
				"errmsg"  => '操作成功',
				"events"  => array(),
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81300',
					"errmsg"  => '操作失败',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
	//收货地址列表
	public function addressList(){
		$this->usercommon();
		$r = D('Address')->getAddressList($this->userid);
		if($r){
			$result = array(
				"errcode" => 'ok',
				"errmsg"  => '操作成功',
				"events"  => $r
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81302',
					"errmsg"  => '没有查询到数据',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
	//收货地址详细信息
	public function addressInfo(){
		$this->usercommon();
		
		$receivingid     = I('receivingid');		
		$r = D('Address')->getAddressInfo($receivingid);
		if($r){
			$result = array(
				"errcode" => 'ok',
				"errmsg"  => '操作成功',
				"events"  => $r
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81302',
					"errmsg"  => '没有查询到数据',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}
	//删除收货地址
	public function addressDel(){
		$this->usercommon();
		
		$receivingid     = I('receivingid');
		$r = D('Address')->Address_del($this->userid,$receivingid);
		if($r){
			$result = array(
				"errcode" => 'ok',
				"errmsg"  => '操作成功',
				"events"  => array(),
			);
			die(JSON($result));
		}else{
			$result = array(
					"errcode" => '81300',
					"errmsg"  => '操作失败',
					"events"  => array(),
			);
			die(JSON($result));
		}
	}


	/******/

	/**接受clientid***/
	/*
	public function verifyClient(){
		$clientid = I('get.clientid');
		$key = I('get.key');
		if(!$clientid)exit(json_encode(array('errcode'=>'30001','errmsg'=>'clientid不能为空')));
		$authkey = md5($this->appkey.$clientid);
		if($authkey == $key)
			cookie('clientid',I('clientid'));
		else
			exit(json_encode(array('errcode'=>'30002','errmsg'=>'密匙不正确')));
		die();
	}
	*/
	/***地鼠微信支付APP创建订单***/
	public function dsWxJsPay(){
		$oid = I('post.oid','','trim');
		if(!$oid)die(JSON(array('errcode'=>81201,'errmsg'=>'订单号不能为空')));
		$orderinfo = D('Order')->field('o_id,o_price,o_sid,o_gtype')->where(array('o_id'=>$oid,'o_pstatus'=>'0'))->find();
		$orderinfo or die(JSON(array('errcode'=>81202,'errmsg'=>'订单不存在')));
		if($orderinfo)$orderinfo['sname'] = D('Shop')->where(array('sid'=>$orderinfo['o_sid']))->getField('sname');

		switch ($orderinfo['o_gtype']){
			case '1':
				$orderinfo['title'] = $orderinfo['sname'].'-在线下单';
				$linkurl = url_param_encrypt(U('Shop/index@yd',array('sid'=>$orderinfo['o_sid'])),'E');
				break;
			case '2':
				$linkurl = url_param_encrypt(U('Shop/index@yd',array('sid'=>$orderinfo['o_sid'])),'E');
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
		vendor("Weixin.JsApiPay");
		$logHandler= new \CLogFileHandler(\WxPayConfig::Log());
		$log = \Log::Init($logHandler, 15);
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($orderinfo['title']);
		$input->SetAttach($orderinfo['sname']);
		$input->SetOut_trade_no($oid);
		$input->SetTotal_fee($orderinfo['o_price']*100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag($orderinfo['sname']);
		$input->SetNotify_url(U('Api/dsWxNotify@yd'));
		$input->SetTrade_type("APP");
		$order = \WxPayApi::unifiedOrder($input);

		$order['timestamp'] = time();
		$order['oid'] = $oid;
		$order['errcode'] = 'ok';
		$order['paykey'] = \WxPayConfig::KEY;
		$order['errmsg'] = $order['return_msg'];
		$order['success_url'] = U('User/myorder@yd',array('returnurl'=>$linkurl));//支付成功返回的url
		$order['fail_url'] = U('User/myorder@yd',array('returnurl'=>$linkurl));//支付失败返回的url
		$order?die(JSON($order)):die(JSON(array('errcode'=>81202,'errmsg'=>'订单号不存在订单!')));
	}


	/***微信支付接受订单***/
	public function dsWxNotify(){
		vendor("Weixin.JsApiPay");
		vendor("Weixin.lib.WxPayNotify");
		vendor("Weixin.lib.PayNotifyCallBack");
		$logHandler= new \CLogFileHandler(\WxPayConfig::Log());
		$log = \Log::Init($logHandler, 15);
		\Log::DEBUG("begin notify");
		$notify = new \PayNotifyCallBack();
		$notify->Handle(false);
		$result = $notify->callback;
		if($result['transaction_id']){ //根据交易号判断支付是否成功，
			$order_info = M('Order')->where(array('o_id'=>$result['out_trade_no']))->find();
			$pay_info = array();
			$pay_info['pay_type'] = 'weixin';
			$pay_info['trade_no'] = $result['transaction_id'];
			$pay_info['out_trade_no'] = $result['out_trade_no'];
			$re = $this->dsWxPaySuccess($pay_info);//支付成功处理
			if ($re == true && in_array($order_info['o_jid'], array_keys(C('EXPRESS_JID')))){
			 	$data  = C('EXPRESS_JID');
			 	$data  = $data[$order_info['o_jid']];
				$re    = D('Order')->xmlservice($pay_info['out_trade_no'] , $order_info['o_name'] , $order_info['o_phone'] , $order_info['o_address'] , $data['d_company'] , $data['d_contact'] , $data['d_telphone'] , $data['d_address']);
				D('Order')->where(array('o_id'=>$order_info['o_id']))->setField(array('o_close_reason'=>$re));
			}

		}
	}


	/**帝鼠OS支付成功后处理**/
	public function dsWxPaySuccess($pay_info){
		$order_info = M('Order')->where(array('o_id'=>$pay_info['out_trade_no']))->find();
		if(!$order_info)return false;
		if($order_info['o_pstatus']>0)return true;
		$orderdata = array('o_pstatus' => '1','o_type'=>'1','o_pway'=>$pay_info['pay_type'],'o_pstime'=>date("Y-m-d H:i:s"));
		M('Order')->where(array('o_id'=>$pay_info['out_trade_no']))->save($orderdata);
		if($order_info['o_pstatus'] == '0'){
			$logdata =array();
			$logdata['oid'] = $order_info['o_id'];
			$logdata['uid'] = $order_info['o_uid'];
			$logdata['pay_type'] = $order_info['o_gtype'];
			$logdata['pay_price'] = $order_info['o_price'];
			$logdata['pay_time'] = date('Y-m-d H:i:s');
			$logdata['pay_way'] = $pay_info['pay_type'];
			$logdata['pay_trade_no'] = $pay_info['trade_no'];
			$result = M('Paylog')->add($logdata);
			if($result && $order_info['o_type']==2){//如果支付款是汇入系统账户，更新商家的账户余额
				$merchant = M('merchant')->where(array('jid'=>$order_info['o_jid']))->find();
				if($merchant)M('member')->where(array('mid'=>$merchant['mid']))->setInc('money',$order_info['o_price']);
			}
			return true;
		}else return false;
	}






}