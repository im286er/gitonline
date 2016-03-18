<?php
namespace Mobile\Controller;
use OT\DataDictionary;
use ORG\ThinkSDK\ThinkOauth;

class UserController extends MobileController {
	//用户中心首页
	public function index(){
		
		if($this->mid){
			$member = M('FlUser')->find($this->mid);
			$this->assign('member',$member);
		}
		$merchant = M('Merchant')->where("jid='{$this->jid}'")->find();
		$this->assign('merchant',$merchant);
		$merchantapp = M('merchant_app')->where("jid='{$this->jid}'")->field('appversions,iosversions')->find();
		$this->assign('merchantapp',$merchantapp);
		$page_name = $merchant['mabbreviation'];
		$this->assign('page_url','banreturn');
		$this->assign('page_name',$page_name);
		$this->mydisplay();
	}

	//系统更新检测
	public function is_update(){
		if(IS_POST){
			sleep(1);
			$appversions = I('post.appversions');
			$msystem = I('post.msystem');
			if(!$appversions)$this->ajaxReturn(array('status'=>'0' ,'msg'=>'当前版本为最新版本!'));
			$merchantapp = M('merchant_app')->where("jid='{$this->jid}'")->field('appurl,iosurl,appversions,iosversions,up_explain')->find();
			$versions = ($msystem=='ios'?$merchantapp['iosversions']:$merchantapp['appversions']);
			if($versions > $appversions){
				$msg = '有最新版本 v'.$versions.'，现在需要更新吗？';
				if($merchantapp['up_explain']){
					$msg = null;
					$msg .= '<strong>v'.$versions.'版本更新内容：</strong>';
					$msg .= '<div style="width:100%; height:110px; overflow-x:hidden;overflow-y:auto;"><p>'.$merchantapp['up_explain'].'</p></div>';
				}
				$this->ajaxReturn(array('status'=>'1' ,'msg'=>nl2br($msg),'url'=>'http://'.I('server.SERVER_NAME').$merchantapp['appurl']));
				//$this->ajaxReturn(array('status'=>'1' ,'msg'=>$msg,'url'=>U('Index/appdown@yd',array('jid'=>$this->jid,'v'=>time())) ));
			}else{
				$this->ajaxReturn(array('status'=>'0' ,'msg'=>'当前版本为最新版本!'));
			}
		}
		//if(I('get.versions') && $this->mid)
		//M('user')->where(array('u_id'=>$this->mid))->setField('u_versions',I('get.versions'));
		$this->ajaxReturn(array('status'=>'0' ,'msg'=>'检测更新错误，请稍后再试!'));
	}
	//在APP状态下二次请求是否已经登录过
	public function is_login(){
		if($this->mid){
			$member = M('FlUser')->find($this->mid);
			$this->ajaxReturn(array('flu_nickname'=>($member['flu_nickname']?$member['flu_nickname']:$member['flu_username']),'flu_avatar'=>$member['flu_avatar']));
		}
		exit;
	}
	
	//获取位置信息
	public function location(){
		if(I('post.longitude') && I('post.latitude')){
			$longitude = I('post.longitude');
			$latitude = I('post.latitude');
			if($longitude && $latitude)
			cookie('location',$longitude.','.$latitude,86400);
		}elseif(I('post.location')){
			$location = I('post.location');
			cookie('location',$location,86400);
		}
		die();
	}

	public function myaccount(){
		
		if((!$this->mid && !cookie('opentype')) )
		redirect(U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('User/index'),'E'),'returnurl'=>url_param_encrypt(U(),'E'))));
		if(IS_POST){
			$data = array('flu_nickname'=>I('post.flu_nickname'));
			$result = M('FlUser')->where(array('flu_userid'=>$this->mid))->setField($data);
			exit($result?'1':'0');
		}
		$user = M('FlUser')->find($this->mid);
		$this->assign('user',$user);
		$page_name = '账户信息';
		$this->assign('page_name',$page_name);
		$this->assign('page_url',U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		$this->mydisplay();
	} 

	//我的订单
	public function myorder(){
		
		if((!$this->mid && !cookie('opentype')) )
		redirect(U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('User/index'),'E'),'returnurl'=>url_param_encrypt(U(),'E'))));

		$odstatus = array(
				1 => '待处理',
				3 => '待完成',
				4 => '已完成',
				5 => '已关闭',
			);
		//$oostatus = array(0=>'未付款',1=>'已付款');
		$this->assign('odstatus',$odstatus);
		//$this->assign('oostatus',$oostatus);
		
		$order = M('order')->where(array('o_uid'=>$this->mid,'o_jid'=>$this->jid,'o_gtype'=>'Choose'))->order('o_dstime desc')->select();
		
		if($order){
			$oid_list = array();
			foreach($order as $key=>$value){
				$oid_list[] = $value['o_id'];
				//$vu_id = M('voucher_order')->where(array('mid'=>$this->mid,'o_id'=>$value['o_id']))->getField('vu_id');
				//if($vu_id){
				$order[$key]['used_vprice'] = M("voucherOrder")->alias("AS vo")->field("vo.o_price,v.vu_name")->join("__VOUCHER__ AS v ON vo.vu_id=v.vu_id")->where( array('vo.mid'=>$this->mid, 'vo.o_id'=>$value['o_id']) )->select();
				//}
				
				if($value['o_type'] == 0){
					$order[$key]['paytype_name'] = '线下支付';
				}else{
					if($value['o_pstatus'] == 0){
						$order[$key]['paytype_name'] = '待付款';
					}elseif($value['o_pstatus'] == 2){
						$order[$key]['paytype_name'] = '已退款';
					}elseif($value['o_pstatus'] == 3){
						$order[$key]['paytype_name'] = '待退款';
					}else{
						$order[$key]['paytype_name'] = '线上已付';
					}
				}
				$order[$key]['dtype_name'] = $odstatus[$value['o_dstatus']];
				if($value['o_close'] == 1){
					$order[$key]['close_name'] = '您的取消订单申请正在审核中';
				}elseif($value['o_close'] == 2){
					$order[$key]['close_name'] = '您的取消订单申请已通过';
				}elseif($value['o_close'] == 3){
					$order[$key]['close_name'] = '您的取消订单申请被拒绝';
				}
				//查询商家电话
				$order[$key]['shop_tel'] = M('shop')->where(array('sid'=>$value['o_sid']))->getField('mservetel');
			}
			$oid_str = join(',',$oid_list);
			$o_table = $order[0]['o_table'];
			$opt = array(
				'sp_oid' => array('in',$oid_str),
			);
			$ogoods = M($o_table)->where($opt)->select();
		
		foreach($order as $k1=>$v1){
			foreach($ogoods as $k2=>$v2){
				if($v2['sp_oid'] == $v1['o_id']){
					$order[$k1]['ogoods'][] = $v2;
				}
			}
		}
		
		$this->assign('order',$order); 
		}
		if(I('get.jump')){
		$this->assign('page_url',U('Index/index@yd',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		}else{
		$this->assign('page_url',U('User/index@yd',array('opentype'=>cookie('opentype'),'jid'=>$this->jid))); 	
		}
 
		$page_name = '我的订单';
		$this->assign('page_name',$page_name);
		$this->mydisplay();
	}
	
	public function myreserve(){
		
		if((!$this->mid && !cookie('opentype')) )
		redirect(U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('User/index'),'E'),'returnurl'=>url_param_encrypt(U(),'E'))));
	
		$odstatus =  array(
				1 => '预定待处理',
				3 => '同意预定',
				4 => '拒绝预定',
				5 => '预定关闭',
		);
		
		$order = M('order')->where(array('o_uid'=>$this->mid,'o_jid'=>$this->jid,'o_gtype'=>'Seat'))->order('o_dstime desc')->select();
		
		if($order){
			$oid_list = array();
			foreach($order as $key=>$value){
				$oid_list[] = $value['o_id'];
				$order[$key]['status_name'] = $odstatus[$value['o_dstatus']];
				$order[$key]['sname'] = M('shop')->where(array('sid'=>$value['o_sid']))->getField('sname');
			}
			$oid_str = join(',',$oid_list);
			$o_table = $order[0]['o_table'];
			$opt = array(
					'sp_oid' => array('in',$oid_str),
			);
			$ogoods = M($o_table)->where($opt)->select();
		}
		foreach($order as $k1=>$v1){
			foreach($ogoods as $k2=>$v2){
				if($v2['sp_oid'] == $v1['o_id']){
					$order[$k1]['ogoods'][] = $v2;
				}
			}
		}
		if(I('get.jump')){
		$this->assign('page_url',U('Index/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		}else{
		$this->assign('page_url',U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));	
		}		
		$this->assign('order',$order);
		$page_name = '我的预定';
		$this->assign('page_name',$page_name);
		$this->mydisplay();
	}

	//我的优惠券
	public function mycoupon(){
		
		if((!$this->mid && !cookie('opentype')) )
		redirect(U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('User/index'),'E'),'returnurl'=>url_param_encrypt(U(),'E'))));
		$voucher = M('voucher_user')->alias('u')->join('azd_voucher v on v.vu_id=u.vu_id')->where(array('u.mid'=>$this->mid,'v.vu_jid'=>$this->jid,'v.vu_status'=>1))->field('u.vu_price as price,v.*')->select();
		$hadcoupon = $unusedcoupon = $pastcoupon = array();
		$n = date("Y-m-d H:i:s");
		foreach($voucher as $k=>$v){
			if($v['price'] > 0 && $n >= $v['vu_stime'] && $n <= $v['vu_etime']){
				$hadcoupon[] = $v;
			}elseif($v['price'] == 0){
				$unusedcoupon[] = $v;
			}else{
				$pastcoupon[] = $v;
			}
		}
		//print_r($hadcoupon);exit;
		$this->assign('hadcoupon',$hadcoupon); //未使用
		$this->assign('unusedcoupon',$unusedcoupon); //已使用
		$this->assign('pastcoupon',$pastcoupon); //已过期
		$page_name = '我的优惠券';
		$this->assign('page_name',$page_name);
		$this->assign('page_url',U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		$this->mydisplay();
	}
	
	//意见反馈
	public function opinion(){
		
		if((!$this->mid && !cookie('opentype')) )
		redirect(U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('User/index'),'E'),'returnurl'=>url_param_encrypt(U(null,array('jump'=>I('get.jump'))),'E'))));
		if(IS_POST){
			$op_content = I('post.op_content');
			$op_telphone = I('post.op_telphone');
			$op_sid = I('post.op_sid');
			if(!$op_content)exit('亲！有您的意见我们会做的更好哦！');
			$Opinion = M("Opinion"); // 实例化User对象
			$data['op_uid'] = $this->mid;
			$data['op_jid'] = $this->jid;
			$data['op_sid'] = $op_sid;
			$data['op_telphone'] = $op_telphone;
			$data['op_content'] = $op_content;
			$data['op_addtime'] = time();
			$data['op_ip'] = get_client_ip();
			$data['op_status'] = '1';
			$result = $Opinion->add($data);
			
			//向商家发送一个消息（自定义推送，商家APP中可以查看）
			if( $result ) {
				$info = array();
				$info['jid'] = $this->jid;
				$info['sid'] = $op_sid;
				$info['avatar'] = M('user')->where("u_id=".$this->mid)->getField('u_avatar');
				$info['title'] = \Org\Util\String::msubstr($op_content, 0, 10);
				$info['content'] = $op_content;
				$info['addtime'] = date('Y-m-d H:i:s');
				$info['type'] = 1;
				M('appmsg')->add($info);
			}
			
			exit($result?'1':'0');
		}
		$opinion = M('Opinion')->where("op_uid = {$this->mid} AND op_status = '1' ")->order('op_addtime desc')->limit(20)->select();
		$this->assign('opinion',$opinion);
		$member = M('FlUser')->where('flu_userid='.$this->mid)->find();
		$shoplist = M('shop')->where(array('jid' => $this->jid,'status' => '1'))->select();
		$merchantapp = M('merchant_app')->where("jid='{$this->jid}'")->find();
		$this->assign('merchantapp',$merchantapp);
		$this->assign('shoplist',$shoplist);
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'AutoReply2.php') && $AutoReply=file_get_contents($path.'AutoReply2.php');
		$this->assign('AutoReply',$AutoReply);
		$page_name = '意见反馈';
		$this->assign('page_name',$page_name);
		
		if(I('get.jump')){
			if(I('get.jump') == 'order'){
				$this->assign('page_url',U('User/myorder'));
			}else{
				$this->assign('page_url',U('Index/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
			}
		}else{
			$this->assign('page_url',U('User/aboutus',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));	
		}	
		$this->assign('member',$member);
		$this->mydisplay();
	}

	//关于我们
	public function aboutus(){
		$app = M("merchant_app")->where(array('jid'=>$this->jid))->find();
		$app['appjs'] = str_replace(chr(32), "&nbsp;",$app['appjs']);
		$this->assign('m_app',$app);
		$page_name = '关于我们';
		if(I('get.jump')==1){
			$this->assign('page_url',U('Index/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		}else{
			$this->assign('page_url',U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		}
		$this->assign('page_name',$page_name);
		
		//把所有的分店的电话获取出来
		$shop_tel = M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->field('sname,mservetel')->select();
		$this->assign('shop_tel', $shop_tel);
		
		$this->mydisplay();
	}

	//登录
/*

	public function login(){
		if($this->mid)U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid));
		I('get.returnurl') ? cookie('returnurl', I('get.returnurl')) : cookie('returnurl', null);
		if(url_param_encrypt(I('get.backurl'), 'D'))
			$this->assign('page_url',rtrim(url_param_encrypt(I('get.backurl'), 'D'),'.html')); 
		elseif(url_param_encrypt(I('get.returnurl'), 'D'))
			$this->assign('page_url','banreturn'); 
		else
			$this->assign('page_url',U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));

		$page_name = '登录';
		$this->assign('page_name', $page_name);
		$this->mydisplay();
	}

*/

	public function login(){
		if(IS_POST){
			$username = I('post.username');
			$password = I('post.password');
			$username or die(JSON(array('errcode'=>80101,'errmsg'=>'请填写用户名')));
			$password or die(JSON(array('errcode'=>80102,'errmsg'=>'请填写密码')));
			$result = D('FlUser')->login($username,$password);
			if($result['errcode']>0)die(JSON($result));
			cookie('mid', $result['flu_userid'],604800);
			die(JSON(array('errcode'=>0,'errmsg'=>'登录成功')));
		}
		if($this->mid)U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid));

		if(url_param_encrypt(I('get.returnurl'), 'D'))
			$returnurl = url_param_encrypt(I('get.returnurl'), 'D');
		elseif(I('get.returnurl'))
			$returnurl = urldecode(I('get.returnurl'));
		else
			$returnurl = U('User/index',array('jid'=>$this->jid));
		$this->assign('returnurl',$returnurl); 
		if(url_param_encrypt(I('get.backurl'), 'D'))
			$this->assign('page_url',rtrim(url_param_encrypt(I('get.backurl'), 'D'),'.html')); 
		elseif(url_param_encrypt(I('get.returnurl'), 'D'))
			$this->assign('page_url','banreturn'); 
		else
			$this->assign('page_url',U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		$page_name = '登录';
		$this->assign('page_name', $page_name);
		$this->mydisplay();
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
			!$is_mobile or die(JSON(array('errcode'=>81014,'errmsg'=>'该手机号码已注册用户！')));
		}elseif($verify=='mobile_verify'){
			$this->common();
			$this->userinfo['flu_phone'] or die(JSON(array('errcode'=>81015,'errmsg'=>'请先绑定手机后再操作')));
			$this->userinfo['flu_phone'] == $mobile or die(JSON(array('errcode'=>81016,'errmsg'=>'您输入的手机号码与您绑定的手机号码不一致')));
		}elseif($verify=='mobile_findpwd'){
			$is_mobile = D('FlUser')->where(array('flu_phone'=>$mobile))->find();
			$is_mobile or die(JSON(array('errcode'=>81014,'errmsg'=>'您输入的手机号码未注册帐号')));
		}
		$content = \Org\Util\String::randString(4, 1);

		session('sendcode', $content);
		session('sendsmsTel', $mobile);
		session('SendSms', $content);
		//die(JSON(array('errcode'=>'ok','errmsg'=>$content)));
		die(flsendmsg( $mobile, $content) ? JSON(array('errcode'=>'ok','errmsg'=>$content)):JSON(array('errcode'=>81444,'errmsg'=>'短信发送失败')));		
	}
	
	//手机注册
	public function register(){
		if(IS_POST){
			/**注册**/
			$username = trim(I('post.username'));
			$password = trim(I('post.password'));
			$sendcode = trim(I('post.sendcode'));
			$username or die(JSON(array('errcode'=>80101,'errmsg'=>'请填写手机号码')));
			$password or die(JSON(array('errcode'=>80102,'errmsg'=>'请填写密码')));
			$sendcode or die(JSON(array('errcode'=>80103,'errmsg'=>'请填写验证码')));
			$sendcode == session('sendcode') or die(JSON(array('errcode'=>80104,'errmsg'=>'填写的验证码有误')));
			$result = D('FlUser')->register($username,$password,$this->jid);
			if($result['errcode']>0)die(JSON($result));
			die(JSON(array('errcode'=>'ok','errmsg'=>'注册成功')));
		}
		if($this->mid)U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid));
		$this->assign('page_url',U('User/login',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		$page_name = '用户注册';
		$this->assign('page_name', $page_name);
		$this->mydisplay();
	}

	//忘记密码
	public function findpwd(){
		if($this->mid)U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid));
		if(IS_POST){
			$mobile = trim(I('post.mobile'));
			$sendcode = trim(I('post.sendcode'));
			$mobile or die( JSON( array('errcode'=>81010, 'errmsg'=>'请输入手机号码') ) );
			$sendcode or die(JSON(array('errcode'=>80103,'errmsg'=>'请填写验证码')));
			$sendcode == session('sendcode') or die(JSON(array('errcode'=>80104,'errmsg'=>'填写的验证码有误')));
			trim(I('post.password')) or die(JSON(array('errcode'=>81014,'errmsg'=>'请输入新密码')));
			D('FlUser')->is_mobile($mobile);
			$newpassword = D('FlUser')->passwordmd5(trim(I('post.password')));
			$result = D('FlUser')->where(array('flu_phone'=>$mobile))->setField('flu_password',$newpassword);
			die(JSON(array('errcode'=>'ok','errmsg'=>'找回密码成功')));
		}
		$page_name = '找回密码';
		$this->assign('page_name', $page_name);
		$this->mydisplay();
	}



	//退出登录
	public function logout(){
		cookie('mid',null);
		cookie('sdkid',null);
		redirect(U('index'));
	}
	
	//登录跳转地址
	public function goLogin($type = null){
		if(I('get.returnurl'))cookie('returnurl', I('get.returnurl'));
		if(strstr(I('server.HTTP_HOST'),'dishuos.com')==false){
			redirect(U('User/login'));
		}
		
		empty($type) && $this->error('参数错误');
		cookie('GTclientid', I('get.cid', ''));
		
		//如果是用户通过WIFI登录
		if( isset($_GET['ref']) && !empty($_GET['ref']) ) {
			cookie('UserMac', I('get.mac', ''));
			cookie('UserRid', I('get.rid', ''));
			cookie('UserRip', I('get.rip', ''));		
		}
		
		import("Org.ThinkSDK.ThinkOauth", LIB_PATH, '.class.php');
		$sns  = \ORG\ThinkSDK\ThinkOauth::getInstance($type);
		redirect($sns->getRequestCodeURL());
	}
	
	//授权回调地址
	public function callback($type=null, $code=null) {
		(empty($type) || empty($code)) && $this->error('参数错误');
		
		//加载ThinkOauth类并实例化一个对象
		import("Org.ThinkSDK.ThinkOauth");
		$sns  = ThinkOauth::getInstance($type);

		//腾讯微博需传递的额外参数
		$extend = null;
		if($type == 'tencent'){
			$extend = array('openid'=>$this->_get('openid'), 'openkey'=>$this->_get('openkey'));
		}

		//请妥善保管这里获取到的Token信息，方便以后API调用
		//调用方法，实例化SDK对象的时候直接作为构造函数的第二个参数传入
		//如： $qq = ThinkOauth::getInstance('qq', $token);
		$token = $sns->getAccessToken($code , $extend);
		
	//获取当前登录用户信息
		if(is_array($token)){
			$TypeEvent = new \Org\Util\TypeEvent();
			$user_info = $TypeEvent->$type($token);
			//$this->checkAuthLogin($type, $user_info, $token);
			if( cookie('UserMac') && cookie('UserRid') && cookie('UserRip') ) {
				setPortal(cookie('UserRid'), cookie('UserRip'), cookie('UserMac'));
			}
			$returnurl = cookie('returnurl');
			
			if(url_param_encrypt($returnurl, 'D')){
				$returnurl = url_param_encrypt($returnurl, 'D');
				//echo $returnurl;exit;
				redirect($returnurl);
			}
			if($returnurl) {
				header("location:".$returnurl);
			} else {
				redirect(U('User/index', array('jid'=>$this->jid)));
			}
		}
	}

/*	
	 //授权登录
	public function authLogin($mid,$msurname){
		cookie('mid', $mid,604800);
		cookie('msurname', $msurname,604800);
		$returnurl = cookie('returnurl');
		
		if(url_param_encrypt($returnurl, 'D')){
			$returnurl = url_param_encrypt($returnurl, 'D');
			//echo $returnurl;exit;
			redirect($returnurl);
		}
		if($returnurl) {
			header("location:".$returnurl);
		} else {
			redirect(U('User/index', array('jid'=>$this->jid)));
		}
	 }

	 //授权成功后检查
	 public function checkAuthLogin($type=null, $user_info=null, $token=null){
		//$member = M('Member')->where(array('m.mname'=>$token['openid'].$this->jid, 'l.jid'=>$this->jid))->alias('AS m')->join('__LOGIN_SDK__ as l ON m.mid=l.mid')->field('m.mid,m.mname,l.jid')->find();
	 	$member =  M('FlUser')->where(array('flu_openid'=>$token['openid'],'flu_jid'=>$this->jid))->find();
	 	
		$_cid = cookie('GTclientid');
		if( !$_cid ) $_cid = \Common\Org\Cookie::get("userclientid");
		
		$this->quickShare($type,$token);//添加分享
		if($member){
			M('FlUser')->where('flu_userid='.$member['flu_userid'])->setField(array('flu_avatar'=>$user_info['head'],'flu_nickname'=>$user_info['nick'],'flu_token'=>$token['access_token'],'flu_lasttime'=>date("Y-m-d H:i:s")));
			
			//更新个推的CID
			if( $_cid ) {
				M('FlUser')->where('flu_userid='.$member['flu_userid'])->setField(array('u_clientid'=>$_cid));
			}
			
			$username = $user_info['nick'];
			$this->authLogin($member['flu_userid'], $username);
		} else {
			$data = array(
				'flu_nickname' => $user_info['nick'],
				'flu_phone' => '',
				'flu_source' => strtolower($type),
				'flu_sjid' => $this->jid,
				'u_agent' => $_SERVER["HTTP_USER_AGENT"],
				'u_clientid' => $_cid ? $_cid : '',
				'u_avatar' => $user_info['head'],
				'u_openid' => $token['openid'],
				'u_token' => $token['access_token'],
				'u_regtime' => date("Y-m-d H:i:s"),
			);
			$result = M('user')->add($data);
		
			$this->authLogin($result, $user_info['nick']);
		}
	 }
	*/

	public function quickShare($type,$token){
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'ShareData.php') && $ShareData=json_decode(file_get_contents($path.'ShareData.php'),true);
		$shareurl = U('Index/appdown@yd',array('jid'=>$this->jid,'v'=>time()));
		$sharetext = "我正在使用帝鼠OS提供的物联网营销解决方案，硬件帮我拉客、软件帮我续客，微信跟支付宝都在帮我倒流。";
		if( cookie('UserMac') && cookie('UserRid') && cookie('UserRip') ) {
			setPortal(cookie('UserRid'), cookie('UserRip'), cookie('UserMac'));
			
			if($ShareData['internet']['text']){
				$sharetext = $ShareData['internet']['text'];//上网分享
			}else{
				return false;
			}
			
		} else {
			if($ShareData['login']['text']){
				$sharetext = $ShareData['login']['text'];
			}else{
				return false;
			}
		}
		$sharetext = $sharetext.$shareurl;
		$add_share = new \Org\Util\AddShare();
		$add_share->set_content($sharetext);
		$add_share->$type($token);
		return true;
	}

	/*商家从地图点击详情页面*/
	public function merchantInfo(){
		$merchantid = I('get.merchantid');
		$shopid = I('get.shopid');
		if($merchantid){
				$merchant = M("merchant")->where(array('jid'=>$merchantid))->find();
				$app = M("merchant_app")->where(array('jid'=>$merchantid))->find();
				$app['appjs'] = str_replace(chr(32), "&nbsp;",$app['appjs']);
				$this->assign('merchant',$merchant);
				$this->assign('m_app',$app);
				//广告图片 start
				$banner = M('banner');
				$opt = array(
					'jid' => $merchantid,
				);
				$banner_list = $banner->where($opt)->order('bid desc')->select();
				foreach($banner_list as $k=>$v){
					$banner_list[$k]['burl'] = stristr('http://', $v['burl']) ? $v['burl'] : 'http://'.$v['burl'];
					$banner_list[$k]['burl']  = $banner_list[$k]['burl'] == 'http://' ? '' : $banner_list[$k]['burl'];
				}
				$this->assign('banner_list',$banner_list);
				
				$vocation = M('vocation')->find($merchant['vid']);
				$this->assign('vocation',$vocation);
				//首页显示的活动 start
				$active_list =M('active')->where(array('av_jid' => $merchantid,'av_status' => 1))->order('av_id desc')->limit( C('NEW_ACTIVE_NUMBER') )->select();
				$this->assign('active_list',$active_list);
				//首页显示的活动 end
		}
		
		if($shopid){
			$shop = M("Shop")->where(array('status'=>'1','sid'=>$shopid))->find();
			$Voucher = M('Voucher')->where('FIND_IN_SET('.$shopid.',vu_sid) AND vu_stime < "'.date('Y-m-d H:i:s').'" AND vu_etime >= "'.date('Y-m-d H:i:s').'"')->select();
			$this->assign('voucher',$Voucher);
			$this->assign('shop',$shop);
		}
		
		$page_name = $shop['sname']? $shop['sname']:$merchant['mabbreviation'];
		$this->assign('page_name',$page_name);
		$this->mydisplay();
	}

	//订单操作
	public function changeOrder(){
		$type = I('type');
		$o_id = I('o_id');
		if($type == 'paytype'){
			$o_type = I('o_type') == 0 ? 0 : 2;
			$opt = array(
					'o_type' => $o_type
			);			
		}elseif($type == 'dtype'){
			$ctype = I("ctype");
			if($ctype == 1){//直接取消
				$opt = array(
					'o_dstatus' => 5
				);
			}elseif($ctype == 2){//需要取消原因
				$qx_reason = I("qx_reason");
				$opt = array(
					'o_dstatus' => 5,
					'o_close_reason' => $qx_reason
				);
			}elseif($ctype == 3){//提交申请
				$qx_reason = I("qx_reason");
				$opt = array(
					'o_close' => 1,
					'o_close_reason' => $qx_reason
				);
			}
		}
		
		M('order')->where(array('o_id'=>$o_id,'o_uid'=>$this->mid))->save($opt);
	
		$data = array("msg" => "true");
		$this->ajaxReturn($data);
	}
}
